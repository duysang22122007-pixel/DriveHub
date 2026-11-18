<?php

declare(strict_types=1);

namespace App\Models;

use DateTimeImmutable;
use InvalidArgumentException;
use PDO;
use Throwable;

final class MaintenanceTicket
{
    public const STATUS_LABELS = [
        'received' => 'Đã tiếp nhận xe',
        'diagnosing' => 'Đang chẩn đoán',
        'in_progress' => 'Đang sửa chữa',
        'waiting_parts' => 'Chờ phụ tùng',
        'quality_check' => 'Đang kiểm tra chất lượng',
        'completed' => 'Đã hoàn thành',
        'cancelled' => 'Đã hủy',
    ];

    public const ITEM_STATUS_LABELS = [
        'pending' => 'Chờ xử lý',
        'in_progress' => 'Đang thực hiện',
        'completed' => 'Đã hoàn tất',
        'skipped' => 'Bỏ qua',
    ];

    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @return array{items: list<array<string, mixed>>, total: int}
     */
    public function adminPage(
        string $keyword = '',
        ?string $status = null,
        ?string $date = null,
        int $page = 1,
        int $perPage = 20
    ): array {
        $whereClauses = ['1=1'];
        $parameters = [];

        if ($status !== null && array_key_exists($status, self::STATUS_LABELS)) {
            $whereClauses[] = 't.status = :status';
            $parameters['status'] = $status;
        }

        if ($date !== null && $date !== '') {
            $whereClauses[] = 'DATE(t.received_at) = :received_date';
            $parameters['received_date'] = $date;
        }

        $trimmed = trim($keyword);
        if ($trimmed !== '') {
            $whereClauses[] = '(t.ticket_code LIKE :kw_code OR v.license_plate LIKE :kw_plate OR u.full_name LIKE :kw_name OR u.phone LIKE :kw_phone)';
            $kw = '%' . $trimmed . '%';
            $parameters['kw_code'] = $kw;
            $parameters['kw_plate'] = $kw;
            $parameters['kw_name'] = $kw;
            $parameters['kw_phone'] = $kw;
        }

        $where = ' WHERE ' . implode(' AND ', $whereClauses);

        $countStmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM maintenance_tickets AS t
             INNER JOIN vehicles AS v ON v.id = t.vehicle_id
             INNER JOIN users AS u ON u.id = v.user_id' . $where
        );
        $countStmt->execute($parameters);
        $total = (int) $countStmt->fetchColumn();

