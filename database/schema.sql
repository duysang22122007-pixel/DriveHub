CREATE DATABASE IF NOT EXISTS autocare_hub
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_0900_ai_ci;

USE autocare_hub;

SET NAMES utf8mb4 COLLATE utf8mb4_0900_ai_ci;
SET time_zone = '+00:00';

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    role ENUM('admin', 'customer') NOT NULL DEFAULT 'customer',
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(254) NOT NULL,
    phone VARCHAR(30) NULL,
    password_hash VARCHAR(255) NOT NULL,
    status ENUM('active', 'inactive', 'locked') NOT NULL DEFAULT 'active',
    email_verified_at DATETIME NULL,
    last_login_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email),
    UNIQUE KEY uq_users_phone (phone),
    KEY idx_users_role_status (role, status),
    CONSTRAINT chk_users_full_name CHECK (CHAR_LENGTH(TRIM(full_name)) > 0),
    CONSTRAINT chk_users_email CHECK (email LIKE '%_@_%._%')
) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS vehicle_brands (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_vehicle_brands_name (name),
    UNIQUE KEY uq_vehicle_brands_slug (slug),
    KEY idx_vehicle_brands_active_name (is_active, name),
    CONSTRAINT chk_vehicle_brands_name CHECK (CHAR_LENGTH(TRIM(name)) > 0)
) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS vehicles (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    owner_id BIGINT UNSIGNED NOT NULL,
    brand_id BIGINT UNSIGNED NOT NULL,
    model VARCHAR(120) NOT NULL,
    model_year YEAR NULL,
    license_plate VARCHAR(20) NOT NULL,
    vin VARCHAR(32) NULL,
    color VARCHAR(50) NULL,
    current_mileage INT UNSIGNED NOT NULL DEFAULT 0,
    notes TEXT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_vehicles_license_plate (license_plate),
    UNIQUE KEY uq_vehicles_vin (vin),
    UNIQUE KEY uq_vehicles_id_owner (id, owner_id),
    KEY idx_vehicles_owner_status (owner_id, status),
    KEY idx_vehicles_brand_model (brand_id, model),
    CONSTRAINT fk_vehicles_owner FOREIGN KEY (owner_id) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_vehicles_brand FOREIGN KEY (brand_id) REFERENCES vehicle_brands (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_vehicles_model CHECK (CHAR_LENGTH(TRIM(model)) > 0),
    CONSTRAINT chk_vehicles_license_plate CHECK (CHAR_LENGTH(TRIM(license_plate)) > 0)
) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS service_categories (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(140) NOT NULL,
    description TEXT NULL,
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_service_categories_name (name),
    UNIQUE KEY uq_service_categories_slug (slug),
    KEY idx_service_categories_active_order (is_active, sort_order),
    CONSTRAINT chk_service_categories_name CHECK (CHAR_LENGTH(TRIM(name)) > 0)
) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS services (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    category_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(160) NOT NULL,
    slug VARCHAR(180) NOT NULL,
    description TEXT NULL,
    base_price DECIMAL(12,2) UNSIGNED NOT NULL,
    estimated_duration_minutes SMALLINT UNSIGNED NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_services_slug (slug),
    UNIQUE KEY uq_services_category_name (category_id, name),
    KEY idx_services_category_active (category_id, is_active),
    CONSTRAINT fk_services_category FOREIGN KEY (category_id) REFERENCES service_categories (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_services_name CHECK (CHAR_LENGTH(TRIM(name)) > 0),
    CONSTRAINT chk_services_duration CHECK (estimated_duration_minutes > 0)
) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS appointments (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    customer_id BIGINT UNSIGNED NOT NULL,
    vehicle_id BIGINT UNSIGNED NOT NULL,
    scheduled_at DATETIME NOT NULL,
    status ENUM('pending', 'confirmed', 'rejected', 'checked_in', 'completed', 'cancelled')
        NOT NULL DEFAULT 'pending',
    customer_note TEXT NULL,
    admin_note TEXT NULL,
    cancellation_reason VARCHAR(500) NULL,
    active_slot DATETIME GENERATED ALWAYS AS (
        CASE
            WHEN status IN ('pending', 'confirmed', 'checked_in') THEN scheduled_at
            ELSE NULL
        END
    ) STORED,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_appointments_active_slot (active_slot),
    UNIQUE KEY uq_appointments_customer_vehicle (id, customer_id, vehicle_id),
    KEY idx_appointments_customer_date (customer_id, scheduled_at),
    KEY idx_appointments_vehicle_date (vehicle_id, scheduled_at),
    KEY idx_appointments_status_date (status, scheduled_at),
    CONSTRAINT fk_appointments_customer FOREIGN KEY (customer_id) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_appointments_customer_vehicle FOREIGN KEY (vehicle_id, customer_id)
        REFERENCES vehicles (id, owner_id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS appointment_details (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    appointment_id BIGINT UNSIGNED NOT NULL,
    service_id BIGINT UNSIGNED NOT NULL,
    service_name_snapshot VARCHAR(160) NOT NULL,
    unit_price_snapshot DECIMAL(12,2) UNSIGNED NOT NULL,
    duration_minutes_snapshot SMALLINT UNSIGNED NOT NULL,
    quantity SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    line_total DECIMAL(14,2) UNSIGNED GENERATED ALWAYS AS (
        unit_price_snapshot * quantity
    ) STORED,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_appointment_details_service (appointment_id, service_id),
    KEY idx_appointment_details_service (service_id),
    CONSTRAINT fk_appointment_details_appointment FOREIGN KEY (appointment_id) REFERENCES appointments (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_appointment_details_service FOREIGN KEY (service_id) REFERENCES services (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_appointment_details_name CHECK (CHAR_LENGTH(TRIM(service_name_snapshot)) > 0),
    CONSTRAINT chk_appointment_details_duration CHECK (duration_minutes_snapshot > 0),
    CONSTRAINT chk_appointment_details_quantity CHECK (quantity > 0)
) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS maintenance_tickets (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    appointment_id BIGINT UNSIGNED NOT NULL,
    customer_id BIGINT UNSIGNED NOT NULL,
    vehicle_id BIGINT UNSIGNED NOT NULL,
    ticket_number VARCHAR(40) NOT NULL,
    status ENUM(
        'received', 'diagnosing', 'in_progress', 'waiting_parts',
        'quality_check', 'completed', 'cancelled'
    ) NOT NULL DEFAULT 'received',
    check_in_mileage INT UNSIGNED NOT NULL DEFAULT 0,
    diagnosis TEXT NULL,
    internal_note TEXT NULL,
    received_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    started_at DATETIME NULL,
    completed_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_maintenance_tickets_appointment (appointment_id),
    UNIQUE KEY uq_maintenance_tickets_number (ticket_number),
    UNIQUE KEY uq_maintenance_tickets_id_customer (id, customer_id),
    KEY idx_maintenance_tickets_customer_status (customer_id, status),
    KEY idx_maintenance_tickets_vehicle_received (vehicle_id, received_at),
    KEY idx_maintenance_tickets_status_updated (status, updated_at),
    CONSTRAINT fk_maintenance_tickets_appointment FOREIGN KEY (appointment_id, customer_id, vehicle_id)
        REFERENCES appointments (id, customer_id, vehicle_id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_maintenance_tickets_customer FOREIGN KEY (customer_id) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_maintenance_tickets_vehicle_owner FOREIGN KEY (vehicle_id, customer_id)
        REFERENCES vehicles (id, owner_id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_maintenance_tickets_number CHECK (CHAR_LENGTH(TRIM(ticket_number)) > 0),
    CONSTRAINT chk_maintenance_tickets_completion CHECK (completed_at IS NULL OR completed_at >= received_at)
) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS maintenance_details (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    maintenance_ticket_id BIGINT UNSIGNED NOT NULL,
    service_id BIGINT UNSIGNED NULL,
    line_type ENUM('service', 'labor', 'part', 'other') NOT NULL DEFAULT 'service',
    description VARCHAR(255) NOT NULL,
    quantity DECIMAL(10,2) UNSIGNED NOT NULL DEFAULT 1.00,
    unit_price DECIMAL(12,2) UNSIGNED NOT NULL,
    line_total DECIMAL(14,2) UNSIGNED GENERATED ALWAYS AS (quantity * unit_price) STORED,
    work_status ENUM('pending', 'in_progress', 'completed', 'skipped') NOT NULL DEFAULT 'pending',
    notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_maintenance_details_ticket_status (maintenance_ticket_id, work_status),
    KEY idx_maintenance_details_service (service_id),
    CONSTRAINT fk_maintenance_details_ticket FOREIGN KEY (maintenance_ticket_id)
        REFERENCES maintenance_tickets (id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_maintenance_details_service FOREIGN KEY (service_id) REFERENCES services (id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT chk_maintenance_details_description CHECK (CHAR_LENGTH(TRIM(description)) > 0),
    CONSTRAINT chk_maintenance_details_quantity CHECK (quantity > 0)
) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS maintenance_histories (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    maintenance_ticket_id BIGINT UNSIGNED NOT NULL,
    changed_by BIGINT UNSIGNED NULL,
    previous_status ENUM(
        'received', 'diagnosing', 'in_progress', 'waiting_parts',
        'quality_check', 'completed', 'cancelled'
    ) NULL,
    new_status ENUM(
        'received', 'diagnosing', 'in_progress', 'waiting_parts',
        'quality_check', 'completed', 'cancelled'
    ) NOT NULL,
    note VARCHAR(1000) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_maintenance_histories_ticket_time (maintenance_ticket_id, created_at),
    KEY idx_maintenance_histories_user_time (changed_by, created_at),
    CONSTRAINT fk_maintenance_histories_ticket FOREIGN KEY (maintenance_ticket_id)
        REFERENCES maintenance_tickets (id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_maintenance_histories_user FOREIGN KEY (changed_by) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS invoices (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    maintenance_ticket_id BIGINT UNSIGNED NOT NULL,
    invoice_number VARCHAR(40) NOT NULL,
    status ENUM('unpaid', 'partial', 'paid', 'cancelled') NOT NULL DEFAULT 'unpaid',
    currency CHAR(3) NOT NULL DEFAULT 'VND',
    subtotal DECIMAL(14,2) UNSIGNED NOT NULL DEFAULT 0.00,
    tax_rate DECIMAL(5,2) UNSIGNED NOT NULL DEFAULT 0.00,
    tax_amount DECIMAL(14,2) UNSIGNED NOT NULL DEFAULT 0.00,
    total_amount DECIMAL(14,2) UNSIGNED NOT NULL DEFAULT 0.00,
    issued_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    due_at DATETIME NULL,
    note VARCHAR(1000) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_invoices_ticket (maintenance_ticket_id),
    UNIQUE KEY uq_invoices_number (invoice_number),
    KEY idx_invoices_status_issued (status, issued_at),
    CONSTRAINT fk_invoices_ticket FOREIGN KEY (maintenance_ticket_id)
        REFERENCES maintenance_tickets (id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_invoices_number CHECK (CHAR_LENGTH(TRIM(invoice_number)) > 0),
    CONSTRAINT chk_invoices_currency CHECK (currency = 'VND'),
    CONSTRAINT chk_invoices_tax CHECK (tax_rate = 0.00 AND tax_amount = 0.00),
    CONSTRAINT chk_invoices_total CHECK (total_amount = subtotal + tax_amount),
    CONSTRAINT chk_invoices_due CHECK (due_at IS NULL OR due_at >= issued_at)
) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS invoice_details (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    invoice_id BIGINT UNSIGNED NOT NULL,
    maintenance_detail_id BIGINT UNSIGNED NULL,
    description VARCHAR(255) NOT NULL,
    quantity DECIMAL(10,2) UNSIGNED NOT NULL DEFAULT 1.00,
    unit_price DECIMAL(12,2) UNSIGNED NOT NULL,
    line_total DECIMAL(14,2) UNSIGNED GENERATED ALWAYS AS (quantity * unit_price) STORED,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_invoice_details_invoice (invoice_id),
    KEY idx_invoice_details_maintenance_detail (maintenance_detail_id),
    CONSTRAINT fk_invoice_details_invoice FOREIGN KEY (invoice_id) REFERENCES invoices (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_invoice_details_maintenance_detail FOREIGN KEY (maintenance_detail_id)
        REFERENCES maintenance_details (id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT chk_invoice_details_description CHECK (CHAR_LENGTH(TRIM(description)) > 0),
    CONSTRAINT chk_invoice_details_quantity CHECK (quantity > 0)
) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS payments (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    invoice_id BIGINT UNSIGNED NOT NULL,
    transaction_ref VARCHAR(64) NOT NULL,
    provider_transaction_id VARCHAR(128) NULL,
    payment_method ENUM('cash', 'vnpay') NOT NULL,
    status ENUM('pending', 'succeeded', 'failed', 'cancelled', 'refunded') NOT NULL DEFAULT 'pending',
    amount DECIMAL(14,2) UNSIGNED NOT NULL,
    currency CHAR(3) NOT NULL DEFAULT 'VND',
    paid_at DATETIME NULL,
    failure_reason VARCHAR(500) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_payments_transaction_ref (transaction_ref),
    UNIQUE KEY uq_payments_provider_transaction (provider_transaction_id),
    KEY idx_payments_invoice_status (invoice_id, status),
    KEY idx_payments_method_created (payment_method, created_at),
    CONSTRAINT fk_payments_invoice FOREIGN KEY (invoice_id) REFERENCES invoices (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_payments_amount CHECK (amount > 0),
    CONSTRAINT chk_payments_currency CHECK (currency = 'VND'),
    CONSTRAINT chk_payments_paid_at CHECK (
        (status IN ('succeeded', 'refunded') AND paid_at IS NOT NULL)
        OR (status NOT IN ('succeeded', 'refunded') AND paid_at IS NULL)
    ),
    CONSTRAINT chk_payments_provider_reference CHECK (
        payment_method = 'cash' OR provider_transaction_id IS NULL OR provider_transaction_id <> ''
    )
) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS reviews (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    maintenance_ticket_id BIGINT UNSIGNED NOT NULL,
    customer_id BIGINT UNSIGNED NOT NULL,
    rating TINYINT UNSIGNED NOT NULL,
    comment TEXT NULL,
    status ENUM('pending', 'published', 'hidden') NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_reviews_ticket (maintenance_ticket_id),
    KEY idx_reviews_customer_created (customer_id, created_at),
    KEY idx_reviews_status_created (status, created_at),
    CONSTRAINT fk_reviews_ticket_customer FOREIGN KEY (maintenance_ticket_id, customer_id)
        REFERENCES maintenance_tickets (id, customer_id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_reviews_customer FOREIGN KEY (customer_id) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_reviews_rating CHECK (rating BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS notifications (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    type VARCHAR(60) NOT NULL,
    title VARCHAR(180) NOT NULL,
    message TEXT NOT NULL,
    read_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_notifications_user_read_created (user_id, read_at, created_at),
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT chk_notifications_title CHECK (CHAR_LENGTH(TRIM(title)) > 0)
) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS system_settings (
    setting_key VARCHAR(120) NOT NULL,
    setting_value TEXT NOT NULL,
    value_type ENUM('string', 'integer', 'decimal', 'boolean', 'json') NOT NULL DEFAULT 'string',
    is_public TINYINT(1) NOT NULL DEFAULT 0,
    updated_by BIGINT UNSIGNED NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (setting_key),
    KEY idx_system_settings_public (is_public),
    CONSTRAINT fk_system_settings_updated_by FOREIGN KEY (updated_by) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT chk_system_settings_key CHECK (CHAR_LENGTH(TRIM(setting_key)) > 0)
) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS payment_webhooks (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    payment_id BIGINT UNSIGNED NOT NULL,
    provider VARCHAR(40) NOT NULL,
    provider_event_id VARCHAR(128) NOT NULL,
    signature_valid TINYINT(1) NOT NULL,
    processing_status ENUM('received', 'processed', 'ignored', 'failed') NOT NULL DEFAULT 'received',
    payload JSON NOT NULL,
    payload_sha256 CHAR(64) NOT NULL,
    received_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    processed_at DATETIME NULL,
    processing_error VARCHAR(1000) NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_payment_webhooks_provider_event (provider, provider_event_id),
    KEY idx_payment_webhooks_payment_received (payment_id, received_at),
    KEY idx_payment_webhooks_status_received (processing_status, received_at),
    CONSTRAINT fk_payment_webhooks_payment FOREIGN KEY (payment_id) REFERENCES payments (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_payment_webhooks_provider CHECK (CHAR_LENGTH(TRIM(provider)) > 0),
    CONSTRAINT chk_payment_webhooks_event CHECK (CHAR_LENGTH(TRIM(provider_event_id)) > 0),
    CONSTRAINT chk_payment_webhooks_hash CHECK (payload_sha256 REGEXP '^[0-9a-f]{64}$')
) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT IGNORE INTO vehicle_brands (name, slug) VALUES
    ('Toyota', 'toyota'),
    ('Honda', 'honda'),
    ('Ford', 'ford'),
    ('Hyundai', 'hyundai'),
    ('Mazda', 'mazda');

INSERT IGNORE INTO service_categories (name, slug, description, sort_order) VALUES
    ('Bảo dưỡng định kỳ', 'bao-duong-dinh-ky', 'Các hạng mục bảo dưỡng theo thời gian và số km sử dụng.', 10),
    ('Động cơ và truyền động', 'dong-co-va-truyen-dong', 'Kiểm tra và bảo dưỡng động cơ, hộp số và hệ truyền động.', 20),
    ('Phanh và lốp', 'phanh-va-lop', 'Kiểm tra hệ thống phanh, lốp và cân bằng bánh xe.', 30),
    ('Điện và điều hòa', 'dien-va-dieu-hoa', 'Kiểm tra hệ thống điện, ắc quy và điều hòa không khí.', 40);

INSERT IGNORE INTO services (
    category_id, name, slug, description, base_price, estimated_duration_minutes
)
SELECT category.id, service.name, service.slug, service.description, service.base_price, service.duration_minutes
FROM (
    SELECT 'bao-duong-dinh-ky' AS category_slug, 'Thay dầu động cơ' AS name,
        'thay-dau-dong-co' AS slug, 'Thay dầu động cơ theo khuyến nghị của nhà sản xuất.' AS description,
        850000.00 AS base_price, 45 AS duration_minutes
    UNION ALL
    SELECT 'bao-duong-dinh-ky', 'Kiểm tra tổng quát 20 hạng mục',
        'kiem-tra-tong-quat-20-hang-muc', 'Kiểm tra các hệ thống an toàn và vận hành cơ bản.',
        450000.00, 60
    UNION ALL
    SELECT 'phanh-va-lop', 'Kiểm tra hệ thống phanh',
        'kiem-tra-he-thong-phanh', 'Kiểm tra má phanh, đĩa phanh và dầu phanh.',
        300000.00, 45
    UNION ALL
    SELECT 'dien-va-dieu-hoa', 'Kiểm tra điều hòa',
        'kiem-tra-dieu-hoa', 'Kiểm tra hiệu suất làm mát và các thành phần điều hòa.',
        350000.00, 45
) AS service
INNER JOIN service_categories AS category ON category.slug = service.category_slug;
