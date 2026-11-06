<?php
$step = $step ?? 'form';
$old = $old ?? [];
$errors = $errors ?? [];
$draft = $draft ?? null;
$groups = $groups ?? [];
$timeSlots = $timeSlots ?? [];
$csrfToken = $csrfToken ?? '';
$minDate = $minDate ?? date('Y-m-d');
$flash = $flash ?? null;

$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$errorFor = static fn (string $field): string => (string) ($errors[$field] ?? '');
$hasError = static fn (string $field): bool => isset($errors[$field]);
$oldValue = static fn (string $field): string => (string) ($old[$field] ?? '');
$fieldClass = static function (string $field) use ($hasError): string {
    return 'booking-input' . ($hasError($field) ? ' is-invalid' : '');
};
$fieldAttributes = static function (string $field) use ($hasError): string {
    return $hasError($field)
        ? ' aria-invalid="true" aria-describedby="' . $field . '-error"'
        : '';
};

$selectedGroup = (string) ($old['service_group'] ?? '');
$selectedService = (string) ($old['service_key'] ?? '');
$stepIndex = $step === 'review' ? 2 : ($step === 'complete' ? 3 : 1);
$formAction = BASE_URL . '?action=booking';

$draftGroup = is_array($draft) ? (string) ($draft['service_group'] ?? '') : '';
$draftGroupLabel = $groups[$draftGroup]['long_label'] ?? '';
$draftServiceLabel = $groups[$draftGroup]['services'][$draft['service_key'] ?? ''] ?? '';
$draftDateLabel = '';

if (is_array($draft) && !empty($draft['preferred_date'])) {
    $draftTimestamp = strtotime((string) $draft['preferred_date']);

    if ($draftTimestamp !== false) {
        $draftDateLabel = date('d/m/Y', $draftTimestamp);
    }
}
?>

