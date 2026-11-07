<?php
$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<section class="workflow-page">
    <div class="container workflow-narrow">
        <p class="eyebrow"><span></span>Khu vực quản trị</p>
        <h1>Tạo tài khoản Admin</h1>
        <?php if ($errors !== []): ?>
            <div class="workflow-alert workflow-alert--error" role="alert">
                <?php foreach ($errors as $error): ?><p><?= $e($error) ?></p><?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form class="workflow-card workflow-form" method="post" action="<?= BASE_URL ?>?action=admin-user-create">
            <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
            <label>Họ tên<input type="text" name="full_name" value="<?= $e($values['full_name']) ?>" maxlength="100" required></label>
            <label>Chức vụ<input type="text" name="position" value="<?= $e($values['position']) ?>" maxlength="80" required></label>
            <label>Số điện thoại<input type="tel" name="phone" value="<?= $e($values['phone']) ?>" maxlength="15" required></label>
            <label>Tên đăng nhập<input type="text" name="username" value="<?= $e($values['username']) ?>" maxlength="80" required autocomplete="username"></label>
            <label>Email<input type="email" name="email" value="<?= $e($values['email']) ?>" maxlength="150" required autocomplete="email"></label>
            <label>Mật khẩu (ít nhất 12 ký tự)<input type="password" name="password" minlength="12" required autocomplete="new-password"></label>
            <label>Xác nhận mật khẩu<input type="password" name="password_confirmation" minlength="12" required autocomplete="new-password"></label>
            <button class="workflow-button" type="submit">Tạo tài khoản Admin</button>
        </form>
        <p><a class="workflow-link" href="<?= BASE_URL ?>?action=admin-users">← Danh sách tài khoản Admin</a></p>
    </div>
</section>
