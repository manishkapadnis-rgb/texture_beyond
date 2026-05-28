-- ============================================================
--  Texture & Beyond — v2 schema (extends v1)
--  Run AFTER importing sql/u663620806_textureb.sql
--  Adds: admins, product_images, banners, pages, enquiries,
--        testimonials, blogs, gallery, wishlist, persistent cart, menus
-- ============================================================
USE `u663620806_textureb`;
SET FOREIGN_KEY_CHECKS = 0;

-- separate admins table (the users table keeps role='admin' for backward compat)
CREATE TABLE IF NOT EXISTS `admins` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `name`       VARCHAR(150) NOT NULL,
  `email`      VARCHAR(190) NOT NULL UNIQUE,
  `password`   VARCHAR(255) NOT NULL,
  `role`       ENUM('super','manager') DEFAULT 'super',
  `avatar`     VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT IGNORE INTO `admins` (name,email,password,role) VALUES
('Admin','admin@texturebeyond.com','PLACEHOLDER_RUN_INSTALL_PHP','super');

CREATE TABLE IF NOT EXISTS `product_images` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `image`      VARCHAR(255) NOT NULL,
  `sort_order` INT DEFAULT 0,
  KEY (`product_id`),
  CONSTRAINT `fk_pi_prod` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `product_variants` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `name`       VARCHAR(120) NOT NULL,
  `value`      VARCHAR(120) NOT NULL,
  `price_diff` DECIMAL(10,2) DEFAULT 0,
  `stock`      INT DEFAULT 0,
  KEY (`product_id`),
  CONSTRAINT `fk_pv_prod` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `banners` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `position`   ENUM('hero','strip','promo','quote') DEFAULT 'hero',
  `title`      VARCHAR(190),
  `subtitle`   VARCHAR(300),
  `image`      VARCHAR(255),
  `link`       VARCHAR(255),
  `button_text` VARCHAR(80),
  `sort_order` INT DEFAULT 0,
  `status`     TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `pages` (
  `id`               INT AUTO_INCREMENT PRIMARY KEY,
  `slug`             VARCHAR(120) UNIQUE,
  `title`            VARCHAR(190),
  `content`          LONGTEXT,
  `meta_title`       VARCHAR(190),
  `meta_description` VARCHAR(300),
  `updated_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT IGNORE INTO `pages` (slug,title,content) VALUES
('about','About Texture & Beyond','<p>Texture & Beyond was born from the desire to bring tactile emotions back into the modern home. Each canvas is a labor of love, individually sculpted to capture light and shadow.</p>'),
('privacy','Privacy Policy','<p>We respect your privacy. Information you share with us is used solely to fulfil orders and improve your experience.</p>'),
('refund','Refund Policy','<p>Returns accepted within 7 days of delivery for unused items in original packaging. Custom pieces are non-refundable.</p>'),
('shipping','Shipping Policy','<p>Pan-India shipping in 5–10 business days. Free shipping on orders above ₹5,000.</p>');

CREATE TABLE IF NOT EXISTS `enquiries` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `type`       ENUM('contact','bulk','corporate','product') DEFAULT 'contact',
  `name`       VARCHAR(150),
  `email`      VARCHAR(190),
  `phone`      VARCHAR(30),
  `company`    VARCHAR(190),
  `product_id` INT DEFAULT NULL,
  `subject`    VARCHAR(190),
  `message`    TEXT,
  `status`     ENUM('new','read','closed') DEFAULT 'new',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY (`type`), KEY (`status`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `testimonials` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `name`       VARCHAR(150),
  `role`       VARCHAR(150),
  `image`      VARCHAR(255),
  `quote`      TEXT,
  `rating`     TINYINT DEFAULT 5,
  `status`     TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

INSERT IGNORE INTO `testimonials` (name,role,quote) VALUES
('Aanya Mehra','Interior Designer, Mumbai','Texture & Beyond pieces transformed a lobby into a living gallery. Unmatched craftsmanship.'),
('Rohan Kapoor','Homeowner, Bengaluru','The plaster wave we installed catches the morning light like nothing else. Truly poetic art.'),
('Studio Lumen','Architecture Studio','Our go-to for statement walls. Quiet luxury at its finest.');

CREATE TABLE IF NOT EXISTS `blogs` (
  `id`               INT AUTO_INCREMENT PRIMARY KEY,
  `slug`             VARCHAR(220) UNIQUE,
  `title`            VARCHAR(220),
  `excerpt`          VARCHAR(500),
  `content`          LONGTEXT,
  `image`            VARCHAR(255),
  `author`           VARCHAR(150) DEFAULT 'Texture & Beyond',
  `meta_title`       VARCHAR(190),
  `meta_description` VARCHAR(300),
  `status`           TINYINT(1) DEFAULT 1,
  `created_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT IGNORE INTO `blogs` (slug,title,excerpt,content) VALUES
('art-of-texture','The Quiet Art of Texture','Why tactile art matters in a digital world.','<p>In a world saturated with screens, our hands long for something real. Texture art invites a slower kind of looking — one that rewards touch as much as sight.</p>'),
('curating-modern-walls','Curating Modern Indian Walls','A primer on building a gallery wall with heritage motifs.','<p>Start with a single anchor piece. Let the wall breathe. Mix scale.</p>');

CREATE TABLE IF NOT EXISTS `gallery` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `title`      VARCHAR(190),
  `image`      VARCHAR(255),
  `caption`    VARCHAR(300),
  `sort_order` INT DEFAULT 0,
  `status`     TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `wishlist` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `user_id`    INT NOT NULL,
  `product_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_wish` (`user_id`,`product_id`),
  CONSTRAINT `fk_w_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_w_prod` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `cart` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `user_id`    INT NOT NULL,
  `product_id` INT NOT NULL,
  `quantity`   INT DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_cart` (`user_id`,`product_id`),
  CONSTRAINT `fk_c_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_c_prod` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `menus` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `location`   ENUM('header','footer') DEFAULT 'header',
  `label`      VARCHAR(120),
  `url`        VARCHAR(255),
  `sort_order` INT DEFAULT 0,
  `status`     TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

INSERT IGNORE INTO `menus` (location,label,url,sort_order) VALUES
('header','Shop','shop.php',1),('header','Collections','collections.php',2),
('header','Gallery','gallery.php',3),('header','Blog','blog.php',4),
('header','About','about.php',5),('header','Contact','contact.php',6);

-- Rebuild settings table with all CMS columns
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id`                   INT AUTO_INCREMENT PRIMARY KEY,
  `site_name`            VARCHAR(150) DEFAULT 'Texture & Beyond',
  `site_tagline`         VARCHAR(190) DEFAULT 'Modern Indian Art & Decor',
  `logo`                 VARCHAR(255) DEFAULT NULL,
  `favicon`              VARCHAR(255) DEFAULT NULL,
  `email`                VARCHAR(190) DEFAULT 'hello@texturenbeyond.com',
  `phone`                VARCHAR(50)  DEFAULT '+91 98765 43210',
  `address`              TEXT,
  `footer_about`         TEXT,
  `hero_title`           VARCHAR(190) DEFAULT 'Feel The Texture, Live The Beyond.',
  `hero_subtitle`        VARCHAR(300) DEFAULT 'Modern handmade texture art crafted to transform your walls.',
  `hero_image`           VARCHAR(255) DEFAULT NULL,
  `shipping_charge`      DECIMAL(10,2) DEFAULT 0,
  `free_shipping_above`  DECIMAL(10,2) DEFAULT 5000,
  `social_facebook`      VARCHAR(255),
  `social_instagram`     VARCHAR(255),
  `social_pinterest`     VARCHAR(255),
  `social_youtube`       VARCHAR(255),
  `seo_home_title`       VARCHAR(190),
  `seo_home_description` VARCHAR(300),
  `seo_keywords`         VARCHAR(300),
  `razorpay_key`         VARCHAR(120),
  `razorpay_secret`      VARCHAR(190)
) ENGINE=InnoDB;
INSERT INTO `settings` (id,address,footer_about,seo_home_title,seo_home_description) VALUES
(1,'Jaipur, Rajasthan, India','Crafting modern Indian sensibilities for global homes.',
 'Texture & Beyond — Modern Indian Art & Decor',
 'Luxury handmade texture art, sculpted plaster canvases, and modern wall decor crafted in India.');

INSERT IGNORE INTO `banners` (position,title,subtitle,image,link,button_text,sort_order) VALUES
('hero','Feel The Texture, Live The Beyond.','Modern handmade texture art for the contemporary home.','https://lh3.googleusercontent.com/aida-public/AB6AXuAk7eUdpc-l3SAm_QZLT2F79JAqQs8GAEzoqtwoBy_tv_-LmlbniglScLPeSMeNJfoCawEo5wjBMBiPqB2rngMfywtnzmDk72YFlrw0G-eNYP00cJlYucJ-IfOOqxcbVKcxnfrL0dg_fMIIxuCQdXntwlDoVuD-0UgCOaEETebZlgENXc7RlXCeZKMU0CCIH7TyNnmRYgZKplJeJbd16Ts7RX1J160iBM3V5eL90VWn0k0CEGdI1XDc8yHxJKRlPQHmnyE9JwPbccE','shop.php','Explore Collection',1);

SET FOREIGN_KEY_CHECKS = 1;
