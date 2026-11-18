<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Services\VehicleFormValidator;
use PDOException;

final class AdminVehicleController extends Controller
{
    /**
     * @param array<string, string|int> $databaseConfig
     */
    public function __construct(private readonly array $databaseConfig)
    {
    }

    public function index(Request $request): Response
    {
        $pdo = Database::connection($this->databaseConfig);
        $customers = new Customer($pdo);
        $customerInput = $request->input('customer_id', '');
        $customerId = $customerInput === '' ? null : $this->positiveId($customerInput);
        if ($customerInput !== '' && $customerId === null) {
            return Response::notFound();
        }

        $selectedCustomer = $customerId === null ? null : $customers->findCustomer($customerId);
        if ($customerId !== null && $selectedCustomer === null) {
            return Response::notFound();
        }

        return $this->view('admin/vehicles/index', [
            'customers' => $customers->list(200),
            'selectedCustomer' => $selectedCustomer,
            'vehicles' => $customerId === null ? [] : (new Vehicle($pdo))->forCustomer($customerId),
            'success' => flash('success'),
            'error' => flash('error'),
        ]);
    }

    /**
     * @param array<string, string> $parameters
     */
    public function show(Request $request, array $parameters): Response
    {
        $customerId = $this->positiveId($parameters['customerId'] ?? null);
        $vehicleId = $this->positiveId($parameters['vehicleId'] ?? null);
        if ($customerId === null || $vehicleId === null) {
            return Response::notFound();
        }

        $pdo = Database::connection($this->databaseConfig);
        $customer = (new Customer($pdo))->findCustomer($customerId);
        $vehicle = (new Vehicle($pdo))->findForAdmin($vehicleId, $customerId);
        if ($customer === null || $vehicle === null) {
            return Response::notFound();
        }

        return $this->view('admin/vehicles/show', [
            'customer' => $customer,
            'vehicle' => $vehicle,
            'success' => flash('success'),
            'error' => flash('error'),
        ]);
    }

    /**
     * @param array<string, string> $parameters
     */
    public function create(Request $request, array $parameters): Response
    {
        $customerId = $this->positiveId($parameters['customerId'] ?? null);
        if ($customerId === null) {
            return Response::notFound();
        }

        $customer = (new Customer(Database::connection($this->databaseConfig)))->findCustomer($customerId);
        if ($customer === null) {
            return Response::notFound();
        }

        return $this->form($customer, [], false, null);
    }

    /**
     * @param array<string, string> $parameters
     */
    public function store(Request $request, array $parameters): Response
    {
        $customerId = $this->positiveId($parameters['customerId'] ?? null);
        $pdo = Database::connection($this->databaseConfig);
        $customer = $customerId === null ? null : (new Customer($pdo))->findCustomer($customerId);
        if ($customerId === null || $customer === null) {
            return Response::notFound();
        }

        $data = (new VehicleFormValidator($pdo))->validate($request);
        if (isset($data['errors'])) {
            if ($request->input('ajax') === '1') {
                return Response::json(json_encode(['success' => false, 'errors' => $data['errors']], JSON_THROW_ON_ERROR), 422);
            }
            return $this->form($customer, $data['old'], false, null, $data['errors'], 422);
        }

        try {
            (new Vehicle($pdo))->create($customerId, $data['vehicle']);
        } catch (PDOException $exception) {
            if (!$this->isDuplicateKey($exception)) {
                throw $exception;
            }

            if ($request->input('ajax') === '1') {
                return Response::json(json_encode([
                    'success' => false,
                    'errors' => ['license_plate' => 'Biển số xe đã được đăng ký trong hệ thống.'],
                ], JSON_THROW_ON_ERROR), 422);
            }

            return $this->form(
                $customer,
                $data['old'],
                false,
                null,
                ['license_plate' => 'Biển số xe đã được đăng ký trong hệ thống.'],
                422
            );
        }

        flash('success', 'Đã thêm phương tiện cho khách hàng.');

        if ($request->input('ajax') === '1') {
            return Response::json(json_encode(['success' => true], JSON_THROW_ON_ERROR));
        }

        return Response::redirect(url('admin/vehicles?customer_id=' . $customerId));
    }

    /**
     * @param array<string, string> $parameters
     */
    public function edit(Request $request, array $parameters): Response
    {
        $customerId = $this->positiveId($parameters['customerId'] ?? null);
        $vehicleId = $this->positiveId($parameters['vehicleId'] ?? null);
        if ($customerId === null || $vehicleId === null) {
            return Response::notFound();
        }

        $pdo = Database::connection($this->databaseConfig);
        $customer = (new Customer($pdo))->findCustomer($customerId);
        $vehicle = (new Vehicle($pdo))->findForAdmin($vehicleId, $customerId);
        if ($customer === null || $vehicle === null) {
            return Response::notFound();
        }

        return $this->form($customer, $vehicle, true, $vehicleId);
    }

