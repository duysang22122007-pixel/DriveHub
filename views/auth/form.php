<?php
$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<section class="workflow-page">
    <div class="container workflow-narrow">
        <p class="eyebrow"><span></span>Tài khoản DriveHub</p>
        <h1>Đăng ký khách hàng</h1>
        <p class="workflow-lead">
            Tạo tài khoản để đặt lịch và theo dõi lịch sử xe của bạn.
        </p>

        <?php if ($errors !== []): ?>
            <div class="workflow-alert workflow-alert--error" role="alert">
                <?php foreach ($errors as $error): ?><p><?= $e($error) ?></p><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form class="workflow-card workflow-form" method="post" action="<?= BASE_URL ?>?action=auth">
            <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
            <input type="hidden" name="mode" value="register">

            <label>Họ tên
                <input type="text" name="full_name" value="<?= $e($old['full_name']) ?>" maxlength="100" required autocomplete="name">
            </label>
            <label>Số điện thoại
                <input type="tel" name="phone" value="<?= $e($old['phone']) ?>" maxlength="15" required autocomplete="tel">
            </label>
            <label>Tên đăng nhập
                <input type="text" name="username" value="<?= $e($old['username']) ?>" maxlength="80" required autocomplete="username">
            </label>
            <label>Email
                <input type="email" name="email" value="<?= $e($old['email']) ?>" maxlength="150" required autocomplete="email">
            </label>
            <label>Mật khẩu (ít nhất 10 ký tự)
                <input type="password" name="password" minlength="10" required autocomplete="new-password">
            </label>
            <label>Nhập lại mật khẩu
                <input type="password" name="password_confirmation" minlength="10" required autocomplete="new-password">
            </label>

            <button class="btn btn--primary" type="submit">Tạo tài khoản</button>
            <p class="workflow-switch">
                Đã có tài khoản? <a href="<?= BASE_URL ?>login">Đăng nhập</a>
            </p>
        </form>
    </div>
</section>
