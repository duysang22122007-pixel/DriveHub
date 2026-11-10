<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Khách hàng | AutoCare Hub</title>
    <style>
        :root{font-family:Inter,Arial,sans-serif;color:#172b3b;background:#f4f7fb}*{box-sizing:border-box}body{margin:0}
        header{background:#123b5d;color:#fff}.top,main{width:min(1160px,calc(100% - 36px));margin:auto}.top{min-height:68px;display:flex;align-items:center;justify-content:space-between;gap:15px}
        .brand{color:#fff;font-size:1.1rem;font-weight:800;text-decoration:none}.top nav{display:flex;align-items:center;gap:18px}.top a{color:#fff;text-decoration:none}
        main{padding:34px 0 60px}h1{margin:0;color:#123b5d}.sub{color:#6b7b8b}.toolbar{display:flex;align-items:center;justify-content:space-between;gap:18px;margin-bottom:20px}
        .button{display:inline-flex;align-items:center;justify-content:center;min-height:38px;padding:0 13px;border:0;border-radius:8px;background:#2563eb;color:#fff;font:inherit;font-weight:700;cursor:pointer}
        .button.secondary{background:#e8eef5;color:#123b5d}.button.danger{background:#fff0ef;color:#b42318}
        .notice{padding:12px 15px;margin:12px 0;border-radius:8px;background:#ecfdf3;color:#067647}.notice.error{background:#fff0ef;color:#b42318}
        .table-wrap{overflow:auto;background:#fff;border:1px solid #e3eaf1;border-radius:12px}table{width:100%;min-width:800px;border-collapse:collapse;text-align:left}th,td{padding:14px 16px;border-bottom:1px solid #edf1f5;vertical-align:middle}th{background:#f8fafc;color:#5a6c7d;font-size:.81rem;text-transform:uppercase;letter-spacing:.04em}td{font-size:.9rem}.status{display:inline-block;padding:5px 9px;border-radius:99px;background:#ecfdf3;color:#067647;font-size:.8rem;font-weight:700}.status.locked{background:#fff1f0;color:#b42318}.actions{display:flex;align-items:center;gap:8px}.actions form{margin:0}.empty{padding:36px;text-align:center;color:#6b7b8b}
        @media(max-width:640px){.top{align-items:flex-start;flex-direction:column;padding:13px 0}.top nav{flex-wrap:wrap;gap:12px}.toolbar{align-items:flex-start;flex-direction:column}}
    </style>
</head>
<body>
<header><div class="top"><a class="brand" href="<?= e(url('admin')) ?>">AutoCare Hub · Admin</a><nav><a href="<?= e(url('admin/vehicles')) ?>">Phương tiện</a><a href="<?= e(url()) ?>">Trang chủ</a><form method="post" action="<?= e(url('logout')) ?>"><?= csrf_field() ?><button class="button secondary" type="submit">Đăng xuất</button></form></nav></div></header>
<main>
    <div class="toolbar"><div><h1>Khách hàng</h1><p class="sub">Danh sách tài khoản khách hàng và phương tiện đã đăng ký.</p></div><a class="button secondary" href="<?= e(url('admin/vehicles')) ?>">Xem phương tiện</a></div>
    <?php if (is_string($success) && $success !== ''): ?><p class="notice" role="status"><?= e($success) ?></p><?php endif; ?>
    <?php if (is_string($error) && $error !== ''): ?><p class="notice error" role="alert"><?= e($error) ?></p><?php endif; ?>
    <div class="table-wrap">
        <?php if ($customers === []): ?><div class="empty">Chưa có tài khoản khách hàng.</div><?php else: ?>
        <table><thead><tr><th>Khách hàng</th><th>Email</th><th>Điện thoại</th><th>Số xe</th><th>Trạng thái</th><th>Thao tác</th></tr></thead><tbody>
        <?php foreach ($customers as $customer): ?>
            <tr>
                <td><?= e($customer['full_name']) ?></td><td><?= e($customer['email']) ?></td><td><?= e($customer['phone']) ?></td>
                <td><?= e((int) $customer['vehicle_count']) ?></td>
                <td><span class="status <?= $customer['status'] === 'locked' ? 'locked' : '' ?>"><?= e($customer['status'] === 'active' ? 'Đang hoạt động' : 'Đã khóa') ?></span></td>
                <td><form method="post" action="<?= e(url('admin/customers/' . (int) $customer['id'] . '/status')) ?>"><?= csrf_field() ?><input type="hidden" name="status" value="<?= e($customer['status'] === 'active' ? 'locked' : 'active') ?>"><button class="button <?= $customer['status'] === 'active' ? 'danger' : 'secondary' ?>" type="submit"><?= e($customer['status'] === 'active' ? 'Khóa tài khoản' : 'Mở khóa') ?></button></form></td>
            </tr>
        <?php endforeach; ?>
        </tbody></table><?php endif; ?>
    </div>
</main>
</body>
</html>
