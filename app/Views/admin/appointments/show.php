<?php

declare(strict_types=1);

$pageTitle = 'Chi tiết lịch ' . $appointment['appointment_code'];
$activeNav = 'appointments';
$pageEyebrow = 'Vận hành';

$statusClass = [
    'pending' => 'pending',
    'confirmed' => 'confirmed',
    'rejected' => 'rejected',
    'checked_in' => 'checked-in',
    'completed' => 'completed',
    'cancelled' => 'cancelled',
][$appointment['status']] ?? '';
$typeLabel = $appointmentTypeLabels[$appointment['appointment_type']] ?? 'Lịch cũ';
$statusLabel = $statuses[$appointment['status']] ?? 'Chưa xác định';
$maintenanceStatus = $maintenanceStatuses[$appointment['maintenance_ticket_status'] ?? ''] ?? null;
$appointmentDate = date('d/m/Y', strtotime((string) $appointment['appointment_date']));
$appointmentTime = substr((string) $appointment['appointment_time'], 0, 5);
$serviceTotal = array_sum(array_map(
    static fn (array $service): float => (float) $service['price'] * (int) $service['quantity'],
    $services
));
$hasCompleteReferencePrices = $services !== []
    && count(array_filter($services, static fn (array $service): bool => (float) $service['price'] > 0)) === count($services);
$createdAt = $appointment['created_at'] === null ? '—' : date('d/m/Y H:i', strtotime((string) $appointment['created_at']));
$updatedAt = $appointment['updated_at'] === null ? '—' : date('d/m/Y H:i', strtotime((string) $appointment['updated_at']));
$cancellationRequestedAt = $appointment['cancellation_requested_at'] === null
    ? '—'
    : date('d/m/Y H:i', strtotime((string) $appointment['cancellation_requested_at']));
$cancellationDecidedAt = $appointment['cancellation_decided_at'] === null
    ? '—'
    : date('d/m/Y H:i', strtotime((string) $appointment['cancellation_decided_at']));
$cancellationStatusLabels = [
    'none' => 'Không có yêu cầu hủy',
    'requested' => 'Muốn hủy lịch',
    'approved' => 'Đã xác nhận hủy',
    'rejected' => 'Đã từ chối hủy',
];

$pageDescription = $typeLabel . ' · Tạo lúc ' . $createdAt;
$pageActions = '<a class="button secondary" href="' . e(url('admin/appointments')) . '">← Danh sách lịch hẹn</a>';
$pageStyles = '.service-line{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;padding:7px 0;border-bottom:1px dashed #e7edf3}.service-line:last-of-type{border-bottom:0}.service-total{margin-top:8px;padding-top:9px;border-top:1px solid #e7edf3;color:#123b5d}.field[hidden]{display:none}';

