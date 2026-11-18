<?php

declare(strict_types=1);

$pageTitle = 'Phiếu sửa chữa ' . $ticket['ticket_code'];
$activeNav = 'tickets';
$pageEyebrow = 'Vận hành';

$statusBadgeClass = [
    'received' => 'pending',
    'diagnosing' => 'confirmed',
    'in_progress' => 'checked-in',
    'waiting_parts' => 'pending',
    'quality_check' => 'confirmed',
    'completed' => 'completed',
    'cancelled' => 'cancelled',
];

$itemStatusClass = [
    'pending' => 'pending',
    'in_progress' => 'checked-in',
    'completed' => 'completed',
    'skipped' => 'cancelled',
];

$currentStatus = (string) $ticket['status'];
$statusLabel = $statuses[$currentStatus] ?? $currentStatus;
$receivedDate = date('d/m/Y H:i', strtotime((string) $ticket['received_at']));
$completedDate = !empty($ticket['completed_at']) ? date('d/m/Y H:i', strtotime((string) $ticket['completed_at'])) : '—';

$pageDescription = 'Phiếu ' . $ticket['ticket_code'] . ' · Tiếp nhận ngày ' . $receivedDate;
$pageActions = '<a class="button secondary" href="' . e(url('admin/tickets')) . '">← Danh sách phiếu</a>';

ob_start();
?>
<?php if (is_string($success) && $success !== ''): ?><p class="notice" role="status"><?= e($success) ?></p><?php endif; ?>
<?php if (is_string($error) && $error !== ''): ?><p class="notice error" role="alert"><?= e($error) ?></p><?php endif; ?>

