<?php
$page_title = 'Banners';
require_once __DIR__ . '/includes/header.php';
$action = $_GET['action'] ?? ''; $id = (int)($_GET['id'] ?? 0);
if ($action==='delete' && $id){ db_exec("DELETE FROM banners WHERE id=?",[$id]); flash('success','Deleted.'); redirect(admin_url('banners.php')); }
if ($_SERVER['REQUEST_METHOD']==='POST'){
    $img = upload_image('image','banners', $_POST['existing_image'] ?? null);
    $data = [$_POST['position'],$_POST['title'],$_POST['subtitle'],$img,$_POST['link'],$_POST['button_text'],(int)$_POST['sort_order'],isset($_POST['status'])?1:0];
    if ($id){ db_exec("UPDATE banners SET position=?,title=?,subtitle=?,image=?,link=?,button_text=?,sort_order=?,status=? WHERE id=?", array_merge($data,[$id])); }
    else { db_exec("INSERT INTO banners (position,title,subtitle,image,link,button_text,sort_order,status) VALUES (?,?,?,?,?,?,?,?)", $data); }
    flash('success','Saved.'); redirect(admin_url('banners.php'));
}
if ($action==='edit'||$action==='new'):
$b = $id ? db_one("SELECT * FROM banners WHERE id=?",[$id]) : [];
?>
<h1 class="serif text-4xl mb-8"><?= $id?'Edit Banner':'New Banner' ?></h1>
<form method="post" enctype="multipart/form-data" class="bg-white p-10 grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl">
  <select class="input" name="position">
    <?php foreach (['hero','strip','promo','quote'] as $p): ?><option value="<?= $p ?>" <?= ($b['position']??'')==$p?'selected':'' ?>><?= ucfirst($p) ?></option><?php endforeach; ?>
  </select>
  <input class="input" name="sort_order" type="number" placeholder="Sort Order" value="<?= e($b['sort_order']??0) ?>"/>
  <input class="input md:col-span-2" name="title" placeholder="Title" value="<?= e($b['title']??'') ?>"/>
  <textarea class="input md:col-span-2" name="subtitle" rows="2" placeholder="Subtitle"><?= e($b['subtitle']??'') ?></textarea>
  <input class="input" name="link" placeholder="Button Link" value="<?= e($b['link']??'') ?>"/>
  <input class="input" name="button_text" placeholder="Button Text" value="<?= e($b['button_text']??'') ?>"/>
  <div class="md:col-span-2"><label class="label-caps block mb-2">Image</label>
    <input type="file" name="image"/><input type="hidden" name="existing_image" value="<?= e($b['image']??'') ?>"/>
    <?php if (!empty($b['image'])): ?><img src="<?= e(admin_upload_url('banners',$b['image'])) ?>" class="w-48 mt-3"/><?php endif; ?>
  </div>
  <label class="flex items-center gap-2"><input type="checkbox" name="status" <?= !isset($b['status'])||$b['status']?'checked':'' ?>/> Active</label>
  <div class="md:col-span-2"><button class="btn">Save</button> <a class="btn-ghost" href="<?= admin_url('banners.php') ?>">Cancel</a></div>
</form>
<?php else:
$rows = db_all("SELECT * FROM banners ORDER BY position, sort_order");
?>
<div class="flex justify-between mb-8"><h1 class="serif text-4xl">Banners</h1><a class="btn" href="?action=new">+ New</a></div>
<table><thead><tr><th></th><th>Position</th><th>Title</th><th>Sort</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach ($rows as $r): ?>
<tr><td><?php if ($r['image']): ?><img src="<?= e(admin_upload_url('banners',$r['image'])) ?>" class="w-20 h-12 object-cover"/><?php endif; ?></td>
<td><?= e($r['position']) ?></td><td><?= e($r['title']) ?></td><td><?= $r['sort_order'] ?></td><td><?= $r['status']?'Active':'Off' ?></td>
<td><a class="btn-ghost" href="?action=edit&id=<?= $r['id'] ?>">Edit</a> <a class="btn-ghost" onclick="return confirm('Delete?')" href="?action=delete&id=<?= $r['id'] ?>">Delete</a></td></tr>
<?php endforeach; ?>
</tbody></table>
<?php endif; include __DIR__ . '/includes/footer.php'; ?>
