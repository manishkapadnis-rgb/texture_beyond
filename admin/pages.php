<?php
$page_title = 'Pages';
require_once __DIR__ . '/includes/header.php';
$action = $_GET['action'] ?? ''; $id = (int)($_GET['id'] ?? 0);
if ($action==='delete' && $id){ db_exec("DELETE FROM pages WHERE id=?",[$id]); flash('success','Deleted.'); redirect(admin_url('pages.php')); }
if ($_SERVER['REQUEST_METHOD']==='POST'){
    $slug = slugify($_POST['slug']);
    $data = [$slug,$_POST['title'],$_POST['content'],$_POST['meta_title'],$_POST['meta_description']];
    if ($id) db_exec("UPDATE pages SET slug=?,title=?,content=?,meta_title=?,meta_description=? WHERE id=?", array_merge($data,[$id]));
    else db_exec("INSERT INTO pages (slug,title,content,meta_title,meta_description) VALUES (?,?,?,?,?)", $data);
    flash('success','Saved.'); redirect(admin_url('pages.php'));
}
if ($action==='edit'||$action==='new'):
$p = $id ? db_one("SELECT * FROM pages WHERE id=?",[$id]) : [];
?>
<h1 class="serif text-4xl mb-8"><?= $id?'Edit':'New' ?> Page</h1>
<form method="post" class="bg-white p-10 grid grid-cols-1 md:grid-cols-2 gap-6 max-w-5xl">
  <input class="input" name="slug" required placeholder="Slug (e.g. about)" value="<?= e($p['slug']??'') ?>"/>
  <input class="input" name="title" required placeholder="Title" value="<?= e($p['title']??'') ?>"/>
  <textarea class="input md:col-span-2" name="content" rows="14" placeholder="Content (HTML allowed)"><?= e($p['content']??'') ?></textarea>
  <input class="input" name="meta_title" placeholder="Meta Title (SEO)" value="<?= e($p['meta_title']??'') ?>"/>
  <input class="input" name="meta_description" placeholder="Meta Description (SEO)" value="<?= e($p['meta_description']??'') ?>"/>
  <div class="md:col-span-2"><button class="btn">Save</button> <a class="btn-ghost" href="<?= admin_url('pages.php') ?>">Cancel</a></div>
</form>
<?php else:
$rows = db_all("SELECT * FROM pages ORDER BY id DESC");
?>
<div class="flex justify-between mb-8"><h1 class="serif text-4xl">Pages</h1><a class="btn" href="?action=new">+ New Page</a></div>
<table><thead><tr><th>Slug</th><th>Title</th><th>Updated</th><th></th></tr></thead><tbody>
<?php foreach ($rows as $r): ?>
<tr><td><code><?= e($r['slug']) ?></code></td><td><?= e($r['title']) ?></td><td><?= date('M d, Y', strtotime($r['updated_at'])) ?></td>
<td><a class="btn-ghost" href="<?= url('page.php?slug='.$r['slug']) ?>" target="_blank">View</a> <a class="btn-ghost" href="?action=edit&id=<?= $r['id'] ?>">Edit</a> <a class="btn-ghost" onclick="return confirm('Delete?')" href="?action=delete&id=<?= $r['id'] ?>">Delete</a></td></tr>
<?php endforeach; ?>
</tbody></table>
<?php endif; include __DIR__ . '/includes/footer.php'; ?>
