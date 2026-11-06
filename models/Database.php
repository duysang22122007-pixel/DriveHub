<?php

class Database
{
    private static ?PDO $connection = null;

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            if (trim(DB_NAME) === '') {
                throw new RuntimeException('DB_NAME chưa được cấu hình trong configs/env.php.');
            }

            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                DB_HOST,
                DB_PORT,
                DB_NAME
            );

            self::$connection = new PDO($dsn, DB_USERNAME, DB_PASSWORD, DB_OPTIONS);
        }

        return self::$connection;
    }
}
