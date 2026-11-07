<?php
$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$statusLabels = ['received' => 'Đã tiếp nhận', 'in_progress' => 'Đang sửa chữa', 'completed' => 'Hoàn tất', 'cancelled' => 'Đã hủy'];
$isAdmin = current_admin() !== null;
$totalCost = array_sum(array_map(
    static fn (array $detail): float => (int) $detail['quantity'] * (float) $detail['unit_price'],
    $order['details']
));
?>
<section class="workflow-page">
    <div class="container workflow-narrow">
        <p class="eyebrow"><span></span>Phiếu sửa chữa</p>
        <h1>Phiếu #<?= (int) $order['id'] ?></h1>
        <?php if ($errors !== []): ?>
            <div class="workflow-alert workflow-alert--error" role="alert">
                <?php foreach ($errors as $error): ?><p><?= $e($error) ?></p><?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if ($flash !== null): ?><div class="workflow-alert"><?= $e($flash) ?></div><?php endif; ?>
        <article class="workflow-card">
            <div class="workflow-card__heading">
                <h2><?= $e($order['car_brand'] . ' ' . $order['car_model']) ?> · <?= $e($order['license_plate']) ?></h2>
                <span class="workflow-status"><?= $e($statusLabels[$order['status']] ?? $order['status']) ?></span>
            </div>
            <p><strong>Khách hàng:</strong> <?= $e($order['customer_name']) ?></p>
            <p><strong>Liên hệ:</strong> <?= $e($order['customer_phone'] ?? '') ?> · <?= $e($order['customer_email'] ?? '') ?></p>
            <p><strong>Tiếp nhận:</strong> <?= $e(date('d/m/Y H:i', strtotime($order['received_date']))) ?> · <?= number_format((int) $order['received_mileage']) ?> km</p>
            <p><strong>Mô tả:</strong><br><?= nl2br($e($order['problem_description'])) ?></p>
            <?php if ($order['diagnosis']): ?><p><strong>Chẩn đoán / kết quả:</strong><br><?= nl2br($e($order['diagnosis'])) ?></p><?php endif; ?>
            <?php if ($order['completed_mileage'] !== null): ?><p><strong>Số km khi hoàn tất:</strong> <?= number_format((int) $order['completed_mileage']) ?> km</p><?php endif; ?>
            <?php if ($order['details'] !== []): ?>
                <h3>Dịch vụ / hạng mục</h3>
                <div class="workflow-detail-list">
                    <?php foreach ($order['details'] as $detail): ?>
                        <article class="workflow-detail">
                            <strong><?= $e($detail['service_name']) ?></strong>
                            <span><?= (int) $detail['quantity'] ?> × <?= number_format((float) $detail['unit_price'], 2, ',', '.') ?> ₫</span>
                            <?php if ($detail['note']): ?><p><?= nl2br($e($detail['note'])) ?></p><?php endif; ?>
                            <?php if ($isAdmin && in_array($order['status'], ['received', 'in_progress'], true)): ?>
                                <form class="workflow-form workflow-form--inline" method="post" action="<?= BASE_URL ?>?action=repair-order&amp;id=<?= (int) $order['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
                                    <input type="hidden" name="operation" value="update_detail">
                                    <input type="hidden" name="detail_id" value="<?= (int) $detail['id'] ?>">
                                    <label>Số lượng<input type="number" name="quantity" min="1" max="9999" value="<?= (int) $detail['quantity'] ?>" required></label>
                                    <label>Đơn giá (₫)<input type="number" name="unit_price" min="0" max="9999999999.99" step="0.01" value="<?= $e($detail['unit_price']) ?>" required></label>
                                    <label>Ghi chú<input type="text" name="note" maxlength="2000" value="<?= $e($detail['note'] ?? '') ?>"></label>
                                    <button class="workflow-button workflow-button--muted" type="submit">Lưu hạng mục</button>
                                </form>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <p><strong>Tổng tạm tính:</strong> <?= number_format($totalCost, 2, ',', '.') ?> ₫</p>
        </article>

        <?php if ($isAdmin && !in_array($order['status'], ['completed', 'cancelled'], true)): ?>
            <?php if (in_array($order['status'], ['received', 'in_progress'], true)): ?>
                <form class="workflow-card workflow-form" method="post" action="<?= BASE_URL ?>?action=repair-order&amp;id=<?= (int) $order['id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
                    <input type="hidden" name="operation" value="save_diagnosis">
                    <h2>Cập nhật chẩn đoán</h2>
                    <label>Chẩn đoán tình trạng xe
                        <textarea name="diagnosis" rows="4" maxlength="5000"><?= $e($order['diagnosis'] ?? '') ?></textarea>
                    </label>
                    <button class="workflow-button workflow-button--muted" type="submit">Lưu chẩn đoán</button>
                </form>

                <form class="workflow-card workflow-form" method="post" action="<?= BASE_URL ?>?action=repair-order&amp;id=<?= (int) $order['id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
                    <input type="hidden" name="operation" value="add_detail">
                    <h2>Thêm hạng mục sửa chữa</h2>
                    <label>Dịch vụ / hạng mục
                        <select name="service_id" required>
                            <option value="">Chọn hạng mục</option>
                            <?php foreach ($services as $service): ?>
                                <option value="<?= (int) $service['id'] ?>"><?= $e($service['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>Số lượng<input type="number" name="quantity" min="1" max="9999" value="1" required></label>
                    <label>Đơn giá (₫)<input type="number" name="unit_price" min="0" max="9999999999.99" step="0.01" value="0" required></label>
                    <label>Ghi chú<textarea name="note" rows="2" maxlength="2000"></textarea></label>
                    <button class="workflow-button" type="submit">Thêm hạng mục</button>
                </form>
            <?php endif; ?>
            <?php if ($order['status'] === 'received'): ?>
                <form class="workflow-card workflow-actions" method="post" action="<?= BASE_URL ?>?action=repair-order&amp;id=<?= (int) $order['id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
                    <button class="workflow-button" type="submit" name="operation" value="in_progress">Bắt đầu sửa chữa</button>
                </form>
            <?php endif; ?>
            <form class="workflow-card workflow-form" method="post" action="<?= BASE_URL ?>?action=repair-order&amp;id=<?= (int) $order['id'] ?>">
                <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
                <h2>Hoàn thành sửa chữa</h2>
                <label>Số km hiện tại
                    <input type="number" name="mileage" min="<?= max((int) $order['current_mileage'], (int) $order['received_mileage']) ?>" value="<?= max((int) $order['current_mileage'], (int) $order['received_mileage']) ?>" required>
                </label>
                <label>Kết quả / chẩn đoán
                    <textarea name="diagnosis" rows="4" maxlength="5000"><?= $e($order['diagnosis'] ?? '') ?></textarea>
                </label>
                <p class="workflow-help">Xác nhận sẽ đồng thời hoàn tất phiếu, cập nhật lịch hẹn, số km xe và lịch sử bảo dưỡng.</p>
                <button class="btn btn--primary" type="submit" name="operation" value="complete">Hoàn thành sửa chữa</button>
            </form>
        <?php endif; ?>
        <form class="workflow-actions" method="post" action="<?= BASE_URL ?>?action=repair-order&amp;id=<?= (int) $order['id'] ?>" onsubmit="return confirm('Bạn có chắc muốn hủy phiếu sửa chữa này?');">
            <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
            <button class="workflow-button workflow-button--muted" type="submit" name="operation" value="cancel">Hủy phiếu sửa chữa</button>
        </form>
        <p class="workflow-actions">
            <a class="workflow-link" href="<?= BASE_URL ?>?action=<?= $isAdmin ? 'repair-orders' : 'my-appointments' ?>">Quay lại</a>
            <?php if (!$isAdmin): ?><a class="workflow-link" href="<?= BASE_URL ?>?action=car-history&amp;id=<?= (int) $order['car_id'] ?>">Xem lịch sử xe</a><?php endif; ?>
        </p>
    </div>
</section>
