# Texture & Beyond — Hostinger Deployment Guide

## 1 · Final project structure

```
texture_beyond/
├── admin/                          Shopify-style admin panel
│   ├── includes/                   header.php, footer.php, crud-helpers.php
│   ├── index.php                   Dashboard
│   ├── products.php · categories.php · orders.php · order-view.php
│   ├── coupons.php · users.php · contacts.php
│   ├── enquiries.php · banners.php · testimonials.php
│   ├── blogs.php · pages.php · menu.php · seo.php
│   ├── gallery-admin.php · settings.php
│   └── login.php · logout.php
├── ajax/                           wishlist.php · search.php · product-enquiry.php
├── assets/
│   ├── css/style.css               Custom styles on top of Tailwind CDN
│   ├── js/main.js                  GSAP scroll reveals, cart AJAX
│   └── images/
├── components/                     product-card.php (reusable partial)
├── config/
│   ├── config.php                  Env-aware: DB creds, base URL auto-detect
│   └── database.php                mysqli + db_one/db_all/db_exec helpers
├── database/
│   └── texture_beyond_v2.sql       Full schema (v1 sql/texture_beyond.sql ⊕ v2 add-ons)
├── includes/                       Storefront includes
│   ├── functions.php               cart, wishlist, coupon, helpers
│   ├── head.php                    Tailwind config + meta/SEO
│   ├── navbar.php                  Dynamic header (menu/logo)
│   ├── header.php · footer.php
│   ├── config.php → /config/config.php (shim)
│   └── db.php → /config/database.php (shim)
├── sql/
│   └── texture_beyond.sql          Original v1 schema
├── uploads/                        products · categories · banners · blog · gallery · testimonials · site
├── logs/                           Auto-rotated error log
├── index.php · shop.php · product.php · cart.php · checkout.php
├── thank-you.php · login.php · logout.php · forgot-password.php
├── my-account.php · orders.php · wishlist.php
├── about.php · contact.php · gallery.php · collections.php
├── blog.php · blog-detail.php · page.php
├── cart-action.php · newsletter.php
├── .htaccess                       SEO URLs · HTTPS · cache · security headers
├── install.php                     One-time bcrypt setup (delete after run)
└── DEPLOYMENT.md · README.md
```

## 2 · Database

Two SQL files run in order:
1. `sql/texture_beyond.sql`   — core ecommerce schema + seed
2. `database/texture_beyond_v2.sql` — admins, banners, pages, enquiries, testimonials, blogs, gallery, wishlist, persistent cart, menus, expanded settings

After import, run `/install.php` once. Default admin: `admin@texturebeyond.com` / `admin123`.

## 3 · Hostinger upload steps

1. **Create DB**
   - hPanel → Databases → MySQL Databases
   - Create database (e.g. `u123456_textureb`) and a user with full privileges
   - Note the **DB name, user, password, host** (usually `localhost`)

2. **Import schema**
   - hPanel → Databases → phpMyAdmin → select the DB
   - Import tab → upload `sql/texture_beyond.sql` → Go
   - Import again → upload `database/texture_beyond_v2.sql` → Go

3. **Upload files**
   - hPanel → Files → File Manager → `public_html`
   - Upload the entire `texture_beyond/` contents into `public_html/` (root)
   - **OR** keep in subfolder `public_html/texture_beyond/` if you don't want it at domain root
   - Permissions: `uploads/` and `logs/` → `0775` (File Manager → right-click → Permissions)

4. **Configure**
   - Open `config/config.php` and edit the production DB block:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'u123456_textureb');
     define('DB_PASS', 'your-real-password');
     define('DB_NAME', 'u123456_textureb');
     ```
   - Set environment variable in hPanel → Advanced → PHP Configuration:
     `APP_ENV=production` (or set `define('ENV', 'production')` directly)
   - `SITE_URL` is auto-detected — no change needed once the domain points to the folder.

5. **Run installer**
   - Browse https://texturenbeyond.com/install.php
   - Verify success page
   - **Delete `install.php` and `db-test.php`** via File Manager

6. **Enable SSL** in hPanel → SSL → install free Let's Encrypt cert.
   Then uncomment the HTTPS-force block in `.htaccess`.

7. **Login to admin** at https://texturenbeyond.com/admin/login.php
   Change the default password under **Users** immediately.

## 4 · Admin login setup

After install, log in and configure:

| Section | What to set |
|---|---|
| **Settings** | Brand logo, favicon, hero, contact, social, Razorpay keys, shipping |
| **SEO** | Home page meta title / description / keywords |
| **Header Menu** | Order of navigation links |
| **Banners** | Hero / promo strip images |
| **Pages** | About, Privacy, Refund, Shipping (CMS rich text) |
| **Blog** | Add journal posts |
| **Gallery** | Bulk upload interior shots |
| **Testimonials** | Customer quotes shown on About |
| **Products / Categories** | Catalogue + images, prices, SEO |
| **Coupons** | WELCOME10, LUXURY500 already seeded |

## 5 · Enquiry system

All forms (contact, bulk, corporate, product) save to `enquiries` table and email the admin via PHP `mail()`. View / export CSV at **Admin → Enquiries**.

## 6 · Production checklist

- [ ] Delete `install.php`, `db-test.php`, `database/texture_beyond_v2.sql` (or move outside `public_html`)
- [ ] Set `APP_ENV=production` (errors logged not shown)
- [ ] Enable SSL + force HTTPS in `.htaccess`
- [ ] Change default admin password
- [ ] Configure Razorpay keys in Settings → Commerce
- [ ] Verify `uploads/` writable (0775)
- [ ] Replace seed products / categories / banners with real content
- [ ] Submit `sitemap.xml` to Google Search Console (auto-generated suggestion: add `sitemap.php` later)

## 7 · Local development

- Project: `E:\free-claude-code\texure\texture_beyond`
- Mirrored to WAMP via junction: `C:\wamp64\www\texture_beyond`
- URL: http://localhost/texture_beyond/
- Admin: http://localhost/texture_beyond/admin/login.php
