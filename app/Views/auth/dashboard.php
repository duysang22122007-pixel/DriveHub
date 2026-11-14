<?php

declare(strict_types=1);

$pageTitle = $dashboardTitle ?? 'Khu vực quản trị';
$activeNav = 'dashboard';
$pageEyebrow = 'Tổng quan';
$pageDescription = 'Xin chào ' . ($userName !== '' ? $userName : 'Quản trị viên') . '. Theo dõi tình hình vận hành gara và lịch hẹn mới nhất.';
$pendingUrl = url('admin/appointments') . '?' . http_build_query(['status' => 'pending']);
$pageActions = '<a class="button secondary" href="' . e($pendingUrl) . '">Xem lịch chờ xử lý</a>';

$metricIcon = static function (string $name): string {
    $icons = [
        'customers' => '<path d="M16 19v-1a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v1M10 10a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm7 9v-1a4 4 0 0 0-3-3.87M16 4.13a3 3 0 0 1 0 5.74"/>',
        'vehicle' => '<path d="M5 16h14M6 16l1-5 2-3h6l2 3 1 5M7 16v2M17 16v2M9 11h6M8 13h.01M16 13h.01"/>',
        'calendar' => '<path d="M7 3v2M17 3v2M4 9h16M5 5h14a1 1 0 0 1 1 1v14H4V6a1 1 0 0 1 1-1Zm3 8h3v3H8v-3Z"/>',
        'ticket' => '<path d="M6 4h12v16H6V4Zm3 4h6M9 12h6M9 16h4"/>',
        'revenue' => '<path d="M12 3v18M16 7.5c-.8-1-2-1.5-4-1.5-2.2 0-4 1.1-4 3s1.8 2.5 4 3 4 1.1 4 3-1.8 3-4 3c-2 0-3.2-.5-4-1.5"/>',
    ];

    return '<svg viewBox="0 0 24 24" aria-hidden="true">' . ($icons[$name] ?? $icons['ticket']) . '</svg>';
};

$appointmentStatusLabels = [
    'pending' => 'Chờ xác nhận',
    'confirmed' => 'Đã xác nhận',
    'checked_in' => 'Đã tiếp nhận',
    'completed' => 'Hoàn thành',
    'rejected' => 'Đã từ chối',
    'cancelled' => 'Đã hủy',
];
$appointmentStatusClasses = [
    'pending' => 'pending',
    'confirmed' => 'confirmed',
    'checked_in' => 'checked-in',
    'completed' => 'completed',
    'rejected' => 'rejected',
    'cancelled' => 'cancelled',
];
$maxStatusCount = 0;
foreach ($stats['appointmentStatuses'] as $appointmentStatus):
    $maxStatusCount = max($maxStatusCount, (int) $appointmentStatus['total']);
endforeach;

ob_start();
?>
<section class="metrics-grid" aria-label="Chỉ số tổng quan">
    <a class="metric metric-link" href="<?= e(url('admin/customers')) ?>">
        <div class="metric-top"><p class="metric-label">Khách hàng</p><span class="metric-icon"><?= $metricIcon('customers') ?></span></div>
        <p class="metric-value"><?= e($stats['customers']) ?></p>
        <p class="metric-note">Tài khoản khách hàng trong hệ thống</p>
    </a>
    <a class="metric metric-link" href="<?= e(url('admin/vehicles')) ?>">
        <div class="metric-top"><p class="metric-label">Phương tiện</p><span class="metric-icon"><?= $metricIcon('vehicle') ?></span></div>
        <p class="metric-value"><?= e($stats['vehicles']) ?></p>
        <p class="metric-note">Xe đã đăng ký</p>
    </a>
    <a class="metric metric-link pending" href="<?= e($pendingUrl) ?>">
        <div class="metric-top"><p class="metric-label">Lịch chờ xử lý</p><span class="metric-icon"><?= $metricIcon('calendar') ?></span></div>
        <p class="metric-value"><?= e($stats['pendingAppointments']) ?></p>
        <p class="metric-note">Cần được xác nhận</p>
    </a>
    <article class="metric">
        <div class="metric-top"><p class="metric-label">Phiếu sửa chữa</p><span class="metric-icon"><?= $metricIcon('ticket') ?></span></div>
        <p class="metric-value"><?= e($stats['activeTickets']) ?></p>
        <p class="metric-note">Đang ở các công đoạn xử lý</p>
    </article>
    <article class="metric revenue">
        <div class="metric-top"><p class="metric-label">Đã thanh toán</p><span class="metric-icon"><?= $metricIcon('revenue') ?></span></div>
        <p class="metric-value"><?= e(number_format($stats['revenue'], 0, ',', '.')) ?> ₫</p>
        <p class="metric-note">Tổng giao dịch thành công</p>
    </article>
