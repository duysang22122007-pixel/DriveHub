<?php
$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<section class="workflow-page customer-login-page">
    <div class="container workflow-narrow">
        <p class="eyebrow"><span></span>Tài khoản DriveHub</p>
        <h1>Đăng nhập khách hàng</h1>
        <p class="workflow-lead">Đăng nhập để đặt lịch và xem lịch sử xe của bạn.</p>

        <?php if ($errors !== []): ?>
            <div class="workflow-alert workflow-alert--error" role="alert">
                <?php foreach ($errors as $error): ?><p><?= $e($error) ?></p><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form class="workflow-card workflow-form" method="post" action="<?= BASE_URL ?>login">
            <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
            <label>Tên đăng nhập hoặc email
                <input type="text" name="login" value="<?= $e($old['login']) ?>" required autocomplete="username">
            </label>
            <label>Mật khẩu
                <input type="password" name="password" required autocomplete="current-password">
            </label>
            <button class="btn btn--primary" type="submit">Đăng nhập</button>
            <p class="workflow-switch">
                Chưa có tài khoản? <a href="<?= BASE_URL ?>?action=auth&amp;mode=register">Đăng ký khách hàng</a>
            </p>
        </form>
    </div>
</section>
