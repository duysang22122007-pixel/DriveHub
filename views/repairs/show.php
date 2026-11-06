<?php
$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$statusLabels = ['received' => 'Đã tiếp nhận', 'in_progress' => 'Đang sửa chữa', 'completed' => 'Hoàn tất', 'cancelled' => 'Đã hủy'];
$isAdmin = current_admin() !== null;
?>
<section class="workflow-page">
    <div class="container workflow-narrow">
        <p class="eyebrow"><span></span>Phiếu sửa chữa</p>
        <h1>Phiếu #<?= (int) $order['id'] ?></h1>
        <?php if ($errors !== []): ?>
            <div class="workflow-alert workflow-alert--error" role="alert">
                <?php foreach ($errors as $error): ?><p><?= $e($error) ?></p><?php endforeach; ?>
            </div>
        <?php endif; ?>
        <article class="workflow-card">
            <div class="workflow-card__heading">
                <h2><?= $e($order['car_brand'] . ' ' . $order['car_model']) ?> · <?= $e($order['license_plate']) ?></h2>
                <span class="workflow-status"><?= $e($statusLabels[$order['status']] ?? $order['status']) ?></span>
            </div>
            <p><strong>Khách hàng:</strong> <?= $e($order['customer_name']) ?></p>
            <p><strong>Tiếp nhận:</strong> <?= $e(date('d/m/Y H:i', strtotime($order['received_date']))) ?> · <?= number_format((int) $order['received_mileage']) ?> km</p>
            <p><strong>Mô tả:</strong><br><?= nl2br($e($order['problem_description'])) ?></p>
            <?php if ($order['diagnosis']): ?><p><strong>Kết quả:</strong><br><?= nl2br($e($order['diagnosis'])) ?></p><?php endif; ?>
            <?php if ($order['completed_mileage'] !== null): ?><p><strong>Số km khi hoàn tất:</strong> <?= number_format((int) $order['completed_mileage']) ?> km</p><?php endif; ?>
            <?php if ($order['details'] !== []): ?>
                <h3>Dịch vụ / hạng mục</h3>
                <ul>
                    <?php foreach ($order['details'] as $detail): ?>
                        <li><?= $e($detail['service_name']) ?><?php if ($detail['note']): ?> — <?= $e($detail['note']) ?><?php endif; ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </article>

        <?php if ($isAdmin && !in_array($order['status'], ['completed', 'cancelled'], true)): ?>
            <?php if ($order['status'] === 'received'): ?>
                <form class="workflow-card workflow-actions" method="post" action="<?= BASE_URL ?>?action=repair-order&amp;id=<?= (int) $order['id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
                    <button class="workflow-button" type="submit" name="operation" value="in_progress">Bắt đầu sửa chữa</button>
                </form>
            <?php endif; ?>
            <form class="workflow-card workflow-form" method="post" action="<?= BASE_URL ?>?action=repair-order&amp;id=<?= (int) $order['id'] ?>">
                <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
                <h2>Hoàn thành sửa chữa</h2>
                <label>Số km hiện tại
                    <input type="number" name="mileage" min="<?= max((int) $order['current_mileage'], (int) $order['received_mileage']) ?>" value="<?= max((int) $order['current_mileage'], (int) $order['received_mileage']) ?>" required>
                </label>
                <label>Kết quả / chẩn đoán
                    <textarea name="diagnosis" rows="4" maxlength="5000"><?= $e($order['diagnosis'] ?? '') ?></textarea>
                </label>
                <p class="workflow-help">Xác nhận sẽ đồng thời hoàn tất phiếu, cập nhật lịch hẹn, số km xe và lịch sử bảo dưỡng.</p>
                <button class="btn btn--primary" type="submit" name="operation" value="complete">Hoàn thành sửa chữa</button>
            </form>
        <?php endif; ?>
        <p class="workflow-actions">
            <a class="workflow-link" href="<?= BASE_URL ?>?action=<?= $isAdmin ? 'repair-orders' : 'my-appointments' ?>">Quay lại</a>
            <?php if (!$isAdmin): ?><a class="workflow-link" href="<?= BASE_URL ?>?action=car-history&amp;id=<?= (int) $order['car_id'] ?>">Xem lịch sử xe</a><?php endif; ?>
        </p>
    </div>
</section>
