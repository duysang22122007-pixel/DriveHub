<?php

declare(strict_types=1);

$pageTitle = 'Quản lý dịch vụ';
$activeNav = 'services';
$pageEyebrow = 'Danh mục dịch vụ';
$pageDescription = 'Quản lý nhóm dịch vụ và danh sách đang hiển thị cho khách hàng. Dịch vụ đã ngừng vẫn được giữ để bảo toàn lịch sử.';
$pageStyles = '.service-editor{min-width:860px}.category-editor{display:grid;grid-template-columns:minmax(180px,1fr) minmax(220px,2fr) auto auto;align-items:end;gap:10px}.service-row{display:grid;grid-template-columns:minmax(180px,1.3fr) minmax(160px,1fr) minmax(120px,.8fr) minmax(100px,.6fr) minmax(140px,.8fr) auto;align-items:end;gap:9px;padding:14px 0;border-bottom:1px solid #edf1f5}.service-row:last-child{border-bottom:0}.service-row .field{margin:0}.service-row label{display:block;margin-bottom:5px;color:#526678;font-size:.72rem;font-weight:800}.service-row input,.service-row select,.category-editor input,.category-editor textarea{width:100%;min-height:38px;padding:7px 9px;border:1px solid #d1dbe5;border-radius:7px;font:inherit}.service-row textarea,.category-editor textarea{min-height:38px;resize:vertical}.service-checkbox{display:flex;align-items:center;gap:6px;min-height:38px;color:#526678;font-size:.82rem;font-weight:700}.service-checkbox input{width:auto;min-height:auto}.bulk-move{display:flex;flex-wrap:wrap;align-items:end;gap:9px;padding:12px;border:1px solid #e3eaf1;border-radius:8px;background:#f8fafc}.bulk-move select{min-width:220px;min-height:38px;padding:7px;border:1px solid #d1dbe5;border-radius:7px;background:#fff}@media(max-width:900px){.category-editor{grid-template-columns:1fr}.service-row{grid-template-columns:1fr 1fr}.service-row>.service-checkbox{grid-column:1/-1}}';

ob_start();
?>
<?php if (is_string($success) && $success !== ''): ?><p class="notice" role="status"><?= e($success) ?></p><?php endif; ?>
<?php if (is_string($error) && $error !== ''): ?><p class="notice error" role="alert"><?= e($error) ?></p><?php endif; ?>

<section class="panel">
    <div class="panel-head"><div><h2 class="panel-title">Thêm dịch vụ</h2><p class="panel-description">Dịch vụ đang hoạt động sẽ được đồng bộ lên trang khách hàng và biểu mẫu đặt lịch.</p></div></div>
    <div class="panel-body">
        <?php if ($categories === []): ?>
            <p class="empty-state">Hãy tạo nhóm dịch vụ trước khi thêm dịch vụ.</p>
        <?php else: ?>
            <form method="post" action="<?= e(url('admin/services')) ?>" class="form-grid">
                <?= csrf_field() ?>
                <div class="field"><label for="new-service-name">Tên dịch vụ</label><input id="new-service-name" name="name" maxlength="150" required></div>
                <div class="field"><label for="new-service-category">Nhóm dịch vụ</label><select id="new-service-category" name="category_id" required><?php foreach ($categories as $category): ?><?php if ((int) $category['status'] === 1): ?><option value="<?= (int) $category['id'] ?>"><?= e($category['name']) ?></option><?php endif; ?><?php endforeach; ?></select></div>
                <div class="field"><label for="new-service-price">Giá tham khảo (VND)</label><input id="new-service-price" name="price" type="number" min="0" max="9999999999.99" step="0.01" value="0" required></div>
                <div class="field"><label for="new-service-duration">Thời lượng (phút; 0 nếu chưa xác định)</label><input id="new-service-duration" name="duration" type="number" min="0" max="65535" value="0" required></div>
                <div class="field"><label for="new-service-description">Mô tả</label><textarea id="new-service-description" name="description" maxlength="5000" rows="3"></textarea></div>
                <div class="form-actions"><button class="button" type="submit">Thêm dịch vụ</button></div>
            </form>
        <?php endif; ?>
    </div>