        $offset = max(0, ($page - 1) * $perPage);
        $sql = "SELECT t.id, t.ticket_code, t.status, t.mileage, t.received_at, t.completed_at,
                       v.id AS vehicle_id, v.model, v.license_plate,
                       b.name AS brand_name,
                       u.id AS customer_id, u.full_name AS customer_name, u.phone AS customer_phone,
                       adm.full_name AS admin_name,
                       COALESCE((SELECT SUM(d.quantity * d.unit_price) FROM maintenance_details d WHERE d.ticket_id = t.id), 0) AS total_estimated_cost,
                       (SELECT COUNT(*) FROM maintenance_details d WHERE d.ticket_id = t.id) AS total_items
                FROM maintenance_tickets AS t
                INNER JOIN vehicles AS v ON v.id = t.vehicle_id
                INNER JOIN vehicle_brands AS b ON b.id = v.brand_id
                INNER JOIN users AS u ON u.id = v.user_id
                LEFT JOIN users AS adm ON adm.id = t.admin_id
                {$where}
                ORDER BY t.created_at DESC, t.id DESC
                LIMIT {$perPage} OFFSET {$offset}";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($parameters);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'items' => $items,
            'total' => $total,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT t.*,
                    v.model, v.license_plate, v.color, v.manufacture_year, v.current_mileage AS vehicle_current_mileage,
                    b.name AS brand_name,
                    u.id AS customer_id, u.full_name AS customer_name, u.phone AS customer_phone, u.email AS customer_email,
                    adm.full_name AS admin_name,
                    a.appointment_code, a.appointment_date, a.appointment_time, a.note AS appointment_note
             FROM maintenance_tickets AS t
             INNER JOIN vehicles AS v ON v.id = t.vehicle_id
             INNER JOIN vehicle_brands AS b ON b.id = v.brand_id
             INNER JOIN users AS u ON u.id = v.user_id
             LEFT JOIN users AS adm ON adm.id = t.admin_id
             LEFT JOIN appointments AS a ON a.id = t.appointment_id
             WHERE t.id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findByAppointmentId(int $appointmentId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, ticket_code, status, mileage, received_at, completed_at
             FROM maintenance_tickets
             WHERE appointment_id = :appointment_id
             LIMIT 1'
        );
        $stmt->execute(['appointment_id' => $appointmentId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getItems(int $ticketId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, ticket_id, service_id, item_name, description, quantity, unit_price,
                    status, note, (quantity * unit_price) AS line_total
             FROM maintenance_details
             WHERE ticket_id = :ticket_id
             ORDER BY id ASC'
        );
        $stmt->execute(['ticket_id' => $ticketId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getHistories(int $ticketId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT h.*, u.full_name AS admin_name
             FROM maintenance_histories AS h
             LEFT JOIN users AS u ON u.id = h.admin_id
             WHERE h.ticket_id = :ticket_id
             ORDER BY h.created_at DESC, h.id DESC'
        );
        $stmt->execute(['ticket_id' => $ticketId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Tạo phiếu sửa chữa mới
     *
     * @param array{
     *     appointment_id: int,
     *     admin_id: int,
     *     mileage?: int,
     *     initial_condition?: string|null,
     *     diagnosis?: string|null
     * } $data
     */
    public function createTicket(array $data): int
    {
        $appointmentId = (int) $data['appointment_id'];
        $adminId = (int) $data['admin_id'];
        $mileage = max(0, (int) ($data['mileage'] ?? 0));
        $initialCondition = isset($data['initial_condition']) && trim((string) $data['initial_condition']) !== ''
            ? trim((string) $data['initial_condition'])
            : null;
        $diagnosis = isset($data['diagnosis']) && trim((string) $data['diagnosis']) !== ''
            ? trim((string) $data['diagnosis'])
            : null;

        $this->pdo->beginTransaction();
        try {
            // 1. Kiểm tra lịch hẹn tồn tại
            $appStmt = $this->pdo->prepare(
                'SELECT a.id, a.vehicle_id, a.status, a.note, v.current_mileage
                 FROM appointments AS a
                 INNER JOIN vehicles AS v ON v.id = a.vehicle_id
                 WHERE a.id = :id
                 LIMIT 1 FOR UPDATE'
            );
            $appStmt->execute(['id' => $appointmentId]);
            $appointment = $appStmt->fetch(PDO::FETCH_ASSOC);

            if (!$appointment) {
                throw new InvalidArgumentException('Không tìm thấy lịch hẹn tương ứng.');
            }

            // Kiểm tra xem lịch hẹn đã có phiếu sửa chữa chưa
            $checkStmt = $this->pdo->prepare(
                'SELECT id, ticket_code FROM maintenance_tickets WHERE appointment_id = :id LIMIT 1'
            );
            $checkStmt->execute(['id' => $appointmentId]);
            $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
            if ($existing) {
                throw new InvalidArgumentException('Lịch hẹn này đã có phiếu sửa chữa: ' . $existing['ticket_code']);
            }

            $vehicleId = (int) $appointment['vehicle_id'];
            if ($mileage === 0 && (int) $appointment['current_mileage'] > 0) {
                $mileage = (int) $appointment['current_mileage'];
            }

            if ($initialCondition === null && !empty($appointment['note'])) {
                $initialCondition = (string) $appointment['note'];
            }

            // 2. Tạo mã phiếu ngẫu nhiên không trùng (SC-YYMMDD-XXXX)
            $ticketCode = 'SC-' . date('ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));

            // 3. Insert phiếu sửa chữa
            $insertTicket = $this->pdo->prepare(
                'INSERT INTO maintenance_tickets
                    (appointment_id, vehicle_id, admin_id, ticket_code, mileage, initial_condition, diagnosis, status, received_at)
                 VALUES
                    (:appointment_id, :vehicle_id, :admin_id, :ticket_code, :mileage, :initial_condition, :diagnosis, :status, NOW())'
            );
            $insertTicket->execute([
                'appointment_id' => $appointmentId,
                'vehicle_id' => $vehicleId,
                'admin_id' => $adminId,
                'ticket_code' => $ticketCode,
                'mileage' => $mileage,
                'initial_condition' => $initialCondition,
                'diagnosis' => $diagnosis,
                'status' => 'received',
            ]);
            $ticketId = (int) $this->pdo->lastInsertId();

            // 4. Cập nhật số km của xe nếu km tiếp nhận lớn hơn
            if ($mileage > 0) {
                $updVeh = $this->pdo->prepare(
                    'UPDATE vehicles SET current_mileage = :mileage WHERE id = :id AND current_mileage < :mileage'
                );
                $updVeh->execute(['mileage' => $mileage, 'id' => $vehicleId]);
            }

            // 5. Cập nhật trạng thái lịch hẹn sang checked_in nếu đang pending hoặc confirmed
            $updApp = $this->pdo->prepare(
                "UPDATE appointments SET status = 'checked_in' WHERE id = :id AND status IN ('pending', 'confirmed')"
            );
            $updApp->execute(['id' => $appointmentId]);

            // 6. Sao chép các dịch vụ khách đã đặt từ appointment_details sang maintenance_details
            $servStmt = $this->pdo->prepare(
                'SELECT service_id, service_name, price, quantity FROM appointment_details WHERE appointment_id = :app_id'
            );
            $servStmt->execute(['app_id' => $appointmentId]);
            $bookedServices = $servStmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($bookedServices)) {
                $insItem = $this->pdo->prepare(
                    'INSERT INTO maintenance_details (ticket_id, service_id, item_name, quantity, unit_price, status)
                     VALUES (:ticket_id, :service_id, :item_name, :quantity, :unit_price, :status)'
                );
                foreach ($bookedServices as $serv) {
                    $insItem->execute([
                        'ticket_id' => $ticketId,
                        'service_id' => $serv['service_id'],
                        'item_name' => $serv['service_name'],
                        'quantity' => max(1, (int) $serv['quantity']),
                        'unit_price' => (float) $serv['price'],
                        'status' => 'pending',
                    ]);
                }
            }

            // 7. Ghi nhận lịch sử tiếp nhận
            $insHist = $this->pdo->prepare(
                'INSERT INTO maintenance_histories (ticket_id, admin_id, old_status, new_status, description, created_at)
                 VALUES (:ticket_id, :admin_id, NULL, :new_status, :description, NOW())'
            );
            $insHist->execute([
                'ticket_id' => $ticketId,
                'admin_id' => $adminId,
                'new_status' => 'received',
                'description' => 'Tiếp nhận xe tại gara và khởi tạo phiếu sửa chữa ' . $ticketCode . ' (Số km: ' . number_format($mileage, 0, ',', '.') . ' km)',
            ]);

            $this->pdo->commit();

            return $ticketId;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Cập nhật tình trạng sửa chữa & số km hoàn thành
     */
    public function updateStatus(
        int $ticketId,
        int $adminId,
        string $newStatus,
        string $description,
        ?int $completedMileage = null
    ): bool {
        if (!array_key_exists($newStatus, self::STATUS_LABELS)) {
            throw new InvalidArgumentException('Trạng thái sửa chữa không hợp lệ.');
        }

        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare(
                'SELECT id, appointment_id, vehicle_id, status, mileage, completed_at FROM maintenance_tickets WHERE id = :id LIMIT 1 FOR UPDATE'
            );
            $stmt->execute(['id' => $ticketId]);
            $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$ticket) {
                throw new InvalidArgumentException('Không tìm thấy phiếu sửa chữa.');
            }

            $oldStatus = (string) $ticket['status'];
            $vehicleId = (int) $ticket['vehicle_id'];
            $appointmentId = (int) $ticket['appointment_id'];

            if ($oldStatus === $newStatus) {
                $this->pdo->rollBack();

                return true;
            }

            // Chuẩn bị cập nhật ticket
            $completedAtSql = '';
            $params = [
                'status' => $newStatus,
                'id' => $ticketId,
            ];

            if ($newStatus === 'completed') {
                $completedAtSql = ', completed_at = NOW()';
                if ($completedMileage !== null && $completedMileage > 0) {
                    $completedAtSql .= ', mileage = :mileage';
                    $params['mileage'] = $completedMileage;

                    // Đồng bộ sang xe
                    $updVeh = $this->pdo->prepare(
                        'UPDATE vehicles SET current_mileage = :m WHERE id = :v_id'
                    );
                    $updVeh->execute(['m' => $completedMileage, 'v_id' => $vehicleId]);
                }

                // Cập nhật lịch hẹn tương ứng thành completed
                $updApp = $this->pdo->prepare(
                    "UPDATE appointments SET status = 'completed' WHERE id = :app_id AND status = 'checked_in'"
                );
                $updApp->execute(['app_id' => $appointmentId]);
            } elseif ($newStatus === 'cancelled') {
                // Nếu hủy phiếu sửa chữa
                $updApp = $this->pdo->prepare(
                    "UPDATE appointments SET status = 'cancelled' WHERE id = :app_id AND status = 'checked_in'"
                );
                $updApp->execute(['app_id' => $appointmentId]);
            }

            $updateTicket = $this->pdo->prepare(
                "UPDATE maintenance_tickets SET status = :status {$completedAtSql} WHERE id = :id"
            );
            $updateTicket->execute($params);

            // Ghi nhận lịch sử bảo dưỡng
            $statusLabel = self::STATUS_LABELS[$newStatus];
            $desc = trim($description) !== '' ? trim($description) : "Chuyển trạng thái sang: {$statusLabel}";
            if ($newStatus === 'completed' && $completedMileage !== null && $completedMileage > 0) {
                $desc .= " (Số km bàn giao: " . number_format($completedMileage, 0, ',', '.') . " km)";
            }

            $insHist = $this->pdo->prepare(
                'INSERT INTO maintenance_histories (ticket_id, admin_id, old_status, new_status, description, created_at)
                 VALUES (:ticket_id, :admin_id, :old_status, :new_status, :description, NOW())'
            );
            $insHist->execute([
                'ticket_id' => $ticketId,
                'admin_id' => $adminId,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'description' => $desc,
            ]);

            $this->pdo->commit();

            return true;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Cập nhật thông tin chẩn đoán lỗi & số km tiếp nhận
     */
    public function updateDiagnosisAndMileage(
        int $ticketId,
        int $mileage,
        ?string $initialCondition,
        ?string $diagnosis,
        ?string $result
    ): bool {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare(
                'SELECT id, vehicle_id FROM maintenance_tickets WHERE id = :id LIMIT 1 FOR UPDATE'
            );
            $stmt->execute(['id' => $ticketId]);
            $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$ticket) {
                throw new InvalidArgumentException('Không tìm thấy phiếu sửa chữa.');
            }

            $vehicleId = (int) $ticket['vehicle_id'];

            $upd = $this->pdo->prepare(
                'UPDATE maintenance_tickets
                 SET mileage = :mileage,
                     initial_condition = :initial_condition,
                     diagnosis = :diagnosis,
                     result = :result
                 WHERE id = :id'
            );
            $upd->execute([
                'mileage' => max(0, $mileage),
                'initial_condition' => ($initialCondition !== null && trim($initialCondition) !== '') ? trim($initialCondition) : null,
                'diagnosis' => ($diagnosis !== null && trim($diagnosis) !== '') ? trim($diagnosis) : null,
                'result' => ($result !== null && trim($result) !== '') ? trim($result) : null,
                'id' => $ticketId,
            ]);

            if ($mileage > 0) {
                $updVeh = $this->pdo->prepare(
                    'UPDATE vehicles SET current_mileage = :mileage WHERE id = :id AND current_mileage < :mileage'
                );
                $updVeh->execute(['mileage' => $mileage, 'id' => $vehicleId]);
            }

            $this->pdo->commit();

            return true;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Thêm hạng mục sửa chữa
     */
    public function addItem(
        int $ticketId,
        ?int $serviceId,
        string $itemName,
        ?string $description,
        int $quantity,
        float $unitPrice,
        ?string $note
    ): int {
        $name = trim($itemName);
        if ($name === '') {
            throw new InvalidArgumentException('Tên hạng mục sửa chữa không được để trống.');
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO maintenance_details
                (ticket_id, service_id, item_name, description, quantity, unit_price, status, note)
             VALUES
                (:ticket_id, :service_id, :item_name, :description, :quantity, :unit_price, :status, :note)'
        );
        $stmt->execute([
            'ticket_id' => $ticketId,
            'service_id' => ($serviceId !== null && $serviceId > 0) ? $serviceId : null,
            'item_name' => $name,
            'description' => ($description !== null && trim($description) !== '') ? trim($description) : null,
            'quantity' => max(1, $quantity),
            'unit_price' => max(0.0, $unitPrice),
            'status' => 'pending',
            'note' => ($note !== null && trim($note) !== '') ? trim($note) : null,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Cập nhật hạng mục sửa chữa
     */
    public function updateItem(
        int $itemId,
        int $ticketId,
        string $itemName,
        ?string $description,
        int $quantity,
        float $unitPrice,
        string $status,
        ?string $note
    ): bool {
        $name = trim($itemName);
        if ($name === '') {
            throw new InvalidArgumentException('Tên hạng mục sửa chữa không được để trống.');
        }
        if (!array_key_exists($status, self::ITEM_STATUS_LABELS)) {
            $status = 'pending';
        }

        $stmt = $this->pdo->prepare(
            'UPDATE maintenance_details
             SET item_name = :item_name,
                 description = :description,
                 quantity = :quantity,
                 unit_price = :unit_price,
                 status = :status,
                 note = :note
             WHERE id = :id AND ticket_id = :ticket_id'
        );

        return $stmt->execute([
            'item_name' => $name,
            'description' => ($description !== null && trim($description) !== '') ? trim($description) : null,
            'quantity' => max(1, $quantity),
            'unit_price' => max(0.0, $unitPrice),
            'status' => $status,
            'note' => ($note !== null && trim($note) !== '') ? trim($note) : null,
            'id' => $itemId,
            'ticket_id' => $ticketId,
        ]);
    }

    /**
     * Xóa hạng mục sửa chữa
     */
    public function deleteItem(int $itemId, int $ticketId): bool
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM maintenance_details WHERE id = :id AND ticket_id = :ticket_id'
        );

        return $stmt->execute(['id' => $itemId, 'ticket_id' => $ticketId]);
    }

    /**
     * Danh sách lịch hẹn đã xác nhận/tiếp nhận nhưng chưa có phiếu sửa chữa
     *
     * @return list<array<string, mixed>>
     */
    public function listAppointmentsReadyForTicket(): array
    {
        $stmt = $this->pdo->query(
            "SELECT a.id, a.appointment_code, a.appointment_date, a.appointment_time, a.status,
                    u.full_name, u.phone,
                    v.model, v.license_plate, v.current_mileage,
                    b.name AS brand_name
             FROM appointments AS a
             INNER JOIN users AS u ON u.id = a.user_id
             INNER JOIN vehicles AS v ON v.id = a.vehicle_id
             INNER JOIN vehicle_brands AS b ON b.id = v.brand_id
             LEFT JOIN maintenance_tickets AS t ON t.appointment_id = a.id
             WHERE t.id IS NULL
               AND a.status IN ('confirmed', 'checked_in')
             ORDER BY a.appointment_date ASC, a.appointment_time ASC"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
