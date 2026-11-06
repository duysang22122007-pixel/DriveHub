USE drivehub;

CREATE TABLE IF NOT EXISTS APPOINTMENT_STATUS_HISTORY (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT UNSIGNED NOT NULL,
    status ENUM('pending', 'confirmed', 'checked_in', 'completed', 'cancelled') NOT NULL,
    changed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    changed_by_user_id INT UNSIGNED NULL,
    note VARCHAR(255) NULL,
    INDEX idx_appointment_status_history (appointment_id, changed_at),
    CONSTRAINT fk_appointment_status_history_appointment FOREIGN KEY (appointment_id) REFERENCES APPOINTMENTS(id),
    CONSTRAINT fk_appointment_status_history_user FOREIGN KEY (changed_by_user_id) REFERENCES USERS(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO APPOINTMENT_STATUS_HISTORY (appointment_id, status, changed_at, note)
SELECT a.id, a.status, NOW(), 'Bắt đầu theo dõi; các mốc trạng thái trước đây chưa được lưu.'
FROM APPOINTMENTS a
LEFT JOIN APPOINTMENT_STATUS_HISTORY h ON h.appointment_id = a.id
WHERE h.id IS NULL;
