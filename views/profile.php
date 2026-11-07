<?php
$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$roleLabel = $profile['role'] === 'admin' ? 'Quản trị viên' : 'Khách hàng';
$initial = mb_strtoupper(mb_substr((string) $profile['full_name'], 0, 1, 'UTF-8'), 'UTF-8');
?>
<section class="workflow-page profile-page">
    <div class="container workflow-narrow">
        <p class="eyebrow"><span></span>Tài khoản DriveHub</p>
        <h1>Trang cá nhân</h1>
        <p class="workflow-lead">Thông tin được lưu trong tài khoản DriveHub của bạn.</p>

        <section class="workflow-card profile-card" id="account-details" aria-label="Thông tin tài khoản">
            <div class="profile-card__heading">
                <span class="profile-card__avatar" aria-hidden="true"><?= $e($initial) ?></span>
                <div>
                    <h2><?= $e($profile['full_name']) ?></h2>
                    <span class="profile-card__role"><?= $e($roleLabel) ?></span>
                </div>
            </div>
            <dl class="profile-card__details">
                <div>
                    <dt>Email</dt>
                    <dd><?= $e($profile['email']) ?></dd>
                </div>
                <div>
                    <dt>Tên đăng nhập</dt>
                    <dd><?= $e($profile['username']) ?></dd>
                </div>
                <?php if (!empty($profile['phone'])): ?>
                    <div>
                        <dt>Số điện thoại</dt>
                        <dd><?= $e($profile['phone']) ?></dd>
                    </div>
                <?php endif; ?>
                <?php if (!empty($profile['address'])): ?>
                    <div>
                        <dt>Địa chỉ</dt>
                        <dd><?= $e($profile['address']) ?></dd>
                    </div>
                <?php endif; ?>
                <?php if (!empty($profile['position'])): ?>
                    <div>
                        <dt>Chức vụ</dt>
                        <dd><?= $e($profile['position']) ?></dd>
                    </div>
                <?php endif; ?>
            </dl>
        </section>
    </div>
</section>