</section>

<section class="panel">
    <div class="panel-head"><div><h2 class="panel-title">Nhóm / thư mục dịch vụ</h2><p class="panel-description">Nhóm đang được dùng không thể xóa; hãy chuyển dịch vụ trước để tránh mất liên kết lịch sử.</p></div></div>
    <div class="panel-body">
        <form method="post" action="<?= e(url('admin/service-categories')) ?>" class="category-editor">
            <?= csrf_field() ?>
            <div class="field"><label for="new-category-name">Tên nhóm mới</label><input id="new-category-name" name="name" maxlength="150" required></div>
            <div class="field"><label for="new-category-description">Mô tả</label><textarea id="new-category-description" name="description" maxlength="5000" rows="1"></textarea></div>
            <button class="button" type="submit">Tạo nhóm</button>
        </form>
        <?php if ($categories === []): ?><div class="empty-state">Chưa có nhóm dịch vụ.</div><?php else: ?>
            <div class="table-wrap" style="margin-top:16px">
                <table class="data-table">
                    <thead><tr><th>Nhóm</th><th>Mô tả</th><th>Số dịch vụ</th><th>Trạng thái</th><th>Thao tác</th></tr></thead>
                    <tbody>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td colspan="5">
                                <form method="post" action="<?= e(url('admin/service-categories/' . (int) $category['id'] . '/update')) ?>" class="category-editor">
                                    <?= csrf_field() ?>
                                    <div class="field"><label for="category-name-<?= (int) $category['id'] ?>">Tên nhóm</label><input id="category-name-<?= (int) $category['id'] ?>" name="name" maxlength="150" required value="<?= e($category['name']) ?>"></div>
                                    <div class="field"><label for="category-description-<?= (int) $category['id'] ?>">Mô tả</label><textarea id="category-description-<?= (int) $category['id'] ?>" name="description" maxlength="5000" rows="1"><?= e($category['description']) ?></textarea></div>
                                    <span class="muted"><?= (int) $category['service_count'] ?> dịch vụ · <?= (int) $category['status'] === 1 ? 'Đang hoạt động' : 'Đã ẩn' ?></span>
                                    <div class="actions">
                                        <button class="button small" type="submit">Lưu nhóm</button>
                                        <button class="button danger small" type="submit" form="delete-category-<?= (int) $category['id'] ?>" <?= (int) $category['service_count'] > 0 ? 'disabled title="Nhóm còn dịch vụ"' : '' ?>>Xóa</button>
                                    </div>
                                </form>
                                <form id="delete-category-<?= (int) $category['id'] ?>" method="post" action="<?= e(url('admin/service-categories/' . (int) $category['id'] . '/delete')) ?>" hidden><?= csrf_field() ?></form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="panel">
    <div class="panel-head"><div><h2 class="panel-title">Dịch vụ theo nhóm</h2><p class="panel-description">Chọn checkbox để chuyển nhiều dịch vụ sang nhóm khác; có thể sửa trạng thái từng dịch vụ.</p></div></div>
    <div class="panel-body">
        <?php if ($categories === []): ?><div class="empty-state">Chưa có nhóm để hiển thị dịch vụ.</div><?php else: ?>
            <form id="bulk-move" method="post" action="<?= e(url('admin/services/move')) ?>" class="bulk-move">
                <?= csrf_field() ?>
                <label for="bulk-category"><strong>Chuyển các dịch vụ đã chọn sang</strong></label>
                <select id="bulk-category" name="category_id" required>
                    <option value="">Chọn nhóm đích</option>
                    <?php foreach ($categories as $category): ?><?php if ((int) $category['status'] === 1): ?><option value="<?= (int) $category['id'] ?>"><?= e($category['name']) ?></option><?php endif; ?><?php endforeach; ?>
                </select>
                <button class="button secondary" type="submit">Chuyển nhóm</button>
            </form>
            <?php foreach ($categories as $category): ?>
                <?php $groupServices = $servicesByCategory[(int) $category['id']] ?? []; ?>
                <section class="panel" aria-label="Nhóm <?= e($category['name']) ?>">
                    <div class="panel-head"><div><h3 class="panel-title"><?= e($category['name']) ?><?= (int) $category['status'] !== 1 ? ' · Đã ẩn' : '' ?></h3><p class="panel-description"><?= (int) $category['service_count'] ?> dịch vụ</p></div></div>
                    <div class="panel-body">
                        <?php if ($groupServices === []): ?><div class="empty-state">Nhóm này chưa có dịch vụ.</div><?php else: ?>
                            <?php foreach ($groupServices as $service): ?>
                                <div class="service-row">
                                    <label class="service-checkbox"><input type="checkbox" name="service_ids[]" value="<?= (int) $service['id'] ?>" form="bulk-move"> Chọn chuyển</label>
                                    <form method="post" action="<?= e(url('admin/services/' . (int) $service['id'] . '/update')) ?>" class="service-row" style="display:contents">
                                        <?= csrf_field() ?>
                                        <div class="field"><label for="service-name-<?= (int) $service['id'] ?>">Tên dịch vụ</label><input id="service-name-<?= (int) $service['id'] ?>" name="name" maxlength="150" required value="<?= e($service['name']) ?>"></div>
                                        <div class="field"><label for="service-category-<?= (int) $service['id'] ?>">Nhóm</label><select id="service-category-<?= (int) $service['id'] ?>" name="category_id" required><?php foreach ($categories as $option): ?><?php if ((int) $option['status'] === 1): ?><option value="<?= (int) $option['id'] ?>" <?= (int) $option['id'] === (int) $service['category_id'] ? 'selected' : '' ?>><?= e($option['name']) ?></option><?php endif; ?><?php endforeach; ?></select></div>
                                        <div class="field"><label for="service-price-<?= (int) $service['id'] ?>">Giá</label><input id="service-price-<?= (int) $service['id'] ?>" name="price" type="number" min="0" max="9999999999.99" step="0.01" required value="<?= e($service['price']) ?>"></div>
                                        <div class="field"><label for="service-duration-<?= (int) $service['id'] ?>">Phút</label><input id="service-duration-<?= (int) $service['id'] ?>" name="duration" type="number" min="0" max="65535" required value="<?= (int) $service['duration'] ?>"></div>
                                        <div class="field"><label for="service-status-<?= (int) $service['id'] ?>">Trạng thái</label><select id="service-status-<?= (int) $service['id'] ?>" name="status"><option value="active" <?= $service['status'] === 'active' ? 'selected' : '' ?>>Đang hoạt động</option><option value="inactive" <?= $service['status'] === 'inactive' ? 'selected' : '' ?>>Đã ngừng</option></select></div>
                                        <div class="actions"><button class="button small" type="submit">Lưu</button></div>
                                        <div class="field" style="grid-column:2/-1"><label for="service-description-<?= (int) $service['id'] ?>">Mô tả</label><textarea id="service-description-<?= (int) $service['id'] ?>" name="description" maxlength="5000" rows="2"><?= e($service['description']) ?></textarea></div>
                                    </form>
                                    <?php if ($service['status'] === 'active'): ?>
                                        <form method="post" action="<?= e(url('admin/services/' . (int) $service['id'] . '/delete')) ?>" onsubmit="return confirm('Ngừng hiển thị dịch vụ này với khách hàng? Lịch sử sẽ được giữ nguyên.');">
                                            <?= csrf_field() ?><button class="button danger small" type="submit">Ngừng</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>
<?php
$content = ob_get_clean();
require BASE_PATH . '/app/Views/admin/_layout.php';
