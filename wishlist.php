<?php
require_once 'includes/functions.php';
require_login();
$u = current_user();
if (($_GET['remove'] ?? 0) > 0){
    db_exec("DELETE FROM wishlist WHERE user_id=? AND product_id=?", [$u['id'], (int)$_GET['remove']]);
    flash('success','Removed from wishlist.');
    redirect(url('wishlist.php'));
}
$items = db_all("SELECT p.*, c.name AS category_name FROM wishlist w JOIN products p ON p.id=w.product_id LEFT JOIN categories c ON c.id=p.category_id WHERE w.user_id=? ORDER BY w.id DESC", [$u['id']]);
$page_title = 'Wishlist | ' . SITE_NAME;
include 'includes/header.php';
?>
<section class="pt-40 pb-section-gap px-margin-mobile md:px-margin-desktop bg-surface min-h-[70vh]">
  <div class="max-w-container-max mx-auto">
    <div class="text-center mb-16">
      <span class="font-label-caps text-label-caps text-secondary mb-4 block">Saved For Later</span>
      <h1 class="font-display-md text-headline-lg md:text-display-md">Your Wishlist</h1>
    </div>
    <?php if (!$items): ?>
      <p class="text-center font-body-lg text-on-surface-variant py-20">No saved pieces yet. <a href="<?= url('shop.php') ?>" class="gold-underline">Browse the gallery →</a></p>
    <?php else: ?>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-gutter gsap-stagger">
        <?php foreach ($items as $p): ?>
          <div class="group zoom-hover">
            <a href="<?= url('product.php?slug=' . $p['slug']) ?>" class="block overflow-hidden mb-4 aspect-[4/5] bg-surface-container-high">
              <img class="w-full h-full object-cover" src="<?= e(product_image($p['image'])) ?>" alt="<?= e($p['name']) ?>"/>
            </a>
            <span class="font-label-caps text-label-caps text-secondary uppercase"><?= e($p['category_name']) ?></span>
            <h3 class="font-display-md text-[20px] mb-2"><?= e($p['name']) ?></h3>
            <div class="flex justify-between items-center">
              <span class="font-body-md font-semibold"><?= money($p['sale_price'] ?: $p['price']) ?></span>
              <a href="?remove=<?= $p['id'] ?>" class="text-xs uppercase tracking-widest text-on-surface-variant hover:text-error">Remove</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php include 'includes/footer.php'; ?>
