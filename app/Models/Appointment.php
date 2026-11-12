<?php

declare(strict_types=1);

namespace App\Models;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use PDO;
use PDOException;
use Throwable;

final class Appointment
{
    private const BOOKING_TIMEZONE = 'Asia/Ho_Chi_Minh';
    private const ADMIN_TRANSITIONS = [
        'pending' => ['confirmed', 'rejected', 'cancelled'],
        'confirmed' => ['checked_in', 'cancelled'],
        'checked_in' => ['completed'],
    ];

    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @return list<string>
     */
    public static function adminTransitionsFrom(string $status): array
    {
        return self::ADMIN_TRANSITIONS[$status] ?? [];
    }

    /**
     * @return array{items: list<array<string, mixed>>, total: int}
     */
    public function adminPage(
        string $keyword,
        ?string $status,
        ?string $appointmentDate,
        int $page,
        int $pageSize = 25
    ): array {
        $pageSize = max(1, min($pageSize, 100));
        $conditions = [];
        $parameters = [];
        if ($keyword !== '') {
            $conditions[] = '(a.appointment_code LIKE :code_keyword
                OR u.full_name LIKE :name_keyword
                OR u.phone LIKE :phone_keyword
                OR v.license_plate LIKE :plate_keyword)';
            $pattern = '%' . $keyword . '%';
            $parameters['code_keyword'] = $pattern;
            $parameters['name_keyword'] = $pattern;
            $parameters['phone_keyword'] = $pattern;
            $parameters['plate_keyword'] = $pattern;
        }
        if ($status !== null) {
            $conditions[] = 'a.status = :status';
            $parameters['status'] = $status;
        }
        if ($appointmentDate !== null) {
            $conditions[] = 'a.appointment_date = :appointment_date';
            $parameters['appointment_date'] = $appointmentDate;
        }

        $where = $conditions === [] ? '' : ' WHERE ' . implode(' AND ', $conditions);
        $from = ' FROM appointments AS a
                  INNER JOIN users AS u ON u.id = a.user_id
                  INNER JOIN vehicles AS v ON v.id = a.vehicle_id
                  INNER JOIN vehicle_brands AS b ON b.id = v.brand_id';

        $count = $this->pdo->prepare('SELECT COUNT(*)' . $from . $where);
        $count->execute($parameters);
        $total = (int) $count->fetchColumn();
        $lastPage = max(1, (int) ceil($total / $pageSize));
        $effectivePage = min(max(1, $page), $lastPage);

        $statement = $this->pdo->prepare(
            'SELECT a.id, a.appointment_code, a.appointment_type,
                    a.appointment_date, a.appointment_time, a.note, a.admin_note,
                    a.status, a.created_at, a.updated_at,
                    u.full_name, u.phone, b.name AS brand_name,
                    v.model, v.license_plate,
                    t.id AS maintenance_ticket_id, t.ticket_code,
                    t.status AS maintenance_ticket_status
             ' . $from . '
             LEFT JOIN maintenance_tickets AS t ON t.appointment_id = a.id'
                . $where .
                ' ORDER BY a.created_at DESC, a.id DESC
                  LIMIT :limit OFFSET :offset'
        );
        foreach ($parameters as $name => $value) {
            $statement->bindValue($name, $value, PDO::PARAM_STR);
        }
        $statement->bindValue('limit', $pageSize, PDO::PARAM_INT);
        $statement->bindValue('offset', ($effectivePage - 1) * $pageSize, PDO::PARAM_INT);
        $statement->execute();

