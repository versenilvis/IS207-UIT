<?php

/**
 * hàm chính để tạo câu hỏi và đáp án cùng lúc, sử dụng transaction
 */
function questionCreateWithOptions(PDO $db, $questionData, $optionsData)
{
	try {
		$db->beginTransaction();

		$questionId = questionCreate($db, $questionData);

		questionAddOptions($db, $questionId, $optionsData);

		$db->commit();

		return $questionId; 
	} catch (Exception $e) {
		$db->rollBack();
		throw new Exception("Lỗi khi lưu câu hỏi: " . $e->getMessage());
	}
}

/**
 * insert câu hỏi
 */
function questionCreate(PDO $db, $data)
{
	$sql = "INSERT INTO questions 
            (test_id, passage_id, part, question_number, content, audio_url, image_url, correct_answer, explanation) 
            VALUES 
            (:test_id, :passage_id, :part, :question_number, :content, :audio_url, :image_url, :correct_answer, :explanation)";

	$stmt = $db->prepare($sql);

	$result = $stmt->execute([
		':test_id' => $data['test_id'],
		':passage_id' => $data['passage_id'] ?? null,
		':part' => $data['part'],
		':question_number' => $data['question_number'],
		':content' => $data['content'] ?? null,
		':audio_url' => $data['audio_url'] ?? null,
		':image_url' => $data['image_url'] ?? null,
		':correct_answer' => $data['correct_answer'],
		':explanation' => $data['explanation'] ?? null
	]);

	if (!$result) {
		throw new Exception("Không thể insert câu hỏi vào database");
	}

	$questionId = $db->lastInsertId();
	if (!$questionId) {
		throw new Exception("Lỗi: Không thể lấy ID câu hỏi vừa tạo");
	}

	return $questionId;
}

/**
 * insert 4 đáp án
 */
function questionAddOptions(PDO $db, $questionId, $options)
{
	$sql = "INSERT INTO options (question_id, label, content) 
            VALUES (:question_id, :label, :content)";

	$stmt = $db->prepare($sql);

	foreach ($options as $option) {
		$result = $stmt->execute([
			':question_id' => $questionId,
			':label' => $option['label'], 
			':content' => $option['content']
		]);

		if (!$result) {
			throw new Exception("Không thể insert đáp án cho câu hỏi ID: " . $questionId);
		}
	}

	return true;
}

/**
 * lấy câu hỏi theo ID bao gồm cả 4 đáp án
 */
