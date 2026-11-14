<?php

declare(strict_types=1);

$pageTitle = 'Khách hàng';
$activeNav = 'customers';
$pageEyebrow = 'Vận hành';
$pageDescription = 'Danh sách tài khoản khách hàng và phương tiện đã đăng ký.';
$pageStyles = '.data-table{min-width:820px}@media(min-width:821px){.filter-grid{grid-template-columns:minmax(220px,2fr) minmax(170px,1fr) auto}}';
$exportUrl = url('admin/customers') . '?' . http_build_query(['q' => $q, 'status' => $status, 'export' => 'csv']);
$pageActions = '<a class="button secondary" href="' . e($exportUrl) . '">Xuất CSV</a>';

ob_start();
?>
<?php if (is_string($success) && $success !== ''): ?><p class="notice" role="status"><?= e($success) ?></p><?php endif; ?>
<?php if (is_string($error) && $error !== ''): ?><p class="notice error" role="alert"><?= e($error) ?></p><?php endif; ?>

<section class="admin-list customer-list" aria-label="Danh sách khách hàng">
<form method="get" action="<?= e(url('admin/customers')) ?>" class="filter-bar">
    <div class="filter-grid">
        <div class="filter-field">
            <label for="customer-q">Tìm kiếm</label>
            <input id="customer-q" type="search" name="q" value="<?= e($q) ?>" placeholder="Tìm theo tên, email, số điện thoại…" autocomplete="off">
        </div>
        <div class="filter-field">
            <label for="customer-status">Trạng thái</label>
            <select id="customer-status" name="status">
                <option value="" <?= $status === '' ? 'selected' : '' ?>>Tất cả trạng thái</option>
                <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Đang hoạt động</option>
                <option value="locked" <?= $status === 'locked' ? 'selected' : '' ?>>Đã khóa</option>
                <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Không hoạt động</option>
            </select>
        </div>
        <div class="filter-actions">
            <button type="submit" class="btn-search">Tìm kiếm</button>
            <?php if ($q !== '' || $status !== ''): ?><a href="<?= e(url('admin/customers')) ?>" class="btn-reset">Xóa bộ lọc</a><?php endif; ?>
        </div>
    </div>
</form>

<p class="table-meta">
    <?php if ($q !== '' || $status !== ''): ?>
        Tìm thấy <strong><?= count($customers) ?></strong> kết quả
        <?php if ($q !== ''): ?> cho "<strong><?= e($q) ?></strong>"<?php endif; ?>
        <?php if ($status !== ''): ?> · Trạng thái: <strong><?= e($status === 'active' ? 'Đang hoạt động' : ($status === 'locked' ? 'Đã khóa' : 'Không hoạt động')) ?></strong><?php endif; ?>
    <?php else: ?>
        Tổng cộng <strong><?= count($customers) ?></strong> khách hàng
    <?php endif; ?>
</p>

<div class="table-wrap">
    <?php if ($customers === []): ?>
        <div class="empty-state"><?= ($q !== '' || $status !== '') ? 'Không tìm thấy khách hàng phù hợp.' : 'Chưa có tài khoản khách hàng.' ?></div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Khách hàng</th>
                    <th>Email</th>
                    <th>Điện thoại</th>
                    <th>Số xe</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($customers as $customer): ?>
                <?php
                    $st = $customer['status'];
                    $stClass = $st === 'locked' ? 'locked' : ($st === 'inactive' ? 'inactive' : 'active');
                    $stLabel = $st === 'active' ? 'Đang hoạt động' : ($st === 'locked' ? 'Đã khóa' : 'Không hoạt động');
                ?>
                <tr>
                    <td><span class="cell-main"><?= e($customer['full_name']) ?></span></td>
                    <td><?= e($customer['email']) ?></td>
                    <td><?= e($customer['phone'] ?? '—') ?></td>
                    <td><?= e((int) $customer['vehicle_count']) ?></td>
                    <td><span class="status <?= e($stClass) ?>"><?= e($stLabel) ?></span></td>
                    <td><?= e(date('d/m/Y', strtotime((string) $customer['created_at']))) ?></td>
                    <td>
                        <div class="actions">
                            <?php if ($customer['status'] === 'active'): ?>
                                <form method="post" action="<?= e(url('admin/customers/' . (int) $customer['id'] . '/status')) ?>" onsubmit="return confirm('Khóa tài khoản khách hàng này?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="status" value="locked">
                                    <button class="button danger small" type="submit">Khóa</button>
                                </form>
                            <?php else: ?>
                                <form method="post" action="<?= e(url('admin/customers/' . (int) $customer['id'] . '/status')) ?>" onsubmit="return confirm('Mở khóa tài khoản khách hàng này?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="status" value="active">
                                    <button class="button success-btn small" type="submit">Mở khóa</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</section>
<?php
$content = ob_get_clean();
require BASE_PATH . '/app/Views/admin/_layout.php';
