<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Request;
use App\Core\Validator;
use App\Models\Vehicle;
use App\Models\VehicleModel;
use PDO;

final class VehicleFormValidator
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @return array{vehicle?: array{brand_id:int,model_id:?int,model:string,license_plate:string,manufacture_year:?int,color:?string,current_mileage:int}, errors?:array<string,string>,old:array<string,mixed>}
     */
    public function validate(
        Request $request,
        bool $requireCatalogModel = false,
        bool $allowInactiveBrand = false
    ): array {
        $input = $request->body();
        $model = is_string($input['model'] ?? null) ? trim($input['model']) : '';
        $plate = is_string($input['license_plate'] ?? null)
            ? strtoupper(trim($input['license_plate']))
            : '';
        $color = is_string($input['color'] ?? null) ? trim($input['color']) : '';
        $brandInput = $input['brand_id'] ?? '';
        $modelIdInput = $input['model_id'] ?? '';
        $mileageInput = $input['current_mileage'] ?? '';
        $yearRaw = $input['manufacture_year'] ?? '';
        $colorRaw = $input['color'] ?? '';
        $brandId = filter_var($brandInput, FILTER_VALIDATE_INT);
        $modelId = filter_var($modelIdInput, FILTER_VALIDATE_INT);
        $mileage = filter_var($mileageInput, FILTER_VALIDATE_INT);
        $yearInput = is_string($yearRaw) || is_int($yearRaw) ? (string) $yearRaw : '';
        $year = $yearInput === '' ? null : filter_var($yearInput, FILTER_VALIDATE_INT);
        $errors = [];

        if ($brandId === false || $brandId < 1) {
            $errors['brand_id'] = 'Vui lòng chọn hãng xe.';
        }
        if (!$requireCatalogModel && !Validator::stringLength($model, 1, 100)) {
            $errors['model'] = 'Tên dòng xe không được vượt quá 100 ký tự.';
        }
        if ($requireCatalogModel && ($modelId === false || $modelId < 1)) {
            $errors['model_id'] = 'Vui lòng chọn dòng xe.';
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
        if ($colorRaw !== '' && !is_string($colorRaw)) {
            $errors['color'] = 'Màu xe không hợp lệ.';
        } elseif ($color !== '' && !Validator::stringLength($color, 1, 50)) {
            $errors['color'] = 'Màu xe không được vượt quá 50 ký tự.';
        }
        if ($mileage === false || $mileage < 0 || $mileage > 4294967295) {
            $errors['current_mileage'] = 'Số km hiện tại không hợp lệ.';
        }
        if (!is_string($yearRaw) && !is_int($yearRaw)) {
            $errors['manufacture_year'] = 'Năm sản xuất không hợp lệ.';
        }

        $old = [
            'brand_id' => is_string($brandInput) || is_int($brandInput) ? $brandInput : '',
            'model_id' => is_string($modelIdInput) || is_int($modelIdInput) ? $modelIdInput : '',
            'model' => $model,
            'license_plate' => $plate,
            'manufacture_year' => $yearInput,
            'color' => $color,
            'current_mileage' => is_string($mileageInput) || is_int($mileageInput) ? $mileageInput : '',
        ];
        if ($errors !== []) {
            return ['errors' => $errors, 'old' => $old];
        }

        $vehicleModel = new Vehicle($this->pdo);
        if (!$vehicleModel->hasActiveBrand($brandId)
            && !($allowInactiveBrand && $vehicleModel->hasBrand($brandId))) {
            return [
                'errors' => ['brand_id' => 'Hãng xe không tồn tại hoặc đang ngừng hoạt động.'],
                'old' => $old,
            ];
        }

        $catalogModel = null;
        if ($requireCatalogModel) {
            $catalogModel = (new VehicleModel($this->pdo))->findActiveForBrand($modelId, $brandId);
            if ($catalogModel === null) {
                return [
                    'errors' => ['model_id' => 'Dòng xe không tồn tại hoặc đang ngừng hoạt động.'],
                    'old' => $old,
                ];
            }
            $model = $catalogModel['name'];
            $old['model'] = $model;
        }

        return [
            'old' => $old,
            'vehicle' => [
                'brand_id' => $brandId,
                'model_id' => $catalogModel === null ? null : (int) $catalogModel['id'],
                'model' => $model,
                'license_plate' => $plate,
                'manufacture_year' => $year === false ? null : $year,
                'color' => $color === '' ? null : $color,
                'current_mileage' => $mileage,
            ],
        ];
    }
}
