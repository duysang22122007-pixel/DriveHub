-- Rollback: xoá cột cancellation_reason khỏi appointments
USE autocare_hub;

ALTER TABLE appointments DROP COLUMN cancellation_reason;
