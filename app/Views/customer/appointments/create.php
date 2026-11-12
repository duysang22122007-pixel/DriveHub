<?php

declare(strict_types=1);

$selectedServices = array_map('intval', is_array($old['service_ids'] ?? null) ? $old['service_ids'] : []);
$bookingType = is_string($old['booking_type'] ?? null) ? $old['booking_type'] : '';
$selectedVehicle = is_string($old['vehicle_id'] ?? null) ? $old['vehicle_id'] : '';

?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đặt lịch sửa chữa | AutoCare Hub</title>
    <?php require __DIR__ . '/_styles.php'; ?>
</head>
<body>
<header class="site-header"><div class="top">
    <a class="brand" href="<?= e(url()) ?>">AutoCare Hub</a>
    <nav aria-label="Điều hướng khách hàng">
        <a href="<?= e(url('vehicles')) ?>">Xe của tôi</a>
        <a href="<?= e(url('appointments')) ?>">Lịch hẹn</a>
        <a href="<?= e(url('profile')) ?>">Tài khoản</a>
        <form method="post" action="<?= e(url('logout')) ?>"><?= csrf_field() ?><button class="button secondary" type="submit">Đăng xuất</button></form>
    </nav>
</div></header>
<main>
    <div class="page-heading">
        <div>
            <span class="eyebrow">Dịch vụ AutoCare Hub</span>
            <h1>Đặt lịch sửa chữa</h1>
            <p>Gửi thời gian mong muốn; lịch chỉ được xác nhận sau khi gara phản hồi.</p>
        </div>
        <a class="button secondary" href="<?= e(url('appointments')) ?>">Lịch hẹn của tôi</a>
    </div>

    <?php if (isset($errors['form'])): ?><div class="notice error" role="alert"><?= e($errors['form']) ?></div><?php endif; ?>

    <ol class="progress" aria-label="Tiến trình đặt lịch">
        <?php foreach (['Hình thức', 'Chọn xe', 'Dịch vụ', 'Thời gian', 'Liên hệ', 'Xác nhận'] as $index => $label): ?>
            <?php $step = $index + 1; ?>
            <li data-progress-step="<?= $step ?>" <?= $step === $activeStep ? 'aria-current="step"' : '' ?>>
                <span class="progress-mark"><?= $step ?></span><span><?= e($label) ?></span>
            </li>
        <?php endforeach; ?>
    </ol>

    <div class="layout">
        <section class="panel">
            <form id="booking-form" method="post" action="<?= e(url('appointments')) ?>" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="appointment_code" value="<?= e($old['appointment_code']) ?>">

                <fieldset class="wizard-step" data-step="1">
                    <legend>1. Chọn hình thức đặt lịch</legend>
                    <p class="panel-intro">Chọn cách bạn muốn gara hỗ trợ. Cả hai lựa chọn đều cần thời gian mong muốn để gara liên hệ xác nhận.</p>
                    <?php if (isset($errors['booking_type'])): ?><p class="field-error" role="alert"><?= e($errors['booking_type']) ?></p><?php endif; ?>
                    <div class="choice-grid">
                        <label class="choice-card <?= $bookingType === 'onsite' ? 'is-selected' : '' ?>">
                            <input type="radio" name="booking_type" value="onsite" required <?= $bookingType === 'onsite' ? 'checked' : '' ?>>
                            <span><strong>Đặt lịch trực tiếp tại gara</strong><small>Mang xe đến gara vào thời gian mong muốn để kiểm tra hoặc sử dụng dịch vụ.</small></span>
                        </label>
                        <label class="choice-card <?= $bookingType === 'online' ? 'is-selected' : '' ?>">
                            <input type="radio" name="booking_type" value="online" required <?= $bookingType === 'online' ? 'checked' : '' ?>>
                            <span><strong>Tư vấn hoặc hỗ trợ trực tuyến</strong><small>Gửi mô tả và thời gian phù hợp để gara liên hệ tư vấn từ xa.</small></span>
                        </label>
                    </div>
                    <div class="form-actions form-actions-end"><button class="button" type="button" data-next>Tiếp tục</button></div>
                </fieldset>

                <fieldset class="wizard-step" data-step="2">
                    <legend>2. Chọn xe cần hỗ trợ</legend>
                    <p class="panel-intro">Danh sách chỉ gồm phương tiện thuộc tài khoản của bạn.</p>
                    <?php if (isset($errors['vehicle_id'])): ?><p class="field-error" role="alert"><?= e($errors['vehicle_id']) ?></p><?php endif; ?>
                    <?php if ($vehicles === []): ?>
                        <div class="empty-state"><strong>Bạn chưa đăng ký xe</strong><p>Thêm xe vào tài khoản trước khi gửi yêu cầu đặt lịch.</p><p><a href="<?= e(url('vehicles/create')) ?>">Thêm phương tiện</a></p></div>
                    <?php else: ?>
                        <div class="vehicle-list">
                            <?php foreach ($vehicles as $vehicle): ?>
                                <?php $vehicleLabel = $vehicle['brand_name'] . ' ' . $vehicle['model']; ?>
                                <label class="vehicle-choice <?= $selectedVehicle === (string) $vehicle['id'] ? 'is-selected' : '' ?>">
                                    <input type="radio" name="vehicle_id" value="<?= (int) $vehicle['id'] ?>" required <?= $selectedVehicle === (string) $vehicle['id'] ? 'checked' : '' ?>>
                                    <span class="vehicle-copy">
                                        <strong><?= e($vehicleLabel) ?></strong>
                                        <span>Năm sản xuất: <?= e($vehicle['manufacture_year'] ?: 'Chưa cập nhật') ?></span>
                                        <span class="plate"><?= e($vehicle['license_plate']) ?></span>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <div class="form-actions"><button class="button secondary" type="button" data-back>Quay lại</button><button class="button" type="button" data-next>Tiếp tục</button></div>
                </fieldset>

                <fieldset class="wizard-step" data-step="3">
                    <legend>3. Chọn dịch vụ</legend>
                    <p class="panel-intro">Có thể chọn nhiều dịch vụ. Giá và thời lượng chỉ mang tính tham khảo; gara xác nhận sau khi tiếp nhận yêu cầu.</p>
                    <?php if (isset($errors['service_ids'])): ?><p class="field-error" role="alert"><?= e($errors['service_ids']) ?></p><?php endif; ?>
                    <?php if ($services === []): ?>
                        <div class="empty-state"><strong>Chưa có dịch vụ đang mở để chọn</strong><p>Bạn vẫn có thể gửi yêu cầu hỗ trợ hoặc mô tả nhu cầu; gara sẽ liên hệ xác nhận nội dung và thời gian.</p></div>
                    <?php else: ?>
                        <div class="service-grid">
                            <?php foreach ($services as $service): ?>
                                <label class="service-card <?= in_array((int) $service['id'], $selectedServices, true) ? 'is-selected' : '' ?>">
                                    <?php if (is_string($service['image']) && $service['image'] !== ''): ?>
                                        <img class="service-image" src="<?= e($service['image']) ?>" alt="" loading="lazy">
                                    <?php else: ?>
                                        <span class="service-image service-placeholder" aria-hidden="true">✦</span>
                                    <?php endif; ?>
                                    <span class="service-copy">
                                        <strong><?= e($service['name']) ?></strong>
                                        <p><?= e($service['description'] ?: 'Chưa có mô tả dịch vụ.') ?></p>
                                        <span class="service-meta">
                                            <span><?= (float) $service['price'] > 0 ? e(number_format((float) $service['price'], 0, ',', '.') . ' ₫') : 'Chưa có giá tham khảo' ?></span>
                                            <span><?= (int) $service['duration'] > 0 ? 'Khoảng ' . (int) $service['duration'] . ' phút' : 'Chưa có thời lượng' ?></span>
                                        </span>
                                    </span>
                                    <input type="checkbox" name="service_ids[]" value="<?= (int) $service['id'] ?>"
                                           data-name="<?= e($service['name']) ?>" data-price="<?= e((float) $service['price']) ?>"
                                           data-duration="<?= (int) $service['duration'] ?>"
                                           <?= in_array((int) $service['id'], $selectedServices, true) ? 'checked' : '' ?>>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <p id="service-selection-summary" class="hint" aria-live="polite"></p>
                    <div class="form-actions"><button class="button secondary" type="button" data-back>Quay lại</button><button class="button" type="button" data-next>Tiếp tục</button></div>
                </fieldset>

                <fieldset class="wizard-step" data-step="4">
                    <legend>4. Chọn ngày và giờ mong muốn</legend>
                    <p class="panel-intro">Hệ thống chưa có cấu hình khung giờ hoặc lịch trống. Đây là thời gian bạn đề xuất, không phải xác nhận còn chỗ.</p>
                    <div class="field-grid">
                        <div class="field">
                            <label for="appointment-date">Ngày mong muốn</label>
                            <input id="appointment-date" type="date" name="appointment_date" min="<?= e($today) ?>" value="<?= e($old['appointment_date'] ?? '') ?>" required>
                            <?php if (isset($errors['appointment_date'])): ?><p class="field-error" role="alert"><?= e($errors['appointment_date']) ?></p><?php endif; ?>
                        </div>
                        <div class="field">
                            <label for="appointment-time">Giờ mong muốn</label>
                            <input id="appointment-time" type="time" name="appointment_time" value="<?= e($old['appointment_time'] ?? '') ?>" required>
                            <?php if (isset($errors['appointment_time'])): ?><p class="field-error" role="alert"><?= e($errors['appointment_time']) ?></p><?php endif; ?>
                        </div>
                    </div>
                    <p class="hint">Thời gian được hiểu theo giờ Việt Nam. Gara sẽ phản hồi để xác nhận hoặc đề xuất thời gian khác.</p>
                    <div class="form-actions"><button class="button secondary" type="button" data-back>Quay lại</button><button class="button" type="button" data-next>Tiếp tục</button></div>
                </fieldset>

                <fieldset class="wizard-step" data-step="5">
                    <legend>5. Thông tin liên hệ và tình trạng xe</legend>
                    <p class="panel-intro">Thông tin liên hệ lấy từ tài khoản hiện tại. Muốn cập nhật, hãy chỉnh sửa hồ sơ trước khi gửi yêu cầu.</p>
                    <div class="contact-card">
                        <div><span>Khách hàng</span><strong><?= e($customer['full_name']) ?></strong></div>
                        <div><span>Số điện thoại</span><strong><?= e($customer['phone']) ?></strong></div>
                        <div><span>Email</span><strong><?= e($customer['email']) ?></strong></div>
                        <a href="<?= e(url('profile')) ?>">Cập nhật thông tin tài khoản</a>
                    </div>
                    <div class="field-grid" style="margin-top:18px">
                        <div class="field full">
                            <label for="condition">Mô tả tình trạng xe (không bắt buộc)</label>
                            <textarea id="condition" name="condition" maxlength="2000" placeholder="Ví dụ: tiếng kêu, đèn cảnh báo hoặc dấu hiệu cần kiểm tra"><?= e($old['condition'] ?? '') ?></textarea>
                            <?php if (isset($errors['condition'])): ?><p class="field-error" role="alert"><?= e($errors['condition']) ?></p><?php endif; ?>
                        </div>
                        <div class="field full">
                            <label for="additional-note">Ghi chú thêm cho gara (không bắt buộc)</label>
                            <textarea id="additional-note" name="additional_note" maxlength="2000" placeholder="Thời gian liên hệ thuận tiện hoặc lưu ý khác"><?= e($old['additional_note'] ?? '') ?></textarea>
                            <?php if (isset($errors['additional_note'])): ?><p class="field-error" role="alert"><?= e($errors['additional_note']) ?></p><?php endif; ?>
                        </div>
                    </div>
                    <div class="form-actions"><button class="button secondary" type="button" data-back>Quay lại</button><button class="button" type="button" data-next>Tiếp tục</button></div>
                </fieldset>

                <fieldset class="wizard-step" data-step="6">
                    <legend>6. Kiểm tra và xác nhận</legend>
                    <p class="panel-intro">Kiểm tra lại thông tin. Gửi yêu cầu sẽ tạo lịch ở trạng thái chờ gara xác nhận.</p>
                    <dl class="summary-list">
                        <div class="summary-row"><dt>Hình thức</dt><dd id="summary-type">Chưa chọn</dd></div>
                        <div class="summary-row"><dt>Xe</dt><dd id="summary-vehicle">Chưa chọn</dd></div>
                        <div class="summary-row"><dt>Dịch vụ</dt><dd id="summary-services">Chưa chọn dịch vụ</dd></div>
                        <div class="summary-row"><dt>Ngày, giờ</dt><dd id="summary-datetime">Chưa chọn</dd></div>
                        <div class="summary-row"><dt>Liên hệ</dt><dd><?= e($customer['full_name']) ?><br><?= e($customer['phone']) ?><br><?= e($customer['email']) ?></dd></div>
                        <div class="summary-row"><dt>Mô tả và ghi chú</dt><dd id="summary-notes">Chưa có ghi chú</dd></div>
                    </dl>
                    <div class="summary-total">Tổng giá tham khảo <span id="summary-total">—</span></div>
                    <p class="summary-note">Giá và thời gian chưa phải báo giá hoặc xác nhận lịch cuối cùng. Gara sẽ liên hệ bạn theo thông tin tài khoản.</p>
                    <div class="form-actions">
                        <button class="button secondary" type="button" data-back>Quay lại chỉnh sửa</button>
                        <button id="booking-submit" class="button" type="submit">Xác nhận gửi yêu cầu</button>
                    </div>
                </fieldset>
            </form>
        </section>

        <aside class="aside">
            <section class="panel">
                <h2>Hỗ trợ đặt lịch</h2>
                <p class="panel-intro">Yêu cầu sẽ ở trạng thái chờ xác nhận. Không có khung giờ trống nào được giả định trước.</p>
                <?php if (is_string($garage['phone'] ?? null) && $garage['phone'] !== ''): ?>
                    <p><strong>Hotline gara</strong><br><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $garage['phone'])) ?>"><?= e($garage['phone']) ?></a></p>
                <?php else: ?>
                    <p class="muted">Gara chưa cấu hình số điện thoại hỗ trợ.</p>
                <?php endif; ?>
                <a href="<?= e(url('appointments')) ?>">Xem lịch hẹn đã gửi</a>
            </section>
        </aside>
    </div>
