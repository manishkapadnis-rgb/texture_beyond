<?php
$page_title = 'Blog';
require_once __DIR__ . '/includes/header.php';
$action = $_GET['action'] ?? ''; $id = (int)($_GET['id'] ?? 0);
if ($action==='delete' && $id){ db_exec("DELETE FROM blogs WHERE id=?",[$id]); flash('success','Deleted.'); redirect(admin_url('blogs.php')); }
if ($_SERVER['REQUEST_METHOD']==='POST'){
    $img = upload_image('image','blog', $_POST['existing_image'] ?? null);
    $slug = slugify($_POST['slug'] ?: $_POST['title']);
    $data = [$slug,$_POST['title'],$_POST['excerpt'],$_POST['content'],$img,$_POST['author'],$_POST['meta_title'],$_POST['meta_description'],isset($_POST['status'])?1:0];
    if ($id) db_exec("UPDATE blogs SET slug=?,title=?,excerpt=?,content=?,image=?,author=?,meta_title=?,meta_description=?,status=? WHERE id=?", array_merge($data,[$id]));
    else db_exec("INSERT INTO blogs (slug,title,excerpt,content,image,author,meta_title,meta_description,status) VALUES (?,?,?,?,?,?,?,?,?)", $data);
    flash('success','Saved.'); redirect(admin_url('blogs.php'));
}
if ($action==='edit'||$action==='new'):
$b = $id ? db_one("SELECT * FROM blogs WHERE id=?",[$id]) : [];
?>
<h1 class="serif text-4xl mb-8"><?= $id?'Edit':'New' ?> Post</h1>
<form method="post" enctype="multipart/form-data" class="bg-white p-10 grid grid-cols-1 md:grid-cols-2 gap-6 max-w-5xl">
  <input class="input md:col-span-2" name="title" required placeholder="Title" value="<?= e($b['title']??'') ?>"/>
  <input class="input" name="slug" placeholder="Slug" value="<?= e($b['slug']??'') ?>"/>
  <input class="input" name="author" placeholder="Author" value="<?= e($b['author']??'Texture & Beyond') ?>"/>
  <input class="input md:col-span-2" name="excerpt" placeholder="Excerpt" value="<?= e($b['excerpt']??'') ?>"/>
  <textarea class="input md:col-span-2" name="content" rows="12" placeholder="Content (HTML allowed)"><?= e($b['content']??'') ?></textarea>
  <div class="md:col-span-2"><label class="label-caps block mb-2">Cover Image</label>
    <input type="file" name="image"/><input type="hidden" name="existing_image" value="<?= e($b['image']??'') ?>"/>
    <?php if (!empty($b['image'])): ?><img src="<?= e(admin_upload_url('blog',$b['image'])) ?>" class="w-48 mt-3"/><?php endif; ?></div>
  <input class="input" name="meta_title" placeholder="Meta Title (SEO)" value="<?= e($b['meta_title']??'') ?>"/>
  <input class="input" name="meta_description" placeholder="Meta Description (SEO)" value="<?= e($b['meta_description']??'') ?>"/>
  <label class="flex items-center gap-2"><input type="checkbox" name="status" <?= !isset($b['status'])||$b['status']?'checked':'' ?>/> Published</label>
  <div class="md:col-span-2"><button class="btn">Save</button> <a class="btn-ghost" href="<?= admin_url('blogs.php') ?>">Cancel</a></div>
</form>
<?php else:
$rows = db_all("SELECT * FROM blogs ORDER BY id DESC");
?>
<div class="flex justify-between mb-8"><h1 class="serif text-4xl">Blog Posts</h1><a class="btn" href="?action=new">+ New Post</a></div>
<table><thead><tr><th>Title</th><th>Author</th><th>Status</th><th>Date</th><th></th></tr></thead><tbody>
<?php foreach ($rows as $r): ?>
<tr><td><?= e($r['title']) ?></td><td><?= e($r['author']) ?></td><td><?= $r['status']?'Published':'Draft' ?></td><td><?= date('M d, Y', strtotime($r['created_at'])) ?></td>
<td><a class="btn-ghost" href="?action=edit&id=<?= $r['id'] ?>">Edit</a> <a class="btn-ghost" onclick="return confirm('Delete?')" href="?action=delete&id=<?= $r['id'] ?>">Delete</a></td></tr>
<?php endforeach; ?>
</tbody></table>
<?php endif; include __DIR__ . '/includes/footer.php'; ?>
