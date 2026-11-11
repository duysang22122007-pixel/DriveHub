<header class="admin-topbar">
    <a class="admin-brand" href="<?= e(url('admin')) ?>"><span class="admin-brand-mark">D</span> DriveHub</a>
    <div class="admin-top-actions">
        <a href="<?= e(url()) ?>">Xem trang chủ</a>
        <form method="post" action="<?= e(url('logout')) ?>">
            <?= csrf_field() ?>
            <button class="admin-logout" type="submit">Đăng xuất</button>
        </form>
    </div>
</header>
<div class="admin-layout">
    <aside class="admin-sidebar" aria-label="Điều hướng quản trị">
        <div class="admin-side-brand"><span class="admin-brand-mark">D</span> DriveHub</div>
        <p class="admin-nav-label">Khu vực quản trị</p>
        <nav class="admin-nav-list">
            <a class="admin-nav-link <?= $activeAdminPage === 'dashboard' ? 'active' : '' ?>" href="<?= e(url('admin')) ?>" <?= $activeAdminPage === 'dashboard' ? 'aria-current="page"' : '' ?>><span class="admin-nav-icon">T</span><span>Tổng quan</span></a>
            <a class="admin-nav-link <?= $activeAdminPage === 'customers' ? 'active' : '' ?>" href="<?= e(url('admin/customers')) ?>" <?= $activeAdminPage === 'customers' ? 'aria-current="page"' : '' ?>><span class="admin-nav-icon">K</span><span>Khách hàng</span></a>
            <a class="admin-nav-link <?= $activeAdminPage === 'vehicles' ? 'active' : '' ?>" href="<?= e(url('admin/vehicles')) ?>" <?= $activeAdminPage === 'vehicles' ? 'aria-current="page"' : '' ?>><span class="admin-nav-icon">P</span><span>Phương tiện</span></a>
            <a class="admin-nav-link <?= $activeAdminPage === 'brands' ? 'active' : '' ?>" href="<?= e(url('admin/brands')) ?>" <?= $activeAdminPage === 'brands' ? 'aria-current="page"' : '' ?>><span class="admin-nav-icon">H</span><span>Hãng xe</span></a>
        </nav>
    </aside>
    <div class="admin-content">
