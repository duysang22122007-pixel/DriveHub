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
$typeLabel = $bookingTypes[$appointment['appointment_type']] ?? 'Khác';
$statusLabel = $statuses[$appointment['status']] ?? 'Chưa xác định';
$maintenanceStatus = $maintenanceStatuses[$appointment['maintenance_ticket_status'] ?? ''] ?? null;
$appointmentDate = date('d/m/Y', strtotime((string) $appointment['appointment_date']));
$appointmentTime = substr((string) $appointment['appointment_time'], 0, 5);
$serviceTotal = array_sum(array_map(
    static fn (array $service): float => (float) $service['price'] * (int) $service['quantity'],
    $services
));
$createdAt = $appointment['created_at'] === null ? '—' : date('d/m/Y H:i', strtotime((string) $appointment['created_at']));
$updatedAt = $appointment['updated_at'] === null ? '—' : date('d/m/Y H:i', strtotime((string) $appointment['updated_at']));

$pageDescription = $typeLabel . ' · Tạo lúc ' . $createdAt;
$pageActions = '<a class="button secondary" href="' . e(url('admin/appointments')) . '">← Danh sách lịch hẹn</a>';
$pageStyles = '.service-line{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;padding:7px 0;border-bottom:1px dashed #e7edf3}.service-line:last-of-type{border-bottom:0}.service-total{margin-top:8px;padding-top:9px;border-top:1px solid #e7edf3;color:#123b5d}';

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
            <span class="status <?= e($statusClass) ?>"><?= e($statusLabel) ?></span>
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
                                <strong><?= e(number_format((float) $service['price'], 0, ',', '.')) ?> ₫</strong>
                            </div>
                        <?php endforeach; ?>
                        <div class="service-total"><strong>Tổng tham khảo: <?= e(number_format($serviceTotal, 0, ',', '.')) ?> ₫</strong></div>
                    <?php endif; ?>
                </dd></div>
                <?php if ($appointment['status'] === 'rejected' && is_string($appointment['admin_note']) && trim($appointment['admin_note']) !== ''): ?>
                    <div class="detail-item"><dt class="detail-label">Lý do từ chối</dt><dd class="detail-value"><?= nl2br(e($appointment['admin_note'])) ?></dd></div>
                <?php endif; ?>
                <div class="detail-item"><dt class="detail-label">Cập nhật lần cuối</dt><dd class="detail-value"><?= e($updatedAt) ?></dd></div>
                <?php if ($maintenanceStatus !== null): ?>
                    <div class="detail-item"><dt class="detail-label">Phiếu sửa chữa</dt><dd class="detail-value"><?= e($appointment['ticket_code']) ?> · <?= e($maintenanceStatus) ?></dd></div>
                <?php endif; ?>
            </dl>
        </div>
    </section>
    <aside class="side-panel">
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
                    <p class="muted">Lý do từ chối là bắt buộc khi từ chối lịch.</p>
                    <form method="post" action="<?= e(url('admin/appointments/' . (int) $appointment['id'] . '/status')) ?>" id="status-form">
                        <?= csrf_field() ?>
                        <label class="field"><span>Trạng thái tiếp theo</span>
                            <select name="status" id="status-select" required>
                                <?php foreach ($transitions as $statusValue => $label): ?>
                                    <option value="<?= e($statusValue) ?>"><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label class="field" id="reason-field" hidden><span>Lý do từ chối</span>
                            <textarea name="reason" id="reason-input" maxlength="2000" aria-describedby="reason-help"></textarea>
                            <small class="help" id="reason-help">Nội dung này sẽ hiển thị cho khách hàng trong chi tiết lịch bị từ chối.</small>
                        </label>
                        <div class="actions" style="margin-top:16px"><button class="button" type="submit">Lưu trạng thái</button></div>
                    </form>
                    <script>
                        (function () {
                            var statusSelect = document.getElementById('status-select');
                            var reasonField = document.getElementById('reason-field');
                            var reasonInput = document.getElementById('reason-input');
                            var form = document.getElementById('status-form');
                            function updateReasonField() {
                                var rejected = statusSelect.value === 'rejected';
                                reasonField.hidden = !rejected;
                                reasonInput.required = rejected;
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
