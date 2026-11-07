<?php
$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$repairStatusLabels = ['received' => 'Đã tiếp nhận', 'in_progress' => 'Đang sửa chữa', 'completed' => 'Hoàn tất', 'cancelled' => 'Đã hủy'];
?>
<section class="workflow-page">
    <div class="container">
        <p class="eyebrow"><span></span>Khu vực quản trị</p>
        <h1><?= $e($customer['full_name']) ?></h1>
        <?php if ($errors !== []): ?>
            <div class="workflow-alert workflow-alert--error" role="alert">
                <?php foreach ($errors as $error): ?><p><?= $e($error) ?></p><?php endforeach; ?>
            </div>
        <?php endif; ?>
        <article class="workflow-card">
            <div class="workflow-card__heading">
                <h2>Thông tin tài khoản</h2>
                <span class="workflow-status"><?= (int) $customer['status'] === 1 ? 'Đang hoạt động' : 'Đã khóa' ?></span>
            </div>
            <p><strong>Email:</strong> <?= $e($customer['email']) ?></p>
            <p><strong>Số điện thoại:</strong> <?= $e($customer['phone']) ?></p>
            <p><strong>Địa chỉ:</strong> <?= $e($customer['address'] ?: 'Chưa cập nhật') ?></p>
            <form method="post" action="<?= BASE_URL ?>?action=admin-customer&amp;id=<?= (int) $customer['customer_id'] ?>" onsubmit="return confirm('Bạn có chắc muốn <?= (int) $customer['status'] === 1 ? 'khóa' : 'mở khóa' ?> tài khoản này?');">
                <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
                <input type="hidden" name="status" value="<?= (int) $customer['status'] === 1 ? 0 : 1 ?>">
                <button class="workflow-button workflow-button--muted" type="submit"><?= (int) $customer['status'] === 1 ? 'Khóa tài khoản khách hàng' : 'Mở khóa tài khoản khách hàng' ?></button>
            </form>
        </article>

        <section class="workflow-section">
            <h2>Phương tiện đã đăng ký</h2>
            <?php if ($cars === []): ?>
                <p class="workflow-empty">Khách hàng chưa đăng ký phương tiện.</p>
            <?php else: ?>
                <div class="workflow-list">
                    <?php foreach ($cars as $car): ?>
                        <article class="workflow-card">
                            <h3><?= $e($car['brand'] . ' ' . $car['model']) ?> · <?= $e($car['license_plate']) ?></h3>
                            <p><strong>Năm sản xuất:</strong> <?= $e($car['manufacture_year'] ?? 'Chưa cập nhật') ?></p>
                            <p><strong>Số km hiện tại:</strong> <?= number_format((int) $car['current_mileage']) ?> km</p>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="workflow-section">
            <h2>Lịch sử sửa chữa</h2>
            <?php if ($repairs === []): ?>
                <p class="workflow-empty">Khách hàng chưa có phiếu sửa chữa.</p>
            <?php else: ?>
                <div class="workflow-list">
                    <?php foreach ($repairs as $repair): ?>
                        <article class="workflow-card">
                            <div class="workflow-card__heading">
                                <h3>Phiếu #<?= (int) $repair['id'] ?> · <?= $e($repair['license_plate']) ?></h3>
                                <span class="workflow-status"><?= $e($repairStatusLabels[$repair['status']] ?? $repair['status']) ?></span>
                            </div>
                            <p><strong>Ngày tiếp nhận:</strong> <?= $e(date('d/m/Y H:i', strtotime($repair['received_date']))) ?></p>
                            <p><strong>Hạng mục:</strong> <?= $e($repair['service_names'] ?: 'Chưa có hạng mục') ?></p>
                            <p><?= nl2br($e($repair['problem_description'])) ?></p>
                            <a class="workflow-link" href="<?= BASE_URL ?>?action=repair-order&amp;id=<?= (int) $repair['id'] ?>">Xem phiếu sửa chữa</a>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
        <p><a class="workflow-link" href="<?= BASE_URL ?>?action=admin-customers">← Danh sách khách hàng</a></p>
    </div>
</section>
