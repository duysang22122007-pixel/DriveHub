<?php

class CustomerManagementController
{
    public function index(): void
    {
        require_role('admin');
        $search = mb_substr(trim((string) ($_GET['search'] ?? '')), 0, 100);
        $customers = (new CustomerManagementModel())->getAll($search);
        $notice = (string) ($_GET['notice'] ?? '');
        $flash = $notice === 'locked'
            ? 'Đã khóa tài khoản khách hàng.'
            : ($notice === 'unlocked' ? 'Đã mở khóa tài khoản khách hàng.' : null);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf_token((string) ($_POST['csrf_token'] ?? ''))) {
                $errors = ['Phiên làm việc đã hết hạn. Vui lòng tải lại trang và thử lại.'];
            } else {
                $customerId = filter_var($_POST['customer_id'] ?? null, FILTER_VALIDATE_INT);
                $status = filter_var($_POST['status'] ?? null, FILTER_VALIDATE_INT);
                $model = new CustomerManagementModel();

                if (!$customerId || !in_array($status, [0, 1], true)) {
                    $errors = ['Yêu cầu cập nhật trạng thái không hợp lệ.'];
                } elseif ($model->getById((int) $customerId) === null) {
                    http_response_code(404);
                    exit('Không tìm thấy tài khoản khách hàng.');
                } else {
                    $model->setStatus((int) $customerId, (int) $status);
                    $this->redirectForStatus($status);
                }
            }
        } else {
            $errors = [];
        }

        $csrfToken = csrf_token();
        $title = 'Quản lý khách hàng - DriveHub';
        $view = 'admin/customers';
        require_once PATH_VIEW_MAIN;
    }

    public function show(): void
    {
        require_role('admin');
        $customerId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        $model = new CustomerManagementModel();
        $customer = $customerId ? $model->getById((int) $customerId) : null;

        if (!$customer) {
            http_response_code(404);
            exit('Không tìm thấy khách hàng.');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf_token((string) ($_POST['csrf_token'] ?? ''))) {
                $errors = ['Phiên làm việc đã hết hạn. Vui lòng tải lại trang và thử lại.'];
            } else {
                $status = filter_var($_POST['status'] ?? null, FILTER_VALIDATE_INT);
                if (!in_array($status, [0, 1], true)) {
                    $errors = ['Trạng thái tài khoản không hợp lệ.'];
                } else {
                    $model->setStatus((int) $customer['customer_id'], (int) $status);
                    $this->redirectForStatus((int) $status);
                }
            }
        } else {
            $errors = [];
        }

        $cars = $model->getCars((int) $customer['customer_id']);
        $repairs = $model->getRepairHistory((int) $customer['customer_id']);
        $csrfToken = csrf_token();
        $title = 'Chi tiết khách hàng - DriveHub';
        $view = 'admin/customer-detail';
        require_once PATH_VIEW_MAIN;
    }

    private function redirectForStatus(int $status): void
    {
        header('Location: ' . BASE_URL . '?action=admin-customers&notice=' . ($status === 1 ? 'unlocked' : 'locked'));
        exit;
    }
}
