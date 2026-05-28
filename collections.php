<?php
require_once 'includes/functions.php';
$cats = get_categories();
$page_title = 'Collections | Texture & Beyond';
include 'includes/header.php';
?>
<section class="pt-40 pb-section-gap px-margin-mobile md:px-margin-desktop bg-surface">
  <div class="max-w-container-max mx-auto">
    <div class="text-center mb-16">
      <span class="font-label-caps text-label-caps text-secondary mb-4 block">Curated Series</span>
      <h1 class="font-display-md text-headline-lg md:text-display-md">The Collections</h1>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-gutter gsap-stagger">
      <?php while ($c = $cats->fetch_assoc()): ?>
        <a href="<?= url('shop.php?category=' . $c['slug']) ?>" class="group zoom-hover block">
          <div class="overflow-hidden aspect-[16/10] bg-surface-container-high relative">
            <img class="w-full h-full object-cover" src="<?= e(category_image($c['image']) ?: 'https://lh3.googleusercontent.com/aida-public/AB6AXuChuVVePNCej-HPpSIHeCZ5TmbXwuDMfc3ZN0kH7YaMWoE3z8NnMM89XdQBnyfWsdChHn1iM0VXeD9ugcScOf9Yurf4sMdG4NGaeM4WZPSRwETQJ2goqrGR6O732j83hnLoAMlXOBTdxEwM_WEzpwByHk2iUF1GGubHkTWHi40ElZDGcIi_sFLgDXQMKjeXRx5_btWRvMlkG8tY-Dg7eq_AhNACcCJQF_oSCdyY8qVxCz_uyhjgKUWzPq0BU8OksF2hH8j4JdbLkW8') ?>" alt="<?= e($c['name']) ?>"/>
            <div class="absolute inset-0 bg-primary/0 group-hover:bg-primary/20 transition-all duration-400"></div>
          </div>
          <div class="mt-6">
            <span class="font-label-caps text-label-caps text-secondary mb-2 block uppercase"><?= e($c['tagline']) ?></span>
            <h3 class="font-display-md text-headline-md mb-2"><?= e($c['name']) ?></h3>
            <p class="font-body-md text-on-surface-variant"><?= e($c['description']) ?></p>
          </div>
        </a>
      <?php endwhile; ?>
    </div>
  </div>
</section>
<?php include 'includes/footer.php'; ?>
