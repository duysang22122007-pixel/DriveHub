<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Models\Appointment;
use App\Models\GarageSchedule;
use App\Models\Invoice;
use App\Models\Review;
use App\Models\Service;
use App\Models\User;
use App\Models\Vehicle;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;

final class AppointmentController extends Controller
{
    private const BOOKING_TIMEZONE = 'Asia/Ho_Chi_Minh';
    private const MAX_SERVICES = 20;

    /**
     * @param array<string, string|int> $databaseConfig
     * @param array<string, string|null> $garage
     */
    public function __construct(
        private readonly array $databaseConfig,
        private readonly array $garage
    ) {
    }

    public function create(Request $request): Response
    {
        $serviceId = filter_var($request->input('service_id'), FILTER_VALIDATE_INT);
        $old = [];

        if ($serviceId !== false && $serviceId > 0) {
            $old['service_ids'] = [$serviceId];
        }

        return $this->form([], $old);
    }

    public function availability(Request $request): Response
    {
        $dateInput = $request->input('date');
        if (!is_string($dateInput) || trim($dateInput) === '') {
            return Response::json(
                json_encode(['error' => 'Vui lòng chọn ngày cần kiểm tra.'], JSON_THROW_ON_ERROR),
                422
            );
        }

        $schedule = new GarageSchedule(Database::connection($this->databaseConfig));

        return Response::json(json_encode(
            $schedule->availability(trim($dateInput)),
            JSON_THROW_ON_ERROR
        ));
    }

    public function store(Request $request): Response
    {
        $input = $request->body();
        $vehicleId = filter_var($input['vehicle_id'] ?? null, FILTER_VALIDATE_INT);
        $date = is_string($input['appointment_date'] ?? null) ? trim($input['appointment_date']) : '';
        $time = is_string($input['appointment_time'] ?? null) ? trim($input['appointment_time']) : '';
        $additionalNote = is_string($input['additional_note'] ?? null) ? trim($input['additional_note']) : '';
        $serviceIds = $this->serviceIds($input['service_ids'] ?? []);
        $appointmentCode = is_string($input['appointment_code'] ?? null)
            ? $input['appointment_code']
            : '';
        $errors = [];

        if ($vehicleId === false || $vehicleId < 1) {
            $errors['vehicle_id'] = 'Vui lòng chọn xe thuộc tài khoản của bạn.';
        }
        if ($date === '') {
            $errors['appointment_date'] = 'Vui lòng chọn ngày mong muốn.';
        }
        if ($time === '') {
            $errors['appointment_time'] = 'Vui lòng chọn giờ mong muốn.';
        }
        if (
            $date !== ''
            && $time !== ''
            && !$this->isFutureDateTime($date, $time)
        ) {
            $errors['appointment_date'] = 'Ngày không hợp lệ hoặc đã qua; vui lòng kiểm tra lại ngày và giờ.';
            $errors['appointment_time'] = 'Ngày không hợp lệ hoặc đã qua; vui lòng kiểm tra lại ngày và giờ.';
        }
        if ($additionalNote !== '' && !Validator::stringLength($additionalNote, 1, 2000)) {
            $errors['additional_note'] = 'Ghi chú không được vượt quá 2.000 ký tự.';
        }
        if ($serviceIds === null) {
            $errors['service_ids'] = 'Danh sách dịch vụ không hợp lệ.';
            $serviceIds = [];
        } elseif ($serviceIds === []) {
            $errors['service_ids'] = 'Vui lòng chọn ít nhất một dịch vụ.';
        }
        if (!preg_match('/\AAC[0-9A-F]{24}\z/', $appointmentCode)) {
            $errors['appointment_code'] = 'Phiên đặt lịch đã hết hạn. Vui lòng kiểm tra lại thông tin và thử lại.';
        }

        $old = [
            'vehicle_id' => is_string($input['vehicle_id'] ?? null) ? $input['vehicle_id'] : '',
            'service_ids' => $serviceIds,
            'appointment_date' => $date,
            'appointment_time' => $time,
            'additional_note' => $additionalNote,
            'appointment_code' => $appointmentCode,
        ];
        if ($errors !== []) {
            return $this->form($errors, $old, 422);
        }

        $customerId = (int) $_SESSION['user_id'];
        $pdo = Database::connection($this->databaseConfig);
        if ((new Vehicle($pdo))->findForCustomer((int) $vehicleId, $customerId) === null) {
            return $this->form(
                ['vehicle_id' => 'Xe không thuộc tài khoản của bạn hoặc không còn khả dụng.'],
                $old,
                422
            );
        }

        try {
            $appointmentId = (new Appointment($pdo))->createForCustomer(
                $customerId,
                (int) $vehicleId,
                $appointmentCode,
                $date,
                $time,
                $additionalNote === '' ? null : $additionalNote,
                $serviceIds
            );
        } catch (InvalidArgumentException $exception) {
            $message = $exception->getMessage();
            $field = str_starts_with($message, 'Xe ')
                ? 'vehicle_id'
                : (str_contains($message, 'dịch vụ') || str_starts_with($message, 'Danh sách')
                    ? 'service_ids'
                    : (str_starts_with($message, 'Gara ') || str_contains($message, 'lịch trong ngày')
                        ? 'appointment_date'
                        : 'appointment_time'));

            return $this->form([$field => $message], $old, 422);
        }

        flash('success', 'Yêu cầu đặt lịch đã được gửi và đang chờ gara xác nhận.');

        return Response::redirect(url('appointments/' . $appointmentId));
    }

