-- Run once in phpMyAdmin to enable product reviews.
CREATE TABLE IF NOT EXISTS `reviews` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `user_id`    INT DEFAULT NULL,
  `name`       VARCHAR(120) NOT NULL,
  `email`      VARCHAR(190) DEFAULT NULL,
  `rating`     TINYINT NOT NULL DEFAULT 5,
  `title`      VARCHAR(190) DEFAULT NULL,
  `body`       TEXT NOT NULL,
  `image`      VARCHAR(190) DEFAULT NULL,
  `status`     TINYINT NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  KEY (`product_id`),
  CONSTRAINT `fk_review_prod` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
