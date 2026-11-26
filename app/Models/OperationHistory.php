<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class OperationHistory
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * Thống kê tổng quan cho phần Lịch sử đã hoàn thành trong Vận hành.
     *
     * @return array{
     *     completed_count: int,
     *     paid_count: int,
     *     pending_payment_count: int,
     *     total_revenue: float,
     *     review_count: int,
     *     avg_rating: float
     * }
     */
    public function summary(): array
    {
        $completedCount = (int) $this->pdo->query(
            "SELECT COUNT(*)
             FROM appointments AS a
             LEFT JOIN maintenance_tickets AS t ON t.appointment_id = a.id
             WHERE a.status = 'completed' OR t.status = 'completed'"
        )->fetchColumn();

        $paidCount = (int) $this->pdo->query(
            "SELECT COUNT(*)
             FROM appointments AS a
             INNER JOIN maintenance_tickets AS t ON t.appointment_id = a.id
             INNER JOIN invoices AS inv ON inv.ticket_id = t.id
             WHERE (a.status = 'completed' OR t.status = 'completed')
               AND inv.status = 'paid'"
        )->fetchColumn();

        $totalRevenue = (float) $this->pdo->query(
            "SELECT COALESCE(SUM(p.amount), 0)
             FROM payments AS p
             INNER JOIN invoices AS inv ON inv.id = p.invoice_id
             INNER JOIN maintenance_tickets AS t ON t.id = inv.ticket_id
             INNER JOIN appointments AS a ON a.id = t.appointment_id
             WHERE p.status = 'success'
               AND (a.status = 'completed' OR t.status = 'completed')"
        )->fetchColumn();

        $reviewRow = $this->pdo->query(
            'SELECT COUNT(*) AS cnt, COALESCE(AVG(rating), 0) AS avg_rating FROM reviews'
        )->fetch(PDO::FETCH_ASSOC);

        return [
            'completed_count' => $completedCount,
            'paid_count' => $paidCount,
            'pending_payment_count' => max(0, $completedCount - $paidCount),
            'total_revenue' => $totalRevenue,
            'review_count' => (int) ($reviewRow['cnt'] ?? 0),
            'avg_rating' => round((float) ($reviewRow['avg_rating'] ?? 0), 1),
        ];
    }

    /**
     * @return array{items: list<array<string, mixed>>, total: int}
     */
    public function adminPage(
        string $keyword = '',
        ?string $fromDate = null,
        ?string $toDate = null,
        string $paymentStatus = '',
        string $reviewStatus = '',
        int $page = 1,
        int $pageSize = 20
    ): array {
        $pageSize = max(1, min($pageSize, 100));
        $conditions = ["(a.status = 'completed' OR t.status = 'completed')"];
        $parameters = [];

        $trimmed = trim($keyword);
        if ($trimmed !== '') {
            $conditions[] = '(a.appointment_code LIKE :kw_app
                OR t.ticket_code LIKE :kw_ticket
                OR inv.invoice_code LIKE :kw_inv
                OR u.full_name LIKE :kw_name
                OR u.phone LIKE :kw_phone
                OR v.license_plate LIKE :kw_plate)';
            $pattern = '%' . $trimmed . '%';
            $parameters['kw_app'] = $pattern;
            $parameters['kw_ticket'] = $pattern;
            $parameters['kw_inv'] = $pattern;
            $parameters['kw_name'] = $pattern;
            $parameters['kw_phone'] = $pattern;
            $parameters['kw_plate'] = $pattern;
        }

        if ($fromDate !== null && $fromDate !== '') {
            $conditions[] = 'DATE(COALESCE(t.completed_at, a.updated_at, a.appointment_date)) >= :from_date';
            $parameters['from_date'] = $fromDate;
        }
        if ($toDate !== null && $toDate !== '') {
            $conditions[] = 'DATE(COALESCE(t.completed_at, a.updated_at, a.appointment_date)) <= :to_date';
            $parameters['to_date'] = $toDate;
        }

        if ($paymentStatus === 'paid') {
            $conditions[] = "inv.status = 'paid'";
        } elseif ($paymentStatus === 'partial') {
            $conditions[] = "inv.status = 'partial'";
        } elseif ($paymentStatus === 'unpaid') {
            $conditions[] = "(inv.id IS NULL OR inv.status = 'unpaid')";
        }

        if ($reviewStatus === 'reviewed') {
            $conditions[] = 'r.id IS NOT NULL';
        } elseif ($reviewStatus === 'sent') {
            $conditions[] = "r.id IS NULL AND EXISTS (
                SELECT 1 FROM notifications AS n
                WHERE n.user_id = a.user_id AND n.type = CONCAT('review_request:', t.id)
            )";
        } elseif ($reviewStatus === 'not_reviewed') {
            $conditions[] = 'r.id IS NULL';
        }

        $where = ' WHERE ' . implode(' AND ', $conditions);
        $joins = ' FROM appointments AS a
                   INNER JOIN users AS u ON u.id = a.user_id
                   INNER JOIN vehicles AS v ON v.id = a.vehicle_id
                   INNER JOIN vehicle_brands AS b ON b.id = v.brand_id
                   LEFT JOIN maintenance_tickets AS t ON t.appointment_id = a.id
                   LEFT JOIN invoices AS inv ON inv.ticket_id = t.id
                   LEFT JOIN reviews AS r ON r.ticket_id = t.id';

        $countStmt = $this->pdo->prepare('SELECT COUNT(*)' . $joins . $where);
        $countStmt->execute($parameters);
        $total = (int) $countStmt->fetchColumn();

        $lastPage = max(1, (int) ceil($total / $pageSize));
        $effectivePage = min(max(1, $page), $lastPage);
        $offset = ($effectivePage - 1) * $pageSize;

        $sql = "SELECT a.id AS appointment_id, a.appointment_code, a.appointment_date, a.appointment_time,
                       a.status AS appointment_status, a.updated_at AS appointment_updated_at,
                       u.id AS customer_id, u.full_name AS customer_name, u.phone AS customer_phone,
                       b.name AS brand_name, v.model, v.license_plate,
                       t.id AS ticket_id, t.ticket_code, t.status AS ticket_status, t.mileage,
                       t.received_at, t.completed_at, t.result AS ticket_result,
                       COALESCE((SELECT SUM(md.quantity * md.unit_price) FROM maintenance_details AS md WHERE md.ticket_id = t.id AND md.status <> 'skipped'), 0) AS ticket_estimated_total,
                       inv.id AS invoice_id, inv.invoice_code, inv.total_amount, inv.amount_paid,
                       inv.status AS invoice_status, inv.updated_at AS invoice_updated_at,
                       r.id AS review_id, r.rating, r.comment AS review_comment, r.status AS review_status, r.created_at AS reviewed_at,
                       (SELECT MAX(n.created_at) FROM notifications AS n
                        WHERE n.user_id = a.user_id AND n.type = CONCAT('review_request:', t.id)) AS review_sent_at
                {$joins}
                {$where}
                ORDER BY COALESCE(t.completed_at, a.updated_at, a.created_at) DESC, a.id DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        foreach ($parameters as $name => $value) {
            $stmt->bindValue($name, $value, PDO::PARAM_STR);
        }
        $stmt->bindValue('limit', $pageSize, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'items' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total' => $total,
        ];
    }
}
