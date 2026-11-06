<?php
$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$statusLabels = [
    'pending' => 'Chờ xác nhận',
    'confirmed' => 'Đã xác nhận',
    'checked_in' => 'Đã tiếp nhận xe',
    'completed' => 'Hoàn tất',
    'cancelled' => 'Đã hủy',
];
?>
<section class="workflow-page">
    <div class="container">
        <p class="eyebrow"><span></span>Khách hàng</p>
        <h1>Lịch hẹn của tôi</h1>
        <nav class="workflow-tabs" aria-label="Các nhóm lịch hẹn">
            <a href="#upcoming">Sắp tới / Đang xử lý (<?= count($upcoming) ?>)</a>
            <a href="#history">Lịch sử đặt hẹn (<?= count($history) ?>)</a>
            <a href="<?= BASE_URL ?>?action=appointment-history">Xem timeline các mốc</a>
        </nav>

        <?php if ($flash !== null): ?><div class="workflow-alert"><?= $e($flash) ?></div><?php endif; ?>
        <?php if ($errors !== []): ?>
            <div class="workflow-alert workflow-alert--error" role="alert">
                <?php foreach ($errors as $error): ?><p><?= $e($error) ?></p><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <section id="upcoming" class="workflow-section">
            <h2>Sắp tới / Đang xử lý</h2>
            <?php if ($upcoming === []): ?>
                <p class="workflow-empty">Bạn chưa có lịch hẹn đang xử lý.</p>
            <?php else: ?>
                <div class="workflow-list">
                    <?php foreach ($upcoming as $appointment): ?>
                        <article class="workflow-card">
                            <div class="workflow-card__heading">
                                <h3><?= $e($appointment['service_name']) ?> · <?= $e($appointment['license_plate']) ?></h3>
                                <span class="workflow-status"><?= $e($statusLabels[$appointment['status']] ?? $appointment['status']) ?></span>
                            </div>
                            <p><strong>Thời gian:</strong> <?= $e(date('d/m/Y H:i', strtotime($appointment['appointment_date']))) ?></p>
                            <p><strong>Xe:</strong> <?= $e($appointment['car_brand'] . ' ' . $appointment['car_model']) ?></p>
                            <p><?= nl2br($e($appointment['description'])) ?></p>
                            <?php if (in_array($appointment['status'], ['pending', 'confirmed'], true)): ?>
                                <form method="post" action="<?= BASE_URL ?>?action=my-appointments" onsubmit="return confirm('Bạn có chắc muốn hủy lịch hẹn này?');">
                                    <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
                                    <input type="hidden" name="appointment_id" value="<?= (int) $appointment['id'] ?>">
                                    <button class="workflow-button workflow-button--muted" type="submit" name="operation" value="cancel">Hủy lịch hẹn</button>
                                </form>
                            <?php endif; ?>
                            <?php if ($appointment['repair_order_id'] && $appointment['repair_status'] !== 'completed'): ?>
                                <a class="workflow-link" href="<?= BASE_URL ?>?action=repair-order&amp;id=<?= (int) $appointment['repair_order_id'] ?>">Theo dõi phiếu sửa chữa</a>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section id="history" class="workflow-section">
            <h2>Lịch sử đặt hẹn</h2>
            <?php if ($history === []): ?>
                <p class="workflow-empty">Chưa có lịch hẹn hoàn tất hoặc đã hủy.</p>
            <?php else: ?>
                <div class="workflow-list">
                    <?php foreach ($history as $appointment): ?>
                        <article class="workflow-card">
                            <div class="workflow-card__heading">
                                <h3><?= $e($appointment['service_name']) ?> · <?= $e($appointment['license_plate']) ?></h3>
                                <span class="workflow-status"><?= $e($statusLabels[$appointment['status']] ?? $appointment['status']) ?></span>
                            </div>
                            <p><strong>Thời gian:</strong> <?= $e(date('d/m/Y H:i', strtotime($appointment['appointment_date']))) ?></p>
                            <p><?= nl2br($e($appointment['description'])) ?></p>
                            <?php if ($appointment['status'] === 'completed' && $appointment['repair_order_id']): ?>
                                <a class="workflow-link" href="<?= BASE_URL ?>?action=repair-order&amp;id=<?= (int) $appointment['repair_order_id'] ?>">Xem chi tiết kết quả sửa chữa</a>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <p><a class="btn btn--primary" href="<?= BASE_URL ?>?action=booking">Đặt lịch mới</a></p>
    </div>
</section>
