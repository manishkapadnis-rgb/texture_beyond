<?php
$page_title = 'Reviews';
require_once __DIR__ . '/includes/header.php';
ensure_review_tables();

$action = $_GET['action'] ?? '';
$id = (int)($_GET['id'] ?? 0);
if ($id && in_array($action, ['approve','reject','pending','delete'], true)) {
    if ($action === 'delete') {
        $conn->query("DELETE FROM reviews WHERE id=$id");
        flash('success', 'Review deleted.');
    } else {
        $stmt = $conn->prepare("UPDATE reviews SET status=? WHERE id=?");
        $stmt->bind_param('si', $action, $id);
        $stmt->execute();
        flash('success', 'Review ' . $action . '.');
    }
    redirect(admin_url('reviews.php' . (!empty($_GET['filter']) ? '?filter=' . urlencode($_GET['filter']) : '')));
}

$filter   = $_GET['filter']   ?? 'all';
$f_prod   = (int)($_GET['product'] ?? 0);
$f_user   = (int)($_GET['user']    ?? 0);
$f_rating = (int)($_GET['rating']  ?? 0);

$where = [];
if (in_array($filter, ['pending','approved','rejected'], true)) $where[] = "r.status='" . $conn->real_escape_string($filter) . "'";
if ($f_prod)   $where[] = "r.product_id=$f_prod";
if ($f_user)   $where[] = "r.user_id=$f_user";
if ($f_rating) $where[] = "r.rating=$f_rating";
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$rows = [];
$r = $conn->query("SELECT r.*, p.name AS product_name, p.slug AS product_slug, u.name AS user_name, u.email AS user_email FROM reviews r LEFT JOIN products p ON p.id=r.product_id LEFT JOIN users u ON u.id=r.user_id $where_sql ORDER BY r.id DESC LIMIT 200");
if ($r) while ($row = $r->fetch_assoc()) $rows[] = $row;

$counts = ['all'=>0,'pending'=>0,'approved'=>0,'rejected'=>0];
$c = $conn->query("SELECT status, COUNT(*) n FROM reviews GROUP BY status");
if ($c) while ($row = $c->fetch_assoc()) { $counts[$row['status']] = (int)$row['n']; $counts['all'] += (int)$row['n']; }
?>
<div class="flex items-center justify-between mb-8">
  <h1 class="serif text-3xl">Customer Reviews</h1>
  <div class="text-sm text-on-surface-variant"><?= count($rows) ?> shown</div>
</div>

<div class="flex flex-wrap items-center gap-2 mb-6">
  <?php foreach (['all'=>'All','pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected'] as $k=>$lbl): ?>
    <a href="?filter=<?= $k ?>" class="px-4 py-2 text-xs font-semibold tracking-widest uppercase rounded <?= $filter===$k ? 'bg-black text-white' : 'bg-white border border-black/10 text-on-surface' ?>">
      <?= $lbl ?> <span class="opacity-70">(<?= $counts[$k] ?? 0 ?>)</span>
    </a>
  <?php endforeach; ?>
</div>

<form method="get" class="mb-6 flex flex-wrap gap-2">
  <input type="hidden" name="filter" value="<?= e($filter) ?>"/>
  <input class="input max-w-[160px]" type="number" name="product" placeholder="Product ID" value="<?= $f_prod ?: '' ?>"/>
  <input class="input max-w-[160px]" type="number" name="user"    placeholder="User ID"    value="<?= $f_user ?: '' ?>"/>
  <select class="input max-w-[140px]" name="rating">
    <option value="">Any rating</option>
    <?php for ($i=5; $i>=1; $i--): ?><option value="<?= $i ?>" <?= $f_rating===$i?'selected':'' ?>><?= $i ?> stars</option><?php endfor; ?>
  </select>
  <button class="btn">Filter</button>
  <?php if ($f_prod || $f_user || $f_rating): ?><a class="btn-ghost" href="?filter=<?= e($filter) ?>">Reset</a><?php endif; ?>
</form>

<div class="bg-white rounded shadow-sm overflow-hidden">
  <table>
    <thead>
      <tr><th>Product</th><th>User</th><th>Rating</th><th>Review</th><th>Status</th><th>Verified</th><th>Date</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php if (empty($rows)): ?>
        <tr><td colspan="8" class="text-center py-12 text-on-surface-variant">No reviews match these filters.</td></tr>
      <?php else: foreach ($rows as $rv): ?>
        <tr>
          <td>
            <a class="font-semibold hover:underline" href="<?= url('product.php?slug=' . $rv['product_slug']) ?>" target="_blank"><?= e($rv['product_name']) ?: 'Deleted' ?></a>
            <div class="text-xs text-on-surface-variant">ID: <?= (int)$rv['product_id'] ?></div>
          </td>
          <td>
            <div><?= e($rv['user_name'] ?: $rv['name']) ?></div>
            <div class="text-xs text-on-surface-variant"><?= e($rv['user_email'] ?: $rv['email']) ?></div>
          </td>
          <td>
            <span class="text-amber-500 tracking-widest"><?= str_repeat('★', (int)$rv['rating']) . str_repeat('☆', 5 - (int)$rv['rating']) ?></span>
          </td>
          <td class="max-w-md">
            <?php if ($rv['title']): ?><div class="font-semibold mb-1"><?= e($rv['title']) ?></div><?php endif; ?>
            <div class="text-sm text-on-surface-variant line-clamp-3"><?= e(mb_substr($rv['body'], 0, 220)) ?><?= mb_strlen($rv['body']) > 220 ? '…' : '' ?></div>
          </td>
          <td>
            <span class="px-2 py-1 text-[10px] font-bold uppercase tracking-widest rounded <?= ['pending'=>'bg-yellow-100 text-yellow-800','approved'=>'bg-green-100 text-green-800','rejected'=>'bg-red-100 text-red-800'][$rv['status']] ?? 'bg-gray-100' ?>">
              <?= e($rv['status']) ?>
            </span>
          </td>
          <td>
            <?php if (!empty($rv['verified'])): ?>
              <span class="material-symbols-outlined text-green-600" title="Verified purchase">verified</span>
            <?php else: ?>
              <span class="text-on-surface-variant text-xs">—</span>
            <?php endif; ?>
          </td>
          <td class="text-xs text-on-surface-variant"><?= e(date('M j, Y', strtotime($rv['created_at']))) ?></td>
          <td>
            <div class="flex gap-1">
              <?php if ($rv['status'] !== 'approved'): ?><a class="btn text-[10px] py-1 px-2 bg-green-700" href="?action=approve&id=<?= $rv['id'] ?>&filter=<?= e($filter) ?>">Approve</a><?php endif; ?>
              <?php if ($rv['status'] !== 'rejected'): ?><a class="btn-ghost text-[10px] py-1 px-2" href="?action=reject&id=<?= $rv['id'] ?>&filter=<?= e($filter) ?>">Reject</a><?php endif; ?>
              <a class="btn-ghost text-[10px] py-1 px-2 border-red-600 text-red-600" href="?action=delete&id=<?= $rv['id'] ?>&filter=<?= e($filter) ?>" onclick="return confirm('Delete this review?')">Delete</a>
            </div>
          </td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
