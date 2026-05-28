<?php
$page_title = 'Header Menu';
require_once __DIR__ . '/includes/header.php';
$action = $_GET['action'] ?? ''; $id = (int)($_GET['id'] ?? 0);
if ($action==='delete' && $id){ db_exec("DELETE FROM menus WHERE id=?",[$id]); flash('success','Deleted.'); redirect(admin_url('menu.php')); }
if ($_SERVER['REQUEST_METHOD']==='POST'){
    if (isset($_POST['bulk'])){
        foreach ($_POST['label'] as $i=>$lbl){
            $mid = (int)$_POST['id'][$i];
            $url=$_POST['url'][$i]; $so=(int)$_POST['sort_order'][$i]; $st=isset($_POST['status'][$i])?1:0;
            if ($mid) db_exec("UPDATE menus SET label=?,url=?,sort_order=?,status=? WHERE id=?", [$lbl,$url,$so,$st,$mid]);
            elseif ($lbl) db_exec("INSERT INTO menus (location,label,url,sort_order,status) VALUES ('header',?,?,?,?)", [$lbl,$url,$so,$st]);
        }
        flash('success','Menu saved.'); redirect(admin_url('menu.php'));
    }
}
$rows = db_all("SELECT * FROM menus WHERE location='header' ORDER BY sort_order, id");
?>
<h1 class="serif text-4xl mb-8">Header Menu</h1>
<form method="post" class="bg-white p-8">
  <input type="hidden" name="bulk" value="1"/>
  <table>
    <thead><tr><th>Label</th><th>URL</th><th>Sort</th><th>Active</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
      <tr>
        <td><input type="hidden" name="id[]" value="<?= $r['id'] ?>"/><input name="label[]" value="<?= e($r['label']) ?>" class="input"/></td>
        <td><input name="url[]" value="<?= e($r['url']) ?>" class="input"/></td>
        <td><input name="sort_order[]" type="number" value="<?= $r['sort_order'] ?>" class="input w-20"/></td>
        <td><input type="checkbox" name="status[<?= $r['id'] ?>]" <?= $r['status']?'checked':'' ?>/></td>
        <td><a class="btn-ghost" onclick="return confirm('Delete?')" href="?action=delete&id=<?= $r['id'] ?>">Delete</a></td>
      </tr>
      <?php endforeach; ?>
      <tr style="background:#fffae6">
        <td><input type="hidden" name="id[]" value="0"/><input name="label[]" placeholder="New label" class="input"/></td>
        <td><input name="url[]" placeholder="page.php?slug=..." class="input"/></td>
        <td><input name="sort_order[]" type="number" value="99" class="input w-20"/></td>
        <td><input type="checkbox" name="status[0]" checked/></td>
        <td></td>
      </tr>
    </tbody>
  </table>
  <button class="btn mt-6">Save Menu</button>
</form>
<?php include __DIR__ . '/includes/footer.php'; ?>
