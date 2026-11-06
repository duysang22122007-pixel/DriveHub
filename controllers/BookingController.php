<?php

class BookingController
{
    private const DRAFT_KEY = 'booking_draft';
    private const CSRF_KEY = 'booking_csrf';
    private const FLASH_KEY = 'booking_flash';
    private const DRAFT_TTL = 1800;

    public function index()
    {
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $user = require_role('customer');
        $profile = (new AuthModel())->getCustomerProfile((int) $user['customer_id']);

        if ($profile === null) {
            http_response_code(403);
            exit('Tài khoản chưa có hồ sơ khách hàng hợp lệ.');
        }

        $title = 'DriveHub - Đặt lịch dịch vụ';
        $view = 'booking/create';
        $groups = $this->serviceGroups();
        $timeSlots = $this->timeSlots();
        $timezone = new DateTimeZone('Asia/Ho_Chi_Minh');
        $now = new DateTimeImmutable('now', $timezone);
        $minDate = $now->format('Y-m-d');

        $errors = [];
        $old = $this->defaultInput();
        $old['full_name'] = $profile['full_name'];
        $old['phone'] = $profile['phone'];
        $old['email'] = $profile['email'];
        $draft = null;
        $step = 'form';
        $flash = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $postedStep = (string) ($_POST['step'] ?? 'review');
            $old = $this->inputFromPost();
            $old['full_name'] = $profile['full_name'];
            $old['phone'] = $profile['phone'];
            $old['email'] = $profile['email'];

            if (!$this->verifyCsrf((string) ($_POST['csrf_token'] ?? ''))) {
                $this->rotateCsrf();
                $errors['form'] = 'Phiên làm việc đã hết hạn. Vui lòng kiểm tra lại thông tin và gửi lại.';
                $step = 'form';
            } elseif ($postedStep === 'edit') {
                $draft = $this->getDraft();

                if ($draft !== null) {
                    $old = $draft;
                } else {
                    $errors['form'] = 'Không tìm thấy thông tin xem lại. Vui lòng nhập lại yêu cầu đặt lịch.';
                }

                $step = 'form';
            } elseif ($postedStep === 'confirm') {
                $draft = $this->getDraft();

                if ($draft === null) {
                    $errors['form'] = 'Thông tin xem lại không còn hiệu lực. Vui lòng nhập lại yêu cầu đặt lịch.';
                    $step = 'form';
                } else {
                    [$validated, $confirmErrors] = $this->validate($draft, $groups, $timeSlots, $now);

                    if ($confirmErrors !== []) {
                        $old = $validated;
                        $errors = $confirmErrors;
                        $draft = null;
                        $step = 'form';
                    } else {
                        $service = (new ServiceModel())->getByCode($validated['service_key']);

                        if ($service === null) {
                            $old = $validated;
                            $errors['form'] = 'Dịch vụ đã chọn hiện không khả dụng. Vui lòng chọn dịch vụ khác.';
                            $draft = null;
                            $step = 'form';
                        } else {
                            $pdo = Database::getConnection();
                            $pdo->beginTransaction();

                            try {
                                $carId = (new CarModel())->getOrCreateForCustomer((int) $profile['id'], $validated);
                                $description = implode("\n\n", array_filter([
                                    $validated['problem_description'],
                                    $validated['symptoms'] !== '' ? 'Triệu chứng: ' . $validated['symptoms'] : '',
                                    $validated['note'] !== '' ? 'Ghi chú: ' . $validated['note'] : '',
                                ]));
                                $appointmentId = (new AppointmentModel())->create(
                                    (int) $profile['id'],
                                    $carId,
                                    (int) $service['id'],
                                    $validated['preferred_date'] . ' ' . $validated['preferred_time'] . ':00',
                                    $description
                                );
                                $pdo->commit();
                                $this->clearDraft();
                                $this->rotateCsrf();
                                $this->setFlash([
                                    'type' => 'notice',
                                    'title' => 'Đã ghi nhận lịch hẹn',
                                    'message' => 'Lịch hẹn #' . $appointmentId . ' đã được lưu. Bạn có thể theo dõi trạng thái trong mục Lịch hẹn của tôi.',
                                ]);
                                $this->redirect('complete');
                            } catch (DomainException $exception) {
                                if ($pdo->inTransaction()) {
                                    $pdo->rollBack();
                                }
                                $old = $validated;
                                $errors['form'] = $exception->getMessage();
                                $draft = null;
                                $step = 'form';
                            } catch (Throwable $exception) {
                                if ($pdo->inTransaction()) {
                                    $pdo->rollBack();
                                }
                                throw $exception;
                            }
                        }
                    }
                }
            } else {
                [$validated, $errors] = $this->validate($old, $groups, $timeSlots, $now);

                if ($errors === []) {
                    $this->storeDraft($validated);
                    $this->redirect('review');
                }

                $old = $validated;
                $step = 'form';
            }
        } else {
            $flash = $this->pullFlash();
            $requestedStep = (string) ($_GET['step'] ?? '');

            if ($requestedStep === 'review') {
                $draft = $this->getDraft();

                if ($draft !== null) {
                    $step = 'review';
                } else {
                    $errors['form'] = 'Thông tin xem lại không còn hiệu lực. Vui lòng nhập lại yêu cầu đặt lịch.';
                }
            } elseif ($requestedStep === 'edit') {
                $draft = $this->getDraft();

                if ($draft !== null) {
                    $old = $draft;
                } else {
                    $errors['form'] = 'Không tìm thấy thông tin xem lại. Vui lòng nhập lại yêu cầu đặt lịch.';
                }
            } elseif ($flash !== null) {
                $step = 'complete';
            }

            if ($step === 'form') {
                $carId = filter_var($_GET['car_id'] ?? null, FILTER_VALIDATE_INT);
                $car = $carId ? (new CarModel())->getOwnedCar((int) $carId, (int) $profile['id']) : null;

                if ($car) {
                    $old['car_brand'] = $car['brand'];
                    $old['car_model'] = $car['model'];
                    $old['license_plate'] = $car['license_plate'];
                    $old['manufacture_year'] = (string) ($car['manufacture_year'] ?? '');
                }

                $serviceId = filter_var($_GET['service_id'] ?? null, FILTER_VALIDATE_INT);
                $service = $serviceId ? (new ServiceModel())->getById((int) $serviceId) : null;

                if ($service && isset($groups[$service['service_group']]['services'][$service['service_code']])) {
                    $old['service_group'] = $service['service_group'];
                    $old['service_key'] = $service['service_code'];
                }
            }
        }

