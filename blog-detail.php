<?php
require_once 'includes/functions.php';
$slug = $_GET['slug'] ?? '';
$b = db_one("SELECT * FROM blogs WHERE slug=? AND status=1", [$slug]);
if (!$b){ http_response_code(404); echo 'Not found'; exit; }
$page_title = ($b['meta_title'] ?: $b['title']) . ' | ' . SITE_NAME;
$meta_description = $b['meta_description'] ?: $b['excerpt'];
include 'includes/header.php';
?>
<article class="pt-40 pb-section-gap px-margin-mobile md:px-margin-desktop bg-surface">
  <div class="max-w-3xl mx-auto">
    <div class="text-center mb-12 gsap-fade">
      <span class="font-label-caps text-label-caps text-secondary mb-4 block"><?= date('M d, Y', strtotime($b['created_at'])) ?> · <?= e($b['author']) ?></span>
      <h1 class="font-display-md text-headline-lg md:text-display-md"><?= e($b['title']) ?></h1>
    </div>
    <?php if ($b['image']): ?>
      <img src="<?= e(UPLOAD_URL.'/blog/'.$b['image']) ?>" class="w-full aspect-[16/9] object-cover mb-12"/>
    <?php endif; ?>
    <div class="font-body-lg text-body-lg text-on-surface-variant leading-relaxed prose max-w-none">
      <?= $b['content'] ?>
    </div>
    <a href="<?= url('blog.php') ?>" class="inline-block mt-12 gold-underline font-label-caps text-label-caps">← Back to Journal</a>
  </div>
</article>
<?php include 'includes/footer.php'; ?>
