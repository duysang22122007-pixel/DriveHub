<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class AdminDashboard
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @return array{
     *     customers: int,
     *     vehicles: int,
     *     pendingAppointments: int,
     *     activeTickets: int,
     *     revenue: float,
     *     appointmentStatuses: list<array<string, mixed>>,
     *     recentAppointments: list<array<string, mixed>>
     * }
     */
    public function overview(): array
    {
        return [
            'customers' => (int) $this->pdo->query(
                "SELECT COUNT(*) FROM users WHERE role = 'customer'"
            )->fetchColumn(),
            'vehicles' => (int) $this->pdo->query(
                'SELECT COUNT(*) FROM vehicles'
            )->fetchColumn(),
            'pendingAppointments' => (int) $this->pdo->query(
                "SELECT COUNT(*) FROM appointments WHERE status = 'pending'"
            )->fetchColumn(),
            'activeTickets' => (int) $this->pdo->query(
                "SELECT COUNT(*) FROM maintenance_tickets
                 WHERE status IN ('received', 'diagnosing', 'in_progress', 'waiting_parts', 'quality_check')"
            )->fetchColumn(),
            'revenue' => (float) $this->pdo->query(
                "SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'success'"
            )->fetchColumn(),
            'appointmentStatuses' => $this->pdo->query(
                "SELECT status, COUNT(*) AS total
                 FROM appointments
                 GROUP BY status
                 ORDER BY CASE status
                     WHEN 'pending' THEN 1
                     WHEN 'confirmed' THEN 2
                     WHEN 'checked_in' THEN 3
                     WHEN 'completed' THEN 4
                     WHEN 'rejected' THEN 5
                     WHEN 'cancelled' THEN 6
                     ELSE 7
                 END"
            )->fetchAll(),
            'recentAppointments' => $this->pdo->query(
                'SELECT a.id, a.appointment_code, a.appointment_date, a.appointment_time,
                        a.status, u.full_name, b.name AS brand_name, v.model, v.license_plate
                 FROM appointments AS a
                 INNER JOIN users AS u ON u.id = a.user_id
                 INNER JOIN vehicles AS v ON v.id = a.vehicle_id
                 INNER JOIN vehicle_brands AS b ON b.id = v.brand_id
                 ORDER BY a.created_at DESC, a.id DESC
                 LIMIT 8'
            )->fetchAll(),
        ];
    }
}
