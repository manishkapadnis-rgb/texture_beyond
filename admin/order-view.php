<?php
$page_title = 'Order';
require_once __DIR__ . '/includes/header.php';
$id = (int)($_GET['id'] ?? 0);
if ($_SERVER['REQUEST_METHOD']==='POST'){
    $stmt = $conn->prepare("UPDATE orders SET status=?, payment_status=? WHERE id=?");
    $stmt->bind_param('ssi', $_POST['status'], $_POST['payment_status'], $id);
    $stmt->execute();
    flash('success','Order updated.');
    redirect(admin_url('order-view.php?id=' . $id));
}
$o = $conn->query("SELECT * FROM orders WHERE id=$id")->fetch_assoc();
if (!$o){ echo 'Not found'; include __DIR__.'/includes/footer.php'; exit; }
$items = $conn->query("SELECT * FROM order_items WHERE order_id=$id");
?>
<h1 class="serif text-4xl mb-2">Order <?= e($o['order_code']) ?></h1>
<p class="text-[#444] mb-8"><?= date('M d, Y · H:i', strtotime($o['created_at'])) ?></p>
<div class="grid grid-cols-1 md:grid-cols-3 gap-8">
  <div class="md:col-span-2 bg-white p-8">
    <h3 class="serif text-2xl mb-4">Items</h3>
    <table><thead><tr><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr></thead><tbody>
    <?php while ($i = $items->fetch_assoc()): ?>
      <tr><td><?= e($i['product_name']) ?></td><td><?= money($i['price']) ?></td><td><?= $i['quantity'] ?></td><td><?= money($i['subtotal']) ?></td></tr>
    <?php endwhile; ?>
    </tbody></table>
    <div class="mt-6 text-right space-y-1">
      <div>Subtotal: <strong><?= money($o['subtotal']) ?></strong></div>
      <?php if ($o['discount']>0): ?><div>Discount (<?= e($o['coupon_code']) ?>): <strong>−<?= money($o['discount']) ?></strong></div><?php endif; ?>
      <div>Shipping: <strong><?= money($o['shipping']) ?></strong></div>
      <div class="serif text-2xl">Total: <?= money($o['total']) ?></div>
    </div>
  </div>
  <div class="space-y-6">
    <div class="bg-white p-6">
      <div class="label-caps mb-2">Customer</div>
      <div class="font-medium"><?= e($o['full_name']) ?></div>
      <div class="text-sm text-[#444]"><?= e($o['email']) ?><br><?= e($o['phone']) ?></div>
      <div class="text-sm mt-4"><?= nl2br(e($o['address'])) ?><br><?= e($o['city']) ?>, <?= e($o['state']) ?> <?= e($o['pincode']) ?></div>
    </div>
    <form method="post" class="bg-white p-6 space-y-4">
      <div><label class="label-caps block mb-2">Order Status</label>
        <select name="status" class="input"><?php foreach (['pending','processing','shipped','delivered','cancelled'] as $s): ?>
          <option <?= $o['status']==$s?'selected':'' ?>><?= $s ?></option>
        <?php endforeach; ?></select></div>
      <div><label class="label-caps block mb-2">Payment Status</label>
        <select name="payment_status" class="input"><?php foreach (['pending','paid','failed'] as $s): ?>
          <option <?= $o['payment_status']==$s?'selected':'' ?>><?= $s ?></option>
        <?php endforeach; ?></select></div>
      <button class="btn w-full">Update</button>
    </form>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
