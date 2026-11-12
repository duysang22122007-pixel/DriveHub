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
use App\Models\VehicleModel;
use App\Services\NhtsaClient;
use App\Services\VehicleCatalogImporter;
use App\Services\VehicleDataNormalizer;
use App\Services\VehicleImportManager;
use InvalidArgumentException;
use PDOException;
use RuntimeException;

final class AdminController extends Controller
{
    /**
     * @param array<string, string|int> $databaseConfig
     */
    public function __construct(private readonly array $databaseConfig)
    {
    }

    public function customers(Request $request): Response
    {
        $keyword = trim((string) $request->input('q', ''));
        $status  = (string) $request->input('status', '');

        $customers = new Customer(Database::connection($this->databaseConfig));

        $list = ($keyword !== '' || in_array($status, ['active', 'inactive', 'locked'], true))
            ? $customers->search($keyword, $status)
            : $customers->list();

        // Xuất CSV nếu được yêu cầu
        if ($request->input('export') === 'csv') {
            return $this->exportCsv($list);
        }

        return $this->view('admin/customers/index', [
            'customers' => $list,
            'q'         => $keyword,
            'status'    => $status,
            'success'   => flash('success'),
            'error'     => flash('error'),
        ]);
    }

    /**
     * @param list<array<string, mixed>> $rows
     */
    private function exportCsv(array $rows): Response
    {
        $filename = 'customers_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');

        // BOM để Excel mở đúng UTF-8
        echo "\xEF\xBB\xBF";

        $out = fopen('php://output', 'w');
        fputcsv($out, ['ID', 'Họ tên', 'Email', 'Điện thoại', 'Số xe', 'Trạng thái', 'Ngày tạo']);

        foreach ($rows as $row) {
            fputcsv($out, [
                $row['id'],
                $row['full_name'],
                $row['email'],
                $row['phone'] ?? '',
                (int) $row['vehicle_count'],
                $row['status'] === 'active' ? 'Đang hoạt động' : ($row['status'] === 'locked' ? 'Đã khóa' : 'Không hoạt động'),
                $row['created_at'],
            ]);
        }

