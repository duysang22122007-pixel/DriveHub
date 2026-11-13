<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Phương tiện | AutoCare Hub</title>
    <style>
        :root{font-family:Inter,Arial,sans-serif;color:#172b3b;background:#f4f7fb}*{box-sizing:border-box}body{margin:0}header{background:#123b5d;color:#fff}
        .top,main{width:min(1160px,calc(100% - 36px));margin:auto}.top{min-height:68px;display:flex;align-items:center;justify-content:space-between;gap:15px}.brand{color:#fff;font-size:1.1rem;font-weight:800;text-decoration:none}.top nav{display:flex;align-items:center;gap:18px}.top a{color:#fff;text-decoration:none}.top a.active{text-decoration:underline;text-underline-offset:3px}
        main{padding:34px 0 60px}h1{margin:0;color:#123b5d}.sub{color:#6b7b8b}.button{display:inline-flex;align-items:center;justify-content:center;min-height:38px;padding:0 13px;border:0;border-radius:8px;background:#e8eef5;color:#123b5d;font:inherit;font-weight:700;cursor:pointer}
        .table-wrap{overflow:auto;margin-top:20px;background:#fff;border:1px solid #e3eaf1;border-radius:12px}table{width:100%;min-width:750px;border-collapse:collapse;text-align:left}th,td{padding:14px 16px;border-bottom:1px solid #edf1f5}th{background:#f8fafc;color:#5a6c7d;font-size:.81rem;text-transform:uppercase;letter-spacing:.04em}td{font-size:.9rem}.empty{padding:36px;text-align:center;color:#6b7b8b}
        @media(max-width:640px){.top{align-items:flex-start;flex-direction:column;padding:13px 0}.top nav{flex-wrap:wrap;gap:12px}}
    </style>
</head>
<body>
<header><div class="top"><a class="brand" href="<?= e(url('admin')) ?>">AutoCare Hub · Admin</a><nav><a href="<?= e(url('admin')) ?>">Tổng quan</a><a href="<?= e(url('admin/appointments')) ?>">Lịch hẹn</a><a href="<?= e(url('admin/customers')) ?>">Khách hàng</a><a href="<?= e(url('admin/vehicles')) ?>" class="active" aria-current="page">Phương tiện</a><a href="<?= e(url('admin/brands')) ?>">Hãng xe</a><a href="<?= e(url('admin/models')) ?>">Dòng xe</a><a href="<?= e(url()) ?>">Trang chủ</a><form method="post" action="<?= e(url('logout')) ?>"><?= csrf_field() ?><button class="button" type="submit">Đăng xuất</button></form></nav></div></header>
<main>
    <h1>Phương tiện</h1><p class="sub">Danh sách xe đã đăng ký và chủ sở hữu theo dữ liệu hệ thống.</p>
    <div class="table-wrap"><?php if ($vehicles === []): ?><div class="empty">Chưa có phương tiện nào được đăng ký.</div><?php else: ?>
        <table><thead><tr><th>Biển số</th><th>Phương tiện</th><th>Năm</th><th>Số km</th><th>Chủ xe</th><th>Email</th></tr></thead><tbody>
        <?php foreach ($vehicles as $vehicle): ?><tr><td><strong><?= e($vehicle['license_plate']) ?></strong></td><td><?= e($vehicle['brand_name']) ?> <?= e($vehicle['model']) ?></td><td><?= e($vehicle['manufacture_year'] ?: '—') ?></td><td><?= e(number_format((int) $vehicle['current_mileage'], 0, ',', '.')) ?></td><td><?= e($vehicle['owner_name']) ?></td><td><?= e($vehicle['owner_email']) ?></td></tr><?php endforeach; ?>
        </tbody></table><?php endif; ?></div>
</main>
</body>
</html>
