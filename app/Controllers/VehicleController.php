<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\Vehicle;
use App\Models\VehicleModel;
use App\Services\VehicleFormValidator;
use App\Services\VehicleImageStorage;
use PDOException;

final class VehicleController extends Controller
{
    /**
     * @param array<string, string|int> $databaseConfig
     */
    public function __construct(private readonly array $databaseConfig)
    {
    }

    public function index(): Response
    {
        $model = new Vehicle(Database::connection($this->databaseConfig));

        return $this->view('customer/vehicles/index', [
            'vehicles' => $model->forCustomer($this->customerId()),
            'success' => flash('success'),
            'error' => flash('error'),
        ]);
    }

    public function create(): Response
    {
        return $this->form();
    }

    public function models(Request $request): Response
    {
        $brandId = filter_var($request->input('brand_id'), FILTER_VALIDATE_INT);
        if ($brandId === false || $brandId < 1) {
            return Response::json(
                json_encode(['error' => 'Vui lòng chọn hãng xe hợp lệ.'], JSON_THROW_ON_ERROR),
                422
            );
        }

        $pdo = Database::connection($this->databaseConfig);
        if (!(new Vehicle($pdo))->hasActiveBrand($brandId)) {
            return Response::json(
                json_encode(['error' => 'Hãng xe không tồn tại hoặc đang ngừng hoạt động.'], JSON_THROW_ON_ERROR),
                404
            );
        }

        $keywordInput = $request->input('q', '');
        $keyword = is_string($keywordInput) ? trim($keywordInput) : '';
        if (mb_strlen($keyword, 'UTF-8') > 100) {
            return Response::json(
                json_encode(['error' => 'Từ khóa tìm kiếm quá dài.'], JSON_THROW_ON_ERROR),
                422
            );
        }

        return Response::json(json_encode(
            (new VehicleModel($pdo))->activeForBrand($brandId, $keyword),
            JSON_THROW_ON_ERROR
        ));
    }

    public function store(Request $request): Response
    {
        $data = (new VehicleFormValidator(Database::connection($this->databaseConfig)))->validate($request, true);
        if (isset($data['errors'])) {
            return $this->form($data['errors'], $data['old'], 422);
        }

        try {
            (new Vehicle(Database::connection($this->databaseConfig)))->create($this->customerId(), $data['vehicle']);
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                return $this->form(
                    ['license_plate' => 'Biển số xe đã được đăng ký trong hệ thống.'],
                    $data['old'],
                    422
                );
            }

            throw $exception;
        }

        flash('success', 'Đã thêm xe vào tài khoản.');

        return Response::redirect(url('vehicles'));
    }

    /**
     * @param array<string, string> $parameters
     */
    public function edit(Request $request, array $parameters): Response
    {
        $vehicleId = $this->vehicleId($parameters);
        $model = new Vehicle(Database::connection($this->databaseConfig));
        $vehicle = $model->findForCustomer($vehicleId, $this->customerId());
        if ($vehicle === null) {
            return Response::notFound();
        }

        return $this->view('customer/vehicles/form', [
            'brands' => $model->activeBrands(),
            'errors' => [],
            'old' => $vehicle,
            'editing' => true,
            'vehicleId' => $vehicleId,
            'success' => null,
            'error' => null,
        ]);
    }

    /**
     * @param array<string, string> $parameters
     */
    public function update(Request $request, array $parameters): Response
    {
        $vehicleId = $this->vehicleId($parameters);
        $model = new Vehicle(Database::connection($this->databaseConfig));
        $existing = $model->findForCustomer($vehicleId, $this->customerId());
        if ($existing === null) {
            return Response::notFound();
        }

        $data = (new VehicleFormValidator(Database::connection($this->databaseConfig)))->validate($request);
        if (isset($data['errors'])) {
            return $this->form($data['errors'], $data['old'], 422, true, $vehicleId);
        }

        try {
            $model->updateForCustomer($vehicleId, $this->customerId(), $data['vehicle']);
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                return $this->form(
                    ['license_plate' => 'Biển số xe đã được đăng ký cho một xe khác.'],
                    $data['old'],
                    422,
                    true,
                    $vehicleId
                );
            }

            throw $exception;
        }

        flash('success', 'Đã cập nhật thông tin xe.');

        return Response::redirect(url('vehicles'));
    }

    /**
     * @param array<string, string> $parameters
     */
    public function delete(Request $request, array $parameters): Response
    {
        $vehicleId = $this->vehicleId($parameters);
        $pdo = Database::connection($this->databaseConfig);
        $vehicleModel = new Vehicle($pdo);
        $deleted = $vehicleModel->deleteUnusedForOwner($vehicleId, $this->customerId());

        if ($deleted === null) {
            return Response::notFound();
        }

        if (!$deleted['deleted']) {
            flash('error', 'Không thể xóa xe đã có lịch hẹn hoặc lịch sử tiếp nhận.');

            return Response::redirect(url('vehicles'));
        }

        $message = 'Đã xóa xe khỏi tài khoản.';
        if (!(new VehicleImageStorage())->deleteIfUnused($pdo, $deleted['image_path'])) {
            error_log('Unable to remove deleted customer vehicle image: ' . (string) $deleted['image_path']);
            $message .= ' Tuy nhiên, chưa thể dọn ảnh phương tiện.';
        }
        flash('success', $message);

        return Response::redirect(url('vehicles'));
    }

    private function form(
        array $errors = [],
        array $old = [],
        int $status = 200,
        bool $editing = false,
        ?int $vehicleId = null
    ): Response {
        return $this->view('customer/vehicles/form', [
            'brands' => (new Vehicle(Database::connection($this->databaseConfig)))->activeBrands(),
            'errors' => $errors,
            'old' => $old,
            'editing' => $editing,
            'vehicleId' => $vehicleId,
            'success' => null,
            'error' => null,
        ], $status);
    }

    /**
     * @param array<string, string> $parameters
     */
    private function vehicleId(array $parameters): int
    {
        $vehicleId = filter_var($parameters['id'] ?? null, FILTER_VALIDATE_INT);
        if ($vehicleId === false || $vehicleId < 1) {
            return 0;
        }

        return $vehicleId;
    }

    private function customerId(): int
    {
        return (int) $_SESSION['user_id'];
    }
}
