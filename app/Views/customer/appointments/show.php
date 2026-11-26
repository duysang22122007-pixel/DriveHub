<?php

declare(strict_types=1);

$type = $appointmentTypeLabels[$appointment['appointment_type']] ?? 'Lịch cũ';
$statusLabel = $statuses[$appointment['status']] ?? 'Chưa xác định';
$cancellationStatus = $appointment['cancellation_status'] ?? 'none';
$cancellationStatusLabels = [
    'none' => 'Chưa yêu cầu hủy',
    'requested' => 'Muốn hủy lịch (đang chờ Admin xử lý)',
    'approved' => 'Đã hủy',
    'rejected' => 'Yêu cầu hủy bị từ chối',
];
$cancellationRequestedAt = $appointment['cancellation_requested_at'] === null
    ? null
    : date('d/m/Y H:i', strtotime((string) $appointment['cancellation_requested_at']));
$cancellationDecidedAt = $appointment['cancellation_decided_at'] === null
    ? null
    : date('d/m/Y H:i', strtotime((string) $appointment['cancellation_decided_at']));
$maintenanceStatus = $maintenanceStatuses[$appointment['maintenance_ticket_status'] ?? ''] ?? null;
$date = date('d/m/Y', strtotime((string) $appointment['appointment_date']));
$time = substr((string) $appointment['appointment_time'], 0, 5);
$referenceTotal = array_sum(array_map(
    static fn (array $service): float => (float) $service['price'] * (int) $service['quantity'],
    $services
));
$hasCompleteReferencePrices = $services !== []
    && count(array_filter($services, static fn (array $service): bool => (float) $service['price'] > 0)) === count($services);

?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Chi tiết lịch hẹn <?= e($appointment['appointment_code']) ?> | AutoCare Hub</title>
    <?php require __DIR__ . '/_styles.php'; ?>
