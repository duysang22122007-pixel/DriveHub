<?php

declare(strict_types=1);

?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng nhập | DriveHub</title>
    <style>
        :root { font-family: Arial, sans-serif; color: #123b5d; background: #f3f6fa; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 20px; }
        main { width: min(100%, 440px); background: #fff; padding: 32px; border-radius: 16px; box-shadow: 0 12px 40px #123b5d14; }
        h1 { margin: 0 0 8px; } p { color: #5b6b7b; }
        label { display: block; margin: 18px 0 7px; font-weight: 600; }
        input { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font: inherit; }
        button { width: 100%; margin-top: 22px; padding: 12px; border: 0; border-radius: 8px; background: #2563eb; color: #fff; font: inherit; font-weight: 700; cursor: pointer; }
        a { color: #2563eb; } .error { color: #b42318; font-size: .9rem; } .notice { padding: 10px; background: #ecfdf3; color: #067647; border-radius: 8px; }
    </style>
</head>
<body>
<main>
    <h1>Đăng nhập</h1>
    <p>Truy cập tài khoản DriveHub của bạn.</p>
    <?php if (is_string($success) && $success !== ''): ?><p class="notice"><?= e($success) ?></p><?php endif; ?>
    <?php if (isset($errors['credentials'])): ?><p class="error" role="alert"><?= e($errors['credentials']) ?></p><?php endif; ?>
    <form method="post" action="<?= e(url('login')) ?>">
        <?= csrf_field() ?>
        <label for="email">Email</label>
        <input id="email" name="email" type="email" maxlength="150" autocomplete="email" required value="<?= e($old['email'] ?? '') ?>">
        <?php if (isset($errors['email'])): ?><p class="error"><?= e($errors['email']) ?></p><?php endif; ?>
        <label for="password">Mật khẩu</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required>
        <?php if (isset($errors['password'])): ?><p class="error"><?= e($errors['password']) ?></p><?php endif; ?>
        <button type="submit">Đăng nhập</button>
    </form>
    <p>Chưa có tài khoản? <a href="<?= e(url('register')) ?>">Đăng ký khách hàng</a></p>
</main>
</body>
</html>
