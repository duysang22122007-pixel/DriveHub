<?php

declare(strict_types=1);

?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quên mật khẩu | AutoCare Hub</title>
    <style>
        :root { font-family: Inter, Arial, sans-serif; color: #172b3b; background: #f4f7fb; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 20px; }
        main { width: min(100%, 480px); background: #fff; padding: 32px; border-radius: 12px; box-shadow: 0 12px 40px #123b5d14; }
        h1 { margin: 0 0 8px; color: #123b5d; } p { color: #5b6b7b; }
        label { display: block; margin: 18px 0 7px; font-weight: 600; color: #172b3b; }
        input { width: 100%; padding: 11px 13px; border: 1px solid #cbd5e1; border-radius: 8px; font: inherit; }
        input:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 2px #2563eb22; }
        .button { width: 100%; margin-top: 20px; padding: 12px; border: 0; border-radius: 8px; background: #2563eb; color: #fff; font: inherit; font-weight: 700; cursor: pointer; }
        a { color: #2563eb; }
        .error-msg { color: #b42318; font-size: .85rem; margin: 4px 0 0; }
        .notice { padding: 12px 15px; margin: 16px 0; border-radius: 8px; background: #ecfdf3; color: #067647; }
    </style>
</head>
<body>
<main>
    <h1>Quên mật khẩu</h1>
    <p>Nhập email tài khoản để nhận liên kết đặt lại mật khẩu.</p>
    <?php if (is_string($success) && $success !== ''): ?>
        <p class="notice" role="status"><?= e($success) ?></p>
    <?php endif; ?>

    <form method="post" action="<?= e(url('forgot-password')) ?>">
        <?= csrf_field() ?>
        <label for="email">Email</label>
        <input id="email" name="email" type="email" maxlength="150" required autocomplete="email"
               value="<?= e((string) ($old['email'] ?? '')) ?>">
        <?php if (isset($errors['email'])): ?><p class="error-msg"><?= e($errors['email']) ?></p><?php endif; ?>
        <button class="button" type="submit">Gửi yêu cầu</button>
    </form>
    <p style="margin-top:18px"><a href="<?= e(url('login')) ?>">← Quay lại đăng nhập</a></p>
</main>
</body>
</html>