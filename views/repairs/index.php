<?php
$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$statusLabels = ['received' => 'Đã tiếp nhận', 'in_progress' => 'Đang sửa chữa', 'completed' => 'Hoàn tất', 'cancelled' => 'Đã hủy'];
?>
<section class="workflow-page">
    <div class="container">
        <p class="eyebrow"><span></span>Quản trị viên</p>
        <h1>Phiếu sửa chữa</h1>
        <p><a class="workflow-link" href="<?= BASE_URL ?>?action=admin-appointments">← Quản lý lịch hẹn</a></p>
        <?php if ($orders === []): ?>
            <p class="workflow-empty">Chưa có phiếu sửa chữa.</p>
        <?php else: ?>
            <div class="workflow-list">
                <?php foreach ($orders as $order): ?>
                    <article class="workflow-card">
                        <div class="workflow-card__heading">
                            <h2>Phiếu #<?= (int) $order['id'] ?> · <?= $e($order['license_plate']) ?></h2>
                            <span class="workflow-status"><?= $e($statusLabels[$order['status']] ?? $order['status']) ?></span>
                        </div>
                        <p><strong>Khách hàng:</strong> <?= $e($order['customer_name']) ?></p>
                        <p><strong>Tiếp nhận:</strong> <?= $e(date('d/m/Y H:i', strtotime($order['received_date']))) ?></p>
                        <a class="workflow-link" href="<?= BASE_URL ?>?action=repair-order&amp;id=<?= (int) $order['id'] ?>">Chi tiết phiếu</a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
