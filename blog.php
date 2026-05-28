<?php
require_once 'includes/functions.php';
$blogs = db_all("SELECT * FROM blogs WHERE status=1 ORDER BY id DESC");
$page_title = 'Journal | ' . SITE_NAME;
include 'includes/header.php';
?>
<section class="pt-40 pb-section-gap px-margin-mobile md:px-margin-desktop bg-surface">
  <div class="max-w-container-max mx-auto">
    <div class="text-center mb-16">
      <span class="font-label-caps text-label-caps text-secondary mb-4 block">Journal</span>
      <h1 class="font-display-md text-headline-lg md:text-display-md">Stories &amp; Notes</h1>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-gutter gsap-stagger">
      <?php foreach ($blogs as $b): ?>
        <a href="<?= url('blog-detail.php?slug=' . $b['slug']) ?>" class="group block">
          <div class="overflow-hidden aspect-[4/3] bg-surface-container-high mb-6 zoom-hover">
            <img class="w-full h-full object-cover" src="<?= e($b['image'] ? UPLOAD_URL.'/blog/'.$b['image'] : 'https://lh3.googleusercontent.com/aida-public/AB6AXuChuVVePNCej-HPpSIHeCZ5TmbXwuDMfc3ZN0kH7YaMWoE3z8NnMM89XdQBnyfWsdChHn1iM0VXeD9ugcScOf9Yurf4sMdG4NGaeM4WZPSRwETQJ2goqrGR6O732j83hnLoAMlXOBTdxEwM_WEzpwByHk2iUF1GGubHkTWHi40ElZDGcIi_sFLgDXQMKjeXRx5_btWRvMlkG8tY-Dg7eq_AhNACcCJQF_oSCdyY8qVxCz_uyhjgKUWzPq0BU8OksF2hH8j4JdbLkW8') ?>"/>
          </div>
          <span class="font-label-caps text-label-caps text-secondary uppercase"><?= date('M d, Y', strtotime($b['created_at'])) ?></span>
          <h3 class="font-display-md text-headline-md my-3"><?= e($b['title']) ?></h3>
          <p class="font-body-md text-on-surface-variant"><?= e($b['excerpt']) ?></p>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php include 'includes/footer.php'; ?>
