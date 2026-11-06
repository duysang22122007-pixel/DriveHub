<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= $title ?? 'Home' ?></title>

    <!-- Latest compiled and minified CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .page-title {
            font-size: clamp(1.5rem, 4vw, 2.25rem);
            overflow-wrap: anywhere;
        }

        .page-content,
        .page-content > * {
            min-width: 0;
        }
    </style>

    <!-- Latest compiled JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>

    <nav class="navbar navbar-expand-lg bg-light justify-content-center px-3 px-sm-4">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link text-uppercase" href="<?= BASE_URL ?>"><b>Home</b></a>
            </li>
        </ul>
    </nav>

    <main class="container py-3 py-md-4">
        <h1 class="page-title mb-3"><?= $title ?? 'Home' ?></h1>

        <div class="row page-content">
            <?php
            if (isset($view)) {
                require_once PATH_VIEW . $view . '.php';
            }
            ?>
        </div>
    </main>

</body>

</html>