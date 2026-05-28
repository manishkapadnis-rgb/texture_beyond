<?php
require_once 'includes/functions.php';
$action = $_REQUEST['action'] ?? '';
$ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) || ($_SERVER['HTTP_ACCEPT'] ?? '') === 'application/json' || isset($_POST['ajax']);

switch ($action){
    case 'add':
        cart_add($_POST['id'] ?? 0, $_POST['qty'] ?? 1);
        flash('success', 'Added to cart.');
        break;
    case 'update':
        foreach (($_POST['qty'] ?? []) as $pid => $q) cart_update($pid, $q);
        flash('success', 'Cart updated.');
        break;
    case 'remove':
        cart_remove($_REQUEST['id'] ?? 0);
        flash('success', 'Item removed.');
        break;
    case 'coupon':
        $res = apply_coupon($_POST['code'] ?? '');
        if ($res['ok']){ $_SESSION['coupon'] = $res['coupon']['code']; flash('success', 'Coupon applied.'); }
        else { unset($_SESSION['coupon']); flash('error', $res['msg']); }
        break;
    case 'clear-coupon':
        unset($_SESSION['coupon']); break;
}

if (str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'urlencoded') && $action === 'add' && !isset($_POST['redirect'])){
    header('Content-Type: application/json');
    echo json_encode(['ok'=>true, 'count'=>cart_count()]);
    exit;
}
redirect($_POST['redirect'] ?? $_SERVER['HTTP_REFERER'] ?? url('cart.php'));
