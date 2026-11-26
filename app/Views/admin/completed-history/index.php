<?php

declare(strict_types=1);

$pageTitle = 'Lịch sử đã hoàn thành';
$activeNav = 'completed-history';
$pageEyebrow = 'Vận hành';
$pageDescription = 'Theo dõi toàn bộ lịch hẹn và phiếu sửa chữa đã hoàn tất, tình trạng thanh toán và đánh giá từ khách hàng.';
$pageActions = '<a class="button secondary" href="' . e(url('admin/tickets')) . '">Phiếu sửa chữa</a>'
    . '<a class="button secondary" href="' . e(url('admin/appointments?status=completed')) . '">Lịch hẹn hoàn thành</a>';
$pageStyles = '.data-table{min-width:1260px}.rating-stars{color:#d97706;font-weight:800;letter-spacing:.05em}.review-excerpt{max-width:240px;color:#475569;font-size:.8rem;line-height:1.45}';

$filters = [
    'q' => $q,
    'from_date' => $fromDate,
    'to_date' => $toDate,
    'payment' => $paymentStatus,
    'review' => $reviewStatus,
];

$invoiceStatusClass = [
    'unpaid' => 'pending',
    'partial' => 'pending',
    'paid' => 'confirmed',
    'cancelled' => 'cancelled',
];

ob_start();
?>
<?php foreach ($errors as $errorMessage): ?><p class="notice error" role="alert"><?= e($errorMessage) ?></p><?php endforeach; ?>
<?php if (is_string($success) && $success !== ''): ?><p class="notice" role="status"><?= e($success) ?></p><?php endif; ?>
<?php if (is_string($error) && $error !== ''): ?><p class="notice error" role="alert"><?= e($error) ?></p><?php endif; ?>

<section class="metrics-grid" aria-label="Thống kê vận hành đã hoàn thành">
    <article class="metric">
        <div class="metric-top"><p class="metric-label">Đã hoàn thành</p></div>
        <p class="metric-value"><?= e((string) $summary['completed_count']) ?></p>
        <p class="metric-note">Tổng lịch hẹn / phiếu sửa chữa hoàn tất</p>
    </article>
    <article class="metric revenue">
        <div class="metric-top"><p class="metric-label">Doanh thu đã thu</p></div>
        <p class="metric-value"><?= e(number_format((float) $summary['total_revenue'], 0, ',', '.')) ?> ₫</p>
        <p class="metric-note">Tổng tiền thanh toán thành công</p>
    </article>
    <article class="metric">
        <div class="metric-top"><p class="metric-label">Đã thanh toán đủ</p></div>
        <p class="metric-value"><?= e((string) $summary['paid_count']) ?></p>
        <p class="metric-note">Còn <?= e((string) $summary['pending_payment_count']) ?> lượt chưa thanh toán đủ</p>
    </article>
    <article class="metric pending">
        <div class="metric-top"><p class="metric-label">Đánh giá trung bình</p></div>
        <p class="metric-value"><?= $summary['review_count'] > 0 ? e(number_format((float) $summary['avg_rating'], 1)) . ' / 5 ★' : '—' ?></p>
        <p class="metric-note"><?= e((string) $summary['review_count']) ?> lượt phản hồi từ khách hàng</p>
    </article>
</section>

