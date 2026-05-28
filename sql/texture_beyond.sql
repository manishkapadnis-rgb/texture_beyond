-- ============================================================
--  Texture & Beyond — Luxury Ecommerce Database Schema
--  MySQL 5.7+ / MariaDB 10+ · InnoDB · utf8mb4
--  Import: phpMyAdmin → Import → choose this file
-- ============================================================

CREATE DATABASE IF NOT EXISTS `texture_beyond`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `texture_beyond`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `coupons`;
DROP TABLE IF EXISTS `contacts`;
DROP TABLE IF EXISTS `newsletter`;
DROP TABLE IF EXISTS `settings`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------
--  USERS  (customers + admins)
-- ------------------------------------------------------------
CREATE TABLE `users` (
  `id`             INT AUTO_INCREMENT PRIMARY KEY,
  `name`           VARCHAR(150) NOT NULL,
  `email`          VARCHAR(190) NOT NULL UNIQUE,
  `phone`          VARCHAR(30)  DEFAULT NULL,
  `password`       VARCHAR(255) NOT NULL,
  `address`        TEXT         DEFAULT NULL,
  `city`           VARCHAR(100) DEFAULT NULL,
  `state`          VARCHAR(100) DEFAULT NULL,
  `pincode`        VARCHAR(20)  DEFAULT NULL,
  `role`           ENUM('user','admin') DEFAULT 'user',
  `reset_token`    VARCHAR(100) DEFAULT NULL,
  `reset_expires`  DATETIME     DEFAULT NULL,
  `created_at`     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_users_role`  (`role`),
  KEY `idx_users_token` (`reset_token`)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
--  CATEGORIES
-- ------------------------------------------------------------
CREATE TABLE `categories` (
  `id`           INT AUTO_INCREMENT PRIMARY KEY,
  `name`         VARCHAR(150) NOT NULL,
  `slug`         VARCHAR(180) NOT NULL UNIQUE,
  `tagline`      VARCHAR(190) DEFAULT NULL,
  `description`  TEXT         DEFAULT NULL,
  `image`        VARCHAR(255) DEFAULT NULL,
  `is_featured`  TINYINT(1)   DEFAULT 0,
  `status`       TINYINT(1)   DEFAULT 1,
  `created_at`   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_cat_status`   (`status`),
  KEY `idx_cat_featured` (`is_featured`)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
--  PRODUCTS
-- ------------------------------------------------------------
CREATE TABLE `products` (
  `id`                INT AUTO_INCREMENT PRIMARY KEY,
  `category_id`       INT NOT NULL,
  `name`              VARCHAR(190) NOT NULL,
  `slug`              VARCHAR(220) NOT NULL UNIQUE,
  `sku`               VARCHAR(80)  DEFAULT NULL,
  `short_description` VARCHAR(500) DEFAULT NULL,
  `description`       TEXT         DEFAULT NULL,
  `price`             DECIMAL(10,2) NOT NULL,
  `sale_price`        DECIMAL(10,2) DEFAULT NULL,
  `stock`             INT DEFAULT 0,
  `image`             VARCHAR(255) DEFAULT NULL,
  `gallery`           TEXT         DEFAULT NULL,
  `dimensions`        VARCHAR(120) DEFAULT NULL,
  `material`          VARCHAR(190) DEFAULT NULL,
  `is_featured`       TINYINT(1) DEFAULT 0,
  `is_bestseller`     TINYINT(1) DEFAULT 0,
  `is_new`            TINYINT(1) DEFAULT 0,
  `meta_title`        VARCHAR(190) DEFAULT NULL,
  `meta_description`  VARCHAR(300) DEFAULT NULL,
  `status`            TINYINT(1) DEFAULT 1,
  `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_prod_cat`    (`category_id`),
  KEY `idx_prod_status` (`status`),
  KEY `idx_prod_flags`  (`is_featured`, `is_bestseller`, `is_new`),
  FULLTEXT KEY `ft_prod_search` (`name`, `short_description`, `description`),
  CONSTRAINT `fk_prod_cat` FOREIGN KEY (`category_id`)
    REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
--  COUPONS
-- ------------------------------------------------------------
CREATE TABLE `coupons` (
  `id`           INT AUTO_INCREMENT PRIMARY KEY,
  `code`         VARCHAR(50) NOT NULL UNIQUE,
  `type`         ENUM('flat','percent') DEFAULT 'flat',
  `discount`     DECIMAL(10,2) NOT NULL,
  `min_order`    DECIMAL(10,2) DEFAULT 0,
  `max_discount` DECIMAL(10,2) DEFAULT NULL,
  `valid_from`   DATE DEFAULT NULL,
  `valid_to`     DATE DEFAULT NULL,
  `usage_limit`  INT  DEFAULT NULL,
  `used`         INT  DEFAULT 0,
  `status`       TINYINT(1) DEFAULT 1,
  KEY `idx_coup_status` (`status`),
  KEY `idx_coup_valid`  (`valid_from`, `valid_to`)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
--  ORDERS
-- ------------------------------------------------------------
CREATE TABLE `orders` (
  `id`             INT AUTO_INCREMENT PRIMARY KEY,
  `order_code`     VARCHAR(40) NOT NULL UNIQUE,
  `user_id`        INT DEFAULT NULL,
  `full_name`      VARCHAR(150) NOT NULL,
  `email`          VARCHAR(190) NOT NULL,
  `phone`          VARCHAR(30)  NOT NULL,
  `address`        TEXT NOT NULL,
  `city`           VARCHAR(100),
  `state`          VARCHAR(100),
  `pincode`        VARCHAR(20),
  `subtotal`       DECIMAL(10,2) NOT NULL,
  `discount`       DECIMAL(10,2) DEFAULT 0,
  `coupon_code`    VARCHAR(50)  DEFAULT NULL,
  `shipping`       DECIMAL(10,2) DEFAULT 0,
  `total`          DECIMAL(10,2) NOT NULL,
  `payment_method` VARCHAR(40) DEFAULT 'COD',
  `payment_status` ENUM('pending','paid','failed') DEFAULT 'pending',
  `status`         ENUM('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `notes`          TEXT DEFAULT NULL,
  `created_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_ord_user`    (`user_id`),
  KEY `idx_ord_status`  (`status`),
  KEY `idx_ord_payment` (`payment_status`),
  KEY `idx_ord_date`    (`created_at`),
  CONSTRAINT `fk_ord_user` FOREIGN KEY (`user_id`)
    REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE `order_items` (
  `id`           INT AUTO_INCREMENT PRIMARY KEY,
  `order_id`     INT NOT NULL,
  `product_id`   INT NOT NULL,
  `product_name` VARCHAR(190) NOT NULL,
  `price`        DECIMAL(10,2) NOT NULL,
  `quantity`     INT NOT NULL,
  `subtotal`     DECIMAL(10,2) NOT NULL,
  KEY `idx_oi_order` (`order_id`),
  KEY `idx_oi_prod`  (`product_id`),
  CONSTRAINT `fk_oi_order` FOREIGN KEY (`order_id`)
    REFERENCES `orders`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_oi_prod`  FOREIGN KEY (`product_id`)
    REFERENCES `products`(`id`)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
--  CONTACT / NEWSLETTER
-- ------------------------------------------------------------
CREATE TABLE `contacts` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `name`       VARCHAR(150),
  `email`      VARCHAR(190),
  `subject`    VARCHAR(190),
  `message`    TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE `newsletter` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `email`      VARCHAR(190) UNIQUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
--  SETTINGS (single row, id=1)
-- ------------------------------------------------------------
CREATE TABLE `settings` (
  `id`                   INT AUTO_INCREMENT PRIMARY KEY,
  `site_name`            VARCHAR(150) DEFAULT 'Texture & Beyond',
  `site_tagline`         VARCHAR(190) DEFAULT 'Modern Indian Art & Decor',
  `email`                VARCHAR(190) DEFAULT 'hello@texturebeyond.com',
  `phone`                VARCHAR(50)  DEFAULT '+91 98765 43210',
  `address`              TEXT,
  `hero_title`           VARCHAR(190) DEFAULT 'Feel The Texture, Live The Beyond.',
  `hero_subtitle`        VARCHAR(300) DEFAULT 'Modern handmade texture art crafted to transform your walls.',
  `hero_image`           VARCHAR(255) DEFAULT NULL,
  `shipping_charge`      DECIMAL(10,2) DEFAULT 0,
  `free_shipping_above`  DECIMAL(10,2) DEFAULT 5000
) ENGINE=InnoDB;

-- ============================================================
--  SEED DATA
-- ============================================================

INSERT INTO `settings` (`id`, `address`)
VALUES (1, 'Jaipur, Rajasthan, India');

-- Admin user (password set by /install.php after import).
INSERT INTO `users` (`name`, `email`, `password`, `role`) VALUES
('Admin', 'admin@texturebeyond.com', 'PLACEHOLDER_RUN_INSTALL_PHP', 'admin');

INSERT INTO `categories` (`name`, `slug`, `tagline`, `description`, `image`, `is_featured`) VALUES
('Texture Art',    'texture-art',    'Sculpted Series', 'Three-dimensional plaster work that breathes life into surfaces.', NULL, 1),
('Digital Arts',   'digital-arts',   'Modern Canvas',   'Contemporary interpretations of heritage motifs in high-definition.', NULL, 1),
('Texture Clocks', 'texture-clocks', 'Functional Art',  'Timepieces redefined as tactile sculptures.', NULL, 1),
('Wall Decor',     'wall-decor',     'Statement Pieces','Curated wall accents for refined interiors.', NULL, 0);

INSERT INTO `products`
(`category_id`,`name`,`slug`,`sku`,`short_description`,`description`,`price`,`sale_price`,`stock`,`image`,`dimensions`,`material`,`is_featured`,`is_bestseller`,`is_new`) VALUES
(1,'Ivory Wave Sculpted Canvas','ivory-wave-sculpted-canvas','TB-TA-001','Handcrafted plaster wave in ivory tones.','A meditative wave of handmade plaster, individually sculpted to catch light through the day.',18500,15999,8,NULL,'48 x 36 inches','Plaster on canvas',1,1,1),
(1,'Mountain Ridge Texture','mountain-ridge-texture','TB-TA-002','Organic mountain-like ridges in warm beige.','Inspired by Himalayan ridges, offering a tactile journey across light and shadow.',22000,NULL,5,NULL,'60 x 40 inches','Plaster, gold leaf',1,0,1),
(2,'Heritage Geometry Print','heritage-geometry-print','TB-DA-001','Earthy geometric digital print.','A contemporary print blending heritage motifs with digital precision.',6500,5499,20,NULL,'24 x 36 inches','Giclée print, oak frame',1,1,0),
(3,'Lunar Texture Clock','lunar-texture-clock','TB-TC-001','Sculptural clock with gold accents.','Functional art — a textured plaster clock face with gold leaf and matte black hands.',9800,NULL,12,NULL,'18 inch diameter','Plaster, brass, gold leaf',0,1,1),
(4,'Sage Linen Wall Panel','sage-linen-wall-panel','TB-WD-001','Soft sage tonal wall panel.','A tonal panel set with linen-like grain for understated luxury.',12500,NULL,7,NULL,'40 x 28 inches','Mixed media',1,0,0),
(2,'Saffron Abstract Series','saffron-abstract-series','TB-DA-002','Vivid saffron abstract.','A bold splash of saffron meets restrained negative space.',7800,NULL,14,NULL,'30 x 30 inches','Giclée print',0,1,0);

INSERT INTO `coupons` (`code`,`type`,`discount`,`min_order`,`valid_from`,`valid_to`,`usage_limit`) VALUES
('WELCOME10','percent', 10,2000,'2024-01-01','2030-12-31',1000),
('LUXURY500','flat',   500,5000,'2024-01-01','2030-12-31', 500);
