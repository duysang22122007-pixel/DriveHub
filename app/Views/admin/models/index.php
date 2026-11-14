<?php

declare(strict_types=1);

$pageTitle = 'Quản lý dòng xe';
$activeNav = 'models';
$pageEyebrow = 'Danh mục xe';
$pageDescription = 'Dòng xe đã dùng sẽ được bảo vệ khỏi xóa; hãy ngừng hoạt động để ẩn khỏi đăng ký mới.';
$pageStyles = '.data-table{min-width:1100px}.entry{display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:center;gap:10px}@media(min-width:821px){.filter-grid{grid-template-columns:minmax(220px,2fr) minmax(180px,1fr) auto}}';
$pageActions = '<a class="button" href="#add-model"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>Thêm dòng xe</a>';

ob_start();
?>
<?php if (is_string($success) && $success !== ''): ?><p class="notice" role="status"><?= e($success) ?></p><?php endif; ?>
<?php if (is_string($error) && $error !== ''): ?><p class="notice error" role="alert"><?= e($error) ?></p><?php endif; ?>

<section class="panel catalog-create" id="add-model">
    <div class="panel-head">
        <div>
            <h2 class="panel-title">Thêm dòng xe</h2>
            <p class="panel-description">Bổ sung dòng xe mới và gắn với hãng xe tương ứng.</p>
        </div>
    </div>
    <div class="panel-body">
        <form method="post" action="<?= e(url('admin/models')) ?>" enctype="multipart/form-data" class="form-grid">
            <?= csrf_field() ?>
            <div class="field"><label for="new-brand">Hãng xe</label><select id="new-brand" name="brand_id" required><option value="">-- Chọn hãng xe --</option><?php foreach ($brands as $brand): ?><option value="<?= (int) $brand['id'] ?>"><?= e($brand['name']) ?><?= (int) $brand['status'] === 1 ? '' : ' (ngừng hoạt động)' ?></option><?php endforeach; ?></select></div>
            <div class="field"><label for="new-name">Tên dòng xe</label><input id="new-name" name="name" maxlength="100" required></div>
            <div class="field"><label for="new-year-from">Năm bắt đầu (tùy chọn)</label><input id="new-year-from" name="year_from" type="number" min="1901" max="<?= (int) date('Y') + 1 ?>"></div>
            <div class="field"><label for="new-year-to">Năm kết thúc (tùy chọn)</label><input id="new-year-to" name="year_to" type="number" min="1901" max="<?= (int) date('Y') + 1 ?>"></div>
            <div class="field"><label for="new-image">Hình ảnh (JPEG, PNG hoặc WebP; tối đa 3 MB)</label><input id="new-image" type="file" name="image" accept="image/jpeg,image/png,image/webp"><span class="help">Có thể bỏ trống năm và hình ảnh nếu chưa có thông tin.</span></div>
            <div class="form-actions"><button class="button" type="submit">Thêm dòng xe</button></div>
        </form>
    </div>
</section>

