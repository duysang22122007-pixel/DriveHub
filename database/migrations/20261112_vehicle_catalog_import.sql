-- Additive NHTSA import metadata and resumable import history.
-- Run against autocare_hub only after verifying the target database and backup.
USE autocare_hub;
SET @database_name = DATABASE();

SET @ddl = IF(
    NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @database_name AND TABLE_NAME = 'vehicle_brands' AND COLUMN_NAME = 'data_source'),
    'ALTER TABLE vehicle_brands ADD COLUMN data_source VARCHAR(16) NOT NULL DEFAULT ''admin''',
    'SELECT 1'
);
PREPARE migration_statement FROM @ddl;
EXECUTE migration_statement;
DEALLOCATE PREPARE migration_statement;

SET @ddl = IF(
    NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @database_name AND TABLE_NAME = 'vehicle_brands' AND COLUMN_NAME = 'source_make_id'),
    'ALTER TABLE vehicle_brands ADD COLUMN source_make_id INT UNSIGNED NULL',
    'SELECT 1'
);
PREPARE migration_statement FROM @ddl;
EXECUTE migration_statement;
DEALLOCATE PREPARE migration_statement;

SET @ddl = IF(
    NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @database_name AND TABLE_NAME = 'vehicle_brands' AND COLUMN_NAME = 'verification_status'),
    'ALTER TABLE vehicle_brands ADD COLUMN verification_status VARCHAR(20) NOT NULL DEFAULT ''unverified''',
    'SELECT 1'
);
PREPARE migration_statement FROM @ddl;
EXECUTE migration_statement;
DEALLOCATE PREPARE migration_statement;

SET @ddl = IF(
    EXISTS (SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = @database_name AND TABLE_NAME = 'vehicle_brands' AND INDEX_NAME = 'uq_vehicle_brands_source_make_id'),
    'ALTER TABLE vehicle_brands DROP INDEX uq_vehicle_brands_source_make_id',
    'SELECT 1'
);
PREPARE migration_statement FROM @ddl;
EXECUTE migration_statement;
DEALLOCATE PREPARE migration_statement;

SET @ddl = IF(
    NOT EXISTS (SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = @database_name AND TABLE_NAME = 'vehicle_brands' AND INDEX_NAME = 'uq_vehicle_brands_source_make_id_only'),
    'ALTER TABLE vehicle_brands ADD UNIQUE INDEX uq_vehicle_brands_source_make_id_only (source_make_id)',
    'SELECT 1'
);
PREPARE migration_statement FROM @ddl;
EXECUTE migration_statement;
DEALLOCATE PREPARE migration_statement;

SET @ddl = IF(
    NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @database_name AND TABLE_NAME = 'vehicle_models' AND COLUMN_NAME = 'data_source'),
    'ALTER TABLE vehicle_models ADD COLUMN data_source VARCHAR(16) NOT NULL DEFAULT ''admin''',
    'SELECT 1'
);
PREPARE migration_statement FROM @ddl;
EXECUTE migration_statement;
DEALLOCATE PREPARE migration_statement;

SET @ddl = IF(
    NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @database_name AND TABLE_NAME = 'vehicle_models' AND COLUMN_NAME = 'source_make_id'),
    'ALTER TABLE vehicle_models ADD COLUMN source_make_id INT UNSIGNED NULL',
    'SELECT 1'
);
PREPARE migration_statement FROM @ddl;
EXECUTE migration_statement;
DEALLOCATE PREPARE migration_statement;

SET @ddl = IF(
    NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @database_name AND TABLE_NAME = 'vehicle_models' AND COLUMN_NAME = 'source_model_id'),
    'ALTER TABLE vehicle_models ADD COLUMN source_model_id INT UNSIGNED NULL',
    'SELECT 1'
);
PREPARE migration_statement FROM @ddl;
EXECUTE migration_statement;
DEALLOCATE PREPARE migration_statement;

SET @ddl = IF(
    NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @database_name AND TABLE_NAME = 'vehicle_models' AND COLUMN_NAME = 'verification_status'),
    'ALTER TABLE vehicle_models ADD COLUMN verification_status VARCHAR(20) NOT NULL DEFAULT ''unverified''',
    'SELECT 1'
);
PREPARE migration_statement FROM @ddl;
EXECUTE migration_statement;
DEALLOCATE PREPARE migration_statement;

SET @ddl = IF(
    EXISTS (SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = @database_name AND TABLE_NAME = 'vehicle_models' AND INDEX_NAME = 'uq_vehicle_models_source_ids'),
    'ALTER TABLE vehicle_models DROP INDEX uq_vehicle_models_source_ids',
    'SELECT 1'
);
PREPARE migration_statement FROM @ddl;
EXECUTE migration_statement;
DEALLOCATE PREPARE migration_statement;

SET @ddl = IF(
    NOT EXISTS (SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = @database_name AND TABLE_NAME = 'vehicle_models' AND INDEX_NAME = 'uq_vehicle_models_source_ids_only'),
    'ALTER TABLE vehicle_models ADD UNIQUE INDEX uq_vehicle_models_source_ids_only (source_make_id, source_model_id)',
    'SELECT 1'
);
PREPARE migration_statement FROM @ddl;
EXECUTE migration_statement;
DEALLOCATE PREPARE migration_statement;

CREATE TABLE IF NOT EXISTS vehicle_import_sessions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    mode ENUM('brands', 'models', 'all') NOT NULL,
    status ENUM('queued', 'running', 'completed', 'completed_with_errors', 'failed') NOT NULL DEFAULT 'queued',
    makes_payload MEDIUMTEXT NOT NULL,
    next_cursor INT UNSIGNED NOT NULL DEFAULT 0,
    makes_total INT UNSIGNED NOT NULL DEFAULT 0,
    makes_processed INT UNSIGNED NOT NULL DEFAULT 0,
    added_count INT UNSIGNED NOT NULL DEFAULT 0,
    duplicate_count INT UNSIGNED NOT NULL DEFAULT 0,
    error_count INT UNSIGNED NOT NULL DEFAULT 0,
    last_error TEXT NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    INDEX idx_vehicle_import_sessions_status_created (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS vehicle_import_errors (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    session_id BIGINT UNSIGNED NOT NULL,
    stage VARCHAR(16) NOT NULL,
    source_make_id INT UNSIGNED NULL,
    source_model_id INT UNSIGNED NULL,
    error_message VARCHAR(500) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_vehicle_import_errors_session (session_id, id),
    CONSTRAINT fk_vehicle_import_errors_session
        FOREIGN KEY (session_id) REFERENCES vehicle_import_sessions (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
