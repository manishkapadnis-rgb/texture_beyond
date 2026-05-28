<?php
$page_title = 'Gallery';
require_once __DIR__ . '/includes/header.php';
$action = $_GET['action'] ?? ''; $id = (int)($_GET['id'] ?? 0);
if ($action==='delete' && $id){ db_exec("DELETE FROM gallery WHERE id=?",[$id]); flash('success','Deleted.'); redirect(admin_url('gallery-admin.php')); }
if ($_SERVER['REQUEST_METHOD']==='POST'){
    if (!empty($_FILES['images']['name'][0])){
        foreach ($_FILES['images']['name'] as $i=>$n){
            if (!$n) continue;
            $fn = time().'_'.$i.'_'.preg_replace('/[^a-zA-Z0-9._-]/','',$n);
            move_uploaded_file($_FILES['images']['tmp_name'][$i], UPLOAD_DIR.'/gallery/'.$fn);
            db_exec("INSERT INTO gallery (title,image,caption,sort_order,status) VALUES (?,?,?,?,1)", [$_POST['title']??'',$fn,$_POST['caption']??'',(int)($_POST['sort_order']??0)]);
        }
    }
    flash('success','Uploaded.'); redirect(admin_url('gallery-admin.php'));
}
$rows = db_all("SELECT * FROM gallery ORDER BY sort_order, id DESC");
?>
<h1 class="serif text-4xl mb-8">Gallery</h1>
<form method="post" enctype="multipart/form-data" class="bg-white p-8 mb-10 grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
  <input class="input" name="title" placeholder="Title (optional)"/>
  <input class="input" name="caption" placeholder="Caption (optional)"/>
  <input class="input" name="sort_order" type="number" placeholder="Sort Order" value="0"/>
  <div class="md:col-span-3"><input type="file" name="images[]" multiple required accept="image/*"/></div>
  <div class="md:col-span-3"><button class="btn">Upload</button></div>
</form>
<div class="grid grid-cols-2 md:grid-cols-5 gap-4">
  <?php foreach ($rows as $g): ?>
    <div class="relative bg-white">
      <img src="<?= e(admin_upload_url('gallery',$g['image'])) ?>" class="w-full aspect-square object-cover"/>
      <div class="p-2 text-xs"><?= e($g['title']) ?></div>
      <a href="?action=delete&id=<?= $g['id'] ?>" onclick="return confirm('Delete?')" class="absolute top-2 right-2 bg-black/70 text-white w-7 h-7 flex items-center justify-center rounded-full">×</a>
    </div>
  <?php endforeach; ?>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
