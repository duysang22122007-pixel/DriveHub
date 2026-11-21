<?php

declare(strict_types=1);

$activeCustomerNav = is_string($activeCustomerNav ?? null) ? $activeCustomerNav : '';
$customerHeaderRole = $_SESSION['user_role'] ?? null;
$customerHeaderName = is_string($_SESSION['user_name'] ?? null) ? trim($_SESSION['user_name']) : '';
$customerHeaderOnHome = ($customerHeaderOnHome ?? false) === true;
$customerHeaderHomeUrl = $customerHeaderRole === 'admin'
    ? url('admin')
    : ($customerHeaderRole === 'customer' ? url('customer') : url('services'));
$customerSectionHref = static function (string $section) use (
    $customerHeaderHomeUrl,
    $customerHeaderOnHome,
    $customerHeaderRole
): string {
    if ($customerHeaderOnHome && $customerHeaderRole === 'customer') {
        return '#' . $section;
    }

    return $customerHeaderRole === 'customer'
        ? $customerHeaderHomeUrl . '#' . $section
        : url('services');
};
$customerServicesHref = $activeCustomerNav === 'services'
    ? url('services')
    : $customerSectionHref('services');
$customerAppointmentsHref = $customerHeaderRole === 'admin'
    ? url('admin/appointments')
    : ($customerHeaderRole === 'customer' ? url('appointments') : url('login'));
$customerPrimaryHref = $customerHeaderRole === 'admin'
    ? url('admin')
    : ($customerHeaderRole === 'customer' ? url('vehicles') : url('register'));
$customerPrimaryLabel = $customerHeaderRole === 'admin'
    ? 'Mở quản trị'
    : ($customerHeaderRole === 'customer' ? 'Xe của tôi' : 'Đăng ký');
$customerAccountHref = url('profile');
$customerAccountLabel = $customerHeaderName !== ''
    ? $customerHeaderName
    : ($customerHeaderRole === 'admin' ? 'Quản trị viên' : 'Tài khoản');
$customerHeaderGarage = is_array($garage ?? null)
    ? $garage
    : require dirname(__DIR__, 3) . '/config/garage.php';
?>
<div class="customer-topline">
    <div class="customer-header-container customer-topline-inner">
        <span class="customer-topline-note">Chăm sóc xe chuyên nghiệp, đồng hành trên mọi hành trình</span>
        <?php if (is_string($customerHeaderGarage['phone'] ?? null) && $customerHeaderGarage['phone'] !== ''): ?>
            <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $customerHeaderGarage['phone'])) ?>">Hotline: <?= e($customerHeaderGarage['phone']) ?></a>
        <?php else: ?>
            <span>Trung tâm chăm sóc và sửa chữa ô tô</span>
        <?php endif; ?>
    </div>
</div>
<header class="customer-site-header">
    <div class="customer-header-container customer-header-inner">
        <a class="customer-brand" href="<?= e($customerHeaderHomeUrl) ?>" aria-label="AutoCare Hub - Trang chủ">
            <span class="customer-brand-mark" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none"><path d="M3 13.5h18l-1.6-5a2 2 0 0 0-1.9-1.4H6.5a2 2 0 0 0-1.9 1.4L3 13.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M4 13.5v4a1.5 1.5 0 0 0 1.5 1.5H7m13-5.5v4a1.5 1.5 0 0 1-1.5 1.5H17M3 15h2m14 0h2m-13-8 1 4m7-4-1 4m-5 5h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            </span>
            <span>AutoCare Hub</span>
        </a>
        <input class="customer-menu-toggle" type="checkbox" id="customer-menu-toggle" aria-label="Bật hoặc tắt menu">
        <label class="customer-menu-label" for="customer-menu-toggle" aria-label="Mở menu">☰</label>
        <nav class="customer-header-nav" aria-label="Điều hướng chính">
            <a href="<?= e($customerSectionHref('home')) ?>"<?= $activeCustomerNav === 'home' ? ' aria-current="page"' : '' ?>>Trang chủ</a>
            <a href="<?= e($customerServicesHref) ?>"<?= $activeCustomerNav === 'services' ? ' aria-current="page"' : '' ?>>Dịch vụ</a>
            <a href="<?= e($customerSectionHref('process')) ?>">Quy trình</a>
            <a href="<?= e($customerSectionHref('contact')) ?>">Liên hệ</a>
            <?php if ($customerHeaderRole === 'customer' || $customerHeaderRole === 'admin'): ?>
                <a href="<?= e($customerAppointmentsHref) ?>"<?= $activeCustomerNav === 'appointments' ? ' aria-current="page"' : '' ?>><?= $customerHeaderRole === 'admin' ? 'Quản lý lịch' : 'Lịch hẹn' ?></a>
            <?php endif; ?>
            <div class="customer-header-actions">
                <a class="customer-nav-primary" href="<?= e($customerPrimaryHref) ?>"<?= $activeCustomerNav === 'vehicles' ? ' aria-current="page"' : '' ?>><?= e($customerPrimaryLabel) ?></a>
                <?php if ($customerHeaderRole === 'customer' || $customerHeaderRole === 'admin'): ?>
                    <a class="customer-nav-account" href="<?= e($customerAccountHref) ?>"<?= $activeCustomerNav === 'account' ? ' aria-current="page"' : '' ?>><?= e($customerAccountLabel) ?></a>
                    <form class="customer-logout-form" method="post" action="<?= e(url('logout')) ?>">
                        <?= csrf_field() ?>
                        <button class="customer-header-link customer-nav-logout" type="submit">Đăng xuất</button>
                    </form>
                <?php else: ?>
                    <a class="customer-nav-account" href="<?= e(url('login')) ?>">Đăng nhập</a>
                <?php endif; ?>
            </div>
        </nav>
    </div>
</header>
