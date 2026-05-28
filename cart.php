<?php
require_once 'includes/functions.php';
$page_title = 'Shopping Cart | Texture & Beyond';
$items = cart_items();
$subtotal = cart_subtotal();
$discount = 0;
$coupon_code = $_SESSION['coupon'] ?? '';
if ($coupon_code){
    $r = apply_coupon($coupon_code);
    if ($r['ok']) $discount = $r['discount']; else unset($_SESSION['coupon']);
}
$s = setting();
$shipping = ($subtotal >= ($s['free_shipping_above'] ?? 0) || $subtotal == 0) ? 0 : (float)$s['shipping_charge'];
$total = max(0, $subtotal - $discount + $shipping);
include 'includes/header.php';
?>
<section class="pt-40 pb-section-gap px-margin-mobile md:px-margin-desktop bg-surface min-h-screen">
  <div class="max-w-container-max mx-auto">
    <div class="text-center mb-16 gsap-fade">
      <span class="font-label-caps text-label-caps text-secondary mb-4 block">Your Curated Cart</span>
      <h1 class="font-display-md text-headline-lg md:text-display-md">Shopping Cart</h1>
    </div>

    <?php if (empty($items)): ?>
      <div class="text-center py-20">
        <p class="font-body-lg text-on-surface-variant mb-8">Your cart is empty.</p>
        <a href="<?= url('shop.php') ?>" class="btn-primary inline-block">Continue Shopping</a>
      </div>
    <?php else: ?>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-16">
      <form method="post" action="<?= url('cart-action.php') ?>" class="lg:col-span-2">
        <input type="hidden" name="action" value="update">
        <div class="border-t border-outline-variant/30">
          <?php foreach ($items as $it): ?>
          <div class="flex gap-6 py-8 border-b border-outline-variant/30 items-center">
            <a href="<?= url('product.php?slug=' . $it['slug']) ?>" class="w-32 h-40 bg-surface-container-high overflow-hidden flex-shrink-0">
              <img src="<?= e(product_image($it['image'])) ?>" class="w-full h-full object-cover"/>
            </a>
            <div class="flex-grow">
              <h3 class="font-display-md text-headline-md mb-2"><?= e($it['name']) ?></h3>
              <p class="font-body-md text-on-surface-variant mb-4"><?= money($it['effective_price']) ?></p>
              <div class="flex items-center gap-6">
                <div class="flex items-center border border-primary">
                  <button type="button" onclick="const i=this.nextElementSibling;i.value=Math.max(1,parseInt(i.value)-1)" class="px-3 py-2">−</button>
                  <input name="qty[<?= $it['id'] ?>]" type="number" value="<?= $it['qty'] ?>" min="1" class="w-12 text-center bg-transparent border-none outline-none focus:ring-0"/>
                  <button type="button" onclick="const i=this.previousElementSibling;i.value=parseInt(i.value)+1" class="px-3 py-2">+</button>
                </div>
                <a href="<?= url('cart-action.php?action=remove&id=' . $it['id']) ?>" class="font-label-caps text-label-caps text-on-surface-variant hover:text-error">Remove</a>
              </div>
            </div>
            <div class="font-display-md text-[20px]"><?= money($it['line_total']) ?></div>
          </div>
          <?php endforeach; ?>
        </div>
        <div class="flex justify-between mt-8">
          <a href="<?= url('shop.php') ?>" class="font-label-caps text-label-caps gold-underline">← Continue Shopping</a>
          <button class="btn-ghost">Update Cart</button>
        </div>
      </form>

      <div class="bg-surface-container-low p-10">
        <h3 class="font-display-md text-headline-md mb-8">Order Summary</h3>
        <div class="flex justify-between font-body-md mb-4"><span>Subtotal</span><span><?= money($subtotal) ?></span></div>
        <?php if ($discount): ?><div class="flex justify-between font-body-md mb-4 text-secondary"><span>Discount (<?= e($coupon_code) ?>)</span><span>−<?= money($discount) ?></span></div><?php endif; ?>
        <div class="flex justify-between font-body-md mb-4"><span>Shipping</span><span><?= $shipping?money($shipping):'Free' ?></span></div>
        <div class="border-t border-primary mt-6 pt-6 flex justify-between font-display-md text-[24px]"><span>Total</span><span><?= money($total) ?></span></div>

        <form method="post" action="<?= url('cart-action.php') ?>" class="mt-8 flex gap-2">
          <input type="hidden" name="action" value="coupon">
          <input name="code" placeholder="Coupon Code" class="input-underline flex-grow" value="<?= e($coupon_code) ?>"/>
          <button class="font-label-caps text-label-caps gold-underline">Apply</button>
        </form>

        <a href="<?= url('checkout.php') ?>" class="btn-primary block text-center mt-8">Proceed to Checkout</a>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php include 'includes/footer.php'; ?>
