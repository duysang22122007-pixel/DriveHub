<?php

declare(strict_types=1);

$pageTitle = 'Chi tiết phương tiện';
$activeNav = 'vehicles';
$pageEyebrow = 'Phương tiện khách hàng';
$pageDescription = $vehicle['brand_name'] . ' ' . $vehicle['model'] . ' · ' . $vehicle['license_plate'];
$pageActions = '<a class="button secondary" href="' . e(url('admin/vehicles?customer_id=' . (int) $customer['id'])) . '">← Danh sách xe</a>'
    . '<a class="button" href="' . e(url('admin/vehicles/' . (int) $customer['id'] . '/' . (int) $vehicle['id'] . '/edit')) . '">Sửa phương tiện</a>';

ob_start();
?>
<?php if (is_string($success) && $success !== ''): ?><p class="notice" role="status"><?= e($success) ?></p><?php endif; ?>
<?php if (is_string($error) && $error !== ''): ?><p class="notice error" role="alert"><?= e($error) ?></p><?php endif; ?>
<section class="panel">
    <div class="panel-head"><div><h2 class="panel-title">Thông tin phương tiện</h2><p class="panel-description">Chủ sở hữu và thông tin đăng ký hiện tại.</p></div></div>
    <div class="panel-body">
        <dl class="detail-summary">
            <div class="detail-item"><dt class="detail-label">Khách hàng</dt><dd class="detail-value"><strong><?= e($customer['full_name']) ?></strong><br><?= e($customer['email']) ?> · <?= e($customer['phone'] ?? 'Chưa có số điện thoại') ?></dd></div>
            <div class="detail-item"><dt class="detail-label">Phương tiện</dt><dd class="detail-value"><?= e($vehicle['brand_name']) ?> <?= e($vehicle['model']) ?></dd></div>
            <div class="detail-item"><dt class="detail-label">Biển số</dt><dd class="detail-value"><?= e($vehicle['license_plate']) ?></dd></div>
            <div class="detail-item"><dt class="detail-label">Năm sản xuất</dt><dd class="detail-value"><?= e($vehicle['manufacture_year'] ?: 'Chưa cập nhật') ?></dd></div>
            <div class="detail-item"><dt class="detail-label">Màu sắc</dt><dd class="detail-value"><?= e($vehicle['color'] ?: 'Chưa cập nhật') ?></dd></div>
            <div class="detail-item"><dt class="detail-label">Số km hiện tại</dt><dd class="detail-value"><?= e(number_format((int) $vehicle['current_mileage'], 0, ',', '.')) ?> km</dd></div>
        </dl>
    </div>
</section>
<?php
$content = ob_get_clean();
require BASE_PATH . '/app/Views/admin/_layout.php';
