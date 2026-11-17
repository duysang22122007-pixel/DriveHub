<?php

declare(strict_types=1);

$selectedServices = array_map('intval', is_array($old['service_ids'] ?? null) ? $old['service_ids'] : []);
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
            <p>Chọn khung giờ còn chỗ; lịch ở trạng thái chờ cho đến khi gara xác nhận.</p>
        </div>
        <a class="button secondary" href="<?= e(url('appointments')) ?>">Lịch hẹn của tôi</a>
    </div>

    <?php if (isset($errors['form']) || isset($errors['appointment_code'])): ?><div class="notice error" role="alert"><?= e($errors['form'] ?? $errors['appointment_code']) ?></div><?php endif; ?>

    <ol class="progress" aria-label="Tiến trình đặt lịch">
        <?php foreach (['Chọn xe', 'Dịch vụ', 'Chọn ngày', 'Khung giờ', 'Ghi chú', 'Xác nhận'] as $index => $label): ?>
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
                    <legend>1. Chọn phương tiện</legend>
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

                <fieldset class="wizard-step" data-step="2">
                    <legend>2. Chọn dịch vụ</legend>
                    <p class="panel-intro">Chọn ít nhất một dịch vụ. Giá và thời lượng chỉ mang tính tham khảo; gara xác nhận sau khi tiếp nhận xe.</p>
                    <?php if (isset($errors['service_ids'])): ?><p class="field-error" role="alert"><?= e($errors['service_ids']) ?></p><?php endif; ?>
                    <?php if ($services === []): ?>
                        <div class="empty-state"><strong>Chưa có dịch vụ đang mở để chọn</strong><p>Hiện chưa thể đặt lịch. Vui lòng liên hệ gara hoặc quay lại khi danh mục dịch vụ được mở.</p></div>
                    <?php else: ?>
                        <div class="service-grid">
                            <?php foreach ($services as $service): ?>
                                <?php $serviceImage = service_image_url($service['image'] ?? null); ?>
                                <label class="service-card <?= in_array((int) $service['id'], $selectedServices, true) ? 'is-selected' : '' ?>">
                                    <?php if ($serviceImage !== null): ?>
                                        <img class="service-image" src="<?= e($serviceImage) ?>" alt="<?= e($service['name']) ?>" width="1200" height="800" loading="lazy" decoding="async">
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
                    <div class="form-actions"><button class="button secondary" type="button" data-back>Quay lại</button><button class="button" type="button" data-next <?= $services === [] ? 'disabled' : '' ?>>Tiếp tục</button></div>
                </fieldset>

                <fieldset class="wizard-step" data-step="3">
                    <legend>3. Chọn ngày</legend>
                    <p class="panel-intro">Chọn ngày để kiểm tra gara có hoạt động và còn sức chứa hay không.</p>
                    <div class="field-grid">
                        <div class="field">
                            <label for="appointment-date">Ngày</label>
                            <input id="appointment-date" type="date" name="appointment_date" min="<?= e($today) ?>" value="<?= e($old['appointment_date'] ?? '') ?>" required>
                            <?php if (isset($errors['appointment_date'])): ?><p class="field-error" role="alert"><?= e($errors['appointment_date']) ?></p><?php endif; ?>
                        </div>
                    </div>
                    <p id="availability-message" class="hint" role="status">Khung giờ và số chỗ còn lại sẽ hiển thị sau khi chọn ngày.</p>
                    <div class="form-actions"><button class="button secondary" type="button" data-back>Quay lại</button><button class="button" type="button" data-next>Tiếp tục</button></div>
                </fieldset>

                <fieldset class="wizard-step" data-step="4">
                    <legend>4. Chọn khung giờ</legend>
                    <p class="panel-intro">Chỉ các khung giờ còn chỗ mới có thể chọn. Khung đã đủ số lịch sẽ bị khóa.</p>
                    <div class="field">
                        <label for="appointment-time">Khung giờ</label>
                        <select id="appointment-time" name="appointment_time" required disabled>
                            <option value="">Chọn ngày trước</option>
                        </select>
                        <?php if (isset($errors['appointment_time'])): ?><p class="field-error" role="alert"><?= e($errors['appointment_time']) ?></p><?php endif; ?>
                    </div>
                    <p id="slot-capacity-message" class="hint" role="status">Chỗ còn lại sẽ được cập nhật theo ngày đã chọn.</p>
                    <div class="form-actions"><button class="button secondary" type="button" data-back>Quay lại</button><button class="button" type="button" data-next>Tiếp tục</button></div>
                </fieldset>

                <fieldset class="wizard-step" data-step="5">
                    <legend>5. Nhập ghi chú</legend>
                    <p class="panel-intro">Ghi rõ dấu hiệu xe, nhu cầu hoặc lưu ý khi gara tiếp nhận. Thông tin liên hệ lấy từ tài khoản của bạn.</p>
                    <div class="contact-card">
                        <div><span>Khách hàng</span><strong><?= e($customer['full_name']) ?></strong></div>
                        <div><span>Số điện thoại</span><strong><?= e($customer['phone']) ?></strong></div>
                        <div><span>Email</span><strong><?= e($customer['email']) ?></strong></div>
                        <a href="<?= e(url('profile')) ?>">Cập nhật thông tin tài khoản</a>
                    </div>
                    <div class="field-grid" style="margin-top:18px">
                        <div class="field full">
                            <label for="additional-note">Ghi chú cho gara (không bắt buộc)</label>
                            <textarea id="additional-note" name="additional_note" maxlength="2000" placeholder="Ví dụ: mô tả dấu hiệu xe hoặc lưu ý khi tiếp nhận"><?= e($old['additional_note'] ?? '') ?></textarea>
                            <?php if (isset($errors['additional_note'])): ?><p class="field-error" role="alert"><?= e($errors['additional_note']) ?></p><?php endif; ?>
                        </div>
                    </div>
                    <div class="form-actions"><button class="button secondary" type="button" data-back>Quay lại</button><button class="button" type="button" data-next>Tiếp tục</button></div>
                </fieldset>

                <fieldset class="wizard-step" data-step="6">
                    <legend>6. Xác nhận đặt lịch</legend>
                    <p class="panel-intro">Kiểm tra thông tin trước khi xác nhận. Lịch sẽ được tạo ở trạng thái chờ gara xác nhận.</p>
                    <dl class="summary-list">
                        <div class="summary-row"><dt>Xe</dt><dd id="summary-vehicle">Chưa chọn</dd></div>
                        <div class="summary-row"><dt>Dịch vụ</dt><dd id="summary-services">Chưa chọn dịch vụ</dd></div>
                        <div class="summary-row"><dt>Ngày, giờ</dt><dd id="summary-datetime">Chưa chọn</dd></div>
                        <div class="summary-row"><dt>Liên hệ</dt><dd><?= e($customer['full_name']) ?><br><?= e($customer['phone']) ?><br><?= e($customer['email']) ?></dd></div>
                        <div class="summary-row"><dt>Ghi chú</dt><dd id="summary-notes">Chưa có ghi chú</dd></div>
                    </dl>
                    <div class="summary-total">Tổng giá tham khảo <span id="summary-total">—</span></div>
                    <p class="summary-note">Giá và thời gian chưa phải báo giá hoặc xác nhận lịch cuối cùng. Gara sẽ liên hệ bạn theo thông tin tài khoản.</p>
                    <div class="form-actions">
                        <button class="button secondary" type="button" data-back>Quay lại chỉnh sửa</button>
                        <button id="booking-submit" class="button" type="submit">Xác nhận đặt lịch</button>
                    </div>
                </fieldset>
            </form>
        </section>

        <aside class="aside">
            <section class="panel">
                <h2>Hỗ trợ đặt lịch</h2>
                <p class="panel-intro">Giờ đã đủ sức chứa sẽ bị khóa. Lịch mới ở trạng thái chờ cho đến khi gara xác nhận.</p>
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
    const dateInput = document.getElementById('appointment-date');
    const timeSelect = document.getElementById('appointment-time');
    const dateMessage = document.getElementById('availability-message');
    const slotMessage = document.getElementById('slot-capacity-message');
    const availabilityUrl = <?= json_encode(url('appointments/availability'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) ?>;
    let preferredTime = window.DriveHubFormDrafts.value(
        form,
        'appointment_time',
        <?= json_encode($old['appointment_time'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) ?>
    );
    const initialStep = Number(window.DriveHubFormDrafts.meta(form, 'step', <?= (int) $activeStep ?>));
    let activeStep = initialStep;
    let submitting = false;
    let availabilityRequest = 0;
    let dateHasAvailableSlots = false;

    const setStep = (number, scrollToHeading = true) => {
        activeStep = Math.max(1, Math.min(steps.length, number));
        window.DriveHubFormDrafts.setMeta(form, 'step', activeStep);
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
            const clearValidity = () => element.setCustomValidity('');
            element.addEventListener('input', clearValidity, { once: true });
            element.addEventListener('change', clearValidity, { once: true });
        } else {
            const alert = document.createElement('p');
            alert.className = 'field-error';
            alert.setAttribute('role', 'alert');
            alert.textContent = message;
            steps[step - 1].prepend(alert);
        }
    };

    const validateStep = (number) => {
        if (number === 1) {
            if (!form.querySelector('input[name="vehicle_id"]:checked')) {
                clientError(1, null, 'Vui lòng thêm hoặc chọn xe thuộc tài khoản của bạn.');
                return false;
            }
        }
        if (number === 2 && !form.querySelector('input[name="service_ids[]"]:checked')) {
            clientError(2, null, 'Vui lòng chọn ít nhất một dịch vụ.');
            return false;
        }
        if (number === 3) {
            if (!dateInput.value) {
                clientError(3, 'appointment-date', 'Vui lòng chọn ngày.');
                return false;
            }
            if (dateInput.value < dateInput.min) {
                clientError(3, 'appointment-date', 'Không thể chọn ngày trong quá khứ.');
                return false;
            }
            if (!dateHasAvailableSlots) {
                clientError(3, 'appointment-date', 'Ngày đã chọn không còn khung giờ có thể đặt.');
                return false;
            }
        }
        if (number === 4) {
            if (!timeSelect.value) {
                clientError(4, 'appointment-time', 'Vui lòng chọn một khung giờ còn chỗ.');
                return false;
            }
            const selectedOption = timeSelect.options[timeSelect.selectedIndex];
            if (!selectedOption || selectedOption.dataset.available !== 'true') {
                clientError(4, 'appointment-time', 'Khung giờ này đã hết chỗ. Vui lòng chọn khung giờ khác.');
                return false;
            }
        }
        return true;
    };

    async function loadAvailability() {
        const requestId = ++availabilityRequest;
        const previousTime = timeSelect.value || preferredTime;
        preferredTime = '';
        dateHasAvailableSlots = false;
        timeSelect.disabled = true;
        timeSelect.innerHTML = '<option value="">Đang tải khung giờ...</option>';

        if (!dateInput.value) {
            timeSelect.innerHTML = '<option value="">Chọn ngày trước</option>';
            dateMessage.textContent = 'Chọn ngày để xem lịch hoạt động và số chỗ còn lại.';
            slotMessage.textContent = 'Chỗ còn lại sẽ được cập nhật theo ngày đã chọn.';
            return;
        }

        dateMessage.textContent = 'Đang kiểm tra lịch hoạt động...';
        try {
            const query = new URLSearchParams({ date: dateInput.value });
            const response = await fetch(`${availabilityUrl}?${query.toString()}`, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });
            const result = await response.json();
            if (requestId !== availabilityRequest) return;
            if (!response.ok) {
                throw new Error(result.error || 'Không thể tải khung giờ. Vui lòng thử lại.');
            }

            const availableSlots = Array.isArray(result.slots) ? result.slots : [];
            timeSelect.innerHTML = '<option value="">Chọn khung giờ</option>';
            availableSlots.forEach((slot) => {
                const option = document.createElement('option');
                option.value = slot.time;
                option.disabled = !slot.available;
                option.dataset.available = slot.available ? 'true' : 'false';
                option.textContent = slot.available
                    ? `${slot.time} · Còn ${slot.remaining} chỗ`
                    : `${slot.time} · ${slot.passed ? 'Đã qua giờ' : 'Đã đủ chỗ'}`;
                if (slot.available && slot.time === previousTime) option.selected = true;
                timeSelect.append(option);
            });

            dateHasAvailableSlots = result.is_open === true && availableSlots.some((slot) => slot.available);
            timeSelect.disabled = availableSlots.length === 0;
            dateMessage.textContent = result.message || 'Không có khung giờ khả dụng.';
            if (Number.isInteger(result.remaining_today)) {
                dateMessage.textContent += ` Còn ${result.remaining_today} lịch trong ngày.`;
            }
            slotMessage.textContent = dateHasAvailableSlots
                ? 'Khung giờ hết chỗ được hiển thị nhưng không thể chọn.'
                : 'Ngày này không còn khung giờ có thể đặt.';
            updateSummary();
        } catch (error) {
            if (requestId !== availabilityRequest) return;
            timeSelect.innerHTML = '<option value="">Không tải được khung giờ</option>';
            timeSelect.disabled = true;
            dateMessage.textContent = error instanceof Error
                ? error.message
                : 'Không thể tải khung giờ. Vui lòng thử lại.';
            slotMessage.textContent = 'Vui lòng tải lại khung giờ trước khi tiếp tục.';
        }
    }

    function updateSummary() {
        const selectedVehicle = form.querySelector('input[name="vehicle_id"]:checked');
        const vehicleLabel = selectedVehicle ? selectedVehicle.closest('.vehicle-choice').querySelector('.vehicle-copy strong').textContent : 'Chưa chọn xe';
        document.getElementById('summary-vehicle').textContent = vehicleLabel;

        const selectedServices = Array.from(form.querySelectorAll('input[name="service_ids[]"]:checked'));
        const serviceNames = selectedServices.map((input) => input.dataset.name);
        const referenceTotal = selectedServices.reduce((total, input) => total + Number(input.dataset.price || 0), 0);
        const duration = selectedServices.reduce((total, input) => total + Number(input.dataset.duration || 0), 0);
        document.getElementById('summary-services').textContent = serviceNames.length ? `${serviceNames.join(', ')} (${duration} phút dự kiến)` : 'Chưa chọn dịch vụ';
        document.getElementById('service-selection-summary').textContent = serviceNames.length
            ? `Đã chọn ${serviceNames.length} dịch vụ · ${duration} phút dự kiến`
            : 'Chọn ít nhất một dịch vụ để tiếp tục.';
        document.getElementById('summary-total').textContent = serviceNames.length
            ? `${new Intl.NumberFormat('vi-VN').format(referenceTotal)} ₫`
            : 'Chưa có dịch vụ';

        const date = dateInput.value;
        const time = timeSelect.value;
        document.getElementById('summary-datetime').textContent = date && time
            ? `${new Date(`${date}T00:00:00`).toLocaleDateString('vi-VN')} · ${time}`
            : 'Chưa chọn';
        const note = document.getElementById('additional-note').value.trim();
        document.getElementById('summary-notes').textContent = note || 'Chưa có ghi chú';
    }

    dateInput.addEventListener('change', loadAvailability);
    timeSelect.addEventListener('change', updateSummary);
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
    form.addEventListener('drivehub:form-draft-restored', (event) => {
        const restoredStep = Number(event.detail.meta.step || initialStep);
        setStep(Number.isFinite(restoredStep) ? restoredStep : initialStep, false);
        updateSummary();
    });
    form.addEventListener('submit', (event) => {
        if (submitting) {
            event.preventDefault();
            return;
        }
        for (let step = 1; step <= 6; step++) {
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
    if (dateInput.value) loadAvailability();
})();
</script>
</body>
</html>
