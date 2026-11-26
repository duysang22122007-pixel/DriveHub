<?php

declare(strict_types=1);

$isCustomer = $userRole === 'customer';
$accountAction = $userRole === 'admin'
    ? url('admin')
    : ($isCustomer ? url('profile') : url('login'));
$accountLabel = $userRole === null ? 'Đăng nhập' : ($userRole === 'admin' ? 'Quản trị' : 'Tài khoản');
$bookingAction = $isCustomer ? url('appointments/create') : ($userRole === 'admin' ? url('admin') : url('login'));
$bookingLabel = $isCustomer ? 'Đặt lịch sửa chữa' : ($userRole === 'admin' ? 'Mở quản trị' : 'Đăng nhập để đặt lịch');

?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="AutoCare Hub hỗ trợ chăm sóc, bảo dưỡng và quản lý sửa chữa ô tô với quy trình minh bạch.">
    <title>AutoCare Hub | Chăm xe an tâm, hành trình trọn vẹn</title>
    <link rel="stylesheet" href="<?= e(url('assets/customer-header.css')) ?>">
    <style>
        :root {
            color-scheme: light;
            --navy: #123b5d;
            --navy-deep: #0b2740;
            --blue: #2563eb;
            --blue-light: #eaf1ff;
            --ink: #172b3b;
            --muted: #657587;
            --line: #e5ebf1;
            --surface: #f5f8fc;
            --white: #fff;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif;
            color: var(--ink);
            background: var(--white);
            scroll-behavior: smooth;
        }
        * { box-sizing: border-box; }
        body { margin: 0; }
        a { color: inherit; text-decoration: none; }
        button, a { -webkit-tap-highlight-color: transparent; }
        a:focus-visible, button:focus-visible, input:focus-visible { outline: 3px solid #60a5fa; outline-offset: 3px; }
        .container { width: min(1160px, calc(100% - 40px)); margin: 0 auto; }
        .brand { display: inline-flex; align-items: center; gap: 11px; color: var(--navy); font-size: 1.15rem; font-weight: 800; letter-spacing: -.04em; white-space: nowrap; }
        .brand-mark { width: 42px; height: 42px; display: grid; place-items: center; color: white; border-radius: 13px; background: var(--blue); box-shadow: 0 8px 18px #2563eb35; }
        .brand-mark svg { width: 25px; height: 25px; }
        .footer-links a:hover { color: var(--blue); }
        #home, #services, #process, #contact { scroll-margin-top: 92px; }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 9px; min-height: 46px; padding: 0 19px; border: 1px solid transparent; border-radius: 9px; font-size: .92rem; font-weight: 700; transition: transform .18s ease, box-shadow .18s ease, background .18s ease; cursor: pointer; }
        .btn:hover { transform: translateY(-1px); }
        .btn-primary { background: var(--blue); color: #fff; box-shadow: 0 9px 22px #2563eb2c; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-outline { color: var(--navy); border-color: #cbd6e1; background: #fff; }
        .btn-outline:hover { background: var(--surface); }
        .btn svg { width: 17px; height: 17px; }
        .hero { overflow: hidden; position: relative; background: radial-gradient(ellipse at 86% 16%, #dceeff 0, transparent 42%), linear-gradient(118deg, #f8fbff 0%, #eaf4ff 54%, #dceeff 100%); color: var(--navy-deep); }
        .hero-background { position: absolute; z-index: 0; inset: 0; overflow: hidden; }
        .hero-slide { position: absolute; inset: 0; background-position: center; background-size: cover; filter: brightness(1.08) contrast(1.06); opacity: 0; transition: opacity 1s cubic-bezier(.22, .61, .36, 1); will-change: opacity; }
        .hero-slide.is-active { opacity: 1; }
        .hero-background::after { content: ""; position: absolute; inset: 0; background: linear-gradient(90deg, rgb(255 255 255 / 88%) 0%, rgb(255 255 255 / 76%) 36%, rgb(230 242 255 / 44%) 68%, rgb(219 236 255 / 28%) 100%); }
        .hero-grid { position: relative; z-index: 1; min-height: 548px; display: grid; grid-template-columns: minmax(0, 1fr); align-items: center; padding-top: 40px; padding-bottom: 44px; }
        .hero-pagination { position: absolute; z-index: 2; right: 0; bottom: 10px; left: 0; display: flex; justify-content: center; gap: 4px; }
        .hero-pagination button { display: grid; width: 34px; height: 34px; padding: 0; place-items: center; border: 0; border-radius: 50%; background: transparent; cursor: pointer; }
        .hero-pagination button::before { content: ""; width: 9px; height: 9px; border: 1px solid var(--navy); border-radius: 50%; background: #123b5d55; transition: background-color .25s ease; }
        .hero-pagination button[aria-pressed="true"]::before { background: var(--navy); }
        .hero-pagination button:focus-visible { outline: 2px solid var(--navy); outline-offset: 1px; }
        .eyebrow { display: inline-flex; align-items: center; gap: 9px; color: #2563a6; text-transform: uppercase; letter-spacing: .12em; font-size: .75rem; font-weight: 800; }
        .eyebrow::before { content: ""; width: 24px; height: 2px; background: #60a5fa; }
        h1 { max-width: 650px; margin: 20px 0 18px; color: var(--navy-deep); font-size: clamp(2.6rem, 5vw, 4.65rem); line-height: 1.04; letter-spacing: -.055em; }
        .hero-copy { max-width: 520px; color: #344f68; font-size: 1.08rem; line-height: 1.75; }
        .hero-actions { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 27px; }
        .hero .btn-outline { color: var(--navy); background: rgb(255 255 255 / 72%); border-color: #9db5ce; }
        .hero .btn-outline:hover { background: #fff; }
        .hero-proof { display: flex; align-items: center; gap: 13px; margin-top: 28px; color: #344f68; font-size: .86rem; }
        .proof-mark { width: 28px; height: 28px; display: grid; place-items: center; border-radius: 50%; background: #2563eb18; color: #1d4ed8; }
        .section { padding: 90px 0; }
        .section-soft { background: var(--surface); }
        .section-heading { max-width: 650px; margin: 0 auto 42px; text-align: center; }
        .section-kicker { color: var(--blue); text-transform: uppercase; letter-spacing: .13em; font-size: .75rem; font-weight: 800; }
        h2 { margin: 12px 0; color: var(--navy); font-size: clamp(2rem, 3.2vw, 2.8rem); line-height: 1.15; letter-spacing: -.04em; }
        .section-heading p { margin: 0; color: var(--muted); line-height: 1.75; }
        .service-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; }
        .service-card { display: flex; flex-direction: column; min-height: 255px; overflow: hidden; border: 1px solid var(--line); border-radius: 15px; background: #fff; box-shadow: 0 8px 24px #123b5d08; transition: transform .18s ease, box-shadow .18s ease; }
        .service-card:hover { transform: translateY(-3px); box-shadow: 0 14px 32px #123b5d14; }
        .service-thumb { height: 150px; display: grid; place-items: center; overflow: hidden; background: linear-gradient(135deg, #eaf1ff, #dbe8fb); }
        .service-thumb img { width: 100%; height: 100%; object-fit: cover; transition: transform .35s ease; }
        .service-card:hover .service-thumb img { transform: scale(1.04); }
        .service-thumb-icon { width: 62px; height: 62px; display: grid; place-items: center; border-radius: 17px; background: #ffffffb8; color: var(--blue); box-shadow: 0 8px 18px #123b5d12; }
        .service-thumb-icon svg { width: 33px; height: 33px; }
        .service-body { display: flex; flex: 1; flex-direction: column; padding: 24px; }
        .service-icon { width: 47px; height: 47px; display: grid; place-items: center; color: var(--blue); border-radius: 13px; background: var(--blue-light); }
        .service-icon svg { width: 24px; height: 24px; }
        .service-category { color: var(--blue); font-size: .75rem; font-weight: 700; }
        .service-card h3 { margin: 7px 0 8px; color: var(--navy); font-size: 1.08rem; line-height: 1.35; }
        .service-card h3 a:hover { color: var(--blue); }
        .service-card p { margin: 0 0 16px; color: var(--muted); font-size: .88rem; line-height: 1.6; }
        .service-meta { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 12px; margin-top: auto; padding-top: 14px; border-top: 1px solid var(--line); color: #506174; font-size: .82rem; }
        .service-meta strong { color: var(--navy); font-size: .95rem; }
        .service-actions { display: flex; flex-wrap: wrap; gap: 9px; margin-top: 15px; }
        .service-actions .btn { min-height: 38px; padding: 0 14px; font-size: .84rem; }
        .explore-all { display: flex; justify-content: center; margin-top: 26px; }
        .empty-state { padding: 34px; border: 1px dashed #b9c9d8; border-radius: 15px; background: #fff; text-align: center; }
        .empty-state strong { color: var(--navy); }
        .empty-state p { margin: 8px 0 0; color: var(--muted); }
        .steps { display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px; counter-reset: step; }
        .step { position: relative; padding: 25px 20px; border: 1px solid var(--line); border-radius: 15px; background: #fff; counter-increment: step; }
        .step-number { width: 42px; height: 42px; display: grid; place-items: center; margin-bottom: 18px; border-radius: 13px; background: var(--navy); color: white; font-weight: 800; }
        .step-number::before { content: "0" counter(step); }
        .step h3 { margin: 0 0 9px; color: var(--navy); font-size: 1rem; }
        .step p { margin: 0; color: var(--muted); font-size: .86rem; line-height: 1.65; }
        .booking-band { display: flex; align-items: center; justify-content: space-between; gap: 28px; padding: 38px 42px; border-radius: 20px; background: linear-gradient(110deg, var(--navy), #1a527a); color: #fff; }
        .booking-band h2 { margin: 0 0 9px; color: #fff; font-size: clamp(1.6rem, 3vw, 2.1rem); }
        .booking-band p { margin: 0; color: #d4e4f0; line-height: 1.65; }
        .booking-band .btn { flex: 0 0 auto; background: #fff; color: var(--navy); }

/* CONTACT SECTION */
.contact-grid {
    display: grid;
    grid-template-columns: 1.1fr .9fr;
    gap: 42px;
    align-items: center;
}

.contact-copy h2 {
    margin: 12px 0;
}

.contact-copy > p {
    color: var(--muted);
    line-height: 1.8;
    max-width: 550px;
}

.contact-list {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
    margin-top: 28px;
}

.contact-item {
    display: flex;
    align-items: flex-start;
    gap: 13px;
    padding: 18px;
    border: 1px solid var(--line);
    border-radius: 14px;
    background: #fff;
    transition: .25s ease;
}

.contact-item:hover {
    transform: translateY(-4px);
    border-color: #bfdbfe;
    box-shadow: 0 10px 25px #123b5d12;
}

.contact-symbol {
    width: 44px;
    height: 44px;
    flex: 0 0 auto;
    display: grid;
    place-items: center;
    border-radius: 12px;
    background: var(--blue-light);
    color: var(--blue);
    font-size: 20px;
    font-weight: 800;
}

.contact-item strong {
    display: block;
    color: var(--navy);
    font-size: .92rem;
}

.contact-item span {
    display: block;
    margin-top: 5px;
    color: var(--muted);
    font-size: .85rem;
    line-height: 1.6;
    overflow-wrap: anywhere;
}

.contact-card {
    position: relative;
    overflow: hidden;
    padding: 38px;
    border: none;
    border-radius: 20px;
    background: linear-gradient(135deg, #123b5d, #1d5b85);
    box-shadow: 0 15px 35px #123b5d20;
}

.contact-card::before {
    content: "";
    position: absolute;
    width: 180px;
    height: 180px;
    top: -80px;
    right: -60px;
    border: 30px solid #ffffff12;
    border-radius: 50%;
}

.contact-card h3 {
    position: relative; 
    margin: 0 0 14px;
    color: #fff;
    font-size: 1.4rem;
    line-height: 1.4;
}

.contact-card p {
    position: relative;
    margin: 0 0 24px;
    color: #d4e4f0;
    line-height: 1.75;
}

.contact-card .btn {
    position: relative;
    background: #fff;
    color: var(--navy);
    min-height: 48px;
}

.contact-card .btn:hover {
    background: #eaf1ff;
}

@media (max-width: 680px) {
    .contact-grid {
        grid-template-columns: 1fr;
        gap: 28px;
    }

    .contact-list {
        grid-template-columns: 1fr;
    }

    .contact-card {
        padding: 28px;
    }
}

        .site-footer { padding: 38px 0 22px; background: #0b2740; color: #d4e2ed; }
        .footer-main { display: flex; justify-content: space-between; gap: 32px; padding-bottom: 28px; }
        .footer-brand { color: #fff; }
        .footer-copy { max-width: 380px; margin: 13px 0 0; color: #aebfce; font-size: .88rem; line-height: 1.7; }
        .footer-links { display: flex; flex-wrap: wrap; align-content: flex-start; gap: 14px 24px; color: #d4e2ed; font-size: .88rem; }
        .copyright { padding-top: 18px; border-top: 1px solid #ffffff1a; color: #aebfce; font-size: .8rem; }
        @media (max-width: 920px) {
            .service-grid { grid-template-columns: repeat(2, 1fr); }
            .steps { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 680px) {
            .container { width: min(100% - 32px, 560px); }
            .brand { font-size: 1rem; }
            .brand-mark { width: 38px; height: 38px; }
            .hero-grid { min-height: unset; padding-top: 60px; padding-bottom: 48px; }
            h1 { max-width: 520px; font-size: clamp(2.5rem, 12vw, 3.7rem); }
            .hero-copy { font-size: 1rem; }
            .section { padding: 66px 0; }
            .section-heading { margin-bottom: 30px; }
            .service-grid, .steps, .contact-grid { grid-template-columns: 1fr; }
            .service-card { min-height: 230px; }
            .booking-band { align-items: flex-start; flex-direction: column; padding: 29px 24px; }
            .contact-grid { gap: 26px; }
            .footer-main { flex-direction: column; }
        }
        @media (prefers-reduced-motion: reduce) { *, *::before, *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; } }
    </style>
</head>
<body>
<?php
$activeCustomerNav = 'home';
$customerHeaderOnHome = true;
require __DIR__ . '/partials/customer-header.php';
?>

<main>
    <section class="hero" id="home">
        <div class="hero-background" aria-hidden="true">
            <div class="hero-slide is-active" style="background-image: url('<?= e(url('assets/images/d6036d73ca06c0f44b8b851c343eafdd.jpg')) ?>')"></div>
            <div class="hero-slide" style="background-image: url('<?= e(url('assets/images/d2d7a74f06ba822c9da94503ef0748a7.jpg')) ?>')"></div>
            <div class="hero-slide" style="background-image: url('<?= e(url('assets/images/0d6c1b9e335745cf224304410dfa70fc.jpg')) ?>')"></div>
        </div>
        <div class="container hero-grid">
            <div>
                <span class="eyebrow">Chăm xe chuẩn chuyên gia</span>
                <h1>Vững tay lái.<br>Trọn hành trình.</h1>
                <p class="hero-copy">Chăm sóc và sửa chữa ô tô với quy trình rõ ràng, thông tin minh bạch và lịch sử dịch vụ được lưu trữ ngay trong tài khoản của bạn.</p>
                <div class="hero-actions">
                    <a class="btn btn-primary" href="#services">
                        Khám phá dịch vụ
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                    <a class="btn btn-outline" href="<?= e($bookingAction) ?>"><?= e($bookingLabel) ?></a>
                </div>
                <div class="hero-proof"><span class="proof-mark">✓</span><span>Hồ sơ xe và dịch vụ được quản lý tập trung</span></div>
            </div>
        </div>
        <div class="hero-pagination" role="group" aria-label="Chọn ảnh nền">
            <button type="button" aria-label="Ảnh nền 1" aria-pressed="true"></button>
            <button type="button" aria-label="Ảnh nền 2" aria-pressed="false"></button>
            <button type="button" aria-label="Ảnh nền 3" aria-pressed="false"></button>
        </div>
    </section>

    <section class="section" id="services">
        <div class="container">
            <?php
            $serviceIcon = static function (string $name): string {
                $icons = [
                    'Bảo dưỡng định kỳ' => '<path d="m14.7 6.3 3-3a5 5 0 0 1-6.6 6.6l-7 7a2.1 2.1 0 0 0 3 3l7-7a5 5 0 0 1 6.6-6.6l-3 3-3-3Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>',
                    'Sửa chữa chung và khắc phục sự cố' => '<path d="M12 8.5a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7Z" stroke="currentColor" stroke-width="1.7"/><path d="m19 12 2 1-2 1v2l-2 .5-1 1.5-1.7-.7-1.3 1.2-1.5-1-1.5 1-1.3-1.2-1.7.7-1-1.5-2-.5v-2l-2-1 2-1v-2l2-.5 1-1.5 1.7.7L9.3 5l1.5 1 1.5-1 1.3 1.2 1.7-.7 1 1.5 2 .5v2Z" stroke="currentColor" stroke-width="1.2" stroke-linejoin="round"/>',
                    'Đồng sơn và phục hồi thân vỏ' => '<path d="M4 5h10v6H4z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M14 7h3a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-3M8 11v8m4-8v8" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>',
                    'Chăm sóc và làm đẹp xe' => '<path d="m12 3 1.7 4.3L18 9l-4.3 1.7L12 15l-1.7-4.3L6 9l4.3-1.7L12 3Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="m18 14 .9 2.1L21 17l-2.1.9L18 20l-.9-2.1L15 17l2.1-.9L18 14Z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/>',
                    'Lắp đặt phụ kiện và nâng cấp xe' => '<rect x="3" y="7" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.7"/><path d="m17 10 4-2v8l-4-2" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>',
                    'Dịch vụ khẩn cấp và tiện ích' => '<path d="M6 3h3l2 5-2 1a12 12 0 0 0 6 6l1-2 5 2v3a2 2 0 0 1-2 2A17 17 0 0 1 4 5a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>',
                ];

                return $icons[$name] ?? '<path d="M5 13.5h14l-1.3-4a2 2 0 0 0-1.9-1.4H8.2a2 2 0 0 0-1.9 1.4L5 13.5Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M6 13.5v4h2m10-4v4h-2M6 15h2m8 0h2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>';
            };
            ?>
            <div class="section-heading">
                <span class="section-kicker">Dịch vụ của chúng tôi</span>
                <h2>Chăm sóc toàn diện cho chiếc xe của bạn</h2>
                <p>Giá khởi điểm tham khảo tại Hà Nội cho xe phổ thông; báo giá cuối cùng có thể thay đổi theo dòng xe, phụ tùng, vật tư và tình trạng thực tế.</p>
            </div>
            <?php if ($services === []): ?>
                <div class="empty-state">
                    <strong>Danh mục dịch vụ đang được cập nhật</strong>
                    <p>Hiện chưa có dịch vụ nào được gara kích hoạt. Vui lòng quay lại sau hoặc liên hệ gara để được hỗ trợ.</p>
                </div>
            <?php else: ?>
                <div class="service-grid">
                    <?php foreach ($services as $service): ?>
                        <?php
                        $serviceImage = service_image_url($service['image'] ?? null);
                        $serviceBookingUrl = $isCustomer
                            ? url('appointments/create?service_id=' . (int) $service['id'])
                            : ($userRole === 'admin' ? url('admin') : url('login'));
                        $servicePrice = (float) ($service['price'] ?? 0);
                        $serviceDuration = (int) ($service['duration'] ?? 0);
                        ?>
                        <article class="service-card">
                            <div class="service-thumb">
                                <?php if ($serviceImage !== null): ?>
                                    <img src="<?= e($serviceImage) ?>" alt="<?= e($service['name']) ?>" width="1200" height="800" loading="lazy" decoding="async">
                                <?php else: ?>
                                    <span class="service-thumb-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none"><?= $serviceIcon((string) $service['category_name']) ?></svg>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="service-body">
                                <span class="service-category"><?= e($service['category_name']) ?></span>
                                <h3><a href="<?= e(url('services/' . (int) $service['id'])) ?>"><?= e($service['name']) ?></a></h3>
                                <p><?= e($service['description'] ?: 'Dịch vụ chăm sóc và sửa chữa xe tại gara.') ?></p>
                                <div class="service-meta">
                                    <strong><?= e($servicePrice > 0 ? 'Từ ' . number_format($servicePrice, 0, ',', '.') . ' ₫' : 'Liên hệ để được báo giá') ?></strong>
                                    <span><?= e($serviceDuration > 0 ? 'Khoảng ' . $serviceDuration . ' phút' : 'Liên hệ để biết thời gian') ?></span>
                                </div>
                                <div class="service-actions">
                                    <a class="btn btn-outline" href="<?= e(url('services/' . (int) $service['id'])) ?>">Xem chi tiết</a>
                                    <a class="btn btn-primary" href="<?= e($serviceBookingUrl) ?>"><?= e($isCustomer ? 'Đặt lịch' : ($userRole === 'admin' ? 'Mở quản trị' : 'Đăng nhập')) ?></a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
                <div class="explore-all">
                    <a class="btn btn-outline" href="<?= e(url('services')) ?>">
                        KHÁM PHÁ TẤT CẢ DỊCH VỤ
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="section section-soft" id="process">
        <div class="container">
            <div class="section-heading">
                <span class="section-kicker">Quy trình phục vụ</span>
                <h2>Mỗi bước đều rõ ràng, dễ theo dõi</h2>
                <p>Từ lúc tiếp nhận xe đến khi hoàn tất, khách hàng có thể nắm được các bước trong quy trình sửa chữa.</p>
            </div>
            <div class="steps">
                <article class="step"><span class="step-number"></span><h3>Đăng ký xe</h3><p>Tạo tài khoản và lưu thông tin phương tiện để thuận tiện theo dõi.</p></article>
                <article class="step"><span class="step-number"></span><h3>Tiếp nhận yêu cầu</h3><p>Chọn dịch vụ, gửi lịch hẹn và nhận xác nhận từ gara.</p></article>
                <article class="step"><span class="step-number"></span><h3>Kiểm tra & sửa chữa</h3><p>Kỹ thuật viên ghi nhận chẩn đoán, hạng mục và tiến độ xử lý.</p></article>
                <article class="step"><span class="step-number"></span><h3>Bàn giao & lưu hồ sơ</h3><p>Hoàn tất dịch vụ, đối soát hóa đơn và lưu lại lịch sử chăm sóc xe.</p></article>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="booking-band">
                <div>
                    <h2>Sẵn sàng chăm sóc chiếc xe của bạn?</h2>
                    <p><?= $isCustomer ? 'Đặt lịch tại gara từ tài khoản của bạn để gara xác nhận thời gian phù hợp.' : 'Đăng nhập để gửi yêu cầu đặt lịch và quản lý phương tiện của bạn.' ?></p>
                </div>
                <a class="btn" href="<?= e($bookingAction) ?>">
                    <?= e($bookingLabel) ?>
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>
            </div>
        </div>
    </section>


<section class="section section-soft" id="contact">
    <div class="container contact-grid">

        <div class="contact-copy">

            <span class="section-kicker">LIÊN HỆ VỚI CHÚNG TÔI</span>

            <h2>Kết nối với AutoCare Hub</h2>

            <p>
                Chúng tôi luôn sẵn sàng đồng hành cùng bạn
                trong quá trình chăm sóc, bảo dưỡng và sửa chữa ô tô.
                Hãy liên hệ với gara để được hỗ trợ nhanh chóng.
            </p>

            <div class="contact-list">

                <div class="contact-item">
                    <span class="contact-symbol">☎</span>
                    <div>
                        <strong>Điện thoại</strong>
                        <span>
                            <?= e(($garage['phone'] ?? '') !== '' ? $garage['phone'] : 'Chưa cập nhật thông tin') ?>
                        </span>
                    </div>
                </div>

                <div class="contact-item">
                    <span class="contact-symbol">⌖</span>
                    <div>
                        <strong>Địa chỉ gara</strong>
                        <span>
                            <?= e(($garage['address'] ?? '') !== '' ? $garage['address'] : 'Chưa cập nhật thông tin') ?>
                        </span>
                    </div>
                </div>

                <div class="contact-item">
                    <span class="contact-symbol">◷</span>
                    <div>
                        <strong>Giờ hoạt động</strong>
                        <span>
                            <?= e(($garage['hours'] ?? '') !== '' ? $garage['hours'] : 'Vui lòng liên hệ gara') ?>
                        </span>
                    </div>
                </div>

            </div>
        </div>

        <aside class="contact-card">

            <h3>Chăm sóc xe dễ dàng hơn mỗi ngày</h3>

            <p>
                Theo dõi phương tiện, quản lý lịch hẹn
                và cập nhật thông tin chăm sóc xe
                ngay trên tài khoản AutoCare Hub.
            </p>

            <a class="btn" href="<?= e($bookingAction) ?>">
                <?= e($bookingLabel) ?>
                <svg viewBox="0 0 24 24" fill="none"
                    aria-hidden="true">
                    <path d="M5 12h14m-6-6 6 6-6 6"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"/>
                </svg>
            </a>

        </aside>
    </div>
</section>

</main>

<footer class="site-footer">
    <div class="container">
        <div class="footer-main">
            <div>
                <a class="brand footer-brand" href="<?= e(url()) ?>"><span class="brand-mark" aria-hidden="true">A</span><span>AutoCare Hub</span></a>
                <p class="footer-copy">Nền tảng quản lý chăm sóc và sửa chữa ô tô, kết nối thông tin phương tiện, lịch dịch vụ và lịch sử bảo dưỡng.</p>
            </div>
            <nav class="footer-links" aria-label="Điều hướng chân trang">
                <a href="#services">Dịch vụ</a><a href="#process">Quy trình</a><a href="#contact">Liên hệ</a>
                <a href="<?= e($accountAction) ?>"><?= e($accountLabel) ?></a>
            </nav>
        </div>
        <div class="copyright">© <?= e(date('Y')) ?> AutoCare Hub. Chăm xe an tâm, hành trình trọn vẹn.</div>
    </div>
</footer>
<script>
    (() => {
        const hero = document.querySelector('.hero');
        if (!hero || hero.dataset.heroSliderInitialized === 'true') {
            return;
        }

        const slides = Array.from(hero.querySelectorAll('.hero-slide'));
        const buttons = Array.from(hero.querySelectorAll('.hero-pagination button'));
        if (slides.length !== 3 || buttons.length !== slides.length) {
            return;
        }

        hero.dataset.heroSliderInitialized = 'true';
        const motionPreference = window.matchMedia('(prefers-reduced-motion: reduce)');
        let current = Math.max(0, slides.findIndex((slide) => slide.classList.contains('is-active')));
        let timer = null;
        const slideImages = slides.map((slide) => {
            const imageUrl = getComputedStyle(slide).backgroundImage.match(/url\(["']?(.*?)["']?\)/)?.[1];
            if (!imageUrl) {
                throw new Error('Hero slide is missing its background image URL.');
            }

            const image = new Image();
            image.decoding = 'async';
            const loaded = new Promise((resolve, reject) => {
                image.onload = () => resolve(true);
                image.onerror = () => reject(new Error(`Unable to preload Hero image: ${imageUrl}`));
            });
            image.src = imageUrl;
            if (image.complete && image.naturalWidth > 0) {
                return Promise.resolve(true);
            }
            return loaded;
        });
        const preloadSlides = Promise.all(slideImages);
        let imagesReady = false;
        const showSlide = (index) => {
            preloadSlides.then(() => {
                if (index === current) {
                    return;
                }

                slides[current].classList.remove('is-active');
                buttons[current].setAttribute('aria-pressed', 'false');
                current = index;
                slides[current].classList.add('is-active');
                buttons[current].setAttribute('aria-pressed', 'true');
            }).catch((error) => {
                console.error(error);
            });
        };

        const syncAutoplay = () => {
            if (timer !== null) {
                window.clearInterval(timer);
                timer = null;
            }
            if (motionPreference.matches || !imagesReady) {
                return;
            }

            timer = window.setInterval(() => showSlide((current + 1) % slides.length), 4500);
        };

        buttons.forEach((button, index) => {
            button.addEventListener('click', () => {
                showSlide(index);
                syncAutoplay();
            });
        });
        preloadSlides.then(() => {
            imagesReady = true;
            syncAutoplay();
        }).catch((error) => {
            console.error(error);
        });
        if (typeof motionPreference.addEventListener === 'function') {
            motionPreference.addEventListener('change', syncAutoplay);
        } else {
            motionPreference.addListener(syncAutoplay);
        }
        syncAutoplay();
    })();

    (() => {
        const nav = document.querySelector('.customer-header-nav');
        const header = document.querySelector('.customer-site-header');
        const sections = ['home', 'services', 'process', 'contact']
            .map((id) => document.getElementById(id))
            .filter((section) => section !== null);
        const links = Array.from(nav?.querySelectorAll('a[href^="#"]') ?? []);
        if (!nav || !header || sections.length === 0 || links.length === 0) {
            return;
        }

        let updatePending = false;
        const updateActiveLink = () => {
            updatePending = false;
            const activeLine = header.getBoundingClientRect().bottom + 24;
            const atPageEnd = window.scrollY + window.innerHeight >= document.documentElement.scrollHeight - 2;
            let activeSection = atPageEnd ? sections[sections.length - 1] : sections[0];
            if (!atPageEnd) {
                sections.forEach((section) => {
                    if (section.getBoundingClientRect().top <= activeLine) {
                        activeSection = section;
                    }
                });
            }

            links.forEach((link) => {
                const isActive = link.hash === `#${activeSection.id}`;
                if (isActive) {
                    link.setAttribute('aria-current', 'page');
                } else {
                    link.removeAttribute('aria-current');
                }
            });
        };
        const scheduleUpdate = () => {
            if (updatePending) {
                return;
            }
            updatePending = true;
            window.requestAnimationFrame(updateActiveLink);
        };

        window.addEventListener('scroll', scheduleUpdate, { passive: true });
        window.addEventListener('resize', scheduleUpdate);
        nav.addEventListener('click', (event) => {
            if (event.target.closest('a[href^="#"]')) {
                const menuToggle = document.getElementById('customer-menu-toggle');
                if (menuToggle) {
                    menuToggle.checked = false;
                }
            }
        });
        updateActiveLink();
    })();
</script>
</body>
</html>
