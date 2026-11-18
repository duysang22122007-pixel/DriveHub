-- Store an optional uploaded image path on each vehicle record.
USE autocare_hub;
SET @database_name = DATABASE();

SET @ddl = IF(
    NOT EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = @database_name
          AND TABLE_NAME = 'vehicles'
          AND COLUMN_NAME = 'image_path'
    ),
    'ALTER TABLE vehicles ADD COLUMN image_path VARCHAR(255) NULL AFTER vehicle_model_id',
    'SELECT 1'
);
PREPARE migration_statement FROM @ddl;
EXECUTE migration_statement;
DEALLOCATE PREPARE migration_statement;