</main>
<footer class="site-footer">AutoCare Hub · Lịch hẹn chỉ được xác nhận sau khi gara phản hồi.</footer>
<script>
(() => {
    const form = document.getElementById('booking-form');
    const steps = Array.from(form.querySelectorAll('.wizard-step'));
    const progress = Array.from(document.querySelectorAll('[data-progress-step]'));
    const initialStep = <?= (int) $activeStep ?>;
    let activeStep = initialStep;
    let submitting = false;

    const setStep = (number, scrollToHeading = true) => {
        activeStep = Math.max(1, Math.min(steps.length, number));
        steps.forEach((step) => {
            step.hidden = Number(step.dataset.step) !== activeStep;
        });
        progress.forEach((item, index) => {
            item.classList.toggle('is-active', index + 1 === activeStep);
            item.classList.toggle('is-complete', index + 1 < activeStep);
            if (index + 1 === activeStep) item.setAttribute('aria-current', 'step');
            else item.removeAttribute('aria-current');
        });
        if (scrollToHeading) {
            document.querySelector('.page-heading').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        updateSummary();
    };

    const clientError = (step, field, message) => {
        setStep(step);
        const element = field ? document.getElementById(field) : null;
        if (element) {
            element.setCustomValidity(message);
            element.reportValidity();
            element.addEventListener('input', () => element.setCustomValidity(''), { once: true });
        } else {
            const alert = document.createElement('p');
            alert.className = 'field-error';
            alert.setAttribute('role', 'alert');
            alert.textContent = message;
            steps[step - 1].prepend(alert);
        }
    };

    const validateStep = (number) => {
        if (number === 1 && !form.querySelector('input[name="booking_type"]:checked')) {
            clientError(1, null, 'Vui lòng chọn hình thức đặt lịch.');
            return false;
        }
        if (number === 2) {
            if (!form.querySelector('input[name="vehicle_id"]:checked')) {
                clientError(2, null, 'Vui lòng thêm hoặc chọn xe thuộc tài khoản của bạn.');
                return false;
            }
        }
        if (number === 4) {
            const date = document.getElementById('appointment-date');
            const time = document.getElementById('appointment-time');
            if (!date.value) {
                clientError(4, 'appointment-date', 'Vui lòng chọn ngày mong muốn.');
                return false;
            }
            if (date.value < date.min) {
                clientError(4, 'appointment-date', 'Không thể chọn ngày trong quá khứ.');
                return false;
            }
            if (!time.value) {
                clientError(4, 'appointment-time', 'Vui lòng chọn giờ mong muốn.');
                return false;
            }
        }
        return true;
    };

    function updateSummary() {
        const selectedType = form.querySelector('input[name="booking_type"]:checked');
        const selectedVehicle = form.querySelector('input[name="vehicle_id"]:checked');
        const vehicleLabel = selectedVehicle ? selectedVehicle.closest('.vehicle-choice').querySelector('.vehicle-copy strong').textContent : 'Chưa chọn xe';
        const labels = { onsite: 'Đặt lịch trực tiếp tại gara', online: 'Tư vấn hoặc hỗ trợ trực tuyến' };
        document.getElementById('summary-type').textContent = selectedType ? labels[selectedType.value] : 'Chưa chọn';
        document.getElementById('summary-vehicle').textContent = vehicleLabel;

        const selectedServices = Array.from(form.querySelectorAll('input[name="service_ids[]"]:checked'));
        const serviceNames = selectedServices.map((input) => input.dataset.name);
        const referenceTotal = selectedServices.reduce((total, input) => total + Number(input.dataset.price || 0), 0);
        const duration = selectedServices.reduce((total, input) => total + Number(input.dataset.duration || 0), 0);
        document.getElementById('summary-services').textContent = serviceNames.length ? `${serviceNames.join(', ')} (${duration} phút dự kiến)` : 'Chưa chọn dịch vụ';
        document.getElementById('service-selection-summary').textContent = serviceNames.length
            ? `Đã chọn ${serviceNames.length} dịch vụ · ${duration} phút dự kiến`
            : 'Bạn có thể gửi yêu cầu không kèm dịch vụ; gara sẽ liên hệ làm rõ nhu cầu.';
        document.getElementById('summary-total').textContent = serviceNames.length
            ? `${new Intl.NumberFormat('vi-VN').format(referenceTotal)} ₫`
            : 'Chưa có dịch vụ';

        const date = document.getElementById('appointment-date').value;
        const time = document.getElementById('appointment-time').value;
        document.getElementById('summary-datetime').textContent = date && time
            ? `${new Date(`${date}T00:00:00`).toLocaleDateString('vi-VN')} · ${time}`
            : 'Chưa chọn';
        const condition = document.getElementById('condition').value.trim();
        const note = document.getElementById('additional-note').value.trim();
        const noteParts = [];
        if (condition) noteParts.push(`Tình trạng xe: ${condition}`);
        if (note) noteParts.push(`Ghi chú thêm: ${note}`);
        document.getElementById('summary-notes').textContent = noteParts.join(' · ') || 'Chưa có ghi chú';
    }

    form.querySelectorAll('[data-next]').forEach((button) => {
        button.addEventListener('click', () => {
            if (validateStep(activeStep)) setStep(activeStep + 1);
        });
    });
    form.querySelectorAll('[data-back]').forEach((button) => {
        button.addEventListener('click', () => setStep(activeStep - 1));
    });
    form.addEventListener('change', (event) => {
        const input = event.target;
        if (input.matches('input[type="radio"], input[type="checkbox"]')) {
            form.querySelectorAll('.choice-card, .vehicle-choice, .service-card').forEach((label) => {
                const control = label.querySelector('input');
                label.classList.toggle('is-selected', Boolean(control && control.checked));
            });
        }
        updateSummary();
    });
    form.addEventListener('input', updateSummary);
    form.addEventListener('submit', (event) => {
        if (submitting) {
            event.preventDefault();
            return;
        }
        for (let step = 1; step <= 5; step++) {
            if (!validateStep(step)) {
                event.preventDefault();
                return;
            }
        }
        submitting = true;
        const button = document.getElementById('booking-submit');
        button.disabled = true;
        button.textContent = 'Đang gửi yêu cầu...';
        form.setAttribute('aria-busy', 'true');
    });

    setStep(initialStep, false);
})();
</script>
</body>
</html>
