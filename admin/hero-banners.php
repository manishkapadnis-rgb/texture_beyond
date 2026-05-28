<?php
$page_title = 'Hero Banners';
require_once __DIR__ . '/includes/header.php';
$action = $_GET['action'] ?? ''; $id = (int)($_GET['id'] ?? 0);

if ($action === 'delete' && $id){
    db_exec("DELETE FROM hero_banners WHERE id=?", [$id]);
    flash('success','Banner deleted.'); redirect(admin_url('hero-banners.php'));
}
if ($action === 'toggle' && $id){
    db_exec("UPDATE hero_banners SET status=1-status WHERE id=?", [$id]);
    redirect(admin_url('hero-banners.php'));
}
if ($action === 'reorder' && $_SERVER['REQUEST_METHOD']==='POST'){
    foreach (($_POST['order'] ?? []) as $i => $bid){
        db_exec("UPDATE hero_banners SET sort_order=? WHERE id=?", [(int)$i+1, (int)$bid]);
    }
    header('Content-Type: application/json'); echo json_encode(['ok'=>true]); exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$action){
    $desktop = upload_image('image_desktop', 'banners', $_POST['existing_desktop'] ?? null);
    $mobile  = upload_image('image_mobile',  'banners', $_POST['existing_mobile']  ?? null);
    $data = [$_POST['heading'], $_POST['subheading'], $_POST['eyebrow'], $desktop, $mobile,
             $_POST['button_text'], $_POST['button_link'], (int)$_POST['sort_order'], isset($_POST['status'])?1:0];
    if ($id){
        db_exec("UPDATE hero_banners SET heading=?,subheading=?,eyebrow=?,image_desktop=?,image_mobile=?,button_text=?,button_link=?,sort_order=?,status=? WHERE id=?", array_merge($data, [$id]));
    } else {
        db_exec("INSERT INTO hero_banners (heading,subheading,eyebrow,image_desktop,image_mobile,button_text,button_link,sort_order,status) VALUES (?,?,?,?,?,?,?,?,?)", $data);
    }
    flash('success','Banner saved.'); redirect(admin_url('hero-banners.php'));
}

if ($action === 'edit' || $action === 'new'):
$b = $id ? db_one("SELECT * FROM hero_banners WHERE id=?", [$id]) : [];
?>
<h1 class="serif text-4xl mb-8"><?= $id ? 'Edit' : 'New' ?> Hero Banner</h1>
<form method="post" enctype="multipart/form-data" class="bg-white p-10 grid grid-cols-1 md:grid-cols-2 gap-6 max-w-5xl">

  <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-3 gap-6">
    <input class="input" name="eyebrow" placeholder="Eyebrow Label (e.g. Exquisite Craftsmanship)" value="<?= e($b['eyebrow'] ?? '') ?>"/>
    <input class="input" name="sort_order" type="number" placeholder="Sort Order" value="<?= e($b['sort_order'] ?? 0) ?>"/>
    <label class="flex items-center gap-2"><input type="checkbox" name="status" <?= !isset($b['status']) || $b['status'] ? 'checked' : '' ?>/> Active</label>
  </div>

  <input class="input md:col-span-2" name="heading" required placeholder="Heading" value="<?= e($b['heading'] ?? '') ?>"/>
  <textarea class="input md:col-span-2" name="subheading" rows="2" placeholder="Subheading"><?= e($b['subheading'] ?? '') ?></textarea>

  <input class="input" name="button_text" placeholder="Button Text (e.g. Explore Collection)" value="<?= e($b['button_text'] ?? '') ?>"/>
  <input class="input" name="button_link" placeholder="Button Link (e.g. shop.php)" value="<?= e($b['button_link'] ?? '') ?>"/>

  <div>
    <label class="label-caps block mb-2">Desktop Banner (1920×900 recommended)</label>
    <input type="file" name="image_desktop" accept="image/*"/>
    <input type="hidden" name="existing_desktop" value="<?= e($b['image_desktop'] ?? '') ?>"/>
    <?php if (!empty($b['image_desktop'])): ?>
      <img src="<?= e(str_starts_with($b['image_desktop'],'http') ? $b['image_desktop'] : admin_upload_url('banners', $b['image_desktop'])) ?>" class="w-full mt-3 max-h-32 object-cover"/>
    <?php endif; ?>
  </div>
  <div>
    <label class="label-caps block mb-2">Mobile Banner (750×900 recommended)</label>
    <input type="file" name="image_mobile" accept="image/*"/>
    <input type="hidden" name="existing_mobile" value="<?= e($b['image_mobile'] ?? '') ?>"/>
    <?php if (!empty($b['image_mobile'])): ?>
      <img src="<?= e(str_starts_with($b['image_mobile'],'http') ? $b['image_mobile'] : admin_upload_url('banners', $b['image_mobile'])) ?>" class="w-full mt-3 max-h-32 object-cover"/>
    <?php endif; ?>
  </div>

  <div class="md:col-span-2 flex gap-4 mt-4">
    <button class="btn">Save Banner</button>
    <a href="<?= admin_url('hero-banners.php') ?>" class="btn-ghost">Cancel</a>
  </div>
