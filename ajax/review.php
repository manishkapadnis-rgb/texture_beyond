<?php
require_once '../includes/functions.php';
header('Content-Type: application/json');

$pid    = (int)($_POST['product_id'] ?? 0);
$name   = trim($_POST['name']   ?? '');
$email  = trim($_POST['email']  ?? '');
$rating = max(1, min(5, (int)($_POST['rating'] ?? 5)));
$title  = trim($_POST['title']  ?? '');
$body   = trim($_POST['body']   ?? '');

if (!$pid || $name === '' || $body === '') {
    echo json_encode(['ok' => false, 'msg' => 'Please fill all required fields.']);
    exit;
}

$image = null;
if (!empty($_FILES['image']['tmp_name']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
    $ext  = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg','jpeg','png','webp','gif'])) {
        $dir = UPLOAD_DIR . '/reviews';
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        $fname = 'r' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        if (@move_uploaded_file($_FILES['image']['tmp_name'], $dir . '/' . $fname)) {
            $image = $fname;
        }
    }
}

$uid = $_SESSION['user_id'] ?? null;
$stmt = $conn->prepare("INSERT INTO reviews (product_id, user_id, name, email, rating, title, body, image, status) VALUES (?,?,?,?,?,?,?,?,1)");
$stmt->bind_param('iississs', $pid, $uid, $name, $email, $rating, $title, $body, $image);
if (!$stmt->execute()) {
    echo json_encode(['ok' => false, 'msg' => 'Could not save your review. Please try again.']);
    exit;
}

echo json_encode(['ok' => true, 'msg' => 'Thank you! Your review has been posted.']);
