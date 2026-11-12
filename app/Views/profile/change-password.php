<?php

declare(strict_types=1);

$role = $_SESSION['user_role'] ?? 'customer';
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đổi mật khẩu | AutoCare Hub</title>
    <style>
        :root{font-family:Inter,Arial,sans-serif;color:#172b3b;background:#f4f7fb}*{box-sizing:border-box}body{margin:0}
        header{background:#123b5d;color:#fff}.top,main{width:min(1160px,calc(100% - 36px));margin:auto}.top{min-height:68px;display:flex;align-items:center;justify-content:space-between;gap:15px}
        .brand{color:#fff;font-size:1.1rem;font-weight:800;text-decoration:none}.top nav{display:flex;align-items:center;gap:18px}.top a{color:#fff;text-decoration:none}.top a.active{text-decoration:underline;text-underline-offset:3px}
        main{padding:34px 0 60px}h1{margin:0 0 6px;color:#123b5d}.sub{color:#6b7b8b;margin:0 0 24px}
        .button{display:inline-flex;align-items:center;justify-content:center;min-height:38px;padding:0 16px;border:0;border-radius:8px;background:#2563eb;color:#fff;font:inherit;font-weight:700;cursor:pointer;text-decoration:none}
        .button.secondary{background:#e8eef5;color:#123b5d}
        .notice{padding:12px 15px;margin:0 0 16px;border-radius:8px;background:#ecfdf3;color:#067647}.notice.error{background:#fff0ef;color:#b42318}
        .card{background:#fff;border:1px solid #e3eaf1;border-radius:12px;padding:28px;max-width:540px}
        label{display:block;margin:16px 0 6px;font-weight:600;font-size:.93rem}
        input{width:100%;padding:11px 13px;border:1px solid #cbd5e1;border-radius:8px;font:inherit;font-size:.95rem}
        input:focus{outline:none;border-color:#2563eb;box-shadow:0 0 0 2px #2563eb22}
        .error-msg{color:#b42318;font-size:.85rem;margin:4px 0 0}
        .form-actions{margin-top:22px;display:flex;gap:10px}
    </style>
</head>
<body>
<header><div class="top">
    <?php if ($role === 'admin'): ?>
        <a class="brand" href="<?= e(url('admin')) ?>">AutoCare Hub · Admin</a>
        <nav>
            <a href="<?= e(url('admin/appointments')) ?>">Lịch hẹn</a>
            <a href="<?= e(url('admin/customers')) ?>">Khách hàng</a>
            <a href="<?= e(url('admin/vehicles')) ?>">Phương tiện</a>
            <a href="<?= e(url('admin/brands')) ?>">Hãng xe</a>
            <a href="<?= e(url('profile')) ?>">Thông tin cá nhân</a>
            <a href="<?= e(url('profile/change-password')) ?>" class="active">Đổi mật khẩu</a>
            <form method="post" action="<?= e(url('logout')) ?>"><?= csrf_field() ?><button class="button secondary" type="submit">Đăng xuất</button></form>
        </nav>
    <?php else: ?>
        <a class="brand" href="<?= e(url('customer')) ?>">AutoCare Hub</a>
        <nav>
            <a href="<?= e(url('vehicles')) ?>">Xe của tôi</a>
            <a href="<?= e(url('profile')) ?>">Thông tin cá nhân</a>
            <a href="<?= e(url('profile/change-password')) ?>" class="active">Đổi mật khẩu</a>
            <form method="post" action="<?= e(url('logout')) ?>"><?= csrf_field() ?><button class="button secondary" type="submit">Đăng xuất</button></form>
        </nav>
    <?php endif; ?>
</div></header>

<main>
    <h1>Đổi mật khẩu</h1>
    <p class="sub">Thay đổi mật khẩu để bảo vệ tài khoản của bạn.</p>

    <?php if (is_string($success) && $success !== ''): ?><p class="notice" role="status"><?= e($success) ?></p><?php endif; ?>
    <?php if (is_string($error) && $error !== ''): ?><p class="notice error" role="alert"><?= e($error) ?></p><?php endif; ?>

    <div class="card">
        <form method="post" action="<?= e(url('profile/change-password')) ?>">
            <?= csrf_field() ?>
            <label for="current_password">Mật khẩu hiện tại</label>
            <input id="current_password" name="current_password" type="password" required autocomplete="current-password">
            <?php if (isset($errors['current_password'])): ?><p class="error-msg"><?= e($errors['current_password']) ?></p><?php endif; ?>

            <label for="new_password">Mật khẩu mới</label>
            <input id="new_password" name="new_password" type="password" required autocomplete="new-password">
            <?php if (isset($errors['new_password'])): ?><p class="error-msg"><?= e($errors['new_password']) ?></p><?php endif; ?>

            <label for="new_password_confirmation">Xác nhận mật khẩu mới</label>
            <input id="new_password_confirmation" name="new_password_confirmation" type="password" required autocomplete="new-password">
            <?php if (isset($errors['new_password_confirmation'])): ?><p class="error-msg"><?= e($errors['new_password_confirmation']) ?></p><?php endif; ?>

            <div class="form-actions">
                <button class="button" type="submit">Đổi mật khẩu</button>
                <a class="button secondary" href="<?= e(url('profile')) ?>">Quay lại</a>
            </div>
        </form>
    </div>
</main>
</body>
</html>