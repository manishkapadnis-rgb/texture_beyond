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
if (!function_exists('user_purchased_product')) {
    function user_purchased_product($uid, $pid){
        global $conn; $uid=(int)$uid; $pid=(int)$pid;
        if (!$uid || !$pid) return null;
        $r = @$conn->query("SELECT o.id FROM orders o INNER JOIN order_items oi ON oi.order_id=o.id WHERE o.user_id=$uid AND oi.product_id=$pid AND o.status IN ('processing','shipped','delivered') ORDER BY o.id DESC LIMIT 1");
        if ($r && $row = $r->fetch_assoc()) return (int)$row['id'];
        return null;
    }
}
if (!function_exists('user_review_for')) {
    function user_review_for($uid, $pid){
        global $conn; $uid=(int)$uid; $pid=(int)$pid;
        if (!$uid || !$pid) return null;
        $r = @$conn->query("SELECT * FROM reviews WHERE user_id=$uid AND product_id=$pid LIMIT 1");
        return $r ? $r->fetch_assoc() : null;
    }
}
if (!function_exists('user_initials')) {
    function user_initials($name){
        $name = trim((string)$name);
        if ($name === '') return '?';
        $parts = preg_split('/\s+/', $name);
        $s = strtoupper(substr($parts[0], 0, 1));
        if (isset($parts[1])) $s .= strtoupper(substr($parts[1], 0, 1));
        return $s;
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
if (function_exists('ensure_review_tables')) ensure_review_tables();
$gallery  = product_gallery($p);
$sizes    = product_sizes($p['id']);
$reviews  = product_reviews($p['id']);
$rsum     = review_summary($p['id']);
$cu       = current_user();
$has_purchased = $cu ? user_purchased_product($cu['id'], $p['id']) : null;
$existing_review = $cu ? user_review_for($cu['id'], $p['id']) : null;
$return_url = url('product.php?slug=' . $p['slug']);
$sold_recent = 4 + (abs(crc32((string)$p['slug'])) % 14);
include 'includes/header.php';
?>
<style>
/* Inlined PDP styles — self-contained so PDP works without style.css redeploy */
.pdp{font-family:'Hanken Grotesk',sans-serif;color:#1a1a1a;max-width:1280px;margin:0 auto;padding:32px 24px}
.pdp-crumbs{display:flex;align-items:center;gap:8px;font-size:12px;letter-spacing:.1em;text-transform:uppercase;margin-bottom:24px}
.pdp-crumbs a{color:#7a3a3a;text-decoration:none}.pdp-crumbs a:hover{text-decoration:underline}
.pdp-crumbs__current{color:#7a3a3a;font-weight:600}.pdp-crumbs span{color:#999}
.pdp-grid{display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:flex-start}
@media(max-width:900px){.pdp-grid{grid-template-columns:1fr;gap:32px}}
.pdp-gallery{display:grid;grid-template-columns:80px 1fr;gap:14px}
.pdp-thumbs{display:flex;flex-direction:column;gap:8px;max-height:640px;overflow-y:auto}
.pdp-thumb{padding:0;border:1px solid transparent;border-radius:4px;background:#f4f1ec;cursor:pointer;overflow:hidden;height:72px;transition:border-color .2s,transform .25s}
.pdp-thumb.is-active{border-color:#1a1a1a}.pdp-thumb:hover{transform:translateY(-2px)}
.pdp-thumb img{width:100%;height:100%;object-fit:cover;display:block}
.pdp-main{position:relative;background:#f4f1ec;border-radius:6px;overflow:hidden;aspect-ratio:4/5}
.pdp-main img{width:100%;height:100%;object-fit:cover;display:block}
.pdp-zoom{position:absolute;top:16px;right:16px;width:38px;height:38px;border-radius:50%;background:#fff;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 14px rgba(0,0,0,.12)}
.pdp-zoom:hover{transform:scale(1.06)}
.pdp-zoom .material-symbols-outlined{font-size:18px;color:#1a1a1a}
@media(max-width:900px){.pdp-gallery{grid-template-columns:1fr}.pdp-thumbs{flex-direction:row;max-height:none;overflow-x:auto}.pdp-thumb{min-width:64px;height:64px}}
.pdp-info{display:flex;flex-direction:column;gap:14px}
.pdp-brand{font-size:11px;letter-spacing:.18em;color:#6b6b6b;text-transform:uppercase}
.pdp-title{font-size:22px;font-weight:700;line-height:1.3;letter-spacing:.02em;margin:0;color:#1a1a1a}
.pdp-urgency{display:inline-flex;align-items:center;gap:8px;font-size:13px;color:#1a1a1a}
.pdp-urgency__icon{font-size:16px}
.pdp-price{display:flex;align-items:baseline;gap:12px;margin:6px 0 4px}
.pdp-price__sale{font-size:22px;font-weight:700;color:#1a1a1a}
.pdp-price__orig{font-size:15px;color:#999;text-decoration:line-through}
.pdp-perks{border:1px solid rgba(0,0,0,.12);border-radius:6px;margin:6px 0}
.pdp-perks__row{padding:14px 18px;border-bottom:1px dashed rgba(0,0,0,.15);font-size:13.5px;color:#1a1a1a}
.pdp-perks__row:last-child{border-bottom:none}
.pdp-viewers{display:inline-flex;align-items:center;gap:8px;background:#1a1a1a;color:#fff;padding:6px 12px;border-radius:4px;font-size:12px;align-self:flex-start}
.pdp-viewers .material-symbols-outlined{font-size:16px}
.pdp-sizes__label{font-size:13.5px;margin-bottom:8px}
.pdp-sizes__opts{display:flex;flex-wrap:wrap;gap:8px}
.pdp-size{min-width:48px;height:42px;padding:0 14px;border:1px solid rgba(0,0,0,.2);background:#fff;cursor:pointer;font-size:13px;font-weight:600;color:#1a1a1a;border-radius:4px;transition:all .2s}
.pdp-size:hover:not([disabled]){border-color:#1a1a1a}
.pdp-size.is-active{background:#1a1a1a;color:#fff;border-color:#1a1a1a}
.pdp-size[disabled]{opacity:.4;cursor:not-allowed;text-decoration:line-through}
.pdp-quick{display:flex;align-items:center;gap:18px;font-size:13px;color:#1a1a1a}
.pdp-quick--row{gap:24px}
.pdp-quick__link{background:none;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:6px;color:#1a1a1a;text-decoration:none;font-size:13px;padding:0}
.pdp-quick__link:hover{color:#7a3a3a}
.pdp-quick__link .material-symbols-outlined{font-size:18px}
.pdp-divider{border:none;border-top:1px solid rgba(0,0,0,.1);margin:8px 0}
.pdp-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.pdp-qty{display:inline-flex;align-items:center;border:1px solid rgba(0,0,0,.2);border-radius:999px;overflow:hidden;background:#fff}
.pdp-qty button{background:none;border:none;width:36px;height:44px;cursor:pointer;font-size:16px;color:#1a1a1a}
.pdp-qty button:hover{background:#f4f1ec}
.pdp-qty input{width:42px;height:44px;text-align:center;border:none;outline:none;background:transparent;font-size:14px;font-weight:600;-moz-appearance:textfield;appearance:textfield}
.pdp-qty input::-webkit-outer-spin-button,.pdp-qty input::-webkit-inner-spin-button{-webkit-appearance:none;margin:0}
.pdp-btn{display:inline-flex;align-items:center;justify-content:center;border-radius:999px;font-size:14px;font-weight:600;letter-spacing:.02em;cursor:pointer;border:1.5px solid transparent;padding:14px 28px;text-decoration:none;transition:transform .2s,background .25s,color .25s,border-color .25s,opacity .25s}
.pdp-btn--dark{background:#1a1a1a;color:#fff;flex:1;min-height:48px}
.pdp-btn--dark:hover{background:#000;transform:translateY(-1px)}
.pdp-btn--maroon{background:#5a1414;color:#fff;width:100%;min-height:50px;margin-top:10px}
.pdp-btn--maroon:hover{background:#7a1c1c}
.pdp-btn--outline-maroon{background:#fff;color:#7a1c1c;border-color:#7a1c1c}
.pdp-btn--outline-maroon:hover{background:#7a1c1c;color:#fff}
.pdp-btn--ghost{background:transparent;color:#1a1a1a;border-color:rgba(0,0,0,.2)}
.pdp-btn--ghost:hover{background:#f4f1ec}
.pdp-icon-btn{width:44px;height:44px;border-radius:50%;border:1px solid rgba(0,0,0,.2);background:#fff;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;color:#1a1a1a;transition:all .25s}
.pdp-icon-btn:hover{border-color:#1a1a1a}
.pdp-icon-btn.is-active{background:#1a1a1a;color:#fff;border-color:#1a1a1a}
.pdp-icon-btn .material-symbols-outlined{font-size:20px}
.pdp-buynow-form{margin-top:4px}
.pdp-accordion{margin-top:16px;border-top:1px solid rgba(0,0,0,.1)}
.pdp-accordion details{border-bottom:1px solid rgba(0,0,0,.1)}
.pdp-accordion summary{list-style:none;cursor:pointer;padding:18px 0;font-size:14px;font-weight:600;display:flex;align-items:center;justify-content:space-between}
.pdp-accordion summary::-webkit-details-marker{display:none}
.pdp-accordion summary::after{content:"+";font-size:22px;font-weight:300}
.pdp-accordion details[open] summary::after{content:"\2013"}
.pdp-accordion__body{padding:0 0 18px;font-size:14px;color:#444;line-height:1.65}
.pdp-meta{margin-top:10px;font-size:13px;color:#1a1a1a;display:flex;flex-direction:column;gap:4px}
.pdp-reviews{margin-top:80px;padding-top:40px;border-top:1px solid rgba(0,0,0,.1)}
.pdp-reviews__title{font-size:22px;font-weight:600;text-align:center;margin:0 0 32px}
.pdp-reviews__summary{display:grid;grid-template-columns:1fr 1.5fr auto;gap:40px;align-items:center;padding:24px 0;border-bottom:1px solid rgba(0,0,0,.08)}
@media(max-width:760px){.pdp-reviews__summary{grid-template-columns:1fr;gap:18px;text-align:center}}
.pdp-stars{display:inline-flex;align-items:center;gap:2px;font-size:18px;color:#cfd5cf}
.pdp-star.is-on{color:#1d7a5c}
.pdp-reviews__avg{margin-left:10px;font-size:14px;font-weight:600;color:#1a1a1a}
.pdp-reviews__based{font-size:13px;color:#1a1a1a;margin-top:6px;display:inline-flex;align-items:center;gap:6px}
.pdp-reviews__based::after{content:"\2713";color:#1d7a5c}
.pdp-reviews__bars{display:flex;flex-direction:column;gap:6px}
.pdp-bar-row{display:grid;grid-template-columns:auto 1fr auto;gap:12px;align-items:center;font-size:13px}
.pdp-bar-stars{display:inline-flex;font-size:13px;color:#cfd5cf}
.pdp-bar-track{position:relative;height:8px;background:#e8e8e8;border-radius:4px;overflow:hidden;min-width:160px}
.pdp-bar-fill{position:absolute;left:0;top:0;height:100%;background:#1d7a5c;border-radius:4px;transition:width .6s ease}
.pdp-bar-count{color:#7a3a3a;font-weight:600;min-width:18px;text-align:right}
.pdp-review-form{display:grid;gap:10px;margin:24px 0;padding:24px;border:1px solid rgba(0,0,0,.1);border-radius:8px;background:#fafafa}
.pdp-review-form input[type="text"],.pdp-review-form input[type="email"],.pdp-review-form textarea{padding:12px 14px;border:1px solid rgba(0,0,0,.15);border-radius:6px;background:#fff;font-family:inherit;font-size:14px;outline:none}
.pdp-review-form input:focus,.pdp-review-form textarea:focus{border-color:#1a1a1a}
.pdp-review-form__rating{font-size:24px;color:#cfd5cf;display:inline-flex;gap:4px;cursor:pointer;align-self:flex-start}
.pdp-review-form__rating .pdp-star{cursor:pointer}
.pdp-file{display:inline-flex;align-items:center;gap:8px;font-size:13px;color:#1a1a1a;cursor:pointer;border:1px dashed rgba(0,0,0,.2);padding:10px 14px;border-radius:6px;width:fit-content}
.pdp-file input[type="file"]{display:none}
.pdp-review-form__actions{display:flex;gap:10px;justify-content:flex-end}
.pdp-reviews__sort{margin:24px 0 12px;font-size:13px;color:#7a3a3a;font-weight:600}
.pdp-reviews__list{display:flex;flex-direction:column}
.pdp-reviews__empty{text-align:center;padding:40px 0;color:#666;font-size:14px}
.pdp-review{padding:20px 0;border-bottom:1px solid rgba(0,0,0,.08)}
.pdp-review__head{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px}
.pdp-review__date{font-size:12px;color:#7a3a3a}
.pdp-review__user{display:flex;align-items:center;gap:10px;margin-bottom:8px}
.pdp-review__avatar{width:32px;height:32px;border-radius:50%;background:#f0ebe4;display:inline-flex;align-items:center;justify-content:center;color:#7a3a3a}
.pdp-review__avatar .material-symbols-outlined{font-size:20px}
.pdp-review__title{margin:6px 0;font-size:15px;font-weight:600}
.pdp-review__body{font-size:14px;line-height:1.6;color:#1a1a1a;margin:0 0 10px}
.pdp-review__photo{display:inline-block;width:80px;height:80px;border-radius:6px;overflow:hidden;border:1px solid rgba(0,0,0,.1)}
.pdp-review__photo img{width:100%;height:100%;object-fit:cover}
.pdp-review__photos{display:flex;gap:8px;flex-wrap:wrap;margin-top:6px}
.pdp-review__avatar--initials{font-size:13px;font-weight:700;letter-spacing:.04em;color:#fff;background:#1a1a1a}
.pdp-review__user-meta{display:flex;flex-direction:column;gap:2px}
.pdp-verified-badge{display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:600;color:#1d7a5c;background:rgba(29,122,92,.08);padding:3px 8px;border-radius:999px;width:fit-content;letter-spacing:.02em}
.pdp-verified-badge .material-symbols-outlined{font-size:14px}
.pdp-verified-badge--sm{font-size:10px;padding:2px 7px}
.pdp-review-gate{display:inline-flex;align-items:center;font-size:12px;color:#7a3a3a;background:#fff;border:1px dashed rgba(122,58,58,.4);padding:10px 16px;border-radius:999px}
.pdp-review-gate--ok{color:#1d7a5c;border-color:rgba(29,122,92,.4);background:rgba(29,122,92,.06)}
.pdp-review-form__head{display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;margin-bottom:4px}
.pdp-review-form__as{font-size:12px;color:#6b6b6b}
</style>
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
          <?php if (!$cu): ?>
            <a href="<?= e(url('login.php?return=' . urlencode($return_url))) ?>" class="pdp-btn pdp-btn--outline-maroon">Login to write a review</a>
          <?php elseif ($existing_review): ?>
            <span class="pdp-review-gate pdp-review-gate--ok">✓ You reviewed this product</span>
          <?php elseif ($has_purchased): ?>
            <button type="button" class="pdp-btn pdp-btn--outline-maroon" id="pdp-write-review">Write a review</button>
          <?php else: ?>
            <span class="pdp-review-gate">Only verified buyers can review</span>
          <?php endif; ?>
        </div>
      </div>

      <?php if ($cu && $has_purchased && !$existing_review): ?>
      <form class="pdp-review-form" id="pdp-review-form" enctype="multipart/form-data" hidden>
        <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>"/>
        <div class="pdp-review-form__head">
          <span class="pdp-verified-badge"><span class="material-symbols-outlined">verified</span> Verified Purchase</span>
          <span class="pdp-review-form__as">Posting as <strong><?= e($cu['name']) ?></strong></span>
        </div>
        <div class="pdp-review-form__rating" data-rating="5">
          <?php for ($i=1;$i<=5;$i++): ?><span class="pdp-star is-on" data-val="<?= $i ?>">★</span><?php endfor; ?>
          <input type="hidden" name="rating" value="5"/>
        </div>
        <input type="text" name="title" placeholder="Review title (optional)"/>
        <textarea name="body" rows="4" placeholder="Share your experience…*" required minlength="10"></textarea>
        <label class="pdp-file"><span class="material-symbols-outlined">image</span> Add photos (up to 4)
          <input type="file" name="images[]" accept="image/*" multiple/>
        </label>
        <div class="pdp-review-form__actions">
          <button type="button" class="pdp-btn pdp-btn--ghost" id="pdp-review-cancel">Cancel</button>
          <button type="submit" class="pdp-btn pdp-btn--dark">Submit Review</button>
        </div>
      </form>
      <?php endif; ?>

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
              <span class="pdp-review__date"><?= e(date('M j, Y', strtotime($rv['created_at']))) ?></span>
            </div>
            <div class="pdp-review__user">
              <span class="pdp-review__avatar pdp-review__avatar--initials"><?= e(user_initials($rv['name'])) ?></span>
              <div class="pdp-review__user-meta">
                <strong><?= e($rv['name']) ?></strong>
                <?php if (!empty($rv['verified'])): ?>
                  <span class="pdp-verified-badge pdp-verified-badge--sm"><span class="material-symbols-outlined">verified</span> Verified Purchase</span>
                <?php endif; ?>
              </div>
            </div>
            <?php if (!empty($rv['title'])): ?><h4 class="pdp-review__title"><?= e($rv['title']) ?></h4><?php endif; ?>
            <p class="pdp-review__body"><?= nl2br(e($rv['body'])) ?></p>
            <?php
              $imgs = !empty($rv['images']) ? $rv['images'] : ([] + (!empty($rv['image']) ? [$rv['image']] : []));
              if ($imgs):
            ?>
              <div class="pdp-review__photos">
                <?php foreach ($imgs as $im): ?>
                  <a href="<?= e(UPLOAD_URL . '/reviews/' . $im) ?>" target="_blank" class="pdp-review__photo">
                    <img src="<?= e(UPLOAD_URL . '/reviews/' . $im) ?>" alt=""/>
                  </a>
                <?php endforeach; ?>
              </div>
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
    const submitBtn = reviewForm.querySelector('button[type="submit"]');
    if (submitBtn) { submitBtn.disabled = true; submitBtn.dataset.t = submitBtn.textContent; submitBtn.textContent = 'Submitting…'; }
    try {
      const r = await fetch(SITE_URL + '/ajax/review.php', { method:'POST', body: fd, credentials:'same-origin' });
      const d = await r.json();
      if (d.login_required) {
        window.TBToast?.show(d.msg || 'Please login', 'info');
        setTimeout(() => { location.href = d.login_url || (SITE_URL + '/login.php'); }, 700);
        return;
      }
      window.TBToast?.show(d.msg, d.ok ? 'success' : 'error');
      if (d.ok) setTimeout(() => location.reload(), 900);
    } catch(err) {
      window.TBToast?.show('Could not submit review', 'error');
    } finally {
      if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = submitBtn.dataset.t || 'Submit Review'; }
    }
  });
})();
</script>

<?php include 'includes/footer.php'; ?>
