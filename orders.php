<?php
require_once 'includes/functions.php';
require_login();
$u = current_user();
$orders = $conn->query("SELECT * FROM orders WHERE user_id={$u['id']} ORDER BY id DESC");
$page_title = 'Order History | Texture & Beyond';
include 'includes/header.php';
?>
<section class="pt-40 pb-section-gap px-margin-mobile md:px-margin-desktop bg-surface min-h-[70vh]">
  <div class="max-w-container-max mx-auto">
    <div class="text-center mb-16">
      <span class="font-label-caps text-label-caps text-secondary mb-4 block">Your Journey</span>
      <h1 class="font-display-md text-headline-lg md:text-display-md">Order History</h1>
    </div>
    <div class="border-t border-outline-variant/30">
      <?php while ($o = $orders->fetch_assoc()):
        $items = $conn->query("SELECT * FROM order_items WHERE order_id={$o['id']}");
      ?>
        <div class="py-8 border-b border-outline-variant/30">
          <div class="flex flex-wrap justify-between items-start gap-4 mb-6">
            <div>
              <div class="font-label-caps text-label-caps text-secondary mb-2"><?= e($o['order_code']) ?></div>
              <div class="font-display-md text-headline-md"><?= money($o['total']) ?></div>
              <div class="font-body-md text-on-surface-variant"><?= date('M d, Y', strtotime($o['created_at'])) ?></div>
            </div>
            <span class="font-label-caps text-label-caps px-4 py-2 border border-primary"><?= strtoupper($o['status']) ?></span>
          </div>
          <div class="space-y-2 pl-4 border-l border-outline-variant/30">
            <?php while ($i = $items->fetch_assoc()): ?>
              <div class="flex justify-between font-body-md"><span><?= e($i['product_name']) ?> × <?= $i['quantity'] ?></span><span><?= money($i['subtotal']) ?></span></div>
            <?php endwhile; ?>
          </div>
        </div>
      <?php endwhile; ?>
      <?php if ($orders->num_rows === 0): ?><p class="py-20 text-center font-body-lg text-on-surface-variant">No orders yet. <a href="<?= url('shop.php') ?>" class="gold-underline">Start shopping →</a></p><?php endif; ?>
    </div>
  </div>
</section>
<?php include 'includes/footer.php'; ?>
