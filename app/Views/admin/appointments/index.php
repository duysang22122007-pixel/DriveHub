<?php

declare(strict_types=1);

$pageTitle = 'Quản lý lịch hẹn';
$activeNav = 'appointments';
$pageEyebrow = 'Vận hành';
$pageDescription = 'Tìm kiếm, xem thông tin và xử lý yêu cầu đặt lịch từ khách hàng.';
$pageStyles = '.data-table{min-width:1240px}';
$pendingUrl = url('admin/appointments') . '?' . http_build_query(['status' => 'pending']);
$pageActions = '<a class="button secondary" href="' . e($pendingUrl) . '">Xem lịch chờ xác nhận</a>';

$filters = ['q' => $q, 'status' => $statusFilter, 'date' => $date];
$statusClasses = [
    'pending' => 'pending',
    'confirmed' => 'confirmed',
    'rejected' => 'rejected',
    'checked_in' => 'checked-in',
    'completed' => 'completed',
    'cancelled' => 'cancelled',
];

ob_start();
?>
<?php foreach ($errors as $errorMessage): ?><p class="notice error" role="alert"><?= e($errorMessage) ?></p><?php endforeach; ?>
<?php if (is_string($success) && $success !== ''): ?><p class="notice" role="status"><?= e($success) ?></p><?php endif; ?>
<?php if (is_string($error) && $error !== ''): ?><p class="notice error" role="alert"><?= e($error) ?></p><?php endif; ?>

<section class="admin-list" aria-label="Danh sách lịch hẹn">
<form method="get" action="<?= e(url('admin/appointments')) ?>" class="filter-bar">
    <div class="filter-grid">
        <div class="filter-field">
            <label for="appointment-q">Tìm kiếm</label>
            <input id="appointment-q" type="search" name="q" value="<?= e($q) ?>" placeholder="Mã lịch, tên, điện thoại hoặc biển số" autocomplete="off">
        </div>
        <div class="filter-field">
            <label for="appointment-status">Trạng thái</label>
            <select id="appointment-status" name="status">
                <option value="">Tất cả trạng thái</option>
                <?php foreach ($statuses as $statusValue => $statusName): ?>
                    <option value="<?= e($statusValue) ?>" <?= $statusFilter === $statusValue ? 'selected' : '' ?>><?= e($statusName) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-field">
            <label for="appointment-date">Ngày hẹn</label>
            <input id="appointment-date" type="date" name="date" value="<?= e($date) ?>">
        </div>
        <div class="filter-actions">
            <button type="submit" class="btn-search">Tìm kiếm</button>
            <?php if ($q !== '' || $statusFilter !== '' || $date !== ''): ?><a href="<?= e(url('admin/appointments')) ?>" class="btn-reset">Xóa bộ lọc</a><?php endif; ?>
        </div>
    </div>
</form>

<p class="table-meta">Tìm thấy <strong><?= e($total) ?></strong> lịch hẹn · Trang <?= e($page) ?>/<?= e($pageCount) ?></p>
<div class="table-wrap">
    <?php if ($appointments === []): ?>
        <div class="empty-state">Không có lịch hẹn phù hợp với bộ lọc.</div>
    <?php else: ?>
        <table class="data-table">
            <thead><tr>
                <th>Mã lịch / Hình thức</th><th>Khách hàng</th><th>Phương tiện</th>
                <th>Ngày, giờ hẹn</th><th>Nội dung</th><th>Trạng thái</th>
                <th>Ngày tạo</th><th>Thao tác</th>
            </tr></thead>
            <tbody>
            <?php foreach ($appointments as $appointment): ?>
                <?php
                    $statusClass = $statusClasses[$appointment['status']] ?? '';
                    $dateLabel = date('d/m/Y', strtotime((string) $appointment['appointment_date']));
                    $timeLabel = substr((string) $appointment['appointment_time'], 0, 5);
                    $createdLabel = $appointment['created_at'] === null
                        ? '—'
                        : date('d/m/Y H:i', strtotime((string) $appointment['created_at']));
                    $typeLabel = $bookingTypes[$appointment['appointment_type']] ?? 'Khác';
                ?>
                <tr>
                    <td><p class="code"><?= e($appointment['appointment_code']) ?></p><p class="cell-sub"><?= e($typeLabel) ?></p></td>
                    <td><p class="cell-main"><?= e($appointment['full_name']) ?></p><p class="cell-sub"><?= e($appointment['phone'] ?? 'Chưa có số điện thoại') ?></p></td>
                    <td><p class="cell-main"><?= e($appointment['brand_name'] . ' ' . $appointment['model']) ?></p><p class="cell-sub"><?= e($appointment['license_plate']) ?></p></td>
                    <td><span class="cell-main"><?= e($dateLabel) ?></span><span class="cell-sub"><?= e($timeLabel) ?></span></td>
                    <td class="note"><?= e($appointment['note'] ?? 'Không có ghi chú') ?></td>
                    <td>
                        <span class="status <?= e($statusClass) ?>"><?= e($statuses[$appointment['status']] ?? 'Chưa xác định') ?></span>
                        <?php $ticketStatus = $maintenanceStatuses[$appointment['maintenance_ticket_status'] ?? ''] ?? null; ?>
                        <?php if ($ticketStatus !== null): ?><p class="cell-sub">Phiếu sửa chữa: <?= e($ticketStatus) ?></p><?php endif; ?>
                        <?php if ($appointment['status'] === 'rejected' && is_string($appointment['admin_note']) && trim($appointment['admin_note']) !== ''): ?>
                            <p class="cell-sub">Lý do: <?= e($appointment['admin_note']) ?></p>
                        <?php endif; ?>
                    </td>
                    <td><?= e($createdLabel) ?></td>
                    <td><a class="link" href="<?= e(url('admin/appointments/' . (int) $appointment['id'])) ?>">Chi tiết</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php if ($pageCount > 1): ?>
    <nav class="pagination" aria-label="Phân trang">
        <?php if ($page > 1): ?><a href="<?= e(url('admin/appointments') . '?' . http_build_query($filters + ['page' => $page - 1])) ?>">← Trước</a><?php endif; ?>
        <span>Trang <?= e($page) ?> / <?= e($pageCount) ?></span>
        <?php if ($page < $pageCount): ?><a href="<?= e(url('admin/appointments') . '?' . http_build_query($filters + ['page' => $page + 1])) ?>">Sau →</a><?php endif; ?>
    </nav>
<?php endif; ?>
</section>
<?php
$content = ob_get_clean();
require BASE_PATH . '/app/Views/admin/_layout.php';
