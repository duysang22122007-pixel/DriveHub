<?php

class CarController
{
    public function index()
    {
        $user = require_role('customer');
        $customerId = (int) $user['customer_id'];
        $cars = (new CarModel())->getByCustomerId($customerId);
        $history = (new MaintenanceHistoryModel())->getHistoryByCustomerId($customerId);
        $title = 'Xe của tôi - DriveHub';
        $view = 'cars/index';
        require_once PATH_VIEW_MAIN;
    }

    public function history()
    {
        $user = require_role('customer');
        $carId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        $car = $carId ? (new CarModel())->getOwnedCar((int) $carId, (int) $user['customer_id']) : null;

        if (!$car) {
            http_response_code(404);
            exit('Không tìm thấy xe trong tài khoản của bạn.');
        }

        $history = (new MaintenanceHistoryModel())->getHistoryByCarId((int) $car['id']);
        $title = 'Lịch sử bảo dưỡng - DriveHub';
        $view = 'cars/history';
        require_once PATH_VIEW_MAIN;
    }
}
