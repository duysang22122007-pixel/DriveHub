<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $editing ? 'Cập nhật xe' : 'Thêm xe' ?> | DriveHub</title>
    <style>
        :root { font-family: Inter, Arial, sans-serif; color:#172b3b; background:#f4f7fb; }
        * { box-sizing:border-box; } body { margin:0; min-height:100vh; display:grid; place-items:center; padding:20px; }
        main { width:min(100%,650px); padding:30px; border:1px solid #e3eaf1; border-radius:16px; background:#fff; box-shadow:0 12px 35px #123b5d0c; }
        h1 { margin:0; color:#123b5d; } p { color:#667788; line-height:1.6; }
        .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:0 16px; }
        label { display:block; margin:15px 0 7px; color:#29445b; font-size:.9rem; font-weight:700; }
        input,select { width:100%; min-height:44px; padding:10px 12px; border:1px solid #cbd6e1; border-radius:8px; background:white; font:inherit; }
        .field-error { margin:5px 0 0; color:#b42318; font-size:.84rem; } .span { grid-column:1/-1; }
        .actions { display:flex; gap:10px; margin-top:23px; } .button { display:inline-flex; align-items:center; justify-content:center; min-height:43px; padding:0 16px; border:0; border-radius:8px; background:#2563eb; color:#fff; text-decoration:none; font:inherit; font-weight:700; cursor:pointer; }
        .button.secondary { background:#e9eff6; color:#123b5d; }
        @media(max-width:540px){main{padding:22px}.form-grid{grid-template-columns:1fr}.span{grid-column:auto}.actions{flex-direction:column}.button{width:100%}}
    </style>
</head>
<body>
<main>
    <h1><?= $editing ? 'Cập nhật thông tin xe' : 'Đăng ký xe mới' ?></h1>
    <p>Thông tin này được lưu trong hồ sơ tài khoản của bạn.</p>
    <form method="post" action="<?= e(url($editing ? 'vehicles/' . (int) $vehicleId . '/update' : 'vehicles')) ?>">
        <?= csrf_field() ?>
        <div class="form-grid">
            <div class="span">
                <label for="brand_id">Hãng xe</label>
                <select id="brand_id" name="brand_id" required>
                    <option value="">-- Chọn hãng xe --</option>
                    <?php foreach ($brands as $brand): ?>
                        <option value="<?= e((int) $brand['id']) ?>" <?= (string) ($old['brand_id'] ?? '') === (string) $brand['id'] ? 'selected' : '' ?>><?= e($brand['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['brand_id'])): ?><p class="field-error"><?= e($errors['brand_id']) ?></p><?php endif; ?>
            </div>
            <div>
                <label for="model">Dòng xe</label>
                <input id="model" name="model" maxlength="100" required value="<?= e($old['model'] ?? '') ?>">
                <?php if (isset($errors['model'])): ?><p class="field-error"><?= e($errors['model']) ?></p><?php endif; ?>
            </div>
            <div>
                <label for="license_plate">Biển số</label>
                <input id="license_plate" name="license_plate" maxlength="20" required value="<?= e($old['license_plate'] ?? '') ?>">
                <?php if (isset($errors['license_plate'])): ?><p class="field-error"><?= e($errors['license_plate']) ?></p><?php endif; ?>
            </div>
            <div>
                <label for="manufacture_year">Năm sản xuất</label>
                <input id="manufacture_year" name="manufacture_year" type="number" min="1901" max="<?= e((int) date('Y') + 1) ?>" value="<?= e($old['manufacture_year'] ?? '') ?>">
                <?php if (isset($errors['manufacture_year'])): ?><p class="field-error"><?= e($errors['manufacture_year']) ?></p><?php endif; ?>
            </div>
            <div>
                <label for="color">Màu xe</label>
                <input id="color" name="color" maxlength="50" value="<?= e($old['color'] ?? '') ?>">
                <?php if (isset($errors['color'])): ?><p class="field-error"><?= e($errors['color']) ?></p><?php endif; ?>
            </div>
            <div class="span">
                <label for="current_mileage">Số km hiện tại</label>
                <input id="current_mileage" name="current_mileage" type="number" min="0" max="4294967295" required value="<?= e($old['current_mileage'] ?? 0) ?>">
                <?php if (isset($errors['current_mileage'])): ?><p class="field-error"><?= e($errors['current_mileage']) ?></p><?php endif; ?>
            </div>
        </div>
        <div class="actions"><button class="button" type="submit"><?= $editing ? 'Lưu thay đổi' : 'Thêm xe' ?></button><a class="button secondary" href="<?= e(url('vehicles')) ?>">Quay lại</a></div>
    </form>
</main>
</body>
</html>
