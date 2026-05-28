<?php
$page_title = 'Dashboard';
require_once __DIR__ . '/includes/header.php';
$stats = [
  'orders' => $conn->query("SELECT COUNT(*) c FROM orders")->fetch_assoc()['c'],
  'revenue'=> $conn->query("SELECT COALESCE(SUM(total),0) c FROM orders WHERE status<>'cancelled'")->fetch_assoc()['c'],
  'products'=> $conn->query("SELECT COUNT(*) c FROM products")->fetch_assoc()['c'],
  'users'  => $conn->query("SELECT COUNT(*) c FROM users WHERE role='user'")->fetch_assoc()['c'],
];
$recent = $conn->query("SELECT * FROM orders ORDER BY id DESC LIMIT 8");
?>
<h1 class="serif text-4xl mb-2">Dashboard</h1>
<p class="text-[#444] mb-10">Welcome back.</p>
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
  <?php foreach ([['Orders',$stats['orders'],'receipt_long'],['Revenue', money($stats['revenue']),'payments'],['Products',$stats['products'],'inventory_2'],['Customers',$stats['users'],'group']] as $s): ?>
    <div class="bg-white p-8 border border-[#ece1d4]">
      <span class="material-symbols-outlined text-[#775a19]"><?= $s[2] ?></span>
      <div class="label-caps text-[#444] mt-4"><?= $s[0] ?></div>
      <div class="serif text-3xl mt-2"><?= $s[1] ?></div>
    </div>
  <?php endforeach; ?>
</div>
<h2 class="serif text-2xl mb-6">Recent Orders</h2>
<table>
<thead><tr><th>Code</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th><th></th></tr></thead>
<tbody>
<?php while ($o = $recent->fetch_assoc()): ?>
<tr>
  <td><?= e($o['order_code']) ?></td>
  <td><?= e($o['full_name']) ?></td>
  <td><?= money($o['total']) ?></td>
  <td><?= ucfirst($o['status']) ?></td>
  <td><?= date('M d, Y', strtotime($o['created_at'])) ?></td>
  <td><a class="btn-ghost" href="<?= admin_url('order-view.php?id=' . $o['id']) ?>">View</a></td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
<?php include __DIR__ . '/includes/footer.php'; ?>
