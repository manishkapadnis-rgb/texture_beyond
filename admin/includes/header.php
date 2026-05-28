<?php
require_once __DIR__ . '/../../includes/functions.php';
require_admin();
require_once __DIR__ . '/crud-helpers.php';
$adm = $_SESSION['admin_id'] ?? null;
$page_title = $page_title ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title><?= e($page_title) ?> | Texture &amp; Beyond Admin</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Bodoni+Moda:wght@400;500&family=Hanken+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined&display=swap" rel="stylesheet"/>
<style>
body{font-family:'Hanken Grotesk';background:#fff8f3;color:#201b13}
h1,h2,h3,.serif{font-family:'Bodoni Moda'}
.label-caps{font-size:12px;font-weight:600;letter-spacing:.15em;text-transform:uppercase}
.material-symbols-outlined{font-family:'Material Symbols Outlined';font-size:20px;line-height:1;vertical-align:middle}
.sidebar a.active{background:#000;color:#fff}
.btn{background:#000;color:#fff;padding:10px 20px;font-size:12px;font-weight:600;letter-spacing:.15em;text-transform:uppercase;display:inline-block;border-radius:2px}
.btn-ghost{border:1px solid #000;color:#000;padding:10px 20px;font-size:12px;font-weight:600;letter-spacing:.15em;text-transform:uppercase;display:inline-block;border-radius:2px}
.input{border:1px solid #c4c7c7;padding:10px 14px;width:100%;background:#fff;border-radius:2px}
.input:focus{outline:none;border-color:#000}
table{width:100%;border-collapse:collapse;background:#fff}
th,td{padding:14px 16px;text-align:left;border-bottom:1px solid #ece1d4}
th{background:#f8ecdf;font-size:11px;letter-spacing:.15em;text-transform:uppercase}
.flash{padding:14px 22px;margin-bottom:20px;background:#1A1A1A;color:#fff;border-radius:2px}
.flash.error{background:#ba1a1a}
</style>
</head>
<body class="min-h-screen flex">
<aside class="w-64 bg-[#1c1b1b] text-white min-h-screen p-6 flex flex-col">
  <a href="<?= admin_url() ?>" class="serif text-[22px] mb-12">Texture & Beyond</a>
  <nav class="flex flex-col gap-1 flex-grow sidebar">
    <?php $cur=basename($_SERVER['PHP_SELF']);
      $links=[ 'index.php'=>['dashboard','Dashboard'],
        'products.php'=>['inventory_2','Products'],
        'categories.php'=>['category','Categories'],
        'orders.php'=>['receipt_long','Orders'],
        'coupons.php'=>['local_offer','Coupons'],
        'users.php'=>['group','Users'],
        'enquiries.php'=>['forum','Enquiries'],
        'contacts.php'=>['mail','Contacts'],
        'hero-banners.php'=>['view_carousel','Hero Slider'],
        'banners.php'=>['image','Promo Banners'],
        'gallery-admin.php'=>['photo_library','Gallery'],
        'testimonials.php'=>['format_quote','Testimonials'],
        'blogs.php'=>['edit_note','Blog'],
        'pages.php'=>['description','Pages'],
        'menu.php'=>['menu','Header Menu'],
        'seo.php'=>['search','SEO'],
        'settings.php'=>['settings','Settings']];
      foreach ($links as $f=>$i): ?>
      <a href="<?= admin_url($f) ?>" class="<?= $cur==$f?'active':'' ?> flex items-center gap-3 px-4 py-3 rounded text-[13px] hover:bg-white/10 transition">
        <span class="material-symbols-outlined"><?= $i[0] ?></span> <?= $i[1] ?>
      </a>
    <?php endforeach; ?>
  </nav>
  <a href="<?= admin_url('logout.php') ?>" class="flex items-center gap-3 px-4 py-3 rounded text-[13px] hover:bg-white/10 mt-8">
    <span class="material-symbols-outlined">logout</span> Sign Out
  </a>
</aside>
<main class="flex-1 p-10">
<?php if ($m = flash('success')): ?><div class="flash"><?= e($m) ?></div><?php endif; ?>
<?php if ($m = flash('error')): ?><div class="flash error"><?= e($m) ?></div><?php endif; ?>
