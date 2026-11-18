<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\Appointment;
use App\Models\MaintenanceTicket;
use App\Models\Service;
use DateTimeImmutable;
use InvalidArgumentException;
use PDOException;
use Throwable;

final class AdminTicketController extends Controller
{
    private const PAGE_SIZE = 20;

    /**
     * @param array<string, string|int> $databaseConfig
     */
    public function __construct(private readonly array $databaseConfig)
    {
    }

    /**
     * Danh sách phiếu sửa chữa
     */
    public function index(Request $request): Response
    {
        $keyword = trim((string) $request->input('q', ''));
        $status = (string) $request->input('status', '');
        $date = trim((string) $request->input('date', ''));

        if ($status !== '' && !array_key_exists($status, MaintenanceTicket::STATUS_LABELS)) {
            $status = '';
        }

        $pageInput = filter_var($request->input('page', 1), FILTER_VALIDATE_INT);
        $page = $pageInput === false || $pageInput < 1 ? 1 : $pageInput;

        $model = new MaintenanceTicket(Database::connection($this->databaseConfig));
        $pageData = $model->adminPage(
            $keyword,
            $status === '' ? null : $status,
            $date === '' ? null : $date,
            $page,
            self::PAGE_SIZE
        );

        $pageCount = max(1, (int) ceil($pageData['total'] / self::PAGE_SIZE));

        return $this->view('admin/tickets/index', [
            'tickets' => $pageData['items'],
            'total' => $pageData['total'],
            'page' => $page,
            'pageCount' => $pageCount,
            'q' => $keyword,
            'statusFilter' => $status,
            'dateFilter' => $date,
            'statuses' => MaintenanceTicket::STATUS_LABELS,
            'success' => flash('success'),
            'error' => flash('error'),
        ]);
    }

    /**
     * Form tạo phiếu sửa chữa
     */
    public function create(Request $request): Response
    {
        $model = new MaintenanceTicket(Database::connection($this->databaseConfig));
        $availableAppointments = $model->listAppointmentsReadyForTicket();

        $selectedAppointmentId = filter_var($request->input('appointment_id'), FILTER_VALIDATE_INT);
        $selectedAppointment = null;

        if ($selectedAppointmentId !== false && $selectedAppointmentId > 0) {
            $appointmentModel = new Appointment(Database::connection($this->databaseConfig));
            $detail = $appointmentModel->findAdminDetail($selectedAppointmentId);
            if ($detail && ($detail['status'] === 'confirmed' || $detail['status'] === 'checked_in')) {
                $selectedAppointment = $detail;
            }
        }

        return $this->view('admin/tickets/create', [
            'availableAppointments' => $availableAppointments,
            'selectedAppointment' => $selectedAppointment,
            'selectedId' => $selectedAppointmentId ?: null,
            'success' => flash('success'),
            'error' => flash('error'),
        ]);
    }

    /**
     * Xử lý tạo phiếu sửa chữa
     */
    public function store(Request $request): Response
    {
        $appointmentId = filter_var($request->input('appointment_id'), FILTER_VALIDATE_INT);
        $mileage = filter_var($request->input('mileage', 0), FILTER_VALIDATE_INT);
        $initialCondition = (string) $request->input('initial_condition', '');
        $diagnosis = (string) $request->input('diagnosis', '');
        $adminId = (int) ($_SESSION['user_id'] ?? 0);

        if ($appointmentId === false || $appointmentId < 1) {
            flash('error', 'Vui lòng chọn lịch hẹn hợp lệ để tạo phiếu sửa chữa.');

            return Response::redirect(url('admin/tickets/create'));
        }

        try {
            $model = new MaintenanceTicket(Database::connection($this->databaseConfig));
            $ticketId = $model->createTicket([
                'appointment_id' => $appointmentId,
                'admin_id' => $adminId,
                'mileage' => max(0, (int) $mileage),
                'initial_condition' => $initialCondition,
                'diagnosis' => $diagnosis,
            ]);

            flash('success', 'Đã khởi tạo phiếu sửa chữa thành công!');

            return Response::redirect(url('admin/tickets/' . $ticketId));
        } catch (Throwable $e) {
            flash('error', 'Lỗi tạo phiếu sửa chữa: ' . $e->getMessage());

            return Response::redirect(url('admin/tickets/create?appointment_id=' . $appointmentId));
        }
    }

