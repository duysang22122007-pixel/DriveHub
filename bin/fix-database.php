<?php

declare(strict_types=1);

use App\Core\Database;
use App\Core\Environment;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This command must be run from the command line.\n");
    exit(1);
}

$basePath = dirname(__DIR__);
require_once $basePath . '/app/Helpers/functions.php';

spl_autoload_register(static function (string $class) use ($basePath): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $file = $basePath . '/app/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});

Environment::load($basePath . '/.env');
$databaseConfig = require $basePath . '/config/database.php';

try {
    echo "Connecting to database '{$databaseConfig['name']}'...\n";
    $pdo = Database::connection($databaseConfig);
    echo "Connected successfully!\n\n";

    // 1. Thêm cột image_path vào bảng vehicles
    echo "[1/4] Checking 'image_path' in table 'vehicles'...\n";
    $checkCol = $pdo->query("SHOW COLUMNS FROM `vehicles` LIKE 'image_path'")->fetch();
    if (!$checkCol) {
        $pdo->exec("ALTER TABLE `vehicles` ADD COLUMN `image_path` VARCHAR(255) NULL AFTER `vehicle_model_id`");
        echo " -> Added column 'image_path' to 'vehicles'.\n";
    } else {
        echo " -> Column 'image_path' already exists.\n";
    }

    // 2. Thêm cột cancellation_reason vào bảng appointments
    echo "[2/4] Checking 'cancellation_reason' in table 'appointments'...\n";
    $checkCol = $pdo->query("SHOW COLUMNS FROM `appointments` LIKE 'cancellation_reason'")->fetch();
    if (!$checkCol) {
        $pdo->exec("ALTER TABLE `appointments` ADD COLUMN `cancellation_reason` VARCHAR(500) NULL AFTER `admin_note`");
        echo " -> Added column 'cancellation_reason' to 'appointments'.\n";
    } else {
        echo " -> Column 'cancellation_reason' already exists.\n";
    }

    // 3. Tạo bảng password_resets
    echo "[3/4] Checking table 'password_resets'...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `password_resets` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_id` BIGINT UNSIGNED NOT NULL,
            `token` CHAR(64) NOT NULL,
            `expires_at` DATETIME NOT NULL,
            `used_at` DATETIME NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uq_password_resets_token` (`token`),
            KEY `idx_password_resets_user` (`user_id`),
            CONSTRAINT `fk_password_resets_user`
                FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
                ON UPDATE CASCADE ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
    ");
    echo " -> Table 'password_resets' is ready.\n";

    // 4. Tạo bảng garage_business_hours & garage_closed_dates
    echo "[4/4] Checking tables 'garage_business_hours' & 'garage_closed_dates'...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `garage_business_hours` (
            `weekday` TINYINT UNSIGNED NOT NULL,
            `is_open` TINYINT(1) NOT NULL DEFAULT 1,
            `opens_at` TIME NOT NULL,
            `closes_at` TIME NOT NULL,
            `slot_interval_minutes` SMALLINT UNSIGNED NOT NULL DEFAULT 30,
            `slot_capacity` SMALLINT UNSIGNED NOT NULL DEFAULT 1,
            `daily_capacity` SMALLINT UNSIGNED NOT NULL DEFAULT 15,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`weekday`),
            CONSTRAINT `chk_garage_business_hours_weekday` CHECK (`weekday` BETWEEN 1 AND 7),
            CONSTRAINT `chk_garage_business_hours_times` CHECK (`closes_at` > `opens_at`),
            CONSTRAINT `chk_garage_business_hours_interval` CHECK (`slot_interval_minutes` BETWEEN 5 AND 240),
            CONSTRAINT `chk_garage_business_hours_capacity` CHECK (`slot_capacity` > 0 AND `daily_capacity` > 0)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    $pdo->exec("
        INSERT IGNORE INTO `garage_business_hours`
            (`weekday`, `is_open`, `opens_at`, `closes_at`, `slot_interval_minutes`, `slot_capacity`, `daily_capacity`)
        VALUES
            (1, 1, '07:30:00', '22:00:00', 30, 1, 15),
            (2, 1, '07:30:00', '22:00:00', 30, 1, 15),
            (3, 1, '07:30:00', '22:00:00', 30, 1, 15),
            (4, 1, '07:30:00', '22:00:00', 30, 1, 15),
            (5, 1, '07:30:00', '22:00:00', 30, 1, 15),
            (6, 1, '07:30:00', '22:00:00', 30, 1, 15),
            (7, 1, '07:30:00', '22:00:00', 30, 1, 15);
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `garage_closed_dates` (
            `closed_date` DATE NOT NULL,
            `reason` VARCHAR(255) NULL,
            `created_by` BIGINT UNSIGNED NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`closed_date`),
            KEY `idx_garage_closed_dates_creator` (`created_by`),
            CONSTRAINT `fk_garage_closed_dates_creator` FOREIGN KEY (`created_by`)
                REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo " -> Tables 'garage_business_hours' and 'garage_closed_dates' are ready.\n";

    echo "\n============================================\n";
    echo "SUCCESS: Database '{$databaseConfig['name']}' has been updated completely!\n";
    echo "============================================\n";
} catch (Throwable $e) {
    fwrite(STDERR, "ERROR: " . $e->getMessage() . "\n");
    exit(1);
}
