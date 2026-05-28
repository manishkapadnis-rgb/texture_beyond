<?php
require_once 'includes/functions.php';

$action = $_REQUEST['action'] ?? '';
$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false
    || !empty($_REQUEST['ajax']);

// Guest carts allowed — account is created at checkout, not on add.
$response = ['ok' => true, 'msg' => ''];

switch ($action) {
    case 'add':
        cart_add($_POST['id'] ?? 0, $_POST['qty'] ?? 1);
        $response['msg'] = 'Product added to cart';
        flash('success', 'Added to cart.');
        break;
    case 'update':
        if (isset($_POST['id'])) {
            cart_update((int)$_POST['id'], (int)($_POST['qty'] ?? 1));
        } else {
            foreach (($_POST['qty'] ?? []) as $pid => $q) cart_update($pid, $q);
        }
        $response['msg'] = 'Cart updated';
        flash('success', 'Cart updated.');
        break;
    case 'remove':
        cart_remove($_REQUEST['id'] ?? 0);
        $response['msg'] = 'Item removed';
        flash('success', 'Item removed.');
        break;
    case 'coupon':
        $res = apply_coupon($_POST['code'] ?? '');
        if ($res['ok']) {
            $_SESSION['coupon'] = $res['coupon']['code'];
            $response['msg'] = 'Coupon applied';
            flash('success', 'Coupon applied.');
        } else {
            unset($_SESSION['coupon']);
            $response['ok'] = false;
            $response['msg'] = $res['msg'];
            flash('error', $res['msg']);
        }
        break;
    case 'clear-coupon':
        unset($_SESSION['coupon']);
        $response['msg'] = 'Coupon removed';
        break;
}

if ($is_ajax) {
    $items = cart_items();
    $payload_items = [];
    foreach ($items as $it) {
        $payload_items[] = [
            'id'         => (int)$it['id'],
            'name'       => $it['name'],
            'slug'       => $it['slug'],
            'image'      => product_image($it['image']),
            'price'      => (float)$it['effective_price'],
            'price_html' => money($it['effective_price']),
            'qty'        => (int)$it['qty'],
            'line_total' => (float)$it['line_total'],
            'line_html'  => money($it['line_total']),
            'url'        => url('product.php?slug=' . $it['slug']),
        ];
    }
    $subtotal = cart_subtotal();
    $response['count']         = cart_count();
    $response['subtotal']      = $subtotal;
    $response['subtotal_html'] = money($subtotal);
    $response['items']         = $payload_items;
    $response['cart_url']      = url('cart.php');
    $response['checkout_url']  = url('checkout.php');
    $s = setting();
    $free_above = (float)($s['free_shipping_above'] ?? 0);
    $response['free_shipping_threshold']      = $free_above;
    $response['free_shipping_threshold_html'] = money($free_above);
    $response['free_shipping_remaining']      = max(0, $free_above - $subtotal);
    $response['free_shipping_remaining_html'] = money(max(0, $free_above - $subtotal));
    $response['free_shipping_progress']       = $free_above > 0 ? min(100, ($subtotal / $free_above) * 100) : 100;
    $response['free_shipping_unlocked']       = $free_above > 0 && $subtotal >= $free_above;

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

redirect($_POST['redirect'] ?? $_SERVER['HTTP_REFERER'] ?? url('cart.php'));