</form>

<?php else:
$rows = db_all("SELECT * FROM hero_banners ORDER BY sort_order, id");
?>
<div class="flex justify-between items-center mb-8">
  <div>
    <h1 class="serif text-4xl">Hero Banner Slider</h1>
    <p class="text-[#666] mt-2">Drag rows to reorder · Toggle active · Edit content / images</p>
  </div>
  <div class="flex gap-3">
    <a class="btn-ghost" href="<?= url('index.php') ?>" target="_blank">Preview Site</a>
    <a class="btn" href="?action=new">+ New Banner</a>
  </div>
</div>

<table id="bannerTable">
  <thead><tr><th width="30"></th><th>Preview</th><th>Heading</th><th>Button</th><th>Sort</th><th>Status</th><th></th></tr></thead>
  <tbody>
    <?php foreach ($rows as $r):
      $img = str_starts_with($r['image_desktop'],'http') ? $r['image_desktop'] : admin_upload_url('banners', $r['image_desktop']);
    ?>
    <tr data-id="<?= $r['id'] ?>" style="cursor:move">
      <td class="text-[#999]"><span class="material-symbols-outlined">drag_indicator</span></td>
      <td><img src="<?= e($img) ?>" class="w-28 h-16 object-cover"/></td>
      <td>
        <div class="font-medium"><?= e($r['heading']) ?></div>
        <?php if ($r['eyebrow']): ?><small class="text-[#666]"><?= e($r['eyebrow']) ?></small><?php endif; ?>
      </td>
      <td><?= e($r['button_text']) ?><?php if ($r['button_link']): ?><br><small class="text-[#666]"><?= e($r['button_link']) ?></small><?php endif; ?></td>
      <td><?= $r['sort_order'] ?></td>
      <td>
        <a href="?action=toggle&id=<?= $r['id'] ?>" class="px-3 py-1 text-[11px] <?= $r['status']?'bg-green-100 text-green-700':'bg-gray-200 text-gray-600' ?>">
          <?= $r['status'] ? 'ACTIVE' : 'HIDDEN' ?>
        </a>
      </td>
      <td>
        <a class="btn-ghost" href="?action=edit&id=<?= $r['id'] ?>">Edit</a>
        <a class="btn-ghost" onclick="return confirm('Delete this banner?')" href="?action=delete&id=<?= $r['id'] ?>">Delete</a>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="7" class="text-center py-8 text-[#666]">No banners yet. <a href="?action=new" class="underline">Add your first banner →</a></td></tr><?php endif; ?>
  </tbody>
</table>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
const tbody = document.querySelector('#bannerTable tbody');
if (tbody && window.Sortable){
  Sortable.create(tbody, {
    animation: 200,
    handle: 'td:first-child',
    onEnd: function(){
      const order = [...tbody.querySelectorAll('tr[data-id]')].map(r => r.dataset.id);
      fetch('?action=reorder', {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body: order.map((id,i) => `order[${i}]=${id}`).join('&')
      });
    }
  });
}
</script>
<?php endif; include __DIR__ . '/includes/footer.php'; ?>
