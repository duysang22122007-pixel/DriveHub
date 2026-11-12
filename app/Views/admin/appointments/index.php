<?php

declare(strict_types=1);

$filters = ['q' => $q, 'status' => $statusFilter, 'date' => $date];
$statusClasses = [
    'pending' => 'pending',
    'confirmed' => 'confirmed',
    'rejected' => 'rejected',
    'checked_in' => 'checked-in',
    'completed' => 'completed',
    'cancelled' => 'cancelled',
];

?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản lý lịch hẹn | AutoCare Hub</title>
    <style>
        :root{font-family:Inter,Arial,sans-serif;color:#172b3b;background:#f4f7fb}*{box-sizing:border-box}body{margin:0}
        header{background:#123b5d;color:#fff}.top,main{width:min(1160px,calc(100% - 36px));margin:auto}.top{min-height:68px;display:flex;align-items:center;justify-content:space-between;gap:15px}
        .brand{color:#fff;font-size:1.1rem;font-weight:800;text-decoration:none}.top nav{display:flex;align-items:center;gap:18px}.top a{color:#fff;text-decoration:none}.top a.active{text-decoration:underline;text-underline-offset:3px}
        main{padding:34px 0 60px}h1{margin:0;color:#123b5d}.sub{color:#6b7b8b}.toolbar{display:flex;align-items:center;justify-content:space-between;gap:18px;margin-bottom:20px}
        .button{display:inline-flex;align-items:center;justify-content:center;min-height:38px;padding:0 13px;border:0;border-radius:8px;background:#2563eb;color:#fff;font:inherit;font-weight:700;cursor:pointer;text-decoration:none}
        .button.secondary{background:#e8eef5;color:#123b5d}.notice{padding:12px 15px;margin:12px 0;border-radius:8px;background:#ecfdf3;color:#067647}.notice.error{background:#fff0ef;color:#b42318}
        .filter-bar{display:flex;flex-wrap:wrap;align-items:center;gap:10px;margin-bottom:18px;padding:14px 16px;background:#fff;border:1px solid #e3eaf1;border-radius:12px}
        .filter-bar input,.filter-bar select{height:40px;padding:0 11px;border:1px solid #d1d9e0;border-radius:8px;font:inherit;background:#fff}.filter-bar input[type=search]{flex:1;min-width:220px}
        .filter-bar input:focus,.filter-bar select:focus{outline:2px solid #2563eb33;border-color:#2563eb}.btn-search,.btn-reset{height:40px;padding:0 15px;border:0;border-radius:8px;font:inherit;font-weight:700;cursor:pointer}
        .btn-search{background:#2563eb;color:#fff}.btn-reset{display:inline-flex;align-items:center;background:#e8eef5;color:#123b5d;text-decoration:none}
        .result-meta{font-size:.88rem;color:#6b7b8b;margin:0 0 10px}.table-wrap{overflow:auto;background:#fff;border:1px solid #e3eaf1;border-radius:12px}
        table{width:100%;min-width:1240px;border-collapse:collapse;text-align:left}th,td{padding:13px 14px;border-bottom:1px solid #edf1f5;vertical-align:top}th{background:#f8fafc;color:#5a6c7d;font-size:.78rem;text-transform:uppercase;letter-spacing:.035em}td{font-size:.88rem}
        td p{margin:0 0 5px}td p:last-child{margin-bottom:0}.code{font-weight:800;color:#123b5d;white-space:nowrap}.muted{color:#718096;font-size:.82rem}.note{max-width:210px;white-space:pre-wrap;overflow-wrap:anywhere}
        .status{display:inline-block;padding:5px 9px;border-radius:99px;background:#f1f5f9;color:#475569;font-size:.78rem;font-weight:700;white-space:nowrap}
        .status.pending{background:#fff7e6;color:#9a6700}.status.confirmed,.status.checked-in{background:#ecfdf3;color:#067647}.status.rejected,.status.cancelled{background:#fff0ef;color:#b42318}.status.completed{background:#eaf2ff;color:#175cd3}
        .link{color:#175cd3;font-weight:700;text-decoration:none}.link:hover{text-decoration:underline}.empty{padding:36px;text-align:center;color:#6b7b8b}
        .pagination{display:flex;justify-content:center;align-items:center;gap:12px;margin:20px 0}.pagination a{padding:8px 12px;border-radius:8px;background:#fff;color:#123b5d;text-decoration:none;border:1px solid #e3eaf1}.pagination span{color:#6b7b8b}
        @media(max-width:760px){.top{align-items:flex-start;flex-direction:column;padding:13px 0}.top nav{flex-wrap:wrap;gap:12px}.toolbar{align-items:flex-start;flex-direction:column}.filter-bar{align-items:stretch;flex-direction:column}.filter-bar input,.filter-bar select{width:100%}}
    </style>
</head>
<body>
<header><div class="top">
    <a class="brand" href="<?= e(url('admin')) ?>">AutoCare Hub · Admin</a>
    <nav aria-label="Điều hướng Admin">
        <a href="<?= e(url('admin')) ?>">Tổng quan</a>
        <a href="<?= e(url('admin/appointments')) ?>" class="active" aria-current="page">Lịch hẹn</a>
        <a href="<?= e(url('admin/customers')) ?>">Khách hàng</a>
        <a href="<?= e(url('admin/vehicles')) ?>">Phương tiện</a>
        <a href="<?= e(url('admin/brands')) ?>">Hãng xe</a>
        <a href="<?= e(url('admin/models')) ?>">Dòng xe</a>
        <a href="<?= e(url('admin/vehicle-import')) ?>">Nhập dữ liệu xe</a>
        <a href="<?= e(url('admin/vehicle-import/review')) ?>">Duyệt dòng xe</a>
        <form method="post" action="<?= e(url('logout')) ?>"><?= csrf_field() ?><button class="button secondary" type="submit">Đăng xuất</button></form>
    </nav>
</div></header>
<main>
    <div class="toolbar">
        <div><h1>Quản lý lịch hẹn</h1><p class="sub">Tìm kiếm, xem thông tin và xử lý yêu cầu từ khách hàng.</p></div>
        <a class="button secondary" href="<?= e(url('admin/appointments') . '?' . http_build_query(['status' => 'pending'])) ?>">Xem lịch chờ xác nhận</a>
    </div>
    <?php foreach ($errors as $errorMessage): ?><p class="notice error" role="alert"><?= e($errorMessage) ?></p><?php endforeach; ?>
    <?php if (is_string($success) && $success !== ''): ?><p class="notice" role="status"><?= e($success) ?></p><?php endif; ?>
    <?php if (is_string($error) && $error !== ''): ?><p class="notice error" role="alert"><?= e($error) ?></p><?php endif; ?>

    <form method="get" action="<?= e(url('admin/appointments')) ?>" class="filter-bar">
        <input type="search" name="q" value="<?= e($q) ?>" placeholder="Mã lịch, tên, điện thoại hoặc biển số" autocomplete="off">
        <select name="status" aria-label="Lọc trạng thái">
            <option value="">Tất cả trạng thái</option>
            <?php foreach ($statuses as $statusValue => $statusName): ?>
                <option value="<?= e($statusValue) ?>" <?= $statusFilter === $statusValue ? 'selected' : '' ?>><?= e($statusName) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="date" name="date" value="<?= e($date) ?>" aria-label="Ngày hẹn">
        <button type="submit" class="btn-search">Tìm kiếm</button>
        <?php if ($q !== '' || $statusFilter !== '' || $date !== ''): ?><a href="<?= e(url('admin/appointments')) ?>" class="btn-reset">Xóa bộ lọc</a><?php endif; ?>
    </form>

    <p class="result-meta">Tìm thấy <strong><?= e($total) ?></strong> lịch hẹn · Trang <?= e($page) ?>/<?= e($pageCount) ?></p>
    <div class="table-wrap">
        <?php if ($appointments === []): ?>
            <div class="empty">Không có lịch hẹn phù hợp với bộ lọc.</div>
        <?php else: ?>
            <table>
                <thead><tr>
                    <th>Mã lịch / Hình thức</th><th>Khách hàng</th><th>Phương tiện</th>
                    <th>Ngày, giờ hẹn</th><th>Nội dung</th><th>Trạng thái</th>
                    <th>Ngày tạo</th><th>Thao tác</th>
                </tr></thead>
                <tbody>
                <?php foreach ($appointments as $appointment): ?>
                    <?php
                        $statusClass = $statusClasses[$appointment['status']] ?? '';
                        $dateLabel = date('d/m/Y', strtotime((string) $appointment['appointment_date']));
                        $timeLabel = substr((string) $appointment['appointment_time'], 0, 5);
                        $createdLabel = $appointment['created_at'] === null
                            ? '—'
                            : date('d/m/Y H:i', strtotime((string) $appointment['created_at']));
                        $typeLabel = $bookingTypes[$appointment['appointment_type']] ?? 'Khác';
                    ?>
                    <tr>
                        <td><p class="code"><?= e($appointment['appointment_code']) ?></p><p class="muted"><?= e($typeLabel) ?></p></td>
                        <td><p><strong><?= e($appointment['full_name']) ?></strong></p><p><?= e($appointment['phone'] ?? 'Chưa có số điện thoại') ?></p></td>
                        <td><p><strong><?= e($appointment['brand_name'] . ' ' . $appointment['model']) ?></strong></p><p><?= e($appointment['license_plate']) ?></p></td>
                        <td><?= e($dateLabel) ?><br><?= e($timeLabel) ?></td>
                        <td class="note"><?= e($appointment['note'] ?? 'Không có ghi chú') ?></td>
                        <td>
                            <span class="status <?= e($statusClass) ?>"><?= e($statuses[$appointment['status']] ?? 'Chưa xác định') ?></span>
                            <?php $ticketStatus = $maintenanceStatuses[$appointment['maintenance_ticket_status'] ?? ''] ?? null; ?>
                            <?php if ($ticketStatus !== null): ?><p class="muted">Phiếu sửa chữa: <?= e($ticketStatus) ?></p><?php endif; ?>
                            <?php if ($appointment['status'] === 'rejected' && is_string($appointment['admin_note']) && trim($appointment['admin_note']) !== ''): ?>
                                <p class="muted">Lý do: <?= e($appointment['admin_note']) ?></p>
                            <?php endif; ?>
                        </td>
                        <td><?= e($createdLabel) ?></td>
                        <td><a class="link" href="<?= e(url('admin/appointments/' . (int) $appointment['id'])) ?>">Chi tiết</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?php if ($pageCount > 1): ?>
        <nav class="pagination" aria-label="Phân trang">
            <?php if ($page > 1): ?><a href="<?= e(url('admin/appointments') . '?' . http_build_query($filters + ['page' => $page - 1])) ?>">← Trước</a><?php endif; ?>
            <span>Trang <?= e($page) ?> / <?= e($pageCount) ?></span>
            <?php if ($page < $pageCount): ?><a href="<?= e(url('admin/appointments') . '?' . http_build_query($filters + ['page' => $page + 1])) ?>">Sau →</a><?php endif; ?>
        </nav>
    <?php endif; ?>
</main>
</body>
</html>