function questionGetWithOptions(PDO $db, $questionId)
{
	$questionSql = "SELECT * FROM questions WHERE id = :id";
	$stmt = $db->prepare($questionSql);
	$stmt->execute([':id' => $questionId]);
	$question = $stmt->fetch(PDO::FETCH_ASSOC);

	if (!$question) {
		return null;
	}

	$optionsSql = "SELECT id, label, content, image_url FROM options WHERE question_id = :question_id ORDER BY label ASC";
	$stmt = $db->prepare($optionsSql);
	$stmt->execute([':question_id' => $questionId]);
	$question['options'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

	return $question;
}

/**
 * kiểm tra bài thi tồn tại
 */
function questionTestExists(PDO $db, $testId)
{
	$sql = "SELECT id FROM tests WHERE id = :id";
	$stmt = $db->prepare($sql);
	$stmt->execute([':id' => $testId]);
	return $stmt->fetch() !== false;
}

/**
 * lấy câu hỏi theo ID
 */
function questionGetById(PDO $db, $questionId)
{
	try {
		$sql = "SELECT id, test_id, passage_id, part, question_number, content, audio_url, image_url, correct_answer, explanation 
                FROM questions 
                WHERE id = :id";

		$stmt = $db->prepare($sql);
		$stmt->execute([':id' => $questionId]);

		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		return $result ? $result : null;
	} catch (Exception $e) {
		throw new Exception("Lỗi khi lấy câu hỏi: " . $e->getMessage());
	}
}

/**
 * lấy tất cả câu hỏi của một bài thi
 */
function questionGetByTestId(PDO $db, $testId)
{
	try {
		$sql = "SELECT id, test_id, passage_id, part, question_number, content, audio_url, image_url, correct_answer, explanation 
                FROM questions 
                WHERE test_id = :test_id 
                ORDER BY question_number ASC";

		$stmt = $db->prepare($sql);
		$stmt->execute([':test_id' => $testId]);

		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	} catch (Exception $e) {
		throw new Exception("Lỗi khi lấy câu hỏi: " . $e->getMessage());
	}
}

/**
 * lấy câu hỏi theo bài thi và part
 */
function questionGetByTestAndPart(PDO $db, $testId, $part)
{
	try {
		$sql = "SELECT id, test_id, passage_id, part, question_number, content, audio_url, image_url, correct_answer, explanation 
                FROM questions 
                WHERE test_id = :test_id AND part = :part 
                ORDER BY question_number ASC";

		$stmt = $db->prepare($sql);
		$stmt->execute([
			':test_id' => $testId,
			':part' => $part
		]);

		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	} catch (Exception $e) {
		throw new Exception("Lỗi khi lấy câu hỏi: " . $e->getMessage());
	}
}

/**
 * cập nhật câu hỏi
 */
function questionUpdate(PDO $db, $questionId, $data)
{
	try {
		$updates = [];
		$params = [':id' => $questionId];

		$fields = ['passage_id', 'part', 'question_number', 'content', 'audio_url', 'image_url', 'correct_answer', 'explanation'];
		foreach ($fields as $field) {
			if (isset($data[$field])) {
				$updates[] = "$field = :$field";
				$params[":$field"] = $data[$field];
			}
		}

		if (empty($updates)) {
			return true;
		}

		$sql = "UPDATE questions SET " . implode(", ", $updates) . " WHERE id = :id";
		$stmt = $db->prepare($sql);

		return $stmt->execute($params);
	} catch (Exception $e) {
		throw new Exception("Lỗi khi cập nhật câu hỏi: " . $e->getMessage());
	}
}

/**
 * xóa câu hỏi
 */
function questionDelete(PDO $db, $questionId)
{
	try {
		$sql = "DELETE FROM options WHERE question_id = :question_id";
		$stmt = $db->prepare($sql);
		$stmt->execute([':question_id' => $questionId]);

		$sql = "DELETE FROM questions WHERE id = :id";
		$stmt = $db->prepare($sql);

		return $stmt->execute([':id' => $questionId]);
	} catch (Exception $e) {
		throw new Exception("Lỗi khi xóa câu hỏi: " . $e->getMessage());
	}
}

/**
 * kiểm tra câu hỏi có tồn tại không
 */
function questionExists(PDO $db, $questionId)
{
	try {
		$sql = "SELECT 1 FROM questions WHERE id = :id LIMIT 1";
		$stmt = $db->prepare($sql);
		$stmt->execute([':id' => $questionId]);

		return $stmt->rowCount() > 0;
	} catch (Exception $e) {
		throw new Exception("Lỗi khi kiểm tra câu hỏi: " . $e->getMessage());
	}
}

/**
 * đếm số câu hỏi trong một bài thi
 */
function questionCountByTestId(PDO $db, $testId)
{
	try {
		$sql = "SELECT COUNT(*) as count FROM questions WHERE test_id = :test_id";
		$stmt = $db->prepare($sql);
		$stmt->execute([':test_id' => $testId]);

		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		return (int) $result['count'];
	} catch (Exception $e) {
		throw new Exception("Lỗi khi đếm câu hỏi: " . $e->getMessage());
	}
}

/**
 * validate đáp án đúng
 */
function questionValidateCorrectAnswer($answer)
{
	return in_array(strtoupper($answer), ['A', 'B', 'C', 'D']);
}