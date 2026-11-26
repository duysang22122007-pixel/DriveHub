<?php

declare(strict_types=1);

namespace App\Models;

use InvalidArgumentException;
use PDO;
use Throwable;

final class Invoice
{
    public const STATUS_LABELS = [
        'unpaid' => 'Chưa thanh toán',
        'partial' => 'Thanh toán một phần',
        'paid' => 'Đã thanh toán',
        'cancelled' => 'Đã hủy',
    ];

    public const PAYMENT_METHOD_LABELS = [
        'cash' => 'Tiền mặt',
        'bank_transfer' => 'Chuyển khoản ngân hàng',
        'online' => 'Thanh toán trực tuyến',
    ];

    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findByTicketId(int $ticketId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, ticket_id, invoice_code, subtotal, discount, total_amount, amount_paid,
                    status, created_at, updated_at
             FROM invoices
             WHERE ticket_id = :ticket_id
             LIMIT 1'
        );
        $stmt->execute(['ticket_id' => $ticketId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findByAppointmentId(int $appointmentId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT inv.id, inv.ticket_id, inv.invoice_code, inv.subtotal, inv.discount,
                    inv.total_amount, inv.amount_paid, inv.status, inv.created_at, inv.updated_at
             FROM invoices AS inv
             INNER JOIN maintenance_tickets AS t ON t.id = inv.ticket_id
             WHERE t.appointment_id = :appointment_id
             LIMIT 1'
        );
        $stmt->execute(['appointment_id' => $appointmentId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getDetails(int $invoiceId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, invoice_id, item_name, item_type, quantity, unit_price, total_price
             FROM invoice_details
             WHERE invoice_id = :invoice_id
             ORDER BY id ASC'
        );
        $stmt->execute(['invoice_id' => $invoiceId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getPayments(int $invoiceId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, invoice_id, payment_code, payment_method, provider, transaction_code,
                    amount, status, paid_at, created_at
             FROM payments
             WHERE invoice_id = :invoice_id
             ORDER BY created_at DESC, id DESC'
        );
        $stmt->execute(['invoice_id' => $invoiceId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Tạo mới hoặc đồng bộ hóa đơn chưa thanh toán từ các hạng mục của phiếu sửa chữa.
     */
    public function createOrSyncFromTicket(int $ticketId, float $discount = 0.0): int
    {
        if ($ticketId < 1) {
            throw new InvalidArgumentException('Phiếu sửa chữa không hợp lệ.');
        }
        if ($discount < 0) {
            throw new InvalidArgumentException('Giảm giá không được nhỏ hơn 0.');
        }

        $this->pdo->beginTransaction();
        try {
            $ticketStmt = $this->pdo->prepare(
                'SELECT t.id, t.appointment_id, t.status, t.ticket_code
                 FROM maintenance_tickets AS t
                 WHERE t.id = :id
                 LIMIT 1 FOR UPDATE'
            );
            $ticketStmt->execute(['id' => $ticketId]);
            $ticket = $ticketStmt->fetch(PDO::FETCH_ASSOC);

            if (!$ticket) {
                throw new InvalidArgumentException('Không tìm thấy phiếu sửa chữa.');
            }
            if ($ticket['status'] === 'cancelled') {
                throw new InvalidArgumentException('Không thể lập hóa đơn cho phiếu sửa chữa đã hủy.');
            }

            $itemsStmt = $this->pdo->prepare(
                "SELECT service_id, item_name, quantity, unit_price
                 FROM maintenance_details
                 WHERE ticket_id = :ticket_id AND status <> 'skipped'
                 ORDER BY id ASC"
            );
            $itemsStmt->execute(['ticket_id' => $ticketId]);
            $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

            if ($items === []) {
                $appItemsStmt = $this->pdo->prepare(
                    'SELECT service_id, service_name AS item_name, quantity, price AS unit_price
                     FROM appointment_details
                     WHERE appointment_id = :appointment_id
                     ORDER BY id ASC'
                );
                $appItemsStmt->execute(['appointment_id' => (int) $ticket['appointment_id']]);
                $items = $appItemsStmt->fetchAll(PDO::FETCH_ASSOC);
            }

            if ($items === []) {
                throw new InvalidArgumentException('Phiếu sửa chữa chưa có hạng mục hoặc dịch vụ nào để lập hóa đơn.');
            }

            $subtotal = 0.0;
            $normalizedItems = [];
            foreach ($items as $item) {
                $qty = max(1, (int) $item['quantity']);
                $unitPrice = max(0.0, (float) $item['unit_price']);
                $lineTotal = round($qty * $unitPrice, 2);
                $subtotal += $lineTotal;
                $normalizedItems[] = [
                    'item_name' => (string) $item['item_name'],
                    'item_type' => $item['service_id'] !== null ? 'service' : 'part',
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'total_price' => $lineTotal,
                ];
            }

            $subtotal = round($subtotal, 2);
            $appliedDiscount = round(min($subtotal, $discount), 2);
            $totalAmount = round(max(0.0, $subtotal - $appliedDiscount), 2);

            $existingStmt = $this->pdo->prepare(
                'SELECT id, status, amount_paid FROM invoices WHERE ticket_id = :ticket_id LIMIT 1 FOR UPDATE'
            );
            $existingStmt->execute(['ticket_id' => $ticketId]);
            $existing = $existingStmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                if ($existing['status'] !== 'unpaid' || (float) $existing['amount_paid'] > 0) {
                    throw new InvalidArgumentException('Hóa đơn đã phát sinh thanh toán, không thể đồng bộ lại hạng mục.');
                }

                $invoiceId = (int) $existing['id'];
                $newStatus = $totalAmount <= 0 ? 'paid' : 'unpaid';
                $upd = $this->pdo->prepare(
                    'UPDATE invoices
                     SET subtotal = :subtotal, discount = :discount, total_amount = :total_amount, status = :status
                     WHERE id = :id'
                );
                $upd->execute([
                    'subtotal' => $subtotal,
                    'discount' => $appliedDiscount,
                    'total_amount' => $totalAmount,
                    'status' => $newStatus,
                    'id' => $invoiceId,
                ]);

                $this->pdo->prepare('DELETE FROM invoice_details WHERE invoice_id = :invoice_id')
                    ->execute(['invoice_id' => $invoiceId]);
            } else {
                $invoiceCode = 'HD-' . date('ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
                $newStatus = $totalAmount <= 0 ? 'paid' : 'unpaid';
                $ins = $this->pdo->prepare(
                    'INSERT INTO invoices (ticket_id, invoice_code, subtotal, discount, total_amount, amount_paid, status)
                     VALUES (:ticket_id, :invoice_code, :subtotal, :discount, :total_amount, 0.00, :status)'
                );
                $ins->execute([
                    'ticket_id' => $ticketId,
                    'invoice_code' => $invoiceCode,
                    'subtotal' => $subtotal,
                    'discount' => $appliedDiscount,
                    'total_amount' => $totalAmount,
                    'status' => $newStatus,
                ]);
                $invoiceId = (int) $this->pdo->lastInsertId();
            }

            $insDetail = $this->pdo->prepare(
                'INSERT INTO invoice_details (invoice_id, item_name, item_type, quantity, unit_price, total_price)
                 VALUES (:invoice_id, :item_name, :item_type, :quantity, :unit_price, :total_price)'
            );
            foreach ($normalizedItems as $line) {
                $insDetail->execute([
                    'invoice_id' => $invoiceId,
                    'item_name' => $line['item_name'],
                    'item_type' => $line['item_type'],
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'total_price' => $line['total_price'],
                ]);
            }

            if ($newStatus === 'paid') {
                $this->createReviewNotificationIfNeeded($ticketId);
            }

            $this->pdo->commit();

            return $invoiceId;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Ghi nhận thanh toán cho hóa đơn của phiếu sửa chữa.
     * Khi hóa đơn chuyển sang trạng thái 'paid', tự động gửi biểu mẫu đánh giá tới khách hàng.
     *
     * @return array{invoice_status: string, review_sent: bool}
     */
    public function recordPayment(
        int $ticketId,
        string $paymentMethod,
        float $amount,
        ?string $transactionCode = null
    ): array {
        if (!array_key_exists($paymentMethod, self::PAYMENT_METHOD_LABELS)) {
            throw new InvalidArgumentException('Phương thức thanh toán không hợp lệ.');
        }
        if ($amount <= 0) {
            throw new InvalidArgumentException('Số tiền thanh toán phải lớn hơn 0.');
        }

        $trimmedTxCode = $transactionCode !== null ? trim($transactionCode) : '';
        if (mb_strlen($trimmedTxCode, 'UTF-8') > 150) {
            throw new InvalidArgumentException('Mã giao dịch không được vượt quá 150 ký tự.');
        }

        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare(
                'SELECT id, total_amount, amount_paid, status
                 FROM invoices
                 WHERE ticket_id = :ticket_id
                 LIMIT 1 FOR UPDATE'
            );
            $stmt->execute(['ticket_id' => $ticketId]);
            $invoice = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$invoice) {
                throw new InvalidArgumentException('Vui lòng lập hóa đơn trước khi ghi nhận thanh toán.');
            }
            if ($invoice['status'] === 'cancelled') {
                throw new InvalidArgumentException('Hóa đơn đã hủy, không thể thanh toán.');
            }
            if ($invoice['status'] === 'paid') {
                throw new InvalidArgumentException('Hóa đơn này đã được thanh toán đủ.');
            }

            $invoiceId = (int) $invoice['id'];
            $totalAmount = (float) $invoice['total_amount'];
            $currentPaid = (float) $invoice['amount_paid'];
            $remaining = round(max(0.0, $totalAmount - $currentPaid), 2);
            $paidNow = round(min($amount, $remaining > 0 ? $remaining : $amount), 2);

            if ($paidNow <= 0) {
                throw new InvalidArgumentException('Số tiền còn lại cần thanh toán không hợp lệ.');
            }

            $paymentCode = 'TT-' . date('ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
            $insPay = $this->pdo->prepare(
                "INSERT INTO payments
                    (invoice_id, payment_code, payment_method, provider, transaction_code, amount, status, paid_at)
                 VALUES
                    (:invoice_id, :payment_code, :payment_method, :provider, :transaction_code, :amount, 'success', NOW())"
            );
            $insPay->execute([
                'invoice_id' => $invoiceId,
                'payment_code' => $paymentCode,
                'payment_method' => $paymentMethod,
                'provider' => $paymentMethod === 'cash' ? 'Gara' : ($paymentMethod === 'bank_transfer' ? 'Ngân hàng' : 'Online'),
                'transaction_code' => $trimmedTxCode !== '' ? $trimmedTxCode : null,
                'amount' => $paidNow,
            ]);

            $newAmountPaid = round($currentPaid + $paidNow, 2);
            $newStatus = $newAmountPaid >= $totalAmount ? 'paid' : 'partial';

            $updInv = $this->pdo->prepare(
                'UPDATE invoices SET amount_paid = :amount_paid, status = :status WHERE id = :id'
            );
            $updInv->execute([
                'amount_paid' => $newAmountPaid,
                'status' => $newStatus,
                'id' => $invoiceId,
            ]);

            $reviewSent = false;
            if ($newStatus === 'paid') {
                $reviewSent = $this->createReviewNotificationIfNeeded($ticketId);
            }

            $this->pdo->commit();

            return [
                'invoice_status' => $newStatus,
                'review_sent' => $reviewSent,
            ];
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Gửi hoặc gửi lại thông báo mời khách hàng điền biểu mẫu đánh giá sau thanh toán.
     */
    public function sendReviewReminder(int $ticketId): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT inv.status AS invoice_status, t.id AS ticket_id, t.appointment_id, t.ticket_code,
                    v.user_id, b.name AS brand_name, v.model, v.license_plate,
                    r.id AS review_id
             FROM maintenance_tickets AS t
             INNER JOIN vehicles AS v ON v.id = t.vehicle_id
             INNER JOIN vehicle_brands AS b ON b.id = v.brand_id
             LEFT JOIN invoices AS inv ON inv.ticket_id = t.id
             LEFT JOIN reviews AS r ON r.ticket_id = t.id
             WHERE t.id = :ticket_id
             LIMIT 1"
        );
        $stmt->execute(['ticket_id' => $ticketId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || $row['invoice_status'] !== 'paid') {
            throw new InvalidArgumentException('Chỉ gửi biểu mẫu đánh giá sau khi hóa đơn đã thanh toán đủ.');
        }
        if ($row['review_id'] !== null) {
            throw new InvalidArgumentException('Khách hàng đã hoàn tất đánh giá cho phiếu này.');
        }

        $ins = $this->pdo->prepare(
            'INSERT INTO notifications (user_id, title, content, type, is_read)
             VALUES (:user_id, :title, :content, :type, 0)'
        );
        $ins->execute([
            'user_id' => (int) $row['user_id'],
            'title' => 'Mời đánh giá dịch vụ phiếu ' . $row['ticket_code'],
            'content' => 'Cảm ơn quý khách đã thanh toán dịch vụ cho xe '
                . $row['brand_name'] . ' ' . $row['model'] . ' (' . $row['license_plate'] . '). '
                . 'Vui lòng mở chi tiết lịch hẹn #' . (int) $row['appointment_id'] . ' để gửi đánh giá trải nghiệm tại AutoCare Hub.',
            'type' => 'review_request:' . $ticketId,
        ]);

        return true;
    }

    public function reviewRequestSentAt(int $ticketId): ?string
    {
        $stmt = $this->pdo->prepare(
            'SELECT MAX(created_at) FROM notifications WHERE type = :type'
        );
        $stmt->execute(['type' => 'review_request:' . $ticketId]);
        $value = $stmt->fetchColumn();

        return is_string($value) && $value !== '' ? $value : null;
    }

    private function createReviewNotificationIfNeeded(int $ticketId): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT t.id, t.appointment_id, t.ticket_code, v.user_id, b.name AS brand_name, v.model, v.license_plate,
                    r.id AS review_id
             FROM maintenance_tickets AS t
             INNER JOIN vehicles AS v ON v.id = t.vehicle_id
             INNER JOIN vehicle_brands AS b ON b.id = v.brand_id
             LEFT JOIN reviews AS r ON r.ticket_id = t.id
             WHERE t.id = :ticket_id
             LIMIT 1'
        );
        $stmt->execute(['ticket_id' => $ticketId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || $row['review_id'] !== null) {
            return false;
        }

        $notifType = 'review_request:' . $ticketId;
        $existsStmt = $this->pdo->prepare(
            'SELECT 1 FROM notifications WHERE user_id = :user_id AND type = :type LIMIT 1'
        );
        $existsStmt->execute([
            'user_id' => (int) $row['user_id'],
            'type' => $notifType,
        ]);
        if ($existsStmt->fetchColumn() !== false) {
            return true;
        }

        $ins = $this->pdo->prepare(
            'INSERT INTO notifications (user_id, title, content, type, is_read)
             VALUES (:user_id, :title, :content, :type, 0)'
        );
        $ins->execute([
            'user_id' => (int) $row['user_id'],
            'title' => 'Thanh toán thành công · Mời bạn đánh giá dịch vụ (' . $row['ticket_code'] . ')',
            'content' => 'Hóa đơn sửa chữa xe ' . $row['brand_name'] . ' ' . $row['model']
                . ' (' . $row['license_plate'] . ') đã được thanh toán. Hãy dành ít phút đánh giá chất lượng phục vụ để AutoCare Hub phục vụ bạn tốt hơn.',
            'type' => $notifType,
        ]);

        return true;
    }
}
