<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

$user_id = $_SESSION['user_id'] ?? null;
$username = $_SESSION['username'] ?? null;
$is_admin = isAdmin();

function get_nav_link($path) {
    $current_dir = basename(dirname($_SERVER['PHP_SELF']));
    $prefix = ($current_dir === 'php') ? '' : '../';
    return $prefix . $path;
}

$page_title = $page_title ?? 'Acasă';
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NiceView - <?php echo htmlspecialchars($page_title); ?></title>
    <?php
    $current_dir = basename(dirname($_SERVER['PHP_SELF']));
    $asset_prefix = ($current_dir === 'php') ? '' : '../';
    
    // Get site name and desc from SQLite
    $sqlite = getSQLitePDO();
    $h_site_name = 'NiceView';
    $h_site_desc = 'Platformă pentru gestionarea priveliștilor turistice';
    if ($sqlite) {
        $res = $sqlite->query("SELECT setting_value FROM settings WHERE setting_key='site_name'");
        $h_site_name = $res ? $res->fetchColumn() : $h_site_name;
        $res = $sqlite->query("SELECT setting_value FROM settings WHERE setting_key='site_description'");
        $h_site_desc = $res ? $res->fetchColumn() : $h_site_desc;
    }
    ?>
    <link rel="icon" type="image/x-icon" href="<?php echo $asset_prefix; ?>images/logo.jpg">
    <link rel="stylesheet" href="<?php echo $asset_prefix; ?>css/style-horizontal.css">
    <link rel="stylesheet" href="<?php echo $asset_prefix; ?>css/style-js.css">
</head>
<body>

<header>
    <h1><?php echo htmlspecialchars($h_site_name); ?></h1>
    <h2><?php echo htmlspecialchars($h_site_desc); ?></h2>
</header>

<nav>
    <ul class="menu">
        <li class="dropdown">
            <a href="<?php echo get_nav_link('index.php'); ?>" class="sprite-acasa">Acasă</a>
            <ul class="dropdown-content">
                <li><a href="<?php echo get_nav_link('index.php'); ?>">Vedere generală</a></li>
                <li><a href="<?php echo get_nav_link('index.php#privelisti'); ?>">Priveliști</a></li>
                <li><a href="<?php echo get_nav_link('index.php#statistici'); ?>">Statistici</a></li>
            </ul>
        </li>
        
        <?php if (isLoggedIn()): ?>
            <li><a href="<?php echo get_nav_link('user/adauga.php'); ?>" class="sprite-adauga">Adaugă priveliște</a></li>
            <li><a href="<?php echo get_nav_link('user/dashboard.php'); ?>" class="sprite-dashboard">Dashboard</a></li>
            <li><a href="<?php echo get_nav_link('harta.php'); ?>" class="sprite-harta">Hartă</a></li>
            <li><a href="<?php echo get_nav_link('user/upload.php'); ?>" class="sprite-galerie">Upload fișiere</a></li>
            <li><a href="<?php echo get_nav_link('user/profile.php'); ?>" class="sprite-harta">Profilul meu</a></li>
            <?php if (isAdmin()): ?>
                <li><a href="<?php echo get_nav_link('admin/admin.php'); ?>">Admin</a></li>
            <?php endif; ?>
            <li><a href="<?php echo get_nav_link('auth/logout.php'); ?>">Logout</a></li>
        <?php else: ?>
            <li><a href="<?php echo get_nav_link('auth/login.php'); ?>">Login</a></li>
            <li><a href="<?php echo get_nav_link('auth/register.php'); ?>">Înregistrare</a></li>
        <?php endif; ?>
        
        <li><a href="<?php echo get_nav_link('contact.php'); ?>">Contact</a></li>
        <li><a href="<?php echo get_nav_link('vulnerabilities/'); ?>" style="color: #e74c3c; font-weight: bold;">Securitate Lab</a></li>
    </ul>
</nav>

<main>
<?php if (isLoggedIn()): ?>
    <div style="background: rgba(44, 62, 80, 0.05); padding: 10px 20px; margin-bottom: 20px; border-left: 4px solid #3498db;">
        <p>Autentificat ca: <strong><?php echo htmlspecialchars($username); ?></strong> (<?php echo htmlspecialchars($_SESSION['role']); ?>)</p>
    </div>
<?php endif; ?>