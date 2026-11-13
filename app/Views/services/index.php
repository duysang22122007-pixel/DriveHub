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

$formatPrice = static function (array $service): string {
    $price = (float) ($service['price'] ?? 0);
    return $price > 0 ? number_format($price, 0, ',', '.') . ' ₫' : 'Liên hệ để được báo giá';
};
$formatDuration = static function (array $service): string {
    $duration = (int) ($service['duration'] ?? 0);
    return $duration > 0 ? 'Khoảng ' . $duration . ' phút' : 'Liên hệ để biết thời gian';
};
$bookingUrl = static function (int $serviceId) use ($userRole): string {
    if ($userRole === 'customer') {
        return url('appointments/create?service_id=' . $serviceId);
    }

    return $userRole === 'admin' ? url('admin') : url('login');
};
$bookingLabel = $userRole === 'customer' ? 'Đặt lịch dịch vụ' : ($userRole === 'admin' ? 'Mở quản trị' : 'Đăng nhập để đặt lịch');
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
$allServicesUrl = url('services' . ($keyword !== '' ? '?q=' . urlencode($keyword) : ''));
$clearUrl = url('services');
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Danh mục dịch vụ chăm sóc, bảo dưỡng và sửa chữa ô tô tại AutoCare Hub.">
    <title>Dịch vụ | AutoCare Hub</title>
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
        a:focus-visible, button:focus-visible, input:focus-visible, select:focus-visible { outline: 3px solid #60a5fa; outline-offset: 3px; }
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
        .page-hero { padding: 64px 0 58px; background: radial-gradient(ellipse at 88% 10%, #2c5c7e 0, transparent 42%), linear-gradient(118deg, #0b2740 0%, #123b5d 58%, #17486b 100%); color: #fff; }
        .page-hero .eyebrow { display: inline-flex; align-items: center; gap: 9px; color: #b8d5fa; text-transform: uppercase; letter-spacing: .12em; font-size: .75rem; font-weight: 800; }
        .page-hero .eyebrow::before { content: ""; width: 24px; height: 2px; background: #60a5fa; }
        .page-hero h1 { max-width: 760px; margin: 17px 0 14px; font-size: clamp(2.2rem, 4.6vw, 3.6rem); line-height: 1.08; letter-spacing: -.05em; }
        .page-hero p { max-width: 680px; margin: 0; color: #d2e0eb; font-size: 1.02rem; line-height: 1.75; }
        .section { padding: 68px 0; }
        .section-soft { background: var(--surface); }
        .section-kicker { color: var(--blue); text-transform: uppercase; letter-spacing: .13em; font-size: .75rem; font-weight: 800; }
        h2 { margin: 12px 0; color: var(--navy); font-size: clamp(1.8rem, 3vw, 2.5rem); line-height: 1.15; letter-spacing: -.04em; }
        .section-heading { max-width: 680px; margin-bottom: 30px; }
        .section-heading p { margin: 0; color: var(--muted); line-height: 1.75; }
        .filter-panel { padding: 24px; border: 1px solid var(--line); border-radius: 16px; background: #fff; box-shadow: 0 8px 24px #123b5d08; }
        .filter-form { display: grid; grid-template-columns: minmax(0, 1fr) 260px auto; gap: 12px; align-items: end; }
        .field label { display: block; margin-bottom: 7px; color: var(--navy); font-size: .82rem; font-weight: 700; }
        .field input, .field select { width: 100%; min-height: 46px; padding: 0 13px; border: 1px solid #cbd6e1; border-radius: 9px; background: #fff; color: var(--ink); font: inherit; }
        .category-pills { display: flex; flex-wrap: wrap; gap: 9px; margin-top: 18px; }
        .category-pill { display: inline-flex; align-items: center; gap: 7px; min-height: 36px; padding: 0 13px; border: 1px solid #cbd6e1; border-radius: 999px; background: #fff; color: #4a5b6d; font-size: .84rem; font-weight: 700; }
        .category-pill:hover { border-color: #93b4d8; color: var(--blue); }
        .category-pill.is-active { border-color: var(--blue); background: var(--blue-light); color: var(--blue); }
        .category-pill span { color: var(--muted); font-weight: 600; }
        .results-bar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 14px; margin: 30px 0 18px; }
        .results-bar h2 { margin: 0; font-size: 1.35rem; }
        .results-bar p { margin: 0; color: var(--muted); font-size: .9rem; }
        .service-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; }
        .service-card { display: flex; flex-direction: column; overflow: hidden; border: 1px solid var(--line); border-radius: 15px; background: #fff; box-shadow: 0 8px 24px #123b5d08; transition: transform .18s ease, box-shadow .18s ease; }
        .service-card:hover { transform: translateY(-3px); box-shadow: 0 14px 32px #123b5d14; }
        .service-thumb { position: relative; height: 150px; display: grid; place-items: center; overflow: hidden; background: linear-gradient(135deg, #eaf1ff, #dbe8fb); }
        .service-thumb img { width: 100%; height: 100%; object-fit: cover; transition: transform .35s ease; }
        .service-card:hover .service-thumb img { transform: scale(1.04); }
        .service-thumb-icon { width: 64px; height: 64px; display: grid; place-items: center; border-radius: 18px; background: #ffffffb8; color: var(--blue); box-shadow: 0 8px 18px #123b5d12; }
        .service-thumb-icon svg { width: 34px; height: 34px; }
        .service-card-body { display: flex; flex: 1; flex-direction: column; padding: 21px; }
        .service-category { color: var(--blue); font-size: .74rem; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
        .service-card h3 { margin: 7px 0 8px; color: var(--navy); font-size: 1.06rem; line-height: 1.35; }
        .service-card h3 a:hover { color: var(--blue); }
        .service-card p { margin: 0 0 16px; color: var(--muted); font-size: .87rem; line-height: 1.6; }
        .service-meta { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 10px; margin-top: auto; padding-top: 14px; border-top: 1px solid var(--line); color: #506174; font-size: .8rem; }
        .service-meta strong { color: var(--navy); font-size: .92rem; }
        .service-actions { display: flex; flex-wrap: wrap; gap: 9px; margin-top: 15px; }
        .empty-state { padding: 34px; border: 1px dashed #b9c9d8; border-radius: 15px; background: #fff; text-align: center; }
        .empty-state strong { color: var(--navy); }
        .empty-state p { margin: 8px 0 0; color: var(--muted); }
        .category-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
        .category-card { display: flex; gap: 14px; padding: 20px; border: 1px solid var(--line); border-radius: 15px; background: #fff; transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease; }
        .category-card:hover { transform: translateY(-2px); border-color: #b7cde4; box-shadow: 0 12px 28px #123b5d12; }
        .category-icon { width: 48px; height: 48px; flex: 0 0 auto; display: grid; place-items: center; border-radius: 13px; background: var(--blue-light); color: var(--blue); }
        .category-icon svg { width: 25px; height: 25px; }
        .category-card h3 { margin: 0 0 5px; color: var(--navy); font-size: .98rem; }
        .category-card p { margin: 0; color: var(--muted); font-size: .84rem; line-height: 1.55; }
        .category-count { display: inline-block; margin-top: 8px; color: var(--blue); font-size: .78rem; font-weight: 800; }
        .site-footer { padding: 38px 0 22px; background: #0b2740; color: #d4e2ed; }
        .footer-main { display: flex; justify-content: space-between; gap: 32px; padding-bottom: 28px; }
        .footer-brand { color: #fff; }
        .footer-copy { max-width: 380px; margin: 13px 0 0; color: #aebfce; font-size: .88rem; line-height: 1.7; }
        .footer-links { display: flex; flex-wrap: wrap; align-content: flex-start; gap: 14px 24px; color: #d4e2ed; font-size: .88rem; }
        .copyright { padding-top: 18px; border-top: 1px solid #ffffff1a; color: #aebfce; font-size: .8rem; }
        @media (max-width: 920px) {
            .nav-links { gap: 17px; }
            .nav-account { display: none; }
            .service-grid, .category-grid { grid-template-columns: repeat(2, 1fr); }
            .filter-form { grid-template-columns: 1fr 1fr; }
            .filter-form .btn { grid-column: 1 / -1; }
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
            .page-hero { padding: 48px 0 42px; }
            .section { padding: 52px 0; }
            .filter-form { grid-template-columns: 1fr; }
            .service-grid, .category-grid { grid-template-columns: 1fr; }
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
            <a href="<?= e(url('services')) ?>" aria-current="page">Dịch vụ</a>
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
    <section class="page-hero">
        <div class="container">
            <span class="eyebrow">Dịch vụ của AutoCare Hub</span>
            <h1>Chọn đúng dịch vụ cho chiếc xe của bạn</h1>
            <p>Danh mục được cập nhật trực tiếp từ hệ thống gara. Bạn có thể tìm theo tên dịch vụ hoặc lọc theo nhóm để xem thông tin chi tiết và đặt lịch.</p>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="filter-panel">
                <form class="filter-form" method="get" action="<?= e(url('services')) ?>">
                    <div class="field">
                        <label for="q">Tìm dịch vụ</label>
                        <input id="q" name="q" type="search" value="<?= e($keyword) ?>" placeholder="Ví dụ: bảo dưỡng, sơn, điện lạnh...">
                    </div>
                    <div class="field">
                        <label for="category">Nhóm dịch vụ</label>
                        <select id="category" name="category">
                            <option value="">Tất cả nhóm dịch vụ</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= e((int) $category['id']) ?>"<?= $categoryId === (int) $category['id'] ? ' selected' : '' ?>><?= e($category['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button class="btn btn-primary" type="submit">Tìm kiếm</button>
                </form>
                <?php if ($categories !== []): ?>
                    <div class="category-pills" aria-label="Lọc nhanh theo nhóm dịch vụ">
                        <a class="category-pill<?= $categoryId === null ? ' is-active' : '' ?>" href="<?= e($allServicesUrl) ?>">Tất cả <span><?= e(array_sum(array_map(static fn (array $category): int => (int) $category['service_count'], $categories))) ?></span></a>
                        <?php foreach ($categories as $category): ?>
                            <?php $categoryUrl = url('services?category=' . (int) $category['id'] . ($keyword !== '' ? '&q=' . urlencode($keyword) : '')); ?>
                            <a class="category-pill<?= $categoryId === (int) $category['id'] ? ' is-active' : '' ?>" href="<?= e($categoryUrl) ?>"><?= e($category['name']) ?> <span><?= e((int) $category['service_count']) ?></span></a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="results-bar">
                <h2><?= e(count($services)) ?> dịch vụ phù hợp</h2>
                <p>
                    <?php if ($activeCategory !== null): ?>
                        Đang xem: <?= e($activeCategory['name']) ?>
                    <?php elseif ($keyword !== ''): ?>
                        Từ khóa: “<?= e($keyword) ?>”
                    <?php else: ?>
                        Toàn bộ dịch vụ đang được gara kích hoạt
                    <?php endif; ?>
                    <?php if ($keyword !== '' || $categoryId !== null): ?>
                        · <a href="<?= e($clearUrl) ?>" style="color:var(--blue);font-weight:700">Xóa bộ lọc</a>
                    <?php endif; ?>
                </p>
            </div>

            <?php if ($services === []): ?>
                <div class="empty-state">
                    <strong>Không tìm thấy dịch vụ phù hợp</strong>
                    <p>Hãy thử từ khóa khác hoặc bỏ bộ lọc để xem toàn bộ danh mục dịch vụ.</p>
                </div>
            <?php else: ?>
                <div class="service-grid">
                    <?php foreach ($services as $service): ?>
                        <?php $image = service_image_url($service['image'] ?? null); ?>
                        <article class="service-card">
                            <div class="service-thumb">
                                <?php if ($image !== null): ?>
                                    <img src="<?= e($image) ?>" alt="<?= e($service['name']) ?>" width="1200" height="800" loading="lazy" decoding="async">
                                <?php else: ?>
                                    <span class="service-thumb-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none"><?= $iconForCategory((string) $service['category_name']) ?></svg>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="service-card-body">
                                <span class="service-category"><?= e($service['category_name']) ?></span>
                                <h3><a href="<?= e(url('services/' . (int) $service['id'])) ?>"><?= e($service['name']) ?></a></h3>
                                <p><?= e($service['description'] ?: 'Dịch vụ chăm sóc và sửa chữa xe tại gara.') ?></p>
                                <div class="service-meta">
                                    <strong><?= e($formatPrice($service)) ?></strong>
                                    <span><?= e($formatDuration($service)) ?></span>
                                </div>
                                <div class="service-actions">
                                    <a class="btn btn-outline btn-small" href="<?= e(url('services/' . (int) $service['id'])) ?>">Xem chi tiết</a>
                                    <a class="btn btn-primary btn-small" href="<?= e($bookingUrl((int) $service['id'])) ?>"><?= e($bookingLabel) ?></a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php if ($categories !== []): ?>
        <section class="section section-soft">
            <div class="container">
                <div class="section-heading">
                    <span class="section-kicker">Nhóm dịch vụ</span>
                    <h2>Chăm sóc xe theo từng nhu cầu</h2>
                    <p>Các nhóm dịch vụ được gara kích hoạt và hiển thị công khai để bạn dễ dàng lựa chọn.</p>
                </div>
                <div class="category-grid">
                    <?php foreach ($categories as $category): ?>
                        <a class="category-card" href="<?= e(url('services?category=' . (int) $category['id'])) ?>">
                            <span class="category-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none"><?= $iconForCategory((string) $category['name']) ?></svg>
                            </span>
                            <div>
                                <h3><?= e($category['name']) ?></h3>
                                <p><?= e($category['description'] ?: 'Nhóm dịch vụ chăm sóc và sửa chữa xe tại gara.') ?></p>
                                <span class="category-count"><?= e((int) $category['service_count']) ?> dịch vụ</span>
                            </div>
                        </a>
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
