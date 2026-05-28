# Texture & Beyond — Luxury PHP Ecommerce

Stack: Core PHP, MySQL, TailwindCSS (CDN), GSAP, vanilla JS.

## Setup
1. Copy this folder into `htdocs/` (XAMPP) or your web root.
2. Create the database: import `sql/texture_beyond.sql` via phpMyAdmin.
3. Edit `includes/config.php` if DB credentials or `SITE_URL` differ.
4. Visit `http://localhost/texture_beyond/install.php` once to set the admin password, then delete `install.php`.
5. Storefront: `/texture_beyond/` · Admin: `/texture_beyond/admin/login.php`

**Default admin:** `admin@texturebeyond.com` / `admin123`

## Structure
```
texture_beyond/
├── index.php · shop.php · product.php · cart.php · checkout.php
├── thank-you.php · login.php · forgot-password.php · my-account.php
├── orders.php · collections.php · gallery.php · contact.php
├── cart-action.php · newsletter.php · logout.php · install.php
├── includes/   (config, functions, head, header, footer)
├── assets/     (css/style.css, js/main.js)
├── uploads/    (products, categories, banners)
├── admin/      (dashboard + product/category/order/coupon/user/contact/settings management)
└── sql/texture_beyond.sql
```

## Features
- Reusable header/footer/head includes with glassmorphic navigation
- Dynamic products, categories, coupons, orders, users, settings
- Session cart + coupon engine (flat / percent, min order, max discount, expiry, usage limit)
- Cash on Delivery / Bank Transfer checkout with stock decrement
- User auth (login/register/forgot), my-account, order history
- Admin: dashboard stats, CRUD on products & categories with image upload, order status, coupons, users, contacts, site settings
- Tailwind config mirrors the Stitch design tokens (Bodoni Moda + Hanken Grotesk, Material Symbols)
- GSAP scroll reveals + hero parallax, gallery masonry, zoom-on-hover cards
- SEO meta tags / canonical / OG per page, products carry meta title + description
- Fully responsive layouts matching the luxury Stitch aesthetic