    public function history(Request $request): Response
    {
        $statusInput = $request->input('status', '');
        $status = is_string($statusInput) ? $statusInput : '';
        $filterStatuses = $this->filterStatuses();
        $errors = [];
        if (!is_string($statusInput) || ($status !== '' && !array_key_exists($status, $filterStatuses))) {
            $errors[] = 'Bộ lọc trạng thái không hợp lệ.';
            $status = '';
        }

        $pdo = Database::connection($this->databaseConfig);
        $customerId = (int) $_SESSION['user_id'];
        $appointments = (new Appointment($pdo))
            ->historyForCustomer($customerId, $status === '' ? null : $status);
        $pendingReviews = (new Review($pdo))->pendingForCustomer($customerId);

        return $this->view('customer/appointments/index', [
            'appointments' => $appointments,
            'pendingReviews' => $pendingReviews,
            'appointmentTypeLabels' => $this->appointmentTypeLabels(),
            'statuses' => $this->statuses(),
            'filterStatuses' => $filterStatuses,
            'maintenanceStatuses' => $this->maintenanceStatuses(),
            'statusFilter' => $status,
            'errors' => $errors,
        ]);
    }

    /**
     * @param array<string, string> $parameters
     */
    public function show(Request $request, array $parameters): Response
    {
        $appointmentId = filter_var($parameters['id'] ?? null, FILTER_VALIDATE_INT);
        if ($appointmentId === false || $appointmentId < 1) {
            return Response::notFound();
        }

        $customerId = (int) $_SESSION['user_id'];
        $pdo = Database::connection($this->databaseConfig);
        $catalog = new Appointment($pdo);
        $appointment = $catalog->findForCustomer($appointmentId, $customerId);
        if ($appointment === null) {
            return Response::notFound();
        }

        $user = (new User($pdo))->findById($customerId);
        $invoiceModel = new Invoice($pdo);
        $invoice = $invoiceModel->findByAppointmentId($appointmentId);
        $invoiceDetails = $invoice !== null ? $invoiceModel->getDetails((int) $invoice['id']) : [];
        $payments = $invoice !== null ? $invoiceModel->getPayments((int) $invoice['id']) : [];
        $review = (new Review($pdo))->findByAppointmentForCustomer($appointmentId, $customerId);
        $reviewRequestSentAt = isset($appointment['maintenance_ticket_id']) && $appointment['maintenance_ticket_id'] !== null
            ? $invoiceModel->reviewRequestSentAt((int) $appointment['maintenance_ticket_id'])
            : null;

        return $this->view('customer/appointments/show', [
            'appointment' => $appointment,
            'services' => $catalog->details($appointmentId),
            'customer' => $user,
            'garage' => $this->garage,
            'invoice' => $invoice,
            'invoiceDetails' => $invoiceDetails,
            'payments' => $payments,
            'invoiceStatuses' => Invoice::STATUS_LABELS,
            'paymentMethods' => Invoice::PAYMENT_METHOD_LABELS,
            'review' => $review,
            'reviewRequestSentAt' => $reviewRequestSentAt,
            'appointmentTypeLabels' => $this->appointmentTypeLabels(),
            'statuses' => $this->statuses(),
            'maintenanceStatuses' => $this->maintenanceStatuses(),
            'success' => flash('success'),
            'error' => flash('error'),
        ]);
    }

