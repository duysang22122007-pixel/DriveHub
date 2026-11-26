<?php

declare(strict_types=1);

$pageTitle = 'Quản lý hãng xe';
$activeNav = 'brands';
$pageEyebrow = 'Danh mục xe';
$pageDescription = 'Cập nhật danh mục hãng xe dùng khi khách hàng đăng ký phương tiện.';
$pageStyles = '.data-table{min-width:1000px}.brand-entry{display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:center;gap:10px}';
$pageActions = '<a class="button secondary" href="' . e(url('admin/brands/template')) . '">Tải file mẫu Excel</a>'
    . '<a class="button secondary" href="' . e(url('admin/brands/export')) . '">Xuất bảng Excel</a>'
    . '<a class="button" href="#add-brand"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>Thêm hãng xe</a>';

ob_start();
?>
<?php if (is_string($success) && $success !== ''): ?><p class="notice" role="status"><?= e($success) ?></p><?php endif; ?>
<?php if (is_string($error) && $error !== ''): ?><p class="notice error" role="alert"><?= e($error) ?></p><?php endif; ?>

<section class="panel" id="import-brands">
    <div class="panel-head">
        <div>
            <h2 class="panel-title">Nhập / Tự động cập nhật hãng xe từ bảng Excel</h2>
            <p class="panel-description">Tải lên tệp Excel (<code>.xlsx</code>) hoặc <code>.csv</code> gồm cột <strong>Tên hãng xe</strong>, <strong>Trạng thái</strong> (1 = hoạt động, 0 = ngừng) và <strong>Logo</strong> (tùy chọn). Hãng chưa có sẽ được thêm mới; hãng đã có sẽ tự động cập nhật.</p>
        </div>
        <div class="actions">
            <a class="button secondary small" href="<?= e(url('admin/brands/template')) ?>">Tải file mẫu (.xlsx)</a>
            <a class="button ghost small" href="<?= e(url('admin/brands/export')) ?>">Xuất danh sách hiện tại (.xlsx)</a>
        </div>
    </div>
    <div class="panel-body">
        <form method="post" action="<?= e(url('admin/brands/import')) ?>" enctype="multipart/form-data" class="form-grid">
            <?= csrf_field() ?>
            <div class="field" style="grid-column:span 2">
                <label for="excel-file">Chọn bảng Excel (.xlsx hoặc .csv, tối đa 5 MB)</label>
                <input id="excel-file" type="file" name="excel_file" accept=".xlsx,.csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/csv" required>
            </div>
            <div class="form-actions">
                <button class="button" type="submit">Tải lên &amp; Tự động cập nhật</button>
            </div>
        </form>
    </div>
</section>

<section class="panel catalog-create" id="add-brand">
    <div class="panel-head">
        <div>
            <h2 class="panel-title">Thêm hãng xe</h2>
            <p class="panel-description">Bổ sung hãng xe mới vào danh mục đăng ký phương tiện.</p>
        </div>
    </div>
    <div class="panel-body">
        <form method="post" action="<?= e(url('admin/brands')) ?>" enctype="multipart/form-data" class="form-grid">
            <?= csrf_field() ?>
            <div class="field"><label for="new-name">Tên hãng xe</label><input id="new-name" name="name" maxlength="100" required></div>
            <div class="field"><label for="new-logo">Logo (JPEG, PNG hoặc WebP; tối đa 3 MB)</label><input id="new-logo" type="file" name="logo" accept="image/jpeg,image/png,image/webp"></div>
            <div class="form-actions"><button class="button" type="submit">Thêm hãng xe</button></div>
        </form>
    </div>
</section>

<section class="panel catalog-list">
    <div class="panel-head">
        <div>
            <h2 class="panel-title">Danh sách hãng xe</h2>
            <p class="panel-description">Chỉnh sửa tên, logo hoặc trạng thái hoạt động của từng hãng.</p>
        </div>
    </div>
    <div class="panel-body">
        <form method="get" action="<?= e(url('admin/brands')) ?>" class="filter-bar">
            <input type="search" name="q" value="<?= e($q) ?>" placeholder="Tìm tên hãng xe" aria-label="Tìm tên hãng xe">
            <button class="btn-search" type="submit">Tìm kiếm</button>
            <?php if ($q !== ''): ?><a class="btn-reset" href="<?= e(url('admin/brands')) ?>">Xóa lọc</a><?php endif; ?>
        </form>
        <div class="table-wrap">
            <?php if ($brands === []): ?>
                <div class="empty-state"><?= $q === '' ? 'Chưa có hãng xe.' : 'Không tìm thấy hãng xe phù hợp.' ?></div>
            <?php else: ?>
                <table class="data-table">
                    <thead><tr><th>Logo</th><th>Hãng xe / Chỉnh sửa</th><th>Thao tác</th></tr></thead>
                    <tbody>
                    <?php foreach ($brands as $brand): ?>
                        <tr>
                            <td><?php if (is_string($brand['logo']) && $brand['logo'] !== ''): ?><img class="thumb" src="<?= e(url($brand['logo'])) ?>" alt="Logo <?= e($brand['name']) ?>"><?php else: ?>—<?php endif; ?></td>
                            <td colspan="2">
                                <div class="brand-entry">
                                    <form method="post" action="<?= e(url('admin/brands/' . (int) $brand['id'] . '/update')) ?>" enctype="multipart/form-data" class="editor-row">
                                        <?= csrf_field() ?>
                                        <div class="field"><label for="brand-name-<?= (int) $brand['id'] ?>">Tên hãng xe</label><input id="brand-name-<?= (int) $brand['id'] ?>" name="name" maxlength="100" required value="<?= e($brand['name']) ?>"></div>
                                        <div class="field"><label for="brand-logo-<?= (int) $brand['id'] ?>">Thay logo</label><input id="brand-logo-<?= (int) $brand['id'] ?>" type="file" name="logo" accept="image/jpeg,image/png,image/webp"><span class="help">Để trống để giữ logo hiện tại.</span></div>
                                        <div class="field"><label for="brand-status-<?= (int) $brand['id'] ?>">Trạng thái</label><select id="brand-status-<?= (int) $brand['id'] ?>" name="status"><option value="1" <?= (int) $brand['status'] === 1 ? 'selected' : '' ?>>Đang hoạt động</option><option value="0" <?= (int) $brand['status'] !== 1 ? 'selected' : '' ?>>Ngừng hoạt động</option></select></div>
                                        <div class="editor-actions"><button class="button" type="submit">Lưu</button></div>
                                    </form>
                                    <form method="post" action="<?= e(url('admin/brands/' . (int) $brand['id'] . '/delete')) ?>" class="inline-form" onsubmit="return confirm('Xóa hãng xe này? Hãng đang có dòng xe hoặc phương tiện liên quan sẽ được giữ nguyên.');">
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
