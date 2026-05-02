-- data test
-- mysql v8.0

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Xóa dữ liệu cũ (nếu có) để tránh trùng lặp khi chạy lại
TRUNCATE TABLE `options`;
TRUNCATE TABLE `attempt_answers`;
TRUNCATE TABLE `attempts`;
TRUNCATE TABLE `questions`;
TRUNCATE TABLE `passages`;
TRUNCATE TABLE `payments`;
TRUNCATE TABLE `tests`;
TRUNCATE TABLE `oauth_accounts`;
TRUNCATE TABLE `users`;

-- Chèn dữ liệu Users
INSERT INTO `users` (`id`, `uuid`, `last_name`, `first_name`, `email`, `password`, `role`) VALUES
(1, 'd3b07384-d990-4495-92b8-508381286699', 'Admin', 'Sáng Lập', 'admin@prephub.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
(2, '8d89163d-42ba-4b68-80f0-3330b62e4975', 'Nguyễn', 'Văn A', 'user@prephub.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

-- Chèn dữ liệu Tests (Đề thi)
INSERT INTO `tests` (`id`, `uuid`, `title`, `description`, `duration`, `is_premium`, `is_active`) VALUES
(1, '550e8400-e29b-41d4-a716-446655440000', 'Đề thi thử TOEIC số 1 (Free)', 'Đề thi mẫu miễn phí cho mọi người luyện tập cơ bản.', 7200, 0, 1),
(2, '6ba7b810-9dad-11d1-80b4-00c04fd430c8', 'Đề thi thực tế ETS 2024 (Premium)', 'Bộ đề thi bản quyền mới nhất từ ETS, độ khó sát thực tế.', 7200, 1, 1),
(3, '123e4567-e89b-12d3-a456-426614174000', 'Đề thi đang soạn thảo (Hidden)', 'Đề này sẽ không hiển thị trên danh sách người dùng.', 7200, 0, 0);

-- Giả lập thanh toán (User 2 đã mua đề 2)
INSERT INTO `payments` (`user_id`, `test_id`) VALUES (2, 2);

-- Chèn dữ liệu Đoạn văn (Passages - Dùng cho Part 7)
INSERT INTO `passages` (`id`, `test_id`, `content`, `image_url`) VALUES
(1, 1, 'Questions 147-148 refer to the following advertisement.\n\nLooking for a new car? Visit Downtown Motors today! We have a wide range of vehicles to suit every budget. All our cars come with a 2-year warranty and free service for the first year. Visit us at 123 Main Street or check our website.', '/server/uploads/image/sample_passage.jpg');

-- Chèn dữ liệu Câu hỏi (Questions)
-- Part 1: Hình ảnh
INSERT INTO `questions` (`id`, `test_id`, `part`, `question_number`, `content`, `image_url`, `correct_answer`) VALUES
(1, 1, 1, 1, 'Look at the picture marked number 1 in your test book.', '/server/uploads/image/part1_sample.jpg', 'A');

-- Part 2: Âm thanh ngắn
INSERT INTO `questions` (`id`, `test_id`, `part`, `question_number`, `content`, `audio_url`, `correct_answer`) VALUES
(2, 1, 2, 7, 'Who is the manager of this project?', '/server/uploads/audio/part2_sample.mp3', 'B');

-- Part 3: Hội thoại (Có âm thanh)
INSERT INTO `questions` (`id`, `test_id`, `part`, `question_number`, `content`, `audio_url`, `correct_answer`) VALUES
(3, 1, 3, 32, 'What does the woman imply about the meeting?', '/server/uploads/audio/part3_sample.mp3', 'C');

-- Part 5: Điền từ (Chỉ có chữ)
INSERT INTO `questions` (`id`, `test_id`, `part`, `question_number`, `content`, `correct_answer`) VALUES
(4, 1, 5, 101, 'The marketing team is currently working on a new strategy to _______ brand awareness.', 'D');

-- Part 7: Đọc hiểu (Liên kết với Passage)
INSERT INTO `questions` (`id`, `test_id`, `passage_id`, `part`, `question_number`, `content`, `correct_answer`) VALUES
(5, 1, 1, 7, 147, 'What is being advertised?', 'A'),
(6, 1, 1, 7, 148, 'What is offered for free for the first year?', 'B');

-- Các đáp án
-- Options cho câu 1
INSERT INTO `options` (`question_id`, `label`, `content`) VALUES
(1, 'A', 'He is holding a umbrella.'),
(1, 'B', 'He is walking in the park.'),
(1, 'C', 'He is sitting on a bench.'),
(1, 'D', 'He is driving a car.');

-- Options cho câu 2
INSERT INTO `options` (`question_id`, `label`, `content`) VALUES
(2, 'A', 'At the restaurant.'),
(2, 'B', 'Mr. Johnson is.'),
(2, 'C', 'Next week.'),
(2, 'D', 'By bus.');

-- Options cho câu 3
INSERT INTO `options` (`question_id`, `label`, `content`) VALUES
(3, 'A', 'It was too long.'),
(3, 'B', 'She missed it.'),
(3, 'C', 'It was very informative.'),
(3, 'D', 'The room was cold.');

-- Options cho câu 4
INSERT INTO `options` (`question_id`, `label`, `content`) VALUES
(4, 'A', 'increase'),
(4, 'B', 'increasing'),
(4, 'C', 'increased'),
(4, 'D', 'to increase');

-- Options cho câu 5
INSERT INTO `options` (`question_id`, `label`, `content`) VALUES
(5, 'A', 'A car dealership'),
(5, 'B', 'A repair shop'),
(5, 'C', 'A website design company'),
(5, 'D', 'A travel agency');

-- Options cho câu 6
INSERT INTO `options` (`question_id`, `label`, `content`) VALUES
(6, 'A', 'New tires'),
(6, 'B', 'Vehicle service'),
(6, 'C', 'Car insurance'),
(6, 'D', 'A GPS system');

SET FOREIGN_KEY_CHECKS = 1;