    /**
     * Chi tiết phiếu sửa chữa
     *
     * @param array<string, string> $parameters
     */
    public function show(Request $request, array $parameters): Response
    {
        $id = filter_var($parameters['id'] ?? null, FILTER_VALIDATE_INT);
        if ($id === false || $id < 1) {
            return Response::notFound();
        }

        $model = new MaintenanceTicket(Database::connection($this->databaseConfig));
        $ticket = $model->findById($id);
        if (!$ticket) {
            return Response::notFound();
        }

        $items = $model->getItems($id);
        $histories = $model->getHistories($id);

        $serviceModel = new Service(Database::connection($this->databaseConfig));
        $services = $serviceModel->listActive(48);

        $totalCost = array_sum(array_map(
            static fn (array $i): float => (float) ($i['line_total'] ?? 0),
            $items
        ));

        return $this->view('admin/tickets/show', [
            'ticket' => $ticket,
            'items' => $items,
            'histories' => $histories,
            'services' => $services,
            'totalCost' => $totalCost,
            'statuses' => MaintenanceTicket::STATUS_LABELS,
            'itemStatuses' => MaintenanceTicket::ITEM_STATUS_LABELS,
            'success' => flash('success'),
            'error' => flash('error'),
        ]);
    }

    /**
     * Cập nhật thông tin chẩn đoán lỗi & số km tiếp nhận
     *
     * @param array<string, string> $parameters
     */
    public function updateDiagnosis(Request $request, array $parameters): Response
    {
        $id = filter_var($parameters['id'] ?? null, FILTER_VALIDATE_INT);
        if ($id === false || $id < 1) {
            return Response::notFound();
        }

        $mileage = filter_var($request->input('mileage', 0), FILTER_VALIDATE_INT);
        $initialCondition = (string) $request->input('initial_condition', '');
        $diagnosis = (string) $request->input('diagnosis', '');
        $result = (string) $request->input('result', '');

        try {
            $model = new MaintenanceTicket(Database::connection($this->databaseConfig));
            $model->updateDiagnosisAndMileage(
                $id,
                max(0, (int) $mileage),
                $initialCondition,
                $diagnosis,
                $result
            );

            flash('success', 'Đã cập nhật thông tin chẩn đoán và số km tiếp nhận.');
        } catch (Throwable $e) {
            flash('error', 'Lỗi cập nhật chẩn đoán: ' . $e->getMessage());
        }

        return Response::redirect(url('admin/tickets/' . $id));
    }

    /**
     * Cập nhật trạng thái sửa chữa & số km khi hoàn thành
     *
     * @param array<string, string> $parameters
     */
    public function updateStatus(Request $request, array $parameters): Response
    {
        $id = filter_var($parameters['id'] ?? null, FILTER_VALIDATE_INT);
        if ($id === false || $id < 1) {
            return Response::notFound();
        }

        $newStatus = (string) $request->input('status', '');
        $description = (string) $request->input('description', '');
        $completedMileage = filter_var($request->input('completed_mileage'), FILTER_VALIDATE_INT);
        $adminId = (int) ($_SESSION['user_id'] ?? 0);

        try {
            $model = new MaintenanceTicket(Database::connection($this->databaseConfig));
            $model->updateStatus(
                $id,
                $adminId,
                $newStatus,
                $description,
                $completedMileage !== false && $completedMileage > 0 ? (int) $completedMileage : null
            );

            flash('success', 'Đã cập nhật trạng thái tiến độ sửa chữa.');
        } catch (Throwable $e) {
            flash('error', 'Lỗi cập nhật trạng thái: ' . $e->getMessage());
        }

        return Response::redirect(url('admin/tickets/' . $id));
    }

