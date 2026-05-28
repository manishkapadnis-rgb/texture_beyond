<?php
$page_title = 'Orders';
require_once __DIR__ . '/includes/header.php';
$rows = $conn->query("SELECT * FROM orders ORDER BY id DESC");
?>
<h1 class="serif text-4xl mb-8">Orders</h1>
<table>
<thead><tr><th>Code</th><th>Customer</th><th>Email</th><th>Total</th><th>Payment</th><th>Status</th><th>Date</th><th></th></tr></thead>
<tbody>
<?php while ($r = $rows->fetch_assoc()): ?>
<tr>
  <td><?= e($r['order_code']) ?></td>
  <td><?= e($r['full_name']) ?></td>
  <td><?= e($r['email']) ?></td>
  <td><?= money($r['total']) ?></td>
  <td><?= e($r['payment_method']) ?> · <?= e($r['payment_status']) ?></td>
  <td><?= ucfirst($r['status']) ?></td>
  <td><?= date('M d, Y', strtotime($r['created_at'])) ?></td>
  <td><a class="btn-ghost" href="<?= admin_url('order-view.php?id=' . $r['id']) ?>">View</a></td>
</tr>
<?php endwhile; ?>
</tbody></table>
<?php include __DIR__ . '/includes/footer.php'; ?>
