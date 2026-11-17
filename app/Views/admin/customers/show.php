<?php

declare(strict_types=1);

$st      = $customer['status'];
$stClass = $st === 'locked' ? 'locked' : ($st === 'inactive' ? 'inactive' : 'active');
$stLabel = $st === 'active' ? 'Đang hoạt động' : ($st === 'locked' ? 'Đã khóa' : 'Không hoạt động');

$pageTitle       = e($customer['full_name']);
$activeNav       = 'customers';
$pageEyebrow     = 'Khách hàng';
$pageDescription = e($customer['email']) . ' · ' . (is_string($customer['phone']) && $customer['phone'] !== '' ? e($customer['phone']) : 'Chưa có SĐT');
$pageActions     = '<a class="button secondary" href="' . e(url('admin/customers')) . '">← Danh sách khách hàng</a>';
$pageStyles      = '.info-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:0}.info-item{padding:12px 0;border-bottom:1px solid #edf1f5}.info-label{margin:0 0 4px;color:var(--muted);font-size:.72rem;font-weight:800;letter-spacing:.04em;text-transform:uppercase}.info-value{margin:0;line-height:1.55;overflow-wrap:anywhere}.vehicle-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:12px}.vehicle-card{padding:14px 16px;border:1px solid var(--line);border-radius:9px;background:#fff}.vehicle-card-title{margin:0 0 6px;color:var(--navy);font-size:.9rem;font-weight:700}.vehicle-card-meta{margin:0;color:var(--muted);font-size:.8rem;line-height:1.6}';

ob_start();
?>
<?php if (is_string($success) && $success !== ''): ?><p class="notice" role="status"><?= e($success) ?></p><?php endif; ?>
<?php if (is_string($error) && $error !== ''): ?><p class="notice error" role="alert"><?= e($error) ?></p><?php endif; ?>

