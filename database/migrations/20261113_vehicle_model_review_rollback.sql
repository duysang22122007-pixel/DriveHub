-- Refuse rollback if any verification decision/audit data would be discarded.
DELIMITER //
CREATE PROCEDURE rollback_vehicle_model_review()
BEGIN
    IF EXISTS (
        SELECT 1 FROM vehicle_models
        WHERE reviewed_by IS NOT NULL
           OR reviewed_at IS NOT NULL
           OR verification_status IN ('verified', 'not_suitable')
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Rollback refused: vehicle-model review data exists.';
    END IF;

    ALTER TABLE vehicle_models
        DROP FOREIGN KEY fk_vehicle_models_reviewed_by,
        DROP INDEX idx_vehicle_models_review_queue,
        DROP COLUMN reviewed_by,
        DROP COLUMN reviewed_at;
END//
DELIMITER ;

CALL rollback_vehicle_model_review();
DROP PROCEDURE rollback_vehicle_model_review;
