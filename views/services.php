<?php
$e = function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

$valueIcons = [
    'inspect' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5" /><path d="m15.5 15.5 5 5" /><path d="m7.5 10.5 2 2 4-4" /></svg>',
    'process' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 6h16M4 12h11M4 18h13" /><circle cx="19" cy="12" r="1.5" /><circle cx="19" cy="18" r="1.5" /></svg>',
    'consult' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 5h12v8H9l-4 3v-3H4V5Z" /><path d="M10 9h9v7h-2v3l-3-3h-4V9Z" /></svg>',
    'cost' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="4" width="16" height="16" rx="2" /><path d="M8 8h8M8 12h6M8 16h4" /></svg>',
];
?>

<div class="services-page">
    <section class="services-hero" aria-labelledby="services-hero-title">
        <div class="services-hero__media" aria-hidden="true">
            <img src="<?= BASE_URL ?>assets/images/hero-garage.jpg" alt="" fetchpriority="high">
        </div>
        <span class="services-hero__overlay" aria-hidden="true"></span>

        <div class="container services-hero__content">
            <nav class="services-breadcrumb" aria-label="Breadcrumb">
                <ol>
                    <li><a href="<?= BASE_URL ?>">Trang chủ</a></li>
                    <li aria-current="page">Dịch vụ</li>
                </ol>
            </nav>

            <p class="eyebrow eyebrow--light"><span></span>Dịch vụ DriveHub</p>
            <h1 class="services-hero__title" id="services-hero-title">DỊCH VỤ BẢO DƯỠNG Ô TÔ</h1>
            <p class="services-hero__desc">
                Cung cấp các giải pháp kiểm tra, bảo dưỡng và sửa chữa giúp xe vận hành
                ổn định, an toàn và bền bỉ.
            </p>
            <a class="btn btn--primary" href="#dich-vu">Khám phá dịch vụ</a>
        </div>
    </section>

    <section class="section services-intro" id="gioi-thieu-dich-vu" aria-labelledby="services-intro-title">
        <div class="container services-intro__grid">
            <div class="services-intro__media" data-reveal>
                <img src="<?= BASE_URL ?>assets/images/intro-garage.jpg"
                     alt="Không gian gara hiện đại của DriveHub"
                     loading="lazy">
            </div>

            <div class="services-intro__body" data-reveal>
                <p class="eyebrow"><span></span>Giới thiệu dịch vụ</p>
                <h2 class="section__title" id="services-intro-title">
                    GIẢI PHÁP CHĂM SÓC XE TOÀN DIỆN
                </h2>
                <p class="services-intro__text">
                    DriveHub cung cấp các giải pháp chăm sóc, bảo dưỡng và sửa chữa ô tô
                    nhằm giúp khách hàng duy trì tình trạng vận hành tốt, phát hiện sớm
                    các vấn đề và đảm bảo an toàn trong quá trình sử dụng xe.
                </p>

                <div class="services-values">
                    <?php foreach ($values as $value): ?>
                        <div class="services-value">
                            <span class="services-value__icon">
                                <?= $valueIcons[$value['icon']] ?? $valueIcons['inspect'] ?>
                            </span>
                            <div>
                                <h3><?= $e($value['title']) ?></h3>
                                <p><?= $e($value['desc']) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="section services-list" id="dich-vu" aria-labelledby="services-list-title">
        <div class="container">
            <header class="section-head section-head--center" data-reveal>
                <p class="eyebrow"><span></span>Danh mục dịch vụ</p>
                <h2 class="section__title" id="services-list-title">
                    DỊCH VỤ CHĂM SÓC XE TOÀN DIỆN
                </h2>
                <p class="section__desc">
                    Sáu nhóm dịch vụ chính giúp khách hàng chủ động kiểm tra, bảo dưỡng
                    và xử lý các vấn đề thường gặp trên xe.
                </p>
            </header>

            <div class="services-grid">
                <?php foreach ($services as $index => $service): ?>
                    <article class="services-card" data-reveal>
                        <div class="services-card__media">
                            <img src="<?= BASE_URL ?>assets/images/<?= $e($service['image']) ?>"
                                 alt="<?= $e($service['alt']) ?>"
                                 loading="lazy">
                            <span class="services-card__index" aria-hidden="true">
                                <?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?>
                            </span>
                        </div>

                        <div class="services-card__body">
                            <h3 class="services-card__title"><?= $e($service['name']) ?></h3>
                            <p class="services-card__desc"><?= $e($service['description']) ?></p>

                            <div class="services-card__meta">
                                <div class="services-card__meta-item">
                                    <span>Giá tham khảo</span>
                                    <strong class="services-card__price"><?= $e($service['price']) ?></strong>
                                </div>
                                <div class="services-card__meta-item">
                                    <span>Thời gian</span>
                                    <strong><?= $e($service['duration']) ?></strong>
                                </div>
                            </div>

                            <ul class="services-card__tasks">
                                <?php foreach ($service['tasks'] as $task): ?>
                                    <li><?= $e($task) ?></li>
                                <?php endforeach; ?>
                            </ul>

                            <a class="services-card__link" href="#bang-gia-<?= $e($service['slug']) ?>">
                                Xem bảng giá
                                <span aria-hidden="true">&rarr;</span>
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="section services-pricing" id="bang-gia" aria-labelledby="services-pricing-title">
        <div class="container">
            <header class="section-head" data-reveal>
                <p class="eyebrow"><span></span>Chi phí tham khảo</p>
                <h2 class="section__title" id="services-pricing-title">BẢNG GIÁ DỊCH VỤ</h2>
                <p class="section__desc">
                    Tham khảo chi phí và thời gian thực hiện các dịch vụ tại DriveHub.
                </p>
            </header>

            <p class="services-price-hint">Vuốt ngang để xem đầy đủ bảng giá.</p>

            <div class="services-price-scroll">
                <table class="services-price-table">
                    <caption class="services-visually-hidden">
                        Bảng giá tham khảo và thời gian thực hiện các dịch vụ tại DriveHub
                    </caption>
                    <thead>
                        <tr>
                            <th scope="col">Dịch vụ</th>
                            <th scope="col">Nội dung</th>
                            <th scope="col">Giá tham khảo</th>
                            <th scope="col">Thời gian ước tính</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($services as $service): ?>
                            <tr id="bang-gia-<?= $e($service['slug']) ?>">
                                <th scope="row"><?= $e($service['name']) ?></th>
                                <td><?= $e($service['table_summary']) ?></td>
                                <td class="services-price-table__price"><?= $e($service['price_short']) ?></td>
                                <td class="services-price-table__time"><?= $e($service['duration']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <p class="services-price-note">
                <strong>Lưu ý:</strong> Giá dịch vụ chỉ mang tính tham khảo, có thể thay đổi
                tùy theo dòng xe, phụ tùng sử dụng và tình trạng thực tế. Chi phí chính xác
                sẽ được tư vấn sau khi kiểm tra xe. Giá đang hiển thị là dữ liệu mẫu cho giao diện.
            </p>
        </div>
    </section>

    <section class="section services-process" id="quy-trinh-dich-vu" aria-labelledby="services-process-title">
        <div class="container">
            <header class="section-head section-head--center" data-reveal>
                <p class="eyebrow eyebrow--light"><span></span>Quy trình phục vụ</p>
                <h2 class="section__title section__title--light" id="services-process-title">
                    QUY TRÌNH PHỤC VỤ CHUYÊN NGHIỆP
                </h2>
                <p class="section__desc section__desc--light">
                    Quy trình thực hiện rõ ràng giúp khách hàng nắm được từng bước khi
                    sử dụng dịch vụ.
                </p>
            </header>

            <ol class="services-process__list">
                <?php foreach ($processSteps as $index => $step): ?>
                    <li class="services-process__step" data-reveal>
                        <span class="services-process__num">
                            <?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?>
                        </span>
                        <h3><?= $e($step['title']) ?></h3>
                        <p><?= $e($step['desc']) ?></p>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
    </section>

    <section class="section services-faq" id="cau-hoi-thuong-gap" aria-labelledby="services-faq-title">
        <div class="container">
            <header class="section-head section-head--center" data-reveal>
                <p class="eyebrow"><span></span>Giải đáp thắc mắc</p>
                <h2 class="section__title" id="services-faq-title">CÂU HỎI THƯỜNG GẶP</h2>
                <p class="section__desc">
                    Những câu hỏi thường gặp trước khi khách hàng lựa chọn dịch vụ tại DriveHub.
                </p>
            </header>

            <div class="services-faq__list">
                <?php foreach ($faqs as $faq): ?>
                    <details class="services-faq__item" data-reveal>
                        <summary>
                            <span><?= $e($faq['question']) ?></span>
                            <span class="services-faq__icon" aria-hidden="true"></span>
                        </summary>
                        <div class="services-faq__answer">
                            <p><?= $e($faq['answer']) ?></p>
                        </div>
                    </details>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="section services-cta" aria-labelledby="services-cta-title">
        <div class="container">
            <div class="services-cta__panel">
                <div class="services-cta__content">
                    <p class="eyebrow eyebrow--light"><span></span>Đồng hành cùng DriveHub</p>
                    <h2 class="section__title section__title--light" id="services-cta-title">
                        ĐỪNG ĐỂ NHỮNG VẤN ĐỀ NHỎ ẢNH HƯỞNG ĐẾN HÀNH TRÌNH CỦA BẠN.
                    </h2>
                    <p class="services-cta__desc">
                        Liên hệ DriveHub để được tư vấn và lựa chọn dịch vụ phù hợp với
                        tình trạng xe.
                    </p>
                </div>

                <div class="services-cta__actions">
                    <a class="btn btn--primary" href="#" data-pending="Đặt lịch">Đặt lịch ngay</a>
                    <a class="btn btn--ghost" href="#lien-he">Liên hệ tư vấn</a>
                </div>
            </div>
        </div>
    </section>
</div>
