<?php
$page_title = 'SEO Settings';
require_once __DIR__ . '/includes/header.php';
if ($_SERVER['REQUEST_METHOD']==='POST'){
    db_exec("UPDATE settings SET seo_home_title=?, seo_home_description=?, seo_keywords=? WHERE id=1",
      [$_POST['seo_home_title'],$_POST['seo_home_description'],$_POST['seo_keywords']]);
    flash('success','SEO saved.'); redirect(admin_url('seo.php'));
}
$s = setting();
?>
<h1 class="serif text-4xl mb-8">SEO Settings</h1>
<form method="post" class="bg-white p-10 grid grid-cols-1 gap-6 max-w-3xl">
  <div><label class="label-caps block mb-2">Home Page Meta Title</label>
    <input class="input" name="seo_home_title" value="<?= e($s['seo_home_title']) ?>"/></div>
  <div><label class="label-caps block mb-2">Home Page Meta Description</label>
    <textarea class="input" name="seo_home_description" rows="3"><?= e($s['seo_home_description']) ?></textarea></div>
  <div><label class="label-caps block mb-2">Meta Keywords</label>
    <input class="input" name="seo_keywords" value="<?= e($s['seo_keywords']) ?>"/></div>
  <div><button class="btn">Save</button></div>
</form>
<p class="text-sm text-[#666] mt-6">Per-product / per-blog SEO fields live on their own edit pages.</p>
<?php include __DIR__ . '/includes/footer.php'; ?>
