<?php
$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$statusLabels = ['received' => 'Đã tiếp nhận', 'in_progress' => 'Đang sửa chữa', 'completed' => 'Hoàn tất', 'cancelled' => 'Đã hủy'];
?>
<section class="workflow-page">
    <div class="container">
        <p class="eyebrow"><span></span>Quản trị viên</p>
        <h1>Phiếu sửa chữa</h1>
        <p><a class="workflow-link" href="<?= BASE_URL ?>?action=admin-appointments">← Quản lý lịch hẹn</a></p>
        <form class="workflow-card workflow-filter" method="get" action="<?= BASE_URL ?>">
            <input type="hidden" name="action" value="repair-orders">
            <label>
                <span>Tìm mã phiếu, tên khách hàng hoặc biển số</span>
                <input type="search" name="search" value="<?= $e($search) ?>" maxlength="100">
            </label>
            <label>
                <span>Trạng thái</span>
                <select name="status">
                    <option value="">Tất cả trạng thái</option>
                    <option value="received"<?= $status === 'received' ? ' selected' : '' ?>>Đã tiếp nhận</option>
                    <option value="in_progress"<?= $status === 'in_progress' ? ' selected' : '' ?>>Đang sửa chữa</option>
                    <option value="completed"<?= $status === 'completed' ? ' selected' : '' ?>>Hoàn tất</option>
                    <option value="cancelled"<?= $status === 'cancelled' ? ' selected' : '' ?>>Đã hủy</option>
                </select>
            </label>
            <div class="workflow-actions">
                <button class="workflow-button" type="submit">Lọc phiếu</button>
                <a class="workflow-link" href="<?= BASE_URL ?>?action=repair-orders">Xóa bộ lọc</a>
            </div>
        </form>
        <?php if ($orders === []): ?>
            <p class="workflow-empty"><?= $search !== '' || $status !== '' ? 'Không tìm thấy phiếu phù hợp.' : 'Chưa có phiếu sửa chữa.' ?></p>
        <?php else: ?>
            <div class="workflow-list">
                <?php foreach ($orders as $order): ?>
                    <article class="workflow-card">
                        <div class="workflow-card__heading">
                            <h2>Phiếu #<?= (int) $order['id'] ?> · <?= $e($order['license_plate']) ?></h2>
                            <span class="workflow-status"><?= $e($statusLabels[$order['status']] ?? $order['status']) ?></span>
                        </div>
                        <p><strong>Khách hàng:</strong> <?= $e($order['customer_name']) ?></p>
                        <p><strong>Xe:</strong> <?= $e($order['car_brand'] . ' ' . $order['car_model']) ?> · <?= $e($order['license_plate']) ?></p>
                        <p><strong>Tiếp nhận:</strong> <?= $e(date('d/m/Y H:i', strtotime($order['received_date']))) ?></p>
                        <p><strong>Tạm tính:</strong> <?= number_format((float) $order['total_cost'], 2, ',', '.') ?> ₫</p>
                        <a class="workflow-link" href="<?= BASE_URL ?>?action=repair-order&amp;id=<?= (int) $order['id'] ?>">Chi tiết phiếu</a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
