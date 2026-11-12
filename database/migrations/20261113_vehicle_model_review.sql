-- Add reviewer audit fields without changing existing model data or status.
USE autocare_hub;
SET @database_name = DATABASE();

SET @ddl = IF(
    NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @database_name AND TABLE_NAME = 'vehicle_models' AND COLUMN_NAME = 'reviewed_by'),
    'ALTER TABLE vehicle_models ADD COLUMN reviewed_by BIGINT UNSIGNED NULL',
    'SELECT 1'
);
PREPARE migration_statement FROM @ddl;
EXECUTE migration_statement;
DEALLOCATE PREPARE migration_statement;

SET @ddl = IF(
    NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @database_name AND TABLE_NAME = 'vehicle_models' AND COLUMN_NAME = 'reviewed_at'),
    'ALTER TABLE vehicle_models ADD COLUMN reviewed_at DATETIME NULL',
    'SELECT 1'
);
PREPARE migration_statement FROM @ddl;
EXECUTE migration_statement;
DEALLOCATE PREPARE migration_statement;

SET @ddl = IF(
    NOT EXISTS (SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = @database_name AND TABLE_NAME = 'vehicle_models' AND INDEX_NAME = 'idx_vehicle_models_review_queue'),
    'ALTER TABLE vehicle_models ADD INDEX idx_vehicle_models_review_queue (data_source, verification_status, brand_id, id)',
    'SELECT 1'
);
PREPARE migration_statement FROM @ddl;
EXECUTE migration_statement;
DEALLOCATE PREPARE migration_statement;

SET @ddl = IF(
    NOT EXISTS (
        SELECT 1
        FROM information_schema.REFERENTIAL_CONSTRAINTS
        WHERE CAST(CONSTRAINT_SCHEMA AS BINARY) = CAST(@database_name AS BINARY)
          AND CAST(CONSTRAINT_NAME AS BINARY) = CAST('fk_vehicle_models_reviewed_by' AS BINARY)
    ),
    'ALTER TABLE vehicle_models ADD CONSTRAINT fk_vehicle_models_reviewed_by FOREIGN KEY (reviewed_by) REFERENCES users (id) ON DELETE SET NULL',
    'SELECT 1'
);
PREPARE migration_statement FROM @ddl;
EXECUTE migration_statement;
DEALLOCATE PREPARE migration_statement;
