<?php
require_once 'includes/functions.php';
$pg = get_page('about');
$page_title = 'About | ' . SITE_NAME;
include 'includes/header.php';
?>
<section class="relative h-[60vh] min-h-[400px] flex items-center justify-center overflow-hidden hero-parallax">
  <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuAmNx85mb0x5Zvjh6I6LEQguLUvlua0zbBjOzo92uDEHCErh3JNOOsS84uCCRUJon9jwbmiT4RWuZd2NTRTnyCrSQWg9J_Zisn46-nzLb1j6mGY1AsLx57LhI28V6-05-dPllMPG4N910cbonv3rqiyt-gXo9SZGOX3FcUdwvclP_YqhcWtPF_D4NJ5ag6qMAyzXxyX8Ff3GnXlcD1I2GwIrbFPf13ZGf1aSGxTp7a4sXLHIoIGRc_ih63TEDdAgvIENhQRQj4Gk6k" class="absolute inset-0 w-full h-full object-cover"/>
  <div class="absolute inset-0 bg-black/40"></div>
  <div class="relative z-10 text-center text-white hero-anim">
    <span class="font-label-caps text-label-caps tracking-[0.3em] uppercase block mb-6 text-white/90">Our Story</span>
    <h1 class="font-display-md text-headline-lg md:text-display-md"><?= e($pg['title'] ?? 'About') ?></h1>
  </div>
</section>
<section class="py-section-gap px-margin-mobile md:px-margin-desktop bg-surface">
  <div class="max-w-3xl mx-auto gsap-fade font-body-lg text-body-lg text-on-surface-variant leading-relaxed">
    <?= $pg['content'] ?? '' ?>
  </div>
</section>
<?php
$ts = get_testimonials();
if ($ts): ?>
<section class="pb-section-gap bg-surface">
  <div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop">
    <div class="text-center mb-16 gsap-fade">
      <span class="font-label-caps text-label-caps text-secondary mb-4 block">Whispers</span>
      <h2 class="font-display-md text-headline-lg md:text-display-md">From Our Patrons</h2>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-gutter gsap-stagger">
      <?php foreach ($ts as $t): ?>
        <blockquote class="bg-surface-container-low p-10">
          <p class="font-display-md text-[20px] italic mb-6">"<?= e($t['quote']) ?>"</p>
          <div class="font-label-caps text-label-caps text-secondary"><?= e($t['name']) ?></div>
          <div class="font-body-md text-on-surface-variant text-sm"><?= e($t['role']) ?></div>
        </blockquote>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; include 'includes/footer.php'; ?>
