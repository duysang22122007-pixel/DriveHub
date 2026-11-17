<?php

declare(strict_types=1);

$pageTitle = 'Lịch hoạt động gara';
$activeNav = 'garage-schedule';
$pageEyebrow = 'Vận hành';
$pageDescription = 'Cấu hình ngày làm việc, khung giờ, sức chứa và ngày nghỉ đột xuất.';
$pageActions = '<a class="button secondary" href="' . e(url('admin/appointments')) . '">Danh sách lịch hẹn</a>';
$today = (new DateTimeImmutable('today', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d');

ob_start();
?>
<?php if (is_string($success) && $success !== ''): ?><p class="notice" role="status"><?= e($success) ?></p><?php endif; ?>
<?php if (is_string($error) && $error !== ''): ?><p class="notice error" role="alert"><?= e($error) ?></p><?php endif; ?>

<section class="panel">
    <div class="panel-head">
        <div>
            <h2 class="panel-title">Lịch cố định theo tuần</h2>
            <p class="panel-description">Giờ đóng cửa là mốc cuối có thể đặt. Sức chứa ngày chỉ tính lịch chờ xác nhận, đã xác nhận hoặc đã tiếp nhận.</p>
        </div>
    </div>
    <div class="panel-body">
        <form method="post" action="<?= e(url('admin/garage-schedule')) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="weekly">
            <div class="table-wrap">
                <table class="data-table" style="min-width:1000px">
                    <thead>
                    <tr>
                        <th>Ngày</th>
                        <th>Hoạt động</th>
                        <th>Mở cửa</th>
                        <th>Đóng cửa</th>
                        <th>Khoảng khung (phút)</th>
                        <th>Lịch / khung</th>
                        <th>Lịch / ngày</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($schedule as $day): ?>
                        <?php
                            $weekday = (int) $day['weekday'];
                            $values = is_array($day) ? $day : [];
                            $opensAt = substr((string) ($values['opens_at'] ?? ''), 0, 5);
                            $closesAt = substr((string) ($values['closes_at'] ?? ''), 0, 5);
                            $isOpen = (int) ($values['is_open'] ?? 0) === 1;
                        ?>
                        <tr>
                            <td><strong><?= e($weekdayNames[$weekday] ?? 'Ngày ' . $weekday) ?></strong></td>
                            <td>
                                <input type="hidden" name="schedule[<?= $weekday ?>][is_open]" value="0">
                                <label><input type="checkbox" name="schedule[<?= $weekday ?>][is_open]" value="1" <?= $isOpen ? 'checked' : '' ?>> Mở cửa</label>
                            </td>
                            <td><input class="schedule-input" type="time" name="schedule[<?= $weekday ?>][opens_at]" value="<?= e($opensAt) ?>" required></td>
                            <td><input class="schedule-input" type="time" name="schedule[<?= $weekday ?>][closes_at]" value="<?= e($closesAt) ?>" required></td>
                            <td><input class="schedule-input" type="number" name="schedule[<?= $weekday ?>][slot_interval_minutes]" min="5" max="240" value="<?= e((int) $values['slot_interval_minutes']) ?>" required></td>
                            <td><input class="schedule-input" type="number" name="schedule[<?= $weekday ?>][slot_capacity]" min="1" max="100" value="<?= e((int) $values['slot_capacity']) ?>" required></td>
                            <td><input class="schedule-input" type="number" name="schedule[<?= $weekday ?>][daily_capacity]" min="1" max="1000" value="<?= e((int) $values['daily_capacity']) ?>" required></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <p class="hint">Các thay đổi áp dụng ngay cho những lịch mới. Lịch đã nhận không bị xóa; nếu giảm sức chứa thấp hơn số lịch hiện có, hệ thống sẽ chặn lịch mới cho đến khi còn chỗ.</p>
            <div class="actions" style="margin-top:16px"><button class="button" type="submit">Lưu lịch hoạt động</button></div>
        </form>
    </div>
</section>

<section class="panel">
    <div class="panel-head">
        <div>
            <h2 class="panel-title">Ngày nghỉ đặc biệt</h2>
            <p class="panel-description">Ngày đã thêm sẽ không nhận lịch mới. Các lịch hiện có vẫn được giữ nguyên để Admin xử lý.</p>
        </div>
    </div>
    <div class="panel-body">
        <form method="post" action="<?= e(url('admin/garage-schedule')) ?>" class="form-grid">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="close">
            <label class="field"><span>Ngày nghỉ</span><input type="date" name="closed_date" min="<?= e($today) ?>" required></label>
            <label class="field"><span>Lý do (không bắt buộc)</span><input type="text" name="reason" maxlength="255" placeholder="Ví dụ: nghỉ lễ, bảo trì gara"></label>
            <div class="form-actions"><button class="button" type="submit">Thêm ngày nghỉ</button></div>
        </form>

        <div class="table-wrap" style="margin-top:18px">
            <?php if ($closures === []): ?>
                <p class="empty">Chưa có ngày nghỉ đặc biệt.</p>
            <?php else: ?>
                <table class="data-table">
                    <thead><tr><th>Ngày</th><th>Lý do</th><th>Thao tác</th></tr></thead>
                    <tbody>
                    <?php foreach ($closures as $closure): ?>
                        <tr>
                            <td><?= e(date('d/m/Y', strtotime((string) $closure['closed_date']))) ?></td>
                            <td><?= e($closure['reason'] ?: 'Không ghi lý do') ?></td>
                            <td>
                                <form method="post" action="<?= e(url('admin/garage-schedule')) ?>" class="inline-form">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="closed_date" value="<?= e($closure['closed_date']) ?>">
                                    <button class="button danger small" type="submit">Gỡ ngày nghỉ</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php
$pageStyles = '.schedule-input{width:128px;min-height:36px;padding:6px 8px;border:1px solid var(--line-strong);border-radius:7px;background:#fff;color:var(--ink);font:inherit;font-size:.84rem}.data-table td label{white-space:nowrap}';
$content = ob_get_clean();
require BASE_PATH . '/app/Views/admin/_layout.php';
