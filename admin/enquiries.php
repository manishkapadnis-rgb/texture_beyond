<?php
$page_title = 'Enquiries';
require_once __DIR__ . '/includes/header.php';
$id = (int)($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';
if ($action==='delete' && $id){ db_exec("DELETE FROM enquiries WHERE id=?", [$id]); flash('success','Deleted.'); redirect(admin_url('enquiries.php')); }
if ($action==='read' && $id){ db_exec("UPDATE enquiries SET status='read' WHERE id=?", [$id]); redirect(admin_url('enquiries.php')); }
if ($action==='export'){
    header('Content-Type: text/csv'); header('Content-Disposition: attachment; filename=enquiries-'.date('Ymd').'.csv');
    $out = fopen('php://output','w');
    fputcsv($out, ['ID','Type','Name','Email','Phone','Company','Subject','Message','Date']);
    foreach (db_all("SELECT * FROM enquiries ORDER BY id DESC") as $e)
        fputcsv($out, [$e['id'],$e['type'],$e['name'],$e['email'],$e['phone'],$e['company'],$e['subject'],$e['message'],$e['created_at']]);
    exit;
}
$type = $_GET['type'] ?? '';
$where = $type ? "WHERE type='" . db()->real_escape_string($type) . "'" : "";
$rows = db_all("SELECT * FROM enquiries $where ORDER BY id DESC");
?>
<div class="flex justify-between items-center mb-8">
  <h1 class="serif text-4xl">Enquiries</h1>
  <div class="flex gap-3">
    <select onchange="location='?type='+this.value" class="input">
      <option value="">All Types</option>
      <?php foreach (['contact','bulk','corporate','product'] as $t): ?>
        <option value="<?= $t ?>" <?= $type==$t?'selected':'' ?>><?= ucfirst($t) ?></option>
      <?php endforeach; ?>
    </select>
    <a class="btn" href="?action=export">Export CSV</a>
  </div>
</div>
<table>
<thead><tr><th>Type</th><th>Name</th><th>Contact</th><th>Subject / Message</th><th>Date</th><th></th></tr></thead>
<tbody>
<?php foreach ($rows as $r): ?>
<tr style="<?= $r['status']=='new'?'background:#fffae6':'' ?>">
  <td><span class="label-caps px-2 py-1 bg-[#1c1b1b] text-white"><?= e($r['type']) ?></span></td>
  <td><?= e($r['name']) ?><?php if ($r['company']): ?><br><small class="text-[#666]"><?= e($r['company']) ?></small><?php endif; ?></td>
  <td><?= e($r['email']) ?><br><small><?= e($r['phone']) ?></small></td>
  <td class="max-w-md"><?php if ($r['subject']): ?><strong><?= e($r['subject']) ?></strong><br><?php endif; ?><?= nl2br(e($r['message'])) ?></td>
  <td><?= date('M d, Y H:i', strtotime($r['created_at'])) ?></td>
  <td>
    <?php if ($r['status']=='new'): ?><a class="btn-ghost" href="?action=read&id=<?= $r['id'] ?>">Mark Read</a><?php endif; ?>
    <a class="btn-ghost" onclick="return confirm('Delete?')" href="?action=delete&id=<?= $r['id'] ?>">Delete</a>
  </td>
</tr>
<?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="6" class="text-center py-8 text-[#666]">No enquiries yet.</td></tr><?php endif; ?>
</tbody></table>
<?php include __DIR__ . '/includes/footer.php'; ?>
