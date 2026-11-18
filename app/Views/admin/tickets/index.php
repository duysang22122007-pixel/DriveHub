<?php

declare(strict_types=1);

$pageTitle = 'Phiếu sửa chữa';
$activeNav = 'tickets';
$pageEyebrow = 'Vận hành';
$pageDescription = 'Quản lý tiến độ sửa chữa, chẩn đoán lỗi và vật tư bảo dưỡng phương tiện.';
$pageActions = '<a class="button" href="' . e(url('admin/tickets/create')) . '">+ Tạo phiếu sửa chữa</a>';

$statusBadgeClass = [
    'received' => 'pending',
    'diagnosing' => 'confirmed',
    'in_progress' => 'checked-in',
    'waiting_parts' => 'pending',
    'quality_check' => 'confirmed',
    'completed' => 'completed',
    'cancelled' => 'cancelled',
];

ob_start();
?>
<?php if (is_string($success) && $success !== ''): ?><p class="notice" role="status"><?= e($success) ?></p><?php endif; ?>
<?php if (is_string($error) && $error !== ''): ?><p class="notice error" role="alert"><?= e($error) ?></p><?php endif; ?>

<div class="panel">
    <div class="panel-head">
        <div>
            <h2 class="panel-title">Danh sách phiếu sửa chữa</h2>
            <p class="panel-description">Tìm thấy <strong><?= e((string) $total) ?></strong> phiếu trong hệ thống.</p>
        </div>
    </div>
    <div class="panel-body">
        <form method="get" action="<?= e(url('admin/tickets')) ?>" class="filter-bar" style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:16px;">
            <div style="flex:1;min-width:220px;">
                <input type="text" name="q" value="<?= e($q) ?>" placeholder="Mã phiếu, biển số xe, tên khách..." style="width:100%;padding:8px 12px;border:1px solid #d1dbe5;border-radius:6px;">
            </div>
            <div style="min-width:180px;">
                <select name="status" style="width:100%;padding:8px 12px;border:1px solid #d1dbe5;border-radius:6px;">
                    <option value="">-- Tất cả trạng thái --</option>
                    <?php foreach ($statuses as $val => $lbl): ?>
                        <option value="<?= e($val) ?>" <?= $statusFilter === $val ? 'selected' : '' ?>><?= e($lbl) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="min-width:160px;">
                <input type="date" name="date" value="<?= e($dateFilter) ?>" style="width:100%;padding:8px 12px;border:1px solid #d1dbe5;border-radius:6px;">
            </div>
            <div>
                <button type="submit" class="button secondary">Lọc dữ liệu</button>
                <?php if ($q !== '' || $statusFilter !== '' || $dateFilter !== ''): ?>
                    <a href="<?= e(url('admin/tickets')) ?>" class="button secondary" style="margin-left:6px;">Đặt lại</a>
                <?php endif; ?>
            </div>
        </form>

        <?php if (empty($tickets)): ?>
            <div class="empty-state" style="text-align:center;padding:48px 16px;color:#667788;">
                <p>Không có phiếu sửa chữa nào phù hợp với bộ lọc.</p>
                <a href="<?= e(url('admin/tickets/create')) ?>" class="button" style="margin-top:12px;">+ Tạo phiếu sửa chữa mới</a>
            </div>
        <?php else: ?>
            <div class="table-container" style="overflow-x:auto;">
                <table class="table" style="width:100%;border-collapse:collapse;font-size:14px;">
                    <thead>
                        <tr style="border-bottom:2px solid #e3eaf1;text-align:left;background:#f8fafc;">
                            <th style="padding:12px 10px;">Mã phiếu</th>
                            <th style="padding:12px 10px;">Khách hàng</th>
                            <th style="padding:12px 10px;">Phương tiện</th>
                            <th style="padding:12px 10px;">Số km</th>
                            <th style="padding:12px 10px;">Trạng thái</th>
                            <th style="padding:12px 10px;">Tổng chi phí (ước tính)</th>
                            <th style="padding:12px 10px;">Thời gian tiếp nhận</th>
                            <th style="padding:12px 10px;text-align:right;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets as $t): ?>
                            <?php
                            $stClass = $statusBadgeClass[$t['status']] ?? 'pending';
                            $stLabel = $statuses[$t['status']] ?? $t['status'];
                            $receivedDate = date('d/m/Y H:i', strtotime((string) $t['received_at']));
                            ?>
                            <tr style="border-bottom:1px solid #e7edf3;">
                                <td style="padding:12px 10px;">
                                    <strong><a href="<?= e(url('admin/tickets/' . (int) $t['id'])) ?>" style="color:#2563eb;text-decoration:none;"><?= e($t['ticket_code']) ?></a></strong>
                                </td>
                                <td style="padding:12px 10px;">
                                    <strong><?= e($t['customer_name']) ?></strong><br>
                                    <small style="color:#667788;"><?= e($t['customer_phone']) ?></small>
                                </td>
                                <td style="padding:12px 10px;">
                                    <strong><?= e($t['brand_name'] . ' ' . $t['model']) ?></strong><br>
                                    <span style="display:inline-block;padding:1px 6px;background:#e2e8f0;border-radius:4px;font-family:monospace;font-size:12px;"><?= e($t['license_plate']) ?></span>
                                </td>
                                <td style="padding:12px 10px;">
                                    <?= number_format((int) $t['mileage'], 0, ',', '.') ?> km
                                </td>
                                <td style="padding:12px 10px;">
                                    <span class="status <?= e($stClass) ?>"><?= e($stLabel) ?></span>
                                </td>
                                <td style="padding:12px 10px;">
                                    <strong style="color:#067647;"><?= number_format((float) $t['total_estimated_cost'], 0, ',', '.') ?> ₫</strong><br>
                                    <small style="color:#667788;"><?= (int) $t['total_items'] ?> hạng mục</small>
                                </td>
                                <td style="padding:12px 10px;color:#475569;">
                                    <?= e($receivedDate) ?>
                                </td>
                                <td style="padding:12px 10px;text-align:right;">
                                    <a href="<?= e(url('admin/tickets/' . (int) $t['id'])) ?>" class="button secondary" style="padding:4px 10px;font-size:13px;">Chi tiết & Xử lý</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($pageCount > 1): ?>
                <div class="pagination" style="display:flex;justify-content:center;gap:6px;margin-top:20px;">
                    <?php for ($p = 1; $p <= $pageCount; $p++): ?>
                        <a href="<?= e(url('admin/tickets?q=' . urlencode($q) . '&status=' . urlencode($statusFilter) . '&date=' . urlencode($dateFilter) . '&page=' . $p)) ?>"
                           class="button <?= $p === $page ? '' : 'secondary' ?>"
                           style="min-width:36px;padding:6px 10px;text-align:center;">
                            <?= $p ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
require BASE_PATH . '/app/Views/admin/_layout.php';
