-- DriveHub - du lieu goi y cho he thong dich vu
-- CHI tao file, KHONG tu dong chay hoac import.
-- Chi chay sau khi thanh vien phu trach database da review va dong y.
-- Gia va thoi luong de 0 de website hien thi "Lien he de duoc bao gia"
-- va "Lien he de biet thoi gian" thay vi dua ra so lieu gia.
-- Anh dich vu: Pexels License, luu cuc bo tai public/assets/images/services/.

SET NAMES utf8mb4;

START TRANSACTION;

-- 1. Nhom dich vu
INSERT INTO service_categories (name, description, status)
SELECT 'Bảo dưỡng định kỳ', 'Kiểm tra, thay thế vật tư và bảo dưỡng theo từng mốc km, giúp xe vận hành ổn định.', 1
WHERE NOT EXISTS (SELECT 1 FROM service_categories WHERE name = 'Bảo dưỡng định kỳ');

INSERT INTO service_categories (name, description, status)
SELECT 'Sửa chữa chung và khắc phục sự cố', 'Chẩn đoán và xử lý các lỗi động cơ, hộp số, gầm, lái, phanh, điện và điện lạnh.', 1
WHERE NOT EXISTS (SELECT 1 FROM service_categories WHERE name = 'Sửa chữa chung và khắc phục sự cố');

INSERT INTO service_categories (name, description, status)
SELECT 'Đồng sơn và phục hồi thân vỏ', 'Khắc phục thân vỏ, sơn sửa và xử lý các hư hỏng do va chạm.', 1
WHERE NOT EXISTS (SELECT 1 FROM service_categories WHERE name = 'Đồng sơn và phục hồi thân vỏ');

INSERT INTO service_categories (name, description, status)
SELECT 'Chăm sóc và làm đẹp xe', 'Rửa xe, dọn nội thất, chăm sóc và bảo vệ bề mặt sơn.', 1
WHERE NOT EXISTS (SELECT 1 FROM service_categories WHERE name = 'Chăm sóc và làm đẹp xe');

INSERT INTO service_categories (name, description, status)
SELECT 'Lắp đặt phụ kiện và nâng cấp xe', 'Lắp đặt thiết bị an toàn, tiện ích và nâng cấp ánh sáng, âm thanh.', 1
WHERE NOT EXISTS (SELECT 1 FROM service_categories WHERE name = 'Lắp đặt phụ kiện và nâng cấp xe');

INSERT INTO service_categories (name, description, status)
SELECT 'Dịch vụ khẩn cấp và tiện ích', 'Hỗ trợ khẩn cấp, giao nhận xe và kiểm tra chẩn đoán lưu động.', 1
WHERE NOT EXISTS (SELECT 1 FROM service_categories WHERE name = 'Dịch vụ khẩn cấp và tiện ích');

-- 2. Nhom Bao duong dinh ky
INSERT INTO services (category_id, name, description, price, duration, image, status)
SELECT c.id, 'Bảo dưỡng cấp 1 – 5.000 km', 'Kiểm tra nhanh các hệ thống thiết yếu, thay dầu và bổ sung dung dịch theo khuyến nghị.', 0.00, 0, 'assets/images/services/maintenance/maintenance-5000km.jpg', 'active'
FROM service_categories c
WHERE c.name = 'Bảo dưỡng định kỳ'
  AND NOT EXISTS (SELECT 1 FROM services s WHERE s.category_id = c.id AND s.name = 'Bảo dưỡng cấp 1 – 5.000 km');

INSERT INTO services (category_id, name, description, price, duration, image, status)
SELECT c.id, 'Bảo dưỡng cấp 2 – 10.000 km', 'Mở rộng kiểm tra động cơ, hệ thống treo và các bộ phận hao mòn, thay thế vật tư định kỳ.', 0.00, 0, 'assets/images/services/maintenance/maintenance-10000km.jpg', 'active'
FROM service_categories c
WHERE c.name = 'Bảo dưỡng định kỳ'
  AND NOT EXISTS (SELECT 1 FROM services s WHERE s.category_id = c.id AND s.name = 'Bảo dưỡng cấp 2 – 10.000 km');

