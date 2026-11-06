<?php

class RepairOrderController
{
    public function index()
    {
        require_role('admin');
        $orders = (new RepairOrderModel())->getAll();
        $title = 'Phiếu sửa chữa - DriveHub';
        $view = 'repairs/index';
        require_once PATH_VIEW_MAIN;
    }

    public function createFromAppointment()
    {
        $user = require_role('admin');
        $appointmentId = filter_var($_GET['appointment_id'] ?? $_POST['appointment_id'] ?? null, FILTER_VALIDATE_INT);
        $appointment = $appointmentId ? (new AppointmentModel())->getById((int) $appointmentId) : null;

        if (!$appointment) {
            http_response_code(404);
            exit('Không tìm thấy lịch hẹn.');
        }

        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf_token((string) ($_POST['csrf_token'] ?? ''))) {
                $errors[] = 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang và thử lại.';
            } elseif ($user['employee_id'] === null) {
                $errors[] = 'Tài khoản nhân viên chưa được liên kết hồ sơ nhân viên.';
            } else {
                try {
                    $repairOrderId = (new RepairOrderModel())->createFromAppointment(
                        (int) $appointment['id'],
                        (int) $appointment['car_id'],
                        (int) $user['employee_id'],
                        $appointment['description'],
                        (int) $user['user_id']
                    );
                    header('Location: ' . BASE_URL . '?action=repair-order&id=' . $repairOrderId);
                    exit;
                } catch (DomainException $exception) {
                    $errors[] = $exception->getMessage();
                }
            }
        }

        $csrfToken = csrf_token();
        $title = 'Tiếp nhận xe - DriveHub';
        $view = 'repairs/create';
        require_once PATH_VIEW_MAIN;
    }

    public function show()
    {
        $admin = current_admin();
        $customer = current_customer();
        if ($admin === null && $customer === null) {
            checkAdminAuth();
        }
        $isAdmin = $admin !== null;

        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id) {
            http_response_code(404);
            exit('Không tìm thấy phiếu sửa chữa.');
        }

        if ($isAdmin) {
            $order = (new RepairOrderModel())->getById((int) $id);
        } else {
            $order = (new RepairOrderModel())->getForCustomer((int) $id, (int) $customer['customer_id']);
        }

        if (!$order) {
            http_response_code(404);
            exit('Không tìm thấy phiếu sửa chữa.');
        }

        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $admin = require_role('admin');

            if (!verify_csrf_token((string) ($_POST['csrf_token'] ?? ''))) {
                $errors[] = 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang và thử lại.';
            } elseif (($_POST['operation'] ?? '') === 'complete') {
                $mileage = filter_var($_POST['mileage'] ?? null, FILTER_VALIDATE_INT);
                $diagnosis = trim((string) ($_POST['diagnosis'] ?? ''));

                if ($mileage === false || $mileage < 0 || mb_strlen($diagnosis) > 5000) {
                    $errors[] = 'Vui lòng nhập số km hợp lệ và chẩn đoán không quá 5000 ký tự.';
                } else {
                    try {
                        (new RepairOrderModel())->complete((int) $id, (int) $mileage, $diagnosis, (int) $admin['user_id']);
                        header('Location: ' . BASE_URL . '?action=repair-order&id=' . $id);
                        exit;
                    } catch (DomainException $exception) {
                        $errors[] = $exception->getMessage();
                    }
                }
            } elseif (($_POST['operation'] ?? '') === 'in_progress') {
                (new RepairOrderModel())->updateStatus((int) $id, 'in_progress');
                header('Location: ' . BASE_URL . '?action=repair-order&id=' . $id);
                exit;
            } else {
                $errors[] = 'Thao tác không hợp lệ.';
            }
        }

        $csrfToken = csrf_token();
        $title = 'Chi tiết phiếu sửa chữa - DriveHub';
        $view = 'repairs/show';
        require_once PATH_VIEW_MAIN;
    }
}