ob_start();
?>
<?php if (is_string($success) && $success !== ''): ?><p class="notice" role="status"><?= e($success) ?></p><?php endif; ?>
<?php if (is_string($error) && $error !== ''): ?><p class="notice error" role="alert"><?= e($error) ?></p><?php endif; ?>
<div class="detail-layout">
    <section class="panel appointment-summary">
        <div class="panel-head">
            <div>
                <h2 class="panel-title">Thông tin lịch hẹn</h2>
                <p class="panel-description">Thông tin khách hàng, phương tiện và nội dung yêu cầu.</p>
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:6px;align-items:center;">
                <?php if (($appointment['cancellation_status'] ?? 'none') === 'requested' && in_array($appointment['status'], ['pending', 'confirmed'], true)): ?>
                    <span class="status want-cancel">Muốn hủy lịch</span>
                <?php endif; ?>
                <span class="status <?= e($statusClass) ?>"><?= e($statusLabel) ?></span>
            </div>
        </div>
        <div class="panel-body">
            <dl class="detail-summary">
                <div class="detail-item"><dt class="detail-label">Khách hàng</dt><dd class="detail-value"><strong><?= e($appointment['full_name']) ?></strong></dd></div>
                <div class="detail-item"><dt class="detail-label">Điện thoại / Email</dt><dd class="detail-value"><?= e($appointment['phone'] ?? 'Chưa cập nhật') ?><br><?= e($appointment['email'] ?? '') ?></dd></div>
                <div class="detail-item"><dt class="detail-label">Phương tiện</dt><dd class="detail-value"><strong><?= e($appointment['brand_name'] . ' ' . $appointment['model']) ?></strong><br>Biển số: <?= e($appointment['license_plate']) ?> · Năm: <?= e($appointment['manufacture_year'] ?: 'Chưa cập nhật') ?></dd></div>
                <div class="detail-item"><dt class="detail-label">Ngày, giờ hẹn</dt><dd class="detail-value"><strong><?= e($appointmentDate) ?></strong> · <?= e($appointmentTime) ?></dd></div>
                <div class="detail-item"><dt class="detail-label">Nội dung / lý do</dt><dd class="detail-value"><?= $appointment['note'] !== null && $appointment['note'] !== '' ? nl2br(e($appointment['note'])) : 'Không có nội dung bổ sung.' ?></dd></div>
                <div class="detail-item"><dt class="detail-label">Dịch vụ khách đã chọn</dt><dd class="detail-value">
                    <?php if ($services === []): ?>Khách chưa chọn dịch vụ.<?php else: ?>
                        <?php foreach ($services as $service): ?>
                            <div class="service-line">
                                <span><?= e($service['service_name']) ?><?= (int) $service['quantity'] > 1 ? ' × ' . (int) $service['quantity'] : '' ?></span>
                                <strong><?= (float) $service['price'] > 0 ? 'Từ ' . e(number_format((float) $service['price'], 0, ',', '.')) . ' ₫' : 'Chưa có giá tham khảo' ?></strong>
                            </div>
                        <?php endforeach; ?>
                        <div class="service-total"><strong>Tổng khởi điểm tham khảo: <?= $hasCompleteReferencePrices ? e(number_format($serviceTotal, 0, ',', '.')) . ' ₫' : 'Chưa đủ giá tham khảo' ?></strong></div>
                    <?php endif; ?>
                </dd></div>
                <?php if ($appointment['status'] === 'rejected' && is_string($appointment['admin_note']) && trim($appointment['admin_note']) !== ''): ?>
                    <div class="detail-item"><dt class="detail-label">Lý do từ chối</dt><dd class="detail-value"><?= nl2br(e($appointment['admin_note'])) ?></dd></div>
                <?php endif; ?>
                <?php if (($appointment['cancellation_status'] ?? 'none') !== 'none'): ?>
                    <div class="detail-item"><dt class="detail-label">Yêu cầu hủy</dt><dd class="detail-value">
                        <strong><?= e($cancellationStatusLabels[$appointment['cancellation_status']] ?? 'Chưa xác định') ?></strong>
                        · gửi lúc <?= e($cancellationRequestedAt) ?>
                        <?php if (is_string($appointment['cancellation_reason']) && trim($appointment['cancellation_reason']) !== ''): ?>
                            <br>Lý do: <?= nl2br(e($appointment['cancellation_reason'])) ?>
                        <?php endif; ?>
                        <?php if ($appointment['cancellation_decided_by'] !== null): ?>
                            <br>Người xử lý (ID): <?= e($appointment['cancellation_decided_by']) ?> · <?= e($cancellationDecidedAt) ?>
                        <?php elseif ($appointment['status'] === 'cancelled' && ($appointment['cancellation_status'] ?? 'none') === 'approved'): ?>
                            <br>Khách hàng đã tự hủy trực tiếp · <?= e($cancellationDecidedAt) ?>
                        <?php endif; ?>
                    </dd></div>
                <?php endif; ?>
                <div class="detail-item"><dt class="detail-label">Cập nhật lần cuối</dt><dd class="detail-value"><?= e($updatedAt) ?></dd></div>
                <?php if (!empty($appointment['ticket_code'])): ?>
                    <div class="detail-item">
                        <dt class="detail-label">Phiếu sửa chữa</dt>
                        <dd class="detail-value">
                            <a href="<?= e(url('admin/tickets/' . (int)$appointment['maintenance_ticket_id'])) ?>" style="font-weight:700;color:#2563eb;text-decoration:none;">
                                <?= e($appointment['ticket_code']) ?>
                            </a>
                            · <span class="status checked-in" style="font-size:12px;padding:2px 8px;"><?= e($maintenanceStatus ?? 'Đang xử lý') ?></span>
                        </dd>
                    </div>
                <?php elseif ($appointment['status'] === 'confirmed' || $appointment['status'] === 'checked_in'): ?>
                    <div class="detail-item">
                        <dt class="detail-label">Phiếu sửa chữa</dt>
                        <dd class="detail-value">
                            <span style="color:#667788;margin-right:8px;">Chưa tạo phiếu</span>
                            <a href="<?= e(url('admin/tickets/create?appointment_id=' . (int)$appointment['id'])) ?>" class="button" style="padding:4px 10px;font-size:12px;">+ Tạo phiếu sửa chữa ngay</a>
                        </dd>
                    </div>
                <?php endif; ?>
            </dl>
        </div>
    </section>
    <aside class="side-panel">
        <?php if (($appointment['cancellation_status'] ?? 'none') === 'requested'): ?>
            <section class="panel">
                <div class="panel-head"><div><h2 class="panel-title">Yêu cầu hủy từ khách hàng</h2><p class="panel-description">Quyết định này sẽ cập nhật đồng bộ trạng thái lịch ở cả hai phía.</p></div></div>
                <div class="panel-body">
                    <?php if (is_string($appointment['cancellation_reason']) && trim($appointment['cancellation_reason']) !== ''): ?><p class="muted"><strong>Lý do:</strong> <?= nl2br(e($appointment['cancellation_reason'])) ?></p><?php else: ?><p class="muted">Khách hàng không nhập lý do.</p><?php endif; ?>
                    <div class="actions">
                        <form method="post" action="<?= e(url('admin/appointments/' . (int) $appointment['id'] . '/cancellation')) ?>" onsubmit="return confirm('Xác nhận hủy lịch này?');">
                            <?= csrf_field() ?><input type="hidden" name="decision" value="approved"><button class="button danger" type="submit">Xác nhận hủy</button>
                        </form>
                        <form method="post" action="<?= e(url('admin/appointments/' . (int) $appointment['id'] . '/cancellation')) ?>" onsubmit="return confirm('Từ chối yêu cầu hủy và giữ nguyên lịch?');">
                            <?= csrf_field() ?><input type="hidden" name="decision" value="rejected"><button class="button secondary" type="submit">Từ chối hủy</button>
                        </form>
                    </div>
                </div>
            </section>
        <?php endif; ?>
        <?php if ($cancellationHistory !== []): ?>
            <section class="panel">
                <div class="panel-head"><div><h2 class="panel-title">Lịch sử xử lý hủy</h2><p class="panel-description">Lưu người thực hiện và thời điểm từng bước.</p></div></div>
                <div class="panel-body">
                    <?php foreach ($cancellationHistory as $event): ?>
                        <?php
                            $eventLabels = ['requested' => 'Khách yêu cầu hủy', 'approved' => 'Đã xác nhận hủy', 'rejected' => 'Đã từ chối hủy'];
                            $eventTime = date('d/m/Y H:i', strtotime((string) $event['created_at']));
                        ?>
                        <p class="muted"><strong><?= e($eventLabels[$event['action']] ?? $event['action']) ?></strong>
                            · <?= e($event['actor_name'] ?? 'Tài khoản đã xóa') ?>
                            · <?= e($eventTime) ?>
                            <?php if (is_string($event['reason']) && trim($event['reason']) !== ''): ?><br>Lý do: <?= nl2br(e($event['reason'])) ?><?php endif; ?>
                        </p>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
        <section class="panel appointment-status-panel">
            <div class="panel-head">
                <div>
                    <h2 class="panel-title">Cập nhật trạng thái</h2>
                    <p class="panel-description">Chỉ chọn trạng thái phù hợp với tiến trình thực tế.</p>
                </div>
            </div>
            <div class="panel-body">
                <?php if ($transitions === []): ?>
                    <p class="muted">Lịch hẹn đã ở trạng thái kết thúc, không còn thao tác chuyển trạng thái được phép.</p>
                <?php else: ?>
                    <p class="muted">Lý do từ chối bắt buộc; có thể nhập lý do khi Admin chủ động hủy lịch.</p>
                    <form method="post" action="<?= e(url('admin/appointments/' . (int) $appointment['id'] . '/status')) ?>" id="status-form">
                        <?= csrf_field() ?>
                        <label class="field"><span>Trạng thái tiếp theo</span>
                            <select name="status" id="status-select" required>
                                <?php foreach ($transitions as $statusValue => $label): ?>
                                    <option value="<?= e($statusValue) ?>"><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label class="field" id="reason-field" hidden><span id="reason-label">Lý do</span>
                            <textarea name="reason" id="reason-input" maxlength="2000" aria-describedby="reason-help"></textarea>
                            <small class="help" id="reason-help">Lý do từ chối hoặc hủy sẽ hiển thị trong chi tiết lịch hẹn.</small>
                        </label>
                        <div class="actions" style="margin-top:16px"><button class="button" type="submit">Lưu trạng thái</button></div>
                    </form>
                    <script>
                        (function () {
                            var statusSelect = document.getElementById('status-select');
                            var reasonField = document.getElementById('reason-field');
                            var reasonInput = document.getElementById('reason-input');
                            var reasonLabel = document.getElementById('reason-label');
                            var form = document.getElementById('status-form');
                            function updateReasonField() {
                                var rejected = statusSelect.value === 'rejected';
                                var cancelled = statusSelect.value === 'cancelled';
                                reasonField.hidden = !rejected && !cancelled;
                                reasonInput.required = rejected;
                                reasonInput.maxLength = cancelled ? 500 : 2000;
                                reasonLabel.textContent = rejected ? 'Lý do từ chối' : 'Lý do hủy';
                            }
                            statusSelect.addEventListener('change', updateReasonField);
                            form.addEventListener('submit', function (event) {
                                var submitButton = form.querySelector('button[type="submit"]');
                                if (!form.reportValidity()) {
                                    event.preventDefault();
                                    return;
                                }
                                submitButton.disabled = true;
                                submitButton.textContent = 'Đang lưu…';
                            });
                            updateReasonField();
                        }());
                    </script>
                <?php endif; ?>
            </div>
        </section>
    </aside>
</div>
<?php
$content = ob_get_clean();
require BASE_PATH . '/app/Views/admin/_layout.php';
