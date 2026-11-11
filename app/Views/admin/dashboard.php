<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tổng quan quản trị | DriveHub</title>
    <style>
        :root{font-family:Inter,"Segoe UI",Arial,sans-serif;color:#173b59;background:#eff5fb;font-synthesis:none;text-rendering:optimizeLegibility}
        *{box-sizing:border-box}
        body{margin:0;min-height:100vh}
        a{color:inherit;text-decoration:none}
        .topbar{height:64px;position:sticky;top:0;z-index:5;display:flex;align-items:center;justify-content:space-between;padding:0 28px;background:#fff;border-bottom:1px solid #dce8f3}
        .brand{display:inline-flex;align-items:center;gap:10px;font-size:.98rem;font-weight:800;color:#17496d}
        .brand-mark{display:grid;place-items:center;width:30px;height:30px;border-radius:9px;background:#4b97d1;color:#fff;font-size:.9rem}
        .top-actions{display:flex;align-items:center;gap:14px}
        .top-actions>a{padding:9px 13px;border:1px solid #dce8f3;border-radius:999px;background:#fff;color:#245578;font-size:.82rem;font-weight:700}
        .top-actions form{margin:0}
        .logout{padding:9px 13px;border:0;border-radius:8px;background:#e8f2f8;color:#245578;font:inherit;font-size:.82rem;font-weight:700;cursor:pointer}
        .layout{display:grid;grid-template-columns:230px minmax(0,1fr);min-height:calc(100vh - 64px)}
        aside{padding:22px 14px;background:#fff;border-right:1px solid #dce8f3}
        .side-brand{display:flex;align-items:center;gap:9px;padding:0 7px 19px;border-bottom:1px solid #e6eef5;color:#245578;font-size:.9rem;font-weight:800}
        .nav-label{margin:22px 9px 9px;color:#93a9bb;font-size:.68rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase}
        .nav-list{display:grid;gap:5px}
        .nav-link{display:flex;align-items:center;gap:11px;padding:11px 10px;border-radius:10px;color:#55738b;font-size:.84rem;font-weight:650;transition:background .15s,color .15s}
        .nav-link:hover,.nav-link.active{background:#e5f3f5;color:#267e9b}
        .nav-icon{display:grid;place-items:center;width:22px;height:22px;border-radius:7px;background:#edf5f8;color:#4b91b0;font-size:.76rem}
        main{min-width:0;width:min(1440px,100%);margin:0 auto;padding:27px 30px 44px}
        .page-heading{display:flex;align-items:flex-end;justify-content:space-between;gap:20px;margin-bottom:20px}
        .eyebrow{margin:0 0 6px;color:#548cb4;font-size:.68rem;font-weight:800;letter-spacing:.11em;text-transform:uppercase}
        h1{margin:0;color:#173b59;font-size:clamp(1.7rem,3vw,2.25rem);letter-spacing:-.04em}
        .welcome{margin:7px 0 0;color:#7890a3;font-size:.9rem}
        .updated{padding:9px 12px;border:1px solid #dce8f3;border-radius:999px;background:#fff;color:#52748f;font-size:.76rem;font-weight:650;white-space:nowrap}
        .stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:13px;margin-bottom:15px}
        .stat-card,.panel{border:1px solid #dce8f3;border-radius:13px;background:#fff;box-shadow:0 8px 24px #204a6810}
        .stat-card{padding:17px 18px;min-height:112px}
        .stat-title{margin:0;color:#7890a3;font-size:.8rem}
        .stat-value{margin:8px 0 5px;color:#183d5a;font-size:1.75rem;font-weight:800;letter-spacing:-.04em}
        .stat-note{margin:0;color:#91a4b4;font-size:.72rem}
        .panel{padding:19px}
        .chart-panel{margin-bottom:15px}
        .panel-heading{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;padding-bottom:14px;border-bottom:1px solid #e4edf5}
        .section-label{margin:0 0 5px;color:#4b8ab7;font-size:.67rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase}
        h2{margin:0;color:#1b4565;font-size:1.08rem;letter-spacing:-.02em}
        .chart-legend{display:flex;align-items:center;gap:16px;margin-top:12px;color:#617d94;font-size:.72rem}
        .legend-item{display:flex;align-items:center;gap:7px}
        .legend-dot{width:8px;height:8px;border-radius:50%;background:#4b97d1}
        .legend-dot.orange{background:#f4ae45}
        .chart{display:block;width:100%;height:194px;margin-top:8px;overflow:visible}
        .chart-grid{stroke:#e8eff5;stroke-width:1}
        .chart-axis{fill:#8ba0b1;font-size:10px}
        .chart-line{fill:none;stroke:#4b97d1;stroke-width:3;stroke-linecap:round;stroke-linejoin:round}
        .chart-line.vehicles{stroke:#f4ae45}
        .chart-point{fill:#fff;stroke:#4b97d1;stroke-width:2}
        .chart-point.vehicles{stroke:#f4ae45}
        .lower-grid{display:grid;grid-template-columns:1fr 1fr;gap:15px}
        .panel-link{color:#4b8ab7;font-size:.77rem;font-weight:700;white-space:nowrap}
        .list{display:grid;margin-top:8px}
        .list-item{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 0;border-bottom:1px solid #edf2f6}
        .list-item:last-child{border-bottom:0}
        .item-primary{min-width:0}
        .item-primary strong{display:block;overflow:hidden;color:#294c68;font-size:.83rem;text-overflow:ellipsis;white-space:nowrap}
        .item-primary span{display:block;margin-top:4px;overflow:hidden;color:#8a9eae;font-size:.74rem;text-overflow:ellipsis;white-space:nowrap}
        .item-meta{flex-shrink:0;color:#7991a4;font-size:.73rem;text-align:right}
        .empty{padding:27px 10px;color:#8499aa;font-size:.84rem;text-align:center}
        @media(max-width:980px){.layout{grid-template-columns:190px minmax(0,1fr)}main{padding:24px 20px}.stats{grid-template-columns:repeat(2,minmax(0,1fr))}}
        @media(max-width:680px){.topbar{height:58px;padding:0 15px}.top-actions>a{display:none}.layout{display:block}.layout aside{position:static;padding:10px 12px;border-right:0;border-bottom:1px solid #dce8f3}.side-brand{display:none}.nav-label{display:none}.nav-list{display:flex;gap:5px;overflow-x:auto}.nav-link{flex-shrink:0;padding:9px}.nav-link span:last-child{font-size:.77rem}main{padding:21px 14px 32px}.page-heading{align-items:flex-start}.updated{display:none}.lower-grid{grid-template-columns:1fr}.chart{height:170px}}
        @media(max-width:390px){.stats{gap:9px}.stat-card{padding:14px}.stat-value{font-size:1.5rem}.panel{padding:15px}.chart-legend{gap:10px}}
    </style>
</head>
<body>
<header class="topbar">
    <a class="brand" href="<?= e(url('admin')) ?>"><span class="brand-mark">D</span> DriveHub</a>
    <div class="top-actions">
        <a href="<?= e(url()) ?>">Xem trang chủ</a>
        <form method="post" action="<?= e(url('logout')) ?>">
            <?= csrf_field() ?>
            <button class="logout" type="submit">Đăng xuất</button>
        </form>
    </div>
</header>
<div class="layout">
    <aside aria-label="Điều hướng quản trị">
        <div class="side-brand"><span class="brand-mark">D</span> DriveHub</div>
        <p class="nav-label">Khu vực quản trị</p>
        <nav class="nav-list">
            <a class="nav-link active" href="<?= e(url('admin')) ?>" aria-current="page"><span class="nav-icon">▦</span><span>Tổng quan</span></a>
            <a class="nav-link" href="<?= e(url('admin/customers')) ?>"><span class="nav-icon">♙</span><span>Khách hàng</span></a>
            <a class="nav-link" href="<?= e(url('admin/vehicles')) ?>"><span class="nav-icon">◇</span><span>Phương tiện</span></a>
            <a class="nav-link" href="<?= e(url('admin/brands')) ?>"><span class="nav-icon">◉</span><span>Hãng xe</span></a>
        </nav>
    </aside>
    <main>
        <div class="page-heading">
            <div>
                <p class="eyebrow">Khu vực quản trị</p>
                <h1>Tổng quan</h1>
                <p class="welcome">Xin chào, <?= e($userName) ?>. Đây là tình hình hoạt động của gara.</p>
            </div>
            <span class="updated">Dữ liệu trực tiếp từ hệ thống</span>
        </div>

        <section class="stats" aria-label="Thống kê">
            <article class="stat-card">
                <p class="stat-title">Khách hàng</p>
                <p class="stat-value"><?= e(number_format($summary['customers'])) ?></p>
                <p class="stat-note">Tổng tài khoản khách hàng</p>
            </article>
            <article class="stat-card">
                <p class="stat-title">Phương tiện</p>
                <p class="stat-value"><?= e(number_format($summary['vehicles'])) ?></p>
                <p class="stat-note">Xe đã đăng ký trong hệ thống</p>
            </article>
            <article class="stat-card">
                <p class="stat-title">Hãng xe hoạt động</p>
                <p class="stat-value"><?= e(number_format($summary['active_brands'])) ?></p>
                <p class="stat-note">Có thể chọn khi đăng ký xe</p>
            </article>
            <article class="stat-card">
                <p class="stat-title">Khách hàng mới</p>
                <p class="stat-value"><?= e(number_format($summary['recent_customers'])) ?></p>
                <p class="stat-note">Đăng ký trong 7 ngày gần nhất</p>
            </article>
        </section>

        <section class="panel chart-panel" aria-labelledby="activity-title">
            <div class="panel-heading">
                <div>
                    <p class="section-label">Biểu đồ</p>
                    <h2 id="activity-title">Hoạt động 7 ngày gần nhất</h2>
                    <div class="chart-legend" aria-label="Chú giải biểu đồ">
                        <span class="legend-item"><span class="legend-dot"></span>Khách hàng mới</span>
                        <span class="legend-item"><span class="legend-dot orange"></span>Phương tiện đăng ký</span>
                    </div>
                </div>
            </div>
            <?php
                $chartMax = 1;
                foreach ($activity as $day) {
                    $chartMax = max($chartMax, $day['customers'], $day['vehicles']);
                }
                $customerPoints = [];
                $vehiclePoints = [];
                foreach ($activity as $index => $day) {
                    $x = 30 + ($index * 940 / 6);
                    $customerY = 158 - ($day['customers'] / $chartMax * 132);
                    $vehicleY = 158 - ($day['vehicles'] / $chartMax * 132);
                    $customerPoints[] = ['x' => $x, 'y' => $customerY];
                    $vehiclePoints[] = ['x' => $x, 'y' => $vehicleY];
                }
                $customerLine = implode(' ', array_map(static fn (array $point): string => $point['x'] . ',' . $point['y'], $customerPoints));
                $vehicleLine = implode(' ', array_map(static fn (array $point): string => $point['x'] . ',' . $point['y'], $vehiclePoints));
            ?>
            <svg class="chart" viewBox="0 0 1000 190" role="img" aria-label="Biểu đồ lượt khách hàng và phương tiện đăng ký trong 7 ngày gần nhất">
                <?php foreach ([26, 70, 114, 158] as $gridY): ?>
                    <line class="chart-grid" x1="20" y1="<?= $gridY ?>" x2="980" y2="<?= $gridY ?>"></line>
                <?php endforeach; ?>
                <polyline class="chart-line" points="<?= e($customerLine) ?>"></polyline>
                <polyline class="chart-line vehicles" points="<?= e($vehicleLine) ?>"></polyline>
                <?php foreach ($activity as $index => $day): ?>
                    <circle class="chart-point" cx="<?= e($customerPoints[$index]['x']) ?>" cy="<?= e($customerPoints[$index]['y']) ?>" r="4"></circle>
                    <circle class="chart-point vehicles" cx="<?= e($vehiclePoints[$index]['x']) ?>" cy="<?= e($vehiclePoints[$index]['y']) ?>" r="4"></circle>
                    <text class="chart-axis" x="<?= e($customerPoints[$index]['x']) ?>" y="181" text-anchor="middle"><?= e($day['label']) ?></text>
                <?php endforeach; ?>
            </svg>
        </section>

        <div class="lower-grid">
            <section class="panel">
                <div class="panel-heading">
                    <div><p class="section-label">Khách hàng</p><h2>Đăng ký gần đây</h2></div>
                    <a class="panel-link" href="<?= e(url('admin/customers')) ?>">Xem tất cả</a>
                </div>
                <div class="list">
                    <?php if ($customers === []): ?>
                        <div class="empty">Chưa có khách hàng đăng ký.</div>
                    <?php else: ?>
                        <?php foreach ($customers as $customer): ?>
                            <div class="list-item">
                                <div class="item-primary">
                                    <strong><?= e($customer['full_name']) ?></strong>
                                    <span><?= e($customer['email']) ?></span>
                                </div>
                                <span class="item-meta"><?= e(date('d/m/Y', strtotime((string) $customer['created_at']))) ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
            <section class="panel">
                <div class="panel-heading">
                    <div><p class="section-label">Phương tiện</p><h2>Xe đăng ký gần đây</h2></div>
                    <a class="panel-link" href="<?= e(url('admin/vehicles')) ?>">Xem tất cả</a>
                </div>
                <div class="list">
                    <?php if ($vehicles === []): ?>
                        <div class="empty">Chưa có phương tiện được đăng ký.</div>
                    <?php else: ?>
                        <?php foreach ($vehicles as $vehicle): ?>
                            <div class="list-item">
                                <div class="item-primary">
                                    <strong><?= e($vehicle['brand_name'] . ' ' . $vehicle['model']) ?></strong>
                                    <span><?= e($vehicle['license_plate']) ?> · <?= e($vehicle['owner_name']) ?></span>
                                </div>
                                <span class="item-meta"><?= e(number_format((int) $vehicle['current_mileage'], 0, ',', '.')) ?> km</span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </main>
</div>
</body>
</html>
