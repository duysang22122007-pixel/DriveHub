-- Add booking type without changing existing appointment records or statuses.
USE autocare_hub;
SET @database_name = DATABASE();

SET @ddl = IF(
    NOT EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = @database_name
          AND TABLE_NAME = 'appointments'
          AND COLUMN_NAME = 'appointment_type'
    ),
    'ALTER TABLE appointments
        ADD COLUMN appointment_type ENUM(''onsite'', ''online'')
        NOT NULL DEFAULT ''onsite'' AFTER appointment_code',
    'SELECT 1'
);
PREPARE migration_statement FROM @ddl;
EXECUTE migration_statement;
DEALLOCATE PREPARE migration_statement;
