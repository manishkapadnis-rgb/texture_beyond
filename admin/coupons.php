<?php
$page_title = 'Coupons';
require_once __DIR__ . '/includes/header.php';
$action = $_GET['action'] ?? '';
$id = (int)($_GET['id'] ?? 0);
if ($action==='delete' && $id){ $conn->query("DELETE FROM coupons WHERE id=$id"); flash('success','Deleted.'); redirect(admin_url('coupons.php')); }
if ($_SERVER['REQUEST_METHOD']==='POST'){
    $code=strtoupper(trim($_POST['code'])); $type=$_POST['type']; $disc=$_POST['discount'];
    $min=$_POST['min_order']?:0; $max=$_POST['max_discount']?:null;
    $vf=$_POST['valid_from']?:null; $vt=$_POST['valid_to']?:null;
    $lim=$_POST['usage_limit']?:null; $st=isset($_POST['status'])?1:0;
    if ($id){
        $stmt=$conn->prepare("UPDATE coupons SET code=?,type=?,discount=?,min_order=?,max_discount=?,valid_from=?,valid_to=?,usage_limit=?,status=? WHERE id=?");
        $stmt->bind_param('ssdddssiii',$code,$type,$disc,$min,$max,$vf,$vt,$lim,$st,$id);
    } else {
        $stmt=$conn->prepare("INSERT INTO coupons (code,type,discount,min_order,max_discount,valid_from,valid_to,usage_limit,status) VALUES (?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param('ssdddssii',$code,$type,$disc,$min,$max,$vf,$vt,$lim,$st);
    }
    $stmt->execute();
    flash('success','Saved.');
    redirect(admin_url('coupons.php'));
}
if ($action==='edit'||$action==='new'):
$c = $id ? $conn->query("SELECT * FROM coupons WHERE id=$id")->fetch_assoc() : [];
?>
<h1 class="serif text-4xl mb-8"><?= $id?'Edit Coupon':'New Coupon' ?></h1>
<form method="post" class="bg-white p-10 grid grid-cols-1 md:grid-cols-2 gap-6 max-w-3xl">
  <input class="input" name="code" required placeholder="CODE" value="<?= e($c['code']??'') ?>"/>
  <select class="input" name="type"><option value="flat" <?= ($c['type']??'')=='flat'?'selected':'' ?>>Flat ₹</option><option value="percent" <?= ($c['type']??'')=='percent'?'selected':'' ?>>Percent %</option></select>
  <input class="input" name="discount" type="number" step="0.01" required placeholder="Discount" value="<?= e($c['discount']??'') ?>"/>
  <input class="input" name="min_order" type="number" step="0.01" placeholder="Min Order" value="<?= e($c['min_order']??'') ?>"/>
  <input class="input" name="max_discount" type="number" step="0.01" placeholder="Max Discount (percent only)" value="<?= e($c['max_discount']??'') ?>"/>
  <input class="input" name="usage_limit" type="number" placeholder="Usage Limit" value="<?= e($c['usage_limit']??'') ?>"/>
  <input class="input" name="valid_from" type="date" value="<?= e($c['valid_from']??'') ?>"/>
  <input class="input" name="valid_to" type="date" value="<?= e($c['valid_to']??'') ?>"/>
  <label class="flex items-center gap-2"><input type="checkbox" name="status" <?= !isset($c['status'])||$c['status']?'checked':'' ?>/> Active</label>
  <div class="md:col-span-2"><button class="btn">Save</button> <a class="btn-ghost" href="<?= admin_url('coupons.php') ?>">Cancel</a></div>
</form>
<?php else:
$rows = $conn->query("SELECT * FROM coupons ORDER BY id DESC");
?>
<div class="flex justify-between mb-8"><h1 class="serif text-4xl">Coupons</h1><a class="btn" href="?action=new">+ New</a></div>
<table><thead><tr><th>Code</th><th>Type</th><th>Discount</th><th>Min Order</th><th>Used</th><th>Status</th><th></th></tr></thead><tbody>
<?php while ($r=$rows->fetch_assoc()): ?>
<tr><td><?= e($r['code']) ?></td><td><?= e($r['type']) ?></td><td><?= $r['type']=='percent'?$r['discount'].'%':money($r['discount']) ?></td><td><?= money($r['min_order']) ?></td><td><?= $r['used'] ?>/<?= $r['usage_limit']?:'∞' ?></td><td><?= $r['status']?'Active':'Off' ?></td>
<td><a class="btn-ghost" href="?action=edit&id=<?= $r['id'] ?>">Edit</a> <a class="btn-ghost" onclick="return confirm('Delete?')" href="?action=delete&id=<?= $r['id'] ?>">Delete</a></td></tr>
<?php endwhile; ?>
</tbody></table>
<?php endif; include __DIR__ . '/includes/footer.php'; ?>
