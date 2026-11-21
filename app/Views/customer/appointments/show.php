<?php

declare(strict_types=1);

$type = $appointmentTypeLabels[$appointment['appointment_type']] ?? 'Lịch cũ';
$statusLabel = $statuses[$appointment['status']] ?? 'Chưa xác định';
$cancellationStatus = $appointment['cancellation_status'] ?? 'none';
$cancellationStatusLabels = [
    'none' => 'Chưa yêu cầu hủy',
    'requested' => 'Đang chờ Admin xử lý yêu cầu hủy',
    'approved' => 'Đã hủy',
    'rejected' => 'Yêu cầu hủy bị từ chối',
];
$cancellationRequestedAt = $appointment['cancellation_requested_at'] === null
    ? null
    : date('d/m/Y H:i', strtotime((string) $appointment['cancellation_requested_at']));
$cancellationDecidedAt = $appointment['cancellation_decided_at'] === null
    ? null
    : date('d/m/Y H:i', strtotime((string) $appointment['cancellation_decided_at']));
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
<?php
$activeCustomerNav = 'appointments';
require __DIR__ . '/../../partials/customer-header.php';
?>
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
            <?php if ($cancellationStatus !== 'none'): ?>
                <div class="notice <?= $cancellationStatus === 'rejected' ? 'error' : '' ?>" role="status">
                    <strong><?= e($cancellationStatusLabels[$cancellationStatus] ?? 'Yêu cầu hủy chưa xác định') ?></strong>
                    <?php if ($cancellationRequestedAt !== null): ?><br>Gửi lúc <?= e($cancellationRequestedAt) ?><?php endif; ?>
                    <?php if ($cancellationDecidedAt !== null): ?><br>Cập nhật lúc <?= e($cancellationDecidedAt) ?><?php endif; ?>
                    <?php if (is_string($appointment['cancellation_reason']) && trim($appointment['cancellation_reason']) !== ''): ?><br>Lý do: <?= nl2br(e($appointment['cancellation_reason'])) ?><?php endif; ?>
                </div>
            <?php endif; ?>
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
            <?php if (in_array($appointment['status'], ['pending', 'confirmed'], true) && $cancellationStatus !== 'requested'): ?>
            <section class="panel">
                <h2>Hủy lịch hẹn</h2>
                <p class="muted" style="margin:0 0 12px">Yêu cầu của bạn sẽ được Admin xem xét. Lịch chỉ chuyển thành <strong>Đã hủy</strong> sau khi được xác nhận.</p>
                <form method="post" action="<?= e(url('appointments/' . (int) $appointment['id'] . '/cancel')) ?>" onsubmit="return confirm('Bạn có chắc muốn hủy lịch hẹn này không?');">
                    <?= csrf_field() ?>
                    <div style="margin-bottom:10px">
                        <label style="display:block;margin-bottom:4px;font-size:.8rem;font-weight:700;color:#526678">Lý do hủy (không bắt buộc)</label>
                        <textarea name="cancellation_reason" maxlength="500" placeholder="Nhập lý do hủy nếu có…" style="width:100%;min-height:80px;padding:8px 10px;border:1px solid #d1dbe5;border-radius:8px;font:inherit;font-size:.86rem;resize:vertical;box-sizing:border-box"><?= $cancellationStatus === 'rejected' ? e($appointment['cancellation_reason']) : '' ?></textarea>
                    </div>
                    <button class="button" type="submit" style="width:100%;background:#b42318;border-color:#b42318"><?= $cancellationStatus === 'rejected' ? 'Gửi lại yêu cầu hủy' : 'Gửi yêu cầu hủy' ?></button>
                </form>
            </section>
            <?php endif; ?>
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
