-- mysql 8.0

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `uuid` CHAR(36) UNIQUE NOT NULL,
    `last_name` VARCHAR(50) NOT NULL,
    `first_name` VARCHAR(50) NOT NULL,
    `email` VARCHAR(100) UNIQUE NOT NULL,
    `password` VARCHAR(255) DEFAULT NULL,
    `avatar` VARCHAR(255) DEFAULT NULL,
    `role` ENUM('user', 'admin') DEFAULT 'user',
    `is_banned` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_uuid` (`uuid`),
    INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `oauth_accounts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNIQUE NOT NULL,
    `google_id` VARCHAR(255) UNIQUE NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_oauth_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    INDEX `idx_google_id` (`google_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tests` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `uuid` CHAR(36) UNIQUE NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `duration` INT DEFAULT 7200,
    `total_questions` INT DEFAULT 200,
    `is_premium` TINYINT(1) DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_uuid` (`uuid`),
    INDEX `idx_premium_active` (`is_premium`, `is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `passages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `test_id` INT NOT NULL,
    `content` TEXT DEFAULT NULL,
    `audio_url` VARCHAR(255) DEFAULT NULL,
    `image_url` VARCHAR(255) DEFAULT NULL,
    CONSTRAINT `fk_passage_test` FOREIGN KEY (`test_id`) REFERENCES `tests` (`id`) ON DELETE CASCADE,
	INDEX `idx_test` (`test_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `questions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `test_id` INT NOT NULL,
    `passage_id` INT DEFAULT NULL,
    `part` TINYINT NOT NULL,
    `question_number` INT NOT NULL,
    `content` TEXT DEFAULT NULL,
    `audio_url` VARCHAR(255) DEFAULT NULL,
    `image_url` VARCHAR(255) DEFAULT NULL,
    `correct_answer` CHAR(1) NOT NULL,
    `explanation` TEXT DEFAULT NULL,
    CONSTRAINT `fk_question_test` FOREIGN KEY (`test_id`) REFERENCES `tests` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_question_passage` FOREIGN KEY (`passage_id`) REFERENCES `passages` (`id`) ON DELETE SET NULL,
    INDEX `idx_test_part` (`test_id`, `part`),
    INDEX `idx_test_number` (`test_id`, `question_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `options` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `question_id` INT NOT NULL,
    `label` CHAR(1) NOT NULL,
    `content` TEXT NOT NULL,
    CONSTRAINT `fk_option_question` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE,
    INDEX `idx_question` (`question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `attempts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `uuid` CHAR(36) UNIQUE NOT NULL,
    `user_id` INT NOT NULL,
    `test_id` INT NOT NULL,
    `listening_correct` INT DEFAULT 0,
    `reading_correct` INT DEFAULT 0,
    `listening_score` INT DEFAULT 0,
    `reading_score` INT DEFAULT 0,
    `total_score` INT DEFAULT 0,
    `time_spent` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_attempt_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_attempt_test` FOREIGN KEY (`test_id`) REFERENCES `tests` (`id`) ON DELETE CASCADE,
    INDEX `idx_uuid` (`uuid`),
    INDEX `idx_user_created` (`user_id`, `created_at` DESC),
	INDEX `idx_test` (`test_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `attempt_answers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `attempt_id` INT NOT NULL,
    `question_id` INT NOT NULL,
    `selected_answer` CHAR(1) DEFAULT NULL,
    `is_correct` TINYINT(1) NOT NULL,
    CONSTRAINT `fk_answer_attempt` FOREIGN KEY (`attempt_id`) REFERENCES `attempts` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_answer_question` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE,
    INDEX `idx_attempt` (`attempt_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `payments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `test_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_payment_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_payment_test` FOREIGN KEY (`test_id`) REFERENCES `tests` (`id`) ON DELETE CASCADE,
    UNIQUE KEY `uq_user_test` (`user_id`, `test_id`),
	INDEX `idx_test` (`test_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;