<?php
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');
if (!is_logged_in()){ echo json_encode(['ok'=>false,'msg'=>'Please login.','login_required'=>true]); exit; }
$pid = (int)($_POST['id'] ?? 0);
if (!$pid){ echo json_encode(['ok'=>false,'msg'=>'Invalid product.']); exit; }
$uid = $_SESSION['user_id'];
if (in_wishlist($pid)){
    db_exec("DELETE FROM wishlist WHERE user_id=? AND product_id=?", [$uid,$pid]);
    echo json_encode(['ok'=>true,'state'=>'removed','count'=>wishlist_count()]);
} else {
    db_exec("INSERT INTO wishlist (user_id,product_id) VALUES (?,?)", [$uid,$pid]);
    echo json_encode(['ok'=>true,'state'=>'added','count'=>wishlist_count()]);
}
