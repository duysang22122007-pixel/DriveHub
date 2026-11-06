<header class="site-header" id="site-header">
    <div class="container header__inner">
        <?php require PATH_VIEW . 'partials/logo.php'; ?>
        <?php $currentAction = $_GET['action'] ?? '/'; ?>

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
                <!-- Route lịch sử bảo dưỡng chưa có trong dự án: giữ placeholder chờ tích hợp -->
                <li>
                    <a class="site-nav__link" href="#" data-pending="Lịch sử bảo dưỡng" title="Chức năng lịch sử bảo dưỡng sẽ được tích hợp">Lịch sử bảo dưỡng</a>
                </li>
                <li>
                    <a class="site-nav__link" href="#lien-he">Liên hệ</a>
                </li>
            </ul>

            <a class="btn btn--primary header__cta" href="<?= BASE_URL ?>?action=booking">
                Đặt lịch ngay
            </a>
        </nav>

        <button class="nav-toggle" type="button" aria-controls="site-nav" aria-expanded="false" aria-label="Mở menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
</header>