    /**
     * Thêm hạng mục sửa chữa
     *
     * @param array<string, string> $parameters
     */
    public function addItem(Request $request, array $parameters): Response
    {
        $id = filter_var($parameters['id'] ?? null, FILTER_VALIDATE_INT);
        if ($id === false || $id < 1) {
            return Response::notFound();
        }

        $serviceId = filter_var($request->input('service_id'), FILTER_VALIDATE_INT);
        $itemName = trim((string) $request->input('item_name', ''));
        $description = trim((string) $request->input('description', ''));
        $quantity = filter_var($request->input('quantity', 1), FILTER_VALIDATE_INT);
        $unitPrice = filter_var($request->input('unit_price', 0), FILTER_VALIDATE_FLOAT);
        $note = trim((string) $request->input('note', ''));

        if ($itemName === '') {
            flash('error', 'Tên hạng mục không được để trống.');

            return Response::redirect(url('admin/tickets/' . $id));
        }

        try {
            $model = new MaintenanceTicket(Database::connection($this->databaseConfig));
            $model->addItem(
                $id,
                $serviceId !== false && $serviceId > 0 ? (int) $serviceId : null,
                $itemName,
                $description !== '' ? $description : null,
                max(1, (int) $quantity),
                max(0.0, (float) $unitPrice),
                $note !== '' ? $note : null
            );

            flash('success', 'Đã thêm hạng mục sửa chữa thành công.');
        } catch (Throwable $e) {
            flash('error', 'Lỗi thêm hạng mục: ' . $e->getMessage());
        }

        return Response::redirect(url('admin/tickets/' . $id));
    }

    /**
     * Cập nhật hạng mục sửa chữa
     *
     * @param array<string, string> $parameters
     */
    public function updateItem(Request $request, array $parameters): Response
    {
        $ticketId = filter_var($parameters['id'] ?? null, FILTER_VALIDATE_INT);
        $itemId = filter_var($parameters['itemId'] ?? null, FILTER_VALIDATE_INT);
        if ($ticketId === false || $ticketId < 1 || $itemId === false || $itemId < 1) {
            return Response::notFound();
        }

        $itemName = trim((string) $request->input('item_name', ''));
        $description = trim((string) $request->input('description', ''));
        $quantity = filter_var($request->input('quantity', 1), FILTER_VALIDATE_INT);
        $unitPrice = filter_var($request->input('unit_price', 0), FILTER_VALIDATE_FLOAT);
        $status = (string) $request->input('status', 'pending');
        $note = trim((string) $request->input('note', ''));

        if ($itemName === '') {
            flash('error', 'Tên hạng mục không được để trống.');

            return Response::redirect(url('admin/tickets/' . $ticketId));
        }

        try {
            $model = new MaintenanceTicket(Database::connection($this->databaseConfig));
            $model->updateItem(
                (int) $itemId,
                (int) $ticketId,
                $itemName,
                $description !== '' ? $description : null,
                max(1, (int) $quantity),
                max(0.0, (float) $unitPrice),
                $status,
                $note !== '' ? $note : null
            );

            flash('success', 'Đã cập nhật hạng mục sửa chữa.');
        } catch (Throwable $e) {
            flash('error', 'Lỗi cập nhật hạng mục: ' . $e->getMessage());
        }

        return Response::redirect(url('admin/tickets/' . $ticketId));
    }

    /**
     * Xóa hạng mục sửa chữa
     *
     * @param array<string, string> $parameters
     */
    public function deleteItem(Request $request, array $parameters): Response
    {
        $ticketId = filter_var($parameters['id'] ?? null, FILTER_VALIDATE_INT);
        $itemId = filter_var($parameters['itemId'] ?? null, FILTER_VALIDATE_INT);
        if ($ticketId === false || $ticketId < 1 || $itemId === false || $itemId < 1) {
            return Response::notFound();
        }

        try {
            $model = new MaintenanceTicket(Database::connection($this->databaseConfig));
            $model->deleteItem((int) $itemId, (int) $ticketId);
            flash('success', 'Đã xóa hạng mục khỏi phiếu sửa chữa.');
        } catch (Throwable $e) {
            flash('error', 'Lỗi xóa hạng mục: ' . $e->getMessage());
        }

        return Response::redirect(url('admin/tickets/' . $ticketId));
    }
}