        fclose($out);
        exit;
    }

    public function vehicles(): Response
    {
        return $this->view('admin/vehicles/index', [
            'vehicles' => (new Vehicle(Database::connection($this->databaseConfig)))->allForAdmin(),
        ]);
    }

    public function vehicleImport(): Response
    {
        $pdo = Database::connection($this->databaseConfig);

        return $this->view('admin/vehicle-import/index', [
            'sessions' => $this->vehicleImportManager($pdo)->history(),
            'success' => flash('success'),
            'error' => flash('error'),
        ]);
    }

    public function vehicleModelsReview(Request $request): Response
    {
        $keywordInput = $request->input('q', '');
        $keyword = is_string($keywordInput) ? trim($keywordInput) : '';
        if (mb_strlen($keyword, 'UTF-8') > 100) {
            $keyword = mb_substr($keyword, 0, 100, 'UTF-8');
        }

        $brandValue = $request->input('brand_id', '');
        $brandId = $brandValue === '' ? null : $this->positiveId($brandValue);
        if ($brandValue !== '' && $brandId === null) {
            $brandId = 0;
        }

        $statusInput = $request->input('status', 'unverified');
        $status = is_string($statusInput) && in_array(
            $statusInput,
            ['all', 'unverified', 'verified', 'not_suitable'],
            true
        ) ? $statusInput : 'unverified';
        $verificationStatus = $status === 'all' ? null : $status;
        $pageInput = filter_var($request->input('page', 1), FILTER_VALIDATE_INT);
        $page = $pageInput === false || $pageInput < 1 ? 1 : $pageInput;
        $catalog = new VehicleModel(Database::connection($this->databaseConfig));
        $pageData = $catalog->reviewPage($keyword, $brandId, $verificationStatus, $page);
        $pageCount = max(1, (int) ceil($pageData['total'] / 25));
        if ($page > $pageCount) {
            $page = $pageCount;
            $pageData = $catalog->reviewPage($keyword, $brandId, $verificationStatus, $page);
        }

        return $this->view('admin/vehicle-import/review', [
            'models' => $pageData['items'],
            'total' => $pageData['total'],
            'page' => $page,
            'pageCount' => $pageCount,
            'brands' => (new VehicleBrand(Database::connection($this->databaseConfig)))->search(),
            'q' => $keyword,
            'brandId' => $brandId,
            'status' => $status,
            'success' => flash('success'),
            'error' => flash('error'),
        ]);
    }

    /**
     * @param array<string, string> $parameters
     */
    public function updateVehicleModelVerification(Request $request, array $parameters): Response
    {
        $modelId = $this->positiveId($parameters['id'] ?? null);
        $status = $request->input('status');
        if (
            $modelId === null
            || !is_string($status)
            || !in_array($status, ['verified', 'not_suitable'], true)
        ) {
            flash('error', 'Yêu cầu duyệt dòng xe không hợp lệ.');

            return Response::redirect(url('admin/vehicle-import/review'));
        }

        $updated = (new VehicleModel(Database::connection($this->databaseConfig)))->setVerificationStatus(
            $modelId,
            $status,
            (int) ($_SESSION['user_id'] ?? 0)
        );
        flash(
            $updated ? 'success' : 'error',
            $updated
                ? ($status === 'verified' ? 'Đã duyệt dòng xe phù hợp.' : 'Đã đánh dấu dòng xe không phù hợp.')
                : 'Không tìm thấy dòng xe NHTSA cần duyệt.'
        );

        return Response::redirect(url('admin/vehicle-import/review'));
    }

    public function bulkReviewVehicleModels(Request $request): Response
    {
        $rawIds = $request->input('model_ids', []);
        $status = $request->input('status');
        if (
            !is_array($rawIds)
            || $rawIds === []
            || count($rawIds) > 100
            || !is_string($status)
            || !in_array($status, ['verified', 'not_suitable'], true)
        ) {
            flash('error', 'Chọn từ 1 đến 100 dòng xe và trạng thái duyệt hợp lệ.');

            return Response::redirect(url('admin/vehicle-import/review'));
        }

        $modelIds = [];
        foreach ($rawIds as $value) {
            $modelId = $this->positiveId($value);
            if ($modelId === null) {
                flash('error', 'Danh sách dòng xe được chọn không hợp lệ.');

                return Response::redirect(url('admin/vehicle-import/review'));
            }
            $modelIds[] = $modelId;
        }
        $modelIds = array_values(array_unique($modelIds));

        $updated = (new VehicleModel(Database::connection($this->databaseConfig)))
            ->setVerificationStatusForMany($modelIds, $status, (int) ($_SESSION['user_id'] ?? 0));
        flash(
            $updated > 0 ? 'success' : 'error',
            $updated > 0
                ? 'Đã cập nhật trạng thái cho ' . $updated . ' dòng xe.'
                : 'Không tìm thấy dòng xe NHTSA phù hợp trong lựa chọn.'
        );

        return Response::redirect(url('admin/vehicle-import/review'));
    }

    public function startVehicleImport(Request $request): Response
    {
        $mode = $request->input('mode');
        if (!is_string($mode)) {
            flash('error', 'Vui lòng chọn loại dữ liệu cần đồng bộ.');

            return Response::redirect(url('admin/vehicle-import'));
        }

        try {
            $session = $this->vehicleImportManager(Database::connection($this->databaseConfig))->start(
                $mode,
                (int) ($_SESSION['user_id'] ?? 0),
                $request->input('include_trucks') === '1'
            );
            flash('success', 'Đã xử lý một đợt đồng bộ. Tiến trình được lưu và có thể tiếp tục.');
            if (is_string($session['last_error'] ?? null) && $session['last_error'] !== '') {
                flash('error', 'Tạm dừng do lỗi API: ' . $session['last_error']);
            }
        } catch (RuntimeException $exception) {
            flash('error', $exception->getMessage());
        }

        return Response::redirect(url('admin/vehicle-import'));
    }

    /**
     * @param array<string, string> $parameters
     */
    public function continueVehicleImport(Request $request, array $parameters): Response
    {
        $sessionId = $this->positiveId($parameters['id'] ?? null);
        if ($sessionId === null) {
            flash('error', 'Phiên đồng bộ không hợp lệ.');

            return Response::redirect(url('admin/vehicle-import'));
        }

        try {
            $session = $this->vehicleImportManager(Database::connection($this->databaseConfig))
                ->resume($sessionId);
            flash(
                is_string($session['last_error'] ?? null) && $session['last_error'] !== '' ? 'error' : 'success',
                is_string($session['last_error'] ?? null) && $session['last_error'] !== ''
                    ? 'Tạm dừng do lỗi API: ' . $session['last_error']
                    : 'Đã tiếp tục xử lý một đợt đồng bộ.'
            );
        } catch (RuntimeException $exception) {
            flash('error', $exception->getMessage());
        }

        return Response::redirect(url('admin/vehicle-import'));
    }

    private function vehicleImportManager(\PDO $pdo): VehicleImportManager
    {
        $normalizer = new VehicleDataNormalizer();

        return new VehicleImportManager(
            $pdo,
            new NhtsaClient(),
            $normalizer,
            new VehicleCatalogImporter($pdo, $normalizer)
        );
    }

    public function brands(Request $request): Response
    {
        $input = $request->input('q', '');
        $keyword = is_string($input) ? trim($input) : '';

        return $this->view('admin/brands/index', [
            'brands'  => (new VehicleBrand(Database::connection($this->databaseConfig)))->search($keyword),
            'q'       => $keyword,
            'success' => flash('success'),
            'error'   => flash('error'),
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
            $logo = $this->catalogImage($request, 'logo', null);
            (new VehicleBrand(Database::connection($this->databaseConfig)))->create(trim($name), $logo);
        } catch (InvalidArgumentException $exception) {
            flash('error', $exception->getMessage());

            return Response::redirect(url('admin/brands'));
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
    public function updateBrand(Request $request, array $parameters): Response
    {
        $brandId = $this->positiveId($parameters['id'] ?? null);
        $name = $request->input('name');
        $status = $request->input('status');
        $brands = new VehicleBrand(Database::connection($this->databaseConfig));
        $brand = $brandId === null ? null : $brands->find($brandId);

        if (
            $brand === null
            || !is_string($name)
            || !Validator::stringLength(trim($name), 1, 100)
            || !is_string($status)
            || !in_array($status, ['0', '1'], true)
        ) {
            flash('error', 'Thông tin hãng xe không hợp lệ.');

            return Response::redirect(url('admin/brands'));
        }

        try {
            $logo = $this->catalogImage($request, 'logo', $brand['logo']);
            $brands->update($brandId, trim($name), $logo, (int) $status);
        } catch (InvalidArgumentException $exception) {
            flash('error', $exception->getMessage());

            return Response::redirect(url('admin/brands'));
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                flash('error', 'Tên hãng xe này đã được sử dụng.');

                return Response::redirect(url('admin/brands'));
            }

            throw $exception;
        }

        flash('success', 'Đã cập nhật hãng xe.');

        return Response::redirect(url('admin/brands'));
    }

    public function models(Request $request): Response
    {
        $keywordInput = $request->input('q', '');
        $keyword = is_string($keywordInput) ? trim($keywordInput) : '';
        $brandIdValue = $request->input('brand_id', '');
        $brandId = $brandIdValue === '' ? null : $this->positiveId($brandIdValue);
        if ($brandIdValue !== '' && $brandId === null) {
            $brandId = 0;
        }

        $pdo = Database::connection($this->databaseConfig);

        return $this->view('admin/models/index', [
            'models' => (new VehicleModel($pdo))->search($keyword, $brandId),
            'brands' => (new VehicleBrand($pdo))->search(),
            'q' => $keyword,
            'brandId' => $brandId,
            'success' => flash('success'),
            'error' => flash('error'),
        ]);
    }

    public function storeModel(Request $request): Response
    {
        $data = $this->validatedCatalogModel($request);
        if ($data === null) {
            return Response::redirect(url('admin/models'));
        }

        try {
            $image = $this->catalogImage($request, 'image', null);
            (new VehicleModel(Database::connection($this->databaseConfig)))->create(
                $data['brand_id'],
                $data['name'],
                $data['year_from'],
                $data['year_to'],
                $image,
                (int) ($_SESSION['user_id'] ?? 0)
            );
        } catch (InvalidArgumentException $exception) {
            flash('error', $exception->getMessage());

            return Response::redirect(url('admin/models'));
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                flash('error', 'Dòng xe này đã tồn tại cho hãng đã chọn.');

                return Response::redirect(url('admin/models'));
            }

            throw $exception;
        }

        flash('success', 'Đã thêm dòng xe.');

        return Response::redirect(url('admin/models'));
    }

    /**
     * @param array<string, string> $parameters
     */
    public function updateModel(Request $request, array $parameters): Response
    {
        $modelId = $this->positiveId($parameters['id'] ?? null);
        $input = $this->validatedCatalogModel($request);
        $status = $request->input('status');
        if ($modelId === null || $input === null || !is_string($status) || !in_array($status, ['0', '1'], true)) {
            flash('error', 'Thông tin dòng xe không hợp lệ.');

            return Response::redirect(url('admin/models'));
        }

        $catalog = new VehicleModel(Database::connection($this->databaseConfig));
        $existing = $catalog->find($modelId);
        if ($existing === null) {
            flash('error', 'Không tìm thấy dòng xe cần cập nhật.');

            return Response::redirect(url('admin/models'));
        }

        try {
            $image = $this->catalogImage($request, 'image', $existing['image']);
            $updated = $catalog->update(
                $modelId,
                $input['brand_id'],
                $input['name'],
                $input['year_from'],
                $input['year_to'],
                $image,
                (int) $status
            );
        } catch (InvalidArgumentException $exception) {
            flash('error', $exception->getMessage());

            return Response::redirect(url('admin/models'));
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                flash('error', 'Dòng xe này đã tồn tại cho hãng đã chọn.');

                return Response::redirect(url('admin/models'));
            }

            throw $exception;
        }

        flash($updated ? 'success' : 'error', $updated ? 'Đã cập nhật dòng xe.' : 'Không tìm thấy dòng xe cần cập nhật.');

        return Response::redirect(url('admin/models'));
    }

    /**
     * @param array<string, string> $parameters
     */
    public function updateModelStatus(Request $request, array $parameters): Response
    {
        $modelId = $this->positiveId($parameters['id'] ?? null);
        $status = $request->input('status');
        if ($modelId === null || !is_string($status) || !in_array($status, ['0', '1'], true)) {
            flash('error', 'Yêu cầu cập nhật dòng xe không hợp lệ.');

            return Response::redirect(url('admin/models'));
        }

        $updated = (new VehicleModel(Database::connection($this->databaseConfig)))->updateStatus(
            $modelId,
            (int) $status
        );
        flash($updated ? 'success' : 'error', $updated ? 'Đã cập nhật trạng thái dòng xe.' : 'Không tìm thấy dòng xe cần cập nhật.');

        return Response::redirect(url('admin/models'));
    }

    /**
     * @param array<string, string> $parameters
     */
    public function deleteModel(Request $request, array $parameters): Response
    {
        $modelId = $this->positiveId($parameters['id'] ?? null);
        if ($modelId === null) {
            flash('error', 'Yêu cầu xóa dòng xe không hợp lệ.');

            return Response::redirect(url('admin/models'));
        }

        try {
            $deleted = (new VehicleModel(Database::connection($this->databaseConfig)))->deleteIfUnused($modelId);
        } catch (PDOException $exception) {
            if ($exception->getCode() !== '23000') {
                throw $exception;
            }

            flash('error', 'Dòng xe đang được phương tiện sử dụng. Hãy ngừng hoạt động thay thế.');

            return Response::redirect(url('admin/models'));
        }
        if ($deleted === null) {
            flash('error', 'Không tìm thấy dòng xe cần xóa.');
        } elseif (!$deleted) {
            flash('error', 'Dòng xe đã được khách hàng sử dụng và không thể xóa. Hãy ngừng hoạt động thay thế.');
        } else {
            flash('success', 'Đã xóa dòng xe.');
        }

        return Response::redirect(url('admin/models'));
    }

    /**
     * @return array{brand_id: int, name: string, year_from: ?int, year_to: ?int}|null
     */
    /**
     * @return array{brand_id: int, name: string, year_from: ?int, year_to: ?int}|null
     */
    private function validatedCatalogModel(Request $request): ?array
    {
        $brandId = $this->positiveId($request->input('brand_id'));
        $name = $request->input('name');
        $yearFromInput = $request->input('year_from', '');
        $yearToInput = $request->input('year_to', '');
        $yearFrom = $yearFromInput === '' ? null : filter_var($yearFromInput, FILTER_VALIDATE_INT);
        $yearTo = $yearToInput === '' ? null : filter_var($yearToInput, FILTER_VALIDATE_INT);

        if (
            $brandId === null
            || !(new VehicleBrand(Database::connection($this->databaseConfig)))->exists($brandId)
            || !is_string($name)
            || !Validator::stringLength(trim($name), 1, 100)
        ) {
            flash('error', 'Vui lòng chọn hãng xe hợp lệ và nhập tên dòng xe từ 1 đến 100 ký tự.');

            return null;
        }

        $maximumYear = (int) date('Y') + 1;
        foreach ([$yearFrom, $yearTo] as $year) {
            if ($year !== null && ($year === false || $year < 1901 || $year > $maximumYear)) {
                flash('error', 'Khoảng năm sản xuất không hợp lệ.');

                return null;
            }
        }
        if ($yearFrom !== null && $yearTo !== null && $yearFrom > $yearTo) {
            flash('error', 'Năm bắt đầu không được lớn hơn năm kết thúc.');

            return null;
        }

        return [
            'brand_id' => $brandId,
            'name' => trim($name),
            'year_from' => $yearFrom === false ? null : $yearFrom,
            'year_to' => $yearTo === false ? null : $yearTo,
        ];
    }

    private function positiveId(mixed $value): ?int
    {
        $id = filter_var($value, FILTER_VALIDATE_INT);

        return $id !== false && $id > 0 ? $id : null;
    }

    private function catalogImage(Request $request, string $field, ?string $current): ?string
    {
        $file = $request->file($field);
        if ($file === null) {
            return $current;
        }
        if (!is_array($file)) {
            throw new InvalidArgumentException('Tệp hình ảnh không hợp lệ.');
        }

        $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($error === UPLOAD_ERR_NO_FILE) {
            return $current;
        }
        if (
            $error !== UPLOAD_ERR_OK
            || !is_string($file['tmp_name'] ?? null)
            || !is_uploaded_file($file['tmp_name'])
            || !is_int($file['size'] ?? null)
            || $file['size'] < 1
            || $file['size'] > 3 * 1024 * 1024
        ) {
            throw new InvalidArgumentException('Hình ảnh phải có dung lượng tối đa 3 MB.');
        }

        $mimeTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $image = getimagesize($file['tmp_name']);
        if (
            !is_string($mime)
            || !isset($mimeTypes[$mime])
            || $image === false
            || ($image['mime'] ?? null) !== $mime
            || $image[0] * $image[1] > 20000000
        ) {
            throw new InvalidArgumentException('Chỉ chấp nhận ảnh JPEG, PNG hoặc WebP hợp lệ.');
        }

        $directory = dirname(__DIR__, 2) . '/public/assets/images/vehicle-catalog';
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new InvalidArgumentException('Không thể tạo thư mục lưu ảnh.');
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $mimeTypes[$mime];
        if (!move_uploaded_file($file['tmp_name'], $directory . '/' . $filename)) {
            throw new InvalidArgumentException('Không thể lưu hình ảnh đã tải lên.');
        }

        return 'assets/images/vehicle-catalog/' . $filename;
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
    public function deleteBrand(Request $request, array $parameters): Response
    {
        $brandId = $this->positiveId($parameters['id'] ?? null);
        if ($brandId === null) {
            flash('error', 'Yêu cầu xóa hãng xe không hợp lệ.');

            return Response::redirect(url('admin/brands'));
        }

        try {
            $deleted = (new VehicleBrand(Database::connection($this->databaseConfig)))->deleteIfUnused($brandId);
        } catch (PDOException $exception) {
            if ($exception->getCode() !== '23000') {
                throw $exception;
            }

            flash('error', 'Hãng xe đang được dòng xe hoặc phương tiện sử dụng.');

            return Response::redirect(url('admin/brands'));
        }
        if ($deleted === null) {
            flash('error', 'Không tìm thấy hãng xe cần xóa.');
        } elseif (!$deleted) {
            flash('error', 'Hãng xe đang có dòng xe hoặc phương tiện liên quan. Hãy ngừng hoạt động thay thế.');
        } else {
            flash('success', 'Đã xóa hãng xe.');
        }

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
