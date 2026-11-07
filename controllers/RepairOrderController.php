<?php

class RepairOrderController
{
    public function index()
    {
        require_role('admin');
        $search = trim((string) ($_GET['search'] ?? ''));
        $search = mb_substr($search, 0, 100);
        $status = (string) ($_GET['status'] ?? '');
        $allowedStatuses = ['received', 'in_progress', 'completed', 'cancelled'];

        if (!in_array($status, $allowedStatuses, true)) {
            $status = '';
        }

        $orders = (new RepairOrderModel())->getAll($search, $status);
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
            } elseif (($_POST['operation'] ?? '') === 'save_diagnosis') {
                $diagnosis = trim((string) ($_POST['diagnosis'] ?? ''));

                if (mb_strlen($diagnosis) > 5000) {
                    $errors[] = 'Chẩn đoán không được vượt quá 5000 ký tự.';
                } else {
                    try {
                        (new RepairOrderModel())->updateDiagnosis((int) $id, $diagnosis);
                        header('Location: ' . BASE_URL . '?action=repair-order&id=' . $id . '&notice=diagnosis-saved');
                        exit;
                    } catch (DomainException $exception) {
                        $errors[] = $exception->getMessage();
                    }
                }
            } elseif (in_array(($_POST['operation'] ?? ''), ['add_detail', 'update_detail'], true)) {
                $detailId = filter_var($_POST['detail_id'] ?? null, FILTER_VALIDATE_INT);
                $serviceId = filter_var($_POST['service_id'] ?? null, FILTER_VALIDATE_INT);
                $quantity = filter_var($_POST['quantity'] ?? null, FILTER_VALIDATE_INT);
                $unitPrice = trim((string) ($_POST['unit_price'] ?? ''));
                $note = trim((string) ($_POST['note'] ?? ''));
                $operation = (string) $_POST['operation'];

                if (($operation === 'update_detail' && (!$detailId || $detailId < 1))
                    || ($operation === 'add_detail' && (!$serviceId || $serviceId < 1))
                    || $quantity === false || $quantity < 1 || $quantity > 9999
                    || !preg_match('/^\d{1,10}(?:\.\d{1,2})?$/', $unitPrice)
                    || mb_strlen($note) > 2000
                ) {
                    $errors[] = 'Vui lòng kiểm tra dịch vụ, số lượng, đơn giá và ghi chú hạng mục.';
                } else {
                    try {
                        $repairOrderModel = new RepairOrderModel();
                        if ($operation === 'add_detail') {
                            $repairOrderModel->addDetail((int) $id, (int) $serviceId, (int) $quantity, $unitPrice, $note);
                        } else {
                            $repairOrderModel->updateDetail((int) $id, (int) $detailId, (int) $quantity, $unitPrice, $note);
                        }
                        header('Location: ' . BASE_URL . '?action=repair-order&id=' . $id . '&notice=detail-saved');
                        exit;
                    } catch (DomainException $exception) {
                        $errors[] = $exception->getMessage();
                    }
                }
            } elseif (($_POST['operation'] ?? '') === 'complete') {
                $mileage = filter_var($_POST['mileage'] ?? null, FILTER_VALIDATE_INT);
                $diagnosis = trim((string) ($_POST['diagnosis'] ?? ''));

                if ($mileage === false || $mileage < 0 || mb_strlen($diagnosis) > 5000) {
                    $errors[] = 'Vui lòng nhập số km hợp lệ và chẩn đoán không quá 5000 ký tự.';
                } else {
                    try {
                        (new RepairOrderModel())->complete((int) $id, (int) $mileage, $diagnosis, (int) $admin['user_id']);
                        header('Location: ' . BASE_URL . '?action=repair-order&id=' . $id . '&notice=completed');
                        exit;
                    } catch (DomainException $exception) {
                        $errors[] = $exception->getMessage();
                    }
                }
            } elseif (($_POST['operation'] ?? '') === 'in_progress') {
                if ((new RepairOrderModel())->updateStatus((int) $id, 'in_progress')) {
                    header('Location: ' . BASE_URL . '?action=repair-order&id=' . $id . '&notice=in-progress');
                    exit;
                }
                $errors[] = 'Phiếu không còn ở trạng thái có thể bắt đầu sửa chữa.';
            } elseif (($_POST['operation'] ?? '') === 'cancel') {
                if ((new RepairOrderModel())->updateStatus((int) $id, 'cancelled')) {
                    header('Location: ' . BASE_URL . '?action=repair-order&id=' . $id . '&notice=cancelled');
                    exit;
                }
                $errors[] = 'Phiếu không còn ở trạng thái có thể hủy.';
            } else {
                $errors[] = 'Thao tác không hợp lệ.';
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $errors !== []) {
            $order = $isAdmin
                ? (new RepairOrderModel())->getById((int) $id)
                : $order;
        }

        $notice = (string) ($_GET['notice'] ?? '');
        $flash = [
            'diagnosis-saved' => 'Đã lưu chẩn đoán.',
            'detail-saved' => 'Đã lưu hạng mục sửa chữa.',
            'in-progress' => 'Đã cập nhật phiếu sang trạng thái đang sửa chữa.',
            'completed' => 'Đã hoàn tất phiếu sửa chữa.',
            'cancelled' => 'Đã hủy phiếu sửa chữa.',
        ][$notice] ?? null;
        $services = $isAdmin && in_array($order['status'], ['received', 'in_progress'], true)
            ? (new ServiceModel())->getActiveServices()
            : [];
        $csrfToken = csrf_token();
        $title = 'Chi tiết phiếu sửa chữa - DriveHub';
        $view = 'repairs/show';
        require_once PATH_VIEW_MAIN;
    }
}
