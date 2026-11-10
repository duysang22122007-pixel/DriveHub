<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hãng xe | AutoCare Hub</title>
    <style>
        :root{font-family:Inter,Arial,sans-serif;color:#172b3b;background:#f4f7fb}*{box-sizing:border-box}body{margin:0}header{background:#123b5d;color:#fff}
        .top,main{width:min(980px,calc(100% - 36px));margin:auto}.top{min-height:68px;display:flex;align-items:center;justify-content:space-between;gap:15px}.brand{color:#fff;font-size:1.1rem;font-weight:800;text-decoration:none}.top nav{display:flex;align-items:center;gap:18px}.top a{color:#fff;text-decoration:none}
        main{padding:34px 0 60px}h1{margin:0;color:#123b5d}.sub{color:#6b7b8b}.grid{display:grid;grid-template-columns:1fr .8fr;gap:20px;margin-top:22px}
        .panel{padding:23px;border:1px solid #e3eaf1;border-radius:13px;background:#fff}.panel h2{margin:0 0 16px;color:#123b5d;font-size:1.1rem}
        label{display:block;margin:10px 0 6px;font-weight:700;font-size:.9rem}input{width:100%;height:43px;padding:10px 12px;border:1px solid #cad6e2;border-radius:8px;font:inherit}
        .button{display:inline-flex;align-items:center;justify-content:center;min-height:38px;padding:0 13px;border:0;border-radius:8px;background:#2563eb;color:#fff;font:inherit;font-weight:700;cursor:pointer}.button.secondary{background:#e8eef5;color:#123b5d}
        .notice{padding:11px 14px;margin:12px 0;border-radius:8px;background:#ecfdf3;color:#067647}.notice.error{background:#fff0ef;color:#b42318}
        .brand-list{display:grid;gap:10px}.brand-row{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:13px;border:1px solid #e8edf2;border-radius:9px}.brand-row strong{display:block;color:#123b5d}.state{display:block;margin-top:4px;color:#718096;font-size:.82rem}
        @media(max-width:680px){.top{align-items:flex-start;flex-direction:column;padding:13px 0}.top nav{flex-wrap:wrap;gap:12px}.grid{grid-template-columns:1fr}}
    </style>
</head>
<body>
<header><div class="top"><a class="brand" href="<?= e(url('admin')) ?>">AutoCare Hub · Admin</a><nav><a href="<?= e(url('admin/customers')) ?>">Khách hàng</a><a href="<?= e(url('admin/vehicles')) ?>">Phương tiện</a><a href="<?= e(url()) ?>">Trang chủ</a><form method="post" action="<?= e(url('logout')) ?>"><?= csrf_field() ?><button class="button secondary" type="submit">Đăng xuất</button></form></nav></div></header>
<main>
    <h1>Hãng xe</h1><p class="sub">Quản lý danh mục thật được dùng trong biểu mẫu đăng ký phương tiện.</p>
    <?php if (is_string($success) && $success !== ''): ?><p class="notice" role="status"><?= e($success) ?></p><?php endif; ?>
    <?php if (is_string($error) && $error !== ''): ?><p class="notice error" role="alert"><?= e($error) ?></p><?php endif; ?>
    <div class="grid">
        <section class="panel">
            <h2>Danh mục hiện có</h2>
            <div class="brand-list">
                <?php if ($brands === []): ?><p>Chưa có hãng xe. Thêm hãng xe để khách hàng có thể đăng ký phương tiện.</p><?php endif; ?>
                <?php foreach ($brands as $brand): ?>
                    <div class="brand-row">
                        <div><strong><?= e($brand['name']) ?></strong><span class="state"><?= (int) $brand['status'] === 1 ? 'Đang hoạt động' : 'Đã ngừng hoạt động' ?></span></div>
                        <form method="post" action="<?= e(url('admin/brands/' . (int) $brand['id'] . '/status')) ?>">
                            <?= csrf_field() ?><input type="hidden" name="status" value="<?= (int) $brand['status'] === 1 ? '0' : '1' ?>">
                            <button class="button secondary" type="submit"><?= (int) $brand['status'] === 1 ? 'Tạm ngừng' : 'Kích hoạt' ?></button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        <section class="panel">
            <h2>Thêm hãng xe</h2>
            <form method="post" action="<?= e(url('admin/brands')) ?>">
                <?= csrf_field() ?>
                <label for="name">Tên hãng xe</label>
                <input id="name" name="name" maxlength="100" required>
                <button class="button" type="submit" style="margin-top:16px">Thêm hãng xe</button>
            </form>
        </section>
    </div>
</main>
</body>
</html>
