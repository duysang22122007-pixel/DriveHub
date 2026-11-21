<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\Service;
use InvalidArgumentException;
use PDOException;

final class AdminServiceController extends Controller
{
    /**
     * @param array<string, string|int> $databaseConfig
     */
    public function __construct(private readonly array $databaseConfig)
    {
    }

    public function index(Request $request): Response
    {
        $model = new Service(Database::connection($this->databaseConfig));
        $categories = $model->adminCategories();
        $servicesByCategory = [];
        foreach ($model->adminServices() as $service) {
            $servicesByCategory[(int) $service['category_id']][] = $service;
        }

        return $this->view('admin/services/index', [
            'categories' => $categories,
            'servicesByCategory' => $servicesByCategory,
            'success' => flash('success'),
            'error' => flash('error'),
        ]);
    }

    public function saveCategory(Request $request): Response
    {
        $name = $request->input('name');
        $description = $request->input('description', '');
        if (
            !is_string($name)
            || !$this->validLength(trim($name), 1, 150)
            || !is_string($description)
            || !$this->validLength($description, 0, 5000)
        ) {
            flash('error', 'Tên nhóm phải có từ 1 đến 150 ký tự; mô tả tối đa 5.000 ký tự.');

            return Response::redirect(url('admin/services'));
        }

        try {
            (new Service(Database::connection($this->databaseConfig)))->saveCategory(
                null,
                trim($name),
                trim($description) === '' ? null : trim($description)
            );
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                flash('error', 'Tên nhóm dịch vụ này đã được sử dụng.');

                return Response::redirect(url('admin/services'));
            }
            error_log('Service category creation failed: ' . $exception->getMessage());
            flash('error', 'Không thể tạo nhóm dịch vụ do lỗi cơ sở dữ liệu.');

            return Response::redirect(url('admin/services'));
        }

        flash('success', 'Đã tạo nhóm dịch vụ.');

