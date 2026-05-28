<?php
require_once 'includes/functions.php';

// Self-healing fallbacks so PDP works even if functions.php / reviews
// table haven't been deployed yet. Safe to remove once both are live.
if (!function_exists('product_gallery')) {
    function product_gallery($p){
        $imgs = [];
        if (!empty($p['image'])) $imgs[] = product_image($p['image']);
        if (!empty($p['gallery'])) {
            foreach (preg_split('/[,\n]+/', $p['gallery']) as $g) {
                $g = trim($g); if ($g !== '') $imgs[] = product_image($g);
            }
        }
        return array_values(array_unique($imgs));
    }
}
if (!function_exists('product_sizes')) {
    function product_sizes($pid){
        global $conn; $pid = (int)$pid; $rows = [];
        $r = @$conn->query("SELECT value, stock FROM product_variants WHERE product_id=$pid AND name='size' ORDER BY id");
        if ($r) while ($row = $r->fetch_assoc()) $rows[] = $row;
        return $rows;
    }
}
if (!function_exists('product_reviews')) {
    function product_reviews($pid){
        global $conn; $pid = (int)$pid; $rows = [];
        $r = @$conn->query("SELECT * FROM reviews WHERE product_id=$pid AND status=1 ORDER BY id DESC");
        if ($r) while ($row = $r->fetch_assoc()) $rows[] = $row;
        return $rows;
    }
}
if (!function_exists('review_summary')) {
    function review_summary($pid){
        global $conn; $pid = (int)$pid;
        $sum = ['count'=>0,'avg'=>0,'breakdown'=>[5=>0,4=>0,3=>0,2=>0,1=>0]];
        $r = @$conn->query("SELECT rating, COUNT(*) c FROM reviews WHERE product_id=$pid AND status=1 GROUP BY rating");
        if (!$r) return $sum;
        $total=0; $weighted=0;
        while ($row = $r->fetch_assoc()) {
            $rt=(int)$row['rating']; $c=(int)$row['c'];
            if (isset($sum['breakdown'][$rt])) $sum['breakdown'][$rt] = $c;
            $total += $c; $weighted += $rt * $c;
        }
        $sum['count'] = $total;
        $sum['avg']   = $total ? round($weighted / $total, 2) : 0;
        return $sum;
    }
}
// Auto-create reviews table on first run so the migration is optional
@$conn->query("CREATE TABLE IF NOT EXISTS `reviews` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `user_id` INT DEFAULT NULL,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(190) DEFAULT NULL,
  `rating` TINYINT NOT NULL DEFAULT 5,
  `title` VARCHAR(190) DEFAULT NULL,
  `body` TEXT NOT NULL,
  `image` VARCHAR(190) DEFAULT NULL,
  `status` TINYINT NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  KEY (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$slug = $_GET['slug'] ?? '';
$stmt = $conn->prepare("SELECT p.*, c.name AS category_name, c.slug AS category_slug FROM products p LEFT JOIN categories c ON c.id=p.category_id WHERE p.slug=? AND p.status=1");
$stmt->bind_param('s', $slug); $stmt->execute();
$p = $stmt->get_result()->fetch_assoc();
if (!$p){ http_response_code(404); echo 'Not Found'; exit; }
$page_title = ($p['meta_title'] ?: $p['name']) . ' | Texture & Beyond';
$meta_description = $p['meta_description'] ?: $p['short_description'];
$related = $conn->query("SELECT * FROM products WHERE status=1 AND category_id={$p['category_id']} AND id<>{$p['id']} LIMIT 4");
$gallery  = product_gallery($p);
$sizes    = product_sizes($p['id']);
$reviews  = product_reviews($p['id']);
$rsum     = review_summary($p['id']);
$sold_recent = 4 + (abs(crc32((string)$p['slug'])) % 14);
include 'includes/header.php';
?>
<section class="pt-32 pb-16 px-margin-mobile md:px-margin-desktop bg-surface pdp">
  <div class="max-w-container-max mx-auto">
    <nav class="pdp-crumbs">
      <a href="<?= url('index.php') ?>">Home</a>
      <span>/</span>
      <a href="<?= url('shop.php?category=' . $p['category_slug']) ?>" class="pdp-crumbs__current"><?= e(strtoupper($p['name'])) ?></a>
    </nav>

    <div class="pdp-grid">
      <!-- Gallery -->
      <div class="pdp-gallery">
        <div class="pdp-thumbs" id="pdp-thumbs">
          <?php foreach ($gallery as $i => $g): ?>
            <button type="button" class="pdp-thumb<?= $i === 0 ? ' is-active' : '' ?>" data-src="<?= e($g) ?>" aria-label="Image <?= $i+1 ?>">
              <img src="<?= e($g) ?>" alt=""/>
            </button>
          <?php endforeach; ?>
        </div>
        <div class="pdp-main">
          <img id="pdp-main-img" src="<?= e($gallery[0] ?? product_image($p['image'])) ?>" alt="<?= e($p['name']) ?>"/>
          <button type="button" class="pdp-zoom" id="pdp-zoom" aria-label="Zoom image">
            <span class="material-symbols-outlined">zoom_out_map</span>
          </button>
        </div>
      </div>

      <!-- Info -->
      <div class="pdp-info">
        <span class="pdp-brand"><?= e(strtoupper($p['category_name'] ?: 'NIIBHZ')) ?></span>
        <h1 class="pdp-title"><?= e(strtoupper($p['name'])) ?></h1>

        <div class="pdp-urgency">
          <span class="pdp-urgency__icon">🔥</span>
          <span><strong><?= $sold_recent ?></strong> sold in last 14 hours</span>
        </div>

        <div class="pdp-price">
          <?php if ($p['sale_price']): ?>
            <span class="pdp-price__sale"><?= money($p['sale_price']) ?></span>
            <span class="pdp-price__orig"><?= money($p['price']) ?></span>
          <?php else: ?>
            <span class="pdp-price__sale"><?= money($p['price']) ?></span>
          <?php endif; ?>
        </div>

        <div class="pdp-perks">
          <div class="pdp-perks__row"><span>Free Shipping</span></div>
          <div class="pdp-perks__row"><span>Free Returns</span></div>
        </div>

        <div class="pdp-viewers">
          <span class="material-symbols-outlined">visibility</span>
          <span><strong id="pdp-viewers-n">26</strong> peoples are viewing this right now</span>
        </div>

        <?php if (!empty($sizes)): ?>
        <div class="pdp-sizes">
          <div class="pdp-sizes__label">Size: <strong id="pdp-size-current"><?= e($sizes[0]['value']) ?></strong></div>
          <div class="pdp-sizes__opts" role="radiogroup" aria-label="Size">
            <?php foreach ($sizes as $i => $sz): ?>
              <button type="button" class="pdp-size<?= $i === 0 ? ' is-active' : '' ?>" data-val="<?= e($sz['value']) ?>" <?= (int)$sz['stock'] <= 0 ? 'disabled' : '' ?>><?= e($sz['value']) ?></button>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <div class="pdp-quick">
          <a href="#" class="pdp-quick__link"><span class="material-symbols-outlined">straighten</span> <u>Size Guide</u></a>
        </div>
        <div class="pdp-quick pdp-quick--row">
          <a href="<?= url('contact.php') ?>" class="pdp-quick__link"><span class="material-symbols-outlined">help</span> Ask a question</a>
          <button type="button" class="pdp-quick__link" id="pdp-share"><span class="material-symbols-outlined">share</span> Share</button>
        </div>

        <hr class="pdp-divider"/>

        <form action="<?= url('cart-action.php') ?>" method="post" class="add-to-cart-form pdp-actions" data-product-id="<?= (int)$p['id'] ?>" id="pdp-form">
          <input type="hidden" name="action" value="add">
          <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
          <input type="hidden" name="size" id="pdp-size-input" value="<?= e($sizes[0]['value'] ?? '') ?>">
          <div class="pdp-qty">
            <button type="button" class="qty-decrease" aria-label="Decrease">−</button>
            <input name="qty" type="number" value="1" min="1" max="<?= (int)$p['stock'] ?>"/>
            <button type="button" class="qty-increase" aria-label="Increase">+</button>
          </div>
          <button type="submit" class="pdp-btn pdp-btn--dark" <?= $p['stock']>0 ? '' : 'disabled' ?>><?= $p['stock']>0 ? 'Add to Cart' : 'Out of Stock' ?></button>
          <button type="button" onclick="toggleWishlist(<?= $p['id'] ?>, this)" class="pdp-icon-btn <?= in_wishlist($p['id'])?'is-active':'' ?>" aria-label="Wishlist"><span class="material-symbols-outlined">favorite</span></button>
          <button type="button" class="pdp-icon-btn" aria-label="Compare"><span class="material-symbols-outlined">compare_arrows</span></button>
        </form>

        <?php if ($p['stock'] > 0): ?>
        <form action="<?= url('cart-action.php') ?>" method="post" class="pdp-buynow-form">
          <input type="hidden" name="action" value="add">
          <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
          <input type="hidden" name="qty" value="1">
          <input type="hidden" name="redirect" value="<?= e(url('checkout.php')) ?>">
          <button type="submit" class="pdp-btn pdp-btn--maroon">Buy it now</button>
        </form>
        <?php endif; ?>

        <div class="pdp-accordion" id="pdp-accordion">
          <details open><summary>Description</summary>
            <div class="pdp-accordion__body"><?= nl2br(e($p['description'] ?: $p['short_description'])) ?>
              <?php if ($p['dimensions'] || $p['material']): ?>
              <div class="pdp-meta">
                <?php if ($p['dimensions']): ?><div><strong>Dimensions:</strong> <?= e($p['dimensions']) ?></div><?php endif; ?>
                <?php if ($p['material']): ?><div><strong>Material:</strong> <?= e($p['material']) ?></div><?php endif; ?>
              </div>
              <?php endif; ?>
            </div>
          </details>
          <details><summary>Shipping and Returns</summary>
            <div class="pdp-accordion__body">
              <p>Free shipping across India on all orders. Dispatched within 2–3 business days.</p>
              <p>Easy 7-day returns. Items must be unused with original packaging.</p>
            </div>
          </details>
          <details><summary>Return Policies</summary>
            <div class="pdp-accordion__body">
              <p>Initiate a return from your account orders page. Refunds are processed within 5–7 business days after inspection.</p>
            </div>
          </details>
        </div>

        <script>
          function toggleWishlist(id, btn){
            fetch(SITE_URL+'/ajax/wishlist.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'id='+id})
              .then(r=>r.json()).then(d=>{
                if (d.login_required){ location.href = SITE_URL+'/login.php'; return; }
                btn.classList.toggle('is-active', d.state==='added');
              });
          }
        </script>
      </div>
    </div>

    <!-- Customer Reviews -->
    <section class="pdp-reviews" id="pdp-reviews">
      <h2 class="pdp-reviews__title">Customer Reviews</h2>
      <div class="pdp-reviews__summary">
        <div class="pdp-reviews__score">
          <div class="pdp-stars" aria-label="<?= e($rsum['avg']) ?> out of 5">
            <?php for ($i=1; $i<=5; $i++): ?>
              <span class="pdp-star<?= $i <= round($rsum['avg']) ? ' is-on' : '' ?>">★</span>
            <?php endfor; ?>
            <span class="pdp-reviews__avg"><?= number_format($rsum['avg'], 2) ?> out of 5</span>
          </div>
          <p class="pdp-reviews__based">Based on <?= (int)$rsum['count'] ?> review<?= $rsum['count'] == 1 ? '' : 's' ?></p>
        </div>
        <div class="pdp-reviews__bars">
          <?php for ($s=5; $s>=1; $s--): $c = (int)$rsum['breakdown'][$s]; $pct = $rsum['count'] ? ($c / $rsum['count']) * 100 : 0; ?>
          <div class="pdp-bar-row">
            <span class="pdp-bar-stars">
              <?php for ($i=1;$i<=5;$i++): ?><span class="pdp-star<?= $i <= $s ? ' is-on' : '' ?>">★</span><?php endfor; ?>
            </span>
            <span class="pdp-bar-track"><span class="pdp-bar-fill" style="width:<?= $pct ?>%"></span></span>
            <span class="pdp-bar-count"><?= $c ?></span>
          </div>
          <?php endfor; ?>
        </div>
        <div class="pdp-reviews__write">
          <button type="button" class="pdp-btn pdp-btn--outline-maroon" id="pdp-write-review">Write a review</button>
        </div>
      </div>

      <form class="pdp-review-form" id="pdp-review-form" enctype="multipart/form-data" hidden>
        <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>"/>
        <div class="pdp-review-form__rating" data-rating="5">
          <?php for ($i=1;$i<=5;$i++): ?><span class="pdp-star is-on" data-val="<?= $i ?>">★</span><?php endfor; ?>
          <input type="hidden" name="rating" value="5"/>
        </div>
        <input type="text" name="name" placeholder="Your name *" required/>
        <input type="email" name="email" placeholder="Email (optional)"/>
        <input type="text" name="title" placeholder="Review title"/>
        <textarea name="body" rows="4" placeholder="Share your experience…*" required></textarea>
        <label class="pdp-file"><span class="material-symbols-outlined">image</span> Add photo
          <input type="file" name="image" accept="image/*"/>
        </label>
        <div class="pdp-review-form__actions">
          <button type="button" class="pdp-btn pdp-btn--ghost" id="pdp-review-cancel">Cancel</button>
          <button type="submit" class="pdp-btn pdp-btn--dark">Submit Review</button>
        </div>
      </form>

      <div class="pdp-reviews__sort">
        <label>Most Recent ▾</label>
      </div>

      <div class="pdp-reviews__list">
        <?php if (empty($reviews)): ?>
          <p class="pdp-reviews__empty">No reviews yet. Be the first to share your experience.</p>
        <?php else: foreach ($reviews as $rv): ?>
          <article class="pdp-review">
            <div class="pdp-review__head">
              <div class="pdp-stars">
                <?php for ($i=1;$i<=5;$i++): ?><span class="pdp-star<?= $i <= (int)$rv['rating'] ? ' is-on' : '' ?>">★</span><?php endfor; ?>
              </div>
              <span class="pdp-review__date"><?= e(date('m/d/Y', strtotime($rv['created_at']))) ?></span>
            </div>
            <div class="pdp-review__user">
              <span class="pdp-review__avatar"><span class="material-symbols-outlined">person</span></span>
              <strong><?= e($rv['name']) ?></strong>
            </div>
            <?php if ($rv['title']): ?><h4 class="pdp-review__title"><?= e($rv['title']) ?></h4><?php endif; ?>
            <p class="pdp-review__body"><?= nl2br(e($rv['body'])) ?></p>
            <?php if ($rv['image']): ?>
              <a href="<?= e(UPLOAD_URL . '/reviews/' . $rv['image']) ?>" target="_blank" class="pdp-review__photo">
                <img src="<?= e(UPLOAD_URL . '/reviews/' . $rv['image']) ?>" alt=""/>
              </a>
            <?php endif; ?>
          </article>
        <?php endforeach; endif; ?>
      </div>
    </section>

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

<script>
(function(){
  // Gallery
  document.querySelectorAll('.pdp-thumb').forEach((t) => {
    t.addEventListener('click', () => {
      document.querySelectorAll('.pdp-thumb').forEach((x) => x.classList.remove('is-active'));
      t.classList.add('is-active');
      const img = document.getElementById('pdp-main-img');
      if (img) img.src = t.dataset.src;
    });
  });
  // Zoom
  const zoom = document.getElementById('pdp-zoom');
  if (zoom) zoom.addEventListener('click', () => {
    const src = document.getElementById('pdp-main-img').src;
    const w = window.open(src, '_blank'); if (w) w.focus();
  });
  // Size selector
  document.querySelectorAll('.pdp-size').forEach((b) => {
    b.addEventListener('click', () => {
      document.querySelectorAll('.pdp-size').forEach((x) => x.classList.remove('is-active'));
      b.classList.add('is-active');
      const v = b.dataset.val;
      const cur = document.getElementById('pdp-size-current'); if (cur) cur.textContent = v;
      const inp = document.getElementById('pdp-size-input'); if (inp) inp.value = v;
    });
  });
  // Live viewers
  let n = 20 + Math.floor(Math.random() * 30);
  const vn = document.getElementById('pdp-viewers-n');
  if (vn) {
    vn.textContent = n;
    setInterval(() => { n += Math.floor(Math.random() * 5) - 2; if (n < 8) n = 8; if (n > 60) n = 60; vn.textContent = n; }, 5000);
  }
  // Share
  const sh = document.getElementById('pdp-share');
  if (sh) sh.addEventListener('click', async () => {
    try {
      if (navigator.share) await navigator.share({ title: document.title, url: location.href });
      else { await navigator.clipboard.writeText(location.href); window.TBToast?.show('Link copied to clipboard', 'success'); }
    } catch(e){}
  });
  // Review form toggle
  const writeBtn = document.getElementById('pdp-write-review');
  const reviewForm = document.getElementById('pdp-review-form');
  const cancelBtn = document.getElementById('pdp-review-cancel');
  if (writeBtn && reviewForm) writeBtn.addEventListener('click', () => { reviewForm.hidden = !reviewForm.hidden; if (!reviewForm.hidden) reviewForm.scrollIntoView({behavior:'smooth', block:'center'}); });
  if (cancelBtn) cancelBtn.addEventListener('click', () => { reviewForm.hidden = true; });
  // Star picker
  const rateWrap = reviewForm?.querySelector('.pdp-review-form__rating');
  if (rateWrap) {
    const stars = rateWrap.querySelectorAll('.pdp-star');
    const hidden = rateWrap.querySelector('input[name="rating"]');
    stars.forEach((s) => s.addEventListener('click', () => {
      const v = parseInt(s.dataset.val, 10);
      hidden.value = v;
      stars.forEach((x, i) => x.classList.toggle('is-on', i < v));
    }));
  }
  // Submit review
  if (reviewForm) reviewForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(reviewForm);
    try {
      const r = await fetch(SITE_URL + '/ajax/review.php', { method:'POST', body: fd, credentials:'same-origin' });
      const d = await r.json();
      window.TBToast?.show(d.msg, d.ok ? 'success' : 'error');
      if (d.ok) setTimeout(() => location.reload(), 900);
    } catch(err) { window.TBToast?.show('Could not submit review', 'error'); }
  });
})();
</script>

<?php include 'includes/footer.php'; ?>