<section class="panel" aria-label="Danh sách lịch sử đã hoàn thành">
    <div class="panel-head">
        <div>
            <h2 class="panel-title">Danh sách lượt dịch vụ đã hoàn thành</h2>
            <p class="panel-description">Kiểm tra tiến độ thanh toán và kết quả biểu mẫu đánh giá sau thanh toán.</p>
        </div>
    </div>
    <div class="panel-body">
        <form method="get" action="<?= e(url('admin/completed-history')) ?>" class="filter-bar">
            <div class="filter-field" style="flex:2;min-width:210px">
                <label for="history-q">Tìm kiếm</label>
                <input id="history-q" type="search" name="q" value="<?= e($q) ?>" placeholder="Mã lịch, mã phiếu, mã HĐ, tên khách, SĐT, biển số" autocomplete="off">
            </div>
            <div class="filter-field" style="min-width:140px">
                <label for="history-from">Từ ngày</label>
                <input id="history-from" type="date" name="from_date" value="<?= e($fromDate) ?>">
            </div>
            <div class="filter-field" style="min-width:140px">
                <label for="history-to">Đến ngày</label>
                <input id="history-to" type="date" name="to_date" value="<?= e($toDate) ?>">
            </div>
            <div class="filter-field" style="min-width:155px">
                <label for="history-payment">Thanh toán</label>
                <select id="history-payment" name="payment">
                    <option value="">Tất cả</option>
                    <option value="paid" <?= $paymentStatus === 'paid' ? 'selected' : '' ?>>Đã thanh toán</option>
                    <option value="partial" <?= $paymentStatus === 'partial' ? 'selected' : '' ?>>Thanh toán một phần</option>
                    <option value="unpaid" <?= $paymentStatus === 'unpaid' ? 'selected' : '' ?>>Chưa lập / chưa thu</option>
                </select>
            </div>
            <div class="filter-field" style="min-width:165px">
                <label for="history-review">Đánh giá</label>
                <select id="history-review" name="review">
                    <option value="">Tất cả</option>
                    <option value="reviewed" <?= $reviewStatus === 'reviewed' ? 'selected' : '' ?>>Đã đánh giá</option>
                    <option value="sent" <?= $reviewStatus === 'sent' ? 'selected' : '' ?>>Đã gửi biểu mẫu</option>
                    <option value="not_reviewed" <?= $reviewStatus === 'not_reviewed' ? 'selected' : '' ?>>Chưa đánh giá</option>
                </select>
            </div>
            <div class="filter-actions" style="align-self:end">
                <button type="submit" class="btn-search">Lọc</button>
                <?php if ($q !== '' || $fromDate !== '' || $toDate !== '' || $paymentStatus !== '' || $reviewStatus !== ''): ?>
                    <a href="<?= e(url('admin/completed-history')) ?>" class="btn-reset">Xóa lọc</a>
                <?php endif; ?>
            </div>
        </form>

        <p class="table-meta">Tìm thấy <strong><?= e((string) $total) ?></strong> lượt đã hoàn thành · Trang <?= e((string) $page) ?>/<?= e((string) $pageCount) ?></p>

        <div class="table-wrap">
            <?php if ($items === []): ?>
                <div class="empty-state">Chưa có lịch sử hoàn thành nào phù hợp với bộ lọc.</div>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Lịch hẹn / Phiếu sửa chữa</th>
                            <th>Khách hàng</th>
                            <th>Phương tiện</th>
                            <th>Hoàn thành lúc</th>
                            <th>Hóa đơn &amp; Thanh toán</th>
                            <th>Biểu mẫu đánh giá</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <?php
                                $completedRaw = $item['completed_at'] ?? $item['appointment_updated_at'] ?? $item['appointment_date'];
                                $completedAtLabel = $completedRaw ? date('d/m/Y H:i', strtotime((string) $completedRaw)) : '—';
                                $invStatus = $item['invoice_status'] ?? null;
                                $hasTicket = !empty($item['ticket_id']);
                            ?>
                            <tr>
                                <td>
                                    <p class="code">
                                        <a class="link" href="<?= e(url('admin/appointments/' . (int) $item['appointment_id'])) ?>"><?= e($item['appointment_code']) ?></a>
                                    </p>
                                    <?php if ($hasTicket): ?>
                                        <p class="cell-sub">Phiếu: <a class="link" href="<?= e(url('admin/tickets/' . (int) $item['ticket_id'])) ?>"><?= e($item['ticket_code']) ?></a></p>
                                    <?php else: ?>
                                        <p class="cell-sub">Hoàn thành trực tiếp (chưa lập phiếu)</p>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <p class="cell-main"><?= e($item['customer_name']) ?></p>
                                    <p class="cell-sub"><?= e($item['customer_phone'] ?? '—') ?></p>
                                </td>
                                <td>
                                    <p class="cell-main"><?= e($item['brand_name'] . ' ' . $item['model']) ?></p>
                                    <p class="cell-sub"><?= e($item['license_plate']) ?><?= !empty($item['mileage']) ? ' · ' . number_format((int) $item['mileage'], 0, ',', '.') . ' km' : '' ?></p>
                                </td>
                                <td>
                                    <span class="cell-main"><?= e($completedAtLabel) ?></span>
                                    <span class="cell-sub">Hẹn: <?= e(date('d/m/Y', strtotime((string) $item['appointment_date']))) ?> <?= e(substr((string) $item['appointment_time'], 0, 5)) ?></span>
                                </td>
                                <td>
                                    <?php if ($invStatus !== null): ?>
                                        <p>
                                            <span class="status <?= e($invoiceStatusClass[$invStatus] ?? 'pending') ?>">
                                                <?= e($invoiceStatusLabels[$invStatus] ?? $invStatus) ?>
                                            </span>
                                        </p>
                                        <p class="cell-sub">
                                            <?= e($item['invoice_code']) ?> · <strong><?= e(number_format((float) $item['total_amount'], 0, ',', '.')) ?> ₫</strong>
                                            <?php if ($invStatus === 'partial'): ?>
                                                (Đã thu <?= e(number_format((float) $item['amount_paid'], 0, ',', '.')) ?> ₫)
                                            <?php endif; ?>
                                        </p>
                                    <?php elseif ($hasTicket): ?>
                                        <span class="status pending">Chưa lập hóa đơn</span>
                                        <p class="cell-sub">Ước tính: <?= e(number_format((float) $item['ticket_estimated_total'], 0, ',', '.')) ?> ₫</p>
                                    <?php else: ?>
                                        <span class="cell-sub">Chưa có phiếu / hóa đơn</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($item['review_id'])): ?>
                                        <?php $stars = max(1, min(5, (int) $item['rating'])); ?>
                                        <p class="rating-stars"><?= str_repeat('★', $stars) . str_repeat('☆', 5 - $stars) ?> (<?= $stars ?>/5)</p>
                                        <?php if (is_string($item['review_comment']) && trim($item['review_comment']) !== ''): ?>
                                            <p class="review-excerpt"><?= e($item['review_comment']) ?></p>
                                        <?php endif; ?>
                                        <p class="cell-sub">
                                            <?= $item['review_status'] === 'visible' ? 'Đang hiển thị' : 'Đang ẩn' ?>
                                            · <?= e(date('d/m/Y H:i', strtotime((string) $item['reviewed_at']))) ?>
                                        </p>
                                    <?php elseif ($invStatus === 'paid'): ?>
                                        <?php if (!empty($item['review_sent_at'])): ?>
                                            <span class="status completed">Đã gửi biểu mẫu</span>
                                            <p class="cell-sub">Gửi lúc <?= e(date('d/m/Y H:i', strtotime((string) $item['review_sent_at']))) ?></p>
                                        <?php else: ?>
                                            <span class="status pending">Chưa gửi biểu mẫu</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="cell-sub">Gửi tự động sau khi thanh toán đủ</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="actions">
                                        <?php if ($hasTicket): ?>
                                            <a class="button small secondary" href="<?= e(url('admin/tickets/' . (int) $item['ticket_id'])) ?>">Phiếu &amp; Thanh toán</a>
                                        <?php else: ?>
                                            <a class="button small secondary" href="<?= e(url('admin/appointments/' . (int) $item['appointment_id'])) ?>">Xem lịch hẹn</a>
                                        <?php endif; ?>
                                        <?php if ($hasTicket && $invStatus === 'paid' && empty($item['review_id'])): ?>
                                            <form method="post" action="<?= e(url('admin/tickets/' . (int) $item['ticket_id'] . '/send-review')) ?>">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="redirect" value="history">
                                                <button class="button small" type="submit">Gửi nhắc đánh giá</button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if (!empty($item['review_id'])): ?>
                                            <form method="post" action="<?= e(url('admin/reviews/' . (int) $item['review_id'] . '/toggle')) ?>">
                                                <?= csrf_field() ?>
                                                <button class="button small ghost" type="submit"><?= $item['review_status'] === 'visible' ? 'Ẩn đánh giá' : 'Hiện đánh giá' ?></button>
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

        <?php if ($pageCount > 1): ?>
            <nav class="pagination" aria-label="Phân trang">
                <?php if ($page > 1): ?><a href="<?= e(url('admin/completed-history') . '?' . http_build_query($filters + ['page' => $page - 1])) ?>">← Trước</a><?php endif; ?>
                <span>Trang <?= e((string) $page) ?> / <?= e((string) $pageCount) ?></span>
                <?php if ($page < $pageCount): ?><a href="<?= e(url('admin/completed-history') . '?' . http_build_query($filters + ['page' => $page + 1])) ?>">Sau →</a><?php endif; ?>
            </nav>
        <?php endif; ?>
    </div>
</section>
<?php
$content = ob_get_clean();
require BASE_PATH . '/app/Views/admin/_layout.php';
