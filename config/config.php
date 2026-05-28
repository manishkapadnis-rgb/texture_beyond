<?php
/**
 * Production-ready config. Auto-detects base URL.
 * Edit DB credentials below for Hostinger.
 */
if (session_status() === PHP_SESSION_NONE){
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    session_start();
}

// ---------- Environment ----------
define('ENV', 'production'); // development | production

// ---------- Database ----------
if (ENV === 'production') {
    define('DB_HOST', 'localhost');
    define('DB_USER', 'u663620806_textureb');
    define('DB_PASS', '/Exjht*4');
    define('DB_NAME', 'u663620806_textureb');
} else {
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'u663620806_textureb');
}

// ---------- Dynamic Base URL ----------
$proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? 80) == 443 ? 'https' : 'http';
$host  = $_SERVER['HTTP_HOST'] ?? 'localhost';
$dir   = rtrim(str_replace('\\','/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
// Strip /admin (and anything after) so SITE_URL always points to project root,
// regardless of whether the project lives at the domain root or in a subfolder.
$dir = preg_replace('#/admin(/.*)?$#', '', $dir);
if ($dir === '' || $dir === '/') $dir = '';
define('SITE_URL',   $proto . '://' . $host . $dir);
define('ADMIN_URL',  SITE_URL . '/admin');
define('ASSETS_URL', SITE_URL . '/assets');
define('UPLOAD_URL', SITE_URL . '/uploads');

define('BASE_DIR',   dirname(__DIR__));
define('UPLOAD_DIR', BASE_DIR . '/uploads');
define('LOG_DIR',    BASE_DIR . '/logs');

// ---------- Brand ----------
define('SITE_NAME',    'Texture & Beyond');
define('SITE_TAGLINE', 'Modern Indian Art & Decor');
define('CURRENCY',     '₹');
define('ITEMS_PER_PAGE', 12);

// ---------- Error reporting ----------
if (ENV === 'development'){
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    @ini_set('error_log', LOG_DIR . '/php-error.log');
}

date_default_timezone_set('Asia/Kolkata');

require_once __DIR__ . '/database.php';
