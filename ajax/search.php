<?php
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');
$q = trim($_GET['q'] ?? '');
if (strlen($q) < 2){ echo json_encode([]); exit; }
$like = '%' . $q . '%';
$rows = db_all("SELECT id,name,slug,image,price,sale_price FROM products WHERE status=1 AND (name LIKE ? OR short_description LIKE ?) LIMIT 8", [$like,$like]);
foreach ($rows as &$r) $r['image_url'] = product_image($r['image']);
echo json_encode($rows);
