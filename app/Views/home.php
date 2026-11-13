<?php

declare(strict_types=1);

$isCustomer = $userRole === 'customer';
$primaryAction = $userRole === 'admin'
    ? url('admin')
    : ($isCustomer ? url('vehicles') : url('register'));
$primaryActionLabel = $userRole === 'admin'
    ? 'Mở quản trị'
    : ($isCustomer ? 'Quản lý xe của tôi' : 'Tạo tài khoản khách hàng');
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
        .topline { background: var(--navy-deep); color: #d7e5f1; font-size: .82rem; }
        .topline-inner { min-height: 38px; display: flex; align-items: center; justify-content: space-between; gap: 20px; }
        .topline a { color: #fff; }
        .site-header { position: sticky; top: 0; z-index: 20; background: rgb(255 255 255 / 96%); border-bottom: 1px solid var(--line); backdrop-filter: blur(14px); }
        .nav-wrap { min-height: 76px; display: flex; align-items: center; justify-content: space-between; gap: 26px; }
        .brand { display: inline-flex; align-items: center; gap: 11px; color: var(--navy); font-size: 1.15rem; font-weight: 800; letter-spacing: -.04em; white-space: nowrap; }
        .brand-mark { width: 42px; height: 42px; display: grid; place-items: center; color: white; border-radius: 13px; background: var(--blue); box-shadow: 0 8px 18px #2563eb35; }
        .brand-mark svg { width: 25px; height: 25px; }
        .nav-links { display: flex; align-items: center; gap: 28px; color: #4a5b6d; font-size: .92rem; font-weight: 600; }
        .nav-links a:not(.nav-account) { position: relative; padding: 8px 0; transition: color .18s ease; }
        .nav-links a:not(.nav-account)::after { position: absolute; right: 0; bottom: 1px; left: 0; height: 2px; background: var(--blue); content: ""; transform: scaleX(0); transform-origin: left; transition: transform .18s ease; }
        .nav-links a:not(.nav-account):hover, .nav-links a[aria-current="page"], .footer-links a:hover { color: var(--blue); }
        .nav-links a:not(.nav-account):hover::after, .nav-links a[aria-current="page"]::after { transform: scaleX(1); }
        .nav-actions { display: flex; align-items: center; gap: 12px; }
        .menu-toggle { display: none; }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 9px; min-height: 46px; padding: 0 19px; border: 1px solid transparent; border-radius: 9px; font-size: .92rem; font-weight: 700; transition: transform .18s ease, box-shadow .18s ease, background .18s ease; cursor: pointer; }
        .btn:hover { transform: translateY(-1px); }
        .btn-primary { background: var(--blue); color: #fff; box-shadow: 0 9px 22px #2563eb2c; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-outline { color: var(--navy); border-color: #cbd6e1; background: #fff; }
        .btn-outline:hover { background: var(--surface); }
        .btn svg { width: 17px; height: 17px; }
        .hero { overflow: hidden; position: relative; background: radial-gradient(ellipse at 86% 16%, #2c5c7e 0, transparent 37%), linear-gradient(118deg, #0b2740 0%, #123b5d 54%, #17486b 100%); color: white; }
        .hero-background { position: absolute; z-index: 0; inset: 0; overflow: hidden; }
        .hero-slide { position: absolute; inset: 0; background-position: center; background-size: cover; opacity: 0; transition: opacity 1s cubic-bezier(.22, .61, .36, 1); will-change: opacity; }
        .hero-slide.is-active { opacity: 1; }
        .hero-background::after { content: ""; position: absolute; inset: 0; background: linear-gradient(90deg, rgb(5 24 42 / 95%) 0%, rgb(8 34 56 / 90%) 55%, rgb(12 45 70 / 84%) 100%); }
        .hero-grid { position: relative; z-index: 1; min-height: 548px; display: grid; grid-template-columns: minmax(0, 1fr); align-items: center; padding-top: 40px; padding-bottom: 44px; }
        .hero-pagination { position: absolute; z-index: 2; right: 0; bottom: 10px; left: 0; display: flex; justify-content: center; gap: 4px; }
        .hero-pagination button { display: grid; width: 34px; height: 34px; padding: 0; place-items: center; border: 0; border-radius: 50%; background: transparent; cursor: pointer; }
        .hero-pagination button::before { content: ""; width: 9px; height: 9px; border: 1px solid #fff; border-radius: 50%; background: #ffffff55; transition: background-color .25s ease; }
        .hero-pagination button[aria-pressed="true"]::before { background: #fff; }
        .hero-pagination button:focus-visible { outline: 2px solid #fff; outline-offset: 1px; }
        .eyebrow { display: inline-flex; align-items: center; gap: 9px; color: #b8d5fa; text-transform: uppercase; letter-spacing: .12em; font-size: .75rem; font-weight: 800; }
        .eyebrow::before { content: ""; width: 24px; height: 2px; background: #60a5fa; }
        h1 { max-width: 650px; margin: 20px 0 18px; font-size: clamp(2.6rem, 5vw, 4.65rem); line-height: 1.04; letter-spacing: -.055em; }
        .hero-copy { max-width: 520px; color: #d2e0eb; font-size: 1.08rem; line-height: 1.75; }
        .hero-actions { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 27px; }
        .hero .btn-outline { color: white; background: #ffffff0a; border-color: #ffffff48; }
        .hero .btn-outline:hover { background: #ffffff17; }
        .hero-proof { display: flex; align-items: center; gap: 13px; margin-top: 28px; color: #d2e0eb; font-size: .86rem; }
        .proof-mark { width: 28px; height: 28px; display: grid; place-items: center; border-radius: 50%; background: #60a5fa24; color: #9dc5ff; }
        .section { padding: 90px 0; }
        .section-soft { background: var(--surface); }
        .section-heading { max-width: 650px; margin: 0 auto 42px; text-align: center; }
        .section-kicker { color: var(--blue); text-transform: uppercase; letter-spacing: .13em; font-size: .75rem; font-weight: 800; }
        h2 { margin: 12px 0; color: var(--navy); font-size: clamp(2rem, 3.2vw, 2.8rem); line-height: 1.15; letter-spacing: -.04em; }
        .section-heading p { margin: 0; color: var(--muted); line-height: 1.75; }
        .service-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; }
        .service-card { display: flex; flex-direction: column; min-height: 255px; padding: 24px; border: 1px solid var(--line); border-radius: 15px; background: #fff; box-shadow: 0 8px 24px #123b5d08; }
        .service-icon { width: 47px; height: 47px; display: grid; place-items: center; color: var(--blue); border-radius: 13px; background: var(--blue-light); }
        .service-icon svg { width: 24px; height: 24px; }
        .service-category { margin-top: 19px; color: var(--blue); font-size: .75rem; font-weight: 700; }
        .service-card h3 { margin: 7px 0 8px; color: var(--navy); font-size: 1.08rem; }
        .service-card p { margin: 0 0 18px; color: var(--muted); font-size: .88rem; line-height: 1.6; }
        .service-meta { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 12px; margin-top: auto; padding-top: 14px; border-top: 1px solid var(--line); color: #506174; font-size: .82rem; }
        .service-meta strong { color: var(--navy); font-size: .95rem; }
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
        .contact-grid { display: grid; grid-template-columns: 1.1fr .9fr; gap: 42px; align-items: center; }
        .contact-copy p { color: var(--muted); line-height: 1.75; }
        .contact-list { display: grid; gap: 14px; margin-top: 25px; }
        .contact-item { display: flex; align-items: flex-start; gap: 13px; color: var(--muted); }
        .contact-item strong { display: block; color: var(--navy); font-size: .92rem; }
        .contact-item span { display: block; margin-top: 3px; font-size: .88rem; }
        .contact-symbol { width: 40px; height: 40px; flex: 0 0 auto; display: grid; place-items: center; border-radius: 12px; background: var(--blue-light); color: var(--blue); font-weight: 800; }
        .contact-card { padding: 28px; border: 1px solid var(--line); border-radius: 17px; background: var(--surface); }
        .contact-card h3 { margin: 0 0 9px; color: var(--navy); }
        .contact-card p { margin: 0 0 19px; color: var(--muted); line-height: 1.65; }
        .site-footer { padding: 38px 0 22px; background: #0b2740; color: #d4e2ed; }
        .footer-main { display: flex; justify-content: space-between; gap: 32px; padding-bottom: 28px; }
        .footer-brand { color: #fff; }
        .footer-copy { max-width: 380px; margin: 13px 0 0; color: #aebfce; font-size: .88rem; line-height: 1.7; }
        .footer-links { display: flex; flex-wrap: wrap; align-content: flex-start; gap: 14px 24px; color: #d4e2ed; font-size: .88rem; }
        .copyright { padding-top: 18px; border-top: 1px solid #ffffff1a; color: #aebfce; font-size: .8rem; }
        @media (max-width: 920px) {
            .nav-links { gap: 17px; }
            .nav-account { display: none; }
            .service-grid { grid-template-columns: repeat(2, 1fr); }
            .steps { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 680px) {
            .container { width: min(100% - 32px, 560px); }
            .topline-inner { justify-content: center; min-height: 34px; }
            .topline-note { display: none; }
            .nav-wrap { min-height: 66px; flex-wrap: wrap; gap: 0; }
            .brand { font-size: 1rem; }
            .brand-mark { width: 38px; height: 38px; }
            .nav-actions { order: 2; gap: 8px; }
            .nav-actions .btn { min-height: 39px; padding: 0 12px; font-size: .82rem; }
            .menu-toggle { display: block; position: absolute; opacity: 0; pointer-events: none; }
            .menu-label { display: grid; place-items: center; width: 40px; height: 40px; margin-left: 2px; border: 1px solid var(--line); border-radius: 9px; color: var(--navy); font-size: 1.2rem; cursor: pointer; }
            .nav-links { display: none; order: 3; width: 100%; padding: 3px 0 17px; flex-direction: column; align-items: stretch; gap: 0; }
            .nav-links a { padding: 11px 4px; border-top: 1px solid var(--line); }
            .menu-toggle:checked ~ .nav-links { display: flex; }
            .nav-wrap { position: relative; }
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
<div class="topline">
    <div class="container topline-inner">
        <span class="topline-note">Chăm sóc xe chuyên nghiệp, đồng hành trên mọi hành trình</span>
        <?php if (is_string($garage['phone'] ?? null) && $garage['phone'] !== ''): ?>
            <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $garage['phone'])) ?>">Hotline: <?= e($garage['phone']) ?></a>
        <?php else: ?>
            <span>Trung tâm chăm sóc và sửa chữa ô tô</span>
        <?php endif; ?>
    </div>
</div>
<header class="site-header">
    <div class="container nav-wrap">
        <a class="brand" href="<?= e(url()) ?>" aria-label="AutoCare Hub - Trang chủ">
            <span class="brand-mark" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none"><path d="M3 13.5h18l-1.6-5a2 2 0 0 0-1.9-1.4H6.5a2 2 0 0 0-1.9 1.4L3 13.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M4 13.5v4a1.5 1.5 0 0 0 1.5 1.5H7m13-5.5v4a1.5 1.5 0 0 1-1.5 1.5H17M3 15h2m14 0h2m-13-8 1 4m7-4-1 4m-5 5h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            </span>
            <span>AutoCare Hub</span>
        </a>
        <input class="menu-toggle" type="checkbox" id="menu-toggle" aria-label="Bật hoặc tắt menu">
        <nav class="nav-links" aria-label="Điều hướng chính">
            <a href="<?= e(url()) ?>" aria-current="page">Trang chủ</a>
            <a href="#services">Dịch vụ</a>
            <a href="#process">Quy trình</a>
            <a href="#contact">Liên hệ</a>
            <?php if ($userRole === 'customer'): ?><a href="<?= e(url('vehicles')) ?>">Xe của tôi</a><?php endif; ?>
            <?php if ($userRole === 'customer'): ?><a href="<?= e(url('appointments')) ?>">Lịch hẹn</a><?php endif; ?>
        </nav>
        <div class="nav-actions">
            <a class="btn btn-primary" href="<?= e($primaryAction) ?>"><?= e($primaryActionLabel) ?></a>
            <a class="nav-account btn btn-outline" href="<?= e($accountAction) ?>"><?= e($accountLabel) ?></a>
            <?php if ($isCustomer): ?>
                <form method="post" action="<?= e(url('logout')) ?>" style="margin:0">
                    <?= csrf_field() ?>
                    <button class="btn btn-outline" type="submit">Đăng xuất</button>
                </form>
            <?php endif; ?>
            <label class="menu-label" for="menu-toggle" aria-label="Mở menu">☰</label>
        </div>
    </div>
</header>

<main>
    <section class="hero">
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
            <div class="section-heading">
                <span class="section-kicker">Dịch vụ của chúng tôi</span>
                <h2>Chăm sóc toàn diện cho chiếc xe của bạn</h2>
                <p>Danh mục hiển thị trực tiếp từ hệ thống dịch vụ của gara. Giá và thời lượng được cập nhật theo dữ liệu đang áp dụng.</p>
            </div>
            <?php if ($services === []): ?>
                <div class="empty-state">
                    <strong>Danh mục dịch vụ đang được cập nhật</strong>
                    <p>Hiện chưa có dịch vụ nào được gara kích hoạt. Vui lòng quay lại sau hoặc liên hệ gara để được hỗ trợ.</p>
                </div>
            <?php else: ?>
                <div class="service-grid">
                    <?php foreach ($services as $service): ?>
                        <article class="service-card">
                            <span class="service-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none"><path d="m14.7 6.3 3-3a5 5 0 0 1-6.6 6.6l-7 7a2.1 2.1 0 0 0 3 3l7-7a5 5 0 0 1 6.6-6.6l-3 3-3-3Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                            <span class="service-category"><?= e($service['category_name']) ?></span>
                            <h3><?= e($service['name']) ?></h3>
                            <p><?= e($service['description'] ?: 'Dịch vụ chăm sóc và sửa chữa xe tại gara.') ?></p>
                            <div class="service-meta">
                                <strong><?= e(number_format((float) $service['price'], 0, ',', '.')) ?> ₫</strong>
                                <span>Dự kiến <?= e((int) $service['duration']) ?> phút</span>
                            </div>
                        </article>
                    <?php endforeach; ?>
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
                    <p><?= $isCustomer ? 'Đặt lịch tại gara hoặc gửi yêu cầu tư vấn trực tuyến từ tài khoản của bạn.' : 'Đăng nhập để gửi yêu cầu đặt lịch và quản lý phương tiện của bạn.' ?></p>
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
                <span class="section-kicker">Liên hệ</span>
                <h2>Gara luôn sẵn sàng hỗ trợ</h2>
                <p>Thông tin liên hệ hiển thị theo cấu hình của gara để khách hàng chủ động kết nối và được tư vấn.</p>
                <div class="contact-list">
                    <div class="contact-item"><span class="contact-symbol">☎</span><div><strong>Điện thoại</strong><span><?= e(($garage['phone'] ?? '') !== '' ? $garage['phone'] : 'Chưa cập nhật thông tin') ?></span></div></div>
                    <div class="contact-item"><span class="contact-symbol">⌖</span><div><strong>Địa chỉ gara</strong><span><?= e(($garage['address'] ?? '') !== '' ? $garage['address'] : 'Chưa cập nhật thông tin') ?></span></div></div>
                    <div class="contact-item"><span class="contact-symbol">◷</span><div><strong>Giờ hoạt động</strong><span><?= e(($garage['hours'] ?? '') !== '' ? $garage['hours'] : 'Vui lòng liên hệ gara') ?></span></div></div>
                </div>
            </div>
            <aside class="contact-card">
                <h3>Quản lý dịch vụ ngay trên tài khoản</h3>
                <p>Theo dõi phương tiện, lịch hẹn và thông tin chăm sóc xe tại một nơi.</p>
                <a class="btn btn-primary" href="<?= e($bookingAction) ?>"><?= e($bookingLabel) ?></a>
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
</script>
</body>
</html>