<section class="panel catalog-list">
    <div class="panel-head">
        <div>
            <h2 class="panel-title">Danh sách dòng xe</h2>
            <p class="panel-description">Chỉnh sửa thông tin, hình ảnh và trạng thái hiển thị của từng dòng xe.</p>
        </div>
    </div>
    <div class="panel-body">
        <form method="get" action="<?= e(url('admin/models')) ?>" class="filter-bar">
            <div class="filter-grid">
                <div class="filter-field">
                    <label for="model-q">Tìm kiếm</label>
                    <input id="model-q" type="search" name="q" value="<?= e($q) ?>" placeholder="Tìm dòng xe hoặc hãng xe">
                </div>
                <div class="filter-field">
                    <label for="model-brand-filter">Hãng xe</label>
                    <select id="model-brand-filter" name="brand_id">
                        <option value="">Tất cả hãng xe</option>
                        <?php foreach ($brands as $brand): ?><option value="<?= (int) $brand['id'] ?>" <?= $brandId === (int) $brand['id'] ? 'selected' : '' ?>><?= e($brand['name']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-actions">
                    <button class="btn-search" type="submit">Tìm kiếm</button>
                    <?php if ($q !== '' || $brandId !== null): ?><a class="btn-reset" href="<?= e(url('admin/models')) ?>">Xóa lọc</a><?php endif; ?>
                </div>
            </div>
        </form>
        <div class="table-wrap">
            <?php if ($models === []): ?>
                <div class="empty-state"><?= $q === '' && $brandId === null ? 'Chưa có dòng xe.' : 'Không tìm thấy dòng xe phù hợp.' ?></div>
            <?php else: ?>
                <table class="data-table">
                    <thead><tr><th>Hình ảnh</th><th>Dòng xe / Chỉnh sửa</th><th>Thao tác</th></tr></thead>
                    <tbody>
                    <?php foreach ($models as $model): ?>
                        <tr>
                            <td><?php if (is_string($model['image']) && $model['image'] !== ''): ?><img class="thumb" src="<?= e(url($model['image'])) ?>" alt="<?= e($model['name']) ?>"><?php else: ?>—<?php endif; ?></td>
                            <td colspan="2">
                                <div class="entry">
                                    <form method="post" action="<?= e(url('admin/models/' . (int) $model['id'] . '/update')) ?>" enctype="multipart/form-data" class="editor-row models">
                                        <?= csrf_field() ?>
                                        <div class="field"><label for="model-brand-<?= (int) $model['id'] ?>">Hãng xe</label><select id="model-brand-<?= (int) $model['id'] ?>" name="brand_id" required><?php foreach ($brands as $brand): ?><option value="<?= (int) $brand['id'] ?>" <?= (int) $brand['id'] === (int) $model['brand_id'] ? 'selected' : '' ?>><?= e($brand['name']) ?></option><?php endforeach; ?></select></div>
                                        <div class="field"><label for="model-name-<?= (int) $model['id'] ?>">Tên</label><input id="model-name-<?= (int) $model['id'] ?>" name="name" maxlength="100" required value="<?= e($model['name']) ?>"></div>
                                        <div class="field"><label for="model-from-<?= (int) $model['id'] ?>">Năm đầu</label><input id="model-from-<?= (int) $model['id'] ?>" name="year_from" type="number" min="1901" max="<?= (int) date('Y') + 1 ?>" value="<?= e($model['year_from']) ?>"></div>
                                        <div class="field"><label for="model-to-<?= (int) $model['id'] ?>">Năm cuối</label><input id="model-to-<?= (int) $model['id'] ?>" name="year_to" type="number" min="1901" max="<?= (int) date('Y') + 1 ?>" value="<?= e($model['year_to']) ?>"></div>
                                        <div class="field"><label for="model-image-<?= (int) $model['id'] ?>">Thay ảnh</label><input id="model-image-<?= (int) $model['id'] ?>" type="file" name="image" accept="image/jpeg,image/png,image/webp"><span class="help">Để trống để giữ ảnh.</span></div>
                                        <div class="field"><label for="model-status-<?= (int) $model['id'] ?>">Trạng thái</label><select id="model-status-<?= (int) $model['id'] ?>" name="status"><option value="1" <?= (int) $model['status'] === 1 ? 'selected' : '' ?>>Hoạt động</option><option value="0" <?= (int) $model['status'] !== 1 ? 'selected' : '' ?>>Ngừng</option></select></div>
                                        <div class="editor-actions"><button class="button" type="submit">Lưu</button></div>
                                    </form>
                                    <form method="post" action="<?= e(url('admin/models/' . (int) $model['id'] . '/delete')) ?>" class="inline-form" onsubmit="return confirm('Xóa dòng xe này? Nếu đã được sử dụng, hệ thống sẽ từ chối và dữ liệu được giữ nguyên.');">
                                        <?= csrf_field() ?>
                                        <button class="button danger small" type="submit">Xóa</button>
                                    </form>
                                </div>
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
$content = ob_get_clean();
require BASE_PATH . '/app/Views/admin/_layout.php';
