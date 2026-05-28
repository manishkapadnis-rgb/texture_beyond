<?php
// Usage: $p = product row; include __DIR__ . '/../components/product-card.php';
?>
<div class="tb-card group flex flex-col h-full rounded-xl sm:rounded-2xl border border-outline-variant/20 bg-surface-container-lowest p-3 sm:p-4 shadow-sm hover:shadow-xl transition-all duration-400">
  <a href="<?= url('product.php?slug=' . $p['slug']) ?>" class="group zoom-hover block flex-1 flex flex-col">
    <div class="overflow-hidden mb-3 sm:mb-4 aspect-[4/5] bg-surface-container-high relative rounded-lg sm:rounded-xl">
      <img loading="lazy" class="w-full h-full object-cover" src="<?= e(product_image($p['image'] ?? '')) ?>" alt="<?= e($p['name'] ?? '') ?>"/>
      <?php if (!empty($p['is_new'])): ?><span class="absolute top-2 sm:top-4 left-2 sm:left-4 bg-primary text-on-primary text-[9px] sm:text-[10px] font-bold tracking-widest px-2 sm:px-3 py-1">NEW</span><?php endif; ?>
      <?php if (!empty($p['sale_price'])): ?><span class="absolute top-2 sm:top-4 right-2 sm:right-4 bg-secondary text-on-secondary text-[9px] sm:text-[10px] font-bold tracking-widest px-2 sm:px-3 py-1">SALE</span><?php endif; ?>
    </div>
    <?php if (!empty($p['category_name'])): ?><span class="font-label-caps text-[10px] sm:text-label-caps text-secondary mb-1 block uppercase tracking-widest"><?= e($p['category_name']) ?></span><?php endif; ?>
    <h3 class="font-display-md text-[15px] sm:text-[18px] md:text-[20px] leading-snug mb-2 line-clamp-2"><?= e($p['name'] ?? '') ?></h3>
    <div class="flex items-baseline flex-wrap gap-x-2 gap-y-1 mb-3 sm:mb-4 mt-auto">
      <?php if (!empty($p['sale_price'])): ?>
        <span class="font-body-md font-semibold text-[14px] sm:text-[15px]"><?= money($p['sale_price']) ?></span>
        <span class="font-body-md text-on-surface-variant line-through text-[12px] sm:text-[13px]"><?= money($p['price'] ?? 0) ?></span>
      <?php else: ?>
        <span class="font-body-md font-semibold text-[14px] sm:text-[15px]"><?= money($p['price'] ?? 0) ?></span>
      <?php endif; ?>
    </div>
  </a>
  <form action="<?= url('cart-action.php') ?>" method="post" class="add-to-cart-form flex items-center justify-between gap-2 sm:gap-3 mt-1" data-product-id="<?= (int)$p['id'] ?>">
    <input type="hidden" name="action" value="add"/>
    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>"/>
    <input type="hidden" name="qty" value="1"/>
    <button type="submit" class="btn-primary text-[10px] sm:text-[11px] px-3 sm:px-4 py-2.5 sm:py-3 flex-1 whitespace-nowrap min-h-[40px]">Add to cart</button>
    <a href="<?= url('product.php?slug=' . $p['slug']) ?>" class="font-label-caps text-[10px] sm:text-label-caps hover:text-secondary whitespace-nowrap shrink-0">Details</a>
  </form>
</div>
