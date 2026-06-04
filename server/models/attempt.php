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
	$sqlQuestions = "
        SELECT 
            q.id AS question_id, 
            q.question_number,
            q.part, 
            q.content AS question_content, 
            q.image_url, 
            q.audio_url, 
            q.correct_answer AS correct_option,
            q.explanation,
            ua.selected_answer AS user_choice,
            p.content AS paragraph,
            p.image_url AS passage_image,
            p.audio_url AS passage_audio,
            p.translation AS passage_translation,
            p.translation_en AS passage_translation_en
        FROM questions q
        LEFT JOIN passages p ON q.passage_id = p.id
        JOIN attempts a ON (a.id = :id OR a.uuid = :uuid) AND q.test_id = a.test_id
        LEFT JOIN attempt_answers ua ON q.id = ua.question_id AND ua.attempt_id = a.id
        ORDER BY q.part ASC, q.question_number ASC
    ";

	$stmtQ = $conn->prepare($sqlQuestions);
	// hỗ trợ cả ID số và UUID
	$stmtQ->execute(['id' => is_numeric($attempt_id) ? $attempt_id : 0, 'uuid' => $attempt_id]);
	$questions = $stmtQ->fetchAll(PDO::FETCH_ASSOC);

	if (empty($questions))
		return [];

	$questionIds = array_column($questions, 'question_id');
	$placeholders = implode(',', array_fill(0, count($questionIds), '?'));

	$sqlOptions = "SELECT question_id, label, content, translation FROM options WHERE question_id IN ($placeholders) ORDER BY label ASC";
	$stmtO = $conn->prepare($sqlOptions);
	$stmtO->execute($questionIds);
	$optionsRaw = $stmtO->fetchAll(PDO::FETCH_ASSOC);

	$groupedOptions = [];
	$groupedTranslations = [];
	// gom tất cả lựa chọn (A, B, C, D) và bản dịch vào mảng theo ID câu hỏi
	foreach ($optionsRaw as $opt) {
		$groupedOptions[$opt['question_id']][$opt['label']] = $opt['content'];
		$groupedTranslations[$opt['question_id']][$opt['label']] = $opt['translation'];
	}

	// gán ngược các đáp án đó vào từng câu hỏi để trả về cho frontend
	foreach ($questions as &$q) {
		$q['options'] = $groupedOptions[$q['question_id']] ?? [];
		$q['option_translations'] = $groupedTranslations[$q['question_id']] ?? [];
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

		// lấy đáp án chuẩn từ database
		$stmt = $conn->prepare("SELECT id, correct_answer, part FROM questions WHERE test_id = ?");
		$stmt->execute([$test_id]);
		$correct_answers = $stmt->fetchAll(PDO::FETCH_ASSOC);

		// đếm tổng số câu listening và reading trong đề thi
		$total_l = 0;
		$total_r = 0;
		foreach ($correct_answers as $row) {
			if ((int)$row['part'] <= 4) {
				$total_l++;
			} else {
				$total_r++;
			}
		}

		$l_correct = 0;
		$r_correct = 0;
		$details_data = [];

		// chấm điểm dựa trên question id (không dùng số thứ tự câu)
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

		// quy đổi điểm
		if ($total_l > 0) {
			$scaled_l = (int)round(($l_correct / $total_l) * 100);
			$l_score = calculateToeicScore($scaled_l);
		} else {
			$l_score = 0;
		}

		if ($total_r > 0) {
			$scaled_r = (int)round(($r_correct / $total_r) * 100);
			$r_score = calculateToeicScore($scaled_r);
		} else {
			$r_score = 0;
		}

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