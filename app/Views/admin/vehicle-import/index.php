<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đồng bộ danh mục xe | AutoCare Hub</title>
    <style>
        :root{font-family:Inter,Arial,sans-serif;color:#172b3b;background:#f4f7fb}*{box-sizing:border-box}body{margin:0}header{background:#123b5d}
        .top,main{width:min(1240px,calc(100% - 36px));margin:auto}.top{min-height:68px;display:flex;align-items:center;justify-content:space-between;gap:15px}.brand{color:#fff;font-size:1.1rem;font-weight:800;text-decoration:none}.top nav{display:flex;align-items:center;gap:18px}.top a{color:#fff;text-decoration:none}
        main{padding:34px 0 60px}h1{margin:0;color:#123b5d}.sub{color:#6b7b8b;line-height:1.6}.toolbar{display:flex;justify-content:space-between;gap:18px}.button{display:inline-flex;align-items:center;justify-content:center;min-height:38px;padding:0 13px;border:0;border-radius:8px;background:#2563eb;color:#fff;font:inherit;font-weight:700;cursor:pointer;text-decoration:none}.button.secondary{background:#e8eef5;color:#123b5d}
        .notice{padding:11px 14px;margin:12px 0;border-radius:8px;background:#ecfdf3;color:#067647;overflow-wrap:anywhere}.notice.error{background:#fff0ef;color:#b42318}.panel{padding:20px;margin-top:20px;border:1px solid #e3eaf1;border-radius:13px;background:#fff}.panel h2{margin:0 0 14px;color:#123b5d;font-size:1.1rem}
        .choices{display:flex;flex-wrap:wrap;gap:12px}.choice{display:grid;grid-template-columns:20px 1fr;align-items:start;gap:8px;min-width:220px;padding:14px;border:1px solid #dce5ed;border-radius:9px}.choice input{margin-top:4px}.choice strong{display:block}.choice small{display:block;margin-top:5px;color:#6b7b8b}.hint{margin:14px 0 0;color:#6b7b8b;font-size:.88rem}.table-wrap{overflow:auto;border:1px solid #e3eaf1;border-radius:10px}table{width:100%;min-width:1000px;border-collapse:collapse;text-align:left}th,td{padding:11px;border-bottom:1px solid #edf1f5;vertical-align:middle}th{background:#f8fafc;color:#5a6c7d;font-size:.8rem}td{font-size:.87rem}.progress{min-width:110px}.track{height:8px;margin-top:5px;border-radius:5px;background:#e8eef5;overflow:hidden}.track span{display:block;height:100%;background:#2563eb}.empty{padding:25px;text-align:center;color:#6b7b8b}.resume{margin:0}
        @media(max-width:760px){.top{align-items:flex-start;flex-direction:column;padding:13px 0}.top nav{flex-wrap:wrap;gap:12px}.toolbar{flex-direction:column}.choices{display:grid}.choice{min-width:0}}
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
    <div class="toolbar"><div><h1>Đồng bộ thư viện xe</h1><p class="sub">Nguồn dữ liệu: NHTSA vPIC. Dữ liệu nhập mới được đánh dấu chưa xác minh; thông tin Admin đã tạo hoặc chỉnh sửa được giữ nguyên.</p></div></div>
    <?php if (is_string($success) && $success !== ''): ?><p class="notice" role="status"><?= e($success) ?></p><?php endif; ?>
    <?php if (is_string($error) && $error !== ''): ?><p class="notice error" role="alert"><?= e($error) ?></p><?php endif; ?>

    <section class="panel">
        <h2>Bắt đầu đồng bộ</h2>
        <form method="post" action="<?= e(url('admin/vehicle-import')) ?>">
            <?= csrf_field() ?>
            <div class="choices">
                <label class="choice"><input type="radio" name="mode" value="brands" required><span><strong>Hãng xe</strong><small>Nhận danh sách hãng từ NHTSA và xử lý tối đa 5 hãng mỗi lượt.</small></span></label>
                <label class="choice"><input type="radio" name="mode" value="models"><span><strong>Dòng xe</strong><small>Đồng bộ dòng xe cho các hãng đã ghép mã nguồn; một hãng mỗi lượt.</small></span></label>
                <label class="choice"><input type="radio" name="mode" value="all"><span><strong>Toàn bộ</strong><small>Đồng bộ hãng và dòng xe; một hãng mỗi lượt để giới hạn thời gian yêu cầu.</small></span></label>
            </div>
            <label class="choice"><input type="checkbox" name="include_trucks" value="1"><span><strong>Thêm hãng có loại Truck</strong><small>Bao gồm xe tải/bán tải theo phân loại NHTSA; lựa chọn này không tự xác định loại của từng model.</small></span></label>
            <p class="hint">Danh sách hãng chỉ gồm các hãng có Passenger Car hoặc MPV; Truck là tùy chọn. NHTSA không phân loại chắc chắn từng model nên model nhập mới vẫn chờ duyệt và không hiển thị cho khách đến khi Admin xác minh.</p>
            <p class="hint">Mỗi lượt lưu trạng thái trong database. Nếu API lỗi hoặc phiên bị gián đoạn, tiếp tục phiên ở bảng lịch sử.</p>
            <p><button class="button" type="submit">Bắt đầu đồng bộ</button></p>
        </form>
    </section>

    <section class="panel">
        <h2>Lịch sử và tiến trình</h2>
        <div class="table-wrap">
            <?php if ($sessions === []): ?><div class="empty">Chưa có phiên đồng bộ.</div><?php else: ?>
                <table>
                    <thead><tr><th>Phiên</th><th>Loại</th><th>Trạng thái</th><th>Tiến độ</th><th>Đã thêm</th><th>Trùng</th><th>Lỗi</th><th>Thông tin</th><th>Thời gian</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($sessions as $session):
                        $total = (int) $session['makes_total'];
                        $processed = (int) $session['makes_processed'];
                        $percent = $total > 0 ? min(100, (int) floor($processed * 100 / $total)) : 100;
                        $canContinue = in_array($session['status'], ['queued', 'running'], true);
                        $modeLabels = ['brands' => 'Hãng xe', 'models' => 'Dòng xe', 'all' => 'Toàn bộ'];
                        $statusLabels = [
                            'queued' => 'Đang chờ',
                            'running' => 'Đang chạy',
                            'completed' => 'Hoàn tất',
                            'completed_with_errors' => 'Hoàn tất có lỗi',
                            'failed' => 'Thất bại',
                        ];
                    ?>
                        <tr>
                            <td>#<?= (int) $session['id'] ?></td>
                            <td><?= e($modeLabels[$session['mode']] ?? $session['mode']) ?></td>
                            <td><?= e($statusLabels[$session['status']] ?? $session['status']) ?></td>
                            <td class="progress"><?= $processed ?>/<?= $total ?> (<?= $percent ?>%)<div class="track"><span style="width:<?= $percent ?>%"></span></div></td>
                            <td><?= (int) $session['added_count'] ?></td>
                            <td><?= (int) $session['duplicate_count'] ?></td>
                            <td><?= (int) $session['error_count'] ?></td>
                            <td><?= e($session['last_error'] ?: $session['latest_detail'] ?: '—') ?></td>
                            <td><?= e($session['updated_at']) ?></td>
                            <td><?php if ($canContinue): ?><form class="resume" method="post" action="<?= e(url('admin/vehicle-import/' . (int) $session['id'] . '/continue')) ?>"><?= csrf_field() ?><button class="button" type="submit">Tiếp tục</button></form><?php else: ?>—<?php endif; ?></td>
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
