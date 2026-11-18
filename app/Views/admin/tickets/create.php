<?php

declare(strict_types=1);

$pageTitle = 'Tạo phiếu sửa chữa mới';
$activeNav = 'tickets';
$pageEyebrow = 'Vận hành';
$pageDescription = 'Khởi tạo phiếu tiếp nhận và sửa chữa xe tại xưởng.';
$pageActions = '<a class="button secondary" href="' . e(url('admin/tickets')) . '">← Danh sách phiếu</a>';

ob_start();
?>
<?php if (is_string($success) && $success !== ''): ?><p class="notice" role="status"><?= e($success) ?></p><?php endif; ?>
<?php if (is_string($error) && $error !== ''): ?><p class="notice error" role="alert"><?= e($error) ?></p><?php endif; ?>

<div class="panel" style="max-width:800px;margin:0 auto;">
    <div class="panel-head">
        <div>
            <h2 class="panel-title">Tiếp nhận xe & Khởi tạo phiếu</h2>
            <p class="panel-description">Chọn lịch hẹn đã được xác nhận của khách để khởi tạo phiếu sửa chữa.</p>
        </div>
    </div>
    <div class="panel-body">
        <?php if (empty($availableAppointments) && empty($selectedAppointment)): ?>
            <div style="text-align:center;padding:32px 16px;color:#667788;">
                <p>Hiện không có lịch hẹn nào đang ở trạng thái <strong>Đã xác nhận</strong> hoặc <strong>Đã tiếp nhận</strong> chưa lập phiếu.</p>
                <div style="margin-top:16px;">
                    <a href="<?= e(url('admin/appointments')) ?>" class="button">Đến danh sách lịch hẹn</a>
                </div>
            </div>
        <?php else: ?>
            <form method="post" action="<?= e(url('admin/tickets')) ?>">
                <?= csrf_field() ?>

                <div style="margin-bottom:20px;">
                    <label style="display:block;font-weight:600;margin-bottom:6px;">Chọn lịch hẹn tiếp nhận <span style="color:#b42318;">*</span></label>
                    <select name="appointment_id" id="appointment_id" required style="width:100%;padding:10px 12px;border:1px solid #d1dbe5;border-radius:6px;font-size:14px;">
                        <option value="">-- Chọn lịch hẹn để lập phiếu --</option>
                        <?php if ($selectedAppointment && !in_array((int)$selectedAppointment['id'], array_column($availableAppointments, 'id'), true)): ?>
                            <option value="<?= (int) $selectedAppointment['id'] ?>" selected>
                                [<?= e($selectedAppointment['appointment_code']) ?>] <?= e($selectedAppointment['full_name']) ?> - <?= e($selectedAppointment['brand_name'] . ' ' . $selectedAppointment['model']) ?> (<?= e($selectedAppointment['license_plate']) ?>)
                            </option>
                        <?php endif; ?>
                        <?php foreach ($availableAppointments as $app): ?>
                            <?php $isSel = ($selectedId === (int) $app['id']); ?>
                            <option value="<?= (int) $app['id'] ?>" <?= $isSel ? 'selected' : '' ?> data-mileage="<?= (int) $app['current_mileage'] ?>">
                                [<?= e($app['appointment_code']) ?>] <?= e($app['full_name']) ?> (<?= e($app['phone']) ?>) - Xe: <?= e($app['brand_name'] . ' ' . $app['model']) ?> [<?= e($app['license_plate']) ?>] - Ngày: <?= date('d/m/Y', strtotime((string)$app['appointment_date'])) ?> <?= substr((string)$app['appointment_time'], 0, 5) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="margin-bottom:20px;">
                    <label style="display:block;font-weight:600;margin-bottom:6px;">Số km xe khi tiếp nhận (odo) <span style="color:#b42318;">*</span></label>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <input type="number" name="mileage" id="mileage_input" min="0" value="<?= $selectedAppointment ? (int) $selectedAppointment['current_mileage'] : 0 ?>" required style="flex:1;padding:10px 12px;border:1px solid #d1dbe5;border-radius:6px;font-size:15px;">
                        <span style="font-weight:600;color:#667788;">km</span>
                    </div>
                    <small style="color:#667788;display:block;margin-top:4px;">Ghi lại đồng hồ công-tơ-mét thực tế của xe khi khách giao xe tại xưởng.</small>
                </div>

                <div style="margin-bottom:20px;">
                    <label style="display:block;font-weight:600;margin-bottom:6px;">Tình trạng ban đầu / Yêu cầu của khách</label>
                    <textarea name="initial_condition" rows="3" placeholder="Ví dụ: Xe kêu cọt kẹt ở gầm trước, đèn check engine sáng, có vết xước nhẹ cửa phụ..." style="width:100%;padding:10px 12px;border:1px solid #d1dbe5;border-radius:6px;font-size:14px;"><?= $selectedAppointment ? e((string)$selectedAppointment['note']) : '' ?></textarea>
                </div>

                <div style="margin-bottom:24px;">
                    <label style="display:block;font-weight:600;margin-bottom:6px;">Chẩn đoán sơ bộ của kỹ thuật viên</label>
                    <textarea name="diagnosis" rows="3" placeholder="Ví dụ: Dự kiến kiểm tra rotuyn trụ đứng, má phanh mòn cần thay, đọc lỗi OBD..." style="width:100%;padding:10px 12px;border:1px solid #d1dbe5;border-radius:6px;font-size:14px;"></textarea>
                </div>

                <div style="display:flex;justify-content:flex-end;gap:12px;border-top:1px solid #e7edf3;padding-top:16px;">
                    <a href="<?= e(url('admin/tickets')) ?>" class="button secondary">Hủy bỏ</a>
                    <button type="submit" class="button">Khởi tạo phiếu sửa chữa</button>
                </div>
            </form>
            <script>
                (function () {
                    var select = document.getElementById('appointment_id');
                    var mileageInput = document.getElementById('mileage_input');
                    if (select && mileageInput) {
                        select.addEventListener('change', function () {
                            var opt = select.options[select.selectedIndex];
                            var km = opt.getAttribute('data-mileage');
                            if (km && parseInt(km, 10) > 0) {
                                mileageInput.value = km;
                            }
                        });
                    }
                }());
            </script>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
require BASE_PATH . '/app/Views/admin/_layout.php';
