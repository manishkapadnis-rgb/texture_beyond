<?php
require_once 'includes/functions.php';
$cat_slug = $_GET['category'] ?? '';
$search = trim($_GET['q'] ?? '');
$filter = $_GET['filter'] ?? '';
$sort = $_GET['sort'] ?? 'new';
$where = "p.status=1";
if ($cat_slug){
    $stmt = $conn->prepare("SELECT id, name FROM categories WHERE slug=?");
    $stmt->bind_param('s', $cat_slug); $stmt->execute();
    $cat = $stmt->get_result()->fetch_assoc();
    if ($cat) $where .= " AND p.category_id=" . (int)$cat['id'];
}
if ($search){ $s = $conn->real_escape_string($search); $where .= " AND p.name LIKE '%$s%'"; }
if ($filter === 'bestseller') $where .= " AND p.is_bestseller=1";
if ($filter === 'new') $where .= " AND p.is_new=1";
$order = match($sort){ 'low'=>'COALESCE(p.sale_price,p.price) ASC','high'=>'COALESCE(p.sale_price,p.price) DESC', default=>'p.id DESC' };
$res = $conn->query("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id=p.category_id WHERE $where ORDER BY $order");
$cats = get_categories();
$page_title = ($cat['name'] ?? 'Shop') . ' | Texture & Beyond';
include 'includes/header.php';
?>
<section class="pt-40 pb-20 px-margin-mobile md:px-margin-desktop bg-surface">
  <div class="max-w-container-max mx-auto">
    <div class="text-center mb-16">
      <span class="font-label-caps text-label-caps text-secondary mb-4 block">The Shop</span>
      <h1 class="font-display-md text-headline-lg md:text-display-md"><?= e($cat['name'] ?? 'All Works') ?></h1>
    </div>

    <div class="flex flex-wrap justify-between items-center gap-6 mb-12 border-y border-outline-variant/30 py-4">
      <div class="flex flex-wrap gap-6 font-label-caps text-label-caps">
        <a href="<?= url('shop.php') ?>" class="<?= !$cat_slug?'text-secondary':'' ?> gold-underline">All</a>
        <?php while ($c = $cats->fetch_assoc()): ?>
          <a href="<?= url('shop.php?category=' . $c['slug']) ?>" class="<?= $cat_slug===$c['slug']?'text-secondary':'' ?> gold-underline"><?= e($c['name']) ?></a>
        <?php endwhile; ?>
      </div>
      <form method="get" class="flex items-center gap-4">
        <?php if ($cat_slug): ?><input type="hidden" name="category" value="<?= e($cat_slug) ?>"><?php endif; ?>
        <select name="sort" onchange="this.form.submit()" class="bg-transparent border-b border-primary py-2 font-label-caps text-label-caps focus:outline-none">
          <option value="new" <?= $sort=='new'?'selected':'' ?>>Newest</option>
          <option value="low" <?= $sort=='low'?'selected':'' ?>>Price: Low</option>
          <option value="high" <?= $sort=='high'?'selected':'' ?>>Price: High</option>
        </select>
      </form>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-gutter gsap-stagger">
      <?php while ($p = $res->fetch_assoc()): include 'components/product-card.php'; endwhile; ?>
      <?php if ($res->num_rows === 0): ?>
        <p class="col-span-full text-center font-body-lg text-on-surface-variant py-20">No works found.</p>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php include 'includes/footer.php'; ?>
