<?php
/**
 * Reusable hero slider — dynamically rendered from `hero_banners` table.
 * Falls back gracefully if no banners are configured.
 */
$slides = get_hero_banners();
if (!$slides) return;
?>
<section class="hero-swiper">
  <div class="swiper" id="heroSwiper">
    <div class="swiper-wrapper">
      <?php foreach ($slides as $s):
        $img_desktop = hero_banner_url($s['image_desktop']);
        $img_mobile  = hero_banner_url($s['image_mobile'] ?: $s['image_desktop']);
      ?>
      <div class="swiper-slide">
        <picture>
          <source media="(max-width: 768px)" srcset="<?= e($img_mobile) ?>"/>
          <img src="<?= e($img_desktop) ?>" alt="<?= e($s['heading']) ?>" class="slide-img" loading="lazy"/>
        </picture>
        <div class="slide-overlay"></div>
        <div class="slide-content">
          <?php if ($s['eyebrow']): ?>
            <span class="font-label-caps text-label-caps tracking-[0.3em] uppercase block mb-6 text-white/90"><?= e($s['eyebrow']) ?></span>
          <?php endif; ?>
          <h1 class="font-display-lg text-[40px] md:text-display-lg text-white mb-8 leading-tight"><?= nl2br(e($s['heading'])) ?></h1>
          <?php if ($s['subheading']): ?>
            <p class="font-body-lg text-body-lg text-white/85 max-w-2xl mx-auto mb-12"><?= e($s['subheading']) ?></p>
          <?php endif; ?>
          <?php if ($s['button_text']): ?>
            <a href="<?= e(url($s['button_link'] ?: '#')) ?>" class="inline-block px-12 py-5 bg-white text-primary font-label-caps text-label-caps rounded-[4px] hover:bg-primary hover:text-on-primary transition-all duration-400">
              <?= e($s['button_text']) ?>
            </a>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php if (count($slides) > 1): ?>
      <div class="swiper-button-prev"></div>
      <div class="swiper-button-next"></div>
      <div class="swiper-pagination" style="bottom:32px"></div>
    <?php endif; ?>
  </div>

</section>

<script>
document.addEventListener('DOMContentLoaded', function(){
  function init(){
    if (typeof Swiper === 'undefined'){ return setTimeout(init, 100); }
    new Swiper('#heroSwiper', {
      loop: <?= count($slides) > 1 ? 'true' : 'false' ?>,
      effect: 'fade',
      fadeEffect: { crossFade: true },
      speed: 1200,
      autoplay: { delay: 5500, disableOnInteraction: false },
      pagination: { el: '.swiper-pagination', clickable: true },
      navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' }
    });
  }
  init();
});
</script>
