<?php
require_once 'includes/functions.php';
$slug = $_GET['slug'] ?? '';
$pg = get_page($slug);
if (!$pg){ http_response_code(404); $pg = ['title'=>'Page Not Found','content'=>'<p>This page does not exist.</p>']; }
$page_title = ($pg['meta_title'] ?? $pg['title']) . ' | ' . SITE_NAME;
$meta_description = $pg['meta_description'] ?? '';
include 'includes/header.php';
?>
<section class="pt-40 pb-section-gap px-margin-mobile md:px-margin-desktop bg-surface">
  <div class="max-w-3xl mx-auto gsap-fade">
    <div class="text-center mb-12">
      <span class="font-label-caps text-label-caps text-secondary mb-4 block">Information</span>
      <h1 class="font-display-md text-headline-lg md:text-display-md"><?= e($pg['title']) ?></h1>
    </div>
    <div class="prose max-w-none font-body-lg text-body-lg text-on-surface-variant leading-relaxed">
      <?= $pg['content'] ?>
    </div>
  </div>
</section>
<?php include 'includes/footer.php'; ?>
