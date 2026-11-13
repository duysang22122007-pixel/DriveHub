<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Duyệt dòng xe | AutoCare Hub</title>
    <style>
        :root{font-family:Inter,Arial,sans-serif;color:#172b3b;background:#f4f7fb}*{box-sizing:border-box}body{margin:0}header{background:#123b5d}
        .top,main{width:min(1320px,calc(100% - 36px));margin:auto}.top{min-height:68px;display:flex;align-items:center;justify-content:space-between;gap:15px}.brand{color:#fff;font-size:1.1rem;font-weight:800;text-decoration:none}.top nav{display:flex;align-items:center;gap:16px}.top a{color:#fff;text-decoration:none}.top a.active{text-decoration:underline;text-underline-offset:3px}
        main{padding:34px 0 60px}h1{margin:0;color:#123b5d}.sub{color:#6b7b8b;line-height:1.55}.toolbar{display:flex;justify-content:space-between;gap:18px}.button{display:inline-flex;align-items:center;justify-content:center;min-height:36px;padding:0 12px;border:0;border-radius:8px;background:#2563eb;color:#fff;font:inherit;font-weight:700;cursor:pointer;text-decoration:none}.button.secondary{background:#e8eef5;color:#123b5d}.button.reject{background:#fff0ef;color:#b42318}
        .notice{padding:11px 14px;margin:12px 0;border-radius:8px;background:#ecfdf3;color:#067647}.notice.error{background:#fff0ef;color:#b42318}.panel{padding:20px;margin-top:20px;border:1px solid #e3eaf1;border-radius:13px;background:#fff}.panel h2{margin:0 0 14px;color:#123b5d;font-size:1.1rem}
        .filters{display:grid;grid-template-columns:2fr 1.3fr 1.3fr auto;align-items:end;gap:11px}.field{display:grid;gap:5px}.field label{font-size:.78rem;color:#526678;font-weight:700}.field input,.field select{width:100%;min-height:39px;padding:8px 10px;border:1px solid #cad6e2;border-radius:8px;background:#fff;font:inherit}
        .bulk{display:flex;align-items:center;flex-wrap:wrap;gap:9px;margin:0 0 12px}.bulk select{min-height:36px;padding:6px 9px;border:1px solid #cad6e2;border-radius:8px;background:#fff;font:inherit}.table-wrap{overflow:auto;border:1px solid #e3eaf1;border-radius:10px}table{width:100%;min-width:1100px;border-collapse:collapse;text-align:left}th,td{padding:10px;border-bottom:1px solid #edf1f5;vertical-align:middle}th{background:#f8fafc;color:#5a6c7d;font-size:.8rem}td{font-size:.86rem}.status{display:inline-block;padding:4px 8px;border-radius:999px;background:#fff7e6;color:#9a6700;font-size:.78rem;font-weight:700}.status.verified{background:#ecfdf3;color:#067647}.status.not_suitable{background:#fff0ef;color:#b42318}.row-actions{display:flex;gap:6px}.row-actions form{margin:0}.source{font-size:.78rem;color:#667788}.empty{text-align:center;padding:30px;color:#6b7b8b}.pagination{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-top:14px;color:#667788}.pagination nav{display:flex;gap:8px;align-items:center}.disabled{opacity:.5;pointer-events:none}
        @media(max-width:820px){.top{align-items:flex-start;flex-direction:column;padding:13px 0}.top nav{flex-wrap:wrap;gap:11px}.toolbar{flex-direction:column}.filters{grid-template-columns:1fr 1fr}.filters .button{grid-column:1/-1}.pagination{align-items:flex-start;flex-direction:column}}
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
        <a href="<?= e(url('admin/vehicle-import/review')) ?>" class="active" aria-current="page">Duyệt dòng xe</a>
        <form method="post" action="<?= e(url('logout')) ?>"><?= csrf_field() ?><button class="button secondary" type="submit">Đăng xuất</button></form>
    </nav>
</div></header>
<main>
    <div class="toolbar">
        <div><h1>Duyệt dòng xe NHTSA</h1><p class="sub">Loại phương tiện NHTSA chỉ xác định hãng, không xác nhận từng model. Chỉ dòng xe đang hoạt động và đã duyệt mới xuất hiện trong form đăng ký.</p></div>
    </div>
    <?php if (is_string($success) && $success !== ''): ?><p class="notice" role="status"><?= e($success) ?></p><?php endif; ?>
    <?php if (is_string($error) && $error !== ''): ?><p class="notice error" role="alert"><?= e($error) ?></p><?php endif; ?>

    <section class="panel">
        <h2>Tìm kiếm và lọc</h2>
        <form method="get" action="<?= e(url('admin/vehicle-import/review')) ?>" class="filters">
            <div class="field"><label for="q">Tên dòng xe</label><input id="q" type="search" name="q" maxlength="100" value="<?= e($q) ?>" placeholder="Tìm tên dòng xe"></div>
            <div class="field"><label for="brand-id">Hãng xe</label><select id="brand-id" name="brand_id"><option value="">Tất cả hãng</option><?php foreach ($brands as $brand): ?><option value="<?= (int) $brand['id'] ?>" <?= $brandId === (int) $brand['id'] ? 'selected' : '' ?>><?= e($brand['name']) ?></option><?php endforeach; ?></select></div>
            <div class="field"><label for="verification-status">Trạng thái duyệt</label><select id="verification-status" name="status"><option value="unverified" <?= $status === 'unverified' ? 'selected' : '' ?>>Chờ xác minh</option><option value="verified" <?= $status === 'verified' ? 'selected' : '' ?>>Đã xác minh</option><option value="not_suitable" <?= $status === 'not_suitable' ? 'selected' : '' ?>>Không phù hợp</option><option value="all" <?= $status === 'all' ? 'selected' : '' ?>>Tất cả</option></select></div>
            <button class="button" type="submit">Lọc</button>
        </form>
    </section>

    <section class="panel">
        <h2>Kết quả (<?= (int) $total ?>)</h2>
        <form id="bulk-review-form" method="post" action="<?= e(url('admin/vehicle-import/review/bulk')) ?>">
            <?= csrf_field() ?>
            <div class="bulk">
                <label for="bulk-status">Áp dụng cho các dòng đã chọn</label>
                <select id="bulk-status" name="status" required><option value="">-- Chọn trạng thái --</option><option value="verified">Duyệt phù hợp</option><option value="not_suitable">Đánh dấu không phù hợp</option></select>
                <button class="button" type="submit" onclick="return confirm('Xác nhận cập nhật trạng thái các dòng xe đã chọn?')">Cập nhật hàng loạt</button>
            </div>
        </form>
        <div class="table-wrap">
            <?php if ($models === []): ?><div class="empty">Không có dòng xe phù hợp bộ lọc.</div><?php else: ?>
                <table>
                    <thead><tr><th>Chọn</th><th>Hãng</th><th>Dòng xe</th><th>Trạng thái duyệt</th><th>Trạng thái hoạt động</th><th>Nguồn và mã NHTSA</th><th>Người duyệt</th><th>Thao tác</th></tr></thead>
                    <tbody>
                    <?php foreach ($models as $model): ?>
                        <tr>
                            <td><input type="checkbox" name="model_ids[]" value="<?= (int) $model['id'] ?>" form="bulk-review-form" aria-label="Chọn <?= e($model['name']) ?>"></td>
                            <td><?= e($model['brand_name']) ?></td>
                            <td><?= e($model['name']) ?><?php if ($model['year_from'] !== null || $model['year_to'] !== null): ?><div class="source"><?= e($model['year_from'] ?? '—') ?>–<?= e($model['year_to'] ?? '—') ?></div><?php endif; ?></td>
                            <td><span class="status <?= e($model['verification_status']) ?>"><?= $model['verification_status'] === 'verified' ? 'Đã xác minh' : ($model['verification_status'] === 'not_suitable' ? 'Không phù hợp' : 'Chưa xác minh') ?></span></td>
                            <td><?= (int) $model['status'] === 1 ? 'Đang hoạt động' : 'Ngừng hoạt động' ?></td>
                            <td><div><?= e($model['data_source']) ?></div><div class="source">Make ID: <?= e($model['source_make_id'] ?? '—') ?> · Model ID: <?= e($model['source_model_id'] ?? '—') ?></div></td>
                            <td><?php if ($model['reviewer_name'] !== null): ?><?= e($model['reviewer_name']) ?><div class="source"><?= e($model['reviewed_at']) ?></div><?php else: ?>—<?php endif; ?></td>
                            <td>
                                <div class="row-actions">
                                    <form method="post" action="<?= e(url('admin/vehicle-import/review/' . (int) $model['id'])) ?>">
                                        <?= csrf_field() ?>
                                        <button class="button" type="submit" name="status" value="verified">Duyệt</button>
                                        <button class="button reject" type="submit" name="status" value="not_suitable">Không phù hợp</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
            $query = ['q' => $q, 'brand_id' => $brandId ?? '', 'status' => $status];
            $pageUrl = static function (int $number) use ($query): string {
                return url('admin/vehicle-import/review') . '?' . http_build_query($query + ['page' => $number]);
            };
        ?>
        <div class="pagination"><span>Trang <?= (int) $page ?> / <?= (int) $pageCount ?></span><nav aria-label="Phân trang">
            <a class="button secondary <?= $page <= 1 ? 'disabled' : '' ?>" href="<?= e($pageUrl(max(1, $page - 1))) ?>">Trước</a>
            <a class="button secondary <?= $page >= $pageCount ? 'disabled' : '' ?>" href="<?= e($pageUrl(min($pageCount, $page + 1))) ?>">Sau</a>
        </nav></div>
    </section>
</main>
</body>
</html>
