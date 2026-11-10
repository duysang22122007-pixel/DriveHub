<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Models\Customer;
use App\Models\VehicleBrand;
use App\Models\Vehicle;
use PDOException;

final class AdminController extends Controller
{
    /**
     * @param array<string, string|int> $databaseConfig
     */
    public function __construct(private readonly array $databaseConfig)
    {
    }

    public function customers(): Response
    {
        $customers = new Customer(Database::connection($this->databaseConfig));

        return $this->view('admin/customers/index', [
            'customers' => $customers->list(),
            'success' => flash('success'),
            'error' => flash('error'),
        ]);
    }

    public function vehicles(): Response
    {
        return $this->view('admin/vehicles/index', [
            'vehicles' => (new Vehicle(Database::connection($this->databaseConfig)))->allForAdmin(),
        ]);
    }

    public function brands(): Response
    {
        return $this->view('admin/brands/index', [
            'brands' => (new VehicleBrand(Database::connection($this->databaseConfig)))->all(),
            'success' => flash('success'),
            'error' => flash('error'),
        ]);
    }

    public function storeBrand(Request $request): Response
    {
        $name = $request->input('name');
        if (!is_string($name) || !Validator::stringLength(trim($name), 1, 100)) {
            flash('error', 'Tên hãng xe phải có từ 1 đến 100 ký tự.');

            return Response::redirect(url('admin/brands'));
        }

        try {
            (new VehicleBrand(Database::connection($this->databaseConfig)))->create(trim($name));
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                flash('error', 'Hãng xe này đã tồn tại.');

                return Response::redirect(url('admin/brands'));
            }

            throw $exception;
        }

        flash('success', 'Đã thêm hãng xe.');

        return Response::redirect(url('admin/brands'));
    }

    /**
     * @param array<string, string> $parameters
     */
    public function updateBrandStatus(Request $request, array $parameters): Response
    {
        $brandId = filter_var($parameters['id'] ?? null, FILTER_VALIDATE_INT);
        $status = $request->input('status');
        if (
            $brandId === false
            || $brandId < 1
            || !is_string($status)
            || !in_array($status, ['0', '1'], true)
        ) {
            flash('error', 'Yêu cầu cập nhật hãng xe không hợp lệ.');

            return Response::redirect(url('admin/brands'));
        }

        $updated = (new VehicleBrand(Database::connection($this->databaseConfig)))->updateStatus(
            $brandId,
            (int) $status
        );
        if (!$updated) {
            flash('error', 'Không tìm thấy hãng xe cần cập nhật.');

            return Response::redirect(url('admin/brands'));
        }

        flash('success', 'Đã cập nhật trạng thái hãng xe.');

        return Response::redirect(url('admin/brands'));
    }

    /**
     * @param array<string, string> $parameters
     */
    public function updateCustomerStatus(Request $request, array $parameters): Response
    {
        $customerId = filter_var($parameters['id'] ?? null, FILTER_VALIDATE_INT);
        $status = $request->input('status');
        if (
            $customerId === false
            || $customerId < 1
            || !is_string($status)
            || !in_array($status, ['active', 'locked'], true)
        ) {
            flash('error', 'Yêu cầu cập nhật khách hàng không hợp lệ.');

            return Response::redirect(url('admin/customers'));
        }

        (new Customer(Database::connection($this->databaseConfig)))->updateStatus($customerId, $status);
        flash('success', 'Đã cập nhật trạng thái tài khoản khách hàng.');

        return Response::redirect(url('admin/customers'));
    }
}
