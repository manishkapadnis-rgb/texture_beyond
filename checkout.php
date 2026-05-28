<?php
require_once 'includes/functions.php';
if (!is_logged_in()) {
    flash('error', 'Please login to checkout.');
    redirect(url('login.php?return=' . urlencode(url('checkout.php'))));
}
$items = cart_items();
if (!$items) redirect(url('cart.php'));

$subtotal = cart_subtotal();
$discount = 0; $coupon_code = $_SESSION['coupon'] ?? '';
if ($coupon_code){ $r = apply_coupon($coupon_code); if ($r['ok']) $discount = $r['discount']; }
$s = setting();
$shipping = ($subtotal >= ($s['free_shipping_above'] ?? 0)) ? 0 : (float)$s['shipping_charge'];
$total = max(0, $subtotal - $discount + $shipping);
$u = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    $code = 'TB' . strtoupper(bin2hex(random_bytes(4)));
    $stmt = $conn->prepare("INSERT INTO orders (order_code,user_id,full_name,email,phone,address,city,state,pincode,subtotal,discount,coupon_code,shipping,total,payment_method,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $uid = $u['id'] ?? null;
    $name=$_POST['full_name']; $em=$_POST['email']; $ph=$_POST['phone'];
    $ad=$_POST['address']; $ci=$_POST['city']; $st=$_POST['state']; $pn=$_POST['pincode'];
    $pm=$_POST['payment_method'] ?? 'COD'; $no=$_POST['notes'] ?? '';
    $stmt->bind_param('sissssssssddsdss', $code,$uid,$name,$em,$ph,$ad,$ci,$st,$pn,$subtotal,$discount,$coupon_code,$shipping,$total,$pm,$no);
    $stmt->execute();
    $order_id = $conn->insert_id;
    $ins = $conn->prepare("INSERT INTO order_items (order_id,product_id,product_name,price,quantity,subtotal) VALUES (?,?,?,?,?,?)");
    foreach ($items as $it){
        $ins->bind_param('iisdid', $order_id, $it['id'], $it['name'], $it['effective_price'], $it['qty'], $it['line_total']);
        $ins->execute();
        $conn->query("UPDATE products SET stock=GREATEST(0,stock-{$it['qty']}) WHERE id={$it['id']}");
    }
    if ($coupon_code) $conn->query("UPDATE coupons SET used=used+1 WHERE code='" . $conn->real_escape_string($coupon_code) . "'");
    cart_clear();
    redirect(url('thank-you.php?code=' . $code));
}

$page_title = 'Checkout | Texture & Beyond';
include 'includes/header.php';
?>
<section class="pt-40 pb-section-gap px-margin-mobile md:px-margin-desktop bg-surface">
  <div class="max-w-container-max mx-auto">
    <div class="text-center mb-16">
      <span class="font-label-caps text-label-caps text-secondary mb-4 block">Final Step</span>
      <h1 class="font-display-md text-headline-lg md:text-display-md">Checkout</h1>
    </div>

    <form method="post" class="grid grid-cols-1 lg:grid-cols-3 gap-16">
      <div class="lg:col-span-2 space-y-10">
        <div>
          <h3 class="font-display-md text-headline-md mb-6">Shipping Details</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <input class="input-underline" name="full_name" required placeholder="Full Name" value="<?= e($u['name'] ?? '') ?>"/>
            <input class="input-underline" name="email" type="email" required placeholder="Email" value="<?= e($u['email'] ?? '') ?>"/>
            <input class="input-underline" name="phone" required placeholder="Phone" value="<?= e($u['phone'] ?? '') ?>"/>
            <input class="input-underline" name="pincode" required placeholder="Pincode" value="<?= e($u['pincode'] ?? '') ?>"/>
            <input class="input-underline md:col-span-2" name="address" required placeholder="Address" value="<?= e($u['address'] ?? '') ?>"/>
            <input class="input-underline" name="city" required placeholder="City" value="<?= e($u['city'] ?? '') ?>"/>
            <input class="input-underline" name="state" required placeholder="State" value="<?= e($u['state'] ?? '') ?>"/>
            <textarea class="input-underline md:col-span-2" name="notes" placeholder="Order notes (optional)" rows="2"></textarea>
          </div>
        </div>
        <div>
          <h3 class="font-display-md text-headline-md mb-6">Payment</h3>
          <label class="flex items-center gap-4 border border-primary p-6 cursor-pointer mb-4">
            <input type="radio" name="payment_method" value="COD" checked/> <span class="font-label-caps text-label-caps">Cash on Delivery</span>
          </label>
          <label class="flex items-center gap-4 border border-outline-variant p-6 cursor-pointer mb-4">
            <input type="radio" name="payment_method" value="RAZORPAY"/> <span class="font-label-caps text-label-caps">Razorpay (Cards / UPI / Netbanking)</span>
          </label>
          <label class="flex items-center gap-4 border border-outline-variant p-6 cursor-pointer">
            <input type="radio" name="payment_method" value="BANK"/> <span class="font-label-caps text-label-caps">Bank Transfer</span>
          </label>
        </div>
      </div>
      <div class="bg-surface-container-low p-10 h-fit sticky top-32">
        <h3 class="font-display-md text-headline-md mb-8">Your Order</h3>
        <div class="space-y-4 border-b border-outline-variant/30 pb-6 mb-6">
          <?php foreach ($items as $it): ?>
          <div class="flex justify-between font-body-md">
            <span><?= e($it['name']) ?> × <?= $it['qty'] ?></span>
            <span><?= money($it['line_total']) ?></span>
          </div>
          <?php endforeach; ?>
        </div>
        <div class="flex justify-between font-body-md mb-3"><span>Subtotal</span><span><?= money($subtotal) ?></span></div>
        <?php if ($discount): ?><div class="flex justify-between font-body-md mb-3 text-secondary"><span>Discount</span><span>−<?= money($discount) ?></span></div><?php endif; ?>
        <div class="flex justify-between font-body-md mb-3"><span>Shipping</span><span><?= $shipping?money($shipping):'Free' ?></span></div>
        <div class="border-t border-primary mt-6 pt-6 flex justify-between font-display-md text-[24px]"><span>Total</span><span><?= money($total) ?></span></div>
        <button class="btn-primary w-full mt-8">Place Order</button>
      </div>
    </form>
  </div>
</section>
<?php include 'includes/footer.php'; ?>
