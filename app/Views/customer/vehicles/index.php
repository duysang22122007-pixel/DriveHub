<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Xe của tôi | AutoCare Hub</title>
    <style>
        :root { font-family: Inter, Arial, sans-serif; color: #172b3b; background: #f4f7fb; }
        * { box-sizing: border-box; } body { margin: 0; }
        header { background: #123b5d; color: #fff; }
        .top, main, footer { width: min(1120px, calc(100% - 36px)); margin: 0 auto; }
        .top { min-height: 68px; display: flex; justify-content: space-between; align-items: center; gap: 16px; }
        .brand { color: white; font-weight: 800; font-size: 1.1rem; } .top nav { display:flex; gap:18px; align-items:center; }
        a { color: #2563eb; text-decoration: none; font-weight: 600; } header a { color: #fff; }
        main { padding: 38px 0 64px; } .heading { display:flex; justify-content:space-between; align-items:center; gap:20px; margin-bottom:22px; }
        h1 { margin:0; color:#123b5d; font-size:clamp(1.8rem,4vw,2.4rem); } .sub { margin:8px 0 0; color:#667788; }
        .button { display:inline-flex; align-items:center; justify-content:center; min-height:42px; padding:0 16px; border:0; border-radius:8px; background:#2563eb; color:#fff; font:inherit; font-weight:700; cursor:pointer; }
        .button.secondary { background:#e9eff6; color:#123b5d; } .button.danger { background:#fff0ef; color:#b42318; min-height:36px; }
        .notice { margin:14px 0; padding:12px 15px; border-radius:9px; background:#ecfdf3; color:#067647; } .notice.error { background:#fff1f0; color:#b42318; }
        .grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:16px; }
        .card, .empty { background:#fff; border:1px solid #e3eaf1; border-radius:14px; padding:22px; box-shadow:0 7px 22px #123b5d07; }
        .card-head { display:flex; justify-content:space-between; align-items:flex-start; gap:12px; }
        h2 { margin:0; color:#123b5d; font-size:1.16rem; } .plate { display:inline-block; margin-top:10px; padding:5px 9px; border:1px solid #cad6e2; border-radius:6px; color:#34495e; font-weight:800; letter-spacing:.06em; }
        .facts { display:flex; flex-wrap:wrap; gap:9px 18px; margin:18px 0; color:#667788; font-size:.9rem; }
        .actions { display:flex; align-items:center; gap:10px; padding-top:14px; border-top:1px solid #edf1f5; }
        .actions form { margin-left:auto; } .empty { grid-column:1/-1; text-align:center; padding:44px 20px; } .empty p { color:#667788; }
        footer { padding:18px 0 26px; border-top:1px solid #e3eaf1; color:#748395; font-size:.85rem; }
        @media(max-width:680px) { .top { align-items:flex-start; flex-direction:column; padding:14px 0; } .top nav { flex-wrap:wrap; gap:12px; } .heading { align-items:flex-start; flex-direction:column; } .grid { grid-template-columns:1fr; } }
    </style>
</head>
<body>
<header><div class="top">
    <a class="brand" href="<?= e(url()) ?>">AutoCare Hub</a>
    <nav><a href="<?= e(url('customer')) ?>">Tài khoản</a><a href="<?= e(url()) ?>">Trang chủ</a>
        <form method="post" action="<?= e(url('logout')) ?>"><?= csrf_field() ?><button class="button secondary" type="submit">Đăng xuất</button></form>
    </nav>
</div></header>
<main>
    <div class="heading">
        <div><h1>Xe của tôi</h1><p class="sub">Quản lý các phương tiện thuộc tài khoản của bạn.</p></div>
        <a class="button" href="<?= e(url('vehicles/create')) ?>">+ Thêm xe</a>
    </div>
    <?php if (is_string($success) && $success !== ''): ?><div class="notice" role="status"><?= e($success) ?></div><?php endif; ?>
    <?php if (is_string($error) && $error !== ''): ?><div class="notice error" role="alert"><?= e($error) ?></div><?php endif; ?>
    <section class="grid" aria-label="Danh sách xe">
        <?php if ($vehicles === []): ?>
            <div class="empty"><h2>Bạn chưa đăng ký xe</h2><p>Thêm phương tiện để quản lý hồ sơ và chuẩn bị sử dụng dịch vụ của gara.</p><a class="button" href="<?= e(url('vehicles/create')) ?>">Đăng ký xe đầu tiên</a></div>
        <?php else: ?>
            <?php foreach ($vehicles as $vehicle): ?>
                <article class="card">
                    <div class="card-head"><div><h2><?= e($vehicle['brand_name']) ?> <?= e($vehicle['model']) ?></h2><span class="plate"><?= e($vehicle['license_plate']) ?></span></div></div>
                    <div class="facts">
                        <span>Năm: <?= e($vehicle['manufacture_year'] ?: 'Chưa cập nhật') ?></span>
                        <span>Màu: <?= e($vehicle['color'] ?: 'Chưa cập nhật') ?></span>
                        <span>Odo: <?= e(number_format((int) $vehicle['current_mileage'], 0, ',', '.')) ?> km</span>
                    </div>
                    <div class="actions">
                        <a class="button secondary" href="<?= e(url('vehicles/' . (int) $vehicle['id'] . '/edit')) ?>">Cập nhật</a>
                        <form method="post" action="<?= e(url('vehicles/' . (int) $vehicle['id'] . '/delete')) ?>" onsubmit="return confirm('Xóa xe này khỏi tài khoản? Xe đã có lịch hẹn sẽ không thể xóa.');">
                            <?= csrf_field() ?><button class="button danger" type="submit">Xóa xe</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</main>
<footer>AutoCare Hub · Thông tin phương tiện chỉ hiển thị trong tài khoản sở hữu.</footer>
</body>
</html>
