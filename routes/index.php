<?php

$action = $_GET['action'] ?? null;

if ($action === null) {
    $path = trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
    $basePath = trim((string) parse_url(BASE_URL, PHP_URL_PATH), '/');

    if ($basePath !== '' && ($path === $basePath || str_starts_with($path, $basePath . '/'))) {
        $path = ltrim(substr($path, strlen($basePath)), '/');
    }

    $action = in_array($path, ['login', 'logout', 'admin/login', 'admin/logout'], true)
        ? $path
        : '/';
}

match ($action) {
    '/' => (new HomeController)->index(),
    'services' => (new ServicesController)->index(),
    'booking' => (new BookingController)->index(),
    'auth' => (new AuthController)->index(),
    'profile' => (new ProfileController)->index(),
    'admin-dashboard' => (new AdminDashboardController)->index(),
    'admin-users' => (new AdminAccountController)->index(),
    'admin-user-create' => (new AdminAccountController)->create(),
    'admin-user' => (new AdminAccountController)->show(),
    'admin-customers' => (new CustomerManagementController)->index(),
    'admin-customer' => (new CustomerManagementController)->show(),
    'login' => (new AuthController)->customerLogin(),
    'customer-login' => (new AuthController)->customerLogin(),
    'admin/login' => (new AuthController)->adminLogin(),
    'admin-login' => (new AuthController)->adminLogin(),
    'logout' => (new AuthController)->logout(),
    'customer-logout' => (new AuthController)->customerLogout(),
    'admin/logout' => (new AuthController)->adminLogout(),
    'admin-logout' => (new AuthController)->adminLogout(),
    'my-appointments' => (new AppointmentController)->index(),
    'appointment-history' => (new AppointmentController)->history(),
    'my-cars' => (new CarController)->index(),
    'car-history' => (new CarController)->history(),
    'admin-appointments' => (new AdminAppointmentController)->index(),
    'repair-orders' => (new RepairOrderController)->index(),
    'repair-order-create' => (new RepairOrderController)->createFromAppointment(),
    'repair-order' => (new RepairOrderController)->show(),
    default => http_response_code(404),
};