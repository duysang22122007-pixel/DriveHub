<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Khách hàng | DriveHub</title>
    <style>
        :root{font-family:Inter,Arial,sans-serif;color:#172b3b;background:#f4f7fb}*{box-sizing:border-box}body{margin:0}
        header{background:#123b5d;color:#fff}.top,main{width:min(1160px,calc(100% - 36px));margin:auto}.top{min-height:68px;display:flex;align-items:center;justify-content:space-between;gap:15px}
        .brand{color:#fff;font-size:1.1rem;font-weight:800;text-decoration:none}.top nav{display:flex;align-items:center;gap:18px}.top a{color:#fff;text-decoration:none}.top a.active{text-decoration:underline;text-underline-offset:3px}
        main{padding:34px 0 60px}h1{margin:0;color:#123b5d}.sub{color:#6b7b8b}
        .toolbar{display:flex;align-items:center;justify-content:space-between;gap:18px;margin-bottom:20px}
        .button{display:inline-flex;align-items:center;justify-content:center;min-height:38px;padding:0 13px;border:0;border-radius:8px;background:#2563eb;color:#fff;font:inherit;font-weight:700;cursor:pointer;text-decoration:none}
        .button.secondary{background:#e8eef5;color:#123b5d}.button.danger{background:#fff0ef;color:#b42318}.button.success-btn{background:#ecfdf3;color:#067647}
        .notice{padding:12px 15px;margin:12px 0;border-radius:8px;background:#ecfdf3;color:#067647}.notice.error{background:#fff0ef;color:#b42318}
        /* Search & Filter bar */
        .filter-bar{display:flex;flex-wrap:wrap;align-items:center;gap:10px;margin-bottom:18px;padding:14px 16px;background:#fff;border:1px solid #e3eaf1;border-radius:12px}
        .filter-bar input[type=search]{flex:1;min-width:200px;height:38px;padding:0 12px;border:1px solid #d1d9e0;border-radius:8px;font:inherit;font-size:.9rem;outline:none}
        .filter-bar input[type=search]:focus{border-color:#2563eb;box-shadow:0 0 0 2px #2563eb22}
        .filter-bar select{height:38px;padding:0 10px;border:1px solid #d1d9e0;border-radius:8px;font:inherit;font-size:.9rem;background:#fff;cursor:pointer}
        .filter-bar select:focus{outline:none;border-color:#2563eb}
        .filter-bar .btn-search{height:38px;padding:0 18px;border:0;border-radius:8px;background:#2563eb;color:#fff;font:inherit;font-weight:700;cursor:pointer}
        .filter-bar .btn-reset{height:38px;padding:0 14px;border:0;border-radius:8px;background:#e8eef5;color:#123b5d;font:inherit;font-weight:600;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center}
        .result-meta{font-size:.85rem;color:#6b7b8b;margin-bottom:10px}
        /* Table */
        .table-wrap{overflow:auto;background:#fff;border:1px solid #e3eaf1;border-radius:12px}table{width:100%;min-width:800px;border-collapse:collapse;text-align:left}
        th,td{padding:14px 16px;border-bottom:1px solid #edf1f5;vertical-align:middle}th{background:#f8fafc;color:#5a6c7d;font-size:.81rem;text-transform:uppercase;letter-spacing:.04em}td{font-size:.9rem}
        .status{display:inline-block;padding:5px 9px;border-radius:99px;background:#ecfdf3;color:#067647;font-size:.8rem;font-weight:700}
        .status.locked{background:#fff1f0;color:#b42318}.status.inactive{background:#f4f7fb;color:#6b7b8b}
        .actions{display:flex;align-items:center;gap:8px}.actions form{margin:0}
        .empty{padding:36px;text-align:center;color:#6b7b8b}
        @media(max-width:640px){.top{align-items:flex-start;flex-direction:column;padding:13px 0}.top nav{flex-wrap:wrap;gap:12px}.toolbar{align-items:flex-start;flex-direction:column}.filter-bar{flex-direction:column;align-items:stretch}}
    </style>
    <link rel="stylesheet" href="<?= e(url('assets/admin.css')) ?>">
</head>
<body>
<?php $activeAdminPage = 'customers'; require __DIR__ . '/../partials/navigation.php'; ?>

<main>
    <div class="toolbar">
        <div>
            <h1>Khách hàng</h1>
            <p class="sub">Danh sách tài khoản khách hàng và phương tiện đã đăng ký.</p>
        </div>
        <a class="button secondary" href="<?= e(url('admin/customers') . '?' . http_build_query(['q' => $q, 'status' => $status, 'export' => 'csv'])) ?>">
            ⬇ Xuất CSV
        </a>
    </div>

    <?php if (is_string($success) && $success !== ''): ?><p class="notice" role="status"><?= e($success) ?></p><?php endif; ?>
    <?php if (is_string($error) && $error !== ''): ?><p class="notice error" role="alert"><?= e($error) ?></p><?php endif; ?>

    <!-- Search & Filter -->
    <form method="get" action="<?= e(url('admin/customers')) ?>" class="filter-bar">
        <input
            type="search"
            name="q"
            value="<?= e($q) ?>"
            placeholder="Tìm theo tên, email, số điện thoại…"
            autocomplete="off"
        >
        <select name="status">
            <option value="" <?= $status === '' ? 'selected' : '' ?>>Tất cả trạng thái</option>
            <option value="active"   <?= $status === 'active'   ? 'selected' : '' ?>>Đang hoạt động</option>
            <option value="locked"   <?= $status === 'locked'   ? 'selected' : '' ?>>Đã khóa</option>
            <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Không hoạt động</option>
        </select>
        <button type="submit" class="btn-search">🔍 Tìm kiếm</button>
        <?php if ($q !== '' || $status !== ''): ?>
            <a href="<?= e(url('admin/customers')) ?>" class="btn-reset">✕ Xóa bộ lọc</a>
        <?php endif; ?>
    </form>

    <p class="result-meta">
        <?php if ($q !== '' || $status !== ''): ?>
            Tìm thấy <strong><?= count($customers) ?></strong> kết quả
            <?php if ($q !== ''): ?> cho "<strong><?= e($q) ?></strong>"<?php endif; ?>
            <?php if ($status !== ''): ?> · Trạng thái: <strong><?= e($status === 'active' ? 'Đang hoạt động' : ($status === 'locked' ? 'Đã khóa' : 'Không hoạt động')) ?></strong><?php endif; ?>
        <?php else: ?>
            Tổng cộng <strong><?= count($customers) ?></strong> khách hàng
        <?php endif; ?>
    </p>

    <div class="table-wrap">
        <?php if ($customers === []): ?>
            <div class="empty">
                <?= ($q !== '' || $status !== '') ? 'Không tìm thấy khách hàng phù hợp.' : 'Chưa có tài khoản khách hàng.' ?>
            </div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Khách hàng</th>
                    <th>Email</th>
                    <th>Điện thoại</th>
                    <th>Số xe</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($customers as $customer): ?>
                <tr>
                    <td><strong><?= e($customer['full_name']) ?></strong></td>
                    <td><?= e($customer['email']) ?></td>
                    <td><?= e($customer['phone'] ?? '—') ?></td>
                    <td><?= e((int) $customer['vehicle_count']) ?></td>
                    <td>
                        <?php
                            $st = $customer['status'];
                            $stClass = $st === 'locked' ? 'locked' : ($st === 'inactive' ? 'inactive' : '');
                            $stLabel = $st === 'active' ? 'Đang hoạt động' : ($st === 'locked' ? 'Đã khóa' : 'Không hoạt động');
                        ?>
                        <span class="status <?= $stClass ?>"><?= e($stLabel) ?></span>
                    </td>
                    <td><?= e(date('d/m/Y', strtotime((string) $customer['created_at']))) ?></td>
                    <td>
                        <div class="actions">
                            <?php if ($customer['status'] === 'active'): ?>
                                <form method="post" action="<?= e(url('admin/customers/' . (int) $customer['id'] . '/status')) ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="status" value="locked">
                                    <button class="button danger" type="submit">Khóa</button>
                                </form>
                            <?php else: ?>
                                <form method="post" action="<?= e(url('admin/customers/' . (int) $customer['id'] . '/status')) ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="status" value="active">
                                    <button class="button success-btn" type="submit">Mở khóa</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</main>
</div>
</div>
</body>
</html>
