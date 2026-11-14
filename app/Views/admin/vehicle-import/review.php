<?php

declare(strict_types=1);

$pageTitle = 'Duyệt dòng xe NHTSA';
$activeNav = 'vehicle-review';
$pageEyebrow = 'Danh mục xe';
$pageDescription = 'Loại phương tiện NHTSA chỉ xác định hãng, không xác nhận từng model. Chỉ dòng xe đang hoạt động và đã duyệt mới xuất hiện trong form đăng ký.';
$pageStyles = '.data-table{min-width:1100px}';
$pageActions = '<a class="button secondary" href="' . e(url('admin/vehicle-import')) . '">Quay lại đồng bộ</a>';

ob_start();
?>
<?php if (is_string($success) && $success !== ''): ?><p class="notice" role="status"><?= e($success) ?></p><?php endif; ?>
<?php if (is_string($error) && $error !== ''): ?><p class="notice error" role="alert"><?= e($error) ?></p><?php endif; ?>

<section class="panel review-filters">
    <div class="panel-head">
        <div>
            <h2 class="panel-title">Tìm kiếm và lọc</h2>
            <p class="panel-description">Lọc danh sách dòng xe cần xác minh theo tên, hãng hoặc trạng thái duyệt.</p>
        </div>
    </div>
    <div class="panel-body">
        <form method="get" action="<?= e(url('admin/vehicle-import/review')) ?>" class="filter-bar">
            <div class="filter-grid">
                <div class="filter-field"><label for="q">Tên dòng xe</label><input id="q" type="search" name="q" maxlength="100" value="<?= e($q) ?>" placeholder="Tìm tên dòng xe"></div>
                <div class="filter-field"><label for="brand-id">Hãng xe</label><select id="brand-id" name="brand_id"><option value="">Tất cả hãng</option><?php foreach ($brands as $brand): ?><option value="<?= (int) $brand['id'] ?>" <?= $brandId === (int) $brand['id'] ? 'selected' : '' ?>><?= e($brand['name']) ?></option><?php endforeach; ?></select></div>
                <div class="filter-field"><label for="verification-status">Trạng thái duyệt</label><select id="verification-status" name="status"><option value="unverified" <?= $status === 'unverified' ? 'selected' : '' ?>>Chờ xác minh</option><option value="verified" <?= $status === 'verified' ? 'selected' : '' ?>>Đã xác minh</option><option value="not_suitable" <?= $status === 'not_suitable' ? 'selected' : '' ?>>Không phù hợp</option><option value="all" <?= $status === 'all' ? 'selected' : '' ?>>Tất cả</option></select></div>
                <div class="filter-actions"><button class="btn-search" type="submit">Lọc</button></div>
            </div>
        </form>
    </div>
</section>

<section class="panel review-results">
    <div class="panel-head">
        <div>
            <h2 class="panel-title">Kết quả (<?= (int) $total ?>)</h2>
            <p class="panel-description">Chọn từng dòng hoặc cập nhật trạng thái hàng loạt cho các dòng đã chọn.</p>
        </div>
    </div>
    <div class="panel-body">
        <form id="bulk-review-form" method="post" action="<?= e(url('admin/vehicle-import/review/bulk')) ?>">
            <?= csrf_field() ?>
            <div class="bulk">
                <label for="bulk-status">Áp dụng cho các dòng đã chọn</label>
                <select id="bulk-status" name="status" required><option value="">-- Chọn trạng thái --</option><option value="verified">Duyệt phù hợp</option><option value="not_suitable">Đánh dấu không phù hợp</option></select>
                <button class="button small" type="submit" onclick="return confirm('Xác nhận cập nhật trạng thái các dòng xe đã chọn?')">Cập nhật hàng loạt</button>
            </div>
        </form>
        <div class="table-wrap">
            <?php if ($models === []): ?>
                <div class="empty-state">Không có dòng xe phù hợp bộ lọc.</div>
            <?php else: ?>
                <table class="data-table">
                    <thead><tr><th>Chọn</th><th>Hãng</th><th>Dòng xe</th><th>Trạng thái duyệt</th><th>Trạng thái hoạt động</th><th>Nguồn và mã NHTSA</th><th>Người duyệt</th><th>Thao tác</th></tr></thead>
                    <tbody>
                    <?php foreach ($models as $model): ?>
                        <?php
                            $verificationClass = $model['verification_status'] === 'verified'
                                ? 'verified'
                                : ($model['verification_status'] === 'not_suitable' ? 'not_suitable' : 'pending');
                        ?>
                        <tr>
                            <td><input type="checkbox" name="model_ids[]" value="<?= (int) $model['id'] ?>" form="bulk-review-form" aria-label="Chọn <?= e($model['name']) ?>"></td>
                            <td><?= e($model['brand_name']) ?></td>
                            <td><span class="cell-main"><?= e($model['name']) ?></span><?php if ($model['year_from'] !== null || $model['year_to'] !== null): ?><span class="source"><?= e($model['year_from'] ?? '—') ?>–<?= e($model['year_to'] ?? '—') ?></span><?php endif; ?></td>
                            <td><span class="status <?= e($verificationClass) ?>"><?= $model['verification_status'] === 'verified' ? 'Đã xác minh' : ($model['verification_status'] === 'not_suitable' ? 'Không phù hợp' : 'Chưa xác minh') ?></span></td>
                            <td><span class="status <?= (int) $model['status'] === 1 ? 'active' : 'inactive' ?>"><?= (int) $model['status'] === 1 ? 'Đang hoạt động' : 'Ngừng hoạt động' ?></span></td>
                            <td><div><?= e($model['data_source']) ?></div><div class="source">Make ID: <?= e($model['source_make_id'] ?? '—') ?> · Model ID: <?= e($model['source_model_id'] ?? '—') ?></div></td>
                            <td><?php if ($model['reviewer_name'] !== null): ?><?= e($model['reviewer_name']) ?><div class="source"><?= e($model['reviewed_at']) ?></div><?php else: ?>—<?php endif; ?></td>
                            <td>
                                <div class="row-actions">
                                    <form method="post" action="<?= e(url('admin/vehicle-import/review/' . (int) $model['id'])) ?>">
                                        <?= csrf_field() ?>
                                        <button class="button small" type="submit" name="status" value="verified">Duyệt</button>
                                        <button class="button reject small" type="submit" name="status" value="not_suitable">Không phù hợp</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
            $query = ['q' => $q, 'brand_id' => $brandId ?? '', 'status' => $status];
            $pageUrl = static function (int $number) use ($query): string {
                return url('admin/vehicle-import/review') . '?' . http_build_query($query + ['page' => $number]);
            };
        ?>
        <div class="pagination">
            <span>Trang <?= (int) $page ?> / <?= (int) $pageCount ?></span>
            <nav aria-label="Phân trang" style="display:flex;gap:8px">
                <a class="button secondary small <?= $page <= 1 ? 'disabled' : '' ?>" href="<?= e($pageUrl(max(1, $page - 1))) ?>">Trước</a>
                <a class="button secondary small <?= $page >= $pageCount ? 'disabled' : '' ?>" href="<?= e($pageUrl(min($pageCount, $page + 1))) ?>">Sau</a>
            </nav>
        </div>
    </div>
</section>
<?php
$content = ob_get_clean();
require BASE_PATH . '/app/Views/admin/_layout.php';
