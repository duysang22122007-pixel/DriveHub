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

$formatPrice = static function (array $item): string {
    $price = (float) ($item['price'] ?? 0);
    return $price > 0 ? number_format($price, 0, ',', '.') . ' ₫' : 'Liên hệ để được báo giá';
};
$formatDuration = static function (array $item): string {
    $duration = (int) ($item['duration'] ?? 0);
    return $duration > 0 ? 'Khoảng ' . $duration . ' phút' : 'Liên hệ để biết thời gian';
};
$bookingUrl = $userRole === 'customer'
    ? url('appointments/create?service_id=' . (int) $service['id'])
    : ($userRole === 'admin' ? url('admin') : url('login'));
$bookingLabel = $userRole === 'customer' ? 'Đặt lịch dịch vụ này' : ($userRole === 'admin' ? 'Mở quản trị' : 'Đăng nhập để đặt lịch');
$iconForCategory = static function (string $name): string {
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
$image = service_image_url($service['image'] ?? null);
$contentDescription = is_string($content['description'] ?? null) ? $content['description'] : '';
$benefits = is_array($content['benefits'] ?? null) ? $content['benefits'] : [];
$items = is_array($content['items'] ?? null) ? $content['items'] : [];
$process = is_array($content['process'] ?? null) ? $content['process'] : [];
$notes = is_array($content['notes'] ?? null) ? $content['notes'] : [];
$hasEditorialContent = $contentDescription !== '' || $benefits !== [] || $items !== [] || $process !== [] || $notes !== [];
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= e($service['description'] ?: 'Thông tin dịch vụ ' . $service['name'] . ' tại AutoCare Hub.') ?>">
    <title><?= e($service['name']) ?> | AutoCare Hub</title>
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
        .btn-small { min-height: 38px; padding: 0 14px; font-size: .84rem; }
        .btn svg { width: 17px; height: 17px; }
        .breadcrumb { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-bottom: 22px; color: #b8d5fa; font-size: .84rem; }
        .breadcrumb a:hover { color: #fff; }
        .breadcrumb span { color: #8fb6d8; }
        .detail-hero { padding: 46px 0 54px; background: radial-gradient(ellipse at 88% 10%, #2c5c7e 0, transparent 42%), linear-gradient(118deg, #0b2740 0%, #123b5d 58%, #17486b 100%); color: #fff; }
        .detail-hero-grid { display: grid; grid-template-columns: minmax(0, 1fr) 300px; gap: 40px; align-items: center; }
        .detail-category { display: inline-flex; align-items: center; gap: 9px; color: #b8d5fa; text-transform: uppercase; letter-spacing: .1em; font-size: .74rem; font-weight: 800; }
        .detail-category::before { content: ""; width: 24px; height: 2px; background: #60a5fa; }
        .detail-hero h1 { max-width: 760px; margin: 16px 0 14px; font-size: clamp(2.1rem, 4.4vw, 3.4rem); line-height: 1.08; letter-spacing: -.05em; }
        .detail-hero p { max-width: 680px; margin: 0; color: #d2e0eb; font-size: 1rem; line-height: 1.75; }
        .detail-meta { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 22px; }
        .meta-pill { display: inline-flex; align-items: center; gap: 8px; padding: 9px 13px; border: 1px solid #ffffff2b; border-radius: 999px; background: #ffffff0d; color: #e4eef6; font-size: .84rem; font-weight: 700; }
        .meta-pill strong { color: #fff; }
        .detail-actions { display: flex; flex-wrap: wrap; gap: 11px; margin-top: 26px; }
        .detail-hero .btn-outline { color: #fff; background: #ffffff0a; border-color: #ffffff48; }
        .detail-hero .btn-outline:hover { background: #ffffff17; }
        .hero-thumb { display: grid; min-height: 260px; place-items: center; overflow: hidden; border: 1px solid #ffffff24; border-radius: 18px; background: linear-gradient(135deg, #ffffff18, #ffffff08); }
        .hero-thumb img { width: 100%; height: 100%; min-height: 260px; object-fit: cover; }
        .hero-thumb-icon { width: 86px; height: 86px; display: grid; place-items: center; border-radius: 24px; background: #ffffff1c; color: #bcd8f7; }
        .hero-thumb-icon svg { width: 46px; height: 46px; }
        .section { padding: 64px 0; }
        .section-soft { background: var(--surface); }
        .section-kicker { color: var(--blue); text-transform: uppercase; letter-spacing: .13em; font-size: .75rem; font-weight: 800; }
        h2 { margin: 12px 0; color: var(--navy); font-size: clamp(1.6rem, 2.8vw, 2.3rem); line-height: 1.15; letter-spacing: -.04em; }
        h3 { color: var(--navy); }
        .detail-grid { display: grid; grid-template-columns: minmax(0, 1fr) 330px; gap: 34px; align-items: start; }
        .detail-main { display: grid; gap: 18px; }
        .detail-block { padding: 26px; border: 1px solid var(--line); border-radius: 16px; background: #fff; box-shadow: 0 8px 24px #123b5d08; }
        .detail-block h2 { margin: 0 0 14px; font-size: 1.35rem; }
        .detail-block p { margin: 0; color: var(--muted); line-height: 1.75; }
        .check-list, .item-list, .note-list { display: grid; gap: 11px; margin: 0; padding: 0; list-style: none; }
        .check-list li { display: flex; gap: 10px; color: #4d5e70; line-height: 1.65; }
        .check-list li::before { content: "✓"; flex: 0 0 auto; width: 22px; height: 22px; display: grid; place-items: center; border-radius: 50%; background: var(--blue-light); color: var(--blue); font-size: .78rem; font-weight: 900; }
        .item-list li { position: relative; padding-left: 20px; color: #4d5e70; line-height: 1.65; }
        .item-list li::before { content: ""; position: absolute; top: .55em; left: 0; width: 7px; height: 7px; border-radius: 50%; background: var(--blue); }
        .process-list { display: grid; gap: 14px; margin: 0; padding: 0; list-style: none; counter-reset: process; }
        .process-list li { position: relative; padding-left: 46px; color: #4d5e70; line-height: 1.65; counter-increment: process; }
        .process-list li::before { content: counter(process, decimal-leading-zero); position: absolute; top: 0; left: 0; width: 32px; height: 32px; display: grid; place-items: center; border-radius: 9px; background: var(--navy); color: #fff; font-size: .78rem; font-weight: 800; }
        .note-list li { padding: 11px 13px; border-left: 3px solid #f59e0b; border-radius: 0 8px 8px 0; background: #fffbeb; color: #6b5a2e; font-size: .88rem; line-height: 1.6; }
        .detail-aside { position: sticky; top: 96px; display: grid; gap: 16px; }
        .booking-card { padding: 24px; border-radius: 17px; background: linear-gradient(150deg, var(--navy), #1a527a); color: #fff; }
        .booking-card h2 { margin: 9px 0 10px; color: #fff; font-size: 1.45rem; }
        .booking-card p { margin: 0 0 18px; color: #d4e4f0; line-height: 1.65; font-size: .9rem; }
        .booking-card .btn { width: 100%; background: #fff; color: var(--navy); }
        .contact-card { padding: 22px; border: 1px solid var(--line); border-radius: 16px; background: #fff; }
        .contact-card h3 { margin: 0 0 12px; font-size: 1rem; }
        .contact-list { display: grid; gap: 12px; }
        .contact-item { display: flex; align-items: flex-start; gap: 11px; color: var(--muted); font-size: .86rem; }
        .contact-item strong { display: block; color: var(--navy); font-size: .84rem; }
        .contact-item span { display: block; margin-top: 2px; }
        .contact-symbol { width: 34px; height: 34px; flex: 0 0 auto; display: grid; place-items: center; border-radius: 9px; background: var(--blue-light); color: var(--blue); font-weight: 800; }
        .empty-state { padding: 26px; border: 1px dashed #b9c9d8; border-radius: 14px; background: #fff; text-align: center; }
        .empty-state strong { color: var(--navy); }
        .empty-state p { margin: 8px 0 0; color: var(--muted); }
        .related-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; }
        .related-card { display: flex; flex-direction: column; overflow: hidden; border: 1px solid var(--line); border-radius: 15px; background: #fff; box-shadow: 0 8px 24px #123b5d08; transition: transform .18s ease, box-shadow .18s ease; }
        .related-card:hover { transform: translateY(-3px); box-shadow: 0 14px 32px #123b5d14; }
        .related-thumb { height: 120px; display: grid; place-items: center; overflow: hidden; background: linear-gradient(135deg, #eaf1ff, #dbe8fb); }
        .related-thumb img { width: 100%; height: 100%; object-fit: cover; transition: transform .35s ease; }
        .related-card:hover .related-thumb img { transform: scale(1.04); }
        .related-thumb-icon { width: 54px; height: 54px; display: grid; place-items: center; border-radius: 15px; background: #ffffffb8; color: var(--blue); }
        .related-thumb-icon svg { width: 29px; height: 29px; }
        .related-body { display: flex; flex: 1; flex-direction: column; padding: 18px; }
        .related-body span { color: var(--blue); font-size: .72rem; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
        .related-body h3 { margin: 6px 0 9px; font-size: .98rem; line-height: 1.4; }
        .related-body h3 a:hover { color: var(--blue); }
        .related-meta { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 8px; margin-top: auto; padding-top: 12px; border-top: 1px solid var(--line); color: #506174; font-size: .78rem; }
        .related-meta strong { color: var(--navy); }
        .site-footer { padding: 38px 0 22px; background: #0b2740; color: #d4e2ed; }
        .footer-main { display: flex; justify-content: space-between; gap: 32px; padding-bottom: 28px; }
        .footer-brand { color: #fff; }
        .footer-copy { max-width: 380px; margin: 13px 0 0; color: #aebfce; font-size: .88rem; line-height: 1.7; }
        .footer-links { display: flex; flex-wrap: wrap; align-content: flex-start; gap: 14px 24px; color: #d4e2ed; font-size: .88rem; }
        .copyright { padding-top: 18px; border-top: 1px solid #ffffff1a; color: #aebfce; font-size: .8rem; }
        @media (max-width: 920px) {
            .nav-links { gap: 17px; }
            .nav-account { display: none; }
            .detail-hero-grid, .detail-grid { grid-template-columns: 1fr; }
            .detail-aside { position: static; grid-row: auto; }
            .related-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 680px) {
            .container { width: min(100% - 32px, 560px); }
            .topline-inner { justify-content: center; min-height: 34px; }
            .topline-note { display: none; }
            .nav-wrap { min-height: 66px; flex-wrap: wrap; gap: 0; position: relative; }
            .brand { font-size: 1rem; }
            .brand-mark { width: 38px; height: 38px; }
            .nav-actions { order: 2; gap: 8px; }
            .nav-actions .btn { min-height: 39px; padding: 0 12px; font-size: .82rem; }
            .menu-toggle { display: block; position: absolute; opacity: 0; pointer-events: none; }
            .menu-label { display: grid; place-items: center; width: 40px; height: 40px; margin-left: 2px; border: 1px solid var(--line); border-radius: 9px; color: var(--navy); font-size: 1.2rem; cursor: pointer; }
            .nav-links { display: none; order: 3; width: 100%; padding: 3px 0 17px; flex-direction: column; align-items: stretch; gap: 0; }
            .nav-links a { padding: 11px 4px; border-top: 1px solid var(--line); }
            .menu-toggle:checked ~ .nav-links { display: flex; }
            .detail-hero { padding: 34px 0 42px; }
            .section { padding: 50px 0; }
            .related-grid { grid-template-columns: 1fr; }
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
            <a href="<?= e(url()) ?>">Trang chủ</a>
            <a href="<?= e(url('services')) ?>">Dịch vụ</a>
            <a href="<?= e(url() . '#process') ?>">Quy trình</a>
            <a href="<?= e(url() . '#contact') ?>">Liên hệ</a>
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
    <section class="detail-hero">
        <div class="container">
            <nav class="breadcrumb" aria-label="Đường dẫn">
                <a href="<?= e(url()) ?>">Trang chủ</a><span>/</span>
                <a href="<?= e(url('services')) ?>">Dịch vụ</a><span>/</span>
                <span><?= e($service['name']) ?></span>
            </nav>
            <div class="detail-hero-grid">
                <div>
                    <span class="detail-category"><?= e($service['category_name']) ?></span>
                    <h1><?= e($service['name']) ?></h1>
                    <p><?= e($service['description'] ?: 'Dịch vụ chăm sóc và sửa chữa xe tại gara.') ?></p>
                    <div class="detail-meta">
                        <span class="meta-pill"><strong>Giá tham khảo:</strong> <?= e($formatPrice($service)) ?></span>
                        <span class="meta-pill"><strong>Thời gian:</strong> <?= e($formatDuration($service)) ?></span>
                    </div>
                    <div class="detail-actions">
                        <a class="btn btn-primary" href="<?= e($bookingUrl) ?>"><?= e($bookingLabel) ?></a>
                        <a class="btn btn-outline" href="<?= e(url('services')) ?>">Xem tất cả dịch vụ</a>
                    </div>
                </div>
                <div class="hero-thumb">
                    <?php if ($image !== null): ?>
                        <img src="<?= e($image) ?>" alt="<?= e($service['name']) ?>" width="1200" height="800" decoding="async">
                    <?php else: ?>
                        <span class="hero-thumb-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none"><?= $iconForCategory((string) $service['category_name']) ?></svg>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container detail-grid">
            <div class="detail-main">
                <?php if ($contentDescription !== ''): ?>
                    <article class="detail-block">
                        <h2>Giới thiệu dịch vụ</h2>
                        <p><?= e($contentDescription) ?></p>
                    </article>
                <?php endif; ?>

                <?php if ($benefits !== []): ?>
                    <article class="detail-block">
                        <h2>Vì sao nên chọn dịch vụ này?</h2>
                        <ul class="check-list">
                            <?php foreach ($benefits as $benefit): ?>
                                <li><?= e((string) $benefit) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </article>
                <?php endif; ?>

                <?php if ($items !== []): ?>
                    <article class="detail-block">
                        <h2>Hạng mục thực hiện</h2>
                        <ul class="item-list">
                            <?php foreach ($items as $item): ?>
                                <li><?= e((string) $item) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </article>
                <?php endif; ?>

                <?php if ($process !== []): ?>
                    <article class="detail-block">
                        <h2>Quy trình thực hiện</h2>
                        <ol class="process-list">
                            <?php foreach ($process as $step): ?>
                                <li><?= e((string) $step) ?></li>
                            <?php endforeach; ?>
                        </ol>
                    </article>
                <?php endif; ?>

                <?php if ($notes !== []): ?>
                    <article class="detail-block">
                        <h2>Lưu ý</h2>
                        <ul class="note-list">
                            <?php foreach ($notes as $note): ?>
                                <li><?= e((string) $note) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </article>
                <?php endif; ?>

                <?php if (!$hasEditorialContent): ?>
                    <div class="empty-state">
                        <strong>Thông tin chi tiết đang được cập nhật</strong>
                        <p>Vui lòng liên hệ gara để được tư vấn cụ thể về dịch vụ này.</p>
                    </div>
                <?php endif; ?>
            </div>

            <aside class="detail-aside">
                <div class="booking-card">
                    <span class="section-kicker" style="color:#bcd8f7">Đặt lịch</span>
                    <h2>Sẵn sàng chăm sóc xe?</h2>
                    <p>Gửi lịch hẹn để gara kiểm tra và tư vấn phương án phù hợp với tình trạng thực tế của xe.</p>
                    <a class="btn" href="<?= e($bookingUrl) ?>"><?= e($bookingLabel) ?></a>
                </div>
                <div class="contact-card">
                    <h3>Liên hệ gara</h3>
                    <div class="contact-list">
                        <div class="contact-item"><span class="contact-symbol">☎</span><div><strong>Điện thoại</strong><span><?= e(($garage['phone'] ?? '') !== '' ? $garage['phone'] : 'Chưa cập nhật thông tin') ?></span></div></div>
                        <div class="contact-item"><span class="contact-symbol">⌖</span><div><strong>Địa chỉ gara</strong><span><?= e(($garage['address'] ?? '') !== '' ? $garage['address'] : 'Chưa cập nhật thông tin') ?></span></div></div>
                        <div class="contact-item"><span class="contact-symbol">◷</span><div><strong>Giờ hoạt động</strong><span><?= e(($garage['hours'] ?? '') !== '' ? $garage['hours'] : 'Vui lòng liên hệ gara') ?></span></div></div>
                    </div>
                </div>
            </aside>
        </div>
    </section>

    <?php if ($relatedServices !== []): ?>
        <section class="section section-soft">
            <div class="container">
                <div style="margin-bottom:26px">
                    <span class="section-kicker">Cùng nhóm dịch vụ</span>
                    <h2>Dịch vụ liên quan</h2>
                </div>
                <div class="related-grid">
                    <?php foreach ($relatedServices as $related): ?>
                        <?php $relatedImage = service_image_url($related['image'] ?? null); ?>
                        <article class="related-card">
                            <div class="related-thumb">
                                <?php if ($relatedImage !== null): ?>
                                    <img src="<?= e($relatedImage) ?>" alt="<?= e($related['name']) ?>" width="1200" height="800" loading="lazy" decoding="async">
                                <?php else: ?>
                                    <span class="related-thumb-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none"><?= $iconForCategory((string) $related['category_name']) ?></svg>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="related-body">
                                <span><?= e($related['category_name']) ?></span>
                                <h3><a href="<?= e(url('services/' . (int) $related['id'])) ?>"><?= e($related['name']) ?></a></h3>
                                <div class="related-meta">
                                    <strong><?= e($formatPrice($related)) ?></strong>
                                    <span><?= e($formatDuration($related)) ?></span>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>
</main>

<footer class="site-footer">
    <div class="container">
        <div class="footer-main">
            <div>
                <a class="brand footer-brand" href="<?= e(url()) ?>"><span class="brand-mark" aria-hidden="true">A</span><span>AutoCare Hub</span></a>
                <p class="footer-copy">Nền tảng quản lý chăm sóc và sửa chữa ô tô, kết nối thông tin phương tiện, lịch dịch vụ và lịch sử bảo dưỡng.</p>
            </div>
            <nav class="footer-links" aria-label="Điều hướng chân trang">
                <a href="<?= e(url('services')) ?>">Dịch vụ</a><a href="<?= e(url() . '#process') ?>">Quy trình</a><a href="<?= e(url() . '#contact') ?>">Liên hệ</a>
                <a href="<?= e($accountAction) ?>"><?= e($accountLabel) ?></a>
            </nav>
        </div>
        <div class="copyright">© <?= e(date('Y')) ?> AutoCare Hub. Chăm xe an tâm, hành trình trọn vẹn.</div>
    </div>
</footer>
</body>
</html>
