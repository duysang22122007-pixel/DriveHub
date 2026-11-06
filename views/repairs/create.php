<?php
$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<section class="workflow-page">
    <div class="container workflow-narrow">
        <p class="eyebrow"><span></span>Tiếp nhận phương tiện</p>
        <h1>Lập phiếu sửa chữa từ lịch hẹn #<?= (int) $appointment['id'] ?></h1>
        <?php if ($errors !== []): ?>
            <div class="workflow-alert workflow-alert--error" role="alert">
                <?php foreach ($errors as $error): ?><p><?= $e($error) ?></p><?php endforeach; ?>
            </div>
        <?php endif; ?>
        <article class="workflow-card">
            <h2><?= $e($appointment['service_name']) ?></h2>
            <p><strong>Khách hàng:</strong> <?= $e($appointment['customer_name']) ?> · <?= $e($appointment['customer_phone']) ?></p>
            <p><strong>Xe:</strong> <?= $e($appointment['car_brand'] . ' ' . $appointment['car_model']) ?> · <?= $e($appointment['license_plate']) ?></p>
            <p><strong>Số km hiện tại:</strong> <?= number_format((int) $appointment['current_mileage']) ?> km</p>
            <p><?= nl2br($e($appointment['description'])) ?></p>
        </article>
        <form class="workflow-card workflow-form" method="post" action="<?= BASE_URL ?>?action=repair-order-create&amp;appointment_id=<?= (int) $appointment['id'] ?>">
            <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
            <input type="hidden" name="appointment_id" value="<?= (int) $appointment['id'] ?>">
            <button class="btn btn--primary" type="submit">Xác nhận tiếp nhận và tạo phiếu</button>
            <a class="workflow-link" href="<?= BASE_URL ?>?action=admin-appointments">Hủy, quay lại lịch hẹn</a>
        </form>
    </div>
</section>
