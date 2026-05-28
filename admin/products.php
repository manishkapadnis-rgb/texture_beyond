<?php
$page_title = 'Products';
require_once __DIR__ . '/includes/header.php';

$action = $_GET['action'] ?? '';
$id = (int)($_GET['id'] ?? 0);

if ($action === 'delete' && $id){
    $conn->query("DELETE FROM products WHERE id=$id");
    flash('success', 'Product deleted.');
    redirect(admin_url('products.php'));
}

if ($_SERVER['REQUEST_METHOD']==='POST'){
    $data = [
        'category_id' => (int)$_POST['category_id'],
        'name' => trim($_POST['name']),
        'slug' => slugify($_POST['slug'] ?: $_POST['name']),
        'sku' => $_POST['sku'],
        'short_description' => $_POST['short_description'],
        'description' => $_POST['description'],
        'price' => $_POST['price'],
        'sale_price' => $_POST['sale_price'] !== '' ? $_POST['sale_price'] : null,
        'stock' => (int)$_POST['stock'],
        'dimensions' => $_POST['dimensions'],
        'material' => $_POST['material'],
        'is_featured' => isset($_POST['is_featured'])?1:0,
        'is_bestseller' => isset($_POST['is_bestseller'])?1:0,
        'is_new' => isset($_POST['is_new'])?1:0,
        'status' => isset($_POST['status'])?1:0,
        'meta_title' => $_POST['meta_title'],
        'meta_description' => $_POST['meta_description'],
    ];

    $img = $_POST['existing_image'] ?? null;
    if (!empty($_FILES['image']['name'])){
        $fn = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], UPLOAD_DIR . '/products/' . $fn);
        $img = $fn;
    }
    $data['image'] = $img;

    if ($id){
        $sets = []; $vals = [];
        foreach ($data as $k=>$v){ $sets[] = "$k=?"; $vals[] = $v; }
        $vals[] = $id;
        $types = str_repeat('s', count($vals));
        $stmt = $conn->prepare("UPDATE products SET ".implode(',', $sets)." WHERE id=?");
        $stmt->bind_param($types, ...$vals);
        $stmt->execute();
    } else {
        $cols = implode(',', array_keys($data));
        $ph = implode(',', array_fill(0, count($data), '?'));
        $stmt = $conn->prepare("INSERT INTO products ($cols) VALUES ($ph)");
        $types = str_repeat('s', count($data));
        $stmt->bind_param($types, ...array_values($data));
        $stmt->execute();
    }
    flash('success','Product saved.');
    redirect(admin_url('products.php'));
}

if ($action === 'edit' || $action === 'new'):
    $p = $id ? $conn->query("SELECT * FROM products WHERE id=$id")->fetch_assoc() : [];
    $cats = get_categories();
?>
<h1 class="serif text-4xl mb-8"><?= $id ? 'Edit Product' : 'New Product' ?></h1>
<form method="post" enctype="multipart/form-data" class="bg-white p-10 grid grid-cols-1 md:grid-cols-2 gap-6 max-w-5xl">
  <input class="input md:col-span-2" name="name" required placeholder="Name" value="<?= e($p['name'] ?? '') ?>"/>
  <input class="input" name="slug" placeholder="Slug (auto)" value="<?= e($p['slug'] ?? '') ?>"/>
  <input class="input" name="sku" placeholder="SKU" value="<?= e($p['sku'] ?? '') ?>"/>
  <select class="input" name="category_id" required>
    <option value="">Category</option>
    <?php while ($c = $cats->fetch_assoc()): ?>
      <option value="<?= $c['id'] ?>" <?= ($p['category_id'] ?? 0)==$c['id']?'selected':'' ?>><?= e($c['name']) ?></option>
    <?php endwhile; ?>
  </select>
  <input class="input" name="stock" type="number" placeholder="Stock" value="<?= e($p['stock'] ?? 0) ?>"/>
  <input class="input" name="price" type="number" step="0.01" required placeholder="Price" value="<?= e($p['price'] ?? '') ?>"/>
  <input class="input" name="sale_price" type="number" step="0.01" placeholder="Sale Price" value="<?= e($p['sale_price'] ?? '') ?>"/>
  <input class="input" name="dimensions" placeholder="Dimensions" value="<?= e($p['dimensions'] ?? '') ?>"/>
  <input class="input" name="material" placeholder="Material" value="<?= e($p['material'] ?? '') ?>"/>
  <input class="input md:col-span-2" name="short_description" placeholder="Short description" value="<?= e($p['short_description'] ?? '') ?>"/>
  <textarea class="input md:col-span-2" name="description" rows="5" placeholder="Description"><?= e($p['description'] ?? '') ?></textarea>
  <div class="md:col-span-2">
    <label class="label-caps block mb-2">Image</label>
    <input type="file" name="image" accept="image/*"/>
    <input type="hidden" name="existing_image" value="<?= e($p['image'] ?? '') ?>"/>
    <?php if (!empty($p['image'])): ?><img src="<?= e(product_image($p['image'])) ?>" class="w-32 mt-3"/><?php endif; ?>
  </div>
  <input class="input" name="meta_title" placeholder="Meta Title (SEO)" value="<?= e($p['meta_title'] ?? '') ?>"/>
  <input class="input" name="meta_description" placeholder="Meta Description (SEO)" value="<?= e($p['meta_description'] ?? '') ?>"/>
  <div class="md:col-span-2 flex flex-wrap gap-6">
    <label class="flex items-center gap-2"><input type="checkbox" name="is_featured" <?= !empty($p['is_featured'])?'checked':'' ?>/> Featured</label>
    <label class="flex items-center gap-2"><input type="checkbox" name="is_bestseller" <?= !empty($p['is_bestseller'])?'checked':'' ?>/> Bestseller</label>
    <label class="flex items-center gap-2"><input type="checkbox" name="is_new" <?= !empty($p['is_new'])?'checked':'' ?>/> New</label>
    <label class="flex items-center gap-2"><input type="checkbox" name="status" <?= !isset($p['status'])||$p['status']?'checked':'' ?>/> Active</label>
  </div>
  <div class="md:col-span-2 flex gap-4 mt-4"><button class="btn">Save</button><a href="<?= admin_url('products.php') ?>" class="btn-ghost">Cancel</a></div>
</form>
<?php else:
$rows = $conn->query("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id=p.category_id ORDER BY p.id DESC");
?>
<div class="flex justify-between mb-8">
  <h1 class="serif text-4xl">Products</h1>
  <a class="btn" href="?action=new">+ New Product</a>
</div>
<table>
<thead><tr><th></th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th></th></tr></thead>
<tbody>
<?php while ($r = $rows->fetch_assoc()): ?>
<tr>
  <td><img class="w-12 h-16 object-cover" src="<?= e(product_image($r['image'])) ?>"/></td>
  <td><?= e($r['name']) ?></td>
  <td><?= e($r['category_name']) ?></td>
  <td><?= money($r['sale_price'] ?: $r['price']) ?></td>
  <td><?= $r['stock'] ?></td>
  <td><?= $r['status']?'Active':'Hidden' ?></td>
  <td>
    <a class="btn-ghost" href="?action=edit&id=<?= $r['id'] ?>">Edit</a>
    <a class="btn-ghost" onclick="return confirm('Delete?')" href="?action=delete&id=<?= $r['id'] ?>">Delete</a>
  </td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
<?php endif; include __DIR__ . '/includes/footer.php'; ?>
