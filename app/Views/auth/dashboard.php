<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($dashboardTitle) ?> | AutoCare Hub</title>
    <style>
        :root{font-family:Inter,Arial,sans-serif;color:#172b3b;background:#f4f7fb}*{box-sizing:border-box}body{margin:0}header{background:#123b5d;color:#fff}
        .top,main{width:min(1440px,calc(100% - 48px));margin:auto}.top{min-height:68px;display:flex;align-items:center;justify-content:space-between;gap:15px}
        .brand{color:#fff;font-size:1.1rem;font-weight:800;text-decoration:none}.top nav{display:flex;align-items:center;gap:14px;white-space:nowrap}.top nav a{white-space:nowrap;transition:color .15s ease}.top a{color:#fff;text-decoration:none}.top a.active{text-decoration:underline;text-underline-offset:3px}.top nav a:hover,.top nav a:focus-visible{color:#bfdbfe;text-decoration:underline;text-underline-offset:4px}
        main{padding:34px 0 60px}h1{margin:0;color:#123b5d}.sub{color:#6b7b8b}.button{display:inline-flex;align-items:center;justify-content:center;min-height:38px;padding:0 13px;border:0;border-radius:8px;background:#2563eb;color:#fff;font:inherit;font-weight:700;cursor:pointer;text-decoration:none}.button.secondary{background:#e8eef5;color:#123b5d}
        .toolbar{display:flex;align-items:center;justify-content:space-between;gap:18px;margin-bottom:22px}.toolbar p{margin:8px 0 0}.stats{display:grid;grid-template-columns:repeat( auto-fit,minmax(205px,1fr));gap:16px}
        .card{padding:21px;border:1px solid #e3eaf1;border-radius:13px;background:#fff;box-shadow:0 7px 22px #123b5d07}.card-label{margin:0;color:#667788;font-size:.9rem;font-weight:700}.card-value{margin:13px 0 0;color:#123b5d;font-size:2rem;font-weight:800;letter-spacing:-.04em}.card-note{margin:7px 0 0;color:#718096;font-size:.82rem}
        .pending-card{display:block;color:inherit;text-decoration:none;transition:border-color .15s,transform .15s}.pending-card:hover{border-color:#9bb8d1;transform:translateY(-2px)}.pending-card:focus-visible{outline:3px solid #2563eb;outline-offset:2px}
        .section-title{margin:34px 0 14px;color:#123b5d;font-size:1.15rem}.actions{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:12px}.action{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:16px 18px;border:1px solid #e3eaf1;border-radius:11px;background:#fff;color:#123b5d;text-decoration:none;font-weight:700;transition:border-color .15s,transform .15s}.action:hover{border-color:#9bb8d1;transform:translateY(-1px)}.arrow{color:#2563eb;font-size:1.1rem}
        @media(max-width:640px){.top{align-items:flex-start;flex-direction:column;padding:13px 0}.top nav{flex-wrap:wrap;gap:12px}.toolbar{align-items:flex-start;flex-direction:column}.stats{grid-template-columns:1fr 1fr}.card{padding:16px}.card-value{font-size:1.65rem}}
        @media(max-width:420px){.stats{grid-template-columns:1fr}}
    </style>
</head>
<body>
<header>
    <div class="top">
        <a class="brand" href="<?= e(url('admin')) ?>">AutoCare Hub · Admin</a>
        <nav aria-label="Điều hướng Admin">
            <a class="active" href="<?= e(url('admin')) ?>" aria-current="page">Tổng quan</a>
            <a href="<?= e(url('admin/appointments')) ?>">Lịch hẹn</a>
            <a href="<?= e(url('admin/customers')) ?>">Khách hàng</a>
            <a href="<?= e(url('admin/vehicles')) ?>">Phương tiện</a>
            <a href="<?= e(url('admin/brands')) ?>">Hãng xe</a>
            <a href="<?= e(url('admin/models')) ?>">Dòng xe</a>
            <a href="<?= e(url('admin/vehicle-import')) ?>">Nhập dữ liệu xe</a>
            <a href="<?= e(url('admin/vehicle-import/review')) ?>">Duyệt dòng xe</a>
            <a href="<?= e(url()) ?>">Trang chủ</a>
            <form method="post" action="<?= e(url('logout')) ?>">
                <?= csrf_field() ?>
                <button class="button secondary" type="submit">Đăng xuất</button>
            </form>
        </nav>
    </div>
</header>
<main>
    <div class="toolbar">
        <div>
            <h1><?= e($dashboardTitle) ?></h1>
            <p class="sub">Xin chào <?= e($userName) ?> · <?= e($roleLabel) ?></p>
        </div>
    </div>

    <section class="stats" aria-label="Tổng quan hệ thống">
        <article class="card">
            <p class="card-label">Khách hàng</p>
            <p class="card-value"><?= e($stats['customers']) ?></p>
            <p class="card-note">Tổng số tài khoản khách hàng</p>
        </article>
        <article class="card">
            <p class="card-label">Phương tiện</p>
            <p class="card-value"><?= e($stats['vehicles']) ?></p>
            <p class="card-note">Tổng số xe đã đăng ký</p>
        </article>
        <a class="card pending-card" href="<?= e(url('admin/appointments') . '?' . http_build_query(['status' => 'pending'])) ?>" aria-label="Xem <?= e($stats['pendingAppointments']) ?> lịch hẹn chờ xử lý">
            <p class="card-label">Lịch hẹn chờ xử lý</p>
            <p class="card-value"><?= e($stats['pendingAppointments']) ?></p>
            <p class="card-note">Lịch hẹn đang có trạng thái chờ</p>
        </a>
        <article class="card">
            <p class="card-label">Phiếu sửa chữa đang xử lý</p>
            <p class="card-value"><?= e($stats['activeTickets']) ?></p>
            <p class="card-note">Chưa hoàn thành hoặc hủy</p>
        </article>
        <article class="card">
            <p class="card-label">Doanh thu đã thanh toán</p>
            <p class="card-value"><?= e(number_format($stats['revenue'], 0, ',', '.')) ?> ₫</p>
            <p class="card-note">Tổng giao dịch thanh toán thành công</p>
        </article>
    </section>

    <h2 class="section-title">Quản lý chính</h2>
    <nav class="actions" aria-label="Thao tác quản lý">
        <a class="action" href="<?= e(url('admin/customers')) ?>">Quản lý khách hàng <span class="arrow" aria-hidden="true">→</span></a>
        <a class="action" href="<?= e(url('admin/appointments')) ?>">Quản lý lịch hẹn <span class="arrow" aria-hidden="true">→</span></a>
        <a class="action" href="<?= e(url('admin/vehicles')) ?>">Xem phương tiện <span class="arrow" aria-hidden="true">→</span></a>
        <a class="action" href="<?= e(url('admin/brands')) ?>">Quản lý hãng xe <span class="arrow" aria-hidden="true">→</span></a>
        <a class="action" href="<?= e(url('admin/models')) ?>">Quản lý dòng xe <span class="arrow" aria-hidden="true">→</span></a>
        <a class="action" href="<?= e(url('admin/vehicle-import')) ?>">Đồng bộ thư viện xe <span class="arrow" aria-hidden="true">→</span></a>
        <a class="action" href="<?= e(url('admin/vehicle-import/review')) ?>">Duyệt dòng xe NHTSA <span class="arrow" aria-hidden="true">→</span></a>
    </nav>
</main>
</body>
</html>
