<?php
$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<section class="workflow-page">
    <div class="container workflow-narrow">
        <p class="eyebrow"><span></span>Khu vực quản trị</p>
        <h1>Chi tiết tài khoản Admin</h1>
        <?php if ($errors !== []): ?>
            <div class="workflow-alert workflow-alert--error" role="alert">
                <?php foreach ($errors as $error): ?><p><?= $e($error) ?></p><?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form class="workflow-card workflow-form" method="post" action="<?= BASE_URL ?>?action=admin-user&amp;id=<?= (int) $account['user_id'] ?>">
            <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
            <label>Họ tên<input type="text" name="full_name" value="<?= $e($values['full_name']) ?>" maxlength="100" required></label>
            <label>Chức vụ<input type="text" name="position" value="<?= $e($values['position']) ?>" maxlength="80" required></label>
            <label>Số điện thoại<input type="tel" name="phone" value="<?= $e($values['phone']) ?>" maxlength="15" required></label>
            <label>Tên đăng nhập<input type="text" name="username" value="<?= $e($values['username']) ?>" maxlength="80" required></label>
            <label>Email<input type="email" name="email" value="<?= $e($values['email']) ?>" maxlength="150" required></label>
            <label>Trạng thái tài khoản
                <select name="status">
                    <option value="1"<?= (string) $values['status'] === '1' ? ' selected' : '' ?>>Đang hoạt động</option>
                    <option value="0"<?= (string) $values['status'] === '0' ? ' selected' : '' ?>>Đã khóa</option>
                </select>
            </label>
            <label>Đặt mật khẩu mới (để trống nếu không đổi)<input type="password" name="password" minlength="12" autocomplete="new-password"></label>
            <label>Xác nhận mật khẩu mới<input type="password" name="password_confirmation" minlength="12" autocomplete="new-password"></label>
            <button class="workflow-button" type="submit">Lưu thay đổi</button>
        </form>
        <p><a class="workflow-link" href="<?= BASE_URL ?>?action=admin-users">← Danh sách tài khoản Admin</a></p>
    </div>
</section>
