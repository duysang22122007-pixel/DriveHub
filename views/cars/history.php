<?php
$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<section class="workflow-page">
    <div class="container">
        <p class="eyebrow"><span></span>Lịch sử phương tiện</p>
        <h1><?= $e($car['brand'] . ' ' . $car['model']) ?></h1>
        <p class="workflow-lead"><?= $e($car['license_plate']) ?> · Hiện tại <?= number_format((int) $car['current_mileage']) ?> km</p>
        <p><a class="workflow-link" href="<?= BASE_URL ?>?action=my-cars">← Quay lại xe của tôi</a></p>

        <?php if ($history === []): ?>
            <p class="workflow-empty">Xe này chưa có lịch sử sửa chữa hoặc bảo dưỡng hoàn tất.</p>
        <?php else: ?>
            <ol class="workflow-timeline">
                <?php foreach ($history as $entry): ?>
                    <li class="workflow-timeline__item">
                        <div class="workflow-card">
                            <p class="workflow-date"><?= $e(date('d/m/Y H:i', strtotime($entry['maintenance_date']))) ?></p>
                            <h2><?= $e($entry['service_names'] ?: 'Sửa chữa / bảo dưỡng') ?></h2>
                            <p><strong>Số km:</strong> <?= number_format((int) $entry['mileage']) ?> km</p>
                            <p><?= nl2br($e($entry['description'])) ?></p>
                            <?php if (!empty($entry['service_id'])): ?>
                                <a class="workflow-link" href="<?= BASE_URL ?>?action=booking&amp;car_id=<?= (int) $car['id'] ?>&amp;service_id=<?= (int) $entry['service_id'] ?>">Đặt lịch lại dịch vụ này</a>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>
    </div>
</section>