INSERT INTO services (category_id, name, description, price, duration, image, status)
SELECT c.id, 'Bảo dưỡng cấp 3 – 20.000 km', 'Kiểm tra sâu hơn các hệ thống quan trọng, tập trung vào độ mài mòn và hiệu quả vận hành.', 0.00, 0, 'assets/images/services/maintenance/maintenance-20000km.jpg', 'active'
FROM service_categories c
WHERE c.name = 'Bảo dưỡng định kỳ'
  AND NOT EXISTS (SELECT 1 FROM services s WHERE s.category_id = c.id AND s.name = 'Bảo dưỡng cấp 3 – 20.000 km');

INSERT INTO services (category_id, name, description, price, duration, image, status)
SELECT c.id, 'Bảo dưỡng cấp 4 – 40.000 km+', 'Bảo dưỡng lớn, kiểm tra toàn diện và thay thế các bộ phận hao mòn theo khuyến nghị.', 0.00, 0, 'assets/images/services/maintenance/maintenance-40000km.jpg', 'active'
FROM service_categories c
WHERE c.name = 'Bảo dưỡng định kỳ'
  AND NOT EXISTS (SELECT 1 FROM services s WHERE s.category_id = c.id AND s.name = 'Bảo dưỡng cấp 4 – 40.000 km+');

INSERT INTO services (category_id, name, description, price, duration, image, status)
SELECT c.id, 'Kiểm tra xe trước chuyến đi xa / trước đăng kiểm', 'Kiểm tra tổng quát các hệ thống an toàn và vận hành trước hành trình dài hoặc trước kỳ đăng kiểm.', 0.00, 0, 'assets/images/services/maintenance/pre-trip-inspection.jpg', 'active'
FROM service_categories c
WHERE c.name = 'Bảo dưỡng định kỳ'
  AND NOT EXISTS (SELECT 1 FROM services s WHERE s.category_id = c.id AND s.name = 'Kiểm tra xe trước chuyến đi xa / trước đăng kiểm');

-- 3. Nhom Sua chua chung va khac phuc su co
INSERT INTO services (category_id, name, description, price, duration, image, status)
SELECT c.id, 'Sửa chữa động cơ – hộp số', 'Chẩn đoán và sửa chữa các lỗi động cơ, hộp số, hệ thống truyền động và tiếng kêu bất thường.', 0.00, 0, 'assets/images/services/repair/engine-repair.jpg', 'active'
FROM service_categories c
WHERE c.name = 'Sửa chữa chung và khắc phục sự cố'
  AND NOT EXISTS (SELECT 1 FROM services s WHERE s.category_id = c.id AND s.name = 'Sửa chữa động cơ – hộp số');

INSERT INTO services (category_id, name, description, price, duration, image, status)
SELECT c.id, 'Sửa chữa hệ thống gầm – lái – phanh', 'Kiểm tra và xử lý các hư hỏng ở gầm, hệ thống lái, treo và phanh để đảm bảo an toàn.', 0.00, 0, 'assets/images/services/repair/brake-repair.jpg', 'active'
FROM service_categories c
WHERE c.name = 'Sửa chữa chung và khắc phục sự cố'
  AND NOT EXISTS (SELECT 1 FROM services s WHERE s.category_id = c.id AND s.name = 'Sửa chữa hệ thống gầm – lái – phanh');

INSERT INTO services (category_id, name, description, price, duration, image, status)
SELECT c.id, 'Sửa chữa hệ thống điện – điện lạnh', 'Chẩn đoán lỗi điện, ắc quy, hệ thống sạc, điều hòa và điện lạnh trên xe.', 0.00, 0, 'assets/images/services/repair/electrical-ac-repair.jpg', 'active'
FROM service_categories c
WHERE c.name = 'Sửa chữa chung và khắc phục sự cố'
  AND NOT EXISTS (SELECT 1 FROM services s WHERE s.category_id = c.id AND s.name = 'Sửa chữa hệ thống điện – điện lạnh');

INSERT INTO services (category_id, name, description, price, duration, image, status)
SELECT c.id, 'Dịch vụ lốp và mâm xe', 'Kiểm tra, thay thế, cân bằng và xử lý các vấn đề liên quan đến lốp, mâm và độ chụm bánh.', 0.00, 0, 'assets/images/services/repair/tire-wheel.jpg', 'active'
FROM service_categories c
WHERE c.name = 'Sửa chữa chung và khắc phục sự cố'
  AND NOT EXISTS (SELECT 1 FROM services s WHERE s.category_id = c.id AND s.name = 'Dịch vụ lốp và mâm xe');

