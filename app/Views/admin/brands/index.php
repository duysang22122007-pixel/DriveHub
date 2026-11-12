<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản lý hãng xe | AutoCare Hub</title>
    <style>
        :root{font-family:Inter,Arial,sans-serif;color:#172b3b;background:#f4f7fb}*{box-sizing:border-box}body{margin:0}header{background:#123b5d;color:#fff}
        .top,main{width:min(1160px,calc(100% - 36px));margin:auto}.top{min-height:68px;display:flex;align-items:center;justify-content:space-between;gap:15px}.brand{color:#fff;font-size:1.1rem;font-weight:800;text-decoration:none}.top nav{display:flex;align-items:center;gap:18px}.top a{color:#fff;text-decoration:none}
        main{padding:34px 0 60px}h1{margin:0;color:#123b5d}.sub{color:#6b7b8b}.toolbar{display:flex;align-items:center;justify-content:space-between;gap:18px}.button{display:inline-flex;align-items:center;justify-content:center;min-height:38px;padding:0 13px;border:0;border-radius:8px;background:#2563eb;color:#fff;font:inherit;font-weight:700;cursor:pointer;text-decoration:none}.button.secondary{background:#e8eef5;color:#123b5d}
        .notice{padding:11px 14px;margin:12px 0;border-radius:8px;background:#ecfdf3;color:#067647}.notice.error{background:#fff0ef;color:#b42318}.panel{padding:20px;margin-top:20px;border:1px solid #e3eaf1;border-radius:13px;background:#fff}.panel h2{margin:0 0 14px;color:#123b5d;font-size:1.1rem}
        .filter{display:flex;gap:9px;margin:16px 0}.filter input{flex:1}.filter input,.field input,.field select{min-height:39px;padding:8px 10px;border:1px solid #cad6e2;border-radius:8px;background:#fff;font:inherit}
        .table-wrap{overflow:auto;border:1px solid #e3eaf1;border-radius:10px}table{width:100%;min-width:1000px;border-collapse:collapse;text-align:left}th,td{padding:12px;border-bottom:1px solid #edf1f5;vertical-align:middle}th{background:#f8fafc;color:#5a6c7d;font-size:.8rem}td{font-size:.88rem}.field{display:grid;gap:5px}.field label{font-size:.78rem;color:#526678;font-weight:700}.field input,.field select{width:100%}.brand-entry{display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:center;gap:10px}.row-form{display:grid;grid-template-columns:1.1fr 1.5fr 1fr auto;align-items:center;gap:10px}.thumb{width:46px;height:40px;object-fit:contain}.empty{text-align:center;padding:30px;color:#6b7b8b}.help{font-size:.82rem;color:#718096}.add-form{display:grid;grid-template-columns:1fr 1fr auto;align-items:end;gap:12px}.add-form input{width:100%}.delete-form{margin:0}.button.danger{background:#fff0ef;color:#b42318}
        @media(max-width:720px){.top{align-items:flex-start;flex-direction:column;padding:13px 0}.top nav{flex-wrap:wrap;gap:12px}.toolbar{align-items:flex-start;flex-direction:column}.filter,.add-form{display:grid;grid-template-columns:1fr}.row-form{grid-template-columns:1fr 1fr}}
    </style>
</head>
<body>
<header><div class="top">
    <a class="brand" href="<?= e(url('admin')) ?>">AutoCare Hub · Admin</a>
    <nav>
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
    <div class="toolbar"><div><h1>Quản lý hãng xe</h1><p class="sub">Cập nhật danh mục hãng xe dùng khi khách hàng đăng ký phương tiện.</p></div></div>
    <?php if (is_string($success) && $success !== ''): ?><p class="notice" role="status"><?= e($success) ?></p><?php endif; ?>
    <?php if (is_string($error) && $error !== ''): ?><p class="notice error" role="alert"><?= e($error) ?></p><?php endif; ?>

    <section class="panel">
        <h2>Thêm hãng xe</h2>
        <form method="post" action="<?= e(url('admin/brands')) ?>" enctype="multipart/form-data" class="add-form">
            <?= csrf_field() ?>
            <div class="field"><label for="new-name">Tên hãng xe</label><input id="new-name" name="name" maxlength="100" required></div>
            <div class="field"><label for="new-logo">Logo (JPEG, PNG hoặc WebP; tối đa 3 MB)</label><input id="new-logo" type="file" name="logo" accept="image/jpeg,image/png,image/webp"></div>
            <button class="button" type="submit">Thêm hãng xe</button>
        </form>
    </section>

    <section class="panel">
        <h2>Danh sách hãng xe</h2>
        <form method="get" action="<?= e(url('admin/brands')) ?>" class="filter">
            <input type="search" name="q" value="<?= e($q) ?>" placeholder="Tìm tên hãng xe" aria-label="Tìm tên hãng xe">
            <button class="button" type="submit">Tìm kiếm</button>
            <?php if ($q !== ''): ?><a class="button secondary" href="<?= e(url('admin/brands')) ?>">Xóa lọc</a><?php endif; ?>
        </form>
        <div class="table-wrap">
            <?php if ($brands === []): ?><div class="empty"><?= $q === '' ? 'Chưa có hãng xe.' : 'Không tìm thấy hãng xe phù hợp.' ?></div><?php else: ?>
                <table>
                    <thead><tr><th>Logo</th><th>Chỉnh sửa</th><th>Trạng thái</th><th>Lưu</th></tr></thead>
                    <tbody>
                    <?php foreach ($brands as $brand): ?>
                        <tr>
                            <td><?php if (is_string($brand['logo']) && $brand['logo'] !== ''): ?><img class="thumb" src="<?= e(url($brand['logo'])) ?>" alt="Logo <?= e($brand['name']) ?>"><?php else: ?>—<?php endif; ?></td>
                            <td colspan="3">
                                <div class="brand-entry">
                                <form method="post" action="<?= e(url('admin/brands/' . (int) $brand['id'] . '/update')) ?>" enctype="multipart/form-data" class="row-form">
                                    <?= csrf_field() ?>
                                    <div class="field"><label for="brand-name-<?= (int) $brand['id'] ?>">Tên hãng xe</label><input id="brand-name-<?= (int) $brand['id'] ?>" name="name" maxlength="100" required value="<?= e($brand['name']) ?>"></div>
                                    <div class="field"><label for="brand-logo-<?= (int) $brand['id'] ?>">Thay logo</label><input id="brand-logo-<?= (int) $brand['id'] ?>" type="file" name="logo" accept="image/jpeg,image/png,image/webp"><span class="help">Để trống để giữ logo hiện tại.</span></div>
                                    <div class="field"><label for="brand-status-<?= (int) $brand['id'] ?>">Trạng thái</label><select id="brand-status-<?= (int) $brand['id'] ?>" name="status"><option value="1" <?= (int) $brand['status'] === 1 ? 'selected' : '' ?>>Đang hoạt động</option><option value="0" <?= (int) $brand['status'] !== 1 ? 'selected' : '' ?>>Ngừng hoạt động</option></select></div>
                                    <button class="button" type="submit">Lưu</button>
                                </form>
                                <form method="post" action="<?= e(url('admin/brands/' . (int) $brand['id'] . '/delete')) ?>" class="delete-form" onsubmit="return confirm('Xóa hãng xe này? Hãng đang có dòng xe hoặc phương tiện liên quan sẽ được giữ nguyên.');"><?= csrf_field() ?><button class="button danger" type="submit">Xóa</button></form>
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
