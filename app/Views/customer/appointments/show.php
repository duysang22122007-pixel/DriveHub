<?php

declare(strict_types=1);

$type = $bookingTypes[$appointment['appointment_type']] ?? 'Hình thức khác';
$statusLabel = $statuses[$appointment['status']] ?? 'Chưa xác định';
$maintenanceStatus = $maintenanceStatuses[$appointment['maintenance_ticket_status'] ?? ''] ?? null;
$date = date('d/m/Y', strtotime((string) $appointment['appointment_date']));
$time = substr((string) $appointment['appointment_time'], 0, 5);
$referenceTotal = array_sum(array_map(
    static fn (array $service): float => (float) $service['price'] * (int) $service['quantity'],
    $services
));

?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Chi tiết lịch hẹn <?= e($appointment['appointment_code']) ?> | AutoCare Hub</title>
    <?php require __DIR__ . '/_styles.php'; ?>
</head>
<body>
<header class="site-header"><div class="top">
    <a class="brand" href="<?= e(url()) ?>">AutoCare Hub</a>
    <nav aria-label="Điều hướng khách hàng">
        <a href="<?= e(url('vehicles')) ?>">Xe của tôi</a>
        <a href="<?= e(url('appointments')) ?>">Lịch hẹn</a>
        <a href="<?= e(url('profile')) ?>">Tài khoản</a>
        <form method="post" action="<?= e(url('logout')) ?>"><?= csrf_field() ?><button class="button secondary" type="submit">Đăng xuất</button></form>
    </nav>
</div></header>
<main>
    <div class="page-heading">
        <div><span class="eyebrow">Thông tin yêu cầu</span><h1>Lịch <?= e($appointment['appointment_code']) ?></h1><p>Thông tin này chỉ hiển thị trong tài khoản đã gửi lịch.</p></div>
        <a class="button secondary" href="<?= e(url('appointments')) ?>">Lịch hẹn của tôi</a>
    </div>
    <?php if (is_string($success) && $success !== ''): ?><div class="notice" role="status"><?= e($success) ?></div><?php endif; ?>
    <div class="layout">
        <section class="panel">
            <h2>Trạng thái</h2>
            <p><span class="status <?= e($appointment['status']) ?>"><?= e($statusLabel) ?></span></p>
            <?php if ($maintenanceStatus !== null): ?><p>Phiếu sửa chữa: <?= e($maintenanceStatus) ?></p><?php endif; ?>
            <?php if ($appointment['status'] === 'rejected' && is_string($appointment['rejection_reason'] ?? null) && trim($appointment['rejection_reason']) !== ''): ?>
                <div class="notice error" role="status"><strong>Lý do từ chối:</strong><br><?= nl2br(e($appointment['rejection_reason'])) ?></div>
            <?php endif; ?>
            <p class="panel-intro">Thời gian hiển thị là đề xuất của bạn. Chỉ được xác nhận khi trạng thái lịch thay đổi sau phản hồi của gara.</p>

            <h2>Thông tin đặt lịch</h2>
            <dl class="summary-list">
                <div class="summary-row"><dt>Hình thức</dt><dd><?= e($type) ?></dd></div>
                <div class="summary-row"><dt>Xe</dt><dd><?= e($appointment['brand_name'] . ' ' . $appointment['model']) ?><br><?= e($appointment['license_plate']) ?> · Năm <?= e($appointment['manufacture_year'] ?: 'Chưa cập nhật') ?></dd></div>
                <div class="summary-row"><dt>Ngày, giờ mong muốn</dt><dd><?= e($date) ?> · <?= e($time) ?></dd></div>
                <div class="summary-row"><dt>Thông tin liên hệ</dt><dd><?= e($customer['full_name'] ?? '') ?><br><?= e($customer['phone'] ?? '') ?><br><?= e($customer['email'] ?? '') ?></dd></div>
                <div class="summary-row">
                    <dt>Dịch vụ</dt>
                    <dd>
                        <?php if ($services === []): ?>Chưa chọn dịch vụ; gara sẽ liên hệ làm rõ nhu cầu.<?php else: ?>
                            <?php foreach ($services as $service): ?>
                                <?= e($service['service_name']) ?> · <?= e(number_format((float) $service['price'], 0, ',', '.')) ?> ₫
                                <?= (int) $service['quantity'] > 1 ? '× ' . (int) $service['quantity'] : '' ?><br>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </dd>
                </div>
                <div class="summary-row"><dt>Mô tả và ghi chú</dt><dd><?= $appointment['note'] !== null && $appointment['note'] !== '' ? nl2br(e($appointment['note'])) : 'Chưa có ghi chú.' ?></dd></div>
            </dl>
            <div class="summary-total">Tổng giá tham khảo <span><?= $services === [] ? 'Chưa có dịch vụ' : e(number_format($referenceTotal, 0, ',', '.') . ' ₫') ?></span></div>
            <p class="summary-note">Đây là giá tham khảo đã lưu tại thời điểm gửi; không thay thế báo giá cuối cùng của gara.</p>
        </section>
        <aside class="aside">
            <section class="panel">
                <h2>Bạn cần hỗ trợ?</h2>
                <?php if (is_string($garage['phone'] ?? null) && $garage['phone'] !== ''): ?>
                    <p class="muted">Liên hệ gara qua số điện thoại đã cấu hình:</p>
                    <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $garage['phone'])) ?>"><?= e($garage['phone']) ?></a>
                <?php else: ?>
                    <p class="muted">Gara chưa cấu hình số điện thoại hỗ trợ. Bạn có thể gửi yêu cầu mới để bổ sung thông tin.</p>
                <?php endif; ?>
                <p><a href="<?= e(url('appointments/create')) ?>">Tạo yêu cầu đặt lịch khác</a></p>
            </section>
        </aside>
    </div>
</main>
<footer class="site-footer">AutoCare Hub · Cảm ơn bạn đã gửi yêu cầu.</footer>
</body>
</html>
