-- migration 00002: thêm các cột dịch nghĩa cho options và passages

ALTER TABLE `options` ADD COLUMN `translation` TEXT DEFAULT NULL AFTER `content`;

ALTER TABLE `passages` 
ADD COLUMN `translation` TEXT DEFAULT NULL AFTER `content`,
ADD COLUMN `translation_en` TEXT DEFAULT NULL AFTER `translation`;

-- thêm các audio nhỏ để người đọc nghe lại cho từng câu 1
ALTER TABLE `tests` ADD COLUMN `audio_url` VARCHAR(255) DEFAULT NULL AFTER `duration`;
