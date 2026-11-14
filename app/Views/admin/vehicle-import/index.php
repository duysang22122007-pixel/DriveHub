<?php

declare(strict_types=1);

$pageTitle = 'Đồng bộ thư viện xe';
$activeNav = 'vehicle-import';
$pageEyebrow = 'Danh mục xe';
$pageDescription = 'Nguồn dữ liệu: NHTSA vPIC. Dữ liệu nhập mới được đánh dấu chưa xác minh; thông tin Admin đã tạo hoặc chỉnh sửa được giữ nguyên.';
$pageStyles = '.data-table{min-width:1050px}';
$pageActions = '<a class="button secondary" href="' . e(url('admin/vehicle-import/review')) . '">Duyệt dòng xe</a>';

ob_start();
?>
<?php if (is_string($success) && $success !== ''): ?><p class="notice" role="status"><?= e($success) ?></p><?php endif; ?>
<?php if (is_string($error) && $error !== ''): ?><p class="notice error" role="alert"><?= e($error) ?></p><?php endif; ?>

<section class="panel import-start">
    <div class="panel-head">
        <div>
            <h2 class="panel-title">Bắt đầu đồng bộ</h2>
            <p class="panel-description">Chọn phạm vi dữ liệu cần đồng bộ từ NHTSA.</p>
        </div>
    </div>
    <div class="panel-body">
        <form method="post" action="<?= e(url('admin/vehicle-import')) ?>">
            <?= csrf_field() ?>
            <div class="choices">
                <label class="choice"><input type="radio" name="mode" value="brands" required><span><strong>Hãng xe</strong><small>Nhận danh sách hãng từ NHTSA và xử lý tối đa 5 hãng mỗi lượt.</small></span></label>
                <label class="choice"><input type="radio" name="mode" value="models"><span><strong>Dòng xe</strong><small>Đồng bộ dòng xe cho các hãng đã ghép mã nguồn; một hãng mỗi lượt.</small></span></label>
                <label class="choice"><input type="radio" name="mode" value="all"><span><strong>Toàn bộ</strong><small>Đồng bộ hãng và dòng xe; một hãng mỗi lượt để giới hạn thời gian yêu cầu.</small></span></label>
            </div>
            <label class="choice wide"><input type="checkbox" name="include_trucks" value="1"><span><strong>Thêm hãng có loại Truck</strong><small>Bao gồm xe tải/bán tải theo phân loại NHTSA; lựa chọn này không tự xác định loại của từng model.</small></span></label>
            <p class="hint">Danh sách hãng chỉ gồm các hãng có Passenger Car hoặc MPV; Truck là tùy chọn. NHTSA không phân loại chắc chắn từng model nên model nhập mới vẫn chờ duyệt và không hiển thị cho khách đến khi Admin xác minh.</p>
            <p class="hint">Mỗi lượt lưu trạng thái trong database. Nếu API lỗi hoặc phiên bị gián đoạn, tiếp tục phiên ở bảng lịch sử.</p>
            <div class="actions" style="margin-top:16px"><button class="button" type="submit">Bắt đầu đồng bộ</button></div>
        </form>
    </div>
</section>

<section class="panel import-history">
    <div class="panel-head">
        <div>
            <h2 class="panel-title">Lịch sử và tiến trình</h2>
            <p class="panel-description">Theo dõi các phiên đồng bộ gần đây và tiếp tục phiên bị gián đoạn.</p>
        </div>
    </div>
    <div class="panel-body">
        <div class="table-wrap">
            <?php if ($sessions === []): ?>
                <div class="empty-state">Chưa có phiên đồng bộ.</div>
            <?php else: ?>
                <table class="data-table">
                    <thead><tr><th>Phiên</th><th>Loại</th><th>Trạng thái</th><th>Tiến độ</th><th>Đã thêm</th><th>Trùng</th><th>Lỗi</th><th>Thông tin</th><th>Thời gian</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($sessions as $session):
                        $total = (int) $session['makes_total'];
                        $processed = (int) $session['makes_processed'];
                        $percent = $total > 0 ? min(100, (int) floor($processed * 100 / $total)) : 100;
                        $canContinue = in_array($session['status'], ['queued', 'running'], true);
                        $modeLabels = ['brands' => 'Hãng xe', 'models' => 'Dòng xe', 'all' => 'Toàn bộ'];
                        $statusLabels = [
                            'queued' => 'Đang chờ',
                            'running' => 'Đang chạy',
                            'completed' => 'Hoàn tất',
                            'completed_with_errors' => 'Hoàn tất có lỗi',
                            'failed' => 'Thất bại',
                        ];
                        $statusClasses = [
                            'queued' => 'pending',
                            'running' => 'pending',
                            'completed' => 'confirmed',
                            'completed_with_errors' => 'pending',
                            'failed' => 'rejected',
                        ];
                    ?>
                        <tr>
                            <td><span class="code">#<?= (int) $session['id'] ?></span></td>
                            <td><?= e($modeLabels[$session['mode']] ?? $session['mode']) ?></td>
                            <td><span class="status <?= e($statusClasses[$session['status']] ?? '') ?>"><?= e($statusLabels[$session['status']] ?? $session['status']) ?></span></td>
                            <td class="progress"><?= $processed ?>/<?= $total ?> (<?= $percent ?>%)<div class="track"><span style="width:<?= $percent ?>%"></span></div></td>
                            <td><?= (int) $session['added_count'] ?></td>
                            <td><?= (int) $session['duplicate_count'] ?></td>
                            <td><?= (int) $session['error_count'] ?></td>
                            <td><?= e($session['last_error'] ?: $session['latest_detail'] ?: '—') ?></td>
                            <td><?= e($session['updated_at']) ?></td>
                            <td><?php if ($canContinue): ?><form class="inline-form" method="post" action="<?= e(url('admin/vehicle-import/' . (int) $session['id'] . '/continue')) ?>"><?= csrf_field() ?><button class="button small" type="submit">Tiếp tục</button></form><?php else: ?>—<?php endif; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php
$content = ob_get_clean();
require BASE_PATH . '/app/Views/admin/_layout.php';
