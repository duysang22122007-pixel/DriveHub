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
     * @return array{customers: int, vehicles: int, pendingAppointments: int, activeTickets: int, revenue: float}
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
        ];
    }
}
