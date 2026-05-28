<?php
/**
 * One-time setup script. Run after importing both SQL files.
 * Sets a working bcrypt password for the default admin.
 * DELETE THIS FILE after running once.
 */
require_once __DIR__ . '/config/config.php';
$hash = password_hash('admin123', PASSWORD_DEFAULT);
db_exec("UPDATE users SET password=? WHERE email='admin@texturebeyond.com'", [$hash]);
db_exec("UPDATE admins SET password=? WHERE email='admin@texturebeyond.com'", [$hash]);
echo "<h3>✔ Setup complete</h3>";
echo "<p>Storefront: <a href='" . SITE_URL . "/'>" . SITE_URL . "</a></p>";
echo "<p>Admin: <a href='" . ADMIN_URL . "/login.php'>" . ADMIN_URL . "/login.php</a></p>";
echo "<p>Login: <code>admin@texturebeyond.com</code> / <code>admin123</code></p>";
echo "<p style='color:red'><strong>Now delete this file (install.php).</strong></p>";
