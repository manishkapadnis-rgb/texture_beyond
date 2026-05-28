<?php
require_once '../includes/functions.php';
header('Content-Type: application/json');
ensure_review_tables();

if (!is_logged_in()) {
    echo json_encode(['ok' => false, 'msg' => 'Please login to write a review.', 'login_required' => true, 'login_url' => url('login.php')]);
    exit;
}

$uid    = (int)$_SESSION['user_id'];
$pid    = (int)($_POST['product_id'] ?? 0);
$rating = max(1, min(5, (int)($_POST['rating'] ?? 5)));
$title  = trim($_POST['title'] ?? '');
$body   = trim($_POST['body']  ?? '');

if (!$pid || $body === '') {
    echo json_encode(['ok' => false, 'msg' => 'Please write your review before submitting.']);
    exit;
}

$order_id = user_purchased_product($uid, $pid);
if (!$order_id) {
    echo json_encode(['ok' => false, 'msg' => 'Only customers who purchased this product can review it.']);
    exit;
}

if (user_review_for($uid, $pid)) {
    echo json_encode(['ok' => false, 'msg' => 'You have already reviewed this product.']);
    exit;
}

$u     = current_user();
$name  = $u['name']  ?? 'Customer';
$email = $u['email'] ?? null;
$verified = 1;
$status   = 'approved';

$stmt = $conn->prepare("INSERT INTO reviews (product_id, user_id, order_id, name, email, rating, title, body, verified, status) VALUES (?,?,?,?,?,?,?,?,?,?)");
$stmt->bind_param('iiississis', $pid, $uid, $order_id, $name, $email, $rating, $title, $body, $verified, $status);
if (!$stmt->execute()) {
    echo json_encode(['ok' => false, 'msg' => 'Could not save your review. Please try again.']);
    exit;
}
$review_id = $conn->insert_id;

if (!empty($_FILES['images']['name']) && is_array($_FILES['images']['name'])) {
    $dir = UPLOAD_DIR . '/reviews';
    if (!is_dir($dir)) @mkdir($dir, 0775, true);
    $ins = $conn->prepare("INSERT INTO review_images (review_id, image, sort_order) VALUES (?,?,?)");
    foreach ($_FILES['images']['name'] as $i => $orig) {
        if (empty($_FILES['images']['tmp_name'][$i])) continue;
        if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) continue;
        $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','webp','gif'])) continue;
        $fname = 'r' . $review_id . '_' . time() . '_' . $i . '.' . $ext;
        if (@move_uploaded_file($_FILES['images']['tmp_name'][$i], $dir . '/' . $fname)) {
            $ins->bind_param('isi', $review_id, $fname, $i);
            $ins->execute();
        }
    }
}

echo json_encode(['ok' => true, 'msg' => 'Thank you! Your verified review has been posted.']);
