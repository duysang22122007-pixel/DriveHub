<?php

class AdminAppointmentController
{
    public function index()
    {
        $user = require_role('admin');
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf_token((string) ($_POST['csrf_token'] ?? ''))) {
                $errors[] = 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang và thử lại.';
            } else {
                $appointmentId = filter_var($_POST['appointment_id'] ?? null, FILTER_VALIDATE_INT);
                $appointmentModel = new AppointmentModel();
                $appointment = $appointmentId ? $appointmentModel->getById((int) $appointmentId) : null;

                if (!$appointment || $appointment['status'] !== 'pending') {
                    $errors[] = 'Chỉ có thể xác nhận lịch hẹn đang chờ.';
                } else {
                    $appointmentModel->updateStatus((int) $appointment['id'], 'confirmed', (int) $user['user_id']);
                    header('Location: ' . BASE_URL . '?action=admin-appointments');
                    exit;
                }
            }
        }

        $appointments = (new AppointmentModel())->getForAdmin();
        $csrfToken = csrf_token();
        $title = 'Quản lý lịch hẹn - DriveHub';
        $view = 'appointments/admin';
        require_once PATH_VIEW_MAIN;
    }
}
