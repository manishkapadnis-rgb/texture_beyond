<?php
require_once 'includes/functions.php';
$code = $_GET['code'] ?? '';
$stmt = $conn->prepare("SELECT * FROM orders WHERE order_code=?");
$stmt->bind_param('s', $code); $stmt->execute();
$o = $stmt->get_result()->fetch_assoc();
$page_title = 'Order Confirmed | Texture & Beyond';
include 'includes/header.php';
?>
<section class="pt-40 pb-section-gap px-margin-mobile md:px-margin-desktop bg-surface min-h-[80vh] flex items-center">
  <div class="max-w-2xl mx-auto text-center gsap-fade">
    <span class="material-symbols-outlined text-secondary text-[72px] mb-6">check_circle</span>
    <span class="font-label-caps text-label-caps text-secondary mb-4 block">Thank You</span>
    <h1 class="font-display-md text-headline-lg md:text-display-md mb-8">Your Order Is Confirmed</h1>
    <?php if ($o): ?>
      <p class="font-body-lg text-on-surface-variant mb-4">Order reference</p>
      <p class="font-display-md text-headline-md mb-8"><?= e($o['order_code']) ?></p>
      <p class="font-body-md text-on-surface-variant mb-12">We have sent confirmation to <strong><?= e($o['email']) ?></strong>. Total: <strong><?= money($o['total']) ?></strong></p>
    <?php endif; ?>
    <div class="flex justify-center gap-4">
      <a href="<?= url('shop.php') ?>" class="btn-ghost">Continue Shopping</a>
      <?php if (is_logged_in()): ?><a href="<?= url('orders.php') ?>" class="btn-primary">View Orders</a><?php endif; ?>
    </div>
  </div>
</section>
<?php include 'includes/footer.php'; ?>
