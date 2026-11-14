<?php

declare(strict_types=1);

$pageTitle = isset($pageTitle) ? (string) $pageTitle : 'Quản trị';
$activeNav = isset($activeNav) ? (string) $activeNav : '';
$pageDescription = isset($pageDescription) ? (string) $pageDescription : '';
$pageActions = isset($pageActions) ? (string) $pageActions : '';
$pageEyebrow = isset($pageEyebrow) ? (string) $pageEyebrow : 'DriveHub Admin';
$content = isset($content) ? (string) $content : '';
$pageStyles = isset($pageStyles) ? (string) $pageStyles : '';
$sessionUserName = (string) ($_SESSION['user_name'] ?? '');
$adminName = $sessionUserName !== '' ? $sessionUserName : 'Quản trị viên';
$adminInitial = function_exists('mb_substr') ? mb_substr($adminName, 0, 1, 'UTF-8') : substr($adminName, 0, 1);

$adminIcon = static function (string $name): string {
    $icons = [
        'dashboard' => '<path d="M4 13h6V4H4v9Zm0 7h6v-5H4v5Zm10 0h6v-9h-6v9Zm0-16v5h6V4h-6Z"/>',
        'calendar' => '<path d="M7 3v2M17 3v2M4 9h16M5 5h14a1 1 0 0 1 1 1v14H4V6a1 1 0 0 1 1-1Zm3 8h3v3H8v-3Z"/>',
        'customers' => '<path d="M16 19v-1a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v1M10 10a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm7 9v-1a4 4 0 0 0-3-3.87M16 4.13a3 3 0 0 1 0 5.74"/>',
        'vehicle' => '<path d="M5 16h14M6 16l1-5 2-3h6l2 3 1 5M7 16v2M17 16v2M9 11h6M8 13h.01M16 13h.01"/>',
        'brand' => '<path d="M12 3 4 7v6c0 4 3.4 7 8 8 4.6-1 8-4 8-8V7l-8-4Zm0 5v6M9 11h6"/>',
        'model' => '<path d="M4 7h16v10H4V7Zm3 3h4M7 13h6M15 10h2M15 13h2"/>',
        'import' => '<path d="M12 3v11m0 0 4-4m-4 4-4-4M4 17v3h16v-3"/>',
        'review' => '<path d="M9 11l2 2 4-5M5 4h14v16H5V4Z"/>',
        'chevron' => '<path d="m9 6 6 6-6 6"/>',
        'menu' => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'arrow' => '<path d="M5 12h14m-5-5 5 5-5 5"/>',
        'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'download' => '<path d="M12 3v12m0 0 4-4m-4 4-4-4M4 19h16"/>',
        'back' => '<path d="M19 12H5m5-5-5 5 5 5"/>',
    ];

    $path = $icons[$name] ?? $icons['dashboard'];

    return '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">' . $path . '</svg>';
};

