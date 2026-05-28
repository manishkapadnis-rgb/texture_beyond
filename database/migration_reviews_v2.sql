-- Verified-purchase reviews v2 — run in phpMyAdmin once.
-- Safe to re-run.

DROP TABLE IF EXISTS `review_images`;
DROP TABLE IF EXISTS `reviews`;

CREATE TABLE `reviews` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `user_id`    INT NOT NULL,
  `order_id`   INT DEFAULT NULL,
  `name`       VARCHAR(120) NOT NULL,
  `email`      VARCHAR(190) DEFAULT NULL,
  `rating`     TINYINT NOT NULL DEFAULT 5,
  `title`      VARCHAR(190) DEFAULT NULL,
  `body`       TEXT NOT NULL,
  `verified`   TINYINT(1) NOT NULL DEFAULT 0,
  `status`     ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_user_product` (`user_id`, `product_id`),
  KEY `idx_rev_product` (`product_id`),
  KEY `idx_rev_status` (`status`),
  CONSTRAINT `fk_review_prod` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_review_user` FOREIGN KEY (`user_id`)    REFERENCES `users`(`id`)    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `review_images` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `review_id`  INT NOT NULL,
  `image`      VARCHAR(190) NOT NULL,
  `sort_order` INT DEFAULT 0,
  KEY `idx_ri_review` (`review_id`),
  CONSTRAINT `fk_ri_review` FOREIGN KEY (`review_id`) REFERENCES `reviews`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
