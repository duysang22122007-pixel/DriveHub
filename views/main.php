<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($title ?? 'DriveHub', ENT_QUOTES, 'UTF-8') ?></title>

    <meta name="description" content="DriveHub - Dịch vụ bảo dưỡng và chăm sóc ô tô chuyên nghiệp, minh bạch và an toàn.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">

    <?php if (($view ?? '') === 'services'): ?>
        <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/services.css">
    <?php endif; ?>

    <?php if (($view ?? '') === 'booking/create'): ?>
        <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/booking.css">
    <?php endif; ?>

    <?php if (in_array(($view ?? ''), ['auth/form', 'auth/customer_login', 'profile', 'appointments/customer', 'appointments/history', 'appointments/admin', 'cars/index', 'cars/history', 'repairs/index', 'repairs/create', 'repairs/show'], true)): ?>
        <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/workflow.css">
    <?php endif; ?>
</head>

<body>

    <a class="skip-link" href="#main-content">Bỏ qua điều hướng</a>

    <?php require PATH_VIEW . 'partials/header.php'; ?>

    <main id="main-content">
        <?php
        if (isset($view)) {
            require_once PATH_VIEW . $view . '.php';
        }
        ?>
    </main>

    <?php require PATH_VIEW . 'partials/footer.php'; ?>

    <script src="<?= BASE_URL ?>assets/js/main.js" defer></script>

    <?php if (($view ?? '') === 'booking/create'): ?>
        <script src="<?= BASE_URL ?>assets/js/booking.js" defer></script>
    <?php endif; ?>
</body>

</html>
