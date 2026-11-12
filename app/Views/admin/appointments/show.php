<?php

declare(strict_types=1);

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

?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Chi tiết lịch <?= e($appointment['appointment_code']) ?> | AutoCare Hub</title>
    <style>
        :root{font-family:Inter,Arial,sans-serif;color:#172b3b;background:#f4f7fb}*{box-sizing:border-box}body{margin:0}
        header{background:#123b5d;color:#fff}.top,main{width:min(1160px,calc(100% - 36px));margin:auto}.top{min-height:68px;display:flex;align-items:center;justify-content:space-between;gap:15px}
        .brand{color:#fff;font-size:1.1rem;font-weight:800;text-decoration:none}.top nav{display:flex;align-items:center;gap:18px}.top a{color:#fff;text-decoration:none}
        main{padding:34px 0 60px}h1,h2{color:#123b5d}h1{margin:0}h2{font-size:1.05rem;margin:0 0 14px}.sub{color:#6b7b8b}.toolbar{display:flex;align-items:center;justify-content:space-between;gap:18px;margin-bottom:20px}
        .button{display:inline-flex;align-items:center;justify-content:center;min-height:38px;padding:0 13px;border:0;border-radius:8px;background:#2563eb;color:#fff;font:inherit;font-weight:700;cursor:pointer;text-decoration:none}
        .button.secondary{background:#e8eef5;color:#123b5d}.notice{padding:12px 15px;margin:12px 0;border-radius:8px;background:#ecfdf3;color:#067647}.notice.error{background:#fff0ef;color:#b42318}
        .layout{display:grid;grid-template-columns:minmax(0,1.5fr) minmax(280px,.8fr);gap:18px}.panel{padding:22px;border:1px solid #e3eaf1;border-radius:13px;background:#fff;box-shadow:0 7px 22px #123b5d07}.summary{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
        .item{padding-bottom:13px;border-bottom:1px solid #edf1f5;min-width:0}.item dt{margin-bottom:5px;color:#6b7b8b;font-size:.82rem;font-weight:700}.item dd{margin:0;white-space:pre-wrap;overflow-wrap:anywhere}
        .status{display:inline-block;padding:5px 10px;border-radius:99px;background:#f1f5f9;color:#475569;font-size:.8rem;font-weight:700}.status.pending{background:#fff7e6;color:#9a6700}.status.confirmed,.status.checked-in{background:#ecfdf3;color:#067647}.status.rejected,.status.cancelled{background:#fff0ef;color:#b42318}.status.completed{background:#eaf2ff;color:#175cd3}
        .field{display:block;margin:14px 0}.field span{display:block;margin-bottom:6px;color:#475569;font-size:.88rem;font-weight:700}.field select,.field textarea{width:100%;padding:10px 11px;border:1px solid #cbd5e1;border-radius:8px;background:#fff;font:inherit}.field textarea{min-height:112px;resize:vertical}.field select:focus,.field textarea:focus{outline:2px solid #2563eb33;border-color:#2563eb}
        .muted{color:#6b7b8b;font-size:.88rem}.actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:16px}.actions .button{min-height:42px}
        @media(max-width:760px){.top{align-items:flex-start;flex-direction:column;padding:13px 0}.top nav{flex-wrap:wrap;gap:12px}.toolbar{align-items:flex-start;flex-direction:column}.layout{grid-template-columns:1fr}}
        @media(max-width:480px){.summary{grid-template-columns:1fr}.panel{padding:17px}}
    </style>
</head>
<body>
<header><div class="top">
    <a class="brand" href="<?= e(url('admin')) ?>">AutoCare Hub · Admin</a>
    <nav aria-label="Điều hướng Admin">
        <a href="<?= e(url('admin')) ?>">Tổng quan</a>
        <a href="<?= e(url('admin/appointments')) ?>">Lịch hẹn</a>
        <a href="<?= e(url('admin/customers')) ?>">Khách hàng</a>
        <a href="<?= e(url('admin/vehicles')) ?>">Phương tiện</a>
        <a href="<?= e(url('admin/brands')) ?>">Hãng xe</a>
        <a href="<?= e(url('admin/models')) ?>">Dòng xe</a>
        <a href="<?= e(url('admin/vehicle-import')) ?>">Nhập dữ liệu xe</a>
        <a href="<?= e(url('admin/vehicle-import/review')) ?>">Duyệt dòng xe</a>
        <form method="post" action="<?= e(url('logout')) ?>"><?= csrf_field() ?><button class="button secondary" type="submit">Đăng xuất</button></form>
    </nav>
</div></header>
<main>
    <div class="toolbar">
        <div><h1>Chi tiết lịch <?= e($appointment['appointment_code']) ?></h1><p class="sub"><?= e($typeLabel) ?> · Tạo lúc <?= e($createdAt) ?></p></div>
        <a class="button secondary" href="<?= e(url('admin/appointments')) ?>">← Danh sách lịch hẹn</a>
    </div>
    <?php if (is_string($success) && $success !== ''): ?><p class="notice" role="status"><?= e($success) ?></p><?php endif; ?>
    <?php if (is_string($error) && $error !== ''): ?><p class="notice error" role="alert"><?= e($error) ?></p><?php endif; ?>
    <div class="layout">
        <section class="panel">
            <h2>Thông tin lịch hẹn</h2>
            <p><span class="status <?= e($statusClass) ?>"><?= e($statusLabel) ?></span></p>
            <dl class="summary">
                <div class="item"><dt>Khách hàng</dt><dd><?= e($appointment['full_name']) ?></dd></div>
                <div class="item"><dt>Điện thoại / Email</dt><dd><?= e($appointment['phone'] ?? 'Chưa cập nhật') ?><br><?= e($appointment['email'] ?? '') ?></dd></div>
                <div class="item"><dt>Phương tiện</dt><dd><?= e($appointment['brand_name'] . ' ' . $appointment['model']) ?><br>Biển số: <?= e($appointment['license_plate']) ?> · Năm: <?= e($appointment['manufacture_year'] ?: 'Chưa cập nhật') ?></dd></div>
                <div class="item"><dt>Ngày, giờ hẹn</dt><dd><?= e($appointmentDate) ?> · <?= e($appointmentTime) ?></dd></div>
                <div class="item"><dt>Nội dung / lý do</dt><dd><?= $appointment['note'] !== null && $appointment['note'] !== '' ? nl2br(e($appointment['note'])) : 'Không có nội dung bổ sung.' ?></dd></div>
                <div class="item"><dt>Dịch vụ khách đã chọn</dt><dd>
                    <?php if ($services === []): ?>Khách chưa chọn dịch vụ.<?php else: ?>
                        <?php foreach ($services as $service): ?>
                            <?= e($service['service_name']) ?><?= (int) $service['quantity'] > 1 ? ' × ' . (int) $service['quantity'] : '' ?> · <?= e(number_format((float) $service['price'], 0, ',', '.')) ?> ₫<br>
                        <?php endforeach; ?>
                        <strong>Tổng tham khảo: <?= e(number_format($serviceTotal, 0, ',', '.')) ?> ₫</strong>
                    <?php endif; ?>
                </dd></div>
                <?php if ($appointment['status'] === 'rejected' && is_string($appointment['admin_note']) && trim($appointment['admin_note']) !== ''): ?>
                    <div class="item"><dt>Lý do từ chối</dt><dd><?= nl2br(e($appointment['admin_note'])) ?></dd></div>
                <?php endif; ?>
                <div class="item"><dt>Cập nhật lần cuối</dt><dd><?= e($updatedAt) ?></dd></div>
                <?php if ($maintenanceStatus !== null): ?>
                    <div class="item"><dt>Phiếu sửa chữa</dt><dd><?= e($appointment['ticket_code']) ?> · <?= e($maintenanceStatus) ?></dd></div>
                <?php endif; ?>
            </dl>
        </section>
        <aside class="panel">
            <h2>Cập nhật trạng thái</h2>
            <?php if ($transitions === []): ?>
                <p class="muted">Lịch hẹn đã ở trạng thái kết thúc, không còn thao tác chuyển trạng thái được phép.</p>
            <?php else: ?>
                <p class="muted">Chỉ chọn trạng thái phù hợp với tiến trình thực tế. Lý do từ chối là bắt buộc khi từ chối lịch.</p>
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
                        <small class="muted" id="reason-help">Nội dung này sẽ hiển thị cho khách hàng trong chi tiết lịch bị từ chối.</small>
                    </label>
                    <div class="actions"><button class="button" type="submit">Lưu trạng thái</button></div>
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
        </aside>
    </div>
</main>
</body>
</html>