</section>

<div class="dashboard-lower">
    <section class="panel dashboard-panel" aria-labelledby="appointment-status-title">
        <div class="panel-head">
            <div>
                <h2 class="panel-title" id="appointment-status-title">Phân bổ lịch hẹn</h2>
                <p class="panel-description">Số lượng theo trạng thái hiện tại</p>
            </div>
            <a class="dashboard-panel-link" href="<?= e(url('admin/appointments')) ?>">Tất cả lịch hẹn →</a>
        </div>
        <div class="panel-body">
            <?php if ($stats['appointmentStatuses'] === []): ?>
                <div class="empty-state">Chưa có dữ liệu lịch hẹn.</div>
            <?php else: ?>
                <div class="chart-list" role="list" aria-label="Số lịch hẹn theo trạng thái">
                    <?php foreach ($stats['appointmentStatuses'] as $appointmentStatus): ?>
                        <?php
                            $statusKey = (string) $appointmentStatus['status'];
                            $statusCount = (int) $appointmentStatus['total'];
                            $barWidth = $maxStatusCount > 0 ? (int) round($statusCount * 100 / $maxStatusCount) : 0;
                        ?>
                        <div class="chart-row <?= e($appointmentStatusClasses[$statusKey] ?? '') ?>" role="listitem">
                            <span class="chart-label"><?= e($appointmentStatusLabels[$statusKey] ?? $statusKey) ?></span>
                            <span class="chart-track" role="img" aria-label="<?= e($statusCount . ' lịch hẹn') ?>"><span style="width:<?= $barWidth ?>%"></span></span>
                            <span class="chart-count"><?= e($statusCount) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="panel dashboard-panel" aria-labelledby="recent-appointments-title">
        <div class="panel-head">
            <div>
                <h2 class="panel-title" id="recent-appointments-title">Lịch hẹn gần đây</h2>
                <p class="panel-description">Các yêu cầu được tạo gần nhất</p>
            </div>
            <a class="dashboard-panel-link" href="<?= e(url('admin/appointments')) ?>">Mở danh sách →</a>
        </div>
        <div class="panel-body">
            <?php if ($stats['recentAppointments'] === []): ?>
                <div class="empty-state">Chưa có lịch hẹn nào.</div>
            <?php else: ?>
                <div class="activity-list">
                    <?php foreach ($stats['recentAppointments'] as $appointment): ?>
                        <?php
                            $statusKey = (string) $appointment['status'];
                            $appointmentDate = date('d/m/Y', strtotime((string) $appointment['appointment_date']));
                            $appointmentTime = substr((string) $appointment['appointment_time'], 0, 5);
                        ?>
                        <article class="activity-item">
                            <div class="activity-main">
                                <a class="activity-title" href="<?= e(url('admin/appointments/' . (int) $appointment['id'])) ?>">
                                    <?= e($appointment['appointment_code']) ?> · <?= e($appointment['full_name']) ?>
                                </a>
                                <span class="activity-meta"><?= e($appointment['brand_name'] . ' ' . $appointment['model']) ?> · <?= e($appointment['license_plate']) ?></span>
                            </div>
                            <div class="activity-side">
                                <span class="status <?= e($appointmentStatusClasses[$statusKey] ?? '') ?>"><?= e($appointmentStatusLabels[$statusKey] ?? $statusKey) ?></span>
                                <time class="activity-date" datetime="<?= e($appointment['appointment_date'] . 'T' . $appointmentTime) ?>"><?= e($appointmentDate . ' · ' . $appointmentTime) ?></time>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>
<?php
$content = ob_get_clean();
require BASE_PATH . '/app/Views/admin/_layout.php';