<div class="detail-layout" style="display:grid;grid-template-columns:2fr 1fr;gap:24px;align-items:start;">
    <!-- CỘT CHÍNH (BÊN TRÁI) -->
    <div style="display:flex;flex-direction:column;gap:24px;">
        <!-- 1. THÔNG TIN TIẾP NHẬN & PHƯƠNG TIỆN -->
        <section class="panel">
            <div class="panel-head" style="display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <h2 class="panel-title">Thông tin xe & Khách hàng</h2>
                    <p class="panel-description">Lịch hẹn liên kết: <a href="<?= e(url('admin/appointments/' . (int)$ticket['appointment_id'])) ?>" style="color:#2563eb;text-decoration:none;"><strong><?= e($ticket['appointment_code']) ?></strong></a></p>
                </div>
                <span class="status <?= e($statusBadgeClass[$currentStatus] ?? 'pending') ?>"><?= e($statusLabel) ?></span>
            </div>
            <div class="panel-body">
                <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:16px;">
                    <div>
                        <small style="color:#667788;display:block;">Chủ xe</small>
                        <strong style="font-size:16px;"><?= e($ticket['customer_name']) ?></strong>
                        <div style="color:#475569;font-size:13px;"><?= e($ticket['customer_phone'] ?: 'Chưa có SĐT') ?></div>
                    </div>
                    <div>
                        <small style="color:#667788;display:block;">Phương tiện</small>
                        <strong style="font-size:16px;"><?= e($ticket['brand_name'] . ' ' . $ticket['model']) ?></strong>
                        <div style="margin-top:2px;">
                            <span style="display:inline-block;padding:2px 8px;background:#e2e8f0;border-radius:4px;font-family:monospace;font-weight:700;font-size:13px;"><?= e($ticket['license_plate']) ?></span>
                            <?php if (!empty($ticket['color'])): ?> · Màu: <?= e($ticket['color']) ?><?php endif; ?>
                        </div>
                    </div>
                    <div>
                        <small style="color:#667788;display:block;">Số km tiếp nhận</small>
                        <strong style="font-size:16px;color:#123b5d;"><?= number_format((int)$ticket['mileage'], 0, ',', '.') ?> km</strong>
                        <small style="display:block;color:#667788;">Odo xe: <?= number_format((int)$ticket['vehicle_current_mileage'], 0, ',', '.') ?> km</small>
                    </div>
                    <div>
                        <small style="color:#667788;display:block;">KTV / Cố vấn dịch vụ</small>
                        <strong><?= e($ticket['admin_name'] ?: 'Chưa phân công') ?></strong>
                        <div style="color:#475569;font-size:13px;">Bàn giao: <?= e($completedDate) ?></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 2. THÔNG TIN CHẨN ĐOÁN LỖI & KẾT QUẢ SỬA CHỮA -->
        <section class="panel">
            <div class="panel-head">
                <div>
                    <h2 class="panel-title">Chẩn đoán lỗi & Tình trạng kỹ thuật</h2>
                    <p class="panel-description">Ghi nhận triệu chứng, nguyên nhân hư hỏng và kết quả khắc phục.</p>
                </div>
            </div>
            <div class="panel-body">
                <form method="post" action="<?= e(url('admin/tickets/' . (int)$ticket['id'] . '/diagnosis')) ?>">
                    <?= csrf_field() ?>
                    <div style="display:grid;grid-template-columns:1fr 2fr;gap:16px;margin-bottom:14px;">
                        <div>
                            <label style="display:block;font-weight:600;margin-bottom:4px;font-size:13px;">Số km tiếp nhận (km)</label>
                            <input type="number" name="mileage" min="0" value="<?= (int)$ticket['mileage'] ?>" required style="width:100%;padding:8px 10px;border:1px solid #d1dbe5;border-radius:6px;font-size:14px;">
                        </div>
                        <div>
                            <label style="display:block;font-weight:600;margin-bottom:4px;font-size:13px;">Tình trạng ban đầu / Hiện tượng báo lỗi</label>
                            <input type="text" name="initial_condition" value="<?= e((string)$ticket['initial_condition']) ?>" placeholder="Ví dụ: Đạp phanh kêu rít, máy giật khi tăng tốc..." style="width:100%;padding:8px 10px;border:1px solid #d1dbe5;border-radius:6px;font-size:14px;">
                        </div>
                    </div>
                    <div style="margin-bottom:14px;">
                        <label style="display:block;font-weight:600;margin-bottom:4px;font-size:13px;">Kết quả chẩn đoán kỹ thuật / Bắt bệnh</label>
                        <textarea name="diagnosis" rows="2" placeholder="Ghi nhận lỗi sau khi kiểm tra bằng máy chẩn đoán hoặc trực quan..." style="width:100%;padding:8px 10px;border:1px solid #d1dbe5;border-radius:6px;font-size:14px;"><?= e((string)$ticket['diagnosis']) ?></textarea>
                    </div>
                    <div style="margin-bottom:16px;">
                        <label style="display:block;font-weight:600;margin-bottom:4px;font-size:13px;">Kết quả xử lý & Nghiệm thu xuất xưởng</label>
                        <textarea name="result" rows="2" placeholder="Nội dung đã khắc phục xong, kiểm tra chạy thử xe đạt chuẩn..." style="width:100%;padding:8px 10px;border:1px solid #d1dbe5;border-radius:6px;font-size:14px;"><?= e((string)$ticket['result']) ?></textarea>
                    </div>
                    <div style="text-align:right;">
                        <button type="submit" class="button secondary" style="padding:6px 14px;font-size:13px;">Lưu thông tin chẩn đoán</button>
                    </div>
                </form>
            </div>
        </section>

        <!-- 3. HẠNG MỤC SỬA CHỮA, THAY THẾ VẬT TƯ & TỔNG CHI PHÍ -->
        <section class="panel">
            <div class="panel-head" style="display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <h2 class="panel-title">Hạng mục sửa chữa & Phụ tùng thay thế</h2>
                    <p class="panel-description">Chi tiết các công việc thực hiện và vật tư sử dụng trong phiếu.</p>
                </div>
                <div style="text-align:right;">
                    <span style="font-size:13px;color:#667788;">Tổng chi phí:</span>
                    <strong style="font-size:18px;color:#067647;display:block;"><?= number_format($totalCost, 0, ',', '.') ?> ₫</strong>
                </div>
            </div>
            <div class="panel-body">
                <?php if (empty($items)): ?>
                    <p style="color:#667788;font-style:italic;margin-bottom:16px;">Chưa có hạng mục sửa chữa nào. Vui lòng thêm hạng mục bên dưới.</p>
                <?php else: ?>
                    <div style="overflow-x:auto;margin-bottom:20px;">
                        <table class="table" style="width:100%;border-collapse:collapse;font-size:13px;">
                            <thead>
                                <tr style="border-bottom:2px solid #e3eaf1;background:#f8fafc;text-align:left;">
                                    <th style="padding:10px 8px;">Tên hạng mục / Phụ tùng</th>
                                    <th style="padding:10px 8px;width:70px;text-align:center;">Số lượng</th>
                                    <th style="padding:10px 8px;text-align:right;width:120px;">Đơn giá</th>
                                    <th style="padding:10px 8px;text-align:right;width:130px;">Thành tiền</th>
                                    <th style="padding:10px 8px;text-align:center;width:110px;">Trạng thái</th>
                                    <th style="padding:10px 8px;text-align:right;width:70px;">Xóa</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <?php
                                    $iSt = $itemStatusClass[$item['status']] ?? 'pending';
                                    $iStLbl = $itemStatuses[$item['status']] ?? $item['status'];
                                    ?>
                                    <tr style="border-bottom:1px solid #e7edf3;">
                                        <td style="padding:10px 8px;">
                                            <strong><?= e($item['item_name']) ?></strong>
                                            <?php if (!empty($item['description'])): ?>
                                                <div style="color:#667788;font-size:12px;"><?= e($item['description']) ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($item['note'])): ?>
                                                <div style="color:#9a6700;font-size:11px;">Ghi chú: <?= e($item['note']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding:10px 8px;text-align:center;">
                                            <?= (int)$item['quantity'] ?>
                                        </td>
                                        <td style="padding:10px 8px;text-align:right;">
                                            <?= number_format((float)$item['unit_price'], 0, ',', '.') ?> ₫
                                        </td>
                                        <td style="padding:10px 8px;text-align:right;">
                                            <strong><?= number_format((float)$item['line_total'], 0, ',', '.') ?> ₫</strong>
                                        </td>
                                        <td style="padding:10px 8px;text-align:center;">
                                            <span class="status <?= e($iSt) ?>" style="font-size:11px;padding:2px 6px;"><?= e($iStLbl) ?></span>
                                        </td>
                                        <td style="padding:10px 8px;text-align:right;">
                                            <form method="post" action="<?= e(url('admin/tickets/' . (int)$ticket['id'] . '/items/' . (int)$item['id'] . '/delete')) ?>" onsubmit="return confirm('Bạn có chắc muốn xóa hạng mục này?');" style="display:inline;">
                                                <?= csrf_field() ?>
                                                <button type="submit" style="background:none;border:none;color:#b42318;cursor:pointer;font-size:12px;text-decoration:underline;">Xóa</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr style="background:#f8fafc;font-weight:700;">
                                    <td colspan="3" style="padding:10px 8px;text-align:right;">Tổng cộng chi phí:</td>
                                    <td style="padding:10px 8px;text-align:right;color:#067647;font-size:15px;"><?= number_format($totalCost, 0, ',', '.') ?> ₫</td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php endif; ?>

                <!-- FORM THÊM HẠNG MỤC MỚI -->
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px;">
                    <h3 style="margin-top:0;margin-bottom:12px;font-size:14px;color:#123b5d;">+ Thêm hạng mục sửa chữa / vật tư mới</h3>
                    <form method="post" action="<?= e(url('admin/tickets/' . (int)$ticket['id'] . '/items')) ?>">
                        <?= csrf_field() ?>
                        <div style="display:grid;grid-template-columns:2fr 1fr;gap:12px;margin-bottom:10px;">
                            <div>
                                <label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px;">Hoặc chọn nhanh từ gói dịch vụ</label>
                                <select id="quick_service" style="width:100%;padding:6px 10px;border:1px solid #cbd5e1;border-radius:4px;font-size:13px;">
                                    <option value="">-- Chọn dịch vụ có sẵn để điền nhanh --</option>
                                    <?php foreach ($services as $s): ?>
                                        <option value="<?= (int)$s['id'] ?>" data-name="<?= e($s['name']) ?>" data-price="<?= (float)$s['price'] ?>">
                                            <?= e($s['name']) ?> (<?= number_format((float)$s['price'], 0, ',', '.') ?> ₫)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <input type="hidden" name="service_id" id="item_service_id" value="">
                                <label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px;">Tên hạng mục / Phụ tùng <span style="color:#b42318;">*</span></label>
                                <input type="text" name="item_name" id="item_name" required placeholder="Tên công việc hoặc phụ tùng..." style="width:100%;padding:6px 10px;border:1px solid #cbd5e1;border-radius:4px;font-size:13px;">
                            </div>
                        </div>

                        <div style="display:grid;grid-template-columns:1fr 1fr 2fr;gap:12px;margin-bottom:10px;">
                            <div>
                                <label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px;">Số lượng <span style="color:#b42318;">*</span></label>
                                <input type="number" name="quantity" id="item_quantity" min="1" value="1" required style="width:100%;padding:6px 10px;border:1px solid #cbd5e1;border-radius:4px;font-size:13px;">
                            </div>
                            <div>
                                <label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px;">Đơn giá (₫) <span style="color:#b42318;">*</span></label>
                                <input type="number" name="unit_price" id="item_price" min="0" step="1000" value="0" required style="width:100%;padding:6px 10px;border:1px solid #cbd5e1;border-radius:4px;font-size:13px;">
                            </div>
                            <div>
                                <label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px;">Mô tả chi tiết / Mã phụ tùng</label>
                                <input type="text" name="description" placeholder="Hãng sản xuất, vị trí thay thế..." style="width:100%;padding:6px 10px;border:1px solid #cbd5e1;border-radius:4px;font-size:13px;">
                            </div>
                        </div>

                        <div style="display:flex;justify-content:flex-end;">
                            <button type="submit" class="button" style="padding:6px 14px;font-size:13px;">+ Thêm vào phiếu</button>
                        </div>
                    </form>
                    <script>
                        (function () {
                            var quickSelect = document.getElementById('quick_service');
                            var nameInput = document.getElementById('item_name');
                            var priceInput = document.getElementById('item_price');
                            var serviceIdInput = document.getElementById('item_service_id');
                            if (quickSelect && nameInput && priceInput) {
                                quickSelect.addEventListener('change', function () {
                                    var opt = quickSelect.options[quickSelect.selectedIndex];
                                    if (opt && opt.value !== '') {
                                        nameInput.value = opt.getAttribute('data-name') || '';
                                        priceInput.value = opt.getAttribute('data-price') || '0';
                                        serviceIdInput.value = opt.value;
                                    }
                                });
                            }
                        }());
                    </script>
                </div>
            </div>
        </section>
    </div>

    <!-- CỘT BÊN PHẢI: ĐỔI TRẠNG THÁI & LỊCH SỬ TIẾN ĐỘ -->
    <div style="display:flex;flex-direction:column;gap:24px;">
        <!-- 4. FORM CHUYỂN TRẠNG THÁI TIẾN ĐỘ SỬA CHỮA -->
        <section class="panel">
            <div class="panel-head">
                <div>
                    <h2 class="panel-title">Cập nhật tình trạng</h2>
                    <p class="panel-description">Điều chỉnh quy trình xử lý thực tế tại gara.</p>
                </div>
            </div>
            <div class="panel-body">
                <form method="post" action="<?= e(url('admin/tickets/' . (int)$ticket['id'] . '/status')) ?>" id="status-update-form">
                    <?= csrf_field() ?>
                    <div style="margin-bottom:14px;">
                        <label style="display:block;font-weight:600;font-size:13px;margin-bottom:4px;">Trạng thái sửa chữa <span style="color:#b42318;">*</span></label>
                        <select name="status" id="ticket_status_select" required style="width:100%;padding:8px 10px;border:1px solid #d1dbe5;border-radius:6px;font-size:14px;">
                            <?php foreach ($statuses as $val => $lbl): ?>
                                <option value="<?= e($val) ?>" <?= $currentStatus === $val ? 'selected' : '' ?>><?= e($lbl) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Ô cập nhật km khi hoàn thành (chỉ hiển thị khi chọn completed) -->
                    <div id="completed_mileage_box" style="margin-bottom:14px;<?= $currentStatus === 'completed' ? '' : 'display:none;' ?>">
                        <label style="display:block;font-weight:600;font-size:13px;margin-bottom:4px;color:#067647;">Cập nhật số km khi hoàn thành (bàn giao xe)</label>
                        <div style="display:flex;align-items:center;gap:6px;">
                            <input type="number" name="completed_mileage" id="completed_mileage_input" min="<?= (int)$ticket['mileage'] ?>" value="<?= (int)$ticket['mileage'] ?>" style="flex:1;padding:8px 10px;border:1px solid #10b981;border-radius:6px;font-size:14px;">
                            <span style="font-weight:600;color:#667788;font-size:13px;">km</span>
                        </div>
                        <small style="color:#667788;display:block;margin-top:4px;">Số km thực tế khi bàn giao xe (sau khi chạy thử).</small>
                    </div>

                    <div style="margin-bottom:16px;">
                        <label style="display:block;font-weight:600;font-size:13px;margin-bottom:4px;">Ghi chú tiến độ / Lý do</label>
                        <textarea name="description" rows="2" placeholder="Ví dụ: Đã thay xong dầu máy và lọc gió, chờ khách tới nhận..." style="width:100%;padding:8px 10px;border:1px solid #d1dbe5;border-radius:6px;font-size:13px;"></textarea>
                    </div>

                    <button type="submit" class="button" style="width:100%;">Lưu thay đổi trạng thái</button>
                </form>
                <script>
                    (function () {
                        var select = document.getElementById('ticket_status_select');
                        var mileageBox = document.getElementById('completed_mileage_box');
                        if (select && mileageBox) {
                            select.addEventListener('change', function () {
                                if (select.value === 'completed') {
                                    mileageBox.style.display = 'block';
                                } else {
                                    mileageBox.style.display = 'none';
                                }
                            });
                        }
                    }());
                </script>
            </div>
        </section>

        <!-- 5. LỊCH SỬ TIẾN ĐỘ BẢO DƯỠNG (TIMELINE) -->
        <section class="panel">
            <div class="panel-head">
                <div>
                    <h2 class="panel-title">Nhật ký tiến độ</h2>
                    <p class="panel-description">Lịch sử thay đổi trạng thái bảo dưỡng.</p>
                </div>
            </div>
            <div class="panel-body" style="padding:12px 16px;">
                <?php if (empty($histories)): ?>
                    <p style="color:#667788;font-size:13px;">Chưa có lịch sử cập nhật.</p>
                <?php else: ?>
                    <div style="position:relative;padding-left:16px;border-left:2px solid #e2e8f0;display:flex;flex-direction:column;gap:16px;">
                        <?php foreach ($histories as $h): ?>
                            <div style="position:relative;">
                                <div style="position:absolute;left:-22px;top:4px;width:10px;height:10px;border-radius:50%;background:#2563eb;border:2px solid #fff;"></div>
                                <div style="font-size:12px;color:#667788;">
                                    <?= date('d/m/Y H:i', strtotime((string)$h['created_at'])) ?> · <strong><?= e($h['admin_name'] ?: 'Quản trị viên') ?></strong>
                                </div>
                                <div style="font-weight:600;font-size:13px;margin:2px 0;color:#1e293b;">
                                    <?= e($statuses[$h['new_status']] ?? $h['new_status']) ?>
                                </div>
                                <?php if (!empty($h['description'])): ?>
                                    <div style="font-size:12px;color:#475569;background:#f8fafc;padding:6px 10px;border-radius:4px;margin-top:4px;">
                                        <?= nl2br(e($h['description'])) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>

<?php
$content = ob_get_clean();
require BASE_PATH . '/app/Views/admin/_layout.php';
