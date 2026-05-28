<?php
require_once 'includes/functions.php';
require_login();
$u = current_user();
if ($_SERVER['REQUEST_METHOD']==='POST'){
    $stmt = $conn->prepare("UPDATE users SET name=?, phone=?, address=?, city=?, state=?, pincode=? WHERE id=?");
    $stmt->bind_param('ssssssi', $_POST['name'],$_POST['phone'],$_POST['address'],$_POST['city'],$_POST['state'],$_POST['pincode'], $u['id']);
    $stmt->execute();
    flash('success','Profile updated.');
    redirect(url('my-account.php'));
}
$orders = $conn->query("SELECT * FROM orders WHERE user_id={$u['id']} ORDER BY id DESC LIMIT 5");
$page_title = 'My Account | Texture & Beyond';
include 'includes/header.php';
?>
<section class="pt-40 pb-section-gap px-margin-mobile md:px-margin-desktop bg-surface">
  <div class="max-w-container-max mx-auto">
    <div class="text-center mb-16">
      <span class="font-label-caps text-label-caps text-secondary mb-4 block">Personal Gallery</span>
      <h1 class="font-display-md text-headline-lg md:text-display-md">My Account</h1>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-16">
      <div class="lg:col-span-1">
        <div class="bg-surface-container-low p-8">
          <div class="font-display-md text-headline-md mb-2"><?= e($u['name']) ?></div>
          <div class="font-body-md text-on-surface-variant mb-8"><?= e($u['email']) ?></div>
          <a href="<?= url('orders.php') ?>" class="block font-label-caps text-label-caps py-3 gold-underline">Order History</a>
          <a href="<?= url('logout.php') ?>" class="block font-label-caps text-label-caps py-3 gold-underline">Sign Out</a>
        </div>
      </div>
      <div class="lg:col-span-2">
        <h3 class="font-display-md text-headline-md mb-8">Profile Details</h3>
        <form method="post" class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <input class="input-underline" name="name" required value="<?= e($u['name']) ?>" placeholder="Name"/>
          <input class="input-underline" name="phone" value="<?= e($u['phone']) ?>" placeholder="Phone"/>
          <input class="input-underline md:col-span-2" name="address" value="<?= e($u['address']) ?>" placeholder="Address"/>
          <input class="input-underline" name="city" value="<?= e($u['city']) ?>" placeholder="City"/>
          <input class="input-underline" name="state" value="<?= e($u['state']) ?>" placeholder="State"/>
          <input class="input-underline" name="pincode" value="<?= e($u['pincode']) ?>" placeholder="Pincode"/>
          <button class="btn-primary md:col-span-2 mt-4">Update Profile</button>
        </form>

        <h3 class="font-display-md text-headline-md mt-16 mb-8">Recent Orders</h3>
        <div class="border-t border-outline-variant/30">
          <?php while ($o = $orders->fetch_assoc()): ?>
            <div class="flex justify-between items-center py-5 border-b border-outline-variant/30">
              <div>
                <div class="font-label-caps text-label-caps text-secondary mb-1"><?= e($o['order_code']) ?></div>
                <div class="font-body-md text-on-surface-variant"><?= date('M d, Y', strtotime($o['created_at'])) ?> · <?= ucfirst($o['status']) ?></div>
              </div>
              <div class="font-display-md text-[20px]"><?= money($o['total']) ?></div>
            </div>
          <?php endwhile; ?>
          <?php if ($orders->num_rows === 0): ?><p class="py-8 font-body-md text-on-surface-variant">No orders yet.</p><?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>
<?php include 'includes/footer.php'; ?>
