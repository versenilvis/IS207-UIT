-- Migration 00001: thêm các cột để track các gói payment của user

-- thêm các cột để dễ track các gói payment của user
ALTER TABLE `users` 
ADD COLUMN `is_premium` TINYINT(1) DEFAULT 0 AFTER `is_banned`,
ADD COLUMN `has_course` TINYINT(1) DEFAULT 0 AFTER `is_premium`,
ADD COLUMN `premium_plan` VARCHAR(50) DEFAULT NULL AFTER `has_course`,
ADD COLUMN `premium_until` DATETIME DEFAULT NULL AFTER `premium_plan`;

-- bảng transaction_history để lưu lại các giao dịch
CREATE TABLE IF NOT EXISTS `transaction_history` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tx_id` VARCHAR(20) UNIQUE NOT NULL,
    `user_id` INT NOT NULL,
    `plan_id` VARCHAR(50) NOT NULL,
    `plan_name` VARCHAR(100) NOT NULL,
    `price` INT NOT NULL,
    `period` VARCHAR(50) NOT NULL,
    `status` ENUM('success', 'failed', 'pending') DEFAULT 'success',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_transaction_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    INDEX `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
