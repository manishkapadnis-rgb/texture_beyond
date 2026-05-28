<?php
/**
 * Storefront footer — 4-column luxury footer + newsletter + JS bundle.
 * Always included at the bottom of every storefront page.
 */
if (!function_exists('e')) require_once __DIR__ . '/functions.php';
$s = setting();
?>
<footer class="w-full mt-section-gap bg-surface-container-highest border-t border-outline-variant/30">
  <div class="grid grid-cols-1 md:grid-cols-4 gap-gutter px-margin-mobile md:px-margin-desktop py-20 max-w-container-max mx-auto">

    <div>
      <h2 class="font-display-md text-headline-md text-primary mb-8"><?= e(SITE_NAME) ?></h2>
      <p class="font-body-md text-on-surface-variant mb-6">Crafting modern Indian sensibilities for global homes.</p>
      <p class="font-body-md text-on-surface-variant mb-6"><?= e($s['footer_about'] ?? '') ?></p>
      <div class="flex gap-3">
        <?php foreach (['social_instagram'=>'photo_camera','social_facebook'=>'facebook','social_pinterest'=>'push_pin','social_youtube'=>'play_circle'] as $k=>$icon): if (!empty($s[$k])): ?>
          <a class="w-10 h-10 border border-outline-variant rounded-full flex items-center justify-center hover:bg-primary hover:text-on-primary transition-all" href="<?= e($s[$k]) ?>" target="_blank" rel="noopener">
            <span class="material-symbols-outlined text-[18px]"><?= $icon ?></span>
          </a>
        <?php endif; endforeach; ?>
      </div>
    </div>

    <div class="flex flex-col gap-4">
      <h4 class="font-label-caps text-label-caps text-primary uppercase mb-4">Quick Links</h4>
      <a class="font-body-md text-on-surface-variant hover:text-secondary-fixed-dim transition-colors" href="<?= url('shop.php') ?>">Shop All</a>
      <a class="font-body-md text-on-surface-variant hover:text-secondary-fixed-dim transition-colors" href="<?= url('shop.php?filter=bestseller') ?>">Best Sellers</a>
      <a class="font-body-md text-on-surface-variant hover:text-secondary-fixed-dim transition-colors" href="<?= url('shop.php?filter=new') ?>">New Arrivals</a>
      <a class="font-body-md text-on-surface-variant hover:text-secondary-fixed-dim transition-colors" href="<?= url('collections.php') ?>">Collections</a>
    </div>

    <div class="flex flex-col gap-4">
      <h4 class="font-label-caps text-label-caps text-primary uppercase mb-4">Customer Care</h4>
      <a class="font-body-md text-on-surface-variant hover:text-secondary-fixed-dim transition-colors" href="<?= url('contact.php') ?>">Contact</a>
      <a class="font-body-md text-on-surface-variant hover:text-secondary-fixed-dim transition-colors" href="<?= url('my-account.php') ?>">My Account</a>
      <a class="font-body-md text-on-surface-variant hover:text-secondary-fixed-dim transition-colors" href="<?= url('orders.php') ?>">Order History</a>
      <a class="font-body-md text-on-surface-variant hover:text-secondary-fixed-dim transition-colors" href="<?= url('gallery.php') ?>">Interior Gallery</a>
      <a class="font-body-md text-on-surface-variant hover:text-secondary-fixed-dim transition-colors" href="<?= url('page.php?slug=shipping') ?>">Shipping Policy</a>
      <a class="font-body-md text-on-surface-variant hover:text-secondary-fixed-dim transition-colors" href="<?= url('page.php?slug=refund') ?>">Refund Policy</a>
      <a class="font-body-md text-on-surface-variant hover:text-secondary-fixed-dim transition-colors" href="<?= url('page.php?slug=privacy') ?>">Privacy Policy</a>
    </div>

    <div class="flex flex-col gap-4">
      <h4 class="font-label-caps text-label-caps text-primary uppercase mb-4">Join Our Journey</h4>
      <p class="font-body-md text-on-surface-variant mb-4">Sign up for exclusive previews and stories behind our art.</p>
      <form action="<?= url('newsletter.php') ?>" method="post" class="flex gap-0 border-b border-primary">
        <input name="email" required type="email" placeholder="Email Address"
               class="bg-transparent border-none outline-none flex-grow py-3 font-body-md placeholder:text-outline/50 focus:ring-0"/>
        <button class="material-symbols-outlined py-3" aria-label="Subscribe">arrow_forward</button>
      </form>
    </div>
  </div>

  <div class="px-margin-mobile md:px-margin-desktop py-8 border-t border-outline-variant/10 flex flex-col md:flex-row justify-between items-center gap-4">
    <p class="font-body-md text-on-surface-variant text-[12px] uppercase tracking-widest">
      © <?= date('Y') ?> <?= e(SITE_NAME) ?>. All rights reserved.
    </p>
    <p class="font-body-md text-on-surface-variant text-[12px] uppercase tracking-widest">
      <?= e($s['phone'] ?? '') ?> · <?= e($s['email'] ?? '') ?>
    </p>
  </div>
</footer>

<script src="<?= ASSETS_URL ?>/js/main.js"></script>
</body>
</html>
