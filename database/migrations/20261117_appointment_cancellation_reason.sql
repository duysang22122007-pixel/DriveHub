-- Migration: thêm cột cancellation_reason vào bảng appointments
USE autocare_hub;

ALTER TABLE appointments
    ADD COLUMN cancellation_reason VARCHAR(500) NULL
        AFTER admin_note;
