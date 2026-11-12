-- Guarded rollback. This refuses to remove metadata if NHTSA records or import
-- history exist, so that source/progress data is never discarded implicitly.
DELIMITER //
CREATE PROCEDURE rollback_vehicle_catalog_import()
BEGIN
    IF EXISTS (SELECT 1 FROM vehicle_brands WHERE source_make_id IS NOT NULL)
       OR EXISTS (SELECT 1 FROM vehicle_models WHERE source_make_id IS NOT NULL OR source_model_id IS NOT NULL)
       OR EXISTS (SELECT 1 FROM vehicle_import_sessions)
       OR EXISTS (SELECT 1 FROM vehicle_import_errors) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Rollback refused: NHTSA catalog data or import history exists.';
    END IF;

    DROP TABLE vehicle_import_errors;
    DROP TABLE vehicle_import_sessions;
    ALTER TABLE vehicle_models
        DROP INDEX uq_vehicle_models_source_ids_only,
        DROP COLUMN verification_status,
        DROP COLUMN source_model_id,
        DROP COLUMN source_make_id,
        DROP COLUMN data_source;
    ALTER TABLE vehicle_brands
        DROP INDEX uq_vehicle_brands_source_make_id_only,
        DROP COLUMN verification_status,
        DROP COLUMN source_make_id,
        DROP COLUMN data_source;
END//
DELIMITER ;

CALL rollback_vehicle_catalog_import();
DROP PROCEDURE rollback_vehicle_catalog_import;
