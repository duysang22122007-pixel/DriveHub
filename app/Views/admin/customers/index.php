<?php

declare(strict_types=1);

$pageTitle = 'Khách hàng';
$activeNav = 'customers';
$pageEyebrow = 'Vận hành';
$pageDescription = 'Danh sách tài khoản khách hàng và phương tiện đã đăng ký.';
$pageStyles = '.data-table{min-width:820px}.customer-detail-link{border:0;padding:0;background:transparent;color:var(--navy);font:inherit;font-weight:800;cursor:pointer;text-align:left}.customer-detail-link:hover{text-decoration:underline;text-underline-offset:3px}.customer-modal{position:fixed;inset:0;z-index:100;display:none;align-items:center;justify-content:center;padding:24px;background:rgba(8,31,51,.48);backdrop-filter:blur(3px)}.customer-modal.open{display:flex}.customer-modal-dialog{width:min(880px,100%);max-height:calc(100vh - 48px);overflow:hidden;border-radius:14px;background:#fff;box-shadow:0 24px 70px rgba(9,39,64,.28)}.customer-modal-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;padding:18px 22px;border-bottom:1px solid var(--line)}.customer-modal-head h2{margin:0;color:var(--navy);font-size:1.15rem}.customer-modal-head p{margin:5px 0 0;color:var(--muted);font-size:.84rem}.customer-modal-close{width:36px;height:36px;border:1px solid var(--line);border-radius:8px;background:#fff;color:var(--navy);cursor:pointer}.customer-modal-body{max-height:calc(100vh - 165px);overflow-y:auto;padding:20px 22px 24px}.customer-info-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;margin-bottom:18px}.customer-info-item{padding:11px 12px;border:1px solid var(--line);border-radius:9px;background:var(--surface-soft)}.customer-info-item span{display:block;margin-bottom:4px;color:var(--muted);font-size:.68rem;font-weight:800;text-transform:uppercase}.customer-info-item strong{display:block;color:var(--navy);font-size:.88rem;overflow-wrap:anywhere}.vehicle-section-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin:4px 0 12px}.vehicle-section-head h3{margin:0;color:var(--navy);font-size:.98rem}.vehicle-scroll{max-height:430px;overflow-y:auto;padding-right:4px}.vehicle-card-modal{display:grid;grid-template-columns:180px minmax(0,1fr);gap:16px;margin-bottom:12px;padding:12px;border:1px solid var(--line);border-radius:12px;background:#fff}.vehicle-card-modal:last-child{margin-bottom:0}.vehicle-modal-image{width:180px;height:126px;border-radius:9px;background:#edf3f8;object-fit:cover}.vehicle-modal-placeholder{display:grid;place-items:center;width:180px;height:126px;border-radius:9px;background:linear-gradient(135deg,#edf3f8,#dce8f2);color:#668096;font-size:.78rem;font-weight:800}.vehicle-card-modal h4{margin:2px 0 7px;color:var(--navy);font-size:.95rem}.vehicle-card-modal p{margin:0 0 10px;color:var(--muted);font-size:.82rem;line-height:1.65}.vehicle-card-modal .actions{gap:7px}.vehicle-empty{padding:28px 20px;border:1px dashed #bdcbd9;border-radius:11px;background:#f9fbfd;text-align:center;color:var(--muted)}.vehicle-empty strong{display:block;margin-bottom:6px;color:var(--navy)}.vehicle-form-modal{display:none;margin:0 0 14px;padding:14px;border:1px solid var(--line);border-radius:11px;background:var(--surface-soft)}.vehicle-form-modal.open{display:block}.vehicle-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:11px}.vehicle-form-modal label{display:grid;gap:6px;color:#29445b;font-size:.78rem;font-weight:800}.vehicle-form-modal input,.vehicle-form-modal select{width:100%;min-height:40px;padding:8px 10px;border:1px solid var(--line-strong);border-radius:8px;background:#fff}.vehicle-form-modal .field-error{color:var(--red);font-size:.78rem}.vehicle-form-modal .actions{margin-top:12px}@media(max-width:821px){.customer-modal{padding:12px}.customer-modal-dialog{max-height:calc(100vh - 24px)}.customer-modal-body{max-height:calc(100vh - 140px);padding:16px}.customer-info-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.vehicle-card-modal{grid-template-columns:120px minmax(0,1fr)}.vehicle-modal-image,.vehicle-modal-placeholder{width:120px;height:100px}.vehicle-form-grid{grid-template-columns:1fr}}@media(max-width:520px){.customer-info-grid{grid-template-columns:1fr}.vehicle-card-modal{grid-template-columns:1fr}.vehicle-modal-image,.vehicle-modal-placeholder{width:100%;height:150px}}@media(min-width:821px){.filter-grid{grid-template-columns:minmax(220px,2fr) minmax(170px,1fr) auto}}';
$exportUrl = url('admin/customers') . '?' . http_build_query(['q' => $q, 'status' => $status, 'export' => 'csv']);
$pageActions = '<a class="button secondary" href="' . e($exportUrl) . '">Xuất CSV</a>';

