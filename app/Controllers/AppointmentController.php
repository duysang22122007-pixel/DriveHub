<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Models\Appointment;
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

    public function store(Request $request): Response
    {
        $input = $request->body();
        $bookingType = is_string($input['booking_type'] ?? null) ? $input['booking_type'] : '';
        $vehicleId = filter_var($input['vehicle_id'] ?? null, FILTER_VALIDATE_INT);
        $date = is_string($input['appointment_date'] ?? null) ? trim($input['appointment_date']) : '';
        $time = is_string($input['appointment_time'] ?? null) ? trim($input['appointment_time']) : '';
        $condition = is_string($input['condition'] ?? null) ? trim($input['condition']) : '';
        $additionalNote = is_string($input['additional_note'] ?? null) ? trim($input['additional_note']) : '';
        $serviceIds = $this->serviceIds($input['service_ids'] ?? []);
        $appointmentCode = is_string($input['appointment_code'] ?? null)
            ? $input['appointment_code']
            : '';
        $errors = [];

        if (!Validator::oneOf($bookingType, ['onsite', 'online'])) {
            $errors['booking_type'] = 'Vui lòng chọn hình thức đặt lịch.';
        }
        if ($vehicleId === false || $vehicleId < 1) {
            $errors['vehicle_id'] = 'Vui lòng chọn xe thuộc tài khoản của bạn.';
        }
        if ($date === '') {
            $errors['appointment_date'] = 'Vui lòng chọn ngày mong muốn.';
        }
        if ($time === '') {
            $errors['appointment_time'] = 'Vui lòng chọn giờ mong muốn.';
        }
        if ($date !== '' && $time !== '' && !$this->isFutureDateTime($date, $time)) {
            $errors['appointment_date'] = 'Ngày hoặc giờ không hợp lệ hoặc đã qua.';
            $errors['appointment_time'] = 'Ngày hoặc giờ không hợp lệ hoặc đã qua.';
        }
        if ($condition !== '' && !Validator::stringLength($condition, 1, 2000)) {
            $errors['condition'] = 'Mô tả tình trạng xe không được vượt quá 2.000 ký tự.';
        }
        if ($additionalNote !== '' && !Validator::stringLength($additionalNote, 1, 2000)) {
            $errors['additional_note'] = 'Ghi chú không được vượt quá 2.000 ký tự.';
        }
        if ($serviceIds === null) {
            $errors['service_ids'] = 'Danh sách dịch vụ không hợp lệ.';
            $serviceIds = [];
        }
        if (!preg_match('/\AAC[0-9A-F]{24}\z/', $appointmentCode)) {
            $errors['form'] = 'Phiên đặt lịch đã hết hạn. Vui lòng kiểm tra lại thông tin và thử lại.';
        }

        $old = [
            'booking_type' => $bookingType,
            'vehicle_id' => is_string($input['vehicle_id'] ?? null) ? $input['vehicle_id'] : '',
            'service_ids' => $serviceIds,
            'appointment_date' => $date,
            'appointment_time' => $time,
            'condition' => $condition,
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

        $noteParts = [];
        if ($condition !== '') {
            $noteParts[] = 'Tình trạng xe: ' . $condition;
        }
        if ($additionalNote !== '') {
            $noteParts[] = 'Ghi chú thêm: ' . $additionalNote;
        }
        $note = $noteParts === [] ? null : implode("\n\n", $noteParts);

        try {
            $appointmentId = (new Appointment($pdo))->createForCustomer(
                $customerId,
                (int) $vehicleId,
                $bookingType,
                $appointmentCode,
                $date,
                $time,
                $note,
                $serviceIds
            );
        } catch (InvalidArgumentException $exception) {
            return $this->form(['form' => $exception->getMessage()], $old, 422);
        }

        flash('success', 'Yêu cầu đặt lịch đã được gửi và đang chờ gara xác nhận.');

        return Response::redirect(url('appointments/' . $appointmentId));
    }

    public function history(): Response
    {
        $appointments = (new Appointment(Database::connection($this->databaseConfig)))
            ->historyForCustomer((int) $_SESSION['user_id']);

        return $this->view('customer/appointments/index', [
            'appointments' => $appointments,
            'bookingTypes' => $this->bookingTypes(),
            'statuses' => $this->statuses(),
            'maintenanceStatuses' => $this->maintenanceStatuses(),
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
        $catalog = new Appointment(Database::connection($this->databaseConfig));
        $appointment = $catalog->findForCustomer($appointmentId, $customerId);
        if ($appointment === null) {
            return Response::notFound();
        }

        $user = (new User(Database::connection($this->databaseConfig)))->findById($customerId);

        return $this->view('customer/appointments/show', [
            'appointment' => $appointment,
            'services' => $catalog->details($appointmentId),
            'customer' => $user,
            'garage' => $this->garage,
            'bookingTypes' => $this->bookingTypes(),
            'statuses' => $this->statuses(),
            'maintenanceStatuses' => $this->maintenanceStatuses(),
            'success' => flash('success'),
        ]);
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
            'booking_type' => 1,
            'vehicle_id' => 2,
            'service_ids' => 3,
            'appointment_date' => 4,
            'appointment_time' => 4,
            'condition' => 5,
            'additional_note' => 5,
            'form' => 1,
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
    private function bookingTypes(): array
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