<div class="booking-page">
    <section class="booking-hero" aria-labelledby="booking-hero-title">
        <div class="booking-hero__media" aria-hidden="true">
            <img src="<?= BASE_URL ?>assets/images/hero-garage.jpg" alt="">
        </div>
        <div class="booking-hero__overlay"></div>

        <div class="container booking-hero__inner">
            <nav class="booking-breadcrumb" aria-label="Đường dẫn">
                <a href="<?= BASE_URL ?>">Trang chủ</a>
                <span aria-hidden="true">/</span>
                <a href="<?= BASE_URL ?>?action=services">Dịch vụ</a>
                <span aria-hidden="true">/</span>
                <span aria-current="page">Đặt lịch</span>
            </nav>

            <p class="eyebrow eyebrow--light"><span></span>Đặt lịch dịch vụ</p>
            <h1 class="booking-hero__title" id="booking-hero-title">ĐẶT LỊCH DỊCH VỤ</h1>
            <p class="booking-hero__desc">
                Cho DriveHub biết nhu cầu và tình trạng xe của bạn. Thông tin sẽ được
                kiểm tra lại trước khi xác nhận.
            </p>

            <ul class="booking-hero__chips" aria-label="Các nhóm dịch vụ">
                <li>Bảo dưỡng định kỳ</li>
                <li>Sửa chữa &amp; khắc phục</li>
                <li>Tư vấn tình trạng xe</li>
            </ul>
        </div>
    </section>

    <section class="section booking-main">
        <div class="container">
            <ol class="booking-progress" aria-label="Tiến trình đặt lịch">
                <li class="booking-progress__item <?= $stepIndex === 1 ? 'is-active' : ($stepIndex > 1 ? 'is-done' : '') ?>">
                    <span class="booking-progress__number">01</span>
                    <span>Điền thông tin</span>
                </li>
                <li class="booking-progress__item <?= $stepIndex === 2 ? 'is-active' : ($stepIndex > 2 ? 'is-done' : '') ?>">
                    <span class="booking-progress__number">02</span>
                    <span>Xem lại</span>
                </li>
                <li class="booking-progress__item <?= $stepIndex === 3 ? 'is-active' : '' ?>">
                    <span class="booking-progress__number">03</span>
                    <span>Hoàn tất</span>
                </li>
            </ol>

            <?php if ($step === 'complete'): ?>
                <div class="booking-complete">
                    <div class="booking-complete__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m5 12 4 4L19 6"></path>
                        </svg>
                    </div>

                    <p class="eyebrow"><span></span>Đã ghi nhận yêu cầu</p>
                    <h2 class="booking-complete__title">
                        <?= $e($flash['title'] ?? 'Đã ghi nhận lịch hẹn') ?>
                    </h2>
                    <p class="booking-complete__desc">
                        <?= $e($flash['message'] ?? 'Lịch hẹn đã được lưu. Bạn có thể theo dõi trạng thái trong mục Lịch hẹn của tôi.') ?>
                    </p>

                    <div class="booking-complete__notice">
                        <strong>Trạng thái hiện tại: Chờ xác nhận.</strong>
                        DriveHub sẽ liên hệ với bạn nếu cần thêm thông tin.
                    </div>

                    <div class="booking-complete__actions">
                        <a class="btn btn--primary" href="<?= BASE_URL ?>?action=my-appointments">Xem lịch hẹn</a>
                        <a class="btn btn--primary" href="<?= BASE_URL ?>?action=booking">Đặt lịch mới</a>
                        <a class="btn booking-btn--outline" href="<?= BASE_URL ?>?action=services">Xem dịch vụ</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="booking-layout">
                    <div class="booking-content">
                        <?php if ($errors !== []): ?>
                            <div class="booking-alert booking-alert--error" role="alert">
                                <strong>Vui lòng kiểm tra lại thông tin.</strong>
                                <ul>
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= $e($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if ($step === 'review'): ?>
                            <form class="booking-review" method="post" action="<?= $e($formAction) ?>">
                                <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">

                                <div class="booking-alert booking-alert--notice">
                                    <strong>Đây là bước xem lại.</strong>
                                    Chưa có thông tin nào được gửi đi. Hãy kiểm tra kỹ trước khi xác nhận.
                                </div>

                                <section class="booking-review__card" aria-labelledby="booking-review-service">
                                    <div class="booking-review__head">
                                        <div>
                                            <span class="booking-review__eyebrow">Bước 06</span>
                                            <h2 id="booking-review-service">Nhu cầu dịch vụ</h2>
                                        </div>
                                    </div>

                                    <dl class="booking-review__list">
                                        <div class="booking-review__item booking-review__item--full">
                                            <dt>Nhóm nhu cầu</dt>
                                            <dd><?= $e($draftGroupLabel) ?></dd>
                                        </div>
                                        <div class="booking-review__item">
                                            <dt>Dịch vụ</dt>
                                            <dd><?= $e($draftServiceLabel) ?></dd>
                                        </div>
                                        <div class="booking-review__item">
                                            <dt>Thời gian mong muốn</dt>
                                            <dd><?= $e($draftDateLabel) ?> · <?= $e($draft['preferred_time'] ?? '') ?></dd>
                                        </div>
                                        <div class="booking-review__item booking-review__item--full">
                                            <dt>Mô tả vấn đề</dt>
                                            <dd><?= nl2br($e($draft['problem_description'] ?? '')) ?></dd>
                                        </div>
                                        <?php if (!empty($draft['symptoms'])): ?>
                                            <div class="booking-review__item booking-review__item--full">
                                                <dt>Triệu chứng / dấu hiệu bất thường</dt>
                                                <dd><?= nl2br($e($draft['symptoms'])) ?></dd>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($draft['note'])): ?>
                                            <div class="booking-review__item booking-review__item--full">
                                                <dt>Ghi chú thêm</dt>
                                                <dd><?= nl2br($e($draft['note'])) ?></dd>
                                            </div>
                                        <?php endif; ?>
                                    </dl>
                                </section>

                                <section class="booking-review__card" aria-labelledby="booking-review-vehicle">
                                    <div class="booking-review__head">
                                        <div>
                                            <span class="booking-review__eyebrow">Thông tin xe</span>
                                            <h2 id="booking-review-vehicle">Phương tiện</h2>
                                        </div>
                                    </div>

                                    <dl class="booking-review__list">
                                        <div class="booking-review__item">
                                            <dt>Hãng xe</dt>
                                            <dd><?= $e($draft['car_brand'] ?: 'Chưa cung cấp') ?></dd>
                                        </div>
                                        <div class="booking-review__item">
                                            <dt>Dòng xe</dt>
                                            <dd><?= $e($draft['car_model'] ?: 'Chưa cung cấp') ?></dd>
                                        </div>
                                        <div class="booking-review__item">
                                            <dt>Biển số xe</dt>
                                            <dd><?= $e($draft['license_plate'] ?: 'Chưa cung cấp') ?></dd>
                                        </div>
                                        <div class="booking-review__item">
                                            <dt>Năm sản xuất</dt>
                                            <dd><?= $e($draft['manufacture_year'] ?: 'Chưa cung cấp') ?></dd>
                                        </div>
                                    </dl>
                                </section>

                                <section class="booking-review__card" aria-labelledby="booking-review-contact">
                                    <div class="booking-review__head">
                                        <div>
                                            <span class="booking-review__eyebrow">Liên hệ</span>
                                            <h2 id="booking-review-contact">Thông tin khách hàng</h2>
                                        </div>
                                    </div>

                                    <dl class="booking-review__list">
                                        <div class="booking-review__item">
                                            <dt>Họ tên</dt>
                                            <dd><?= $e($draft['full_name'] ?? '') ?></dd>
                                        </div>
                                        <div class="booking-review__item">
                                            <dt>Số điện thoại</dt>
                                            <dd><?= $e($draft['phone'] ?? '') ?></dd>
                                        </div>
                                        <div class="booking-review__item booking-review__item--full">
                                            <dt>Email</dt>
                                            <dd><?= $e($draft['email'] ?? '') ?></dd>
                                        </div>
                                    </dl>
                                </section>

                                <div class="booking-actions">
                                    <button class="btn booking-btn--outline" type="submit" name="step" value="edit">
                                        <span aria-hidden="true">←</span> Quay lại chỉnh sửa
                                    </button>
                                    <button class="btn btn--primary" type="submit" name="step" value="confirm">
                                        Xác nhận đặt lịch
                                    </button>
                                </div>
                            </form>
                        <?php else: ?>
                            <form class="booking-form" method="post" action="<?= $e($formAction) ?>" novalidate>
                                <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">

                                <section class="booking-form__section" aria-labelledby="booking-step-service">
                                    <div class="booking-form__section-head">
                                        <span class="booking-step">01</span>
                                        <div>
                                            <h2 id="booking-step-service">Nhu cầu dịch vụ</h2>
                                            <p>Chọn nhóm nhu cầu và dịch vụ bạn muốn DriveHub thực hiện.</p>
                                        </div>
                                    </div>

                                    <fieldset class="booking-fieldset">
                                        <legend class="booking-label">
                                            Bạn cần gì? <span class="booking-required">*</span>
                                        </legend>

                                        <div class="booking-needs">
                                            <?php foreach ($groups as $groupKey => $group): ?>
                                                <label class="booking-need">
                                                    <input
                                                        type="radio"
                                                        name="service_group"
                                                        value="<?= $e($groupKey) ?>"
                                                        <?= $selectedGroup === $groupKey ? 'checked' : '' ?>
                                                    >
                                                    <span class="booking-need__box">
                                                        <span class="booking-need__icon" aria-hidden="true">
                                                            <?php if ($groupKey === 'maintenance'): ?>
                                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                                    <path d="M14.7 6.3a4 4 0 0 0-5.4 5.4L4 17l3 3 5.3-5.3a4 4 0 0 0 5.4-5.4l-2.3 2.3-2-2 2.3-2.3Z"></path>
                                                                </svg>
                                                            <?php elseif ($groupKey === 'repair'): ?>
                                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                                    <path d="M5 15.5h14l-1.4-5.1A2 2 0 0 0 15.7 9H8.3a2 2 0 0 0-1.9 1.4L5 15.5Z"></path>
                                                                    <path d="M4 15.5v3h2v-1.5h12v1.5h2v-3"></path>
                                                                    <circle cx="7.5" cy="15.5" r="1"></circle>
                                                                    <circle cx="16.5" cy="15.5" r="1"></circle>
                                                                </svg>
                                                            <?php else: ?>
                                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                                    <circle cx="11" cy="11" r="6"></circle>
                                                                    <path d="m16 16 4 4"></path>
                                                                </svg>
                                                            <?php endif; ?>
                                                        </span>
                                                        <span class="booking-need__content">
                                                            <span class="booking-need__title"><?= $e($group['label']) ?></span>
                                                            <span class="booking-need__desc"><?= $e($group['description']) ?></span>
                                                        </span>
                                                    </span>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>

                                        <?php if ($hasError('service_group')): ?>
                                            <p class="booking-error" id="service_group-error"><?= $e($errorFor('service_group')) ?></p>
                                        <?php endif; ?>
                                    </fieldset>

                                    <div class="booking-services">
                                        <p class="booking-label">
                                            Dịch vụ cụ thể <span class="booking-required">*</span>
                                        </p>
                                        <p class="booking-services__empty" data-booking-empty hidden>
                                            Chọn nhóm nhu cầu để xem các dịch vụ phù hợp.
                                        </p>

                                        <?php foreach ($groups as $groupKey => $group): ?>
                                            <div class="booking-services__group" data-booking-group="<?= $e($groupKey) ?>">
                                                <p class="booking-services__heading"><?= $e($group['long_label']) ?></p>
                                                <div class="booking-service-grid">
                                                    <?php foreach ($group['services'] as $serviceKey => $serviceLabel): ?>
                                                        <label class="booking-service">
                                                            <input
                                                                type="radio"
                                                                name="service_key"
                                                                value="<?= $e($serviceKey) ?>"
                                                                <?= $selectedService === $serviceKey ? 'checked' : '' ?>
                                                            >
                                                            <span><?= $e($serviceLabel) ?></span>
                                                        </label>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>

                                        <?php if ($hasError('service_key')): ?>
                                            <p class="booking-error" id="service_key-error"><?= $e($errorFor('service_key')) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </section>

                                <section class="booking-form__section" aria-labelledby="booking-step-vehicle">
                                    <div class="booking-form__section-head">
                                        <span class="booking-step">02</span>
                                        <div>
                                            <h2 id="booking-step-vehicle">Thông tin xe</h2>
                                            <p>Nhập đầy đủ hãng xe, dòng xe và biển số để tạo lịch hẹn cho đúng phương tiện.</p>
                                        </div>
                                    </div>

                                    <div class="booking-grid">
                                        <div class="booking-field">
                                            <label class="booking-label" for="car_brand">Hãng xe <span class="booking-required">*</span></label>
                                            <input
                                                class="<?= $e($fieldClass('car_brand')) ?>"
                                                type="text"
                                                id="car_brand"
                                                name="car_brand"
                                                value="<?= $e($oldValue('car_brand')) ?>"
                                                maxlength="80"
                                                placeholder="Ví dụ: Toyota"
                                                autocomplete="organization"
                                                <?= $fieldAttributes('car_brand') ?>
                                            >
                                            <?php if ($hasError('car_brand')): ?>
                                                <p class="booking-error" id="car_brand-error"><?= $e($errorFor('car_brand')) ?></p>
                                            <?php endif; ?>
                                        </div>

                                        <div class="booking-field">
                                            <label class="booking-label" for="car_model">Dòng xe <span class="booking-required">*</span></label>
                                            <input
                                                class="<?= $e($fieldClass('car_model')) ?>"
                                                type="text"
                                                id="car_model"
                                                name="car_model"
                                                value="<?= $e($oldValue('car_model')) ?>"
                                                maxlength="120"
                                                placeholder="Ví dụ: Corolla Cross"
                                                <?= $fieldAttributes('car_model') ?>
                                            >
                                            <?php if ($hasError('car_model')): ?>
                                                <p class="booking-error" id="car_model-error"><?= $e($errorFor('car_model')) ?></p>
                                            <?php endif; ?>
                                        </div>

                                        <div class="booking-field">
                                            <label class="booking-label" for="license_plate">Biển số xe <span class="booking-required">*</span></label>
                                            <input
                                                class="<?= $e($fieldClass('license_plate')) ?>"
                                                type="text"
                                                id="license_plate"
                                                name="license_plate"
                                                value="<?= $e($oldValue('license_plate')) ?>"
                                                maxlength="20"
                                                placeholder="Ví dụ: 30A-123.45"
                                                autocomplete="off"
                                                <?= $fieldAttributes('license_plate') ?>
                                            >
                                            <?php if ($hasError('license_plate')): ?>
                                                <p class="booking-error" id="license_plate-error"><?= $e($errorFor('license_plate')) ?></p>
                                            <?php endif; ?>
                                        </div>

                                        <div class="booking-field">
                                            <label class="booking-label" for="manufacture_year">Năm sản xuất</label>
                                            <input
                                                class="<?= $e($fieldClass('manufacture_year')) ?>"
                                                type="text"
                                                id="manufacture_year"
                                                name="manufacture_year"
                                                value="<?= $e($oldValue('manufacture_year')) ?>"
                                                maxlength="4"
                                                inputmode="numeric"
                                                placeholder="Ví dụ: 2020"
                                                <?= $fieldAttributes('manufacture_year') ?>
                                            >
                                            <?php if ($hasError('manufacture_year')): ?>
                                                <p class="booking-error" id="manufacture_year-error"><?= $e($errorFor('manufacture_year')) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </section>

                                <section class="booking-form__section" aria-labelledby="booking-step-time">
                                    <div class="booking-form__section-head">
                                        <span class="booking-step">03</span>
                                        <div>
                                            <h2 id="booking-step-time">Thời gian mong muốn</h2>
                                            <p>Chọn ngày và khung giờ bạn muốn đến DriveHub.</p>
                                        </div>
                                    </div>

                                    <div class="booking-grid">
                                        <div class="booking-field">
                                            <label class="booking-label" for="preferred_date">
                                                Ngày đặt lịch <span class="booking-required">*</span>
                                            </label>
                                            <input
                                                class="<?= $e($fieldClass('preferred_date')) ?>"
                                                type="date"
                                                id="preferred_date"
                                                name="preferred_date"
                                                value="<?= $e($oldValue('preferred_date')) ?>"
                                                min="<?= $e($minDate) ?>"
                                                <?= $fieldAttributes('preferred_date') ?>
                                            >
                                            <p class="booking-help">Chỉ chọn từ hôm nay.</p>
                                            <?php if ($hasError('preferred_date')): ?>
                                                <p class="booking-error" id="preferred_date-error"><?= $e($errorFor('preferred_date')) ?></p>
                                            <?php endif; ?>
                                        </div>

                                        <div class="booking-field">
                                            <label class="booking-label" for="preferred_time">
                                                Khung giờ mong muốn <span class="booking-required">*</span>
                                            </label>
                                            <select
                                                class="<?= $e($fieldClass('preferred_time')) ?>"
                                                id="preferred_time"
                                                name="preferred_time"
                                                <?= $fieldAttributes('preferred_time') ?>
                                            >
                                                <option value="">Chọn khung giờ</option>
                                                <?php foreach ($timeSlots as $timeSlot): ?>
                                                    <option value="<?= $e($timeSlot) ?>" <?= $oldValue('preferred_time') === $timeSlot ? 'selected' : '' ?>>
                                                        <?= $e($timeSlot) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <p class="booking-help">Khung giờ tiếp nhận: 08:00 – 22:00.</p>
                                            <?php if ($hasError('preferred_time')): ?>
                                                <p class="booking-error" id="preferred_time-error"><?= $e($errorFor('preferred_time')) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="booking-inline-note">
                                        Thời gian bạn chọn là mong muốn, chưa phải lịch hẹn đã được xác nhận.
                                    </div>
                                </section>

                                <section class="booking-form__section" aria-labelledby="booking-step-problem">
                                    <div class="booking-form__section-head">
                                        <span class="booking-step">04</span>
                                        <div>
                                            <h2 id="booking-step-problem">Mô tả tình trạng</h2>
                                            <p>Càng mô tả rõ, DriveHub càng chuẩn bị chính xác hơn.</p>
                                        </div>
                                    </div>

                                    <div class="booking-grid">
                                        <div class="booking-field booking-field--full">
                                            <label class="booking-label" for="problem_description">
                                                Vấn đề hoặc nhu cầu của bạn <span class="booking-required">*</span>
                                            </label>
                                            <textarea
                                                class="<?= $e($fieldClass('problem_description')) ?>"
                                                id="problem_description"
                                                name="problem_description"
                                                maxlength="2000"
                                                rows="5"
                                                placeholder="Ví dụ: Xe có tiếng kêu lạ khi phanh gấp, cần kiểm tra tổng quát..."
                                                <?= $fieldAttributes('problem_description') ?>
                                            ><?= $e($oldValue('problem_description')) ?></textarea>
                                            <p class="booking-help">Tối đa 2000 ký tự.</p>
                                            <?php if ($hasError('problem_description')): ?>
                                                <p class="booking-error" id="problem_description-error"><?= $e($errorFor('problem_description')) ?></p>
                                            <?php endif; ?>
                                        </div>

                                        <div class="booking-field booking-field--full">
                                            <label class="booking-label" for="symptoms">Triệu chứng / dấu hiệu bất thường</label>
                                            <textarea
                                                class="<?= $e($fieldClass('symptoms')) ?>"
                                                id="symptoms"
                                                name="symptoms"
                                                maxlength="2000"
                                                rows="3"
                                                placeholder="Ví dụ: Tiếng kêu, rung lắc, đèn cảnh báo, mùi lạ..."
                                                <?= $fieldAttributes('symptoms') ?>
                                            ><?= $e($oldValue('symptoms')) ?></textarea>
                                            <?php if ($hasError('symptoms')): ?>
                                                <p class="booking-error" id="symptoms-error"><?= $e($errorFor('symptoms')) ?></p>
                                            <?php endif; ?>
                                        </div>

                                        <div class="booking-field booking-field--full">
                                            <label class="booking-label" for="note">Ghi chú thêm</label>
                                            <textarea
                                                class="<?= $e($fieldClass('note')) ?>"
                                                id="note"
                                                name="note"
                                                maxlength="1000"
                                                rows="3"
                                                placeholder="Thời gian thuận tiện, yêu cầu riêng hoặc thông tin bổ sung..."
                                                <?= $fieldAttributes('note') ?>
                                            ><?= $e($oldValue('note')) ?></textarea>
                                            <?php if ($hasError('note')): ?>
                                                <p class="booking-error" id="note-error"><?= $e($errorFor('note')) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </section>

                                <section class="booking-form__section" aria-labelledby="booking-step-contact">
                                    <div class="booking-form__section-head">
                                        <span class="booking-step">05</span>
                                        <div>
                                            <h2 id="booking-step-contact">Thông tin tài khoản</h2>
                                            <p>DriveHub sẽ dùng hồ sơ khách hàng đã đăng nhập để liên hệ xác nhận.</p>
                                        </div>
                                    </div>

                                    <dl class="booking-review__list">
                                        <div class="booking-review__item">
                                            <dt>Họ tên</dt>
                                            <dd><?= $e($oldValue('full_name')) ?></dd>
                                        </div>
                                        <div class="booking-review__item">
                                            <dt>Số điện thoại</dt>
                                            <dd><?= $e($oldValue('phone')) ?></dd>
                                        </div>
                                        <div class="booking-review__item booking-review__item--full">
                                            <dt>Email</dt>
                                            <dd><?= $e($oldValue('email')) ?></dd>
                                        </div>
                                    </dl>
                                </section>

                                <section class="booking-form__section booking-form__section--submit" aria-labelledby="booking-step-review">
                                    <div class="booking-form__section-head">
                                        <span class="booking-step">06</span>
                                        <div>
                                            <h2 id="booking-step-review">Kiểm tra và xác nhận</h2>
                                            <p>Xem lại toàn bộ thông tin trước khi xác nhận yêu cầu.</p>
                                        </div>
                                    </div>

                                    <div class="booking-submit-note">
                                        <strong>Chưa gửi yêu cầu ở bước này.</strong>
                                        Bạn sẽ được xem lại thông tin và có thể quay lại chỉnh sửa.
                                    </div>

                                    <button class="btn btn--primary booking-submit" type="submit" name="step" value="review">
                                        Kiểm tra thông tin <span aria-hidden="true">→</span>
                                    </button>
                                </section>
                            </form>
                        <?php endif; ?>
                    </div>

                    <aside class="booking-sidebar" aria-label="Thông tin hỗ trợ">
                        <div class="booking-side-card booking-side-card--dark">
                            <p class="eyebrow eyebrow--light"><span></span>DriveHub tư vấn</p>
                            <h2>Cần hỗ trợ chọn dịch vụ?</h2>
                            <p>
                                Nếu chưa chắc xe cần bảo dưỡng hay sửa chữa, hãy gọi để được
                                tư vấn trước khi đặt lịch.
                            </p>
                            <a class="booking-side-phone" href="tel:0337085291">0337 085 291</a>
                            <p class="booking-side-hours">Tiếp nhận 08:00 – 22:00 hằng ngày</p>
                        </div>

                        <div class="booking-side-card booking-side-card--light">
                            <h3>Lưu ý trước khi đặt lịch</h3>
                            <ul>
                                <li>Khung giờ bạn chọn là mong muốn, DriveHub sẽ liên hệ xác nhận.</li>
                                <li>Chưa thanh toán trực tuyến trên trang này.</li>
                                <li>Chi phí sửa chữa được tư vấn sau khi kiểm tra tình trạng xe.</li>
                                <li>Thông tin hãng xe, dòng xe và biển số là bắt buộc để lưu lịch hẹn đúng phương tiện.</li>
                            </ul>
                        </div>
                    </aside>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>