ob_start();
?>
<?php if (is_string($success) && $success !== ''): ?><p class="notice" role="status"><?= e($success) ?></p><?php endif; ?>
<?php if (is_string($error) && $error !== ''): ?><p class="notice error" role="alert"><?= e($error) ?></p><?php endif; ?>

<section class="admin-list customer-list" aria-label="Danh sách khách hàng">
<form method="get" action="<?= e(url('admin/customers')) ?>" class="filter-bar">
    <div class="filter-grid">
        <div class="filter-field">
            <label for="customer-q">Tìm kiếm</label>
            <input id="customer-q" type="search" name="q" value="<?= e($q) ?>" placeholder="Tìm theo tên, email, số điện thoại…" autocomplete="off">
        </div>
        <div class="filter-field">
            <label for="customer-status">Trạng thái</label>
            <select id="customer-status" name="status">
                <option value="" <?= $status === '' ? 'selected' : '' ?>>Tất cả trạng thái</option>
                <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Đang hoạt động</option>
                <option value="locked" <?= $status === 'locked' ? 'selected' : '' ?>>Đã khóa</option>
                <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Không hoạt động</option>
            </select>
        </div>
        <div class="filter-actions">
            <button type="submit" class="btn-search">Tìm kiếm</button>
            <?php if ($q !== '' || $status !== ''): ?><a href="<?= e(url('admin/customers')) ?>" class="btn-reset">Xóa bộ lọc</a><?php endif; ?>
        </div>
    </div>
</form>

<p class="table-meta">
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
        <div class="empty-state"><?= ($q !== '' || $status !== '') ? 'Không tìm thấy khách hàng phù hợp.' : 'Chưa có tài khoản khách hàng.' ?></div>
    <?php else: ?>
        <table class="data-table">
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
                <?php
                    $st = $customer['status'];
                    $stClass = $st === 'locked' ? 'locked' : ($st === 'inactive' ? 'inactive' : 'active');
                    $stLabel = $st === 'active' ? 'Đang hoạt động' : ($st === 'locked' ? 'Đã khóa' : 'Không hoạt động');
                ?>
                <tr>
                    <td><button type="button" class="customer-detail-link" data-customer-id="<?= e((int) $customer['id']) ?>"><?= e($customer['full_name']) ?></button></td>
                    <td><?= e($customer['email']) ?></td>
                    <td><?= e($customer['phone'] ?? '—') ?></td>
                    <td><?= e((int) $customer['vehicle_count']) ?></td>
                    <td><span class="status <?= e($stClass) ?>"><?= e($stLabel) ?></span></td>
                    <td><?= e(date('d/m/Y', strtotime((string) $customer['created_at']))) ?></td>
                    <td>
                        <div class="actions">
                            <button type="button" class="button secondary small" data-customer-id="<?= e((int) $customer['id']) ?>" data-open-customer>Chi tiết</button>
                            <?php if ($customer['status'] === 'active'): ?>
                                <form method="post" action="<?= e(url('admin/customers/' . (int) $customer['id'] . '/status')) ?>" onsubmit="return confirm('Khóa tài khoản khách hàng này?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="status" value="locked">
                                    <button class="button danger small" type="submit">Khóa</button>
                                </form>
                            <?php else: ?>
                                <form method="post" action="<?= e(url('admin/customers/' . (int) $customer['id'] . '/status')) ?>" onsubmit="return confirm('Mở khóa tài khoản khách hàng này?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="status" value="active">
                                    <button class="button success-btn small" type="submit">Mở khóa</button>
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
</section>

