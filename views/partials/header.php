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
                <?php if ($currentCustomer === null && $currentAdmin === null): ?>
                    <a class="btn btn--primary header-login" href="<?= BASE_URL ?>login">Đăng nhập</a>
                    <a class="header-register" href="<?= BASE_URL ?>?action=auth&amp;mode=register">Đăng ký</a>
                <?php else: ?>
                    <?php
                    $currentAccount = $currentCustomer ?? $currentAdmin;
                    $accountSessionKey = $currentCustomer !== null ? 'customer' : 'admin';
                    if (!isset($currentAccount['email']) || !isset($currentAccount['username'])) {
                        $accountProfile = (new AuthModel())->getAccountProfileByUserId((int) ($currentAccount['user_id'] ?? 0));
                        if ($accountProfile !== null) {
                            $currentAccount['email'] = $accountProfile['email'];
                            $currentAccount['username'] = $accountProfile['username'];
                            $_SESSION[$accountSessionKey]['email'] = $accountProfile['email'];
                            $_SESSION[$accountSessionKey]['username'] = $accountProfile['username'];
                        }
                    }
                    $accountName = trim((string) ($currentAccount['name'] ?? ''));
                    $accountName = $accountName !== '' ? $accountName : (string) ($currentAccount['username'] ?? '');
                    $accountInitial = mb_strtoupper(mb_substr($accountName, 0, 1, 'UTF-8'), 'UTF-8');
                    $accountRole = $currentCustomer !== null ? 'Khách hàng' : 'Quản trị viên';
                    $logoutPath = $currentCustomer !== null ? 'logout' : 'admin/logout';
                    ?>
                    <div class="profile-menu" data-profile-menu>
                        <button
                            class="profile-trigger"
                            type="button"
                            id="profile-trigger"
                            aria-expanded="false"
                            aria-controls="profile-dropdown"
                            aria-haspopup="true"
                        >
                            <span class="profile-trigger__avatar" aria-hidden="true"><?= htmlspecialchars($accountInitial, ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="profile-trigger__name"><?= htmlspecialchars($accountName, ENT_QUOTES, 'UTF-8') ?></span>
                            <svg class="profile-trigger__chevron" aria-hidden="true" viewBox="0 0 16 16" fill="none">
                                <path d="m4 6 4 4 4-4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                        <div class="profile-dropdown" id="profile-dropdown" aria-labelledby="profile-trigger" hidden>
                            <div class="profile-dropdown__identity">
                                <span class="profile-dropdown__avatar" aria-hidden="true"><?= htmlspecialchars($accountInitial, ENT_QUOTES, 'UTF-8') ?></span>
                                <div class="profile-dropdown__details">
                                    <strong><?= htmlspecialchars($accountName, ENT_QUOTES, 'UTF-8') ?></strong>
                                    <span><?= htmlspecialchars((string) ($currentAccount['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                    <small><?= htmlspecialchars($accountRole, ENT_QUOTES, 'UTF-8') ?></small>
                                </div>
                            </div>
                            <div class="profile-dropdown__links">
                                <a href="<?= BASE_URL ?>?action=profile">
                                    <span>Trang cá nhân</span>
                                    <span class="profile-dropdown__arrow" aria-hidden="true">›</span>
                                </a>
                                <a href="<?= BASE_URL ?>?action=profile#account-details">Thông tin tài khoản</a>
                                <?php if ($currentCustomer !== null): ?>
                                    <a href="<?= BASE_URL ?>?action=my-appointments">Lịch hẹn</a>
                                    <a href="<?= BASE_URL ?>?action=my-cars">Xe của tôi</a>
                                    <a href="<?= BASE_URL ?>?action=appointment-history">Lịch sử đặt lịch</a>
                                <?php else: ?>
                                    <a href="<?= BASE_URL ?>?action=admin-appointments">Lịch hẹn gara</a>
                                    <a href="<?= BASE_URL ?>?action=repair-orders">Phiếu sửa chữa</a>
                                <?php endif; ?>
                            </div>
                            <form class="profile-dropdown__logout" method="post" action="<?= BASE_URL . htmlspecialchars($logoutPath, ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                <button type="submit">
                                    <svg aria-hidden="true" viewBox="0 0 20 20" fill="none">
                                        <path d="M8 3H4.75A1.75 1.75 0 0 0 3 4.75v10.5A1.75 1.75 0 0 0 4.75 17H8m4-3 4-4-4-4m4 4H7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    Đăng xuất
                                </button>
                            </form>
                        </div>
                    </div>
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
