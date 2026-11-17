<?php

declare(strict_types=1);

$pageTitle = 'Phương tiện';
$activeNav = 'vehicles';
$pageEyebrow = 'Vận hành';
$pageDescription = 'Chọn khách hàng để xem và quản lý chính xác các phương tiện thuộc tài khoản đó.';
$pageStyles = '.data-table{min-width:820px}.customer-select{display:flex;align-items:end;gap:12px;flex-wrap:wrap}.customer-select .filter-field{min-width:min(100%,360px)}';
if ($selectedCustomer !== null) {
    $pageActions = '<a class="button secondary" href="' . e(url('admin/vehicles')) . '">← Danh sách khách hàng</a>'
        . '<a class="button" href="' . e(url('admin/vehicles/' . (int) $selectedCustomer['id'] . '/create')) . '">+ Thêm phương tiện</a>';
}

ob_start();
?>
<?php if (is_string($success) && $success !== ''): ?><p class="notice" role="status"><?= e($success) ?></p><?php endif; ?>
<?php if (is_string($error) && $error !== ''): ?><p class="notice error" role="alert"><?= e($error) ?></p><?php endif; ?>
<section class="panel fleet-panel">
    <div class="panel-head">
        <div>
            <h2 class="panel-title"><?= $selectedCustomer === null ? 'Chọn khách hàng' : 'Phương tiện của ' . e($selectedCustomer['full_name']) ?></h2>
            <p class="panel-description"><?= $selectedCustomer === null ? 'Danh sách khách hàng được lấy từ dữ liệu tài khoản hiện có.' : e($selectedCustomer['email']) . ' · ' . e($selectedCustomer['phone'] ?? 'Chưa có số điện thoại') ?></p>
        </div>
    </div>
    <div class="panel-body">
        <?php if ($selectedCustomer === null): ?>
            <form method="get" action="<?= e(url('admin/vehicles')) ?>" class="filter-bar customer-select">
                <div class="filter-field">
                    <label for="customer-id">Khách hàng</label>
                    <select id="customer-id" name="customer_id" required>
                        <option value="">-- Chọn khách hàng --</option>
                        <?php foreach ($customers as $customer): ?>
                            <option value="<?= e((int) $customer['id']) ?>"><?= e($customer['full_name']) ?> · <?= e($customer['email']) ?> (<?= e((int) $customer['vehicle_count']) ?> xe)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-actions"><button type="submit" class="btn-search">Xem phương tiện</button></div>
            </form>
            <div class="table-wrap">
                <?php if ($customers === []): ?>
                    <div class="empty-state">Chưa có khách hàng nào trong hệ thống.</div>
                <?php else: ?>
                    <table class="data-table">
                        <thead><tr><th>Khách hàng</th><th>Email</th><th>Điện thoại</th><th>Số phương tiện</th><th>Thao tác</th></tr></thead>
                        <tbody>
                        <?php foreach ($customers as $customer): ?>
                            <tr>
                                <td><span class="cell-main"><?= e($customer['full_name']) ?></span></td>
                                <td><?= e($customer['email']) ?></td>
                                <td><?= e($customer['phone'] ?? '—') ?></td>
                                <td><?= e((int) $customer['vehicle_count']) ?></td>
                                <td><a class="link" href="<?= e(url('admin/vehicles') . '?' . http_build_query(['customer_id' => (int) $customer['id']])) ?>">Quản lý xe</a></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        <?php else: ?>
        <div class="table-wrap">
            <?php if ($vehicles === []): ?>
                <div class="empty-state">Khách hàng này chưa đăng ký phương tiện. <a class="link" href="<?= e(url('admin/vehicles/' . (int) $selectedCustomer['id'] . '/create')) ?>">Thêm phương tiện</a></div>
            <?php else: ?>
                <table class="data-table">
                    <thead><tr><th>Biển số</th><th>Phương tiện</th><th>Năm</th><th>Số km</th><th>Thao tác</th></tr></thead>
                    <tbody>
                    <?php foreach ($vehicles as $vehicle): ?>
                        <tr>
                            <td><span class="cell-main"><?= e($vehicle['license_plate']) ?></span></td>
                            <td><?= e($vehicle['brand_name']) ?> <?= e($vehicle['model']) ?></td>
                            <td><?= e($vehicle['manufacture_year'] ?: '—') ?></td>
                            <td><?= e(number_format((int) $vehicle['current_mileage'], 0, ',', '.')) ?></td>
                            <td><a class="link" href="<?= e(url('admin/vehicles/' . (int) $selectedCustomer['id'] . '/' . (int) $vehicle['id'])) ?>">Chi tiết</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php
$content = ob_get_clean();
require BASE_PATH . '/app/Views/admin/_layout.php';
