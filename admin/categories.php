<?php
$page_title = 'Categories';
require_once __DIR__ . '/includes/header.php';
$action = $_GET['action'] ?? '';
$id = (int)($_GET['id'] ?? 0);
if ($action==='delete' && $id){ $conn->query("DELETE FROM categories WHERE id=$id"); flash('success','Deleted.'); redirect(admin_url('categories.php')); }
if ($_SERVER['REQUEST_METHOD']==='POST'){
    $name=$_POST['name']; $slug=slugify($_POST['slug']?:$_POST['name']);
    $tag=$_POST['tagline']; $desc=$_POST['description'];
    $fe=isset($_POST['is_featured'])?1:0; $st=isset($_POST['status'])?1:0;
    $img = $_POST['existing_image'] ?? null;
    if (!empty($_FILES['image']['name'])){
        $fn=time().'_'.preg_replace('/[^a-zA-Z0-9._-]/','',$_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], UPLOAD_DIR.'/categories/'.$fn);
        $img=$fn;
    }
    if ($id){
        $stmt=$conn->prepare("UPDATE categories SET name=?,slug=?,tagline=?,description=?,image=?,is_featured=?,status=? WHERE id=?");
        $stmt->bind_param('sssssiii',$name,$slug,$tag,$desc,$img,$fe,$st,$id);
    } else {
        $stmt=$conn->prepare("INSERT INTO categories (name,slug,tagline,description,image,is_featured,status) VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param('sssssii',$name,$slug,$tag,$desc,$img,$fe,$st);
    }
    $stmt->execute();
    flash('success','Saved.');
    redirect(admin_url('categories.php'));
}
if ($action==='edit'||$action==='new'):
$c = $id ? $conn->query("SELECT * FROM categories WHERE id=$id")->fetch_assoc() : [];
?>
<h1 class="serif text-4xl mb-8"><?= $id?'Edit Category':'New Category' ?></h1>
<form method="post" enctype="multipart/form-data" class="bg-white p-10 grid grid-cols-1 md:grid-cols-2 gap-6 max-w-3xl">
  <input class="input" name="name" required placeholder="Name" value="<?= e($c['name']??'') ?>"/>
  <input class="input" name="slug" placeholder="Slug" value="<?= e($c['slug']??'') ?>"/>
  <input class="input md:col-span-2" name="tagline" placeholder="Tagline" value="<?= e($c['tagline']??'') ?>"/>
  <textarea class="input md:col-span-2" name="description" rows="3" placeholder="Description"><?= e($c['description']??'') ?></textarea>
  <div class="md:col-span-2"><label class="label-caps block mb-2">Image</label>
    <input type="file" name="image"/>
    <input type="hidden" name="existing_image" value="<?= e($c['image']??'') ?>"/>
    <?php if (!empty($c['image'])): ?><img src="<?= e(category_image($c['image'])) ?>" class="w-32 mt-3"/><?php endif; ?>
  </div>
  <label class="flex items-center gap-2"><input type="checkbox" name="is_featured" <?= !empty($c['is_featured'])?'checked':'' ?>/> Featured</label>
  <label class="flex items-center gap-2"><input type="checkbox" name="status" <?= !isset($c['status'])||$c['status']?'checked':'' ?>/> Active</label>
  <div class="md:col-span-2 flex gap-4"><button class="btn">Save</button><a href="<?= admin_url('categories.php') ?>" class="btn-ghost">Cancel</a></div>
</form>
<?php else:
$rows = $conn->query("SELECT * FROM categories ORDER BY id DESC");
?>
<div class="flex justify-between mb-8"><h1 class="serif text-4xl">Categories</h1><a class="btn" href="?action=new">+ New</a></div>
<table><thead><tr><th>Name</th><th>Slug</th><th>Featured</th><th>Status</th><th></th></tr></thead><tbody>
<?php while ($r=$rows->fetch_assoc()): ?>
<tr><td><?= e($r['name']) ?></td><td><?= e($r['slug']) ?></td><td><?= $r['is_featured']?'Yes':'No' ?></td><td><?= $r['status']?'Active':'Hidden' ?></td>
<td><a class="btn-ghost" href="?action=edit&id=<?= $r['id'] ?>">Edit</a> <a class="btn-ghost" onclick="return confirm('Delete?')" href="?action=delete&id=<?= $r['id'] ?>">Delete</a></td></tr>
<?php endwhile; ?>
</tbody></table>
<?php endif; include __DIR__ . '/includes/footer.php'; ?>