-- 4. Nhom Dong son va phuc hoi than vo
INSERT INTO services (category_id, name, description, price, duration, image, status)
SELECT c.id, 'Sơn dặm / sơn vá', 'Xử lý vết trầy xước, bong tróc hoặc hư hỏng nhỏ trên bề mặt sơn, hạn chế gỉ sét.', 0.00, 0, 'assets/images/services/body-paint/spot-paint.jpg', 'active'
FROM service_categories c
WHERE c.name = 'Đồng sơn và phục hồi thân vỏ'
  AND NOT EXISTS (SELECT 1 FROM services s WHERE s.category_id = c.id AND s.name = 'Sơn dặm / sơn vá');

INSERT INTO services (category_id, name, description, price, duration, image, status)
SELECT c.id, 'Sơn quây / sơn đổi màu toàn xe', 'Sơn lại toàn bộ hoặc một phần thân vỏ, phù hợp khi xe hư hỏng nhiều bề mặt hoặc cần đổi màu.', 0.00, 0, 'assets/images/services/body-paint/full-body-paint.jpg', 'active'
FROM service_categories c
WHERE c.name = 'Đồng sơn và phục hồi thân vỏ'
  AND NOT EXISTS (SELECT 1 FROM services s WHERE s.category_id = c.id AND s.name = 'Sơn quây / sơn đổi màu toàn xe');

INSERT INTO services (category_id, name, description, price, duration, image, status)
SELECT c.id, 'Gò hàn và nắn khung gầm', 'Xử lý hư hỏng kết cấu do va chạm, gò hàn thân vỏ và nắn khung gầm theo tiêu chuẩn an toàn.', 0.00, 0, 'assets/images/services/body-paint/frame-repair.jpg', 'active'
FROM service_categories c
WHERE c.name = 'Đồng sơn và phục hồi thân vỏ'
  AND NOT EXISTS (SELECT 1 FROM services s WHERE s.category_id = c.id AND s.name = 'Gò hàn và nắn khung gầm');

INSERT INTO services (category_id, name, description, price, duration, image, status)
SELECT c.id, 'Xử lý bảo hiểm thân vỏ', 'Hỗ trợ kiểm tra, ghi nhận và xử lý các hư hỏng thân vỏ thuộc phạm vi bảo hiểm.', 0.00, 0, 'assets/images/services/body-paint/insurance-body.jpg', 'active'
FROM service_categories c
WHERE c.name = 'Đồng sơn và phục hồi thân vỏ'
  AND NOT EXISTS (SELECT 1 FROM services s WHERE s.category_id = c.id AND s.name = 'Xử lý bảo hiểm thân vỏ');

-- 5. Nhom Cham soc va lam dep xe
INSERT INTO services (category_id, name, description, price, duration, image, status)
SELECT c.id, 'Rửa xe và dọn nội thất', 'Rửa xe, vệ sinh nội thất và làm sạch các khu vực thường xuyên tiếp xúc.', 0.00, 0, 'assets/images/services/detailing/wash-interior.jpg', 'active'
FROM service_categories c
WHERE c.name = 'Chăm sóc và làm đẹp xe'
  AND NOT EXISTS (SELECT 1 FROM services s WHERE s.category_id = c.id AND s.name = 'Rửa xe và dọn nội thất');

INSERT INTO services (category_id, name, description, price, duration, image, status)
SELECT c.id, 'Chăm sóc ngoại thất', 'Chăm sóc bề mặt sơn, nhựa, kính và các chi tiết ngoại thất để duy trì độ bóng.', 0.00, 0, 'assets/images/services/detailing/exterior-care.jpg', 'active'
FROM service_categories c
WHERE c.name = 'Chăm sóc và làm đẹp xe'
  AND NOT EXISTS (SELECT 1 FROM services s WHERE s.category_id = c.id AND s.name = 'Chăm sóc ngoại thất');

INSERT INTO services (category_id, name, description, price, duration, image, status)
SELECT c.id, 'Bảo vệ và phủ bề mặt', 'Phủ lớp bảo vệ lên bề mặt sơn hoặc chi tiết ngoại thất, hạn chế bám bẩn và tác động môi trường.', 0.00, 0, 'assets/images/services/detailing/ceramic-coating.jpg', 'active'
FROM service_categories c
WHERE c.name = 'Chăm sóc và làm đẹp xe'
  AND NOT EXISTS (SELECT 1 FROM services s WHERE s.category_id = c.id AND s.name = 'Bảo vệ và phủ bề mặt');

