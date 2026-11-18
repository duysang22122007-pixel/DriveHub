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
use App\Services\VehicleImageStorage;
use InvalidArgumentException;
use PDOException;
use Throwable;

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

        $images = new VehicleImageStorage();
        try {
            $imagePath = $images->store($request);
        } catch (InvalidArgumentException $exception) {
            $errors = ['image' => $exception->getMessage()];
            if ($request->input('ajax') === '1') {
                return Response::json(json_encode(['success' => false, 'errors' => $errors], JSON_THROW_ON_ERROR), 422);
            }

            return $this->form($customer, $data['old'], false, null, $errors, 422);
        }
        $data['vehicle']['image_path'] = $imagePath;

        try {
            (new Vehicle($pdo))->create($customerId, $data['vehicle']);
        } catch (PDOException $exception) {
            $this->cleanupImage($pdo, $images, $imagePath);
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
        } catch (Throwable $exception) {
            $this->cleanupImage($pdo, $images, $imagePath);
            throw $exception;
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

        $images = new VehicleImageStorage();
        try {
            $newImagePath = $images->store($request);
        } catch (InvalidArgumentException $exception) {
            $errors = ['image' => $exception->getMessage()];
            if ($request->input('ajax') === '1') {
                return Response::json(json_encode(['success' => false, 'errors' => $errors], JSON_THROW_ON_ERROR), 422);
            }

            return $this->form($customer, $data['old'], true, $vehicleId, $errors, 422);
        }
        $data['vehicle']['image_path'] = $newImagePath ?? $existing['image_path'];

        try {
            $model->updateForAdmin($vehicleId, $customerId, $data['vehicle']);
        } catch (PDOException $exception) {
            $this->cleanupImage($pdo, $images, $newImagePath);
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
        } catch (Throwable $exception) {
            $this->cleanupImage($pdo, $images, $newImagePath);
            throw $exception;
        }

        $warning = null;
        if (
            $newImagePath !== null
            && $existing['image_path'] !== $newImagePath
            && !$images->deleteIfUnused($pdo, $existing['image_path'])
        ) {
            error_log('Unable to remove replaced vehicle image: ' . (string) $existing['image_path']);
            $warning = 'Đã lưu phương tiện nhưng chưa thể dọn ảnh cũ.';
        }

        flash('success', 'Đã cập nhật thông tin phương tiện.' . ($warning !== null ? ' ' . $warning : ''));

        if ($request->input('ajax') === '1') {
            return Response::json(json_encode(['success' => true, 'warning' => $warning], JSON_THROW_ON_ERROR));
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

        $pdo = Database::connection($this->databaseConfig);
        $deleted = (new Vehicle($pdo))
            ->deleteUnusedForOwner($vehicleId, $customerId);
        if ($deleted === null) {
            return $request->input('ajax') === '1'
                ? Response::json(json_encode(['success' => false, 'error' => 'Không tìm thấy phương tiện.'], JSON_THROW_ON_ERROR), 404)
                : Response::notFound();
        }
        if (!$deleted['deleted']) {
            $message = 'Không thể xóa xe đã có lịch hẹn hoặc lịch sử tiếp nhận.';
            if ($request->input('ajax') === '1') {
                return Response::json(json_encode(['success' => false, 'error' => $message], JSON_THROW_ON_ERROR), 409);
            }
            flash('error', $message);

            return Response::redirect(url('admin/vehicles?customer_id=' . $customerId));
        }

        $warning = null;
        if (!(new VehicleImageStorage())->deleteIfUnused($pdo, $deleted['image_path'])) {
            error_log('Unable to remove deleted vehicle image: ' . (string) $deleted['image_path']);
            $warning = ' Phương tiện đã xóa nhưng chưa thể dọn ảnh.';
        }

        if ($request->input('ajax') === '1') {
            return Response::json(json_encode(['success' => true, 'warning' => $warning], JSON_THROW_ON_ERROR));
        }

        flash('success', 'Đã xóa phương tiện khỏi tài khoản khách hàng.' . ($warning ?? ''));

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
        $currentVehicle = $editing && $vehicleId !== null
            ? $vehicleModel->findForAdmin($vehicleId, (int) $customer['id'])
            : null;
        $brandId = (int) ($old['brand_id'] ?? 0);
        if ($brandId > 0 && !array_filter($brands, static fn (array $brand): bool => (int) $brand['id'] === $brandId)) {
            if ($currentVehicle !== null && (int) $currentVehicle['brand_id'] === $brandId) {
                $brands[] = ['id' => $brandId, 'name' => $currentVehicle['brand_name']];
            }
        }
        $images = new VehicleImageStorage();
        $currentImage = $currentVehicle === null
            ? url('assets/images/vehicle-placeholder.svg')
            : ($images->publicUrl($currentVehicle['image_path'])
                ?? $images->publicUrl($currentVehicle['catalog_image'])
                ?? url('assets/images/vehicle-placeholder.svg'));

        return $this->view('admin/vehicles/form', [
            'customer' => $customer,
            'brands' => $brands,
            'errors' => $errors,
            'old' => $old,
            'editing' => $editing,
            'vehicleId' => $vehicleId,
            'currentImage' => $currentImage,
        ], $status);
    }

    private function cleanupImage(\PDO $pdo, VehicleImageStorage $images, ?string $path): void
    {
        if ($path !== null && !$images->deleteIfUnused($pdo, $path)) {
            error_log('Unable to remove unreferenced vehicle image: ' . $path);
        }
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
