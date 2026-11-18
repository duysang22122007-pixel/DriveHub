<?php

declare(strict_types=1);

$pageTitle = $editing ? 'Cập nhật phương tiện' : 'Thêm phương tiện';
$activeNav = 'customers';
$pageEyebrow = 'Khách hàng';
$pageDescription = $customer['full_name'] . ' · ' . $customer['email'];
$backUrl = $editing
    ? url('admin/vehicles/' . (int) $customer['id'] . '/' . (int) $vehicleId)
    : url('admin/vehicles?customer_id=' . (int) $customer['id']);
$pageActions = '<a class="button secondary" href="' . e($backUrl) . '">← Quay lại</a>';
$pageStyles = '.vehicle-form{max-width:760px}.vehicle-fields{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.vehicle-fields .field-wide{grid-column:1/-1}.vehicle-fields label{display:grid;gap:7px;color:#29445b;font-size:.9rem;font-weight:700}.vehicle-fields input,.vehicle-fields select{width:100%;min-height:44px;padding:10px 12px;border:1px solid #cbd6e1;border-radius:8px;background:#fff}.field-error{margin:5px 0 0;color:#b42318;font-size:.84rem}@media(max-width:600px){.vehicle-fields{grid-template-columns:1fr}.vehicle-fields .field-wide{grid-column:auto}}';
$pageStyles .= '.vehicle-image-control{grid-column:1/-1}.vehicle-image-control img,.vehicle-image-control .vehicle-image-placeholder{display:grid;place-items:center;width:100%;max-width:360px;height:190px;margin-top:8px;border:1px solid #dce5ee;border-radius:10px;background:#edf3f8;object-fit:cover;color:#668096;font-size:.82rem;font-weight:700}.vehicle-image-control .vehicle-image-placeholder{border-style:dashed}@media(max-width:600px){.vehicle-image-control{grid-column:auto}}';

ob_start();
?>
<section class="panel vehicle-form">
    <div class="panel-head">
        <div>
            <h2 class="panel-title"><?= $editing ? 'Cập nhật thông tin xe' : 'Đăng ký xe cho khách hàng' ?></h2>
            <p class="panel-description">Xe sẽ được liên kết với khách hàng <?= e($customer['full_name']) ?>.</p>
        </div>
    </div>
    <div class="panel-body">
        <form method="post" enctype="multipart/form-data" action="<?= e($editing
            ? url('admin/vehicles/' . (int) $customer['id'] . '/' . (int) $vehicleId . '/update')
            : url('admin/vehicles/' . (int) $customer['id'])) ?>">
            <?= csrf_field() ?>
            <div class="vehicle-fields">
                <div>
                    <label for="brand_id">Hãng xe
                        <select id="brand_id" name="brand_id" required>
                            <option value="">-- Chọn hãng xe --</option>
                            <?php foreach ($brands as $brand): ?>
                                <option value="<?= e((int) $brand['id']) ?>" <?= (string) ($old['brand_id'] ?? '') === (string) $brand['id'] ? 'selected' : '' ?>><?= e($brand['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <?php if (isset($errors['brand_id'])): ?><p class="field-error"><?= e($errors['brand_id']) ?></p><?php endif; ?>
                </div>
                <div>
                    <label for="model">Dòng xe
                        <input id="model" name="model" maxlength="100" required value="<?= e($old['model'] ?? '') ?>">
                    </label>
                    <?php if (isset($errors['model'])): ?><p class="field-error"><?= e($errors['model']) ?></p><?php endif; ?>
                </div>
                <div>
                    <label for="license_plate">Biển số
                        <input id="license_plate" name="license_plate" maxlength="20" required value="<?= e($old['license_plate'] ?? '') ?>">
                    </label>
                    <?php if (isset($errors['license_plate'])): ?><p class="field-error"><?= e($errors['license_plate']) ?></p><?php endif; ?>
                </div>
                <div>
                    <label for="manufacture_year">Năm sản xuất
                        <input id="manufacture_year" name="manufacture_year" type="number" min="1901" max="<?= e((int) date('Y') + 1) ?>" value="<?= e($old['manufacture_year'] ?? '') ?>">
                    </label>
                    <?php if (isset($errors['manufacture_year'])): ?><p class="field-error"><?= e($errors['manufacture_year']) ?></p><?php endif; ?>
                </div>
                <div>
                    <label for="color">Màu xe
                        <input id="color" name="color" maxlength="50" value="<?= e($old['color'] ?? '') ?>">
                    </label>
                    <?php if (isset($errors['color'])): ?><p class="field-error"><?= e($errors['color']) ?></p><?php endif; ?>
                </div>
                <div>
                    <label for="current_mileage">Số km hiện tại
                        <input id="current_mileage" name="current_mileage" type="number" min="0" max="4294967295" required value="<?= e($old['current_mileage'] ?? 0) ?>">
                    </label>
                    <?php if (isset($errors['current_mileage'])): ?><p class="field-error"><?= e($errors['current_mileage']) ?></p><?php endif; ?>
                </div>
                <div class="vehicle-image-control">
                    <label for="vehicle-image">Ảnh phương tiện (JPG, JPEG, PNG hoặc WEBP; tối đa 3 MB)
                        <input id="vehicle-image" type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                    </label>
                    <?php if (isset($errors['image'])): ?><p class="field-error"><?= e($errors['image']) ?></p><?php endif; ?>
                    <div id="vehicle-image-preview" data-placeholder="<?= e(url('assets/images/vehicle-placeholder.svg')) ?>">
                        <img src="<?= e($currentImage ?: url('assets/images/vehicle-placeholder.svg')) ?>" alt="<?= $editing ? 'Ảnh hiện tại của phương tiện' : 'Ảnh mặc định phương tiện' ?>">
                    </div>
                </div>
            </div>
            <div class="actions" style="margin-top:20px">
                <button class="button" type="submit"><?= $editing ? 'Lưu thay đổi' : 'Thêm phương tiện' ?></button>
                <a class="button secondary" href="<?= e($backUrl) ?>">Hủy</a>
            </div>
        </form>
    </div>
</section>
<script>
(function () {
    const input = document.getElementById('vehicle-image');
    const preview = document.getElementById('vehicle-image-preview');
    let previewUrl = null;
    input.addEventListener('change', function () {
        const file = input.files && input.files[0];
        if (!file) return;
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = URL.createObjectURL(file);
        preview.innerHTML = '';
        const image = document.createElement('img');
        image.alt = 'Ảnh xem trước của phương tiện';
        image.src = previewUrl;
        preview.appendChild(image);
    });
    preview.addEventListener('error', function (event) {
        if (event.target.tagName !== 'IMG') return;
        const fallbackUrl = preview.dataset.placeholder;
        if (event.target.getAttribute('src') !== fallbackUrl) {
            event.target.src = fallbackUrl;
            return;
        }
        const placeholder = document.createElement('div');
        placeholder.className = 'vehicle-image-placeholder';
        placeholder.textContent = 'Chưa có hình ảnh';
        event.target.replaceWith(placeholder);
    }, true);
})();
</script>
<?php
$content = ob_get_clean();
require BASE_PATH . '/app/Views/admin/_layout.php';
