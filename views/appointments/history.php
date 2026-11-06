<?php
$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$formatDate = static function ($value): string {
    if (!$value) {
        return 'Chưa có';
    }

    return (new DateTimeImmutable((string) $value, new DateTimeZone('Asia/Ho_Chi_Minh')))->format('d/m/Y H:i');
};
$statusLabels = [
    'pending' => 'Đã gửi yêu cầu',
    'confirmed' => 'Lịch hẹn được xác nhận',
    'checked_in' => 'Xe đã được tiếp nhận',
    'completed' => 'Sửa chữa đã hoàn tất',
    'cancelled' => 'Lịch hẹn đã hủy',
];
?>
<section class="workflow-page">
    <div class="container">
        <p class="eyebrow"><span></span>Khách hàng</p>
        <h1>Lịch sử đặt lịch</h1>
        <p class="workflow-lead">Thông tin yêu cầu, thời gian hẹn và các mốc xử lý được ghi nhận cho từng lịch.</p>
        <p class="workflow-actions">
            <a class="workflow-link" href="<?= BASE_URL ?>?action=my-appointments">← Quay lại lịch hẹn</a>
        </p>

        <?php if ($appointments === []): ?>
            <p class="workflow-empty">Bạn chưa có lịch hẹn nào. Lịch đã đặt sẽ xuất hiện tại đây.</p>
        <?php else: ?>
            <div class="workflow-list">
                <?php foreach ($appointments as $appointment): ?>
                    <article class="workflow-card">
                        <div class="workflow-card__heading">
                            <h2>Lịch #<?= (int) $appointment['id'] ?> · <?= $e($appointment['service_name']) ?></h2>
                            <span class="workflow-status"><?= $e($statusLabels[$appointment['status']] ?? $appointment['status']) ?></span>
                        </div>

                        <dl class="appointment-history-details">
                            <div>
                                <dt>Xe</dt>
                                <dd><?= $e($appointment['car_brand'] . ' ' . $appointment['car_model']) ?> · <?= $e($appointment['license_plate']) ?></dd>
                            </div>
                            <div>
                                <dt>Thời gian gửi yêu cầu</dt>
                                <dd><?= $e($formatDate($appointment['created_at'])) ?></dd>
                            </div>
                            <div>
                                <dt>Thời gian hẹn mong muốn</dt>
                                <dd><?= $e($formatDate($appointment['appointment_date'])) ?></dd>
                            </div>
                            <div>
                                <dt>Nội dung</dt>
                                <dd><?= nl2br($e($appointment['description'])) ?></dd>
                            </div>
                        </dl>

                        <h3>Các mốc xử lý</h3>
                        <?php if ($appointment['status_history'] === []): ?>
                            <p class="workflow-empty">
                                Chưa có dữ liệu mốc trạng thái. Các lần cập nhật sau khi bật theo dõi sẽ được lưu tại đây.
                            </p>
                        <?php else: ?>
                            <ol class="workflow-timeline">
                                <?php foreach ($appointment['status_history'] as $event): ?>
                                    <li class="workflow-timeline__item">
                                        <div class="workflow-timeline__event">
                                            <p class="workflow-date"><?= $e($formatDate($event['changed_at'])) ?></p>
                                            <?php $eventLabel = str_starts_with((string) ($event['note'] ?? ''), 'Bắt đầu theo dõi') ? 'Trạng thái hiện tại được ghi nhận' : ($statusLabels[$event['status']] ?? $event['status']); ?>
                                            <strong><?= $e($eventLabel) ?></strong>
                                            <?php if ($event['note']): ?>
                                                <p><?= $e($event['note']) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ol>
                        <?php endif; ?>

                        <?php if ($appointment['repair_order_id']): ?>
                            <p>
                                <a class="workflow-link" href="<?= BASE_URL ?>?action=repair-order&amp;id=<?= (int) $appointment['repair_order_id'] ?>">
                                    <?= $appointment['repair_status'] === 'completed' ? 'Xem kết quả sửa chữa' : 'Theo dõi phiếu sửa chữa' ?>
                                </a>
                            </p>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
