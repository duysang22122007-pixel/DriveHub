<?php

$action = $_GET['action'] ?? '/';

match ($action) {
    '/' => (new HomeController)->index(),
    'services' => (new ServicesController)->index(),
    'booking' => (new BookingController)->index(),
    'auth' => (new AuthController)->index(),
    'logout' => (new AuthController)->logout(),
    'my-appointments' => (new AppointmentController)->index(),
    'my-cars' => (new CarController)->index(),
    'car-history' => (new CarController)->history(),
    'admin-appointments' => (new AdminAppointmentController)->index(),
    'repair-orders' => (new RepairOrderController)->index(),
    'repair-order-create' => (new RepairOrderController)->createFromAppointment(),
    'repair-order' => (new RepairOrderController)->show(),
    default => http_response_code(404),
};