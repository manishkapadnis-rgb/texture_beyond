<?php
require_once 'includes/functions.php';
$page_title = 'Texture & Beyond | Modern Indian Art & Decor';
$s = setting();
$collections = get_categories(true);
$featured = $conn->query("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id=p.category_id WHERE p.status=1 AND p.is_featured=1 ORDER BY p.id DESC LIMIT 8");
include 'includes/header.php';
?>

<?php include 'components/hero-slider.php'; ?>

<!-- About Teaser -->
<section class="py-section-gap px-margin-mobile md:px-margin-desktop bg-surface">
  <div class="max-w-container-max mx-auto grid grid-cols-1 md:grid-cols-12 items-center gap-16">
    <div class="md:col-span-6 gsap-fade">
      <div class="relative group overflow-hidden rounded-lg shadow-[0px_20px_40px_rgba(26,26,26,0.05)]">
        <img class="w-full h-[600px] object-cover transition-transform duration-1000 group-hover:scale-105" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAmNx85mb0x5Zvjh6I6LEQguLUvlua0zbBjOzo92uDEHCErh3JNOOsS84uCCRUJon9jwbmiT4RWuZd2NTRTnyCrSQWg9J_Zisn46-nzLb1j6mGY1AsLx57LhI28V6-05-dPllMPG4N910cbonv3rqiyt-gXo9SZGOX3FcUdwvclP_YqhcWtPF_D4NJ5ag6qMAyzXxyX8Ff3GnXlcD1I2GwIrbFPf13ZGf1aSGxTp7a4sXLHIoIGRc_ih63TEDdAgvIENhQRQj4Gk6k" alt="Artisan at work"/>
        <div class="absolute bottom-8 left-8 bg-surface-container px-6 py-3 rounded-full flex items-center gap-3">
          <span class="material-symbols-outlined text-secondary">verified</span>
          <span class="font-label-caps text-label-caps text-on-surface">Handmade in India</span>
        </div>
      </div>
    </div>
    <div class="md:col-span-6 gsap-fade">
      <span class="font-label-caps text-label-caps text-secondary mb-4 block">Our Philosophy</span>
      <h2 class="font-display-md text-headline-lg md:text-display-md mb-8">Every Wall Deserves Art. Every Heart Deserves Beauty.</h2>
      <p class="font-body-lg text-body-lg text-on-surface-variant mb-12 leading-relaxed">Texture &amp; Beyond was born from the desire to bring tactile emotions back into the modern home. Each canvas is a labor of love, individually sculpted to capture light and shadow in ever-changing ways.</p>
      <a class="inline-flex items-center gap-4 font-label-caps text-label-caps group" href="<?= url('contact.php') ?>">Discover Our Story <span class="w-12 h-[1px] bg-primary group-hover:w-20 transition-all duration-400"></span></a>
    </div>
  </div>
</section>

<!-- Collections -->
<section class="pb-section-gap bg-surface">
  <div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop">
    <div class="flex justify-between items-end mb-16 gsap-fade">
      <div>
        <span class="font-label-caps text-label-caps text-secondary mb-4 block">Curated Series</span>
        <h2 class="font-display-md text-headline-lg md:text-display-md">The Collections</h2>
      </div>
    </div>
    <div class="flex overflow-x-auto gap-gutter pb-12 custom-scrollbar snap-x">
      <?php while ($c = $collections->fetch_assoc()): ?>
        <a href="<?= url('shop.php?category=' . $c['slug']) ?>" class="min-w-[300px] md:min-w-[440px] snap-start group zoom-hover block">
          <div class="overflow-hidden mb-6 aspect-[4/5] bg-surface-container-high relative">
            <img class="w-full h-full object-cover" src="<?= e(category_image($c['image']) ?: 'https://lh3.googleusercontent.com/aida-public/AB6AXuBoDj3uPQt3tYbxcyJlucBCcvmTryeq217D7IVM-hjt2LGQMenX7b2O0pHsiuY6DgeTj4ec5W1mlfHFkIuTCe6e2UjCzqMIJRYCDtuPRgFI4yFTWxbywmFGG5ol1lC8fCifQ0OEZ1xBWHAnc_29-3-tpjL-GoAd3zONystPqcAqfW0PJ7RNULw-gOxONNRyetPxtv4YYzeXOA6n5bOkeWA1R362bPMZ8Q-3qnFTg5GQW6-ZWniuxFvzEvJuy3d3A29Gzw0IdUpiiWc') ?>" alt="<?= e($c['name']) ?>"/>
            <div class="absolute inset-0 bg-primary/0 group-hover:bg-primary/10 transition-all duration-400"></div>
          </div>
          <span class="font-label-caps text-label-caps text-secondary mb-2 block uppercase"><?= e($c['tagline']) ?></span>
          <h3 class="font-display-md text-headline-md mb-2"><?= e($c['name']) ?></h3>
          <p class="font-body-md text-on-surface-variant"><?= e($c['description']) ?></p>
        </a>
      <?php endwhile; ?>
    </div>
  </div>
</section>

<!-- Featured Products -->
<section class="pb-section-gap bg-surface">
  <div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop">
    <div class="text-center mb-16 gsap-fade">
      <span class="font-label-caps text-label-caps text-secondary mb-4 block">Signature Pieces</span>
      <h2 class="font-display-md text-headline-lg md:text-display-md">Featured Works</h2>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-gutter gsap-stagger">
      <?php while ($p = $featured->fetch_assoc()): include 'components/product-card.php'; endwhile; ?>
    </div>
  </div>
</section>

<!-- Parallax Quote -->
<section class="relative h-[614px] flex items-center justify-center parallax-bg" style="background-image:url('https://lh3.googleusercontent.com/aida-public/AB6AXuCP2Eg_5zQk02vRTA_mNtXjgqqME_iPNlKDql-QTwJO_2MJ2HG4Oh4akcF8rmKiDWtLnCn1p5fwbZ0pRG_ojZOwAQT_7RL5uhvstgy-piofO17y3AtKysvSez1-THFgNz0bskOYHtBjdWn_MBgukRxmNTSgJRj6CJMS7ko0-zTgBmNqk410znGdb0x00MtXKqpMB0DAyAjFXmtJVhvz0PkQycq0Sgh6zlNwO_9xiEp6z75QK093pJCcx1vCmmmRszY-5AinpKfBFY8')">
  <div class="absolute inset-0 bg-primary/40"></div>
  <div class="relative z-10 text-center px-margin-mobile">
    <h2 class="font-display-md text-headline-lg md:text-display-md text-white mb-8 max-w-4xl mx-auto italic">"Art is not what you see, but what you make others feel."</h2>
    <div class="w-16 h-[1px] bg-white/40 mx-auto"></div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