        if ($step === 'review') {
            $draft = $this->getDraft();

            if ($draft === null) {
                $step = 'form';
                $errors['form'] = 'Thông tin xem lại không còn hiệu lực. Vui lòng nhập lại yêu cầu đặt lịch.';
            }
        }

        $csrfToken = $this->csrfToken();

        require_once PATH_VIEW_MAIN;
    }

    private function serviceGroups(): array
    {
        return [
            'maintenance' => [
                'label' => 'Bảo dưỡng',
                'long_label' => 'Bảo dưỡng định kỳ và chăm sóc xe',
                'description' => 'Chăm sóc định kỳ, thay thế vật tư và kiểm tra các hệ thống thiết yếu.',
                'services' => [
                    'bao_duong_dinh_ky' => 'Bảo dưỡng định kỳ',
                    'thay_dau_dong_co' => 'Thay dầu động cơ',
                    'kiem_tra_cham_soc_lop' => 'Kiểm tra và chăm sóc lốp',
                    'kiem_tra_thay_ac_quy' => 'Kiểm tra, thay ắc quy',
                    'kiem_tra_he_thong_phanh' => 'Kiểm tra hệ thống phanh',
                    'kiem_tra_dieu_hoa' => 'Kiểm tra điều hòa',
                ],
            ],
            'repair' => [
                'label' => 'Sửa chữa',
                'long_label' => 'Sửa chữa và khắc phục hư hỏng',
                'description' => 'Khắc phục các hư hỏng đã nhận biết hoặc được xác định sau khi kiểm tra.',
                'services' => [
                    'sua_chua_he_thong_phanh' => 'Sửa chữa hệ thống phanh',
                    'sua_chua_dong_co' => 'Sửa chữa động cơ',
                    'sua_chua_dieu_hoa' => 'Sửa chữa điều hòa',
                    'sua_chua_he_thong_dien' => 'Sửa chữa hệ thống điện',
                    'kiem_tra_tieng_on' => 'Kiểm tra tiếng ồn bất thường',
                    'sua_chua_khac' => 'Sửa chữa khác',
                ],
            ],
            'diagnosis' => [
                'label' => 'Chưa xác định',
                'long_label' => 'Chưa xác định – cần kiểm tra, tư vấn',
                'description' => 'Bạn chưa rõ tình trạng xe? DriveHub sẽ kiểm tra và tư vấn hướng xử lý.',
                'services' => [
                    'kiem_tra_tong_quat' => 'Kiểm tra tổng quát',
                    'tu_van_chan_doan' => 'Tư vấn và chẩn đoán tình trạng xe',
                ],
            ],
        ];
    }

    private function timeSlots(): array
    {
        $slots = [];

        for ($hour = 8; $hour <= 22; $hour++) {
            $slots[] = sprintf('%02d:00', $hour);
        }

        return $slots;
    }

    private function defaultInput(): array
    {
        return [
            'service_group' => '',
            'service_key' => '',
            'car_brand' => '',
            'car_model' => '',
            'license_plate' => '',
            'manufacture_year' => '',
            'preferred_date' => '',
            'preferred_time' => '',
            'problem_description' => '',
            'symptoms' => '',
            'note' => '',
            'full_name' => '',
            'phone' => '',
            'email' => '',
        ];
    }

    private function inputFromPost(): array
    {
        $data = $this->defaultInput();

        foreach (array_keys($data) as $key) {
            $data[$key] = trim((string) ($_POST[$key] ?? ''));
        }

        return $data;
    }

    private function validate(array $input, array $groups, array $timeSlots, DateTimeImmutable $now): array
    {
        $data = $this->defaultInput();
        $errors = [];

        foreach (array_keys($data) as $key) {
            $data[$key] = trim((string) ($input[$key] ?? ''));
        }

        $data['license_plate'] = strtoupper(preg_replace('/\s+/u', ' ', $data['license_plate']));
        $data['phone'] = preg_replace('/[\s.\-()]+/u', '', $data['phone']);

        if ($data['service_group'] === '') {
            $errors['service_group'] = 'Vui lòng chọn nhóm nhu cầu.';
        } elseif (!isset($groups[$data['service_group']])) {
            $errors['service_group'] = 'Nhóm nhu cầu không hợp lệ.';
        } elseif ($data['service_key'] === '') {
            $errors['service_key'] = 'Vui lòng chọn dịch vụ cụ thể.';
        } elseif (!isset($groups[$data['service_group']]['services'][$data['service_key']])) {
            $errors['service_key'] = 'Dịch vụ không thuộc nhóm nhu cầu đã chọn.';
        }

        if ($data['full_name'] === '') {
            $errors['full_name'] = 'Vui lòng nhập họ tên.';
        } elseif (mb_strlen($data['full_name']) < 2 || mb_strlen($data['full_name']) > 100) {
            $errors['full_name'] = 'Họ tên phải từ 2 đến 100 ký tự.';
        } elseif (!preg_match('/^[\p{L}\s.\-\']+$/u', $data['full_name'])) {
            $errors['full_name'] = 'Họ tên không được chứa số hoặc ký tự đặc biệt.';
        }

        if ($data['phone'] === '') {
            $errors['phone'] = 'Vui lòng nhập số điện thoại.';
        } elseif (!preg_match('/^(0\d{9}|\+84\d{9})$/', $data['phone'])) {
            $errors['phone'] = 'Số điện thoại chưa đúng định dạng Việt Nam.';
        }

        if ($data['email'] === '') {
            $errors['email'] = 'Vui lòng nhập email.';
        } elseif (mb_strlen($data['email']) > 150 || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email chưa đúng định dạng.';
        }

        if (mb_strlen($data['car_brand']) < 2 || mb_strlen($data['car_brand']) > 80) {
            $errors['car_brand'] = 'Vui lòng nhập hãng xe từ 2 đến 80 ký tự.';
        } elseif (!preg_match('~^[\p{L}\p{N}\s.,/&()+\-]+$~u', $data['car_brand'])) {
            $errors['car_brand'] = 'Hãng xe chứa ký tự không hợp lệ.';
        }

        if (mb_strlen($data['car_model']) < 2 || mb_strlen($data['car_model']) > 120) {
            $errors['car_model'] = 'Vui lòng nhập dòng xe từ 2 đến 120 ký tự.';
        } elseif (!preg_match('~^[\p{L}\p{N}\s.,/&()+\-]+$~u', $data['car_model'])) {
            $errors['car_model'] = 'Dòng xe chứa ký tự không hợp lệ.';
        }

        if (!preg_match('/^[A-Z0-9.\-\s]{4,20}$/', $data['license_plate'])) {
            $errors['license_plate'] = 'Vui lòng nhập biển số xe từ 4 đến 20 ký tự, gồm chữ, số, dấu chấm hoặc gạch nối.';
        }

        if ($data['manufacture_year'] !== '') {
            $currentYear = (int) $now->format('Y');

            if (!preg_match('/^\d{4}$/', $data['manufacture_year'])) {
                $errors['manufacture_year'] = 'Năm sản xuất phải là 4 chữ số.';
            } elseif ((int) $data['manufacture_year'] < 1900 || (int) $data['manufacture_year'] > $currentYear + 1) {
                $errors['manufacture_year'] = 'Năm sản xuất không hợp lệ.';
            }
        }

        $today = $now->format('Y-m-d');
        $date = null;

        if ($data['preferred_date'] === '') {
            $errors['preferred_date'] = 'Vui lòng chọn ngày đặt lịch.';
        } else {
            $date = DateTimeImmutable::createFromFormat('!Y-m-d', $data['preferred_date'], new DateTimeZone('Asia/Ho_Chi_Minh'));

            if ($date === false || $date->format('Y-m-d') !== $data['preferred_date']) {
                $errors['preferred_date'] = 'Ngày đặt lịch không hợp lệ.';
                $date = null;
            } elseif ($data['preferred_date'] < $today) {
                $errors['preferred_date'] = 'Ngày đặt lịch không được ở trong quá khứ.';
            }
        }

        if ($data['preferred_time'] === '') {
            $errors['preferred_time'] = 'Vui lòng chọn khung giờ mong muốn.';
        } elseif (!in_array($data['preferred_time'], $timeSlots, true)) {
            $errors['preferred_time'] = 'Khung giờ không hợp lệ.';
        } elseif ($date !== null && $data['preferred_date'] === $today) {
            $slot = DateTimeImmutable::createFromFormat('!Y-m-d H:i', $data['preferred_date'] . ' ' . $data['preferred_time'], new DateTimeZone('Asia/Ho_Chi_Minh'));

            if ($slot !== false && $slot <= $now) {
                $errors['preferred_time'] = 'Khung giờ hôm nay phải lớn hơn thời điểm hiện tại.';
            }
        }

        if ($data['problem_description'] === '') {
            $errors['problem_description'] = 'Vui lòng mô tả vấn đề hoặc nhu cầu của bạn.';
        } elseif (mb_strlen($data['problem_description']) < 5) {
            $errors['problem_description'] = 'Mô tả vấn đề cần ít nhất 5 ký tự.';
        } elseif (mb_strlen($data['problem_description']) > 2000) {
            $errors['problem_description'] = 'Mô tả vấn đề không vượt quá 2000 ký tự.';
        }

        if (mb_strlen($data['symptoms']) > 2000) {
            $errors['symptoms'] = 'Triệu chứng không vượt quá 2000 ký tự.';
        }

        if (mb_strlen($data['note']) > 1000) {
            $errors['note'] = 'Ghi chú không vượt quá 1000 ký tự.';
        }

        return [$data, $errors];
    }

    private function csrfToken(): string
    {
        if (empty($_SESSION[self::CSRF_KEY]) || !is_string($_SESSION[self::CSRF_KEY])) {
            $this->rotateCsrf();
        }

        return $_SESSION[self::CSRF_KEY];
    }

    private function verifyCsrf(string $token): bool
    {
        return isset($_SESSION[self::CSRF_KEY])
            && is_string($_SESSION[self::CSRF_KEY])
            && $token !== ''
            && hash_equals($_SESSION[self::CSRF_KEY], $token);
    }

    private function rotateCsrf(): void
    {
        $_SESSION[self::CSRF_KEY] = bin2hex(random_bytes(32));
    }

    private function storeDraft(array $data): void
    {
        $_SESSION[self::DRAFT_KEY] = [
            'data' => $data,
            'created_at' => time(),
        ];
    }

    private function getDraft(): ?array
    {
        $stored = $_SESSION[self::DRAFT_KEY] ?? null;

        if (!is_array($stored) || !isset($stored['data'], $stored['created_at'])) {
            return null;
        }

        if (time() - (int) $stored['created_at'] > self::DRAFT_TTL) {
            $this->clearDraft();
            return null;
        }

        return is_array($stored['data']) ? $stored['data'] : null;
    }

    private function clearDraft(): void
    {
        unset($_SESSION[self::DRAFT_KEY]);
    }

    private function setFlash(array $flash): void
    {
        $_SESSION[self::FLASH_KEY] = $flash;
    }

    private function pullFlash(): ?array
    {
        $flash = $_SESSION[self::FLASH_KEY] ?? null;
        unset($_SESSION[self::FLASH_KEY]);

        return is_array($flash) ? $flash : null;
    }

    private function redirect(string $step): void
    {
        header('Location: ' . BASE_URL . '?action=booking&step=' . $step);
        exit;
    }
}
