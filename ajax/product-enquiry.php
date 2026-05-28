<?php
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');
$pid = (int)($_POST['product_id'] ?? 0);
db_exec("INSERT INTO enquiries (type,product_id,name,email,phone,subject,message) VALUES ('product',?,?,?,?,?,?)",
  [$pid, $_POST['name']??'', $_POST['email']??'', $_POST['phone']??'', 'Product enquiry', $_POST['message']??'']);
send_admin_notification('New product enquiry', json_encode($_POST, JSON_PRETTY_PRINT));
echo json_encode(['ok'=>true]);
