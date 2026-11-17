-- Add configurable garage hours and booking capacity without altering appointments.
USE autocare_hub;

CREATE TABLE IF NOT EXISTS garage_business_hours (
    weekday TINYINT UNSIGNED NOT NULL,
    is_open TINYINT(1) NOT NULL DEFAULT 1,
    opens_at TIME NOT NULL,
    closes_at TIME NOT NULL,
    slot_interval_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 30,
    slot_capacity SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    daily_capacity SMALLINT UNSIGNED NOT NULL DEFAULT 15,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (weekday),
    CONSTRAINT chk_garage_business_hours_weekday CHECK (weekday BETWEEN 1 AND 7),
    CONSTRAINT chk_garage_business_hours_times CHECK (closes_at > opens_at),
    CONSTRAINT chk_garage_business_hours_interval CHECK (slot_interval_minutes BETWEEN 5 AND 240),
    CONSTRAINT chk_garage_business_hours_capacity CHECK (slot_capacity > 0 AND daily_capacity > 0)
) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO garage_business_hours
    (weekday, is_open, opens_at, closes_at, slot_interval_minutes, slot_capacity, daily_capacity)
VALUES
    (1, 1, '07:30:00', '22:00:00', 30, 1, 15),
    (2, 1, '07:30:00', '22:00:00', 30, 1, 15),
    (3, 1, '07:30:00', '22:00:00', 30, 1, 15),
    (4, 1, '07:30:00', '22:00:00', 30, 1, 15),
    (5, 1, '07:30:00', '22:00:00', 30, 1, 15),
    (6, 1, '07:30:00', '22:00:00', 30, 1, 15),
    (7, 1, '07:30:00', '22:00:00', 30, 1, 15);

CREATE TABLE IF NOT EXISTS garage_closed_dates (
    closed_date DATE NOT NULL,
    reason VARCHAR(255) NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (closed_date),
    KEY idx_garage_closed_dates_creator (created_by),
    CONSTRAINT fk_garage_closed_dates_creator FOREIGN KEY (created_by)
        REFERENCES users (id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