INSERT INTO services (category_id, name, description, price, duration, image, status)
SELECT c.id, 'Vệ sinh khoang máy', 'Vệ sinh khoang máy đúng cách, hạn chế nước xâm nhập cụm điện tử và giúp dễ phát hiện rò rỉ.', 0.00, 0, 'assets/images/services/detailing/engine-bay-cleaning.jpg', 'active'
FROM service_categories c
WHERE c.name = 'Chăm sóc và làm đẹp xe'
  AND NOT EXISTS (SELECT 1 FROM services s WHERE s.category_id = c.id AND s.name = 'Vệ sinh khoang máy');

-- 6. Nhom Lap dat phu kien va nang cap xe
INSERT INTO services (category_id, name, description, price, duration, image, status)
SELECT c.id, 'Lắp thiết bị an toàn và tiện ích', 'Tư vấn và lắp đặt camera, cảm biến, màn hình và các thiết bị hỗ trợ an toàn, tiện ích.', 0.00, 0, 'assets/images/services/accessories/safety-accessories.jpg', 'active'
FROM service_categories c
WHERE c.name = 'Lắp đặt phụ kiện và nâng cấp xe'
  AND NOT EXISTS (SELECT 1 FROM services s WHERE s.category_id = c.id AND s.name = 'Lắp thiết bị an toàn và tiện ích');

INSERT INTO services (category_id, name, description, price, duration, image, status)
SELECT c.id, 'Nâng cấp ánh sáng và âm thanh', 'Nâng cấp hệ thống chiếu sáng và âm thanh theo nhu cầu, phù hợp với xe và điều kiện sử dụng.', 0.00, 0, 'assets/images/services/accessories/lighting-audio.jpg', 'active'
FROM service_categories c
WHERE c.name = 'Lắp đặt phụ kiện và nâng cấp xe'
  AND NOT EXISTS (SELECT 1 FROM services s WHERE s.category_id = c.id AND s.name = 'Nâng cấp ánh sáng và âm thanh');

-- 7. Nhom Dich vu khan cap va tien ich
INSERT INTO services (category_id, name, description, price, duration, image, status)
SELECT c.id, 'Cứu hộ giao thông 24/7', 'Hỗ trợ cứu hộ khi xe gặp sự cố trên đường, kết nối phương án vận chuyển xe về gara.', 0.00, 0, 'assets/images/services/roadside/roadside-assistance.jpg', 'active'
FROM service_categories c
WHERE c.name = 'Dịch vụ khẩn cấp và tiện ích'
  AND NOT EXISTS (SELECT 1 FROM services s WHERE s.category_id = c.id AND s.name = 'Cứu hộ giao thông 24/7');

INSERT INTO services (category_id, name, description, price, duration, image, status)
SELECT c.id, 'Giao nhận xe tại nhà', 'Hỗ trợ nhận xe tại nhà hoặc địa điểm hẹn và giao lại sau khi hoàn tất dịch vụ.', 0.00, 0, 'assets/images/services/roadside/home-pickup-delivery.jpg', 'active'
FROM service_categories c
WHERE c.name = 'Dịch vụ khẩn cấp và tiện ích'
  AND NOT EXISTS (SELECT 1 FROM services s WHERE s.category_id = c.id AND s.name = 'Giao nhận xe tại nhà');

INSERT INTO services (category_id, name, description, price, duration, image, status)
SELECT c.id, 'Kiểm tra xe và chẩn đoán lỗi', 'Kiểm tra tổng quát hoặc chẩn đoán lỗi theo triệu chứng bằng thiết bị đọc lỗi và kiểm tra trực tiếp.', 0.00, 0, 'assets/images/services/roadside/diagnostics.jpg', 'active'
FROM service_categories c
WHERE c.name = 'Dịch vụ khẩn cấp và tiện ích'
  AND NOT EXISTS (SELECT 1 FROM services s WHERE s.category_id = c.id AND s.name = 'Kiểm tra xe và chẩn đoán lỗi');

COMMIT;