<div class="customer-modal" id="customer-detail-modal" role="dialog" aria-modal="true" aria-labelledby="customer-modal-title" data-detail-base="<?= e(url('admin/customers')) ?>">
    <?= csrf_field() ?>
    <div class="customer-modal-dialog">
        <div class="customer-modal-head">
            <div>
                <h2 id="customer-modal-title">Chi tiết khách hàng</h2>
                <p id="customer-modal-subtitle">Thông tin tài khoản và phương tiện đã đăng ký.</p>
            </div>
            <button type="button" class="customer-modal-close" aria-label="Đóng">×</button>
        </div>
        <div class="customer-modal-body">
            <div id="customer-modal-loading" class="vehicle-empty">Đang tải thông tin khách hàng…</div>
            <div id="customer-modal-content" hidden></div>
        </div>
    </div>
</div>

<script>
(function () {
    const modal = document.getElementById('customer-detail-modal');
    const loading = document.getElementById('customer-modal-loading');
    const content = document.getElementById('customer-modal-content');
    const detailBase = modal.dataset.detailBase;
    const csrfInput = document.querySelector('input[name="_token"]');
    const csrfToken = csrfInput ? csrfInput.value : '';
    let activeCustomerId = 0;
    let activeBrands = [];

    const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    })[char]);

    const statusLabel = (status) => status === 'active'
        ? 'Đang hoạt động'
        : (status === 'locked' ? 'Đã khóa' : 'Không hoạt động');

    async function readApiResponse(response) {
        const contentType = response.headers.get('content-type') || '';
        const body = await response.text();
        if (!response.ok) {
            if (contentType.includes('application/json')) {
                try {
                    const errorData = JSON.parse(body);
                    const validationError = errorData.errors ? Object.values(errorData.errors)[0] : '';
                    throw new Error(errorData.error || validationError || 'Yêu cầu thất bại (HTTP ' + response.status + ').');
                } catch (error) {
                    if (error instanceof SyntaxError) {
                        throw new Error('Máy chủ trả về JSON lỗi (HTTP ' + response.status + ').');
                    }
                    throw error;
                }
            }
            throw new Error('Yêu cầu thất bại (HTTP ' + response.status + '); máy chủ không trả JSON.');
        }
        if (!contentType.includes('application/json')) {
            throw new Error('Máy chủ không trả JSON (HTTP ' + response.status + '). Hãy kiểm tra phiên đăng nhập hoặc lỗi máy chủ.');
        }
        try {
            return JSON.parse(body);
        } catch (error) {
            throw new Error('Phản hồi API không phải JSON hợp lệ.');
        }
    }

    function closeModal() {
        modal.classList.remove('open');
        document.body.style.overflow = '';
        content.hidden = true;
    }

    function openModal(customerId) {
        activeCustomerId = Number(customerId);
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
        loading.hidden = false;
        loading.textContent = 'Đang tải thông tin khách hàng…';
        content.hidden = true;
        fetch(detailBase + '/' + activeCustomerId + '/detail', { headers: { 'Accept': 'application/json' } })
            .then(readApiResponse)
            .then((data) => {
                if (!data.customer || !Array.isArray(data.vehicles)) {
                    throw new Error('API thiếu thông tin khách hàng hoặc danh sách phương tiện.');
                }
                activeBrands = data.brands || [];
                renderDetail(data.customer, data.vehicles);
            })
            .catch((error) => {
                loading.hidden = false;
                loading.textContent = error.message;
            });
    }

    function renderDetail(customer, vehicles) {
        loading.hidden = true;
        content.hidden = false;
        document.getElementById('customer-modal-subtitle').textContent = 'Mã khách hàng: ' + customer.id;

        let vehicleHtml = '';
        if (!vehicles.length) {
            vehicleHtml = '<div class="vehicle-empty"><strong>Khách hàng chưa đăng ký phương tiện.</strong><div style="margin-top:12px"><button type="button" class="button" data-add-vehicle>+ Thêm phương tiện</button></div></div>';
        } else {
            vehicleHtml = vehicles.map((vehicle) => {
                const image = vehicle.image
                    ? '<img class="vehicle-modal-image" src="' + escapeHtml(vehicle.image) + '" alt="' + escapeHtml(vehicle.brand_name + ' ' + vehicle.model) + '">'
                    : '<div class="vehicle-modal-placeholder" aria-label="Chưa có hình ảnh xe">Chưa có hình ảnh</div>';
                return '<article class="vehicle-card-modal">' + image
                    + '<div><h4>' + escapeHtml(vehicle.brand_name) + ' ' + escapeHtml(vehicle.model) + '</h4>'
                    + '<p><strong>Biển số:</strong> ' + escapeHtml(vehicle.license_plate)
                    + '<br><strong>Năm:</strong> ' + escapeHtml(vehicle.manufacture_year || 'Chưa cập nhật')
                    + ' · <strong>Màu:</strong> ' + escapeHtml(vehicle.color || 'Chưa cập nhật')
                    + '<br><strong>Số km:</strong> ' + Number(vehicle.current_mileage || 0).toLocaleString('vi-VN') + ' km</p>'
                    + '<div class="actions">'
                    + '<a class="button secondary small" href="' + detailBase.replace('/admin/customers', '/admin/vehicles') + '/' + activeCustomerId + '/' + Number(vehicle.id) + '">Xem chi tiết xe</a>'
                    + '<button type="button" class="button secondary small" data-edit-vehicle="' + Number(vehicle.id) + '">Sửa xe</button>'
                    + '<button type="button" class="button danger small" data-delete-vehicle="' + Number(vehicle.id) + '">Xóa xe</button>'
                    + '</div></div></article>';
            }).join('');
        }

        content.innerHTML =
            '<div class="customer-info-grid">'
            + '<div class="customer-info-item"><span>Tên khách hàng</span><strong>' + escapeHtml(customer.full_name) + '</strong></div>'
            + '<div class="customer-info-item"><span>Mã khách hàng</span><strong>KH-' + String(customer.id).padStart(5, '0') + '</strong></div>'
            + '<div class="customer-info-item"><span>Email</span><strong>' + escapeHtml(customer.email) + '</strong></div>'
            + '<div class="customer-info-item"><span>Số điện thoại</span><strong>' + escapeHtml(customer.phone || 'Chưa cập nhật') + '</strong></div>'
            + '<div class="customer-info-item"><span>Trạng thái tài khoản</span><strong>' + escapeHtml(statusLabel(customer.status)) + '</strong></div>'
            + '<div class="customer-info-item"><span>Ngày đăng ký</span><strong>' + escapeHtml(customer.created_at ? new Date(customer.created_at).toLocaleDateString('vi-VN') : 'Chưa cập nhật') + '</strong></div>'
            + '</div>'
            + '<div class="vehicle-section-head"><h3>Phương tiện đã đăng ký (' + vehicles.length + ')</h3><button type="button" class="button small" data-add-vehicle>+ Thêm phương tiện</button></div>'
            + '<div class="vehicle-form-modal" id="vehicle-form-modal"></div>'
            + '<div class="vehicle-scroll">' + vehicleHtml + '</div>';

        bindVehicleActions(vehicles);
    }

    function brandOptions(selectedId) {
        return '<option value="">-- Chọn hãng xe --</option>' + activeBrands.map((brand) =>
            '<option value="' + Number(brand.id) + '"' + (Number(selectedId) === Number(brand.id) ? ' selected' : '') + '>' + escapeHtml(brand.name) + '</option>'
        ).join('');
    }

    function showVehicleForm(vehicle) {
        const form = document.getElementById('vehicle-form-modal');
        const editing = Boolean(vehicle);
        form.classList.add('open');
        form.innerHTML =
            '<form>' +
            '<input type="hidden" name="_token" value="' + escapeHtml(csrfToken) + '">' +
            '<input type="hidden" name="ajax" value="1">' +
            '<div class="vehicle-form-grid">' +
            '<label>Hãng xe<select name="brand_id" required>' + brandOptions(vehicle ? vehicle.brand_id : '') + '</select></label>' +
            '<label>Dòng xe<input name="model" maxlength="100" required value="' + escapeHtml(vehicle ? vehicle.model : '') + '"></label>' +
            '<label>Biển số<input name="license_plate" maxlength="20" required value="' + escapeHtml(vehicle ? vehicle.license_plate : '') + '"></label>' +
            '<label>Năm sản xuất<input name="manufacture_year" type="number" min="1901" max="' + (new Date().getFullYear() + 1) + '" value="' + escapeHtml(vehicle ? vehicle.manufacture_year : '') + '"></label>' +
            '<label>Màu xe<input name="color" maxlength="50" value="' + escapeHtml(vehicle ? vehicle.color : '') + '"></label>' +
            '<label>Số km hiện tại<input name="current_mileage" type="number" min="0" max="4294967295" required value="' + escapeHtml(vehicle ? vehicle.current_mileage : 0) + '"></label>' +
            '</div><div class="actions"><button type="submit" class="button">' + (editing ? 'Lưu thay đổi' : 'Thêm phương tiện') + '</button><button type="button" class="button secondary" data-cancel-vehicle>Hủy</button></div></form>';

        form.querySelector('[data-cancel-vehicle]').addEventListener('click', () => form.classList.remove('open'));
        form.querySelector('form').addEventListener('submit', (event) => {
            event.preventDefault();
            const submitForm = new FormData(event.currentTarget);
            const vehicleId = vehicle ? Number(vehicle.id) : 0;
            const vehicleBase = detailBase.replace('/admin/customers', '/admin/vehicles');
            const url = editing
                ? vehicleBase + '/' + activeCustomerId + '/' + vehicleId + '/update'
                : vehicleBase + '/' + activeCustomerId;
            fetch(url, { method: 'POST', body: submitForm, headers: { 'Accept': 'application/json' } })
                .then(readApiResponse)
                .then((data) => {
                    if (!data.success) throw new Error('Không thể lưu phương tiện.');
                    openModal(activeCustomerId);
                })
                .catch((error) => alert(error.message));
        });
    }

    function bindVehicleActions(vehicles) {
        content.querySelectorAll('[data-add-vehicle]').forEach((button) => button.addEventListener('click', () => showVehicleForm(null)));
        content.querySelectorAll('[data-edit-vehicle]').forEach((button) => {
            button.addEventListener('click', () => {
                const vehicle = vehicles.find((item) => Number(item.id) === Number(button.dataset.editVehicle));
                showVehicleForm(vehicle);
            });
        });
        content.querySelectorAll('[data-delete-vehicle]').forEach((button) => {
            button.addEventListener('click', () => {
                if (!confirm('Xóa phương tiện này? Xe đã có lịch hẹn sẽ không thể xóa.')) return;
                const vehicleBase = detailBase.replace('/admin/customers', '/admin/vehicles');
                const body = new URLSearchParams({ _token: csrfToken, ajax: '1' });
                fetch(vehicleBase + '/' + activeCustomerId + '/' + Number(button.dataset.deleteVehicle) + '/delete', {
                    method: 'POST', body, headers: { 'Accept': 'application/json' }
                }).then(readApiResponse)
                  .then((data) => {
                      if (!data.success) throw new Error(data.error || 'Không thể xóa phương tiện.');
                      openModal(activeCustomerId);
                  }).catch((error) => alert(error.message));
            });
        });
    }

    document.querySelectorAll('[data-customer-id][data-open-customer], .customer-detail-link[data-customer-id]').forEach((button) => {
        button.addEventListener('click', () => openModal(button.dataset.customerId));
    });
    modal.querySelector('.customer-modal-close').addEventListener('click', closeModal);
    modal.addEventListener('click', (event) => { if (event.target === modal) closeModal(); });
    document.addEventListener('keydown', (event) => { if (event.key === 'Escape' && modal.classList.contains('open')) closeModal(); });
})();
</script>
<?php
$content = ob_get_clean();
require BASE_PATH . '/app/Views/admin/_layout.php';