$adminNavGroups = [
    'Tổng quan' => [
        ['key' => 'dashboard', 'label' => 'Tổng quan', 'href' => url('admin'), 'icon' => 'dashboard'],
    ],
    'Vận hành' => [
        ['key' => 'appointments', 'label' => 'Lịch hẹn', 'href' => url('admin/appointments'), 'icon' => 'calendar'],
        ['key' => 'customers', 'label' => 'Khách hàng', 'href' => url('admin/customers'), 'icon' => 'customers'],
        ['key' => 'vehicles', 'label' => 'Phương tiện', 'href' => url('admin/vehicles'), 'icon' => 'vehicle'],
    ],
    'Danh mục xe' => [
        ['key' => 'brands', 'label' => 'Hãng xe', 'href' => url('admin/brands'), 'icon' => 'brand'],
        ['key' => 'models', 'label' => 'Dòng xe', 'href' => url('admin/models'), 'icon' => 'model'],
        ['key' => 'vehicle-import', 'label' => 'Nhập dữ liệu xe', 'href' => url('admin/vehicle-import'), 'icon' => 'import'],
        ['key' => 'vehicle-review', 'label' => 'Duyệt dòng xe', 'href' => url('admin/vehicle-import/review'), 'icon' => 'review'],
    ],
];
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title><?= e($pageTitle) ?> | DriveHub Admin</title>
    <style>
        :root{
            --navy:#123b5d;--navy-deep:#0b2740;--navy-soft:#1b4d73;--blue:#2563eb;--blue-dark:#1d4ed8;
            --ink:#172b3b;--muted:#667788;--muted-light:#8794a3;--line:#e3eaf1;--line-strong:#d1dbe5;
            --surface:#ffffff;--canvas:#f4f7fb;--surface-soft:#f8fafc;--green:#067647;--green-soft:#ecfdf3;
            --amber:#9a6700;--amber-soft:#fff7e6;--red:#b42318;--red-soft:#fff0ef;--shadow:none;
            font-family:Inter,"Segoe UI",Arial,sans-serif;color:var(--ink);background:var(--canvas);
        }
        *{box-sizing:border-box}
        html{min-width:320px;scroll-behavior:smooth}
        body{margin:0;min-height:100vh;background:var(--canvas)}
        body,button,input,select,textarea{font-family:inherit}
        a{color:inherit}
        button,input,select,textarea{font-size:inherit}
        img{max-width:100%}
        .skip-link{position:fixed;left:16px;top:-64px;z-index:100;padding:10px 14px;border-radius:8px;background:var(--navy);color:#fff;font-weight:700;text-decoration:none;transition:top .15s ease}
        .skip-link:focus{top:14px}
        .admin-shell{min-height:100vh}
        .admin-sidebar{position:fixed;inset:0 auto 0 0;z-index:50;display:flex;flex-direction:column;width:264px;padding:18px 14px;overflow-y:auto;background:var(--navy-deep);color:#fff;transition:width .2s ease,transform .2s ease}
        .sidebar-brand{display:flex;align-items:center;gap:12px;min-height:48px;padding:4px 8px 18px;border-bottom:1px solid rgba(255,255,255,.1)}
        .brand-link{display:flex;align-items:center;gap:11px;min-width:0;color:#fff;text-decoration:none}
        .brand-mark{display:grid;place-items:center;flex:0 0 38px;width:38px;height:38px;border-radius:8px;background:var(--blue)}
        .brand-mark svg{width:23px;height:23px;fill:none;stroke:#fff;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
        .brand-copy{display:grid;gap:2px;min-width:0}
        .brand-copy strong{font-size:1.06rem;letter-spacing:-.02em}
        .brand-copy small{color:#a8bed0;font-size:.72rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase}
        .sidebar-nav{display:flex;flex-direction:column;gap:20px;padding:20px 0 12px}
        .nav-group{display:grid;gap:5px}
        .nav-label{margin:0 0 4px;padding:0 12px;color:#8fa8bc;font-size:.68rem;font-weight:800;letter-spacing:.12em;text-transform:uppercase}
        .nav-link{position:relative;display:flex;align-items:center;gap:11px;min-height:42px;padding:0 12px;border-radius:9px;color:#c9d8e5;text-decoration:none;font-size:.9rem;font-weight:600;transition:background .15s ease,color .15s ease}
        .nav-link svg{flex:0 0 19px;width:19px;height:19px;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
        .nav-link:hover,.nav-link:focus-visible{background:rgba(255,255,255,.07);color:#fff;text-decoration:none}
        .nav-link.active{background:rgba(59,130,246,.18);color:#fff;box-shadow:inset 3px 0 0 #60a5fa}
        .nav-link.active svg{color:#93c5fd}
        .sidebar-foot{margin-top:auto;padding:16px 8px 4px;border-top:1px solid rgba(255,255,255,.1)}
        .sidebar-foot p{margin:0 0 3px;color:#8fa8bc;font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase}
        .sidebar-foot strong{display:block;overflow:hidden;color:#e7f0f7;font-size:.88rem;text-overflow:ellipsis;white-space:nowrap}
        .admin-main{display:flex;flex-direction:column;min-height:100vh;margin-left:264px;transition:margin-left .2s ease}
        .admin-topbar{position:sticky;top:0;z-index:30;display:flex;align-items:center;justify-content:space-between;gap:18px;min-height:72px;padding:0 28px;border-bottom:1px solid var(--line);background:rgba(255,255,255,.94);backdrop-filter:blur(10px)}
        .topbar-left,.topbar-right{display:flex;align-items:center;gap:14px;min-width:0}
        .icon-button{display:inline-grid;place-items:center;width:38px;height:38px;padding:0;border:1px solid var(--line);border-radius:9px;background:#fff;color:var(--navy);cursor:pointer;transition:border-color .15s ease,background .15s ease}
        .icon-button:hover,.icon-button:focus-visible{border-color:#b7c9d9;background:var(--surface-soft)}
        .icon-button svg{width:19px;height:19px;fill:none;stroke:currentColor;stroke-width:1.9;stroke-linecap:round;stroke-linejoin:round}
        .breadcrumb{display:flex;align-items:center;gap:8px;min-width:0;color:var(--muted);font-size:.82rem;font-weight:600;white-space:nowrap}
        .breadcrumb a{color:var(--navy);text-decoration:none}
        .breadcrumb a:hover{text-decoration:underline;text-underline-offset:3px}
        .breadcrumb span:last-child{overflow:hidden;text-overflow:ellipsis}
        .account-menu{position:relative}
        .account-menu summary{display:flex;align-items:center;gap:10px;padding:6px 8px 6px 6px;border:1px solid transparent;border-radius:10px;cursor:pointer;list-style:none;transition:background .15s ease,border-color .15s ease}
        .account-menu summary::-webkit-details-marker{display:none}
        .account-menu summary:hover,.account-menu[open] summary{background:var(--surface-soft);border-color:var(--line)}
        .avatar{display:grid;place-items:center;width:36px;height:36px;border-radius:50%;background:var(--navy);color:#fff;font-size:.9rem;font-weight:800;text-transform:uppercase}
        .account-copy{display:grid;gap:1px;min-width:0;text-align:left}
        .account-copy strong{max-width:150px;overflow:hidden;color:var(--navy);font-size:.84rem;text-overflow:ellipsis;white-space:nowrap}
        .account-copy small{color:var(--muted);font-size:.72rem;font-weight:600}
        .account-menu summary>svg{width:15px;height:15px;fill:none;stroke:var(--muted);stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
        .account-popover{position:absolute;right:0;top:calc(100% + 8px);width:230px;padding:8px;border:1px solid var(--line);border-radius:11px;background:#fff;box-shadow:0 18px 40px rgba(18,59,93,.16)}
        .account-popover .account-meta{padding:9px 10px 11px;border-bottom:1px solid var(--line);margin-bottom:6px}
        .account-popover .account-meta strong{display:block;color:var(--navy);font-size:.88rem}
        .account-popover .account-meta small{color:var(--muted);font-size:.76rem}
        .account-popover a,.account-popover button{display:flex;align-items:center;width:100%;min-height:36px;padding:0 10px;border:0;border-radius:7px;background:transparent;color:var(--ink);font:inherit;font-size:.86rem;font-weight:600;text-align:left;text-decoration:none;cursor:pointer}
        .account-popover a:hover,.account-popover button:hover,.account-popover a:focus-visible,.account-popover button:focus-visible{background:var(--surface-soft);outline:none}
        .account-popover button{color:var(--red)}
        .account-popover form{margin:0}
        .admin-content{width:100%;max-width:1500px;margin:0 auto;padding:30px 30px 60px}
        .page-head{display:flex;align-items:flex-start;justify-content:space-between;gap:22px;margin-bottom:24px}
        .page-head-copy{min-width:0}
        .page-eyebrow{margin:0 0 7px;color:var(--blue);font-size:.7rem;font-weight:800;letter-spacing:.12em;text-transform:uppercase}
        .page-title{margin:0;color:var(--navy);font-size:clamp(1.45rem,2vw,1.85rem);letter-spacing:-.035em;line-height:1.2}
        .page-description{max-width:820px;margin:8px 0 0;color:var(--muted);font-size:.92rem;line-height:1.6}
        .page-actions{display:flex;flex-wrap:wrap;align-items:center;justify-content:flex-end;gap:9px;flex:0 0 auto}
        .button{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:38px;padding:0 14px;border:1px solid transparent;border-radius:8px;background:var(--blue);color:#fff;font:inherit;font-size:.86rem;font-weight:700;line-height:1;text-decoration:none;cursor:pointer;transition:background .15s ease,border-color .15s ease,box-shadow .15s ease,transform .15s ease}
        .button:hover{background:var(--blue-dark);text-decoration:none}
        .button:disabled,.btn-search:disabled{opacity:.55;cursor:not-allowed}
        .button:active{transform:translateY(1px)}
        .button:focus-visible,.btn-search:focus-visible,.btn-reset:focus-visible,.icon-button:focus-visible,.nav-link:focus-visible,.account-menu summary:focus-visible,.metric-link:focus-visible,.quick-card:focus-visible,.pagination a:focus-visible{outline:3px solid rgba(37,99,235,.25);outline-offset:2px}
        .button.secondary{background:#eef3f8;border-color:#e0e8f0;color:var(--navy)}
        .button.secondary:hover{background:#e4ecf4;border-color:#cfdae5}
        .button.danger,.button.reject{background:var(--red-soft);border-color:#ffd8d5;color:var(--red)}
        .button.danger:hover,.button.reject:hover{background:#ffe3e0;border-color:#ffc9c5}
        .button.success-btn{background:var(--green-soft);border-color:#c9f0d9;color:var(--green)}
        .button.success-btn:hover{background:#dcfae8}
        .button.ghost{background:transparent;border-color:var(--line-strong);color:var(--navy)}
        .button.ghost:hover{background:var(--surface-soft)}
        .button.small{min-height:32px;padding:0 10px;font-size:.8rem}
        .button svg{width:16px;height:16px;fill:none;stroke:currentColor;stroke-width:1.9;stroke-linecap:round;stroke-linejoin:round}
        .notice{display:flex;align-items:flex-start;gap:9px;padding:11px 14px;margin:0 0 14px;border:1px solid #c9f0d9;border-radius:9px;background:var(--green-soft);color:var(--green);font-size:.88rem;font-weight:600;line-height:1.45}
        .notice.error{border-color:#ffd8d5;background:var(--red-soft);color:var(--red)}
        .panel{margin-top:20px;border:1px solid var(--line);border-radius:8px;background:var(--surface)}
        .panel[id]{scroll-margin-top:90px}
        .panel-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;padding:18px 20px 0}
        .panel-title{margin:0;color:var(--navy);font-size:1rem;letter-spacing:-.01em}
        .panel-description{margin:5px 0 0;color:var(--muted);font-size:.84rem;line-height:1.5}
        .panel-body{padding:18px 20px 20px}
        .panel-body>.filter-bar:first-child,.panel-body>.table-wrap:first-child{margin-top:0}
        .filter-bar{display:flex;flex-wrap:wrap;align-items:center;gap:10px;margin:16px 0 18px;padding:13px 14px;border:1px solid var(--line);border-radius:9px;background:var(--surface-soft)}
        .filter-grid{display:grid;grid-template-columns:minmax(220px,2fr) minmax(160px,1fr) minmax(150px,1fr) auto;align-items:end;gap:11px}
        .filter-field{display:grid;gap:5px;min-width:0}
        .filter-field label{color:#526678;font-size:.72rem;font-weight:800;letter-spacing:.04em;text-transform:uppercase}
        .filter-bar input,.filter-bar select,.filter-field input,.filter-field select,.field input,.field select,.field textarea{width:100%;min-height:39px;padding:8px 11px;border:1px solid var(--line-strong);border-radius:8px;background:#fff;color:var(--ink);font:inherit;font-size:.88rem}
        .filter-bar input[type=search]{flex:1;min-width:220px}
        .filter-bar input:focus,.filter-bar select:focus,.filter-field input:focus,.filter-field select:focus,.field input:focus,.field select:focus,.field textarea:focus{outline:none;border-color:var(--blue);box-shadow:0 0 0 3px rgba(37,99,235,.13)}
        .filter-actions{display:flex;align-items:center;gap:8px}
        .btn-search,.btn-reset{display:inline-flex;align-items:center;justify-content:center;min-height:39px;padding:0 15px;border:1px solid transparent;border-radius:8px;font:inherit;font-size:.86rem;font-weight:700;cursor:pointer;text-decoration:none;white-space:nowrap}
        .btn-search{background:var(--blue);color:#fff}
        .btn-search:hover{background:var(--blue-dark)}
        .btn-reset{background:#e8eef5;border-color:#dde6ef;color:var(--navy)}
        .btn-reset:hover{background:#dfe8f2}
        .table-meta,.result-meta{margin:0 0 10px;color:var(--muted);font-size:.84rem}
        .table-meta strong,.result-meta strong{color:var(--navy)}
        .table-wrap{overflow:auto;border:1px solid var(--line);border-radius:9px;background:#fff}
        .data-table{width:100%;min-width:900px;border-collapse:collapse;text-align:left}
        .data-table th,.data-table td{padding:12px 14px;border-bottom:1px solid #edf1f5;vertical-align:middle}
        .data-table th{background:var(--surface-soft);color:#5a6c7d;font-size:.72rem;font-weight:800;letter-spacing:.05em;text-transform:uppercase;white-space:nowrap}
        .data-table td{font-size:.87rem}
        .data-table tbody tr{transition:background .12s ease}
        .data-table tbody tr:hover{background:#fbfdff}
        .data-table tbody tr:last-child td{border-bottom:0}
        .data-table p{margin:0 0 4px}
        .data-table p:last-child{margin-bottom:0}
        .cell-main{display:block;color:var(--ink);font-weight:700}
        .cell-sub,.muted{color:var(--muted);font-size:.8rem;line-height:1.45}
        .code{color:var(--navy);font-weight:800;white-space:nowrap}
        .note{max-width:230px;white-space:pre-wrap;overflow-wrap:anywhere}
        .status{display:inline-flex;align-items:center;gap:5px;padding:4px 9px;border-radius:999px;background:#f1f5f9;color:#475569;font-size:.74rem;font-weight:800;line-height:1.3;white-space:nowrap}
        .status.pending{background:var(--amber-soft);color:var(--amber)}
        .status.confirmed,.status.checked-in,.status.verified,.status.active{background:var(--green-soft);color:var(--green)}
        .status.rejected,.status.cancelled,.status.not_suitable,.status.locked{background:var(--red-soft);color:var(--red)}
        .status.completed{background:#eaf2ff;color:#175cd3}
        .status.inactive{background:#f4f7fb;color:var(--muted)}
        .link{color:#175cd3;font-weight:700;text-decoration:none}
        .link:hover{text-decoration:underline;text-underline-offset:3px}
        .inline-form{display:inline-flex;margin:0}
        .actions{display:flex;flex-wrap:wrap;align-items:center;gap:8px}
        .actions form{margin:0}
        .empty,.empty-state{padding:38px 20px;text-align:center;color:var(--muted);font-size:.9rem}
        .pagination{display:flex;align-items:center;justify-content:center;gap:12px;margin:18px 0 0;color:var(--muted);font-size:.84rem}
        .pagination a{display:inline-flex;align-items:center;min-height:34px;padding:0 11px;border:1px solid var(--line);border-radius:8px;background:#fff;color:var(--navy);font-weight:700;text-decoration:none}
        .pagination a:hover{border-color:#c4d2df;background:var(--surface-soft)}
        .pagination span{font-weight:600}
        .pagination .disabled{opacity:.45;pointer-events:none}
        .metrics-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(215px,1fr));gap:14px}
        .metric{position:relative;display:flex;flex-direction:column;min-height:138px;padding:18px;border:1px solid var(--line);border-radius:8px;background:#fff}
        .metric-link{color:inherit;text-decoration:none;transition:border-color .15s ease,transform .15s ease}
        .metric-link:hover{border-color:#b9cddd;transform:translateY(-2px)}
        .metric-top{display:flex;align-items:center;justify-content:space-between;gap:10px}
        .metric-label{margin:0;color:var(--muted);font-size:.78rem;font-weight:800;letter-spacing:.04em;text-transform:uppercase}
        .metric-icon{display:grid;place-items:center;width:34px;height:34px;border-radius:9px;background:#eef4fb;color:var(--blue)}
        .metric-icon svg{width:18px;height:18px;fill:none;stroke:currentColor;stroke-width:1.9;stroke-linecap:round;stroke-linejoin:round}
        .metric-value{margin:14px 0 0;color:var(--navy);font-size:1.85rem;font-weight:800;letter-spacing:-.05em;line-height:1}
        .metric-note{margin:8px 0 0;color:var(--muted);font-size:.78rem;line-height:1.45}
        .metric.pending{border-color:#f0dfb6;background:linear-gradient(180deg,#fffdf8,#fff)}
        .metric.pending .metric-icon{background:var(--amber-soft);color:var(--amber)}
        .metric.revenue .metric-icon{background:var(--green-soft);color:var(--green)}
        .section-head{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;margin:30px 0 13px}
        .section-title{margin:0;color:var(--navy);font-size:1.06rem;letter-spacing:-.015em}
        .section-note{margin:4px 0 0;color:var(--muted);font-size:.82rem}
        .quick-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:11px}
        .quick-card{display:flex;align-items:center;justify-content:space-between;gap:12px;min-height:58px;padding:0 15px;border:1px solid var(--line);border-radius:9px;background:#fff;color:var(--navy);font-size:.88rem;font-weight:700;text-decoration:none;transition:border-color .15s ease,background .15s ease,transform .15s ease}
        .quick-card:hover{border-color:#b9cddd;background:#fbfdff;transform:translateY(-1px)}
        .quick-card svg{flex:0 0 16px;width:16px;height:16px;fill:none;stroke:var(--blue);stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
        .form-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));align-items:end;gap:13px}
        .form-grid .form-actions{display:flex;align-items:end;gap:8px}
        .field{display:grid;gap:5px;min-width:0}
        .field label,.field>span:first-child{color:#526678;font-size:.75rem;font-weight:800;letter-spacing:.03em}
        .field textarea{min-height:112px;padding:10px 11px;resize:vertical;line-height:1.5}
        .field input[type=file]{padding:7px 9px;font-size:.8rem}
        .help{color:var(--muted);font-size:.76rem;line-height:1.45}
        .detail-layout{display:grid;grid-template-columns:minmax(0,1.55fr) minmax(290px,.85fr);gap:18px;align-items:start}
        .detail-summary{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:0 18px;margin:0}
        .detail-item{min-width:0;padding:12px 0;border-bottom:1px solid #edf1f5}
        .detail-label{margin:0 0 5px;color:var(--muted);font-size:.72rem;font-weight:800;letter-spacing:.04em;text-transform:uppercase}
        .detail-value{margin:0;line-height:1.55;overflow-wrap:anywhere}
        .detail-value strong{color:var(--navy)}
        .side-panel{position:sticky;top:94px}
        .side-panel .panel{margin-top:0}
        .status-line{display:flex;align-items:center;gap:9px;margin:0 0 14px}
        .choices{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:11px;margin-bottom:12px}
        .choice{display:grid;grid-template-columns:20px 1fr;align-items:start;gap:9px;min-width:0;padding:13px 14px;border:1px solid var(--line);border-radius:9px;background:#fff;cursor:pointer;transition:border-color .15s ease,background .15s ease}
        .choice:hover{border-color:#b9cddd;background:#fbfdff}
        .choice:has(input:checked){border-color:#9fc3e8;background:#f4f9ff;box-shadow:inset 3px 0 0 var(--blue)}
        .choice input{margin-top:3px;accent-color:var(--blue)}
        .choice strong{display:block;color:var(--navy);font-size:.86rem}
        .choice small{display:block;margin-top:4px;color:var(--muted);font-size:.78rem;line-height:1.45}
        .choice.wide{grid-column:1/-1}
        .hint{margin:11px 0 0;color:var(--muted);font-size:.82rem;line-height:1.55}
        .progress{min-width:130px}
        .track{height:7px;margin-top:6px;overflow:hidden;border-radius:999px;background:#e8eef5}
        .track span{display:block;height:100%;border-radius:inherit;background:var(--blue)}
        .bulk{display:flex;align-items:center;flex-wrap:wrap;gap:9px;margin:0 0 13px;padding:11px 13px;border:1px solid var(--line);border-radius:9px;background:var(--surface-soft)}
        .bulk label{color:#526678;font-size:.78rem;font-weight:700}
        .bulk select{min-height:36px;padding:6px 10px;border:1px solid var(--line-strong);border-radius:8px;background:#fff;font:inherit;font-size:.84rem}
        .row-actions{display:flex;flex-wrap:wrap;gap:6px}
        .row-actions form{display:flex;gap:6px;margin:0}
        .source{color:var(--muted);font-size:.76rem;line-height:1.4}
        .editor{display:grid;gap:10px}
        .editor-row{display:grid;grid-template-columns:minmax(0,1.05fr) minmax(0,1.35fr) minmax(0,.85fr) auto;align-items:end;gap:10px}
        .editor-row.models{grid-template-columns:minmax(0,1fr) minmax(0,1.15fr) minmax(0,.7fr) minmax(0,.7fr) minmax(0,1.1fr) minmax(0,.75fr) auto}
        .editor-actions{display:flex;align-items:center;gap:7px}
        .thumb{width:52px;height:42px;object-fit:contain;border-radius:6px;background:var(--surface-soft)}
        .brand-cell{display:flex;align-items:center;gap:12px;min-width:0}
        .brand-cell .field{flex:1}
        .toolbar{display:flex;align-items:flex-start;justify-content:space-between;gap:18px}
        .toolbar p{margin:0}
        .sub{color:var(--muted);font-size:.9rem;line-height:1.6}
        .is-hidden{display:none!important}
        body.sidebar-collapsed .admin-sidebar{width:84px}
        body.sidebar-collapsed .admin-main{margin-left:84px}
        body.sidebar-collapsed .brand-copy,body.sidebar-collapsed .nav-label,body.sidebar-collapsed .nav-text,body.sidebar-collapsed .sidebar-foot p,body.sidebar-collapsed .sidebar-foot strong,body.sidebar-collapsed .account-copy,body.sidebar-collapsed .account-menu summary>svg{display:none}
        body.sidebar-collapsed .sidebar-brand{justify-content:center;padding-left:0;padding-right:0}
        body.sidebar-collapsed .nav-link{justify-content:center;padding:0}
        body.sidebar-collapsed .nav-group{gap:6px}
        body.sidebar-collapsed .sidebar-foot{display:none}
        .sidebar-scrim{position:fixed;inset:0;z-index:45;display:none;background:rgba(11,39,64,.48);backdrop-filter:blur(2px)}
        @media(max-width:1180px){.filter-grid{grid-template-columns:minmax(200px,1.7fr) minmax(150px,1fr) minmax(140px,1fr) auto}.editor-row.models{grid-template-columns:repeat(3,minmax(0,1fr))}.editor-row.models .editor-actions{grid-column:1/-1}.editor-row{grid-template-columns:repeat(2,minmax(0,1fr))}.editor-row .editor-actions{grid-column:1/-1}}
        @media(max-width:1024px){
            .admin-sidebar{transform:translateX(-100%);width:280px}
            .admin-main,body.sidebar-collapsed .admin-main{margin-left:0}
            body.sidebar-open .admin-sidebar{transform:translateX(0)}
            body.sidebar-open .sidebar-scrim{display:block}
            body.sidebar-open{overflow:hidden}
            body.sidebar-collapsed .brand-copy{display:grid}
            body.sidebar-collapsed .nav-label{display:block}
            body.sidebar-collapsed .nav-text{display:inline}
            body.sidebar-collapsed .sidebar-foot{display:block}
            body.sidebar-collapsed .account-copy{display:grid}
            body.sidebar-collapsed .account-menu summary>svg{display:block}
            body.sidebar-collapsed .admin-sidebar{width:280px}
            body.sidebar-collapsed .sidebar-brand{justify-content:flex-start;padding:4px 8px 18px}
            body.sidebar-collapsed .nav-link{justify-content:flex-start;padding:0 12px}
            .admin-topbar{padding:0 20px}
            .admin-content{padding:24px 20px 48px}
        }
        @media(max-width:820px){
            .page-head{flex-direction:column;gap:14px}
            .page-actions{justify-content:flex-start}
            .detail-layout{grid-template-columns:1fr}
            .side-panel{position:static}
            .filter-grid{grid-template-columns:1fr 1fr}
            .filter-grid .filter-actions{grid-column:1/-1}
            .detail-summary{grid-template-columns:1fr}
        }
        @media(max-width:640px){
            .admin-topbar{min-height:64px;padding:0 14px}
            .admin-content{padding:20px 14px 42px}
            .account-copy{display:none}
            .breadcrumb{font-size:.78rem}
            .page-actions{width:100%;justify-content:stretch}
            .page-actions .button{flex:1}
            .filter-bar{align-items:stretch;flex-direction:column}
            .filter-bar input[type=search]{min-width:0}
            .filter-grid{grid-template-columns:1fr}
            .filter-actions{flex-direction:column;align-items:stretch}
            .filter-actions .button,.filter-actions .btn-search,.filter-actions .btn-reset{width:100%}
            .form-grid,.editor-row,.editor-row.models{grid-template-columns:1fr}
            .metrics-grid{grid-template-columns:1fr 1fr}
            .metric{min-height:126px;padding:15px}
            .metric-value{font-size:1.55rem}
            .panel-head,.panel-body{padding-left:15px;padding-right:15px}
            .page-title{font-size:1.45rem}
            .choices{grid-template-columns:1fr}
            .choice.wide{grid-column:auto}
            .pagination{justify-content:flex-start;flex-wrap:wrap}
        }
        @media(max-width:420px){.metrics-grid{grid-template-columns:1fr}.topbar-right .account-menu summary{padding-right:6px}}
        @media(prefers-reduced-motion:reduce){*,*::before,*::after{scroll-behavior:auto!important;transition-duration:.01ms!important;animation-duration:.01ms!important}}
    </style>
    <link rel="stylesheet" href="<?= e(url('assets/admin.css')) ?>">
    <?php if ($pageStyles !== ''): ?><style><?= $pageStyles ?></style><?php endif; ?>
</head>
<body>
<a class="skip-link" href="#admin-content">Bỏ qua điều hướng</a>
<div class="admin-shell">
    <aside class="admin-sidebar" id="admin-sidebar" aria-label="Điều hướng quản trị">
        <div class="sidebar-brand">
            <a class="brand-link" href="<?= e(url('admin')) ?>" aria-label="DriveHub Admin" title="DriveHub Admin">
                <span class="brand-mark">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 16h14M6 16l1-5 2-3h6l2 3 1 5M7 16v2M17 16v2M9 11h6M8 13h.01M16 13h.01"/></svg>
                </span>
                <span class="brand-copy"><strong>DriveHub</strong><small>Garage operations</small></span>
            </a>
        </div>
        <nav class="sidebar-nav" aria-label="Chức năng quản trị">
            <?php foreach ($adminNavGroups as $groupLabel => $items): ?>
                <div class="nav-group">
                    <p class="nav-label"><?= e($groupLabel) ?></p>
                    <?php foreach ($items as $item): ?>
                        <a class="nav-link<?= $activeNav === $item['key'] ? ' active' : '' ?>" href="<?= e($item['href']) ?>" aria-label="<?= e($item['label']) ?>" title="<?= e($item['label']) ?>"<?= $activeNav === $item['key'] ? ' aria-current="page"' : '' ?>>
                            <?= $adminIcon($item['icon']) ?>
                            <span class="nav-text"><?= e($item['label']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </nav>
        <div class="sidebar-foot">
            <p>Không gian quản trị</p>
            <strong><?= e($adminName) ?></strong>
        </div>
    </aside>
    <div class="sidebar-scrim" data-sidebar-close></div>
    <div class="admin-main">
        <header class="admin-topbar">
            <div class="topbar-left">
                <button class="icon-button" type="button" data-sidebar-toggle aria-label="Mở hoặc thu gọn điều hướng" aria-controls="admin-sidebar" aria-expanded="true">
                    <?= $adminIcon('menu') ?>
                </button>
                <div class="breadcrumb-stack">
                    <span class="topbar-overline">DRIVEHUB · GARAGE MANAGEMENT</span>
                    <nav class="breadcrumb" aria-label="Breadcrumb">
                        <a href="<?= e(url('admin')) ?>">DriveHub</a>
                        <span aria-hidden="true">/</span>
                        <span><?= e($pageEyebrow) ?></span>
                        <span aria-hidden="true">/</span>
                        <span aria-current="page"><?= e($pageTitle) ?></span>
                    </nav>
                </div>
            </div>
            <div class="topbar-right">
                <details class="account-menu">
                    <summary aria-label="Mở menu tài khoản">
                        <span class="avatar"><?= e($adminInitial) ?></span>
                        <span class="account-copy"><strong><?= e($adminName) ?></strong><small>Quản trị viên</small></span>
                        <?= $adminIcon('chevron') ?>
                    </summary>
                    <div class="account-popover">
                        <div class="account-meta"><strong><?= e($adminName) ?></strong><small>Quyền quản trị hệ thống</small></div>
                        <a href="<?= e(url()) ?>">Về trang khách</a>
                        <a href="<?= e(url('profile')) ?>">Hồ sơ cá nhân</a>
                        <a href="<?= e(url('profile/change-password')) ?>">Đổi mật khẩu</a>
                        <form method="post" action="<?= e(url('logout')) ?>">
                            <?= csrf_field() ?>
                            <button type="submit">Đăng xuất</button>
                        </form>
                    </div>
                </details>
            </div>
        </header>
        <main class="admin-content" id="admin-content">
            <div class="page-head">
                <div class="page-head-copy">
                    <p class="page-eyebrow"><?= e($pageEyebrow) ?></p>
                    <h1 class="page-title"><?= e($pageTitle) ?></h1>
                    <?php if ($pageDescription !== ''): ?><p class="page-description"><?= e($pageDescription) ?></p><?php endif; ?>
                </div>
                <?php if ($pageActions !== ''): ?><div class="page-actions"><?= $pageActions ?></div><?php endif; ?>
            </div>
            <?= $content ?>
        </main>
    </div>
</div>
<script>
    (function () {
        var body = document.body;
        var toggle = document.querySelector('[data-sidebar-toggle]');
        var close = document.querySelector('[data-sidebar-close]');
        var accountMenu = document.querySelector('.account-menu');
        var media = window.matchMedia('(max-width: 1024px)');
        var storageKey = 'drivehub.admin.sidebar';

        if (!toggle) {
            return;
        }

        function updateToggleState() {
            var expanded = media.matches
                ? body.classList.contains('sidebar-open')
                : !body.classList.contains('sidebar-collapsed');
            toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        }

        if (!media.matches && window.localStorage.getItem(storageKey) === 'collapsed') {
            body.classList.add('sidebar-collapsed');
        }

        toggle.addEventListener('click', function () {
            if (media.matches) {
                body.classList.toggle('sidebar-open');
                updateToggleState();
                return;
            }

            body.classList.toggle('sidebar-collapsed');
            window.localStorage.setItem(storageKey, body.classList.contains('sidebar-collapsed') ? 'collapsed' : 'open');
            updateToggleState();
        });

        if (close) {
            close.addEventListener('click', function () {
                body.classList.remove('sidebar-open');
                updateToggleState();
            });
        }

        if (accountMenu) {
            document.addEventListener('click', function (event) {
                if (!accountMenu.contains(event.target)) {
                    accountMenu.removeAttribute('open');
                }
            });
        }

        media.addEventListener('change', function () {
            body.classList.remove('sidebar-open');
            updateToggleState();
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                body.classList.remove('sidebar-open');
                if (accountMenu) {
                    accountMenu.removeAttribute('open');
                }
                updateToggleState();
            }
        });

        updateToggleState();
    }());
</script>
</body>
</html>