<div class="detail-layout">
    <div>
        <section class="panel">
            <div class="panel-head">
                <div>
                    <h2 class="panel-title">Thông tin tài khoản</h2>
                    <p class="panel-description">Thông tin cá nhân và trạng thái tài khoản.</p>
                </div>
                <span class="status <?= e($stClass) ?>"><?= e($stLabel) ?></span>
            </div>
            <div class="panel-body">
                <dl class="info-grid">
                    <div class="info-item"><dt class="info-label">Họ và tên</dt><dd class="info-value"><strong><?= e($customer['full_name']) ?></strong></dd></div>
                    <div class="info-item"><dt class="info-label">Email</dt><dd class="info-value"><?= e($customer['email']) ?></dd></div>
                    <div class="info-item"><dt class="info-label">Điện thoại</dt><dd class="info-value"><?= e(is_string($customer['phone']) && $customer['phone'] !== '' ? $customer['phone'] : 'Chưa cập nhật') ?></dd></div>
                    <div class="info-item"><dt class="info-label">Ngày tạo</dt><dd class="info-value"><?= e(date('d/m/Y H:i', strtotime((string) $customer['created_at']))) ?></dd></div>
                    <div class="info-item"><dt class="info-label">Số phương tiện</dt><dd class="info-value"><?= (int) $customer['vehicle_count'] ?> xe</dd></div>
                    <div class="info-item"><dt class="info-label">Tổng lịch hẹn</dt><dd class="info-value"><?= (int) $customer['appointment_count'] ?> lịch</dd></div>
                </dl>
            </div>
        </section>

        <section class="panel">
            <div class="panel-head">
                <div>
                    <h2 class="panel-title">Phương tiện đã đăng ký</h2>
                    <p class="panel-description">Danh sách xe thuộc tài khoản này.</p>
                </div>
            </div>
            <div class="panel-body">
                <?php if ($vehicles === []): ?>
                    <p class="muted">Khách hàng chưa đăng ký phương tiện nào.</p>
                <?php else: ?>
                    <div class="vehicle-grid">
                        <?php foreach ($vehicles as $vehicle): ?>
                            <?php
                                $vSt      = $vehicle['status'];
                                $vStClass = $vSt === 'active' ? 'active' : 'inactive';
                                $vStLabel = $vSt === 'active' ? 'Đang dùng' : 'Ngừng dùng';
                            ?>
                            <div class="vehicle-card">
                                <p class="vehicle-card-title">
                                    <?= e($vehicle['brand_name'] . ' ' . $vehicle['model']) ?>
                                    <span class="status <?= e($vStClass) ?>" style="font-size:.68rem;margin-left:6px"><?= e($vStLabel) ?></span>
                                </p>
                                <p class="vehicle-card-meta">
                                    Biển số: <strong><?= e($vehicle['license_plate']) ?></strong><br>
                                    <?php if (is_string($vehicle['manufacture_year']) && $vehicle['manufacture_year'] !== '' && $vehicle['manufacture_year'] !== null): ?>Năm: <?= e($vehicle['manufacture_year']) ?> · <?php endif; ?>
                                    <?= e(number_format((int) $vehicle['current_mileage'], 0, ',', '.')) ?> km
                                </p>
                                <div class="actions" style="margin-top:10px">
                                    <a class="button secondary small" href="<?= e(url('admin/vehicles/' . (int) $vehicle['user_id'] . '/' . (int) $vehicle['id'])) ?>">Xem xe</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <aside class="side-panel">
        <section class="panel">
            <div class="panel-head">
                <div>
                    <h2 class="panel-title">Thao tác tài khoản</h2>
                    <p class="panel-description">Thay đổi trạng thái tài khoản khách hàng.</p>
                </div>
            </div>
            <div class="panel-body">
                <?php if ($customer['status'] === 'active'): ?>
                    <form method="post" action="<?= e(url('admin/customers/' . (int) $customer['id'] . '/status')) ?>" onsubmit="return confirm('Khóa tài khoản khách hàng này?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="status" value="locked">
                        <button class="button danger" type="submit" style="width:100%">Khóa tài khoản</button>
                    </form>
                <?php else: ?>
                    <form method="post" action="<?= e(url('admin/customers/' . (int) $customer['id'] . '/status')) ?>" onsubmit="return confirm('Mở khóa tài khoản khách hàng này?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="status" value="active">
                        <button class="button success-btn" type="submit" style="width:100%">Mở khóa tài khoản</button>
                    </form>
                <?php endif; ?>
            </div>
        </section>

        <section class="panel">
            <div class="panel-head">
                <div>
                    <h2 class="panel-title">Lịch hẹn gần đây</h2>
                    <p class="panel-description">10 lịch hẹn mới nhất của khách hàng.</p>
                </div>
            </div>
            <div class="panel-body">
                <?php if ($appointments === []): ?>
                    <p class="muted">Khách hàng chưa có lịch hẹn nào.</p>
                <?php else: ?>
                    <?php
                        $statusLabels = [
                            'pending'    => ['label' => 'Chờ xác nhận', 'class' => 'pending'],
                            'confirmed'  => ['label' => 'Đã xác nhận',  'class' => 'confirmed'],
                            'rejected'   => ['label' => 'Từ chối',       'class' => 'rejected'],
                            'checked_in' => ['label' => 'Tiếp nhận',     'class' => 'confirmed'],
                            'completed'  => ['label' => 'Hoàn thành',    'class' => 'completed'],
                            'cancelled'  => ['label' => 'Đã hủy',        'class' => 'cancelled'],
                        ];
                    ?>
                    <ul style="margin:0;padding:0;list-style:none;display:grid;gap:10px">
                        <?php foreach ($appointments as $appt): ?>
                            <?php
                                $apptSt      = (string) $appt['status'];
                                $apptInfo    = $statusLabels[$apptSt] ?? ['label' => $apptSt, 'class' => ''];
                                $apptDate    = date('d/m/Y', strtotime((string) $appt['appointment_date']));
                            ?>
                            <li style="padding:10px 12px;border:1px solid var(--line);border-radius:8px;background:#fafcff">
                                <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;margin-bottom:4px">
                                    <a class="link" href="<?= e(url('admin/appointments/' . (int) $appt['id'])) ?>" style="font-size:.82rem;font-weight:700"><?= e($appt['appointment_code']) ?></a>
                                    <span class="status <?= e($apptInfo['class']) ?>" style="font-size:.68rem"><?= e($apptInfo['label']) ?></span>
                                </div>
                                <p style="margin:0;color:var(--muted);font-size:.78rem"><?= e($appt['brand_name'] . ' ' . $appt['model']) ?> · <?= e($apptDate) ?></p>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </section>
    </aside>
</div>
<?php
$content = ob_get_clean();
require BASE_PATH . '/app/Views/admin/_layout.php';