        return ['items' => $statement->fetchAll(), 'total' => $total];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findForAdmin(int $appointmentId): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT a.id, a.user_id, a.vehicle_id, a.appointment_code, a.appointment_type,
                    a.appointment_date, a.appointment_time, a.note, a.admin_note,
                    a.status, a.created_at, a.updated_at,
                    u.full_name, u.phone, u.email,
                    b.name AS brand_name, v.model, v.license_plate, v.manufacture_year,
                    t.id AS maintenance_ticket_id, t.ticket_code,
                    t.status AS maintenance_ticket_status
             FROM appointments AS a
             INNER JOIN users AS u ON u.id = a.user_id
             INNER JOIN vehicles AS v ON v.id = a.vehicle_id
             INNER JOIN vehicle_brands AS b ON b.id = v.brand_id
             LEFT JOIN maintenance_tickets AS t ON t.appointment_id = a.id
             WHERE a.id = :appointment_id
             LIMIT 1'
        );
        $statement->execute(['appointment_id' => $appointmentId]);
        $appointment = $statement->fetch();

        return $appointment === false ? null : $appointment;
    }

    public function updateStatusByAdmin(int $appointmentId, string $status, ?string $reason = null): bool
    {
        if ($appointmentId < 1 || !in_array($status, ['confirmed', 'rejected', 'checked_in', 'completed', 'cancelled'], true)) {
            throw new InvalidArgumentException('Trạng thái lịch hẹn không hợp lệ.');
        }
        if ($status === 'rejected' && (
            $reason === null
            || trim($reason) === ''
            || preg_match('/\A.{1,2000}\z/us', $reason) !== 1
        )) {
            throw new InvalidArgumentException('Vui lòng nhập lý do từ chối, tối đa 2.000 ký tự.');
        }

        $this->pdo->beginTransaction();
        try {
            $select = $this->pdo->prepare(
                'SELECT status FROM appointments WHERE id = :id LIMIT 1 FOR UPDATE'
            );
            $select->execute(['id' => $appointmentId]);
            $currentStatus = $select->fetchColumn();
            if ($currentStatus === false) {
                $this->pdo->rollBack();

                return false;
            }
            if (!in_array($status, self::adminTransitionsFrom((string) $currentStatus), true)) {
                throw new InvalidArgumentException('Không thể chuyển lịch hẹn từ trạng thái hiện tại sang trạng thái đã chọn.');
            }

            if ($status === 'completed') {
                $ticket = $this->pdo->prepare(
                    'SELECT status FROM maintenance_tickets WHERE appointment_id = :appointment_id LIMIT 1'
                );
                $ticket->execute(['appointment_id' => $appointmentId]);
                $ticketStatus = $ticket->fetchColumn();
                if ($ticketStatus !== false && $ticketStatus !== 'completed') {
                    throw new InvalidArgumentException('Chỉ hoàn thành lịch khi phiếu sửa chữa liên kết đã hoàn thành.');
                }
            }

            if ($status === 'rejected') {
                $update = $this->pdo->prepare(
                    'UPDATE appointments SET status = :status, admin_note = :admin_note
                     WHERE id = :id AND status = :current_status'
                );
                $update->execute([
                    'status' => $status,
                    'admin_note' => trim((string) $reason),
                    'id' => $appointmentId,
                    'current_status' => $currentStatus,
                ]);
            } else {
                $update = $this->pdo->prepare(
                    'UPDATE appointments SET status = :status
                     WHERE id = :id AND status = :current_status'
                );
                $update->execute([
                    'status' => $status,
                    'id' => $appointmentId,
                    'current_status' => $currentStatus,
                ]);
            }
            $this->pdo->commit();

            return $update->rowCount() === 1;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    /**
     * @param list<int> $serviceIds
     */
    public function createForCustomer(
        int $customerId,
        int $vehicleId,
        string $bookingType,
        string $appointmentCode,
        string $date,
        string $time,
        ?string $note,
        array $serviceIds
    ): int {
        if (
            $customerId < 1
            || $vehicleId < 1
            || !in_array($bookingType, ['onsite', 'online'], true)
            || !preg_match('/\AAC[0-9A-F]{24}\z/', $appointmentCode)
            || !$this->isFutureDateTime($date, $time)
        ) {
            throw new \InvalidArgumentException('Thông tin đặt lịch không hợp lệ.');
        }

        $serviceIds = array_values(array_unique($serviceIds));
        if (count($serviceIds) > 20 || array_filter($serviceIds, static fn (int $id): bool => $id < 1) !== []) {
            throw new \InvalidArgumentException('Danh sách dịch vụ không hợp lệ.');
        }

        $this->pdo->beginTransaction();
        try {
            $existing = $this->pdo->prepare(
                'SELECT id FROM appointments
                 WHERE user_id = :user_id AND appointment_code = :appointment_code
                 LIMIT 1 FOR UPDATE'
            );
            $existing->execute([
                'user_id' => $customerId,
                'appointment_code' => $appointmentCode,
            ]);
            $existingId = $existing->fetchColumn();
            if ($existingId !== false) {
                $this->pdo->commit();

                return (int) $existingId;
            }

            $vehicle = $this->pdo->prepare(
                'SELECT id FROM vehicles WHERE id = :vehicle_id AND user_id = :user_id LIMIT 1 FOR UPDATE'
            );
            $vehicle->execute(['vehicle_id' => $vehicleId, 'user_id' => $customerId]);
            if ($vehicle->fetchColumn() === false) {
                throw new \InvalidArgumentException('Xe không thuộc tài khoản hoặc không còn khả dụng.');
            }

            $services = $this->activeServicesForUpdate($serviceIds);
            if (count($services) !== count($serviceIds)) {
                throw new \InvalidArgumentException('Một hoặc nhiều dịch vụ đã ngừng hoạt động. Vui lòng tải lại.');
            }

            try {
                $insert = $this->pdo->prepare(
                    "INSERT INTO appointments
                        (user_id, vehicle_id, appointment_code, appointment_type,
                         appointment_date, appointment_time, note, status)
                     VALUES
                        (:user_id, :vehicle_id, :appointment_code, :appointment_type,
                         :appointment_date, :appointment_time, :note, 'pending')"
                );
                $insert->execute([
                    'user_id' => $customerId,
                    'vehicle_id' => $vehicleId,
                    'appointment_code' => $appointmentCode,
                    'appointment_type' => $bookingType,
                    'appointment_date' => $date,
                    'appointment_time' => $time,
                    'note' => $note,
                ]);
            } catch (PDOException $exception) {
                if ($exception->getCode() !== '23000') {
                    throw $exception;
                }

                $this->pdo->rollBack();
                $existingId = $this->findBySubmissionCode($customerId, $appointmentCode);
                if ($existingId !== null) {
                    return $existingId;
                }

                throw $exception;
            }

            $appointmentId = (int) $this->pdo->lastInsertId();
            if ($services !== []) {
                $detail = $this->pdo->prepare(
                    'INSERT INTO appointment_details
                        (appointment_id, service_id, service_name, price, quantity)
                     VALUES (:appointment_id, :service_id, :service_name, :price, 1)'
                );
                foreach ($services as $service) {
                    $detail->execute([
                        'appointment_id' => $appointmentId,
                        'service_id' => $service['id'],
                        'service_name' => $service['name'],
                        'price' => $service['price'],
                    ]);
                }
            }

            $this->pdo->commit();

            return $appointmentId;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function historyForCustomer(int $customerId, int $limit = 50): array
    {
        $statement = $this->pdo->prepare(
            '                        SELECT a.id, a.appointment_code, a.appointment_type, a.appointment_date,
                   a.appointment_time, a.status, a.created_at,
                    v.model, v.license_plate, b.name AS brand_name,
                   t.status AS maintenance_ticket_status,
                    COALESCE(details.service_count, 0) AS service_count,
                    COALESCE(details.reference_total, 0) AS reference_total
             FROM appointments AS a
             INNER JOIN vehicles AS v ON v.id = a.vehicle_id AND v.user_id = a.user_id
             INNER JOIN vehicle_brands AS b ON b.id = v.brand_id
             LEFT JOIN maintenance_tickets AS t ON t.appointment_id = a.id
             LEFT JOIN (
                 SELECT appointment_id, COUNT(*) AS service_count,
                        SUM(price * quantity) AS reference_total
                 FROM appointment_details
                 GROUP BY appointment_id
             ) AS details ON details.appointment_id = a.id
             WHERE a.user_id = :user_id
             ORDER BY a.created_at DESC, a.id DESC
             LIMIT :limit'
        );
        $statement->bindValue('user_id', $customerId, PDO::PARAM_INT);
        $statement->bindValue('limit', max(1, min($limit, 100)), PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findForCustomer(int $appointmentId, int $customerId): ?array
    {
        $statement = $this->pdo->prepare(
            "SELECT a.id, a.user_id, a.vehicle_id, a.appointment_code, a.appointment_type,
                   a.appointment_date, a.appointment_time, a.note, a.admin_note,
                   CASE WHEN a.status = 'rejected' THEN a.admin_note ELSE NULL END AS rejection_reason,
                   a.status, a.created_at, v.brand_id, b.name AS brand_name,
                   v.model, v.license_plate, v.manufacture_year,
                   t.id AS maintenance_ticket_id, t.status AS maintenance_ticket_status
             FROM appointments AS a
             INNER JOIN vehicles AS v ON v.id = a.vehicle_id AND v.user_id = a.user_id
             INNER JOIN vehicle_brands AS b ON b.id = v.brand_id
             LEFT JOIN maintenance_tickets AS t ON t.appointment_id = a.id
             WHERE a.id = :appointment_id AND a.user_id = :user_id
             LIMIT 1"
        );
        $statement->execute(['appointment_id' => $appointmentId, 'user_id' => $customerId]);
        $appointment = $statement->fetch();

        return $appointment === false ? null : $appointment;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function details(int $appointmentId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT service_name, price, quantity
            FROM appointment_details
            WHERE appointment_id = :appointment_id
            ORDER BY id'
        );
        $statement->execute(['appointment_id' => $appointmentId]);

        return $statement->fetchAll();
    }

    /**
     * @param list<int> $serviceIds
     * @return list<array{id: int, name: string, price: string}>
     */
    private function activeServicesForUpdate(array $serviceIds): array
    {
        if ($serviceIds === []) {
            return [];
        }

        $placeholders = [];
        $parameters = [];
        foreach ($serviceIds as $index => $serviceId) {
            $placeholder = 'service_id_' . $index;
            $placeholders[] = ':' . $placeholder;
            $parameters[$placeholder] = $serviceId;
        }

        $statement = $this->pdo->prepare(
            "SELECT s.id, s.name, s.price
            FROM services AS s
            INNER JOIN service_categories AS c ON c.id = s.category_id AND c.status = 1
            WHERE s.status = 'active' AND s.id IN (" . implode(', ', $placeholders) . ')
            ORDER BY s.id FOR UPDATE'
        );
        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    private function findBySubmissionCode(int $customerId, string $appointmentCode): ?int
    {
        $statement = $this->pdo->prepare(
            'SELECT id FROM appointments
            WHERE user_id = :user_id AND appointment_code = :appointment_code
            LIMIT 1'
        );
        $statement->execute([
            'user_id' => $customerId,
            'appointment_code' => $appointmentCode,
        ]);
        $id = $statement->fetchColumn();

        return $id === false ? null : (int) $id;
    }

    private function isFutureDateTime(string $date, string $time): bool
    {
        if (
            !preg_match('/\A\d{4}-\d{2}-\d{2}\z/', $date)
            || !preg_match('/\A(?:[01]\d|2[0-3]):[0-5]\d\z/', $time)
        ) {
            return false;
        }

        $timezone = new DateTimeZone(self::BOOKING_TIMEZONE);
        $requested = DateTimeImmutable::createFromFormat('!Y-m-d H:i', $date . ' ' . $time, $timezone);
        $errors = DateTimeImmutable::getLastErrors();

        return $requested !== false
            && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))
            && $requested->format('Y-m-d H:i') === $date . ' ' . $time
            && $requested > new DateTimeImmutable('now', $timezone);
    }
}
