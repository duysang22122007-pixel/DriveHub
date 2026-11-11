<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($dashboardTitle) ?> | AutoCare Hub</title>
    <style>
        :root { font-family: Arial, sans-serif; color: #123b5d; background: #f3f6fa; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 20px; }
        main { width: min(100%, 640px); padding: 36px; border-radius: 16px; background: #fff; box-shadow: 0 12px 40px #123b5d14; }
        button, .link { display:inline-flex; align-items:center; padding:10px 16px; border:0; border-radius:8px; background:#123b5d; color:#fff; text-decoration:none; cursor:pointer; font:inherit; font-weight:700; }
        .link.secondary { background:#e9eff6; color:#123b5d; }
        .badge { display: inline-block; padding: 6px 10px; border-radius: 999px; background: #e8f0fe; color: #2563eb; }
        .links { display:flex; flex-wrap:wrap; gap:10px; margin:22px 0; }
    </style>
</head>
<body>
<main>
    <span class="badge"><?= e($roleLabel) ?></span>
    <h1><?= e($dashboardTitle) ?></h1>
    <p>Xin chào, <?= e($userName) ?>.</p>
    <div class="links">
        <?php if ($role === 'admin'): ?>
            <a class="link" href="<?= e(url('admin/customers')) ?>">Quản lý khách hàng</a>
            <a class="link secondary" href="<?= e(url('admin/vehicles')) ?>">Danh sách phương tiện</a>
            <a class="link secondary" href="<?= e(url('admin/brands')) ?>">Quản lý hãng xe</a>
            <a class="link secondary" href="<?= e(url('profile')) ?>">Thông tin cá nhân</a>
            <a class="link secondary" href="<?= e(url('profile/change-password')) ?>">Đổi mật khẩu</a>
        <?php else: ?>
            <a class="link" href="<?= e(url('vehicles')) ?>">Quản lý xe của tôi</a>
            <a class="link secondary" href="<?= e(url('#services')) ?>">Xem dịch vụ</a>
            <a class="link secondary" href="<?= e(url('profile')) ?>">Thông tin cá nhân</a>
            <a class="link secondary" href="<?= e(url('profile/change-password')) ?>">Đổi mật khẩu</a>
        <?php endif; ?>
    </div>
    <form method="post" action="<?= e(url('logout')) ?>">
        <?= csrf_field() ?>
        <button type="submit">Đăng xuất</button>
    </form>
</main>
</body>
</html>
