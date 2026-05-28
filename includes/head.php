<?php
$page_title = $page_title ?? 'Texture & Beyond | Modern Indian Art & Decor';
$meta_description = $meta_description ?? 'Modern handmade texture art crafted to transform your walls. Luxury Indian art and decor.';
$meta_keywords = $meta_keywords ?? 'texture art, indian art, luxury decor, handmade wall art, plaster art';
?>
<!DOCTYPE html>
<html class="scroll-smooth" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title><?= e($page_title) ?></title>
<meta name="description" content="<?= e($meta_description) ?>"/>
<meta name="keywords" content="<?= e($meta_keywords) ?>"/>
<meta property="og:title" content="<?= e($page_title) ?>"/>
<meta property="og:description" content="<?= e($meta_description) ?>"/>
<meta property="og:type" content="website"/>
<link rel="canonical" href="<?= e(SITE_URL . $_SERVER['REQUEST_URI']) ?>"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Bodoni+Moda:ital,opsz,wght@0,6..96,400..900;1,6..96,400..900&family=Hanken+Grotesk:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js" defer></script>
<script>
tailwind.config = {
  darkMode: "class",
  theme: {
    extend: {
      colors: {
        "surface-container":"#f8ecdf","secondary":"#775a19","inverse-primary":"#c8c6c5",
        "secondary-fixed":"#ffdea5","on-secondary-container":"#785a1a","background":"#fff8f3",
        "surface-container-low":"#fef2e5","outline":"#747878","surface-container-highest":"#ece1d4",
        "inverse-on-surface":"#fbefe2","on-surface-variant":"#444748","surface":"#fff8f3",
        "on-primary":"#ffffff","secondary-container":"#fed488","surface-container-high":"#f2e6da",
        "inverse-surface":"#362f27","outline-variant":"#c4c7c7","secondary-fixed-dim":"#e9c176",
        "on-surface":"#201b13","tertiary":"#000000","surface-dim":"#e4d8cc",
        "primary":"#000000","on-secondary":"#ffffff","primary-container":"#1c1b1b",
        "surface-container-lowest":"#ffffff","error":"#ba1a1a"
      },
      borderRadius: {"DEFAULT":"0.125rem","lg":"0.25rem","xl":"0.5rem","full":"0.75rem"},
      spacing: {"gutter":"32px","container-max":"1440px","section-gap":"160px","margin-mobile":"24px","unit":"8px","margin-desktop":"80px"},
      fontFamily: {
        "body-md":["Hanken Grotesk"],"body-lg":["Hanken Grotesk"],"headline-lg-mobile":["Bodoni Moda"],
        "display-md":["Bodoni Moda"],"headline-md":["Bodoni Moda"],"display-lg":["Bodoni Moda"],
        "headline-lg":["Bodoni Moda"],"label-caps":["Hanken Grotesk"]
      },
      fontSize: {
        "body-md":["16px",{"lineHeight":"24px","fontWeight":"400"}],
        "body-lg":["20px",{"lineHeight":"32px","fontWeight":"300"}],
        "headline-lg-mobile":["32px",{"lineHeight":"40px","fontWeight":"400"}],
        "display-md":["64px",{"lineHeight":"72px","letterSpacing":"-0.02em","fontWeight":"400"}],
        "headline-md":["32px",{"lineHeight":"40px","fontWeight":"400"}],
        "display-lg":["84px",{"lineHeight":"96px","letterSpacing":"-0.02em","fontWeight":"400"}],
        "headline-lg":["48px",{"lineHeight":"56px","fontWeight":"400"}],
        "label-caps":["12px",{"lineHeight":"16px","letterSpacing":"0.15em","fontWeight":"600"}]
      }
    }
  }
}
</script>
<link rel="stylesheet" href="<?= url('assets/css/style.css') ?>"/>
<script>window.SITE_URL = "<?= SITE_URL ?>";</script>
</head>
<body class="bg-background text-on-surface font-body-md selection:bg-secondary-container selection:text-on-secondary-container overflow-x-hidden">
<div class="grain-overlay"></div>
