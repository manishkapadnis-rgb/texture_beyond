<?php
// Usage: $p = product row; include __DIR__ . '/../components/product-card.php';
?>
<a href="<?= url('product.php?slug=' . $p['slug']) ?>" class="group zoom-hover block">
  <div class="overflow-hidden mb-4 aspect-[4/5] bg-surface-container-high relative">
    <img class="w-full h-full object-cover" src="<?= e(product_image($p['image'])) ?>" alt="<?= e($p['name']) ?>"/>
    <?php if (!empty($p['is_new'])): ?><span class="absolute top-4 left-4 bg-primary text-on-primary text-[10px] font-bold tracking-widest px-3 py-1">NEW</span><?php endif; ?>
    <?php if (!empty($p['sale_price'])): ?><span class="absolute top-4 right-4 bg-secondary text-on-secondary text-[10px] font-bold tracking-widest px-3 py-1">SALE</span><?php endif; ?>
  </div>
  <?php if (!empty($p['category_name'])): ?><span class="font-label-caps text-label-caps text-secondary mb-1 block uppercase"><?= e($p['category_name']) ?></span><?php endif; ?>
  <h3 class="font-display-md text-[20px] mb-2"><?= e($p['name']) ?></h3>
  <div class="flex items-center gap-3">
    <?php if (!empty($p['sale_price'])): ?>
      <span class="font-body-md font-semibold"><?= money($p['sale_price']) ?></span>
      <span class="font-body-md text-on-surface-variant line-through"><?= money($p['price']) ?></span>
    <?php else: ?>
      <span class="font-body-md font-semibold"><?= money($p['price']) ?></span>
    <?php endif; ?>
  </div>
</a>
