<?php
require_once 'includes/functions.php';
$slug = $_GET['slug'] ?? '';
$stmt = $conn->prepare("SELECT p.*, c.name AS category_name, c.slug AS category_slug FROM products p LEFT JOIN categories c ON c.id=p.category_id WHERE p.slug=? AND p.status=1");
$stmt->bind_param('s', $slug); $stmt->execute();
$p = $stmt->get_result()->fetch_assoc();
if (!$p){ http_response_code(404); echo 'Not Found'; exit; }
$page_title = ($p['meta_title'] ?: $p['name']) . ' | Texture & Beyond';
$meta_description = $p['meta_description'] ?: $p['short_description'];
$related = $conn->query("SELECT * FROM products WHERE status=1 AND category_id={$p['category_id']} AND id<>{$p['id']} LIMIT 4");
include 'includes/header.php';
?>
<section class="pt-40 pb-20 px-margin-mobile md:px-margin-desktop bg-surface">
  <div class="max-w-container-max mx-auto">
    <nav class="font-label-caps text-label-caps text-on-surface-variant mb-12">
      <a href="<?= url('shop.php') ?>" class="hover:text-secondary">Shop</a> /
      <a href="<?= url('shop.php?category=' . $p['category_slug']) ?>" class="hover:text-secondary"><?= e($p['category_name']) ?></a>
    </nav>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-16">
      <div class="gsap-fade">
        <div class="overflow-hidden aspect-[4/5] bg-surface-container-high">
          <img class="w-full h-full object-cover" src="<?= e(product_image($p['image'])) ?>" alt="<?= e($p['name']) ?>"/>
        </div>
      </div>
      <div class="gsap-fade flex flex-col justify-center">
        <span class="font-label-caps text-label-caps text-secondary mb-4 uppercase"><?= e($p['category_name']) ?></span>
        <h1 class="font-display-md text-headline-lg md:text-display-md mb-6"><?= e($p['name']) ?></h1>
        <div class="flex items-center gap-4 mb-8">
          <?php if ($p['sale_price']): ?>
            <span class="font-display-md text-[32px]"><?= money($p['sale_price']) ?></span>
            <span class="font-body-lg text-on-surface-variant line-through"><?= money($p['price']) ?></span>
          <?php else: ?>
            <span class="font-display-md text-[32px]"><?= money($p['price']) ?></span>
          <?php endif; ?>
        </div>
        <p class="font-body-lg text-body-lg text-on-surface-variant mb-10 leading-relaxed"><?= nl2br(e($p['description'] ?: $p['short_description'])) ?></p>

        <div class="grid grid-cols-2 gap-6 mb-10 border-y border-outline-variant/30 py-6">
          <?php if ($p['dimensions']): ?><div><div class="font-label-caps text-label-caps text-secondary mb-2">Dimensions</div><div class="font-body-md"><?= e($p['dimensions']) ?></div></div><?php endif; ?>
          <?php if ($p['material']): ?><div><div class="font-label-caps text-label-caps text-secondary mb-2">Material</div><div class="font-body-md"><?= e($p['material']) ?></div></div><?php endif; ?>
        </div>

        <form action="<?= url('cart-action.php') ?>" method="post" class="add-to-cart-form flex items-center gap-4" data-product-id="<?= (int)$p['id'] ?>">
          <input type="hidden" name="action" value="add">
          <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
          <div class="flex items-center border border-primary rounded-sm overflow-hidden bg-surface-container-lowest">
            <button type="button" class="qty-decrease px-4 py-3" aria-label="Decrease quantity">−</button>
            <input name="qty" type="number" value="1" min="1" max="<?= (int)$p['stock'] ?>" class="w-16 text-center bg-transparent border-none outline-none focus:ring-0"/>
            <button type="button" class="qty-increase px-4 py-3" aria-label="Increase quantity">+</button>
          </div>
          <button type="submit" class="btn-primary flex-grow" <?= $p['stock']>0 ? '' : 'disabled' ?>><?= $p['stock']>0?'Add to Cart':'Out of Stock' ?></button>
          <button type="button" onclick="toggleWishlist(<?= $p['id'] ?>, this)" class="material-symbols-outlined p-4 border border-primary <?= in_wishlist($p['id'])?'bg-primary text-on-primary':'' ?>" title="Wishlist">favorite</button>
        </form>
        <script>
          function toggleWishlist(id, btn){
            fetch(SITE_URL+'/ajax/wishlist.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'id='+id})
              .then(r=>r.json()).then(d=>{
                if (d.login_required){ location.href = SITE_URL+'/login.php'; return; }
                btn.classList.toggle('bg-primary', d.state==='added');
                btn.classList.toggle('text-on-primary', d.state==='added');
              });
          }
        </script>
      </div>
    </div>

    <?php if ($related->num_rows): ?>
    <div class="mt-section-gap">
      <h2 class="font-display-md text-headline-lg mb-12 text-center">You May Also Love</h2>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-gutter">
        <?php while ($r = $related->fetch_assoc()): ?>
          <div class="group block rounded-2xl border border-outline-variant/20 bg-surface-container-lowest p-4 shadow-sm hover:shadow-xl transition-all duration-400">
            <a href="<?= url('product.php?slug=' . $r['slug']) ?>" class="group zoom-hover block">
              <div class="overflow-hidden mb-4 aspect-[4/5] bg-surface-container-high rounded-xl">
                <img class="w-full h-full object-cover" src="<?= e(product_image($r['image'])) ?>" alt="<?= e($r['name']) ?>"/>
              </div>
              <h3 class="font-display-md text-[20px] mb-1"><?= e($r['name']) ?></h3>
              <span class="font-body-md font-semibold"><?= money($r['sale_price'] ?: $r['price']) ?></span>
            </a>
            <form action="<?= url('cart-action.php') ?>" method="post" class="add-to-cart-form mt-4 flex items-center justify-between gap-3" data-product-id="<?= (int)$r['id'] ?>">
              <input type="hidden" name="action" value="add"/>
              <input type="hidden" name="id" value="<?= (int)$r['id'] ?>"/>
              <input type="hidden" name="qty" value="1"/>
              <button type="submit" class="btn-primary text-[11px] px-4 py-3 flex-1">Add to cart</button>
              <a href="<?= url('product.php?slug=' . $r['slug']) ?>" class="font-label-caps text-label-caps hover:text-secondary">View</a>
            </form>
          </div>
        <?php endwhile; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php include 'includes/footer.php'; ?>