    /**
     * @param array<string, string> $parameters
     */
    public function cancel(Request $request, array $parameters): Response
    {
        $appointmentId = filter_var($parameters['id'] ?? null, FILTER_VALIDATE_INT);
        if ($appointmentId === false || $appointmentId < 1) {
            return Response::notFound();
        }

        $customerId = (int) $_SESSION['user_id'];
        $input      = $request->body();
        $reason     = is_string($input['cancellation_reason'] ?? null) ? trim($input['cancellation_reason']) : null;
        if ($reason === '') {
            $reason = null;
        }
        $modeInput = $input['cancel_mode'] ?? 'request';
        $mode = is_string($modeInput) && in_array($modeInput, ['request', 'immediate'], true)
            ? $modeInput
            : 'request';

        try {
            $cancelled = (new Appointment(Database::connection($this->databaseConfig)))
                ->requestCancellationByCustomer($appointmentId, $customerId, $reason, $mode);
        } catch (\InvalidArgumentException $exception) {
            flash('error', $exception->getMessage());

            return Response::redirect(url('appointments/' . $appointmentId));
        } catch (\PDOException $exception) {
            error_log('Appointment cancellation request failed: ' . $exception->getMessage());
            flash('error', 'Không thể cập nhật trạng thái hủy lịch do lỗi cơ sở dữ liệu. Vui lòng thử lại.');

            return Response::redirect(url('appointments/' . $appointmentId));
        }

        if (!$cancelled) {
            return Response::notFound();
        }

        flash(
            'success',
            $mode === 'immediate'
                ? 'Lịch hẹn đã được hủy thành công và đồng bộ trạng thái Đã hủy.'
                : 'Đã cập nhật trạng thái Muốn hủy lịch và gửi thông báo tới Admin.'
        );

        return Response::redirect(url('appointments/' . $appointmentId));
    }

    /**
     * Khách hàng rút lại trạng thái Muốn hủy lịch.
     *
     * @param array<string, string> $parameters
     */
    public function withdrawCancel(Request $request, array $parameters): Response
    {
        $appointmentId = filter_var($parameters['id'] ?? null, FILTER_VALIDATE_INT);
        if ($appointmentId === false || $appointmentId < 1) {
            return Response::notFound();
        }

        $customerId = (int) $_SESSION['user_id'];
        try {
            $withdrawn = (new Appointment(Database::connection($this->databaseConfig)))
                ->withdrawCancellationByCustomer($appointmentId, $customerId);
        } catch (\InvalidArgumentException $exception) {
            flash('error', $exception->getMessage());

            return Response::redirect(url('appointments/' . $appointmentId));
        }

        if (!$withdrawn) {
            return Response::notFound();
        }

        flash('success', 'Đã giữ lại lịch hẹn và hủy bỏ yêu cầu muốn hủy.');

        return Response::redirect(url('appointments/' . $appointmentId));
    }

    /**
     * Khách hàng gửi biểu mẫu đánh giá dịch vụ sau thanh toán.
     *
     * @param array<string, string> $parameters
     */
    public function submitReview(Request $request, array $parameters): Response
    {
        $appointmentId = filter_var($parameters['id'] ?? null, FILTER_VALIDATE_INT);
        if ($appointmentId === false || $appointmentId < 1) {
            return Response::notFound();
        }

        $rating = filter_var($request->input('rating'), FILTER_VALIDATE_INT);
        $comment = is_string($request->input('comment')) ? $request->input('comment') : null;
        if ($rating === false || $rating < 1 || $rating > 5) {
            flash('error', 'Vui lòng chọn số sao đánh giá từ 1 đến 5.');

            return Response::redirect(url('appointments/' . $appointmentId . '#review-form'));
        }

        $customerId = (int) $_SESSION['user_id'];
        try {
            (new Review(Database::connection($this->databaseConfig)))->submitForCustomer(
                (int) $appointmentId,
                $customerId,
                (int) $rating,
                $comment
            );
            flash('success', 'Cảm ơn bạn! Đánh giá dịch vụ của bạn đã được ghi nhận.');
        } catch (\InvalidArgumentException $exception) {
            flash('error', $exception->getMessage());
        } catch (\PDOException $exception) {
            error_log('Customer review submission failed: ' . $exception->getMessage());
            flash('error', 'Không thể gửi đánh giá lúc này. Vui lòng thử lại.');
        }

        return Response::redirect(url('appointments/' . $appointmentId . '#review-form'));
    }

