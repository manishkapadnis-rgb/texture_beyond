<?php
$page_title = 'Testimonials';
require_once __DIR__ . '/includes/header.php';
$action = $_GET['action'] ?? ''; $id = (int)($_GET['id'] ?? 0);
if ($action==='delete' && $id){ db_exec("DELETE FROM testimonials WHERE id=?",[$id]); flash('success','Deleted.'); redirect(admin_url('testimonials.php')); }
if ($_SERVER['REQUEST_METHOD']==='POST'){
    $img = upload_image('image','testimonials', $_POST['existing_image'] ?? null);
    $data = [$_POST['name'],$_POST['role'],$img,$_POST['quote'],(int)$_POST['rating'],isset($_POST['status'])?1:0];
    if ($id) db_exec("UPDATE testimonials SET name=?,role=?,image=?,quote=?,rating=?,status=? WHERE id=?", array_merge($data,[$id]));
    else db_exec("INSERT INTO testimonials (name,role,image,quote,rating,status) VALUES (?,?,?,?,?,?)", $data);
    flash('success','Saved.'); redirect(admin_url('testimonials.php'));
}
if ($action==='edit'||$action==='new'):
$t = $id ? db_one("SELECT * FROM testimonials WHERE id=?",[$id]) : [];
?>
<h1 class="serif text-4xl mb-8"><?= $id?'Edit':'New' ?> Testimonial</h1>
<form method="post" enctype="multipart/form-data" class="bg-white p-10 grid grid-cols-1 md:grid-cols-2 gap-6 max-w-3xl">
  <input class="input" name="name" required placeholder="Name" value="<?= e($t['name']??'') ?>"/>
  <input class="input" name="role" placeholder="Role / Location" value="<?= e($t['role']??'') ?>"/>
  <textarea class="input md:col-span-2" name="quote" rows="4" required placeholder="Quote"><?= e($t['quote']??'') ?></textarea>
  <input class="input" type="number" min="1" max="5" name="rating" value="<?= e($t['rating']??5) ?>"/>
  <div><input type="file" name="image"/><input type="hidden" name="existing_image" value="<?= e($t['image']??'') ?>"/></div>
  <label class="flex items-center gap-2"><input type="checkbox" name="status" <?= !isset($t['status'])||$t['status']?'checked':'' ?>/> Active</label>
  <div class="md:col-span-2"><button class="btn">Save</button> <a class="btn-ghost" href="<?= admin_url('testimonials.php') ?>">Cancel</a></div>
</form>
<?php else:
$rows = db_all("SELECT * FROM testimonials ORDER BY id DESC");
?>
<div class="flex justify-between mb-8"><h1 class="serif text-4xl">Testimonials</h1><a class="btn" href="?action=new">+ New</a></div>
<table><thead><tr><th>Name</th><th>Role</th><th>Quote</th><th>Rating</th><th></th></tr></thead><tbody>
<?php foreach ($rows as $r): ?>
<tr><td><?= e($r['name']) ?></td><td><?= e($r['role']) ?></td><td class="max-w-lg"><?= e($r['quote']) ?></td><td><?= $r['rating'] ?>★</td>
<td><a class="btn-ghost" href="?action=edit&id=<?= $r['id'] ?>">Edit</a> <a class="btn-ghost" onclick="return confirm('Delete?')" href="?action=delete&id=<?= $r['id'] ?>">Delete</a></td></tr>
<?php endforeach; ?>
</tbody></table>
<?php endif; include __DIR__ . '/includes/footer.php'; ?>
