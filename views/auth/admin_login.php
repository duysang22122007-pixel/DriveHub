<?php
$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<section class="workflow-page admin-login-page">
    <div class="container workflow-narrow">
        <p class="eyebrow"><span></span>Quản trị DriveHub</p>
        <h1>Đăng nhập Admin</h1>
        <p class="workflow-lead">Trang đăng nhập dành riêng cho tài khoản quản trị gara.</p>

        <?php if ($errors !== []): ?>
            <div class="workflow-alert workflow-alert--error" role="alert">
                <?php foreach ($errors as $error): ?><p><?= $e($error) ?></p><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form class="workflow-card workflow-form" method="post" action="<?= BASE_URL ?>admin/login">
            <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
            <label>Tên đăng nhập hoặc email Admin
                <input type="text" name="login" value="<?= $e($old['login']) ?>" required autocomplete="username">
            </label>
            <label>Mật khẩu Admin
                <input type="password" name="password" required autocomplete="current-password">
            </label>
            <button class="btn btn--primary" type="submit">Đăng nhập Admin</button>
            <p class="workflow-switch">
                Khách hàng? <a href="<?= BASE_URL ?>login">Đăng nhập khách hàng</a>
            </p>
        </form>
    </div>
</section>
