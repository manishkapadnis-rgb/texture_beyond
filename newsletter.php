<?php
require_once 'includes/functions.php';
$e = trim($_POST['email'] ?? '');
if ($e){
    $stmt = $conn->prepare("INSERT IGNORE INTO newsletter (email) VALUES (?)");
    $stmt->bind_param('s', $e); $stmt->execute();
    flash('success', 'Subscribed.');
}
redirect($_SERVER['HTTP_REFERER'] ?? url('index.php'));
