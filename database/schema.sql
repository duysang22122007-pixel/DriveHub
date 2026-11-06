CREATE DATABASE IF NOT EXISTS drivehub
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE drivehub;

CREATE TABLE IF NOT EXISTS ROLES (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(30) NOT NULL UNIQUE
) ENGINE=InnoDB;

INSERT IGNORE INTO ROLES (name) VALUES ('customer'), ('admin');

CREATE TABLE IF NOT EXISTS USERS (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id INT UNSIGNED NOT NULL,
    username VARCHAR(80) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES ROLES(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS CUSTOMERS (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT NULL,
    CONSTRAINT fk_customers_user FOREIGN KEY (user_id) REFERENCES USERS(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS EMPLOYEES (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    position VARCHAR(80) NOT NULL DEFAULT 'Nhân viên',
    CONSTRAINT fk_employees_user FOREIGN KEY (user_id) REFERENCES USERS(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS CAR_BRANDS (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS CARS (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED NOT NULL,
    brand_id INT UNSIGNED NOT NULL,
    license_plate VARCHAR(20) NOT NULL UNIQUE,
    model VARCHAR(120) NOT NULL,
    manufacture_year SMALLINT UNSIGNED NULL,
    color VARCHAR(50) NULL,
    current_mileage INT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cars_customer FOREIGN KEY (customer_id) REFERENCES CUSTOMERS(id),
    CONSTRAINT fk_cars_brand FOREIGN KEY (brand_id) REFERENCES CAR_BRANDS(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS SERVICES (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_code VARCHAR(80) NOT NULL UNIQUE,
    service_group VARCHAR(30) NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    price DECIMAL(12,2) NOT NULL DEFAULT 0,
    status TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS APPOINTMENTS (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED NOT NULL,
    car_id INT UNSIGNED NOT NULL,
    service_id INT UNSIGNED NOT NULL,
    appointment_date DATETIME NOT NULL,
    description TEXT NOT NULL,
    status ENUM('pending', 'confirmed', 'checked_in', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_appointments_status_date (status, appointment_date),
    INDEX idx_appointments_customer_status (customer_id, status),
    CONSTRAINT fk_appointments_customer FOREIGN KEY (customer_id) REFERENCES CUSTOMERS(id),
    CONSTRAINT fk_appointments_car FOREIGN KEY (car_id) REFERENCES CARS(id),
    CONSTRAINT fk_appointments_service FOREIGN KEY (service_id) REFERENCES SERVICES(id)
) ENGINE=InnoDB;

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

CREATE TABLE IF NOT EXISTS REPAIR_ORDERS (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT UNSIGNED NULL UNIQUE,
    car_id INT UNSIGNED NOT NULL,
    employee_id INT UNSIGNED NOT NULL,
    received_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    received_mileage INT UNSIGNED NOT NULL DEFAULT 0,
    completed_mileage INT UNSIGNED NULL,
    problem_description TEXT NOT NULL,
    diagnosis TEXT NULL,
    status ENUM('received', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'received',
    completed_at DATETIME NULL,
    CONSTRAINT fk_repair_orders_appointment FOREIGN KEY (appointment_id) REFERENCES APPOINTMENTS(id),
    CONSTRAINT fk_repair_orders_car FOREIGN KEY (car_id) REFERENCES CARS(id),
    CONSTRAINT fk_repair_orders_employee FOREIGN KEY (employee_id) REFERENCES EMPLOYEES(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS REPAIR_DETAILS (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    repair_order_id INT UNSIGNED NOT NULL,
    service_id INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 1,
    unit_price DECIMAL(12,2) NOT NULL DEFAULT 0,
    note TEXT NULL,
    CONSTRAINT fk_repair_details_order FOREIGN KEY (repair_order_id) REFERENCES REPAIR_ORDERS(id),
    CONSTRAINT fk_repair_details_service FOREIGN KEY (service_id) REFERENCES SERVICES(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS MAINTENANCE_HISTORY (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    car_id INT UNSIGNED NOT NULL,
    repair_order_id INT UNSIGNED NOT NULL UNIQUE,
    maintenance_date DATETIME NOT NULL,
    mileage INT UNSIGNED NOT NULL,
    description TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_maintenance_car_date (car_id, maintenance_date),
    CONSTRAINT fk_maintenance_car FOREIGN KEY (car_id) REFERENCES CARS(id),
    CONSTRAINT fk_maintenance_order FOREIGN KEY (repair_order_id) REFERENCES REPAIR_ORDERS(id)
) ENGINE=InnoDB;

INSERT INTO SERVICES (service_code, service_group, name, description)
VALUES
    ('bao_duong_dinh_ky', 'maintenance', 'Bảo dưỡng định kỳ', 'Bảo dưỡng định kỳ và chăm sóc xe'),
    ('thay_dau_dong_co', 'maintenance', 'Thay dầu động cơ', 'Thay dầu động cơ'),
    ('kiem_tra_cham_soc_lop', 'maintenance', 'Kiểm tra và chăm sóc lốp', 'Kiểm tra và chăm sóc lốp'),
    ('kiem_tra_thay_ac_quy', 'maintenance', 'Kiểm tra, thay ắc quy', 'Kiểm tra, thay ắc quy'),
    ('kiem_tra_he_thong_phanh', 'maintenance', 'Kiểm tra hệ thống phanh', 'Kiểm tra hệ thống phanh'),
    ('kiem_tra_dieu_hoa', 'maintenance', 'Kiểm tra điều hòa', 'Kiểm tra điều hòa'),
    ('sua_chua_he_thong_phanh', 'repair', 'Sửa chữa hệ thống phanh', 'Sửa chữa hệ thống phanh'),
    ('sua_chua_dong_co', 'repair', 'Sửa chữa động cơ', 'Sửa chữa động cơ'),
    ('sua_chua_dieu_hoa', 'repair', 'Sửa chữa điều hòa', 'Sửa chữa điều hòa'),
    ('sua_chua_he_thong_dien', 'repair', 'Sửa chữa hệ thống điện', 'Sửa chữa hệ thống điện'),
    ('kiem_tra_tieng_on', 'repair', 'Kiểm tra tiếng ồn bất thường', 'Kiểm tra tiếng ồn bất thường'),
    ('sua_chua_khac', 'repair', 'Sửa chữa khác', 'Sửa chữa khác'),
    ('kiem_tra_tong_quat', 'diagnosis', 'Kiểm tra tổng quát', 'Kiểm tra tổng quát'),
    ('tu_van_chan_doan', 'diagnosis', 'Tư vấn và chẩn đoán tình trạng xe', 'Tư vấn và chẩn đoán tình trạng xe')
ON DUPLICATE KEY UPDATE
    service_group = VALUES(service_group),
    name = VALUES(name),
    description = VALUES(description);
