<?php
$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$isRegister = $mode === 'register';
?>
<section class="workflow-page">
    <div class="container workflow-narrow">
        <p class="eyebrow"><span></span>Tài khoản DriveHub</p>
        <h1><?= $isRegister ? 'Đăng ký khách hàng' : 'Đăng nhập' ?></h1>
        <p class="workflow-lead">
            <?= $isRegister ? 'Tạo tài khoản để đặt lịch và theo dõi lịch sử xe của bạn.' : 'Đăng nhập để tiếp tục sử dụng DriveHub.' ?>
        </p>

        <?php if ($errors !== []): ?>
            <div class="workflow-alert workflow-alert--error" role="alert">
                <?php foreach ($errors as $error): ?><p><?= $e($error) ?></p><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form class="workflow-card workflow-form" method="post" action="<?= BASE_URL ?>?action=auth">
            <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
            <input type="hidden" name="mode" value="<?= $isRegister ? 'register' : 'login' ?>">

            <?php if ($isRegister): ?>
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
            <?php else: ?>
                <label>Tên đăng nhập hoặc email
                    <input type="text" name="login" value="<?= $e($old['login']) ?>" required autocomplete="username">
                </label>
                <label>Mật khẩu
                    <input type="password" name="password" required autocomplete="current-password">
                </label>
            <?php endif; ?>

            <button class="btn btn--primary" type="submit"><?= $isRegister ? 'Tạo tài khoản' : 'Đăng nhập' ?></button>
            <p class="workflow-switch">
                <?php if ($isRegister): ?>
                    Đã có tài khoản? <a href="<?= BASE_URL ?>?action=auth&amp;mode=login">Đăng nhập</a>
                <?php else: ?>
                    Chưa có tài khoản khách hàng? <a href="<?= BASE_URL ?>?action=auth&amp;mode=register">Đăng ký</a>
                <?php endif; ?>
            </p>
        </form>
    </div>
</section>