    /**
     * @param array<string, string> $errors
     * @param array<string, mixed> $old
     */
    private function form(array $errors = [], array $old = [], int $status = 200): Response
    {
        $pdo = Database::connection($this->databaseConfig);
        $customerId = (int) $_SESSION['user_id'];
        $customer = (new User($pdo))->findById($customerId);
        if ($customer === null || $customer['role'] !== 'customer') {
            return Response::redirect(url('login'));
        }

        if (!isset($old['appointment_code']) || !is_string($old['appointment_code'])
            || !preg_match('/\AAC[0-9A-F]{24}\z/', $old['appointment_code'])) {
            $old['appointment_code'] = $this->newAppointmentCode();
        }

        $activeStep = 1;
        foreach ([
            'vehicle_id' => 1,
            'service_ids' => 2,
            'appointment_date' => 3,
            'appointment_time' => 4,
            'additional_note' => 5,
            'appointment_code' => 6,
            'form' => 4,
        ] as $field => $step) {
            if (isset($errors[$field])) {
                $activeStep = $step;
                break;
            }
        }

        $now = new DateTimeImmutable('now', new DateTimeZone(self::BOOKING_TIMEZONE));

        return $this->view('customer/appointments/create', [
            'customer' => $customer,
            'garage' => $this->garage,
            'vehicles' => (new Vehicle($pdo))->forCustomer($customerId),
            'services' => (new Service($pdo))->listActive(48),
            'errors' => $errors,
            'old' => $old,
            'activeStep' => $activeStep,
            'today' => $now->format('Y-m-d'),
            'success' => null,
        ], $status);
    }

    /**
     * @return list<int>|null
     */
    private function serviceIds(mixed $value): ?array
    {
        if (!is_array($value) || count($value) > self::MAX_SERVICES) {
            return null;
        }

        $ids = [];
        foreach ($value as $item) {
            $id = filter_var($item, FILTER_VALIDATE_INT);
            if ($id === false || $id < 1) {
                return null;
            }
            $ids[] = $id;
        }

        return array_values(array_unique($ids));
    }

    private function isFutureDateTime(string $date, string $time): bool
    {
        if (
            !preg_match('/\A\d{4}-\d{2}-\d{2}\z/', $date)
            || !preg_match('/\A(?:[01]\d|2[0-3]):[0-5]\d\z/', $time)
        ) {
            return false;
        }

        $timezone = new DateTimeZone(self::BOOKING_TIMEZONE);
        $requested = DateTimeImmutable::createFromFormat('!Y-m-d H:i', $date . ' ' . $time, $timezone);
        $errors = DateTimeImmutable::getLastErrors();

        return $requested !== false
            && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))
            && $requested->format('Y-m-d H:i') === $date . ' ' . $time
            && $requested > new DateTimeImmutable('now', $timezone);
    }

    private function newAppointmentCode(): string
    {
        return 'AC' . strtoupper(bin2hex(random_bytes(12)));
    }

    /**
     * @return array<string, string>
     */
    private function appointmentTypeLabels(): array
    {
        return [
            'onsite' => 'Đặt lịch trực tiếp tại gara',
            'online' => 'Tư vấn hoặc hỗ trợ trực tuyến',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function statuses(): array
    {
        return [
            'pending' => 'Chờ gara xác nhận',
            'confirmed' => 'Đã xác nhận',
            'rejected' => 'Không thể tiếp nhận',
            'checked_in' => 'Đã tiếp nhận',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function filterStatuses(): array
    {
        return [
            'pending' => 'Chờ gara xác nhận',
            'confirmed' => 'Đã xác nhận',
            'cancellation_requested' => 'Muốn hủy lịch',
            'checked_in' => 'Đã tiếp nhận',
            'completed' => 'Hoàn thành',
            'rejected' => 'Không thể tiếp nhận',
            'cancelled' => 'Đã hủy',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function maintenanceStatuses(): array
    {
        return [
            'received' => 'Đã tiếp nhận xe',
            'diagnosing' => 'Đang chẩn đoán',
            'in_progress' => 'Đang thực hiện',
            'waiting_parts' => 'Chờ phụ tùng',
            'quality_check' => 'Đang kiểm tra chất lượng',
            'completed' => 'Phiếu sửa chữa hoàn tất',
            'cancelled' => 'Phiếu sửa chữa đã hủy',
        ];
    }
}
