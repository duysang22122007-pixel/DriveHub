<?php

declare(strict_types=1);

use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\AdminController;
use App\Controllers\VehicleController;
use App\Controllers\ProfileController;
use App\Core\Router;
use App\Middleware\Authenticated;
use App\Middleware\GuestOnly;
use App\Middleware\RequireRole;
use App\Middleware\VerifyCsrfToken;

return static function (Router $router, array $databaseConfig, array $garageConfig): void {
    $homeController = new HomeController($databaseConfig, $garageConfig);
    $authController = new AuthController($databaseConfig);
    $vehicleController = new VehicleController($databaseConfig);
    $adminController = new AdminController($databaseConfig);
    $profileController = new ProfileController($databaseConfig);
    $guestOnly = new GuestOnly($databaseConfig);
    $authenticated = new Authenticated($databaseConfig);
    $verifyCsrf = new VerifyCsrfToken();
    $customer = new RequireRole('customer');
    $admin = new RequireRole('admin');

    $router->get('/', [$homeController, 'index']);
    $router->get('/register', [$authController, 'showRegister'], [$guestOnly]);
    $router->post('/register', [$authController, 'register'], [$guestOnly, $verifyCsrf]);
    $router->get('/login', [$authController, 'showLogin'], [$guestOnly]);
    $router->post('/login', [$authController, 'login'], [$guestOnly, $verifyCsrf]);
    $router->post('/logout', [$authController, 'logout'], [$authenticated, $verifyCsrf]);
    $router->get('/dashboard', [$authController, 'dashboard'], [$authenticated]);
    $router->get('/admin', [$authController, 'adminHome'], [$authenticated, new RequireRole('admin')]);
    $router->get('/customer', [$authController, 'customerHome'], [$authenticated, new RequireRole('customer')]);

    $router->get('/vehicles', [$vehicleController, 'index'], [$authenticated, $customer]);
    $router->get('/vehicles/create', [$vehicleController, 'create'], [$authenticated, $customer]);
    $router->post('/vehicles', [$vehicleController, 'store'], [$authenticated, $customer, $verifyCsrf]);
    $router->get('/vehicles/{id}/edit', [$vehicleController, 'edit'], [$authenticated, $customer]);
    $router->post('/vehicles/{id}/update', [$vehicleController, 'update'], [$authenticated, $customer, $verifyCsrf]);
    $router->post('/vehicles/{id}/delete', [$vehicleController, 'delete'], [$authenticated, $customer, $verifyCsrf]);

    $router->get('/admin/customers', [$adminController, 'customers'], [$authenticated, $admin]);
    $router->post('/admin/customers/{id}/status', [$adminController, 'updateCustomerStatus'], [$authenticated, $admin, $verifyCsrf]);
    $router->get('/admin/vehicles', [$adminController, 'vehicles'], [$authenticated, $admin]);
    $router->get('/admin/brands', [$adminController, 'brands'], [$authenticated, $admin]);
    $router->post('/admin/brands', [$adminController, 'storeBrand'], [$authenticated, $admin, $verifyCsrf]);
    $router->post('/admin/brands/{id}/status', [$adminController, 'updateBrandStatus'], [$authenticated, $admin, $verifyCsrf]);

    $router->get('/profile', [$profileController, 'showProfile'], [$authenticated]);
    $router->post('/profile', [$profileController, 'updateProfile'], [$authenticated, $verifyCsrf]);
    $router->get('/profile/change-password', [$profileController, 'showChangePassword'], [$authenticated]);
    $router->post('/profile/change-password', [$profileController, 'changePassword'], [$authenticated, $verifyCsrf]);

    $router->get('/forgot-password', [$profileController, 'showForgotPassword'], [$guestOnly]);
    $router->post('/forgot-password', [$profileController, 'processForgotPassword'], [$guestOnly, $verifyCsrf]);
    $router->get('/reset-password/{token}', [$profileController, 'showResetPassword'], [$guestOnly]);
    $router->post('/reset-password/{token}', [$profileController, 'processResetPassword'], [$guestOnly, $verifyCsrf]);
};