<?php
$page_title = 'Settings';
require_once __DIR__ . '/includes/header.php';
if ($_SERVER['REQUEST_METHOD']==='POST'){
    $logo    = upload_image('logo','site',    $_POST['existing_logo']    ?? null);
    $favicon = upload_image('favicon','site', $_POST['existing_favicon'] ?? null);
    $hero    = upload_image('hero_image','site', $_POST['existing_hero']  ?? null);
    db_exec("UPDATE settings SET
      site_name=?, site_tagline=?, logo=?, favicon=?, email=?, phone=?, address=?, footer_about=?,
      hero_title=?, hero_subtitle=?, hero_image=?, shipping_charge=?, free_shipping_above=?,
      social_facebook=?, social_instagram=?, social_pinterest=?, social_youtube=?,
      razorpay_key=?, razorpay_secret=? WHERE id=1",
      [$_POST['site_name'],$_POST['site_tagline'],$logo,$favicon,$_POST['email'],$_POST['phone'],$_POST['address'],$_POST['footer_about'],
       $_POST['hero_title'],$_POST['hero_subtitle'],$hero,$_POST['shipping_charge'],$_POST['free_shipping_above'],
       $_POST['social_facebook'],$_POST['social_instagram'],$_POST['social_pinterest'],$_POST['social_youtube'],
       $_POST['razorpay_key'],$_POST['razorpay_secret']]);
    flash('success','Settings saved.'); redirect(admin_url('settings.php'));
}
$s = setting();
?>
<h1 class="serif text-4xl mb-8">Website Settings</h1>
<form method="post" enctype="multipart/form-data" class="space-y-8 max-w-5xl">

  <fieldset class="bg-white p-8"><legend class="serif text-2xl px-2">Brand</legend>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
      <input class="input" name="site_name" placeholder="Site Name" value="<?= e($s['site_name']) ?>"/>
      <input class="input" name="site_tagline" placeholder="Tagline" value="<?= e($s['site_tagline']) ?>"/>
      <div><label class="label-caps block mb-2">Logo</label><input type="file" name="logo"/><input type="hidden" name="existing_logo" value="<?= e($s['logo']) ?>"/>
        <?php if ($s['logo']): ?><img class="h-12 mt-2" src="<?= e(admin_upload_url('site',$s['logo'])) ?>"/><?php endif; ?></div>
      <div><label class="label-caps block mb-2">Favicon</label><input type="file" name="favicon"/><input type="hidden" name="existing_favicon" value="<?= e($s['favicon']) ?>"/></div>
    </div>
  </fieldset>

  <fieldset class="bg-white p-8"><legend class="serif text-2xl px-2">Contact</legend>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
      <input class="input" name="email" placeholder="Email" value="<?= e($s['email']) ?>"/>
      <input class="input" name="phone" placeholder="Phone" value="<?= e($s['phone']) ?>"/>
      <textarea class="input md:col-span-2" name="address" rows="2" placeholder="Address"><?= e($s['address']) ?></textarea>
      <textarea class="input md:col-span-2" name="footer_about" rows="2" placeholder="Footer About Text"><?= e($s['footer_about']) ?></textarea>
    </div>
  </fieldset>

  <fieldset class="bg-white p-8"><legend class="serif text-2xl px-2">Homepage Hero</legend>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
      <input class="input md:col-span-2" name="hero_title" placeholder="Hero Title" value="<?= e($s['hero_title']) ?>"/>
      <textarea class="input md:col-span-2" name="hero_subtitle" rows="2" placeholder="Hero Subtitle"><?= e($s['hero_subtitle']) ?></textarea>
      <div><label class="label-caps block mb-2">Hero Image</label><input type="file" name="hero_image"/><input type="hidden" name="existing_hero" value="<?= e($s['hero_image']) ?>"/></div>
    </div>
  </fieldset>

  <fieldset class="bg-white p-8"><legend class="serif text-2xl px-2">Social</legend>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
      <input class="input" name="social_instagram" placeholder="Instagram URL" value="<?= e($s['social_instagram']) ?>"/>
      <input class="input" name="social_facebook"  placeholder="Facebook URL"  value="<?= e($s['social_facebook']) ?>"/>
      <input class="input" name="social_pinterest" placeholder="Pinterest URL" value="<?= e($s['social_pinterest']) ?>"/>
      <input class="input" name="social_youtube"   placeholder="YouTube URL"   value="<?= e($s['social_youtube']) ?>"/>
    </div>
  </fieldset>

  <fieldset class="bg-white p-8"><legend class="serif text-2xl px-2">Commerce</legend>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
      <input class="input" type="number" step="0.01" name="shipping_charge" placeholder="Shipping Charge" value="<?= e($s['shipping_charge']) ?>"/>
      <input class="input" type="number" step="0.01" name="free_shipping_above" placeholder="Free Shipping Above" value="<?= e($s['free_shipping_above']) ?>"/>
      <input class="input" name="razorpay_key" placeholder="Razorpay Key ID" value="<?= e($s['razorpay_key']) ?>"/>
      <input class="input" name="razorpay_secret" placeholder="Razorpay Secret" value="<?= e($s['razorpay_secret']) ?>"/>
    </div>
  </fieldset>

  <button class="btn">Save All Settings</button>
</form>
<?php include __DIR__ . '/includes/footer.php'; ?>
