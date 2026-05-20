<?php

/**
 * Nhận số câu đúng -> Áp dụng bảng quy đổi TOEIC chuẩn -> Trả về điểm số (max 495)
 * TOEIC listening + reading = 495 + 495 = 990
 */
function calculateToeicScore($correct)
{
	// Đúng 96-100 câu: Max 495 điểm
	if ($correct >= 96)
		return 495;
	// Đúng 91-95 câu: Giảm dần mỗi câu 5 điểm
	if ($correct >= 91)
		return 490 - (95 - $correct) * 5;
	// Dưới 90 câu: Mỗi câu đúng được 5 điểm (Logic đơn giản cho đề thi thử)
	return $correct * 5;
}

/**
 * Tìm bài làm theo ID/UUID -> Lấy danh sách câu hỏi và đáp án user đã chọn -> 
 * Lấy danh sách Options tương ứng -> Group Options vào từng câu hỏi -> Trả về mảng dữ liệu đầy đủ để Review
 */
function getReviewDetails($conn, $attempt_id)
{
	// Lấy danh sách câu hỏi và đáp án của user
	$sqlQuestions = "
        SELECT 
            q.id AS question_id, 
            q.question_number,
            q.part, 
            q.content AS question_content, 
            q.image_url, 
            q.audio_url, 
            q.correct_answer AS correct_option,
            ua.selected_answer AS user_choice
        FROM questions q
        JOIN attempts a ON (a.id = :id OR a.uuid = :uuid) AND q.test_id = a.test_id
        LEFT JOIN attempt_answers ua ON q.id = ua.question_id AND ua.attempt_id = a.id
        ORDER BY q.part ASC, q.question_number ASC
    ";

	$stmtQ = $conn->prepare($sqlQuestions);
	// Hỗ trợ cả ID số và UUID
	$stmtQ->execute(['id' => is_numeric($attempt_id) ? $attempt_id : 0, 'uuid' => $attempt_id]);
	$questions = $stmtQ->fetchAll(PDO::FETCH_ASSOC);

	if (empty($questions))
		return [];

	$questionIds = array_column($questions, 'question_id');
	$placeholders = implode(',', array_fill(0, count($questionIds), '?'));

	$sqlOptions = "SELECT question_id, label, content FROM options WHERE question_id IN ($placeholders) ORDER BY label ASC";
	$stmtO = $conn->prepare($sqlOptions);
	$stmtO->execute($questionIds);
	$optionsRaw = $stmtO->fetchAll(PDO::FETCH_ASSOC);

	$groupedOptions = [];
	// Gom tất cả lựa chọn (A, B, C, D) vào một mảng theo ID câu hỏi để tra cứu đáp án theo ID câu hỏi cho nhanh
	foreach ($optionsRaw as $opt) {
		$groupedOptions[$opt['question_id']][$opt['label']] = $opt['content'];
	}

	// gán ngược các đáp án đó vào từng câu hỏi để trả về cho Frontend
	foreach ($questions as &$q) {
		$q['options'] = $groupedOptions[$q['question_id']] ?? [];
	}

	return $questions;
}

/**
 * Tìm đề thi theo UUID -> Lấy danh sách đáp án đúng từ DB -> So khớp với đáp án User gửi lên theo ID câu hỏi -> 
 * Tính tổng số câu đúng từng phần -> Quy đổi điểm TOEIC -> Lưu tổng quan vào bảng attempts -> 
 * Lưu chi tiết từng câu vào bảng attempt_answers -> Hoàn tất
 */
function submitAndGrade($conn, $user_id, $test_uuid, $user_answers, $time_spent)
{
	try {
		// 1. Tìm ID đề thi
		$stmt = $conn->prepare("SELECT id FROM tests WHERE uuid = ?");
		$stmt->execute([$test_uuid]);
		$test = $stmt->fetch(PDO::FETCH_ASSOC);
		if (!$test)
			return false;
		$test_id = $test['id'];

		// 2. Lấy đáp án chuẩn
		$stmt = $conn->prepare("SELECT id, correct_answer, part FROM questions WHERE test_id = ?");
		$stmt->execute([$test_id]);
		$correct_answers = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$l_correct = 0;
		$r_correct = 0;
		$details_data = [];

		// 3. Chấm điểm dựa trên Question ID (Không dùng số thứ tự câu)
		foreach ($correct_answers as $row) {
			$qId = $row['id'];
			$correct = $row['correct_answer'];
			$part = (int) $row['part'];

			$user_choice = $user_answers[$qId] ?? null;
			$is_correct = ($user_choice === $correct) ? 1 : 0;

			if ($is_correct) {
				if ($part <= 4)
					$l_correct++;
				else
					$r_correct++;
			}

			$details_data[] = [
				'question_id' => $qId,
				'selected_answer' => $user_choice,
				'is_correct' => $is_correct
			];
		}

		// 4. Quy đổi điểm
		$l_score = calculateToeicScore($l_correct);
		$r_score = calculateToeicScore($r_correct);
		$total_score = $l_score + $r_score;

		// 5. Lưu vào attempts
		$conn->beginTransaction();

		$attempt_uuid = bin2hex(random_bytes(16));
		$stmt = $conn->prepare("
            INSERT INTO attempts (uuid, user_id, test_id, listening_correct, reading_correct, listening_score, reading_score, total_score, time_spent) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
		$stmt->execute([$attempt_uuid, $user_id, $test_id, $l_correct, $r_correct, $l_score, $r_score, $total_score, $time_spent]);
		$attempt_id = $conn->lastInsertId();

		// 6. Lưu chi tiết
		$stmtDetail = $conn->prepare("INSERT INTO attempt_answers (attempt_id, question_id, selected_answer, is_correct) VALUES (?, ?, ?, ?)");
		foreach ($details_data as $d) {
			$stmtDetail->execute([$attempt_id, $d['question_id'], $d['selected_answer'], $d['is_correct']]);
		}

		$conn->commit();
		return $attempt_id;

	} catch (Exception $e) {
		if ($conn->inTransaction())
			$conn->rollBack();
		throw $e;
	}
}