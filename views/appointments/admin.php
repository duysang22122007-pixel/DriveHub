<?php
$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$statusLabels = [
    'pending' => 'Chờ xác nhận',
    'confirmed' => 'Đã xác nhận',
    'checked_in' => 'Đã tiếp nhận',
    'completed' => 'Hoàn tất',
    'cancelled' => 'Đã hủy',
];
?>
<section class="workflow-page">
    <div class="container">
        <p class="eyebrow"><span></span>Quản trị viên</p>
        <h1>Quản lý lịch hẹn</h1>
        <p class="workflow-actions"><a class="btn btn--primary" href="<?= BASE_URL ?>?action=repair-orders">Danh sách phiếu sửa chữa</a></p>
        <?php if ($errors !== []): ?>
            <div class="workflow-alert workflow-alert--error" role="alert">
                <?php foreach ($errors as $error): ?><p><?= $e($error) ?></p><?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if ($appointments === []): ?>
            <p class="workflow-empty">Chưa có lịch hẹn.</p>
        <?php else: ?>
            <div class="workflow-list">
                <?php foreach ($appointments as $appointment): ?>
                    <article class="workflow-card">
                        <div class="workflow-card__heading">
                            <h2>#<?= (int) $appointment['id'] ?> · <?= $e($appointment['service_name']) ?></h2>
                            <span class="workflow-status"><?= $e($statusLabels[$appointment['status']] ?? $appointment['status']) ?></span>
                        </div>
                        <p><strong>Khách hàng:</strong> <?= $e($appointment['customer_name']) ?> · <?= $e($appointment['customer_phone']) ?></p>
                        <p><strong>Xe:</strong> <?= $e($appointment['car_brand'] . ' ' . $appointment['car_model']) ?> · <?= $e($appointment['license_plate']) ?></p>
                        <p><strong>Ngày giờ:</strong> <?= $e(date('d/m/Y H:i', strtotime($appointment['appointment_date']))) ?></p>
                        <p><?= nl2br($e($appointment['description'])) ?></p>
                        <?php if ($appointment['repair_order_id']): ?>
                            <a class="workflow-link" href="<?= BASE_URL ?>?action=repair-order&amp;id=<?= (int) $appointment['repair_order_id'] ?>">Mở phiếu sửa chữa</a>
                        <?php elseif (in_array($appointment['status'], ['pending', 'confirmed'], true)): ?>
                            <div class="workflow-actions">
                                <?php if ($appointment['status'] === 'pending'): ?>
                                    <form method="post" action="<?= BASE_URL ?>?action=admin-appointments">
                                        <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
                                        <input type="hidden" name="appointment_id" value="<?= (int) $appointment['id'] ?>">
                                        <button class="workflow-button workflow-button--muted" type="submit">Xác nhận lịch</button>
                                    </form>
                                <?php endif; ?>
                                <a class="workflow-button" href="<?= BASE_URL ?>?action=repair-order-create&amp;appointment_id=<?= (int) $appointment['id'] ?>">Tiếp nhận xe</a>
                            </div>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
