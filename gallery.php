<?php
require_once 'includes/functions.php';
$page_title = 'Interior Gallery | Texture & Beyond';
$products = $conn->query("SELECT * FROM products WHERE status=1 ORDER BY id DESC LIMIT 12");
include 'includes/header.php';
?>
<section class="pt-40 pb-20 px-margin-mobile md:px-margin-desktop bg-surface">
  <div class="max-w-container-max mx-auto text-center">
    <span class="font-label-caps text-label-caps text-secondary mb-4 block">In Spaces</span>
    <h1 class="font-display-md text-headline-lg md:text-display-md">Interior Gallery</h1>
    <p class="font-body-lg text-body-lg text-on-surface-variant max-w-2xl mx-auto mt-6">A curated look at our pieces in real homes — where art meets life.</p>
  </div>
</section>
<section class="pb-section-gap px-margin-mobile md:px-margin-desktop bg-surface">
  <div class="max-w-container-max mx-auto columns-2 md:columns-3 gap-gutter space-y-gutter">
    <?php $i=0; while ($p = $products->fetch_assoc()): $i++; $aspect = $i%3==0?'aspect-[3/4]':($i%2==0?'aspect-square':'aspect-[4/5]'); ?>
      <a href="<?= url('product.php?slug=' . $p['slug']) ?>" class="block break-inside-avoid zoom-hover overflow-hidden <?= $aspect ?> bg-surface-container-high">
        <img class="w-full h-full object-cover" src="<?= e(product_image($p['image'])) ?>" alt="<?= e($p['name']) ?>"/>
      </a>
    <?php endwhile; ?>
  </div>
</section>
<?php include 'includes/footer.php'; ?>
