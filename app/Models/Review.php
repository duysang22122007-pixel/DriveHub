<?php

declare(strict_types=1);

namespace App\Models;

use InvalidArgumentException;
use PDO;
use Throwable;

final class Review
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findByTicketId(int $ticketId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT r.id, r.user_id, r.ticket_id, r.rating, r.comment, r.status, r.created_at,
                    u.full_name AS customer_name
             FROM reviews AS r
             INNER JOIN users AS u ON u.id = r.user_id
             WHERE r.ticket_id = :ticket_id
             LIMIT 1'
        );
        $stmt->execute(['ticket_id' => $ticketId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findByAppointmentForCustomer(int $appointmentId, int $customerId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT r.id, r.user_id, r.ticket_id, r.rating, r.comment, r.status, r.created_at
             FROM reviews AS r
             INNER JOIN maintenance_tickets AS t ON t.id = r.ticket_id
             WHERE t.appointment_id = :appointment_id AND r.user_id = :user_id
             LIMIT 1'
        );
        $stmt->execute([
            'appointment_id' => $appointmentId,
            'user_id' => $customerId,
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }

    /**
     * Khách hàng gửi biểu mẫu đánh giá sau khi đã thanh toán hóa đơn.
     */
    public function submitForCustomer(
        int $appointmentId,
        int $customerId,
        int $rating,
        ?string $comment
    ): int {
        if ($appointmentId < 1 || $customerId < 1) {
            throw new InvalidArgumentException('Thông tin lịch hẹn không hợp lệ.');
        }
        if ($rating < 1 || $rating > 5) {
            throw new InvalidArgumentException('Vui lòng chọn số sao đánh giá từ 1 đến 5.');
        }

        $trimmedComment = $comment !== null ? trim($comment) : '';
        if ($trimmedComment !== '' && mb_strlen($trimmedComment, 'UTF-8') > 2000) {
            throw new InvalidArgumentException('Nhận xét đánh giá không được vượt quá 2.000 ký tự.');
        }

        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare(
                "SELECT a.id AS appointment_id, t.id AS ticket_id, inv.status AS invoice_status
                 FROM appointments AS a
                 INNER JOIN maintenance_tickets AS t ON t.appointment_id = a.id
                 INNER JOIN invoices AS inv ON inv.ticket_id = t.id
                 WHERE a.id = :appointment_id AND a.user_id = :user_id
                 LIMIT 1 FOR UPDATE"
            );
            $stmt->execute([
                'appointment_id' => $appointmentId,
                'user_id' => $customerId,
            ]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                throw new InvalidArgumentException('Chỉ có thể đánh giá sau khi xe đã có phiếu sửa chữa và hóa đơn.');
            }
            if ($row['invoice_status'] !== 'paid') {
                throw new InvalidArgumentException('Biểu mẫu đánh giá chỉ khả dụng sau khi hóa đơn đã thanh toán hoàn tất.');
            }

            $ticketId = (int) $row['ticket_id'];
            $existing = $this->pdo->prepare('SELECT id FROM reviews WHERE ticket_id = :ticket_id LIMIT 1');
            $existing->execute(['ticket_id' => $ticketId]);
            if ($existing->fetchColumn() !== false) {
                throw new InvalidArgumentException('Bạn đã gửi đánh giá cho dịch vụ này rồi.');
            }

            $ins = $this->pdo->prepare(
                "INSERT INTO reviews (user_id, ticket_id, rating, comment, status)
                 VALUES (:user_id, :ticket_id, :rating, :comment, 'visible')"
            );
            $ins->execute([
                'user_id' => $customerId,
                'ticket_id' => $ticketId,
                'rating' => $rating,
                'comment' => $trimmedComment !== '' ? $trimmedComment : null,
            ]);
            $reviewId = (int) $this->pdo->lastInsertId();

            $markRead = $this->pdo->prepare(
                'UPDATE notifications SET is_read = 1 WHERE user_id = :user_id AND type = :type'
            );
            $markRead->execute([
                'user_id' => $customerId,
                'type' => 'review_request:' . $ticketId,
            ]);

            $this->pdo->commit();

            return $reviewId;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Danh sách các lịch hẹn đã thanh toán nhưng khách hàng chưa gửi đánh giá.
     *
     * @return list<array<string, mixed>>
     */
    public function pendingForCustomer(int $customerId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT a.id AS appointment_id, a.appointment_code, t.id AS ticket_id, t.ticket_code,
                    inv.invoice_code, inv.total_amount, b.name AS brand_name, v.model, v.license_plate,
                    (SELECT MAX(n.created_at) FROM notifications AS n
                     WHERE n.user_id = a.user_id AND n.type = CONCAT('review_request:', t.id)) AS sent_at
             FROM appointments AS a
             INNER JOIN vehicles AS v ON v.id = a.vehicle_id AND v.user_id = a.user_id
             INNER JOIN vehicle_brands AS b ON b.id = v.brand_id
             INNER JOIN maintenance_tickets AS t ON t.appointment_id = a.id
             INNER JOIN invoices AS inv ON inv.ticket_id = t.id AND inv.status = 'paid'
             LEFT JOIN reviews AS r ON r.ticket_id = t.id
             WHERE a.user_id = :user_id AND r.id IS NULL
             ORDER BY inv.updated_at DESC, a.id DESC"
        );
        $stmt->execute(['user_id' => $customerId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Đổi trạng thái hiển thị của đánh giá (visible / hidden) phía Admin.
     */
    public function toggleVisibility(int $reviewId): ?string
    {
        if ($reviewId < 1) {
            return null;
        }

        $stmt = $this->pdo->prepare('SELECT status FROM reviews WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $reviewId]);
        $current = $stmt->fetchColumn();
        if ($current === false) {
            return null;
        }

        $next = $current === 'visible' ? 'hidden' : 'visible';
        $upd = $this->pdo->prepare('UPDATE reviews SET status = :status WHERE id = :id');
        $upd->execute(['status' => $next, 'id' => $reviewId]);

        return $next;
    }
}
