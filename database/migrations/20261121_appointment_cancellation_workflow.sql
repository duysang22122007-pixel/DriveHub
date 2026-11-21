-- Apply once after 20261117_appointment_cancellation_reason.sql.
-- Adds a separate request/decision lifecycle; existing appointment statuses stay unchanged.
USE autocare_hub;

ALTER TABLE appointments
    ADD COLUMN cancellation_status ENUM('none', 'requested', 'approved', 'rejected')
        NOT NULL DEFAULT 'none' AFTER cancellation_reason,
    ADD COLUMN cancellation_requested_at DATETIME NULL AFTER cancellation_status,
    ADD COLUMN cancellation_decided_by BIGINT UNSIGNED NULL AFTER cancellation_requested_at,
    ADD COLUMN cancellation_decided_at DATETIME NULL AFTER cancellation_decided_by,
    ADD KEY idx_appointments_cancellation_status (cancellation_status, cancellation_requested_at),
    ADD CONSTRAINT fk_appointments_cancellation_decider
        FOREIGN KEY (cancellation_decided_by) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE SET NULL;

CREATE TABLE appointment_cancellation_events (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    appointment_id BIGINT UNSIGNED NOT NULL,
    actor_id BIGINT UNSIGNED NULL,
    action ENUM('requested', 'approved', 'rejected') NOT NULL,
    reason VARCHAR(500) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_appointment_cancellation_events_appointment (appointment_id, created_at),
    KEY idx_appointment_cancellation_events_actor (actor_id, created_at),
    CONSTRAINT fk_appointment_cancellation_events_appointment
        FOREIGN KEY (appointment_id) REFERENCES appointments (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_appointment_cancellation_events_actor
        FOREIGN KEY (actor_id) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
