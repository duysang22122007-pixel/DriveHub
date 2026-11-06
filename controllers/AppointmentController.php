<?php

class AppointmentController
{
    public function index()
    {
        $user = require_role('customer');
        $customerId = (int) $user['customer_id'];
        $errors = [];
        $flash = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf_token((string) ($_POST['csrf_token'] ?? ''))) {
                $errors[] = 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang và thử lại.';
            } elseif (($_POST['operation'] ?? '') === 'cancel') {
                $appointmentId = filter_var($_POST['appointment_id'] ?? null, FILTER_VALIDATE_INT);
                $appointmentModel = new AppointmentModel();
                $appointment = $appointmentId ? $appointmentModel->getOwnedById((int) $appointmentId, $customerId) : null;

                if (!$appointment || !in_array($appointment['status'], ['pending', 'confirmed'], true)) {
                    $errors[] = 'Lịch hẹn không tồn tại hoặc không thể hủy ở trạng thái hiện tại.';
                } else {
                    $appointmentModel->updateStatus((int) $appointment['id'], 'cancelled', (int) $user['user_id']);
                    header('Location: ' . BASE_URL . '?action=my-appointments&notice=cancelled');
                    exit;
                }
            }
        }

        $notice = (string) ($_GET['notice'] ?? '');
        if ($notice === 'cancelled') {
            $flash = 'Đã hủy lịch hẹn.';
        }

        $upcoming = (new AppointmentModel())->getUpcomingAppointments($customerId);
        $history = (new AppointmentModel())->getHistoryAppointments($customerId);
        $csrfToken = csrf_token();
        $title = 'Lịch hẹn của tôi - DriveHub';
        $view = 'appointments/customer';
        require_once PATH_VIEW_MAIN;
    }

    public function history()
    {
        $user = require_role('customer');
        $appointments = (new AppointmentModel())->getTimelineByCustomerId((int) $user['customer_id']);
        $statusLabels = [
            'pending' => 'Đã gửi yêu cầu',
            'confirmed' => 'Lịch hẹn được xác nhận',
            'checked_in' => 'Xe đã được tiếp nhận',
            'completed' => 'Sửa chữa đã hoàn tất',
            'cancelled' => 'Lịch hẹn đã hủy',
        ];
        $title = 'Lịch sử đặt lịch - DriveHub';
        $view = 'appointments/history';
        require_once PATH_VIEW_MAIN;
    }
}
