<?php

declare(strict_types=1);

?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lịch hẹn của tôi | AutoCare Hub</title>
    <?php require __DIR__ . '/_styles.php'; ?>
</head>
<body>
<header class="site-header"><div class="top">
    <a class="brand" href="<?= e(url()) ?>">AutoCare Hub</a>
    <nav aria-label="Điều hướng khách hàng">
        <a href="<?= e(url('vehicles')) ?>">Xe của tôi</a>
        <a href="<?= e(url('appointments')) ?>" aria-current="page">Lịch hẹn</a>
        <a href="<?= e(url('profile')) ?>">Tài khoản</a>
        <form method="post" action="<?= e(url('logout')) ?>"><?= csrf_field() ?><button class="button secondary" type="submit">Đăng xuất</button></form>
    </nav>
</div></header>
<main>
    <div class="page-heading">
        <div><span class="eyebrow">Tài khoản khách hàng</span><h1>Lịch hẹn của tôi</h1><p>Theo dõi các yêu cầu đặt lịch đã gửi bằng tài khoản này.</p></div>
        <a class="button" href="<?= e(url('appointments/create')) ?>">+ Đặt lịch mới</a>
    </div>
    <?php foreach ($errors as $errorMessage): ?><p class="notice error" role="alert"><?= e($errorMessage) ?></p><?php endforeach; ?>
    <form method="get" action="<?= e(url('appointments')) ?>" class="panel" style="display:flex;align-items:end;gap:12px;flex-wrap:wrap;margin-bottom:18px;padding:16px">
        <label for="appointment-status" style="display:grid;gap:6px;min-width:220px;color:#34495e;font-weight:700">
            Lọc theo trạng thái
            <select id="appointment-status" name="status" style="min-height:42px;padding:8px 10px;border:1px solid #cbd6e1;border-radius:8px;background:#fff;font:inherit">
                <option value="">Tất cả trạng thái</option>
                <?php foreach ($statuses as $statusValue => $statusName): ?>
                    <option value="<?= e($statusValue) ?>" <?= $statusFilter === $statusValue ? 'selected' : '' ?>><?= e($statusName) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button class="button" type="submit">Lọc</button>
        <?php if ($statusFilter !== ''): ?><a class="button secondary" href="<?= e(url('appointments')) ?>">Xóa bộ lọc</a><?php endif; ?>
    </form>
    <?php if ($appointments === []): ?>
        <section class="panel empty-state">
            <strong><?= $statusFilter === '' ? 'Bạn chưa có lịch hẹn' : 'Không có lịch hẹn phù hợp với trạng thái đã chọn' ?></strong>
            <p><?= $statusFilter === '' ? 'Chọn xe và gửi thời gian mong muốn để bắt đầu yêu cầu dịch vụ.' : 'Thử chọn trạng thái khác hoặc xem toàn bộ lịch hẹn.' ?></p>
            <?php if ($statusFilter === ''): ?><p><a class="button" href="<?= e(url('appointments/create')) ?>">Bắt đầu đặt lịch</a></p><?php else: ?><p><a class="button secondary" href="<?= e(url('appointments')) ?>">Xem tất cả lịch hẹn</a></p><?php endif; ?>
        </section>
    <?php else: ?>
        <section class="list-grid" aria-label="Lịch hẹn">
            <?php foreach ($appointments as $appointment): ?>
                <?php
                    $type = $appointmentTypeLabels[$appointment['appointment_type']] ?? 'Lịch cũ';
                    $statusLabel = $statuses[$appointment['status']] ?? 'Chưa xác định';
                    $maintenanceStatus = $maintenanceStatuses[$appointment['maintenance_ticket_status'] ?? ''] ?? null;
                    $date = date('d/m/Y', strtotime((string) $appointment['appointment_date']));
                    $time = substr((string) $appointment['appointment_time'], 0, 5);
                    $createdAt = date('d/m/Y H:i', strtotime((string) $appointment['created_at']));
                ?>
                <article class="appointment-card">
                    <div class="appointment-card-head">
                        <div><h2>Mã lịch <?= e($appointment['appointment_code']) ?></h2><p><?= e($type) ?></p></div>
                        <span class="status <?= e($appointment['status']) ?>"><?= e($statusLabel) ?></span>
                    </div>
                    <p><strong><?= e($appointment['brand_name'] . ' ' . $appointment['model']) ?></strong><br>
                        <?= e($appointment['license_plate']) ?> · <?= e($date) ?> lúc <?= e($time) ?></p>
                    <p>Ngày đặt: <?= e($createdAt) ?></p>
                    <p>Dịch vụ: <?= e($appointment['service_names'] ?: 'Chưa chọn dịch vụ') ?></p>
                    <?php if ($appointment['status'] === 'rejected' && is_string($appointment['rejection_reason'] ?? null) && trim($appointment['rejection_reason']) !== ''): ?>
                        <p class="notice error"><strong>Lý do từ chối:</strong> <?= nl2br(e($appointment['rejection_reason'])) ?></p>
                    <?php endif; ?>
                    <?php if ($maintenanceStatus !== null): ?><p>Phiếu sửa chữa: <?= e($maintenanceStatus) ?></p><?php endif; ?>
                    <p><?= (int) $appointment['service_count'] ?> dịch vụ · Tham khảo <?= e(number_format((float) $appointment['reference_total'], 0, ',', '.')) ?> ₫</p>
                    <a class="card-link" href="<?= e(url('appointments/' . (int) $appointment['id'])) ?>">Xem chi tiết →</a>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
</main>
<footer class="site-footer">AutoCare Hub · Trạng thái lịch được cập nhật theo phản hồi của gara.</footer>
</body>
</html>
