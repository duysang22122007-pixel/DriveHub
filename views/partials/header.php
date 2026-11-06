<header class="site-header" id="site-header">
    <div class="container header__inner">
        <?php require PATH_VIEW . 'partials/logo.php'; ?>
        <?php $currentAction = $_GET['action'] ?? '/'; ?>
        <?php $currentCustomer = current_customer(); ?>
        <?php $currentAdmin = current_admin(); ?>

        <nav class="site-nav" id="site-nav" aria-label="Điều hướng chính">
            <ul class="site-nav__list">
                <li>
                    <a class="site-nav__link<?= $currentAction === '/' ? ' is-active' : '' ?>" href="<?= BASE_URL ?>"<?= $currentAction === '/' ? ' aria-current="page"' : '' ?>>Trang chủ</a>
                </li>
                <li>
                    <a class="site-nav__link" href="#gioi-thieu">Giới thiệu</a>
                </li>
                <li>
                    <a class="site-nav__link<?= $currentAction === 'services' ? ' is-active' : '' ?>" href="<?= BASE_URL ?>?action=services"<?= $currentAction === 'services' ? ' aria-current="page"' : '' ?>>Dịch vụ</a>
                </li>
                <li>
                    <a class="site-nav__link<?= $currentAction === 'appointment-history' ? ' is-active' : '' ?>" href="<?= BASE_URL ?>?action=appointment-history"<?= $currentAction === 'appointment-history' ? ' aria-current="page"' : '' ?>>Lịch sử đặt lịch</a>
                </li>
                <?php if ($currentCustomer !== null): ?>
                    <li><a class="site-nav__link<?= $currentAction === 'my-appointments' ? ' is-active' : '' ?>" href="<?= BASE_URL ?>?action=my-appointments">Lịch hẹn</a></li>
                    <li><a class="site-nav__link<?= in_array($currentAction, ['my-cars', 'car-history'], true) ? ' is-active' : '' ?>" href="<?= BASE_URL ?>?action=my-cars">Xe của tôi</a></li>
                <?php endif; ?>
                <?php if ($currentAdmin !== null): ?>
                    <li><a class="site-nav__link<?= $currentAction === 'admin-appointments' ? ' is-active' : '' ?>" href="<?= BASE_URL ?>?action=admin-appointments">Lịch hẹn gara</a></li>
                    <li><a class="site-nav__link<?= in_array($currentAction, ['repair-orders', 'repair-order', 'repair-order-create'], true) ? ' is-active' : '' ?>" href="<?= BASE_URL ?>?action=repair-orders">Phiếu sửa chữa</a></li>
                <?php endif; ?>
                <li>
                    <a class="site-nav__link" href="#lien-he">Liên hệ</a>
                </li>
            </ul>

            <a class="btn btn--primary header__cta" href="<?= BASE_URL ?>?action=<?= $currentAdmin !== null ? 'admin-appointments' : 'booking' ?>">
                <?= $currentAdmin !== null ? 'Lịch hẹn gara' : 'Đặt lịch ngay' ?>
            </a>
            <div class="header-login-group">
                <?php if ($currentCustomer === null): ?>
                    <a class="btn btn--primary header-login" href="<?= BASE_URL ?>login">Đăng nhập</a>
                <?php else: ?>
                    <form class="header-logout" method="post" action="<?= BASE_URL ?>logout">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                        <button class="site-nav__link header-auth" type="submit">Đăng xuất khách hàng</button>
                    </form>
                <?php endif; ?>
                <?php if ($currentAdmin !== null): ?>
                    <form class="header-logout" method="post" action="<?= BASE_URL ?>admin/logout">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                        <button class="site-nav__link header-auth" type="submit">Đăng xuất Admin</button>
                    </form>
                <?php endif; ?>
            </div>
        </nav>

        <button class="nav-toggle" type="button" aria-controls="site-nav" aria-expanded="false" aria-label="Mở menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
</header>
