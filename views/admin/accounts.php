<?php
$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<section class="workflow-page">
    <div class="container">
        <p class="eyebrow"><span></span>Khu vực quản trị</p>
        <h1>Tài khoản Admin</h1>
        <?php if ($flash !== null): ?><div class="workflow-alert"><?= $e($flash) ?></div><?php endif; ?>
        <p class="workflow-actions"><a class="workflow-button" href="<?= BASE_URL ?>?action=admin-user-create">Tạo tài khoản Admin</a></p>
        <form class="workflow-card workflow-filter" method="get" action="<?= BASE_URL ?>">
            <input type="hidden" name="action" value="admin-users">
            <label>
                <span>Tìm theo tên, tên đăng nhập, email hoặc số điện thoại</span>
                <input type="search" name="search" value="<?= $e($search) ?>" maxlength="100">
            </label>
            <button class="workflow-button" type="submit">Tìm kiếm</button>
            <a class="workflow-link" href="<?= BASE_URL ?>?action=admin-users">Xóa bộ lọc</a>
        </form>
        <?php if ($admins === []): ?>
            <p class="workflow-empty"><?= $search !== '' ? 'Không tìm thấy tài khoản phù hợp.' : 'Chưa có tài khoản Admin.' ?></p>
        <?php else: ?>
            <div class="workflow-list">
                <?php foreach ($admins as $account): ?>
                    <article class="workflow-card">
                        <div class="workflow-card__heading">
                            <h2><?= $e($account['full_name']) ?></h2>
                            <span class="workflow-status"><?= (int) $account['status'] === 1 ? 'Đang hoạt động' : 'Đã khóa' ?></span>
                        </div>
                        <p><strong>Tên đăng nhập:</strong> <?= $e($account['username']) ?></p>
                        <p><strong>Email:</strong> <?= $e($account['email']) ?></p>
                        <p><strong>Số điện thoại:</strong> <?= $e($account['phone']) ?></p>
                        <a class="workflow-link" href="<?= BASE_URL ?>?action=admin-user&amp;id=<?= (int) $account['user_id'] ?>">Xem và chỉnh sửa tài khoản</a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
