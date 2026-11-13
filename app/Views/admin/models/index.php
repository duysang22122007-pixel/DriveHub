<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản lý dòng xe | AutoCare Hub</title>
    <style>
        :root{font-family:Inter,Arial,sans-serif;color:#172b3b;background:#f4f7fb}*{box-sizing:border-box}body{margin:0}header{background:#123b5d;color:#fff}
        .top,main{width:min(1240px,calc(100% - 36px));margin:auto}.top{min-height:68px;display:flex;align-items:center;justify-content:space-between;gap:15px}.brand{color:#fff;font-size:1.1rem;font-weight:800;text-decoration:none}.top nav{display:flex;align-items:center;gap:18px}.top a{color:#fff;text-decoration:none}
        main{padding:34px 0 60px}h1{margin:0;color:#123b5d}.sub{color:#6b7b8b}.toolbar{display:flex;align-items:center;justify-content:space-between;gap:18px}.button{display:inline-flex;align-items:center;justify-content:center;min-height:38px;padding:0 13px;border:0;border-radius:8px;background:#2563eb;color:#fff;font:inherit;font-weight:700;cursor:pointer;text-decoration:none}.button.secondary{background:#e8eef5;color:#123b5d}.button.danger{background:#fff0ef;color:#b42318}
        .notice{padding:11px 14px;margin:12px 0;border-radius:8px;background:#ecfdf3;color:#067647}.notice.error{background:#fff0ef;color:#b42318}.panel{padding:20px;margin-top:20px;border:1px solid #e3eaf1;border-radius:13px;background:#fff}.panel h2{margin:0 0 14px;color:#123b5d;font-size:1.1rem}
        .filter,.form-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));align-items:end;gap:12px}.filter{margin-bottom:16px}.field{display:grid;gap:5px}.field label{font-size:.78rem;color:#526678;font-weight:700}.field input,.field select,.filter input,.filter select{width:100%;min-height:39px;padding:8px 10px;border:1px solid #cad6e2;border-radius:8px;background:#fff;font:inherit}.filter .actions{display:flex;gap:8px}
        .table-wrap{overflow:auto;border:1px solid #e3eaf1;border-radius:10px}table{width:100%;min-width:1060px;border-collapse:collapse;text-align:left}th,td{padding:12px;border-bottom:1px solid #edf1f5;vertical-align:middle}th{background:#f8fafc;color:#5a6c7d;font-size:.8rem}td{font-size:.88rem}.entry{display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:center;gap:10px}.row-form{display:grid;grid-template-columns:1.1fr 1.2fr .8fr .8fr 1.2fr .8fr auto;align-items:end;gap:9px}.thumb{width:54px;height:44px;object-fit:contain}.empty{text-align:center;padding:30px;color:#6b7b8b}.help{font-size:.78rem;color:#718096}.delete-form{margin:0}
        @media(max-width:760px){.top{align-items:flex-start;flex-direction:column;padding:13px 0}.top nav{flex-wrap:wrap;gap:12px}.toolbar{align-items:flex-start;flex-direction:column}.filter,.form-grid{grid-template-columns:1fr 1fr}.filter .actions{grid-column:1/-1}}
    </style>
</head>
<body>
<header><div class="top">
    <a class="brand" href="<?= e(url('admin')) ?>">AutoCare Hub · Admin</a>
    <nav>
        <a href="<?= e(url('admin')) ?>">Tổng quan</a>
        <a href="<?= e(url('admin/appointments')) ?>">Lịch hẹn</a>
        <a href="<?= e(url('admin/customers')) ?>">Khách hàng</a>
        <a href="<?= e(url('admin/vehicles')) ?>">Phương tiện</a>
        <a href="<?= e(url('admin/brands')) ?>">Hãng xe</a>
        <a href="<?= e(url('admin/models')) ?>">Dòng xe</a>
        <a href="<?= e(url('admin/vehicle-import')) ?>">Nhập dữ liệu xe</a>
        <a href="<?= e(url('admin/vehicle-import/review')) ?>">Duyệt dòng xe</a>
        <a href="<?= e(url()) ?>">Trang chủ</a>
        <form method="post" action="<?= e(url('logout')) ?>"><?= csrf_field() ?><button class="button secondary" type="submit">Đăng xuất</button></form>
    </nav>
</div></header>
<main>
    <div class="toolbar"><div><h1>Quản lý dòng xe</h1><p class="sub">Dòng xe đã dùng sẽ được bảo vệ khỏi xóa; hãy ngừng hoạt động để ẩn khỏi đăng ký mới.</p></div></div>
    <?php if (is_string($success) && $success !== ''): ?><p class="notice" role="status"><?= e($success) ?></p><?php endif; ?>
    <?php if (is_string($error) && $error !== ''): ?><p class="notice error" role="alert"><?= e($error) ?></p><?php endif; ?>

    <section class="panel">
        <h2>Thêm dòng xe</h2>
        <form method="post" action="<?= e(url('admin/models')) ?>" enctype="multipart/form-data" class="form-grid">
            <?= csrf_field() ?>
            <div class="field"><label for="new-brand">Hãng xe</label><select id="new-brand" name="brand_id" required><option value="">-- Chọn hãng xe --</option><?php foreach ($brands as $brand): ?><option value="<?= (int) $brand['id'] ?>"><?= e($brand['name']) ?><?= (int) $brand['status'] === 1 ? '' : ' (ngừng hoạt động)' ?></option><?php endforeach; ?></select></div>
            <div class="field"><label for="new-name">Tên dòng xe</label><input id="new-name" name="name" maxlength="100" required></div>
            <div class="field"><label for="new-year-from">Năm bắt đầu (tùy chọn)</label><input id="new-year-from" name="year_from" type="number" min="1901" max="<?= (int) date('Y') + 1 ?>"></div>
            <div class="field"><label for="new-year-to">Năm kết thúc (tùy chọn)</label><input id="new-year-to" name="year_to" type="number" min="1901" max="<?= (int) date('Y') + 1 ?>"></div>
            <div class="field"><label for="new-image">Hình ảnh (JPEG, PNG hoặc WebP; tối đa 3 MB)</label><input id="new-image" type="file" name="image" accept="image/jpeg,image/png,image/webp"></div>
            <div class="field"><span class="help">Có thể bỏ trống năm và hình ảnh nếu chưa có thông tin.</span></div>
            <button class="button" type="submit">Thêm dòng xe</button>
        </form>
    </section>

    <section class="panel">
        <h2>Danh sách dòng xe</h2>
        <form method="get" action="<?= e(url('admin/models')) ?>" class="filter">
            <input type="search" name="q" value="<?= e($q) ?>" placeholder="Tìm dòng xe hoặc hãng xe" aria-label="Tìm dòng xe hoặc hãng xe">
            <select name="brand_id" aria-label="Lọc theo hãng xe">
                <option value="">Tất cả hãng xe</option>
                <?php foreach ($brands as $brand): ?><option value="<?= (int) $brand['id'] ?>" <?= $brandId === (int) $brand['id'] ? 'selected' : '' ?>><?= e($brand['name']) ?></option><?php endforeach; ?>
            </select>
            <div class="actions"><button class="button" type="submit">Tìm kiếm</button><?php if ($q !== '' || $brandId !== null): ?><a class="button secondary" href="<?= e(url('admin/models')) ?>">Xóa lọc</a><?php endif; ?></div>
        </form>
        <div class="table-wrap">
            <?php if ($models === []): ?><div class="empty"><?= $q === '' && $brandId === null ? 'Chưa có dòng xe.' : 'Không tìm thấy dòng xe phù hợp.' ?></div><?php else: ?>
                <table>
                    <thead><tr><th>Hình ảnh</th><th>Hãng xe</th><th>Tên dòng xe</th><th>Năm đầu</th><th>Năm cuối</th><th>Ảnh mới</th><th>Trạng thái</th><th>Thao tác</th></tr></thead>
                    <tbody>
                    <?php foreach ($models as $model): ?>
                        <tr>
                            <td><?php if (is_string($model['image']) && $model['image'] !== ''): ?><img class="thumb" src="<?= e(url($model['image'])) ?>" alt="<?= e($model['name']) ?>"><?php else: ?>—<?php endif; ?></td>
                            <td colspan="7">
                                <div class="entry">
                                <form method="post" action="<?= e(url('admin/models/' . (int) $model['id'] . '/update')) ?>" enctype="multipart/form-data" class="row-form">
                                    <?= csrf_field() ?>
                                    <div class="field"><label for="model-brand-<?= (int) $model['id'] ?>">Hãng xe</label><select id="model-brand-<?= (int) $model['id'] ?>" name="brand_id" required><?php foreach ($brands as $brand): ?><option value="<?= (int) $brand['id'] ?>" <?= (int) $brand['id'] === (int) $model['brand_id'] ? 'selected' : '' ?>><?= e($brand['name']) ?></option><?php endforeach; ?></select></div>
                                    <div class="field"><label for="model-name-<?= (int) $model['id'] ?>">Tên</label><input id="model-name-<?= (int) $model['id'] ?>" name="name" maxlength="100" required value="<?= e($model['name']) ?>"></div>
                                    <div class="field"><label for="model-from-<?= (int) $model['id'] ?>">Năm đầu</label><input id="model-from-<?= (int) $model['id'] ?>" name="year_from" type="number" min="1901" max="<?= (int) date('Y') + 1 ?>" value="<?= e($model['year_from']) ?>"></div>
                                    <div class="field"><label for="model-to-<?= (int) $model['id'] ?>">Năm cuối</label><input id="model-to-<?= (int) $model['id'] ?>" name="year_to" type="number" min="1901" max="<?= (int) date('Y') + 1 ?>" value="<?= e($model['year_to']) ?>"></div>
                                    <div class="field"><label for="model-image-<?= (int) $model['id'] ?>">Thay ảnh</label><input id="model-image-<?= (int) $model['id'] ?>" type="file" name="image" accept="image/jpeg,image/png,image/webp"><span class="help">Để trống để giữ ảnh.</span></div>
                                    <div class="field"><label for="model-status-<?= (int) $model['id'] ?>">Trạng thái</label><select id="model-status-<?= (int) $model['id'] ?>" name="status"><option value="1" <?= (int) $model['status'] === 1 ? 'selected' : '' ?>>Hoạt động</option><option value="0" <?= (int) $model['status'] !== 1 ? 'selected' : '' ?>>Ngừng</option></select></div>
                                    <button class="button" type="submit">Lưu</button>
                                </form>
                                <form method="post" action="<?= e(url('admin/models/' . (int) $model['id'] . '/delete')) ?>" class="delete-form" onsubmit="return confirm('Xóa dòng xe này? Nếu đã được sử dụng, hệ thống sẽ từ chối và dữ liệu được giữ nguyên.');"><?= csrf_field() ?><button class="button danger" type="submit">Xóa</button></form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </section>
</main>
</body>
</html>
