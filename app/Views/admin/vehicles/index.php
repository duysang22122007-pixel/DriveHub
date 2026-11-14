<?php

declare(strict_types=1);

$pageTitle = 'Phương tiện';
$activeNav = 'vehicles';
$pageEyebrow = 'Vận hành';
$pageDescription = 'Danh sách xe đã đăng ký và chủ sở hữu theo dữ liệu hệ thống.';
$pageStyles = '.data-table{min-width:820px}';

ob_start();
?>
<section class="panel fleet-panel">
    <div class="panel-head">
        <div>
            <h2 class="panel-title">Danh sách phương tiện</h2>
            <p class="panel-description">Thông tin biển số, dòng xe, số km và chủ sở hữu.</p>
        </div>
    </div>
    <div class="panel-body">
        <div class="table-wrap">
            <?php if ($vehicles === []): ?>
                <div class="empty-state">Chưa có phương tiện nào được đăng ký.</div>
            <?php else: ?>
                <table class="data-table">
                    <thead><tr><th>Biển số</th><th>Phương tiện</th><th>Năm</th><th>Số km</th><th>Chủ xe</th><th>Email</th></tr></thead>
                    <tbody>
                    <?php foreach ($vehicles as $vehicle): ?>
                        <tr>
                            <td><span class="cell-main"><?= e($vehicle['license_plate']) ?></span></td>
                            <td><?= e($vehicle['brand_name']) ?> <?= e($vehicle['model']) ?></td>
                            <td><?= e($vehicle['manufacture_year'] ?: '—') ?></td>
                            <td><?= e(number_format((int) $vehicle['current_mileage'], 0, ',', '.')) ?></td>
                            <td><?= e($vehicle['owner_name']) ?></td>
                            <td><?= e($vehicle['owner_email']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php
$content = ob_get_clean();
require BASE_PATH . '/app/Views/admin/_layout.php';
