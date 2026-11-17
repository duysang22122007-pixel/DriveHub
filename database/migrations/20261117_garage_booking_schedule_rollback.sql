-- Refuse rollback if Admin-configured weekly hours or closure data would be lost.
DELIMITER //
CREATE PROCEDURE rollback_garage_booking_schedule()
BEGIN
    IF EXISTS (
        SELECT 1
        FROM garage_closed_dates
    ) OR EXISTS (
        SELECT 1
        FROM garage_business_hours
        WHERE is_open <> 1
           OR opens_at <> '07:30:00'
           OR closes_at <> '22:00:00'
           OR slot_interval_minutes <> 30
           OR slot_capacity <> 1
           OR daily_capacity <> 15
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Rollback refused: configured garage schedule data exists.';
    END IF;

    DROP TABLE garage_closed_dates;
    DROP TABLE garage_business_hours;
END//
DELIMITER ;

CALL rollback_garage_booking_schedule();
DROP PROCEDURE rollback_garage_booking_schedule;
