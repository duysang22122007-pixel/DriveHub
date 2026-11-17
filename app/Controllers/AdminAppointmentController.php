<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\Appointment;
use App\Models\GarageSchedule;
use DateTimeImmutable;
use InvalidArgumentException;
use PDOException;

final class AdminAppointmentController extends Controller
{
    private const PAGE_SIZE = 25;

    /**
     * @param array<string, string|int> $databaseConfig
     */
    public function __construct(private readonly array $databaseConfig)
    {
    }

    public function index(Request $request): Response
    {
        $keywordInput = $request->input('q', '');
        $keyword = is_string($keywordInput) ? trim($keywordInput) : '';
        if (mb_strlen($keyword, 'UTF-8') > 100) {
            $keyword = mb_substr($keyword, 0, 100, 'UTF-8');
        }

        $errors = [];
        $statusInput = $request->input('status', '');
        $status = is_string($statusInput) ? $statusInput : '';
        if ($status !== '' && !array_key_exists($status, $this->statuses())) {
            $errors[] = 'Bộ lọc trạng thái không hợp lệ.';
            $status = '';
        }

        $dateInput = $request->input('date', '');
        $date = is_string($dateInput) ? trim($dateInput) : '';
        if ($date !== '' && !$this->isValidDate($date)) {
            $errors[] = 'Ngày lọc không hợp lệ.';
            $date = '';
        }

        $pageInput = filter_var($request->input('page', 1), FILTER_VALIDATE_INT);
        $page = $pageInput === false || $pageInput < 1 ? 1 : $pageInput;
        $model = new Appointment(Database::connection($this->databaseConfig));
        $pageData = $model->adminPage(
            $keyword,
            $status === '' ? null : $status,
            $date === '' ? null : $date,
            $page,
            self::PAGE_SIZE
        );
        $pageCount = max(1, (int) ceil($pageData['total'] / self::PAGE_SIZE));
        if ($page > $pageCount) {
            $page = $pageCount;
            $pageData = $model->adminPage(
                $keyword,
                $status === '' ? null : $status,
                $date === '' ? null : $date,
                $page,
                self::PAGE_SIZE
            );
        }

        return $this->view('admin/appointments/index', [
            'appointments' => $pageData['items'],
            'total' => $pageData['total'],
            'page' => $page,
            'pageCount' => $pageCount,
            'q' => $keyword,
            'statusFilter' => $status,
            'date' => $date,
            'statuses' => $this->statuses(),
            'appointmentTypeLabels' => $this->appointmentTypeLabels(),
            'maintenanceStatuses' => $this->maintenanceStatuses(),
            'errors' => $errors,
            'success' => flash('success'),
            'error' => flash('error'),
        ]);
    }

    public function schedule(Request $request): Response
    {
        return $this->scheduleView(
            new GarageSchedule(Database::connection($this->databaseConfig))
        );
    }

    public function updateSchedule(Request $request): Response
    {
        $model = new GarageSchedule(Database::connection($this->databaseConfig));
        $action = $request->input('action');

        try {
            if ($action === 'weekly') {
                $rows = $request->input('schedule');
                if (!is_array($rows)) {
                    throw new InvalidArgumentException('Dữ liệu lịch hoạt động không hợp lệ.');
                }
                $model->saveWeeklySchedule($rows);
                flash('success', 'Lịch hoạt động và sức chứa đã được cập nhật.');
            } elseif ($action === 'close') {
                $date = $request->input('closed_date');
                $reason = $request->input('reason', '');
                if (!is_string($date) || !is_string($reason)) {
                    throw new InvalidArgumentException('Thông tin ngày nghỉ không hợp lệ.');
                }
                $model->addClosure($date, trim($reason), (int) $_SESSION['user_id']);
                flash('success', 'Ngày nghỉ của gara đã được lưu.');
            } elseif ($action === 'remove') {
                $date = $request->input('closed_date');
                if (!is_string($date)) {
                    throw new InvalidArgumentException('Ngày nghỉ không hợp lệ.');
                }
                $model->removeClosure($date);
                flash('success', 'Ngày nghỉ đã được gỡ.');
            } else {
                throw new InvalidArgumentException('Thao tác cấu hình lịch không hợp lệ.');
            }
        } catch (InvalidArgumentException $exception) {
            return $this->scheduleView(
                $model,
                $exception->getMessage(),
                $action === 'weekly' && is_array($request->input('schedule'))
                    ? $request->input('schedule')
                    : null,
                422
            );
        } catch (PDOException $exception) {
            error_log('Garage schedule update failed: ' . $exception->getMessage());

            return $this->scheduleView($model, 'Không thể cập nhật lịch gara do lỗi cơ sở dữ liệu. Vui lòng thử lại.', null, 500);
        }

        return Response::redirect(url('admin/garage-schedule'));
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

        $catalog = new Appointment(Database::connection($this->databaseConfig));
        $appointment = $catalog->findForAdmin($appointmentId);
        if ($appointment === null) {
            return Response::notFound();
        }

        $transitions = [];
        foreach (Appointment::adminTransitionsFrom((string) $appointment['status']) as $nextStatus) {
            $transitions[$nextStatus] = $this->statuses()[$nextStatus];
        }

        return $this->view('admin/appointments/show', [
            'appointment' => $appointment,
            'services' => $catalog->details($appointmentId),
            'statuses' => $this->statuses(),
            'appointmentTypeLabels' => $this->appointmentTypeLabels(),
            'maintenanceStatuses' => $this->maintenanceStatuses(),
            'transitions' => $transitions,
            'success' => flash('success'),
            'error' => flash('error'),
        ]);
    }

