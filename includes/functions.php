<?php
require_once __DIR__ . '/config.php';

function e($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
function url($p=''){ return SITE_URL . '/' . ltrim($p, '/'); }
function admin_url($p=''){ return ADMIN_URL . '/' . ltrim($p, '/'); }
function redirect($u){ header("Location: $u"); exit; }
function money($n){ return '₹' . number_format((float)$n, 0); }

function product_image($img){
    if (!$img) return 'https://lh3.googleusercontent.com/aida-public/AB6AXuBoDj3uPQt3tYbxcyJlucBCcvmTryeq217D7IVM-hjt2LGQMenX7b2O0pHsiuY6DgeTj4ec5W1mlfHFkIuTCe6e2UjCzqMIJRYCDtuPRgFI4yFTWxbywmFGG5ol1lC8fCifQ0OEZ1xBWHAnc_29-3-tpjL-GoAd3zONystPqcAqfW0PJ7RNULw-gOxONNRyetPxtv4YYzeXOA6n5bOkeWA1R362bPMZ8Q-3qnFTg5GQW6-ZWniuxFvzEvJuy3d3A29Gzw0IdUpiiWc';
    if (str_starts_with($img, 'http')) return $img;
    if (file_exists(UPLOAD_DIR . '/products/' . $img)) return UPLOAD_URL . '/products/' . $img;
    return UPLOAD_URL . '/products/' . $img;
}
function category_image($img){
    if (!$img) return '';
    if (str_starts_with($img, 'http')) return $img;
    return UPLOAD_URL . '/categories/' . $img;
}

function slugify($t){
    $t = strtolower(trim($t));
    $t = preg_replace('/[^a-z0-9-]+/', '-', $t);
    return trim($t, '-');
}

function flash($key, $msg=null){
    if ($msg === null){
        $m = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $m;
    }
    $_SESSION['flash'][$key] = $msg;
}

function is_logged_in(){ return !empty($_SESSION['user_id']); }
function current_user(){
    global $conn;
    if (!is_logged_in()) return null;
    $id = (int)$_SESSION['user_id'];
    $r = $conn->query("SELECT * FROM users WHERE id=$id");
    return $r ? $r->fetch_assoc() : null;
}
function require_login(){
    if (!is_logged_in()){
        flash('error', 'Please login to continue.');
        redirect(url('login.php'));
    }
}
function is_admin(){ return !empty($_SESSION['admin_id']); }
function require_admin(){ if (!is_admin()) redirect(ADMIN_URL . '/login.php'); }

function cart(){
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    return $_SESSION['cart'];
}
function cart_count(){ return array_sum(array_column(cart(), 'qty')); }
function cart_add($pid, $qty=1){
    $pid = (int)$pid; $qty = max(1, (int)$qty);
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    if (isset($_SESSION['cart'][$pid])) $_SESSION['cart'][$pid]['qty'] += $qty;
    else $_SESSION['cart'][$pid] = ['id'=>$pid, 'qty'=>$qty];
}
function cart_update($pid, $qty){
    $pid=(int)$pid; $qty=(int)$qty;
    if ($qty <= 0) unset($_SESSION['cart'][$pid]);
    else $_SESSION['cart'][$pid]['qty'] = $qty;
}
function cart_remove($pid){ unset($_SESSION['cart'][(int)$pid]); }
function cart_clear(){ $_SESSION['cart'] = []; unset($_SESSION['coupon']); }

function cart_items(){
    global $conn;
    $items = [];
    foreach (cart() as $pid => $c){
        $r = $conn->query("SELECT * FROM products WHERE id=" . (int)$pid);
        if ($r && $row = $r->fetch_assoc()){
            $row['qty'] = $c['qty'];
            $row['effective_price'] = $row['sale_price'] ?: $row['price'];
            $row['line_total'] = $row['effective_price'] * $c['qty'];
            $items[] = $row;
        }
    }
    return $items;
}
function cart_subtotal(){
    $s = 0;
    foreach (cart_items() as $i) $s += $i['line_total'];
    return $s;
}

function apply_coupon($code){
    global $conn;
    $code = trim($code);
    $stmt = $conn->prepare("SELECT * FROM coupons WHERE code=? AND status=1");
    $stmt->bind_param('s', $code);
    $stmt->execute();
    $c = $stmt->get_result()->fetch_assoc();
    if (!$c) return ['ok'=>false, 'msg'=>'Invalid coupon.'];
    $today = date('Y-m-d');
    if ($c['valid_from'] && $today < $c['valid_from']) return ['ok'=>false,'msg'=>'Coupon not yet active.'];
    if ($c['valid_to'] && $today > $c['valid_to']) return ['ok'=>false,'msg'=>'Coupon expired.'];
    $sub = cart_subtotal();
    if ($sub < $c['min_order']) return ['ok'=>false,'msg'=>'Min order ' . money($c['min_order']) . ' required.'];
    if ($c['usage_limit'] && $c['used'] >= $c['usage_limit']) return ['ok'=>false,'msg'=>'Coupon limit reached.'];
    $disc = $c['type']=='percent' ? ($sub * $c['discount']/100) : $c['discount'];
    if ($c['max_discount']) $disc = min($disc, $c['max_discount']);
    return ['ok'=>true, 'coupon'=>$c, 'discount'=>round($disc,2)];
}

function setting(){
    global $conn;
    static $s = null;
    if ($s === null){
        $r = $conn->query("SELECT * FROM settings WHERE id=1");
        $s = $r ? $r->fetch_assoc() : [];
    }
    return $s;
}

function get_categories($featured_only=false){
    global $conn;
    $w = $featured_only ? "WHERE status=1 AND is_featured=1" : "WHERE status=1";
    return $conn->query("SELECT * FROM categories $w ORDER BY id DESC");
}

function get_menu($location='header'){
    return db_all("SELECT * FROM menus WHERE location=? AND status=1 ORDER BY sort_order, id", [$location]);
}

function get_hero_banners(){
    return db_all("SELECT * FROM hero_banners WHERE status=1 ORDER BY sort_order, id");
}
function hero_banner_url($file){
    if (!$file) return '';
    if (str_starts_with($file, 'http')) return $file;
    return UPLOAD_URL . '/banners/' . $file;
}

function get_banners($position='hero'){
    return db_all("SELECT * FROM banners WHERE status=1 AND position=? ORDER BY sort_order, id", [$position]);
}

function get_testimonials($limit=6){
    $limit = (int)$limit;
    return db_all("SELECT * FROM testimonials WHERE status=1 ORDER BY id DESC LIMIT $limit");
}

function get_page($slug){ return db_one("SELECT * FROM pages WHERE slug=?", [$slug]); }

function in_wishlist($pid){
    if (!is_logged_in()) return false;
    return (bool)db_one("SELECT id FROM wishlist WHERE user_id=? AND product_id=?", [$_SESSION['user_id'], (int)$pid]);
}
function wishlist_count(){
    if (!is_logged_in()) return 0;
    $r = db_one("SELECT COUNT(*) c FROM wishlist WHERE user_id=?", [$_SESSION['user_id']]);
    return (int)($r['c'] ?? 0);
}
function logo_url(){
    $s = setting();
    if (!empty($s['logo'])) return UPLOAD_URL . '/site/' . $s['logo'];
    return null;
}
function product_gallery($p){
    $imgs = [];
    if (!empty($p['image'])) $imgs[] = product_image($p['image']);
    if (!empty($p['gallery'])){
        foreach (preg_split('/[,\n]+/', $p['gallery']) as $g){
            $g = trim($g);
            if ($g !== '') $imgs[] = product_image($g);
        }
    }
    return array_values(array_unique($imgs));
}

function product_sizes($pid){
    global $conn;
    $pid = (int)$pid;
    $rows = [];
    $r = @$conn->query("SELECT value, stock FROM product_variants WHERE product_id=$pid AND name='size' ORDER BY id");
    if ($r) while ($row = $r->fetch_assoc()) $rows[] = $row;
    return $rows;
}

function product_reviews($pid){
    global $conn;
    $pid = (int)$pid;
    $rows = [];
    $r = @$conn->query("SELECT * FROM reviews WHERE product_id=$pid AND status=1 ORDER BY id DESC");
    if ($r) while ($row = $r->fetch_assoc()) $rows[] = $row;
    return $rows;
}

function review_summary($pid){
    global $conn;
    $pid = (int)$pid;
    $sum = ['count'=>0, 'avg'=>0, 'breakdown'=>[5=>0,4=>0,3=>0,2=>0,1=>0]];
    $r = @$conn->query("SELECT rating, COUNT(*) c FROM reviews WHERE product_id=$pid AND status=1 GROUP BY rating");
    if (!$r) return $sum;
    $total = 0; $weighted = 0;
    while ($row = $r->fetch_assoc()){
        $rt = (int)$row['rating']; $c = (int)$row['c'];
        if (isset($sum['breakdown'][$rt])) $sum['breakdown'][$rt] = $c;
        $total += $c; $weighted += $rt * $c;
    }
    $sum['count'] = $total;
    $sum['avg'] = $total ? round($weighted / $total, 2) : 0;
    return $sum;
}

function send_admin_notification($subject, $body){
    $s = setting();
    $to = $s['email'] ?? 'admin@texturenbeyond.com';
    @mail($to, $subject, $body, "From: noreply@texturenbeyond.com\r\nContent-Type: text/plain; charset=utf-8");
}
