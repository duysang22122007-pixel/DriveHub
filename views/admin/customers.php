<?php
$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<section class="workflow-page">
    <div class="container">
        <p class="eyebrow"><span></span>Khu vực quản trị</p>
        <h1>Khách hàng</h1>
        <?php if ($flash !== null): ?><div class="workflow-alert"><?= $e($flash) ?></div><?php endif; ?>
        <?php if ($errors !== []): ?>
            <div class="workflow-alert workflow-alert--error" role="alert">
                <?php foreach ($errors as $error): ?><p><?= $e($error) ?></p><?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form class="workflow-card workflow-filter" method="get" action="<?= BASE_URL ?>">
            <input type="hidden" name="action" value="admin-customers">
            <label>
                <span>Tìm theo tên, email hoặc số điện thoại</span>
                <input type="search" name="search" value="<?= $e($search) ?>" maxlength="100">
            </label>
            <button class="workflow-button" type="submit">Tìm kiếm</button>
            <a class="workflow-link" href="<?= BASE_URL ?>?action=admin-customers">Xóa bộ lọc</a>
        </form>
        <?php if ($customers === []): ?>
            <p class="workflow-empty"><?= $search !== '' ? 'Không tìm thấy khách hàng phù hợp.' : 'Chưa có khách hàng.' ?></p>
        <?php else: ?>
            <div class="workflow-list">
                <?php foreach ($customers as $customer): ?>
                    <article class="workflow-card">
                        <div class="workflow-card__heading">
                            <h2><?= $e($customer['full_name']) ?></h2>
                            <span class="workflow-status"><?= (int) $customer['status'] === 1 ? 'Đang hoạt động' : 'Đã khóa' ?></span>
                        </div>
                        <p><strong>Email:</strong> <?= $e($customer['email']) ?></p>
                        <p><strong>Số điện thoại:</strong> <?= $e($customer['phone']) ?></p>
                        <div class="workflow-actions">
                            <a class="workflow-link" href="<?= BASE_URL ?>?action=admin-customer&amp;id=<?= (int) $customer['customer_id'] ?>">Chi tiết khách hàng</a>
                            <form method="post" action="<?= BASE_URL ?>?action=admin-customers" onsubmit="return confirm('Bạn có chắc muốn <?= (int) $customer['status'] === 1 ? 'khóa' : 'mở khóa' ?> tài khoản này?');">
                                <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
                                <input type="hidden" name="customer_id" value="<?= (int) $customer['customer_id'] ?>">
                                <input type="hidden" name="status" value="<?= (int) $customer['status'] === 1 ? 0 : 1 ?>">
                                <button class="workflow-button workflow-button--muted" type="submit"><?= (int) $customer['status'] === 1 ? 'Khóa tài khoản' : 'Mở khóa tài khoản' ?></button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
