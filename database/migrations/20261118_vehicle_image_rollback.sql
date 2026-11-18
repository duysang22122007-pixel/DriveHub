-- Remove the optional vehicle image path column.
USE autocare_hub;
SET @database_name = DATABASE();

SET @ddl = IF(
    EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = @database_name
          AND TABLE_NAME = 'vehicles'
          AND COLUMN_NAME = 'image_path'
    ),
    'ALTER TABLE vehicles DROP COLUMN image_path',
    'SELECT 1'
);
PREPARE migration_statement FROM @ddl;
EXECUTE migration_statement;
DEALLOCATE PREPARE migration_statement;
