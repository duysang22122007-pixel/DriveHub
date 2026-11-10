<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Models\Vehicle;
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

    public function store(Request $request): Response
    {
        $data = $this->validatedVehicle($request);
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

        $data = $this->validatedVehicle($request);
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
        $deleted = (new Vehicle(Database::connection($this->databaseConfig)))
            ->deleteUnusedForCustomer($vehicleId, $this->customerId());

        if ($deleted === null) {
            return Response::notFound();
        }

        if (!$deleted) {
            flash('error', 'Không thể xóa xe đã có lịch hẹn hoặc lịch sử tiếp nhận.');

            return Response::redirect(url('vehicles'));
        }

        flash('success', 'Đã xóa xe khỏi tài khoản.');

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
     * @return array{vehicle?: array{brand_id:int,model:string,license_plate:string,manufacture_year:?int,color:?string,current_mileage:int}, errors?:array<string,string>,old:array<string,mixed>}
     */
    private function validatedVehicle(Request $request): array
    {
        $input = $request->body();
        $model = is_string($input['model'] ?? null) ? trim($input['model']) : '';
        $plate = is_string($input['license_plate'] ?? null)
            ? strtoupper(trim($input['license_plate']))
            : '';
        $color = is_string($input['color'] ?? null) ? trim($input['color']) : '';
        $brandId = filter_var($input['brand_id'] ?? null, FILTER_VALIDATE_INT);
        $mileage = filter_var($input['current_mileage'] ?? null, FILTER_VALIDATE_INT);
        $yearInput = $input['manufacture_year'] ?? '';
        $year = $yearInput === '' ? null : filter_var($yearInput, FILTER_VALIDATE_INT);
        $errors = [];

        if ($brandId === false || $brandId < 1) {
            $errors['brand_id'] = 'Vui lòng chọn hãng xe.';
        }
        if (!Validator::stringLength($model, 1, 100)) {
            $errors['model'] = 'Tên dòng xe không được vượt quá 100 ký tự.';
        }
        if (!preg_match('/\A[A-Z0-9][A-Z0-9 .-]{2,19}\z/', $plate)) {
            $errors['license_plate'] = 'Biển số xe không hợp lệ.';
        }
        if ($yearInput !== '' && (
            $year === false
            || $year < 1901
            || $year > ((int) date('Y') + 1)
        )) {
            $errors['manufacture_year'] = 'Năm sản xuất không hợp lệ.';
        }
        if ($color !== '' && !Validator::stringLength($color, 1, 50)) {
            $errors['color'] = 'Màu xe không được vượt quá 50 ký tự.';
        }
        if ($mileage === false || $mileage < 0) {
            $errors['current_mileage'] = 'Số km hiện tại không hợp lệ.';
        }

        $old = [
            'brand_id' => $input['brand_id'] ?? '',
            'model' => $model,
            'license_plate' => $plate,
            'manufacture_year' => $yearInput,
            'color' => $color,
            'current_mileage' => $input['current_mileage'] ?? '',
        ];
        if ($errors !== []) {
            return ['errors' => $errors, 'old' => $old];
        }

        $brandExists = false;
        foreach ((new Vehicle(Database::connection($this->databaseConfig)))->activeBrands() as $brand) {
            if ((int) $brand['id'] === $brandId) {
                $brandExists = true;
                break;
            }
        }
        if (!$brandExists) {
            return [
                'errors' => ['brand_id' => 'Hãng xe không tồn tại hoặc đang ngừng hoạt động.'],
                'old' => $old,
            ];
        }

        return [
            'old' => $old,
            'vehicle' => [
                'brand_id' => $brandId,
                'model' => $model,
                'license_plate' => $plate,
                'manufacture_year' => $year === false ? null : $year,
                'color' => $color === '' ? null : $color,
                'current_mileage' => $mileage,
            ],
        ];
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
