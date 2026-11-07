<?php
$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<section class="workflow-page customer-login-page">
    <div class="container workflow-narrow">
        <p class="eyebrow"><span></span>Tài khoản DriveHub</p>
        <h1><?= $loginRole === 'admin' ? 'Đăng nhập quản trị viên' : 'Đăng nhập khách hàng' ?></h1>
        <p class="workflow-lead">
            <?= $loginRole === 'admin'
                ? 'Đăng nhập bằng tài khoản quản trị được cấp quyền.'
                : 'Đăng nhập để đặt lịch và theo dõi thông tin xe của bạn.' ?>
        </p>

        <?php if ($errors !== []): ?>
            <div class="workflow-alert workflow-alert--error" role="alert">
                <?php foreach ($errors as $error): ?><p><?= $e($error) ?></p><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form class="workflow-card workflow-form" method="post" action="<?= BASE_URL . ($loginRole === 'admin' ? 'admin/login' : 'login') ?>">
            <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
            <label>Tên đăng nhập hoặc email
                <input type="text" name="login" value="<?= $e($old['login']) ?>" required autocomplete="username">
            </label>
            <label>Mật khẩu
                <input type="password" name="password" required autocomplete="current-password">
            </label>
            <button class="btn btn--primary" type="submit">Đăng nhập</button>
            <?php if ($loginRole === 'admin'): ?>
                <p class="workflow-switch">Bạn là khách hàng? <a href="<?= BASE_URL ?>login">Đăng nhập khách hàng</a></p>
            <?php else: ?>
                <p class="workflow-switch">
                    Chưa có tài khoản? <a href="<?= BASE_URL ?>?action=auth&amp;mode=register">Đăng ký</a>
                    · <a href="<?= BASE_URL ?>admin/login">Đăng nhập quản trị</a>
                </p>
            <?php endif; ?>
        </form>
    </div>
</section>
