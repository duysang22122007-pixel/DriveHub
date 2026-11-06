<?php
$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<section class="workflow-page">
    <div class="container">
        <p class="eyebrow"><span></span>Khách hàng</p>
        <h1>Xe của tôi</h1>
        <?php if ($cars === []): ?>
            <p class="workflow-empty">Bạn chưa có xe. Thông tin xe sẽ được lưu cùng lịch hẹn đầu tiên.</p>
        <?php else: ?>
            <div class="workflow-list">
                <?php foreach ($cars as $car): ?>
                    <article class="workflow-card">
                        <h2><?= $e($car['brand'] . ' ' . $car['model']) ?></h2>
                        <p><strong>Biển số:</strong> <?= $e($car['license_plate']) ?></p>
                        <p><strong>Số km hiện tại:</strong> <?= number_format((int) $car['current_mileage']) ?> km</p>
                        <a class="workflow-link" href="<?= BASE_URL ?>?action=car-history&amp;id=<?= (int) $car['id'] ?>">Xem lịch sử bảo dưỡng</a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <section class="workflow-section">
            <h2>Lịch sử sửa chữa gần đây</h2>
            <?php if ($history === []): ?>
                <p class="workflow-empty">Chưa có sửa chữa hoặc bảo dưỡng hoàn tất.</p>
            <?php else: ?>
                <div class="workflow-list">
                    <?php foreach ($history as $entry): ?>
                        <article class="workflow-card">
                            <h3><?= $e($entry['car_brand'] . ' ' . $entry['car_model']) ?> · <?= $e($entry['license_plate']) ?></h3>
                            <p><?= $e(date('d/m/Y', strtotime($entry['maintenance_date']))) ?> · <?= number_format((int) $entry['mileage']) ?> km</p>
                            <a class="workflow-link" href="<?= BASE_URL ?>?action=car-history&amp;id=<?= (int) $entry['car_id'] ?>">Mở timeline xe</a>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</section>