    /**
     * @param array<string, string> $parameters
     */
    public function update(Request $request, array $parameters): Response
    {
        $customerId = $this->positiveId($parameters['customerId'] ?? null);
        $vehicleId = $this->positiveId($parameters['vehicleId'] ?? null);
        if ($customerId === null || $vehicleId === null) {
            return Response::notFound();
        }

        $pdo = Database::connection($this->databaseConfig);
        $customer = (new Customer($pdo))->findCustomer($customerId);
        $model = new Vehicle($pdo);
        $existing = $model->findForAdmin($vehicleId, $customerId);
        if ($customer === null || $existing === null) {
            return Response::notFound();
        }

        $data = (new VehicleFormValidator($pdo))->validate($request, false, true);
        if (isset($data['errors'])) {
            if ($request->input('ajax') === '1') {
                return Response::json(json_encode(['success' => false, 'errors' => $data['errors']], JSON_THROW_ON_ERROR), 422);
            }
            return $this->form($customer, $data['old'], true, $vehicleId, $data['errors'], 422);
        }

        try {
            $model->updateForAdmin($vehicleId, $customerId, $data['vehicle']);
        } catch (PDOException $exception) {
            if (!$this->isDuplicateKey($exception)) {
                throw $exception;
            }

            if ($request->input('ajax') === '1') {
                return Response::json(json_encode([
                    'success' => false,
                    'errors' => ['license_plate' => 'Biển số xe đã được đăng ký cho một xe khác.'],
                ], JSON_THROW_ON_ERROR), 422);
            }

            return $this->form(
                $customer,
                $data['old'],
                true,
                $vehicleId,
                ['license_plate' => 'Biển số xe đã được đăng ký cho một xe khác.'],
                422
            );
        }

        flash('success', 'Đã cập nhật thông tin phương tiện.');

        if ($request->input('ajax') === '1') {
            return Response::json(json_encode(['success' => true], JSON_THROW_ON_ERROR));
        }

        return Response::redirect(url('admin/vehicles/' . $customerId . '/' . $vehicleId));
    }

    /**
     * @param array<string, string> $parameters
     */
    public function delete(Request $request, array $parameters): Response
    {
        $customerId = $this->positiveId($parameters['customerId'] ?? null);
        $vehicleId = $this->positiveId($parameters['vehicleId'] ?? null);
        if ($customerId === null || $vehicleId === null) {
            return Response::notFound();
        }

        $deleted = (new Vehicle(Database::connection($this->databaseConfig)))
            ->deleteUnusedForAdmin($vehicleId, $customerId);
        if ($deleted === null) {
            return $request->input('ajax') === '1'
                ? Response::json(json_encode(['success' => false, 'error' => 'Không tìm thấy phương tiện.'], JSON_THROW_ON_ERROR), 404)
                : Response::notFound();
        }
        if (!$deleted) {
            $message = 'Không thể xóa xe đã có lịch hẹn hoặc lịch sử tiếp nhận.';
            if ($request->input('ajax') === '1') {
                return Response::json(json_encode(['success' => false, 'error' => $message], JSON_THROW_ON_ERROR), 409);
            }
            flash('error', $message);

            return Response::redirect(url('admin/vehicles?customer_id=' . $customerId));
        }

        if ($request->input('ajax') === '1') {
            return Response::json(json_encode(['success' => true], JSON_THROW_ON_ERROR));
        }

        flash('success', 'Đã xóa phương tiện khỏi tài khoản khách hàng.');

        return Response::redirect(url('admin/vehicles?customer_id=' . $customerId));
    }

    /**
     * @param array<string, mixed> $customer
     * @param array<string, mixed> $old
     * @param array<string, string> $errors
     */
    private function form(
        array $customer,
        array $old,
        bool $editing,
        ?int $vehicleId,
        array $errors = [],
        int $status = 200
    ): Response {
        $vehicleModel = new Vehicle(Database::connection($this->databaseConfig));
        $brands = $vehicleModel->activeBrands();
        $brandId = (int) ($old['brand_id'] ?? 0);
        if ($brandId > 0 && !array_filter($brands, static fn (array $brand): bool => (int) $brand['id'] === $brandId)) {
            $currentVehicle = $editing && $vehicleId !== null
                ? $vehicleModel->findForAdmin($vehicleId, (int) $customer['id'])
                : null;
            if ($currentVehicle !== null && (int) $currentVehicle['brand_id'] === $brandId) {
                $brands[] = ['id' => $brandId, 'name' => $currentVehicle['brand_name']];
            }
        }

        return $this->view('admin/vehicles/form', [
            'customer' => $customer,
            'brands' => $brands,
            'errors' => $errors,
            'old' => $old,
            'editing' => $editing,
            'vehicleId' => $vehicleId,
        ], $status);
    }

    private function positiveId(mixed $value): ?int
    {
        $id = filter_var($value, FILTER_VALIDATE_INT);

        return $id === false || $id < 1 ? null : $id;
    }

    private function isDuplicateKey(PDOException $exception): bool
    {
        return $exception->getCode() === '23000'
            && isset($exception->errorInfo[1])
            && (int) $exception->errorInfo[1] === 1062;
    }
}