    /**
     * @param array<string, string> $parameters
     */
    public function updateStatus(Request $request, array $parameters): Response
    {
        $appointmentId = filter_var($parameters['id'] ?? null, FILTER_VALIDATE_INT);
        if ($appointmentId === false || $appointmentId < 1) {
            flash('error', 'Mã lịch hẹn không hợp lệ.');

            return Response::redirect(url('admin/appointments'));
        }

        $status = $request->input('status');
        $reason = $request->input('reason', '');
        if (!is_string($status) || !is_string($reason)) {
            flash('error', 'Dữ liệu cập nhật lịch hẹn không hợp lệ.');

            return Response::redirect(url('admin/appointments/' . $appointmentId));
        }

        try {
            $updated = (new Appointment(Database::connection($this->databaseConfig)))
                ->updateStatusByAdmin(
                    $appointmentId,
                    $status,
                    $status === 'rejected' ? trim($reason) : null
                );
            if (!$updated) {
                flash('error', 'Không tìm thấy lịch hẹn cần cập nhật.');

                return Response::redirect(url('admin/appointments'));
            }

            flash('success', 'Trạng thái lịch hẹn đã được cập nhật.');
        } catch (InvalidArgumentException $exception) {
            flash('error', $exception->getMessage());
        } catch (PDOException $exception) {
            error_log('Appointment status update failed: ' . $exception->getMessage());
            flash('error', 'Không thể cập nhật lịch hẹn do lỗi cơ sở dữ liệu. Vui lòng thử lại.');
        }

        return Response::redirect(url('admin/appointments/' . $appointmentId));
    }

    /**
     * @return array<string, string>
     */
    private function statuses(): array
    {
        return [
            'pending' => 'Chờ xác nhận',
            'confirmed' => 'Đã xác nhận',
            'rejected' => 'Đã từ chối',
            'checked_in' => 'Đã tiếp nhận',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function appointmentTypeLabels(): array
    {
        return [
            'onsite' => 'Trực tiếp tại gara',
            'online' => 'Tư vấn trực tuyến',
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

    private function isValidDate(string $date): bool
    {
        if (!preg_match('/\A\d{4}-\d{2}-\d{2}\z/', $date)) {
            return false;
        }

        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        $errors = DateTimeImmutable::getLastErrors();

        return $parsed !== false
            && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))
            && $parsed->format('Y-m-d') === $date;
    }

    /**
     * @param array<mixed>|null $submittedSchedule
     */
    private function scheduleView(
        GarageSchedule $model,
        ?string $error = null,
        ?array $submittedSchedule = null,
        int $status = 200
    ): Response {
        $schedule = $model->weeklySchedule();
        if ($submittedSchedule !== null) {
            foreach ($schedule as &$day) {
                $weekday = (int) $day['weekday'];
                if (isset($submittedSchedule[$weekday]) && is_array($submittedSchedule[$weekday])) {
                    $day = array_merge($day, $submittedSchedule[$weekday]);
                }
            }
            unset($day);
        }

        return $this->view('admin/appointments/schedule', [
            'schedule' => $schedule,
            'closures' => $model->upcomingClosures(),
            'weekdayNames' => [
                1 => 'Thứ Hai',
                2 => 'Thứ Ba',
                3 => 'Thứ Tư',
                4 => 'Thứ Năm',
                5 => 'Thứ Sáu',
                6 => 'Thứ Bảy',
                7 => 'Chủ Nhật',
            ],
            'success' => flash('success'),
            'error' => $error ?? flash('error'),
        ], $status);
    }
}
