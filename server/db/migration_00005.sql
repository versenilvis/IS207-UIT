-- Migration 00005: Thêm giá trị 'refunded' vào ENUM status của bảng transaction_history
ALTER TABLE `transaction_history` 
MODIFY COLUMN `status` ENUM('success', 'failed', 'pending', 'refunded') DEFAULT 'success';
