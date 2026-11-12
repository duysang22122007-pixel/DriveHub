-- Refuse rollback if online booking records would lose their type.
DELIMITER //
CREATE PROCEDURE rollback_appointment_booking_type()
BEGIN
    IF EXISTS (
        SELECT 1 FROM appointments WHERE appointment_type = 'online'
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Rollback refused: online appointment records exist.';
    END IF;

    ALTER TABLE appointments DROP COLUMN appointment_type;
END//
DELIMITER ;

CALL rollback_appointment_booking_type();
DROP PROCEDURE rollback_appointment_booking_type;
