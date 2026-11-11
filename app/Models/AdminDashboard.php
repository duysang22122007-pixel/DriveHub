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
     * @return array{customers: int, vehicles: int, active_brands: int, recent_customers: int}
     */
    public function summary(string $since): array
    {
        $statement = $this->pdo->prepare(
            "SELECT
                (SELECT COUNT(*) FROM users WHERE role = 'customer') AS customers,
                (SELECT COUNT(*) FROM vehicles) AS vehicles,
                (SELECT COUNT(*) FROM vehicle_brands WHERE status = 1) AS active_brands,
                (SELECT COUNT(*) FROM users WHERE role = 'customer' AND created_at >= :since) AS recent_customers"
        );
        $statement->execute(['since' => $since]);
        $summary = $statement->fetch();

        if ($summary === false) {
            throw new \RuntimeException('Unable to load admin dashboard summary.');
        }

        return [
            'customers' => (int) $summary['customers'],
            'vehicles' => (int) $summary['vehicles'],
            'active_brands' => (int) $summary['active_brands'],
            'recent_customers' => (int) $summary['recent_customers'],
        ];
    }

    /**
     * @return array<string, array{customers: int, vehicles: int}>
     */
    public function activitySince(string $since): array
    {
        $statement = $this->pdo->prepare(
            "SELECT activity_date, SUM(new_customers) AS customers, SUM(new_vehicles) AS vehicles
             FROM (
                SELECT DATE(created_at) AS activity_date, COUNT(*) AS new_customers, 0 AS new_vehicles
                FROM users
                WHERE role = 'customer' AND created_at >= ?
                GROUP BY DATE(created_at)
                UNION ALL
                SELECT DATE(created_at) AS activity_date, 0 AS new_customers, COUNT(*) AS new_vehicles
                FROM vehicles
                WHERE created_at >= ?
                GROUP BY DATE(created_at)
             ) AS daily_activity
             GROUP BY activity_date
             ORDER BY activity_date"
        );
        $statement->execute([$since, $since]);

        $activity = [];
        foreach ($statement->fetchAll() as $row) {
            $activity[(string) $row['activity_date']] = [
                'customers' => (int) $row['customers'],
                'vehicles' => (int) $row['vehicles'],
            ];
        }

        return $activity;
    }
}
