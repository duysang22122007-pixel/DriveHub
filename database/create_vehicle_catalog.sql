USE autocare_hub;

CREATE TABLE IF NOT EXISTS vehicle_models (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    brand_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    year_from SMALLINT UNSIGNED NULL,
    year_to SMALLINT UNSIGNED NULL,
    image VARCHAR(255) NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_vehicle_models_brand_name (brand_id, name),
    KEY idx_vehicle_models_brand_status (brand_id, status),
    CONSTRAINT fk_vehicle_models_brand FOREIGN KEY (brand_id) REFERENCES vehicle_brands (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_vehicle_models_name CHECK (CHAR_LENGTH(TRIM(name)) > 0),
    CONSTRAINT chk_vehicle_models_year_range CHECK (
        year_from IS NULL OR year_to IS NULL OR year_from <= year_to
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

ALTER TABLE vehicles
    ADD COLUMN vehicle_model_id BIGINT UNSIGNED NULL AFTER brand_id,
    ADD KEY idx_vehicles_vehicle_model (vehicle_model_id),
    ADD CONSTRAINT fk_vehicles_vehicle_model FOREIGN KEY (vehicle_model_id)
        REFERENCES vehicle_models (id) ON UPDATE CASCADE ON DELETE RESTRICT;