        return Response::redirect(url('admin/services'));
    }

    /**
     * @param array<string, string> $parameters
     */
    public function updateCategory(Request $request, array $parameters): Response
    {
        $categoryId = $this->positiveId($parameters['id'] ?? null);
        $name = $request->input('name');
        $description = $request->input('description', '');
        if (
            $categoryId === null
            || !is_string($name)
            || !$this->validLength(trim($name), 1, 150)
            || !is_string($description)
            || !$this->validLength($description, 0, 5000)
        ) {
            flash('error', 'Thông tin nhóm dịch vụ không hợp lệ.');

            return Response::redirect(url('admin/services'));
        }

        try {
            (new Service(Database::connection($this->databaseConfig)))->saveCategory(
                $categoryId,
                trim($name),
                trim($description) === '' ? null : trim($description)
            );
        } catch (InvalidArgumentException $exception) {
            flash('error', $exception->getMessage());

            return Response::redirect(url('admin/services'));
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                flash('error', 'Tên nhóm dịch vụ này đã được sử dụng.');

                return Response::redirect(url('admin/services'));
            }
            error_log('Service category update failed: ' . $exception->getMessage());
            flash('error', 'Không thể cập nhật nhóm dịch vụ do lỗi cơ sở dữ liệu.');

            return Response::redirect(url('admin/services'));
        }

        flash('success', 'Đã cập nhật nhóm dịch vụ.');

        return Response::redirect(url('admin/services'));
    }

    /**
     * @param array<string, string> $parameters
     */
    public function deleteCategory(Request $request, array $parameters): Response
    {
        $categoryId = $this->positiveId($parameters['id'] ?? null);
        if ($categoryId === null) {
            flash('error', 'Mã nhóm dịch vụ không hợp lệ.');

            return Response::redirect(url('admin/services'));
        }

        try {
            (new Service(Database::connection($this->databaseConfig)))->deleteCategoryIfEmpty($categoryId);
        } catch (InvalidArgumentException $exception) {
            flash('error', $exception->getMessage());

            return Response::redirect(url('admin/services'));
        } catch (PDOException $exception) {
            error_log('Service category deletion failed: ' . $exception->getMessage());
            flash('error', 'Không thể xóa nhóm dịch vụ do lỗi cơ sở dữ liệu.');

            return Response::redirect(url('admin/services'));
        }

        flash('success', 'Đã xóa nhóm dịch vụ trống.');

        return Response::redirect(url('admin/services'));
    }

    public function store(Request $request): Response
    {
        return $this->saveService($request, null);
    }

    /**
     * @param array<string, string> $parameters
     */
    public function update(Request $request, array $parameters): Response
    {
        $serviceId = $this->positiveId($parameters['id'] ?? null);
        if ($serviceId === null) {
            flash('error', 'Mã dịch vụ không hợp lệ.');

            return Response::redirect(url('admin/services'));
        }

        return $this->saveService($request, $serviceId);
    }

    /**
     * @param array<string, string> $parameters
     */
    public function deactivate(Request $request, array $parameters): Response
    {
        $serviceId = $this->positiveId($parameters['id'] ?? null);
        if ($serviceId === null) {
            flash('error', 'Mã dịch vụ không hợp lệ.');

            return Response::redirect(url('admin/services'));
        }

        try {
            $found = (new Service(Database::connection($this->databaseConfig)))->deactivateService($serviceId);
        } catch (PDOException $exception) {
            error_log('Service deactivation failed: ' . $exception->getMessage());
            flash('error', 'Không thể ngừng dịch vụ do lỗi cơ sở dữ liệu.');

            return Response::redirect(url('admin/services'));
        }

        flash($found ? 'success' : 'error', $found ? 'Đã ngừng dịch vụ; dữ liệu lịch sử được giữ nguyên.' : 'Không tìm thấy dịch vụ.');

        return Response::redirect(url('admin/services'));
    }

    public function move(Request $request): Response
    {
        $rawIds = $request->input('service_ids');
        $categoryId = $this->positiveId($request->input('category_id'));
        if (!is_array($rawIds) || count($rawIds) > 100 || $categoryId === null) {
            flash('error', 'Danh sách chuyển nhóm không hợp lệ.');

            return Response::redirect(url('admin/services'));
        }

        $serviceIds = [];
        foreach ($rawIds as $rawId) {
            $serviceId = $this->positiveId($rawId);
            if ($serviceId === null) {
                flash('error', 'Danh sách dịch vụ cần chuyển không hợp lệ.');

                return Response::redirect(url('admin/services'));
            }
            $serviceIds[] = $serviceId;
        }

        try {
            (new Service(Database::connection($this->databaseConfig)))->moveServices($serviceIds, $categoryId);
        } catch (InvalidArgumentException $exception) {
            flash('error', $exception->getMessage());

            return Response::redirect(url('admin/services'));
        } catch (PDOException $exception) {
            error_log('Service category transfer failed: ' . $exception->getMessage());
            flash('error', 'Không thể chuyển dịch vụ do lỗi cơ sở dữ liệu.');

            return Response::redirect(url('admin/services'));
        }

        flash('success', 'Đã chuyển các dịch vụ được chọn sang nhóm mới.');

        return Response::redirect(url('admin/services'));
    }

    private function saveService(Request $request, ?int $serviceId): Response
    {
        $categoryId = $this->positiveId($request->input('category_id'));
        $name = $request->input('name');
        $description = $request->input('description', '');
        $price = $request->input('price');
        $duration = filter_var($request->input('duration'), FILTER_VALIDATE_INT);
        $status = $request->input('status', 'active');

        if (
            $categoryId === null
            || !is_string($name)
            || !$this->validLength(trim($name), 1, 150)
            || !is_string($description)
            || !$this->validLength($description, 0, 5000)
            || !is_string($price)
            || preg_match('/\A\d{1,10}(?:\.\d{1,2})?\z/', $price) !== 1
            || $duration === false
            || $duration < 0
            || $duration > 65535
            || !is_string($status)
            || !in_array($status, ['active', 'inactive'], true)
        ) {
            flash('error', 'Thông tin dịch vụ không hợp lệ; vui lòng kiểm tra tên, giá, thời lượng và trạng thái.');

            return Response::redirect(url('admin/services'));
        }

        try {
            (new Service(Database::connection($this->databaseConfig)))->saveService(
                $serviceId,
                $categoryId,
                trim($name),
                trim($description) === '' ? null : trim($description),
                $price,
                $duration,
                $status
            );
        } catch (InvalidArgumentException $exception) {
            flash('error', $exception->getMessage());

            return Response::redirect(url('admin/services'));
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                flash('error', 'Dịch vụ trùng tên với một dịch vụ khác trong nhóm này.');

                return Response::redirect(url('admin/services'));
            }
            error_log('Service save failed: ' . $exception->getMessage());
            flash('error', 'Không thể lưu dịch vụ do lỗi cơ sở dữ liệu.');

            return Response::redirect(url('admin/services'));
        }

        flash('success', $serviceId === null ? 'Đã thêm dịch vụ.' : 'Đã cập nhật dịch vụ.');

        return Response::redirect(url('admin/services'));
    }

    private function positiveId(mixed $value): ?int
    {
        $id = filter_var($value, FILTER_VALIDATE_INT);

        return $id !== false && $id > 0 ? $id : null;
    }

    private function validLength(string $value, int $min, int $max): bool
    {
        $length = function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);

        return $length >= $min && $length <= $max;
    }
}
