ALTER TABLE `users`
-- Reset lại password
    ADD COLUMN `reset_token_hash` VARCHAR(64) NULL DEFAULT NULL,
    ADD COLUMN `reset_token_expires_at` DATETIME NULL DEFAULT NULL,
-- Verify password trước khi sigup
    ADD COLUMN `account_activation_hash` VARCHAR(64) NULL DEFAULT NULL AFTER `reset_token_expires_at`,
    ADD UNIQUE KEY `users_reset_token_hash_unique` (`reset_token_hash`),
    ADD UNIQUE KEY `users_account_activation_hash_unique` (`account_activation_hash`);