</head>
<body>
<?php
$activeCustomerNav = 'appointments';
require __DIR__ . '/../../partials/customer-header.php';
?>
<main>
    <div class="page-heading">
        <div><span class="eyebrow">Thông tin yêu cầu</span><h1>Lịch <?= e($appointment['appointment_code']) ?></h1><p>Thông tin này chỉ hiển thị trong tài khoản đã gửi lịch.</p></div>
        <a class="button secondary" href="<?= e(url('appointments')) ?>">Lịch hẹn của tôi</a>
    </div>
    <?php if (is_string($success) && $success !== ''): ?><div class="notice" role="status"><?= e($success) ?></div><?php endif; ?>
    <?php if (is_string($error ?? null) && $error !== ''): ?><div class="notice error" role="alert"><?= e($error) ?></div><?php endif; ?>
    <div class="layout">
        <section class="panel">
            <h2>Trạng thái</h2>
            <p style="display:flex;flex-wrap:wrap;gap:8px;align-items:center">
                <?php if ($cancellationStatus === 'requested' && in_array($appointment['status'], ['pending', 'confirmed'], true)): ?>
                    <span class="status want-cancel">Muốn hủy lịch</span>
                <?php endif; ?>
                <span class="status <?= e($appointment['status']) ?>"><?= e($statusLabel) ?></span>
            </p>
            <?php if ($cancellationStatus !== 'none'): ?>
                <div class="notice <?= $cancellationStatus === 'rejected' ? 'error' : '' ?>" role="status">
                    <strong><?= e($cancellationStatusLabels[$cancellationStatus] ?? 'Yêu cầu hủy chưa xác định') ?></strong>
                    <?php if ($cancellationRequestedAt !== null): ?><br>Gửi lúc <?= e($cancellationRequestedAt) ?><?php endif; ?>
                    <?php if ($cancellationDecidedAt !== null): ?><br>Cập nhật lúc <?= e($cancellationDecidedAt) ?><?php endif; ?>
                    <?php if (is_string($appointment['cancellation_reason']) && trim($appointment['cancellation_reason']) !== ''): ?><br>Lý do: <?= nl2br(e($appointment['cancellation_reason'])) ?><?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if ($maintenanceStatus !== null): ?><p>Phiếu sửa chữa: <?= e($maintenanceStatus) ?></p><?php endif; ?>
            <?php if ($appointment['status'] === 'rejected' && is_string($appointment['rejection_reason'] ?? null) && trim($appointment['rejection_reason']) !== ''): ?>
                <div class="notice error" role="status"><strong>Lý do từ chối:</strong><br><?= nl2br(e($appointment['rejection_reason'])) ?></div>
            <?php endif; ?>
            <p class="panel-intro">Thời gian hiển thị là đề xuất của bạn. Chỉ được xác nhận khi trạng thái lịch thay đổi sau phản hồi của gara.</p>

            <h2>Thông tin đặt lịch</h2>
            <dl class="summary-list">
                <div class="summary-row"><dt>Hình thức</dt><dd><?= e($type) ?></dd></div>
                <div class="summary-row"><dt>Xe</dt><dd><?= e($appointment['brand_name'] . ' ' . $appointment['model']) ?><br><?= e($appointment['license_plate']) ?> · Năm <?= e($appointment['manufacture_year'] ?: 'Chưa cập nhật') ?></dd></div>
                <div class="summary-row"><dt>Ngày, giờ mong muốn</dt><dd><?= e($date) ?> · <?= e($time) ?></dd></div>
                <div class="summary-row"><dt>Thông tin liên hệ</dt><dd><?= e($customer['full_name'] ?? '') ?><br><?= e($customer['phone'] ?? '') ?><br><?= e($customer['email'] ?? '') ?></dd></div>
                <div class="summary-row">
                    <dt>Dịch vụ</dt>
                    <dd>
                        <?php if ($services === []): ?>Chưa chọn dịch vụ; gara sẽ liên hệ làm rõ nhu cầu.<?php else: ?>
                            <?php foreach ($services as $service): ?>
                                <?= e($service['service_name']) ?> · <?= (float) $service['price'] > 0 ? 'Từ ' . e(number_format((float) $service['price'], 0, ',', '.')) . ' ₫' : 'Chưa có giá tham khảo' ?>
                                <?= (int) $service['quantity'] > 1 ? '× ' . (int) $service['quantity'] : '' ?><br>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </dd>
                </div>
                <div class="summary-row"><dt>Mô tả và ghi chú</dt><dd><?= $appointment['note'] !== null && $appointment['note'] !== '' ? nl2br(e($appointment['note'])) : 'Chưa có ghi chú.' ?></dd></div>
            </dl>
            <div class="summary-total">Tổng khởi điểm tham khảo <span><?= !$hasCompleteReferencePrices ? 'Chưa đủ giá tham khảo' : e(number_format($referenceTotal, 0, ',', '.') . ' ₫') ?></span></div>
            <p class="summary-note">Đây là giá tham khảo đã lưu tại thời điểm gửi; không thay thế báo giá cuối cùng của gara.</p>

            <?php if ($invoice !== null): ?>
                <hr style="border:0;border-top:1px solid #edf1f5;margin:24px 0">
                <h2>Hóa đơn &amp; Thanh toán</h2>
                <dl class="summary-list">
                    <div class="summary-row"><dt>Mã hóa đơn</dt><dd><strong><?= e($invoice['invoice_code']) ?></strong> · <span class="status <?= $invoice['status'] === 'paid' ? 'completed' : ($invoice['status'] === 'cancelled' ? 'cancelled' : '') ?>"><?= e($invoiceStatuses[$invoice['status']] ?? $invoice['status']) ?></span></dd></div>
                    <?php if ($invoiceDetails !== []): ?>
                        <div class="summary-row">
                            <dt>Chi tiết hóa đơn</dt>
                            <dd>
                                <?php foreach ($invoiceDetails as $line): ?>
                                    <?= e($line['item_name']) ?> (×<?= (int) $line['quantity'] ?>) · <strong><?= e(number_format((float) $line['total_price'], 0, ',', '.')) ?> ₫</strong><br>
                                <?php endforeach; ?>
                            </dd>
                        </div>
                    <?php endif; ?>
                    <div class="summary-row"><dt>Tổng thanh toán</dt><dd><strong><?= e(number_format((float) $invoice['total_amount'], 0, ',', '.')) ?> ₫</strong> (Đã thanh toán: <?= e(number_format((float) $invoice['amount_paid'], 0, ',', '.')) ?> ₫)</dd></div>
                    <?php if ($payments !== []): ?>
                        <div class="summary-row">
                            <dt>Lịch sử giao dịch</dt>
                            <dd>
                                <?php foreach ($payments as $pay): ?>
                                    <?= e($pay['payment_code']) ?> · <?= e($paymentMethods[$pay['payment_method']] ?? $pay['payment_method']) ?> · <strong><?= e(number_format((float) $pay['amount'], 0, ',', '.')) ?> ₫</strong>
                                    <?= !empty($pay['paid_at']) ? ' (' . e(date('d/m/Y H:i', strtotime((string) $pay['paid_at']))) . ')' : '' ?><br>
                                <?php endforeach; ?>
                            </dd>
                        </div>
                    <?php endif; ?>
                </dl>

                <?php if ($invoice['status'] === 'paid'): ?>
                    <div id="review-form" style="margin-top:22px;padding:18px;border:1px solid #c9f0d9;border-radius:12px;background:#f7fcf9">
                        <h2 style="margin:0 0 8px">Biểu mẫu đánh giá dịch vụ</h2>
                        <?php if ($review !== null): ?>
                            <?php $stars = max(1, min(5, (int) $review['rating'])); ?>
                            <p style="margin:0 0 8px;color:#067647;font-weight:700">Cảm ơn bạn đã gửi đánh giá! (<?= e(date('d/m/Y H:i', strtotime((string) $review['created_at']))) ?>)</p>
                            <p style="margin:0 0 6px;font-size:1.15rem;color:#d97706;letter-spacing:.08em"><?= str_repeat('★', $stars) . str_repeat('☆', 5 - $stars) ?> <span style="font-size:.9rem;color:#123b5d;font-weight:700">(<?= $stars ?>/5)</span></p>
                            <?php if (is_string($review['comment']) && trim($review['comment']) !== ''): ?>
                                <p style="margin:0;color:#253f55;line-height:1.6"><?= nl2br(e($review['comment'])) ?></p>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="muted" style="margin:0 0 14px">
                                Hóa đơn của bạn đã được thanh toán hoàn tất<?= $reviewRequestSentAt !== null ? ' (biểu mẫu được gửi lúc ' . e(date('d/m/Y H:i', strtotime((string) $reviewRequestSentAt))) . ')' : '' ?>. Hãy đánh giá mức độ hài lòng của bạn về chất lượng dịch vụ tại AutoCare Hub.
                            </p>
                            <form method="post" action="<?= e(url('appointments/' . (int) $appointment['id'] . '/review')) ?>">
                                <?= csrf_field() ?>
                                <div style="margin-bottom:12px">
                                    <span style="display:block;font-size:.86rem;font-weight:700;color:#123b5d;margin-bottom:6px">Mức độ hài lòng <span style="color:#b42318">*</span></span>
                                    <div style="display:flex;flex-wrap:wrap;gap:8px">
                                        <?php
                                            $ratingLabels = [
                                                5 => '5 ★ · Rất hài lòng',
                                                4 => '4 ★ · Hài lòng',
                                                3 => '3 ★ · Bình thường',
                                                2 => '2 ★ · Chưa hài lòng',
                                                1 => '1 ★ · Không hài lòng',
                                            ];
                                        ?>
                                        <?php foreach ($ratingLabels as $rVal => $rText): ?>
                                            <label style="display:inline-flex;align-items:center;gap:6px;padding:7px 11px;border:1px solid #cbd6e1;border-radius:8px;background:#fff;cursor:pointer;font-size:.84rem;font-weight:600">
                                                <input type="radio" name="rating" value="<?= $rVal ?>" <?= $rVal === 5 ? 'checked' : '' ?> required>
                                                <span><?= e($rText) ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div style="margin-bottom:12px">
                                    <label for="review-comment" style="display:block;font-size:.86rem;font-weight:700;color:#123b5d;margin-bottom:6px">Nhận xét chi tiết (không bắt buộc)</label>
                                    <textarea id="review-comment" name="comment" maxlength="2000" placeholder="Chia sẻ cảm nhận về thái độ phục vụ, tay nghề kỹ thuật viên, thời gian bàn giao..." style="width:100%;min-height:90px;padding:9px 11px;border:1px solid #cbd6e1;border-radius:8px;font:inherit;font-size:.9rem;resize:vertical"></textarea>
                                </div>
                                <button class="button" type="submit">Gửi đánh giá dịch vụ</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </section>
        <aside class="aside">
            <?php if (in_array($appointment['status'], ['pending', 'confirmed'], true)): ?>
                <?php $canImmediateCancel = empty($appointment['maintenance_ticket_id']); ?>
                <section class="panel">
                    <h2>Hủy lịch hẹn</h2>
                    <?php if ($cancellationStatus === 'requested'): ?>
                        <p class="muted" style="margin:0 0 12px">Lịch đang ở trạng thái <strong>Muốn hủy lịch</strong> và đã hiển thị bên Admin.</p>
                        <?php if ($canImmediateCancel): ?>
                            <form method="post" action="<?= e(url('appointments/' . (int) $appointment['id'] . '/cancel')) ?>" onsubmit="return confirm('Xác nhận hủy lịch luôn ngay bây giờ? Trạng thái ở cả bạn và Admin sẽ chuyển thành Đã hủy.');" style="margin-bottom:8px">
                                <?= csrf_field() ?>
                                <input type="hidden" name="cancel_mode" value="immediate">
                                <button class="button" type="submit" style="width:100%;background:#b42318;border-color:#b42318">Hủy lịch luôn</button>
                            </form>
                        <?php endif; ?>
                        <form method="post" action="<?= e(url('appointments/' . (int) $appointment['id'] . '/cancel-withdraw')) ?>" onsubmit="return confirm('Bạn muốn giữ lại lịch hẹn này và bỏ trạng thái Muốn hủy lịch?');">
                            <?= csrf_field() ?>
                            <button class="button secondary" type="submit" style="width:100%">Giữ lại lịch hẹn</button>
                        </form>
                    <?php else: ?>
                        <p class="muted" style="margin:0 0 12px">Bạn có thể cập nhật trạng thái <strong>Muốn hủy lịch</strong> để gara nắm thông tin, hoặc bấm <strong>Hủy lịch luôn</strong> (khi xe chưa tiếp nhận) để hủy ngay trên cả hệ thống Admin.</p>
                        <form method="post" action="<?= e(url('appointments/' . (int) $appointment['id'] . '/cancel')) ?>">
                            <?= csrf_field() ?>
                            <div style="margin-bottom:10px">
                                <label style="display:block;margin-bottom:4px;font-size:.8rem;font-weight:700;color:#526678">Lý do hủy (không bắt buộc)</label>
                                <textarea name="cancellation_reason" maxlength="500" placeholder="Nhập lý do hủy nếu có…" style="width:100%;min-height:80px;padding:8px 10px;border:1px solid #d1dbe5;border-radius:8px;font:inherit;font-size:.86rem;resize:vertical;box-sizing:border-box"><?= $cancellationStatus === 'rejected' ? e($appointment['cancellation_reason']) : '' ?></textarea>
                            </div>
                            <div style="display:grid;gap:8px">
                                <button class="button secondary" type="submit" name="cancel_mode" value="request" style="width:100%;background:#fff1e6;color:#c2410c;border-color:#fed7aa" onclick="return confirm('Cập nhật trạng thái Muốn hủy lịch cho lịch hẹn này?');">Cập nhật: Muốn hủy lịch</button>
                                <?php if ($canImmediateCancel): ?>
                                    <button class="button" type="submit" name="cancel_mode" value="immediate" style="width:100%;background:#b42318;border-color:#b42318" onclick="return confirm('Bạn có chắc muốn hủy lịch ngay lập tức? Lịch sẽ chuyển sang Đã hủy ở cả phía Admin.');">Hủy lịch luôn</button>
                                <?php endif; ?>
                            </div>
                        </form>
                    <?php endif; ?>
                </section>
            <?php endif; ?>
            <section class="panel">
                <h2>Bạn cần hỗ trợ?</h2>
                <?php if (is_string($garage['phone'] ?? null) && $garage['phone'] !== ''): ?>
                    <p class="muted">Liên hệ gara qua số điện thoại đã cấu hình:</p>
                    <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $garage['phone'])) ?>"><?= e($garage['phone']) ?></a>
                <?php else: ?>
                    <p class="muted">Gara chưa cấu hình số điện thoại hỗ trợ. Bạn có thể gửi yêu cầu mới để bổ sung thông tin.</p>
                <?php endif; ?>
                <p><a href="<?= e(url('appointments/create')) ?>">Tạo yêu cầu đặt lịch khác</a></p>
            </section>
        </aside>
    </div>
</main>
<footer class="site-footer">AutoCare Hub · Cảm ơn bạn đã gửi yêu cầu.</footer>
</body>
</html>
