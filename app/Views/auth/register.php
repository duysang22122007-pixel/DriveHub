<?php

declare(strict_types=1);

?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#071426">
    <title>Đăng ký | DriveHub</title>
    <link rel="stylesheet" href="<?= e(url('assets/auth.css')) ?>">
    <script src="<?= e(url('assets/auth.js')) ?>" defer></script>
</head>
<body class="auth-page">
<canvas class="auth-waves" aria-hidden="true"></canvas>
<main class="auth-layout">
    <section class="auth-intro" aria-label="Giới thiệu DriveHub">
        <a class="auth-brand" href="<?= e(url('services')) ?>" aria-label="DriveHub - Dịch vụ">
            <span class="auth-brand-icon" aria-hidden="true">D</span>
            <span class="auth-brand-name">DRIVEHUB</span>
            <span class="auth-eyebrow">Smart Automotive Management</span>
        </a>

        <div class="auth-intro-copy">
            <h1>Chăm sóc xe thông<br>minh.<br><span>Hành trình an tâm.</span></h1>
            <p class="auth-lead">Quản lý phương tiện, đặt lịch dịch vụ và theo dõi quá trình sửa chữa của bạn trong một nền tảng duy nhất.</p>

            <div class="auth-benefits" aria-label="Tiện ích DriveHub">
                <span>Quản lý xe dễ dàng</span>
                <span>Theo dõi sửa chữa</span>
                <span>Đặt lịch thuận tiện</span>
            </div>
        </div>
    </section>

    <section class="auth-panel" aria-labelledby="register-title">
        <div class="auth-card">
            <div class="auth-card-heading">
                <h2 id="register-title">Tạo tài khoản khách hàng</h2>
                <p>Đăng ký khách hàng để quản lý xe và lịch bảo dưỡng.</p>
            </div>

            <form class="auth-form" method="post" action="<?= e(url('register')) ?>">
                <?= csrf_field() ?>
                <div class="auth-field">
                    <label for="full_name">Họ và tên</label>
                    <input id="full_name" name="full_name" maxlength="100" autocomplete="name" required value="<?= e($old['full_name'] ?? '') ?>"<?= isset($errors['full_name']) ? ' aria-invalid="true" aria-describedby="full_name-error"' : '' ?>>
                    <?php if (isset($errors['full_name'])): ?><p class="auth-field-error" id="full_name-error"><?= e($errors['full_name']) ?></p><?php endif; ?>
                </div>

                <div class="auth-field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" maxlength="150" autocomplete="email" required value="<?= e($old['email'] ?? '') ?>"<?= isset($errors['email']) ? ' aria-invalid="true" aria-describedby="email-error"' : '' ?>>
                    <?php if (isset($errors['email'])): ?><p class="auth-field-error" id="email-error"><?= e($errors['email']) ?></p><?php endif; ?>
                </div>

                <div class="auth-field">
                    <label for="phone">Số điện thoại</label>
                    <input id="phone" name="phone" type="tel" maxlength="20" autocomplete="tel" required value="<?= e($old['phone'] ?? '') ?>"<?= isset($errors['phone']) ? ' aria-invalid="true" aria-describedby="phone-error"' : '' ?>>
                    <?php if (isset($errors['phone'])): ?><p class="auth-field-error" id="phone-error"><?= e($errors['phone']) ?></p><?php endif; ?>
                </div>

                <div class="auth-field">
                    <label for="password">Mật khẩu <span class="auth-label-hint">(ít nhất 8 ký tự)</span></label>
                    <div class="auth-password-wrap">
                        <input id="password" name="password" type="password" minlength="8" maxlength="72" autocomplete="new-password" required<?= isset($errors['password']) ? ' aria-invalid="true" aria-describedby="password-error"' : '' ?>>
                        <button class="auth-password-toggle" type="button" data-password-toggle="password" aria-label="Hiện mật khẩu" aria-pressed="false">
                            <svg class="auth-eye-open" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M2.5 12s3.4-6 9.5-6 9.5 6 9.5 6-3.4 6-9.5 6-9.5-6-9.5-6Z" stroke="currentColor" stroke-width="1.7"/><circle cx="12" cy="12" r="2.7" stroke="currentColor" stroke-width="1.7"/></svg>
                            <svg class="auth-eye-closed" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m3 3 18 18M10.6 6.2A9.8 9.8 0 0 1 12 6c6.1 0 9.5 6 9.5 6a15 15 0 0 1-3.1 3.6M6.2 6.7C3.8 8.2 2.5 12 2.5 12s3.4 6 9.5 6c1 0 2-.2 2.8-.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                        </button>
                    </div>
                    <?php if (isset($errors['password'])): ?><p class="auth-field-error" id="password-error"><?= e($errors['password']) ?></p><?php endif; ?>
                </div>

                <div class="auth-field">
                    <label for="password_confirmation">Nhập lại mật khẩu</label>
                    <div class="auth-password-wrap">
                        <input id="password_confirmation" name="password_confirmation" type="password" minlength="8" maxlength="72" autocomplete="new-password" required<?= isset($errors['password_confirmation']) ? ' aria-invalid="true" aria-describedby="password_confirmation-error"' : '' ?>>
                        <button class="auth-password-toggle" type="button" data-password-toggle="password_confirmation" aria-label="Hiện mật khẩu" aria-pressed="false">
                            <svg class="auth-eye-open" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M2.5 12s3.4-6 9.5-6 9.5 6 9.5 6-3.4 6-9.5 6-9.5-6-9.5-6Z" stroke="currentColor" stroke-width="1.7"/><circle cx="12" cy="12" r="2.7" stroke="currentColor" stroke-width="1.7"/></svg>
                            <svg class="auth-eye-closed" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m3 3 18 18M10.6 6.2A9.8 9.8 0 0 1 12 6c6.1 0 9.5 6 9.5 6a15 15 0 0 1-3.1 3.6M6.2 6.7C3.8 8.2 2.5 12 2.5 12s3.4 6 9.5 6c1 0 2-.2 2.8-.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                        </button>
                    </div>
                    <?php if (isset($errors['password_confirmation'])): ?><p class="auth-field-error" id="password_confirmation-error"><?= e($errors['password_confirmation']) ?></p><?php endif; ?>
                </div>

                <button class="auth-submit" type="submit">Tạo tài khoản <span aria-hidden="true">→</span></button>
            </form>

            <p class="auth-switch">Đã có tài khoản? <a href="<?= e(url('login')) ?>">Đăng nhập</a></p>
        </div>
        <p class="auth-copyright">© 2026 DRIVEHUB · SMART AUTOMOTIVE MANAGEMENT</p>
    </section>
</main>
</body>
</html>
