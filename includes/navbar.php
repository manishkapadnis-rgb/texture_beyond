<?php
if (!function_exists('e')) require_once __DIR__ . '/functions.php';
$cur = basename($_SERVER['PHP_SELF']);
$menu = get_menu('header');
if (!$menu) $menu = [
  ['label'=>'Shop','url'=>'shop.php'],['label'=>'Collections','url'=>'collections.php'],
  ['label'=>'Gallery','url'=>'gallery.php'],['label'=>'Blog','url'=>'blog.php'],
  ['label'=>'About','url'=>'about.php'],['label'=>'Contact','url'=>'contact.php']
];
$logo = logo_url();
?>
<nav class="fixed top-0 w-full z-50 glass-nav shadow-sm">
  <div class="flex justify-between items-center px-margin-mobile md:px-margin-desktop py-6 max-w-container-max mx-auto">
    <a href="<?= url('index.php') ?>" class="flex items-center gap-3">
      <?php if ($logo): ?>
        <img src="<?= e($logo) ?>" alt="<?= e(SITE_NAME) ?>" class="h-10"/>
      <?php else: ?>
        <span class="font-display-lg text-[20px] md:text-[26px] tracking-tighter text-primary"><?= e(SITE_NAME) ?></span>
      <?php endif; ?>
    </a>
    <div class="hidden md:flex items-center gap-10 font-label-caps text-label-caps">
      <?php foreach ($menu as $m): $active = ($cur === basename($m['url'])); ?>
        <a href="<?= url($m['url']) ?>"
           class="<?= $active ? 'text-secondary border-b border-secondary pb-1 font-bold' : 'text-on-surface' ?> gold-underline hover:text-secondary-fixed-dim transition-colors duration-400">
          <?= e($m['label']) ?>
        </a>
      <?php endforeach; ?>
    </div>
    <div class="flex items-center gap-5">
      <a href="<?= url('shop.php') ?>" class="material-symbols-outlined text-primary hover:opacity-80">search</a>
      <?php if (is_logged_in()): ?>
        <a href="<?= url('wishlist.php') ?>" class="relative material-symbols-outlined text-primary hover:opacity-80">favorite
          <?php if (wishlist_count()): ?><span class="absolute -top-2 -right-2 bg-secondary text-on-secondary text-[10px] w-4 h-4 rounded-full flex items-center justify-center font-bold"><?= wishlist_count() ?></span><?php endif; ?>
        </a>
        <a href="<?= url('my-account.php') ?>" class="material-symbols-outlined text-primary hover:opacity-80">person</a>
      <?php else: ?>
        <a href="<?= url('login.php') ?>" class="material-symbols-outlined text-primary hover:opacity-80">person_outline</a>
      <?php endif; ?>
      <a href="<?= url('cart.php') ?>" class="relative material-symbols-outlined text-primary hover:opacity-80">shopping_bag
        <?php if (cart_count() > 0): ?><span class="absolute -top-2 -right-2 bg-primary text-on-primary text-[10px] w-4 h-4 rounded-full flex items-center justify-center font-bold"><?= cart_count() ?></span><?php endif; ?>
      </a>
      <button class="md:hidden material-symbols-outlined text-primary" onclick="document.getElementById('mobile-menu').classList.toggle('hidden')">menu</button>
    </div>
  </div>
  <div id="mobile-menu" class="hidden md:hidden bg-background border-t border-outline-variant/30 px-margin-mobile py-6 flex flex-col gap-4 font-label-caps text-label-caps">
    <?php foreach ($menu as $m): ?><a href="<?= url($m['url']) ?>"><?= e($m['label']) ?></a><?php endforeach; ?>
    <?php if (is_logged_in()): ?>
      <a href="<?= url('wishlist.php') ?>">Wishlist</a><a href="<?= url('my-account.php') ?>">My Account</a><a href="<?= url('logout.php') ?>">Sign Out</a>
    <?php else: ?><a href="<?= url('login.php') ?>">Sign In</a><?php endif; ?>
  </div>
</nav>
