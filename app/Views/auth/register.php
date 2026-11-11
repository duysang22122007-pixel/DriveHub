<?php

declare(strict_types=1);

?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng ký | DriveHub</title>
    <style>
        :root { font-family: Arial, sans-serif; color: #123b5d; background: #f3f6fa; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 20px; }
        main { width: min(100%, 480px); background: #fff; padding: 32px; border-radius: 16px; box-shadow: 0 12px 40px #123b5d14; }
        h1 { margin: 0 0 8px; } p { color: #5b6b7b; }
        label { display: block; margin: 16px 0 7px; font-weight: 600; }
        input { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font: inherit; }
        button { width: 100%; margin-top: 22px; padding: 12px; border: 0; border-radius: 8px; background: #2563eb; color: #fff; font: inherit; font-weight: 700; cursor: pointer; }
        a { color: #2563eb; } .error { color: #b42318; font-size: .9rem; }
    </style>
</head>
<body>
<main>
    <h1>Tạo tài khoản khách hàng</h1>
    <p>Đăng ký để quản lý xe và lịch bảo dưỡng.</p>
    <form method="post" action="<?= e(url('register')) ?>">
        <?= csrf_field() ?>
        <label for="full_name">Họ và tên</label>
        <input id="full_name" name="full_name" maxlength="100" autocomplete="name" required value="<?= e($old['full_name'] ?? '') ?>">
        <?php if (isset($errors['full_name'])): ?><p class="error"><?= e($errors['full_name']) ?></p><?php endif; ?>
        <label for="email">Email</label>
        <input id="email" name="email" type="email" maxlength="150" autocomplete="email" required value="<?= e($old['email'] ?? '') ?>">
        <?php if (isset($errors['email'])): ?><p class="error"><?= e($errors['email']) ?></p><?php endif; ?>
        <label for="phone">Số điện thoại</label>
        <input id="phone" name="phone" type="tel" maxlength="20" autocomplete="tel" required value="<?= e($old['phone'] ?? '') ?>">
        <?php if (isset($errors['phone'])): ?><p class="error"><?= e($errors['phone']) ?></p><?php endif; ?>
        <label for="password">Mật khẩu (ít nhất 8 ký tự)</label>
        <input id="password" name="password" type="password" minlength="8" maxlength="72" autocomplete="new-password" required>
        <?php if (isset($errors['password'])): ?><p class="error"><?= e($errors['password']) ?></p><?php endif; ?>
        <label for="password_confirmation">Nhập lại mật khẩu</label>
        <input id="password_confirmation" name="password_confirmation" type="password" minlength="8" maxlength="72" autocomplete="new-password" required>
        <?php if (isset($errors['password_confirmation'])): ?><p class="error"><?= e($errors['password_confirmation']) ?></p><?php endif; ?>
        <button type="submit">Đăng ký</button>
    </form>
    <p>Đã có tài khoản? <a href="<?= e(url('login')) ?>">Đăng nhập</a></p>
</main>
</body>
</html>
