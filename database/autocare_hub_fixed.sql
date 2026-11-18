-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 18, 2026
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `autocare_hub`
--
CREATE DATABASE IF NOT EXISTS `autocare_hub` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `autocare_hub`;

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `vehicle_id` bigint UNSIGNED NOT NULL,
  `appointment_code` varchar(30) NOT NULL,
  `appointment_type` enum('onsite','online') NOT NULL DEFAULT 'onsite',
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `note` text,
  `admin_note` text,
  `cancellation_reason` varchar(500) DEFAULT NULL,
  `status` enum('pending','confirmed','rejected','checked_in','completed','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `user_id`, `vehicle_id`, `appointment_code`, `appointment_type`, `appointment_date`, `appointment_time`, `note`, `admin_note`, `cancellation_reason`, `status`, `created_at`, `updated_at`) VALUES
(1, 3, 4, 'ACB7BF310DF9DDEC2DB01C9214', 'online', '2026-11-21', '11:28:00', 'Tình trạng xe: wtwtw4\n\nGhi chú thêm: www52642hdjtrgk', NULL, NULL, 'confirmed', '2026-11-12 04:25:18', '2026-11-12 05:05:10');

-- --------------------------------------------------------

--
-- Table structure for table `appointment_details`
--

CREATE TABLE `appointment_details` (
  `id` bigint UNSIGNED NOT NULL,
  `appointment_id` bigint UNSIGNED NOT NULL,
  `service_id` bigint UNSIGNED NOT NULL,
  `service_name` varchar(150) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `quantity` int UNSIGNED NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `garage_business_hours`
--

CREATE TABLE IF NOT EXISTS `garage_business_hours` (
  `weekday` tinyint UNSIGNED NOT NULL,
  `is_open` tinyint(1) NOT NULL DEFAULT '1',
  `opens_at` time NOT NULL,
  `closes_at` time NOT NULL,
  `slot_interval_minutes` smallint UNSIGNED NOT NULL DEFAULT '30',
  `slot_capacity` smallint UNSIGNED NOT NULL DEFAULT '1',
  `daily_capacity` smallint UNSIGNED NOT NULL DEFAULT '15',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`weekday`),
  CONSTRAINT `chk_garage_business_hours_weekday` CHECK ((`weekday` between 1 and 7)),
  CONSTRAINT `chk_garage_business_hours_times` CHECK ((`closes_at` > `opens_at`)),
  CONSTRAINT `chk_garage_business_hours_interval` CHECK ((`slot_interval_minutes` between 5 and 240)),
  CONSTRAINT `chk_garage_business_hours_capacity` CHECK (((`slot_capacity` > 0) and (`daily_capacity` > 0)))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `garage_business_hours` (`weekday`, `is_open`, `opens_at`, `closes_at`, `slot_interval_minutes`, `slot_capacity`, `daily_capacity`) VALUES
(1, 1, '07:30:00', '22:00:00', 30, 1, 15),
(2, 1, '07:30:00', '22:00:00', 30, 1, 15),
(3, 1, '07:30:00', '22:00:00', 30, 1, 15),
(4, 1, '07:30:00', '22:00:00', 30, 1, 15),
(5, 1, '07:30:00', '22:00:00', 30, 1, 15),
(6, 1, '07:30:00', '22:00:00', 30, 1, 15),
(7, 1, '07:30:00', '22:00:00', 30, 1, 15);

-- --------------------------------------------------------

--
-- Table structure for table `garage_closed_dates`
--

CREATE TABLE IF NOT EXISTS `garage_closed_dates` (
  `closed_date` date NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`closed_date`),
  KEY `idx_garage_closed_dates_creator` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` bigint UNSIGNED NOT NULL,
  `ticket_id` bigint UNSIGNED NOT NULL,
  `invoice_code` varchar(30) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL DEFAULT '0.00',
  `discount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `amount_paid` decimal(12,2) NOT NULL DEFAULT '0.00',
  `status` enum('unpaid','partial','paid','cancelled') NOT NULL DEFAULT 'unpaid',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_details`
--

CREATE TABLE `invoice_details` (
  `id` bigint UNSIGNED NOT NULL,
  `invoice_id` bigint UNSIGNED NOT NULL,
  `item_name` varchar(150) NOT NULL,
  `item_type` enum('service','part','other') NOT NULL DEFAULT 'service',
  `quantity` int UNSIGNED NOT NULL DEFAULT '1',
  `unit_price` decimal(12,2) NOT NULL,
  `total_price` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_details`
--

CREATE TABLE `maintenance_details` (
  `id` bigint UNSIGNED NOT NULL,
  `ticket_id` bigint UNSIGNED NOT NULL,
  `service_id` bigint UNSIGNED DEFAULT NULL,
  `item_name` varchar(150) NOT NULL,
  `description` text,
  `quantity` int UNSIGNED NOT NULL DEFAULT '1',
  `unit_price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `status` enum('pending','in_progress','completed','skipped') NOT NULL DEFAULT 'pending',
  `note` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_histories`
--

CREATE TABLE `maintenance_histories` (
  `id` bigint UNSIGNED NOT NULL,
  `ticket_id` bigint UNSIGNED NOT NULL,
  `admin_id` bigint UNSIGNED NOT NULL,
  `old_status` varchar(30) DEFAULT NULL,
  `new_status` varchar(30) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_tickets`
--

CREATE TABLE `maintenance_tickets` (
  `id` bigint UNSIGNED NOT NULL,
  `appointment_id` bigint UNSIGNED NOT NULL,
  `vehicle_id` bigint UNSIGNED NOT NULL,
  `admin_id` bigint UNSIGNED NOT NULL,
  `ticket_code` varchar(30) NOT NULL,
  `mileage` int UNSIGNED NOT NULL DEFAULT '0',
  `initial_condition` text,
  `diagnosis` text,
  `result` text,
  `status` enum('received','diagnosing','in_progress','waiting_parts','quality_check','completed','cancelled') NOT NULL DEFAULT 'received',
  `received_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `completed_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `title` varchar(150) NOT NULL,
  `content` text NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `token` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_password_resets_token` (`token`),
  KEY `idx_password_resets_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint UNSIGNED NOT NULL,
  `invoice_id` bigint UNSIGNED NOT NULL,
  `payment_code` varchar(50) NOT NULL,
  `payment_method` enum('cash','bank_transfer','online') NOT NULL,
  `provider` varchar(50) DEFAULT NULL,
  `transaction_code` varchar(150) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `status` enum('pending','success','failed','refunded') NOT NULL DEFAULT 'pending',
  `paid_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_webhooks`
--

CREATE TABLE `payment_webhooks` (
  `id` bigint UNSIGNED NOT NULL,
  `payment_id` bigint UNSIGNED DEFAULT NULL,
  `provider` varchar(50) NOT NULL,
  `event_code` varchar(150) NOT NULL,
  `transaction_code` varchar(150) DEFAULT NULL,
  `payload` json NOT NULL,
  `status` enum('received','processed','failed') NOT NULL DEFAULT 'received',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `processed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `ticket_id` bigint UNSIGNED NOT NULL,
  `rating` tinyint UNSIGNED NOT NULL,
  `comment` text,
  `status` enum('visible','hidden') NOT NULL DEFAULT 'visible',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` bigint UNSIGNED NOT NULL,
  `category_id` bigint UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text,
  `price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `duration` int UNSIGNED NOT NULL DEFAULT '60',
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `category_id`, `name`, `description`, `price`, `duration`, `image`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Bảo dưỡng cấp 1 – 5.000 km', 'Kiểm tra nhanh các hệ thống thiết yếu, thay dầu và bổ sung dung dịch theo khuyến nghị.', '0.00', 0, 'assets/images/services/maintenance/maintenance-5000km.jpg', 'active', '2026-11-17 01:04:49', '2026-11-17 01:04:49'),
(2, 1, 'Bảo dưỡng cấp 2 – 10.000 km', 'Mở rộng kiểm tra động cơ, hệ thống treo và các bộ phận hao mòn, thay thế vật tư định kỳ.', '0.00', 0, 'assets/images/services/maintenance/maintenance-10000km.jpg', 'active', '2026-11-17 01:04:49', '2026-11-17 01:04:49'),
(3, 1, 'Bảo dưỡng cấp 3 – 20.000 km', 'Kiểm tra sâu hơn các hệ thống quan trọng, tập trung vào độ mài mòn và hiệu quả vận hành.', '0.00', 0, 'assets/images/services/maintenance/maintenance-20000km.jpg', 'active', '2026-11-17 01:04:49', '2026-11-17 01:04:49'),
(4, 1, 'Bảo dưỡng cấp 4 – 40.000 km+', 'Bảo dưỡng lớn, kiểm tra toàn diện và thay thế các bộ phận hao mòn theo khuyến nghị.', '0.00', 0, 'assets/images/services/maintenance/maintenance-40000km.jpg', 'active', '2026-11-17 01:04:50', '2026-11-17 01:04:50'),
(5, 1, 'Kiểm tra xe trước chuyến đi xa / trước đăng kiểm', 'Kiểm tra tổng quát các hệ thống an toàn và vận hành trước hành trình dài hoặc trước kỳ đăng kiểm.', '0.00', 0, 'assets/images/services/maintenance/pre-trip-inspection.jpg', 'active', '2026-11-17 01:04:50', '2026-11-17 01:04:50'),
(6, 2, 'Sửa chữa động cơ – hộp số', 'Chẩn đoán và sửa chữa các lỗi động cơ, hộp số, hệ thống truyền động và tiếng kêu bất thường.', '0.00', 0, 'assets/images/services/repair/engine-repair.jpg', 'active', '2026-11-17 01:04:50', '2026-11-17 01:04:50'),
(7, 2, 'Sửa chữa hệ thống gầm – lái – phanh', 'Kiểm tra và xử lý các hư hỏng ở gầm, hệ thống lái, treo và phanh để đảm bảo an toàn.', '0.00', 0, 'assets/images/services/repair/brake-repair.jpg', 'active', '2026-11-17 01:04:50', '2026-11-17 01:04:50'),
(8, 2, 'Sửa chữa hệ thống điện – điện lạnh', 'Chẩn đoán lỗi điện, ắc quy, hệ thống sạc, điều hòa và điện lạnh trên xe.', '0.00', 0, 'assets/images/services/repair/electrical-ac-repair.jpg', 'active', '2026-11-17 01:04:50', '2026-11-17 01:04:50'),
(9, 2, 'Dịch vụ lốp và mâm xe', 'Kiểm tra, thay thế, cân bằng và xử lý các vấn đề liên quan đến lốp, mâm và độ chụm bánh.', '0.00', 0, 'assets/images/services/repair/tire-wheel.jpg', 'active', '2026-11-17 01:04:50', '2026-11-17 01:04:50'),
(10, 3, 'Sơn dặm / sơn vá', 'Xử lý vết trầy xước, bong tróc hoặc hư hỏng nhỏ trên bề mặt sơn, hạn chế gỉ sét.', '0.00', 0, 'assets/images/services/body-paint/spot-paint.jpg', 'active', '2026-11-17 01:04:50', '2026-11-17 01:04:50'),
(11, 3, 'Sơn quây / sơn đổi màu toàn xe', 'Sơn lại toàn bộ hoặc một phần thân vỏ, phù hợp khi xe hư hỏng nhiều bề mặt hoặc cần đổi màu.', '0.00', 0, 'assets/images/services/body-paint/full-body-paint.jpg', 'active', '2026-11-17 01:04:50', '2026-11-17 01:04:50'),
(12, 3, 'Gò hàn và nắn khung gầm', 'Xử lý hư hỏng kết cấu do va chạm, gò hàn thân vỏ và nắn khung gầm theo tiêu chuẩn an toàn.', '0.00', 0, 'assets/images/services/body-paint/frame-repair.jpg', 'active', '2026-11-17 01:04:50', '2026-11-17 01:04:50'),
(13, 3, 'Xử lý bảo hiểm thân vỏ', 'Hỗ trợ kiểm tra, ghi nhận và xử lý các hư hỏng thân vỏ thuộc phạm vi bảo hiểm.', '0.00', 0, 'assets/images/services/body-paint/insurance-body.jpg', 'active', '2026-11-17 01:04:50', '2026-11-17 01:04:50'),
(14, 4, 'Rửa xe và dọn nội thất', 'Rửa xe, vệ sinh nội thất và làm sạch các khu vực thường xuyên tiếp xúc.', '0.00', 0, 'assets/images/services/detailing/wash-interior.jpg', 'active', '2026-11-17 01:04:50', '2026-11-17 01:04:50'),
(15, 4, 'Chăm sóc ngoại thất', 'Chăm sóc bề mặt sơn, nhựa, kính và các chi tiết ngoại thất để duy trì độ bóng.', '0.00', 0, 'assets/images/services/detailing/exterior-care.jpg', 'active', '2026-11-17 01:04:50', '2026-11-17 01:04:50'),
(16, 4, 'Bảo vệ và phủ bề mặt', 'Phủ lớp bảo vệ lên bề mặt sơn hoặc chi tiết ngoại thất, hạn chế bám bẩn và tác động môi trường.', '0.00', 0, 'assets/images/services/detailing/ceramic-coating.jpg', 'active', '2026-11-17 01:04:50', '2026-11-17 01:04:50'),
(17, 4, 'Vệ sinh khoang máy', 'Vệ sinh khoang máy đúng cách, hạn chế nước xâm nhập cụm điện tử và giúp dễ phát hiện rò rỉ.', '0.00', 0, 'assets/images/services/detailing/engine-bay-cleaning.jpg', 'active', '2026-11-17 01:04:50', '2026-11-17 01:04:50'),
(18, 5, 'Lắp thiết bị an toàn và tiện ích', 'Tư vấn và lắp đặt camera, cảm biến, màn hình và các thiết bị hỗ trợ an toàn, tiện ích.', '0.00', 0, 'assets/images/services/accessories/safety-accessories.jpg', 'active', '2026-11-17 01:04:50', '2026-11-17 01:04:50'),
(19, 5, 'Nâng cấp ánh sáng và âm thanh', 'Nâng cấp hệ thống chiếu sáng và âm thanh theo nhu cầu, phù hợp với xe và điều kiện sử dụng.', '0.00', 0, 'assets/images/services/accessories/lighting-audio.jpg', 'active', '2026-11-17 01:04:50', '2026-11-17 01:04:50'),
(20, 6, 'Cứu hộ giao thông 24/7', 'Hỗ trợ cứu hộ khi xe gặp sự cố trên đường, kết nối phương án vận chuyển xe về gara.', '0.00', 0, 'assets/images/services/roadside/roadside-assistance.jpg', 'active', '2026-11-17 01:04:50', '2026-11-17 01:04:50'),
(21, 6, 'Giao nhận xe tại nhà', 'Hỗ trợ nhận xe tại nhà hoặc địa điểm hẹn và giao lại sau khi hoàn tất dịch vụ.', '0.00', 0, 'assets/images/services/roadside/home-pickup-delivery.jpg', 'active', '2026-11-17 01:04:50', '2026-11-17 01:04:50'),
(22, 6, 'Kiểm tra xe và chẩn đoán lỗi', 'Kiểm tra tổng quát hoặc chẩn đoán lỗi theo triệu chứng bằng thiết bị đọc lỗi và kiểm tra trực tiếp.', '0.00', 0, 'assets/images/services/roadside/diagnostics.jpg', 'active', '2026-11-17 01:04:50', '2026-11-17 01:04:50');

-- --------------------------------------------------------

--
-- Table structure for table `service_categories`
--

CREATE TABLE `service_categories` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `service_categories`
--

INSERT INTO `service_categories` (`id`, `name`, `description`, `status`, `created_at`) VALUES
(1, 'Bảo dưỡng định kỳ', 'Kiểm tra, thay thế vật tư và bảo dưỡng theo từng mốc km, giúp xe vận hành ổn định.', 1, '2026-11-17 01:04:49'),
(2, 'Sửa chữa chung và khắc phục sự cố', 'Chẩn đoán và xử lý các lỗi động cơ, hộp số, gầm, lái, phanh, điện và điện lạnh.', 1, '2026-11-17 01:04:49'),
(3, 'Đồng sơn và phục hồi thân vỏ', 'Khắc phục thân vỏ, sơn sửa và xử lý các hư hỏng do va chạm.', 1, '2026-11-17 01:04:49'),
(4, 'Chăm sóc và làm đẹp xe', 'Rửa xe, dọn nội thất, chăm sóc và bảo vệ bề mặt sơn.', 1, '2026-11-17 01:04:49'),
(5, 'Lắp đặt phụ kiện và nâng cấp xe', 'Lắp đặt thiết bị an toàn, tiện ích và nâng cấp ánh sáng, âm thanh.', 1, '2026-11-17 01:04:49'),
(6, 'Dịch vụ khẩn cấp và tiện ích', 'Hỗ trợ khẩn cấp, giao nhận xe và kiểm tra chẩn đoán lưu động.', 1, '2026-11-17 01:04:49');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` bigint UNSIGNED NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `description` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','customer') NOT NULL DEFAULT 'customer',
  `avatar` varchar(255) DEFAULT NULL,
  `status` enum('active','locked') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `phone`, `password`, `role`, `avatar`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Admin DriveHub', 'admin@drivehub.local', '0900000000', '$2y$10$HZ/u/jLc7MlR7h3odjb94.VWmx9uGPisPG7E4Lj37IYvmPOyc.Uj6', 'admin', NULL, 'active', '2026-11-10 06:10:03', '2026-11-10 06:10:03'),
(2, 'Duy Sang_PH 6 6 0 1 9', 'duysang22122007@gmail.com', '0987654321', '$2y$10$Vc29aTkaeFHJCHelJEBOgeEePrqoLryXlb2bW2kpebsdu9ebzaFRG', 'customer', NULL, 'active', '2026-11-11 05:58:54', '2026-11-11 05:58:54'),
(3, 'Nguyễn Đắc Tiệp P H 6 5 7 8 4', 'tiepdac10@gmail.com', '0812614154', '$2y$10$WERHoJZEfjQeugWW4CAuLu2oebPdA2aU3B3Pvv0B5gv.7OxJVlJmK', 'customer', NULL, 'active', '2026-11-12 01:08:18', '2026-11-12 01:08:18'),
(7, 'kngan', 'luungan2601.hy@gmail.com', '0357805092', '$2y$10$vBK.lluA.DnLNrr.vw8VTeQX1f2Qtmt16yYfXHgJsDJAV.EjHqZn2', 'customer', NULL, 'active', '2026-11-14 01:42:00', '2026-11-14 01:42:00');

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `brand_id` bigint UNSIGNED NOT NULL,
  `vehicle_model_id` bigint UNSIGNED DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `model` varchar(100) NOT NULL,
  `license_plate` varchar(20) NOT NULL,
  `manufacture_year` year DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `current_mileage` int UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`id`, `user_id`, `brand_id`, `vehicle_model_id`, `image_path`, `model`, `license_plate`, `manufacture_year`, `color`, `current_mileage`, `created_at`, `updated_at`) VALUES
(4, 3, 5, NULL, NULL, 'CB1100', '17A-22352', 2007, 'Đen', 10000, '2026-11-12 03:42:57', '2026-11-12 03:43:03');

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_brands`
--

CREATE TABLE `vehicle_brands` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `data_source` varchar(16) NOT NULL DEFAULT 'admin',
  `source_make_id` int UNSIGNED DEFAULT NULL,
  `verification_status` varchar(20) NOT NULL DEFAULT 'unverified'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_import_errors`
--

CREATE TABLE `vehicle_import_errors` (
  `id` bigint UNSIGNED NOT NULL,
  `session_id` bigint UNSIGNED NOT NULL,
  `stage` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `source_make_id` int UNSIGNED DEFAULT NULL,
  `source_model_id` int UNSIGNED DEFAULT NULL,
  `error_message` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_import_sessions`
--

CREATE TABLE `vehicle_import_sessions` (
  `id` bigint UNSIGNED NOT NULL,
  `mode` enum('brands','models','all') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('queued','running','completed','completed_with_errors','failed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'queued',
  `makes_payload` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `next_cursor` int UNSIGNED NOT NULL DEFAULT '0',
  `makes_total` int UNSIGNED NOT NULL DEFAULT '0',
  `makes_processed` int UNSIGNED NOT NULL DEFAULT '0',
  `added_count` int UNSIGNED NOT NULL DEFAULT '0',
  `duplicate_count` int UNSIGNED NOT NULL DEFAULT '0',
  `error_count` int UNSIGNED NOT NULL DEFAULT '0',
  `last_error` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_models`
--

CREATE TABLE `vehicle_models` (
  `id` bigint UNSIGNED NOT NULL,
  `brand_id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `year_from` smallint UNSIGNED DEFAULT NULL,
  `year_to` smallint UNSIGNED DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `data_source` varchar(16) NOT NULL DEFAULT 'admin',
  `source_make_id` int UNSIGNED DEFAULT NULL,
  `source_model_id` int UNSIGNED DEFAULT NULL,
  `verification_status` varchar(20) NOT NULL DEFAULT 'unverified',
  `reviewed_by` bigint UNSIGNED DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `appointment_code` (`appointment_code`),
  ADD KEY `idx_appointment_date` (`appointment_date`,`status`),
  ADD KEY `idx_appointment_user` (`user_id`),
  ADD KEY `idx_appointment_vehicle` (`vehicle_id`);

--
-- Indexes for table `appointment_details`
--
ALTER TABLE `appointment_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `appointment_id` (`appointment_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ticket_id` (`ticket_id`),
  ADD UNIQUE KEY `invoice_code` (`invoice_code`);

--
-- Indexes for table `invoice_details`
--
ALTER TABLE `invoice_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`);

--
-- Indexes for table `maintenance_details`
--
ALTER TABLE `maintenance_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `idx_detail_ticket` (`ticket_id`);

--
-- Indexes for table `maintenance_histories`
--
ALTER TABLE `maintenance_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `idx_history_ticket` (`ticket_id`,`created_at`);

--
-- Indexes for table `maintenance_tickets`
--
ALTER TABLE `maintenance_tickets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `appointment_id` (`appointment_id`),
  ADD UNIQUE KEY `ticket_code` (`ticket_code`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `idx_ticket_vehicle` (`vehicle_id`),
  ADD KEY `idx_ticket_status` (`status`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notification_user` (`user_id`,`is_read`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payment_code` (`payment_code`),
  ADD KEY `idx_payment_invoice` (`invoice_id`),
  ADD KEY `idx_payment_transaction` (`transaction_code`);

--
-- Indexes for table `payment_webhooks`
--
ALTER TABLE `payment_webhooks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_provider_event` (`provider`,`event_code`),
  ADD KEY `idx_webhook_transaction` (`transaction_code`),
  ADD KEY `fk_webhook_payment` (`payment_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ticket_id` (`ticket_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_service_category` (`category_id`);

--
-- Indexes for table `service_categories`
--
ALTER TABLE `service_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `license_plate` (`license_plate`),
  ADD KEY `idx_vehicle_user` (`user_id`),
  ADD KEY `idx_vehicle_brand` (`brand_id`),
  ADD KEY `idx_vehicles_vehicle_model` (`vehicle_model_id`);

--
-- Indexes for table `vehicle_brands`
--
ALTER TABLE `vehicle_brands`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `uq_vehicle_brands_source_make_id_only` (`source_make_id`);

--
-- Indexes for table `vehicle_import_errors`
--
ALTER TABLE `vehicle_import_errors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_vehicle_import_errors_session` (`session_id`,`id`);

--
-- Indexes for table `vehicle_import_sessions`
--
ALTER TABLE `vehicle_import_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_vehicle_import_sessions_status_created` (`status`,`created_at`);

--
-- Indexes for table `vehicle_models`
--
ALTER TABLE `vehicle_models`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_vehicle_models_brand_name` (`brand_id`,`name`),
  ADD UNIQUE KEY `uq_vehicle_models_source_ids_only` (`source_make_id`,`source_model_id`),
  ADD KEY `idx_vehicle_models_brand_status` (`brand_id`,`status`),
  ADD KEY `idx_vehicle_models_review_queue` (`data_source`,`verification_status`,`brand_id`,`id`),
  ADD KEY `fk_vehicle_models_reviewed_by` (`reviewed_by`);

--
-- Constraints for dumped tables
--

ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`);

ALTER TABLE `appointment_details`
  ADD CONSTRAINT `appointment_details_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointment_details_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE RESTRICT;

ALTER TABLE `garage_closed_dates`
  ADD CONSTRAINT `fk_garage_closed_dates_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `maintenance_tickets` (`id`);

ALTER TABLE `invoice_details`
  ADD CONSTRAINT `invoice_details_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE;

ALTER TABLE `maintenance_details`
  ADD CONSTRAINT `maintenance_details_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `maintenance_tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `maintenance_details_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL;

ALTER TABLE `maintenance_histories`
  ADD CONSTRAINT `maintenance_histories_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `maintenance_tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `maintenance_histories_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`);

ALTER TABLE `maintenance_tickets`
  ADD CONSTRAINT `maintenance_tickets_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`),
  ADD CONSTRAINT `maintenance_tickets_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`),
  ADD CONSTRAINT `maintenance_tickets_ibfk_3` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`);

ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `password_resets`
  ADD CONSTRAINT `fk_password_resets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`);

ALTER TABLE `payment_webhooks`
  ADD CONSTRAINT `fk_webhook_payment` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`);

ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`ticket_id`) REFERENCES `maintenance_tickets` (`id`);

ALTER TABLE `services`
  ADD CONSTRAINT `services_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `service_categories` (`id`);

ALTER TABLE `vehicles`
  ADD CONSTRAINT `fk_vehicles_vehicle_model` FOREIGN KEY (`vehicle_model_id`) REFERENCES `vehicle_models` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `vehicles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `vehicles_ibfk_2` FOREIGN KEY (`brand_id`) REFERENCES `vehicle_brands` (`id`);

ALTER TABLE `vehicle_import_errors`
  ADD CONSTRAINT `fk_vehicle_import_errors_session` FOREIGN KEY (`session_id`) REFERENCES `vehicle_import_sessions` (`id`) ON DELETE CASCADE;

ALTER TABLE `vehicle_models`
  ADD CONSTRAINT `fk_vehicle_models_brand` FOREIGN KEY (`brand_id`) REFERENCES `vehicle_brands` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_vehicle_models_reviewed_by` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
