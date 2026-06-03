<?php

require_once __DIR__ . '/../db/config.php';
require_once __DIR__ . '/../models/question.php';
require_once __DIR__ . '/../models/passage.php';
require_once __DIR__ . '/../utils/validator.php';
require_once __DIR__ . '/../utils/fileHandler.php';
require_once __DIR__ . '/../utils/response.php';

/**
 * tạo một câu hỏi mới với các tệp phương tiện tùy chọn
 */
function apiCreateQuestion(PDO $db)
{
	try {
		$testId = helperGetPostValue('test_id');
		$part = helperGetPostValue('part');
		$questionNumber = helperGetPostValue('question_number');
		$passageId = helperGetPostValue('passage_id');
		$content = helperGetPostValue('content');
		$correctAnswer = helperGetPostValue('correct_answer');
		$explanation = helperGetPostValue('explanation');
		$isSubQuestion = !empty($passageId);

		if ($content === 'null' || $content === 'NULL')
			$content = null;
		if ($explanation === 'null' || $explanation === 'NULL')
			$explanation = null;

		$options = json_decode(helperGetPostValue('options', '{}'), true);
		if (!is_array($options)) {
			throw new Exception("Định dạng đáp án không hợp lệ");
		}

		validateToeicPart($part);
		validateQuestionNumber($questionNumber, $part);
		validateQuestionContent($content, $part);
		validateCorrectAnswer($correctAnswer);
		validateOptions($options, $part);

		if (!empty($explanation)) {
			validateExplanation($explanation);
		}

		$internalTestId = helperGetInternalTestId($db, $testId);
		if (!$internalTestId) {
			throw new Exception("Đề thi không tồn tại");
		}

		if (!empty($passageId)) {
			validatePassageExists($db, $passageId, $internalTestId);
		}

		$audioUrl = null;
		$imageUrl = null;

		if (isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] === UPLOAD_ERR_OK) {
			try {
				$audioUrl = fh_upload_file($_FILES['audio_file'], 'audio');
			} catch (Exception $e) {
				throw new Exception("Lỗi upload audio: " . $e->getMessage());
			}
		} else {
			$audioUrl = helperGetPostValue('audio_url');
		}
		if ($audioUrl === 'null' || $audioUrl === 'NULL')
			$audioUrl = null;

		if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
			try {
				$imageUrl = fh_upload_file($_FILES['image_file'], 'image');
			} catch (Exception $e) {
				if ($audioUrl && isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] === UPLOAD_ERR_OK) {
					fh_delete_file($audioUrl);
				}
				throw new Exception("Lỗi upload hình ảnh: " . $e->getMessage());
			}
		} else {
			$imageUrl = helperGetPostValue('image_url');
		}
		if ($imageUrl === 'null' || $imageUrl === 'NULL')
			$imageUrl = null;

		helperValidatePartRequirements($part, $content, $audioUrl, $imageUrl, $passageId, $isSubQuestion);

		$questionData = [
			'test_id' => $internalTestId,
			'part' => $part,
			'question_number' => $questionNumber,
			'passage_id' => !empty($passageId) ? $passageId : null,
			'content' => !empty($content) ? trim($content) : null,
			'correct_answer' => strtoupper($correctAnswer),
			'audio_url' => $audioUrl,
			'image_url' => $imageUrl,
			'explanation' => !empty($explanation) ? trim($explanation) : null
		];

		$optionsData = helperNormalizeOptionsData($options, $part);

		$questionId = questionCreateWithOptions($db, $questionData, $optionsData);

		return [
			'success' => true,
			'message' => 'Câu hỏi đã được lưu thành công',
			'data' => [
				'question_id' => $questionId,
				'test_id' => $testId,
				'part' => $part,
				'question_number' => $questionNumber
			]
		];

	} catch (Exception $e) {
		if (isset($audioUrl))
			fh_delete_file($audioUrl);
		if (isset($imageUrl))
			fh_delete_file($imageUrl);

		return [
			'success' => false,
			'message' => $e->getMessage(),
			'code' => 'VALIDATION_ERROR'
		];
	}
}

/**
 * tạo nhiều câu hỏi từ form
 */
function apiCreateQuestionsFromForm(PDO $db)
{
	try {
		$testId = helperGetPostValue('test_id');
		$part = helperGetPostValue('part');
		$questionsJson = helperGetPostValue('questions', '[]');

		$questions = json_decode($questionsJson, true);
		if (!is_array($questions)) {
			throw new Exception("Định dạng dữ liệu câu hỏi không hợp lệ");
		}

		$internalTestId = helperGetInternalTestId($db, $testId);
		if (!$internalTestId) {
			throw new Exception("Đề thi không tồn tại");
		}

		validateToeicPart($part);

		$createdQuestions = [];
		$errors = [];

		foreach ($questions as $index => $questionData) {
			try {
				if (empty($questionData['question_number'])) {
					throw new Exception("Câu " . ($index + 1) . ": Thiếu số thứ tự câu hỏi");
				}

				if (empty($questionData['options'])) {
					throw new Exception("Câu " . ($index + 1) . ": Thiếu đáp án");
				}

				$qData = [
					'test_id' => $internalTestId,
					'part' => $part,
					'question_number' => $questionData['question_number'],
					'passage_id' => $questionData['passage_id'] ?? null,
					'content' => $questionData['content'] ?? null,
					'correct_answer' => strtoupper($questionData['correct_answer'] ?? ''),
					'audio_url' => $questionData['audio_url'] ?? null,
					'image_url' => $questionData['image_url'] ?? null,
					'explanation' => $questionData['explanation'] ?? null
				];

				validateQuestionContent($qData['content'], $part);
				validateCorrectAnswer($qData['correct_answer']);
				validateOptions($questionData['options'], $part);

				$questionId = questionCreate($db, $qData);
				$createdQuestions[] = [
					'question_id' => $questionId,
					'question_number' => $qData['question_number']
				];

			} catch (Exception $e) {
				$errors[] = "Câu " . ($index + 1) . ": " . $e->getMessage();
			}
		}

		$response = [
			'success' => count($errors) === 0,
			'message' => count($createdQuestions) . ' câu hỏi được tạo thành công',
			'created_count' => count($createdQuestions),
			'created_questions' => $createdQuestions
		];

		if (count($errors) > 0) {
			$response['errors'] = $errors;
			$response['message'] .= ' (' . count($errors) . ' lỗi)';
		}

		return $response;

	} catch (Exception $e) {
		return [
			'success' => false,
			'message' => 'Lỗi: ' . $e->getMessage(),
			'code' => 'VALIDATION_ERROR'
		];
	}
}

/**
 * lấy danh sách câu hỏi cho một bài thi
 */
function apiGetQuestions(PDO $db, $testId)
{
	try {
		$internalTestId = helperGetInternalTestId($db, $testId);
		if (!$internalTestId) {
			throw new Exception("Đề thi không tồn tại");
		}

		$sql = "SELECT * FROM questions 
                WHERE test_id = :test_id 
                ORDER BY part ASC, question_number ASC";

		$stmt = $db->prepare($sql);
		$stmt->execute([':test_id' => $internalTestId]);
		$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

		if (!empty($questions)) {
			$questionIds = array_column($questions, 'id');
			$placeholders = implode(',', array_fill(0, count($questionIds), '?'));
			$optionsSql = "SELECT id, question_id, label, content, translation FROM options WHERE question_id IN ($placeholders) ORDER BY question_id ASC, label ASC";
			$optionsStmt = $db->prepare($optionsSql);
			$optionsStmt->execute($questionIds);

			$optionsByQuestion = [];
			foreach ($optionsStmt->fetchAll(PDO::FETCH_ASSOC) as $option) {
				$optionsByQuestion[$option['question_id']][] = [
					'id' => $option['id'],
					'label' => $option['label'],
					'content' => $option['content'],
					'translation' => $option['translation']
				];
			}

			foreach ($questions as &$question) {
				$question['options'] = $optionsByQuestion[$question['id']] ?? [];
			}
		}

		return [
			'success' => true,
			'data' => $questions,
			'count' => count($questions)
		];

	} catch (Exception $e) {
		return [
			'success' => false,
			'message' => $e->getMessage()
		];
	}
}

/**
 * lấy thông tin một câu hỏi
 */
function apiGetQuestion(PDO $db, $questionId)
{
	try {
		$sql = "SELECT * FROM questions WHERE id = :id";
		$stmt = $db->prepare($sql);
		$stmt->execute([':id' => $questionId]);
		$question = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$question) {
			throw new Exception("Câu hỏi không tồn tại");
		}

		$optionsSql = "SELECT id, label, content, translation FROM options WHERE question_id = :question_id ORDER BY label";
		$optionsStmt = $db->prepare($optionsSql);
		$optionsStmt->execute([':question_id' => $questionId]);
		$question['options'] = $optionsStmt->fetchAll(PDO::FETCH_ASSOC);

		return [
			'success' => true,
			'data' => $question
		];

	} catch (Exception $e) {
		return [
			'success' => false,
			'message' => $e->getMessage()
		];
	}
}

/**
 * tạo một đoạn văn mới
 */
function apiCreatePassage(PDO $db)
{
	try {
		$testId = helperGetPostValue('test_id');
		$part = helperGetPostValue('part');
		$content = helperGetPostValue('content');
		if ($content === 'null' || $content === 'NULL')
			$content = null;

		$internalTestId = helperGetInternalTestId($db, $testId);
		if (empty($testId))
			throw new Exception("test_id là bắt buộc");
		if (!$internalTestId)
			throw new Exception("Đề thi không tồn tại");

		$audioUrl = null;
		$imageUrl = null;

		if (isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] === UPLOAD_ERR_OK) {
			try {
				$audioUrl = fh_upload_file($_FILES['audio_file'], 'audio');
			} catch (Exception $e) {
				throw new Exception("Lỗi upload audio: " . $e->getMessage());
			}
		} else {
			$audioUrl = helperGetPostValue('audio_url');
		}
		if ($audioUrl === 'null' || $audioUrl === 'NULL')
			$audioUrl = null;

		if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
			try {
				$imageUrl = fh_upload_file($_FILES['image_file'], 'image');
			} catch (Exception $e) {
				if ($audioUrl)
					fh_delete_file($audioUrl);
				throw new Exception("Lỗi upload hình ảnh: " . $e->getMessage());
			}
		} else {
			$imageUrl = helperGetPostValue('image_url');
		}
		if ($imageUrl === 'null' || $imageUrl === 'NULL')
			$imageUrl = null;

		if (!empty($part)) {
			helperValidatePassageMediaRequirements($part, $audioUrl, $imageUrl);
		}

		$passageData = [
			'test_id' => $internalTestId,
			'content' => ($content !== null && $content !== '') ? trim($content) : null,
			'audio_url' => $audioUrl,
			'image_url' => $imageUrl
		];

		$passageId = passageCreate($db, $passageData);

		return [
			'success' => true,
			'message' => 'Đoạn văn đã được tạo thành công',
			'data' => [
				'passage_id' => $passageId,
				'test_id' => $testId
			]
		];

	} catch (Exception $e) {
		if (isset($audioUrl))
			fh_delete_file($audioUrl);
		if (isset($imageUrl))
			fh_delete_file($imageUrl);

		return [
			'success' => false,
			'message' => $e->getMessage(),
			'code' => 'VALIDATION_ERROR'
		];
	}
}

/**
 * lấy các đoạn văn của một bài thi
 */
function apiGetPassages(PDO $db, $testId)
{
	try {
		$internalTestId = helperGetInternalTestId($db, $testId);
		if (!$internalTestId) {
			throw new Exception("Đề thi không tồn tại");
		}

		$passages = passageGetByTestId($db, $internalTestId);

		return [
			'success' => true,
			'data' => $passages,
			'count' => count($passages)
		];

	} catch (Exception $e) {
		return [
			'success' => false,
			'message' => $e->getMessage()
		];
	}
}

/**
 * xóa một câu hỏi
 */
function apiDeleteQuestion(PDO $db, $questionId)
{
	try {
		$question = questionGetById($db, $questionId);

		if (!$question)
			throw new Exception("Câu hỏi không tồn tại");

		if ($question['audio_url'])
			fh_delete_file($question['audio_url']);
		if ($question['image_url'])
			fh_delete_file($question['image_url']);

		questionDelete($db, $questionId);

		return [
			'success' => true,
			'message' => 'Câu hỏi đã được xóa thành công'
		];

	} catch (Exception $e) {
		return [
			'success' => false,
			'message' => $e->getMessage()
		];
	}
}

/**
 * xóa một đoạn văn
 */
function apiDeletePassage(PDO $db, $passageId)
{
	try {
		$passage = passageGetById($db, $passageId);
		if (!$passage)
			throw new Exception("Đoạn văn không tồn tại");

		// xóa file của passage
		if ($passage['audio_url'])
			fh_delete_file($passage['audio_url']);
		if ($passage['image_url'])
			fh_delete_file($passage['image_url']);

		// lấy danh sách câu hỏi thuộc passage này để dọn dẹp file
		$sql = "SELECT id, audio_url, image_url FROM questions WHERE passage_id = :passage_id";
		$stmt = $db->prepare($sql);
		$stmt->execute([':passage_id' => $passageId]);
		$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

		foreach ($questions as $q) {
			if ($q['audio_url'])
				fh_delete_file($q['audio_url']);
			if ($q['image_url'])
				fh_delete_file($q['image_url']);

			// xóa options (nếu db không có on delete cascade)
			$db->prepare("DELETE FROM options WHERE question_id = ?")->execute([$q['id']]);
		}

		// xóa các câu hỏi
		$db->prepare("DELETE FROM questions WHERE passage_id = ?")->execute([$passageId]);

		// cuối cùng xóa passage
		passageDelete($db, $passageId);

		return [
			'success' => true,
			'message' => 'Đoạn văn đã được xóa thành công'
		];

	} catch (Exception $e) {
		return [
			'success' => false,
			'message' => $e->getMessage()
		];
	}
}


/**
 * ==========================================
 * CÁC HÀM TIỆN ÍCH NỘI BỘ (HELPER FUNCTIONS)
 * ==========================================
 */

function helperGetPostValue($key, $default = null)
{
	return isset($_POST[$key]) ? $_POST[$key] : $default;
}

function helperNormalizeOptionsData($options, $part = null)
{
	$partNum = intval($part);
	$labels = ($partNum === 2) ? ['A', 'B', 'C'] : ['A', 'B', 'C', 'D'];
	$normalized = [];
	foreach ($labels as $label) {
		$value = $options[$label] ?? '';
		$normalized[] = [
			'label' => $label,
			'content' => is_array($value) ? ($value['content'] ?? '') : $value,
			'translation' => is_array($value) ? trim($value['translation'] ?? '') : null
		];
	}

	return $normalized;
}

function helperGetInternalTestId(PDO $db, $uuid)
{
	try {
		$sql = "SELECT id FROM tests WHERE uuid = :uuid";
		$stmt = $db->prepare($sql);
		$stmt->execute([':uuid' => $uuid]);
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		return $result ? (int) $result['id'] : false;
	} catch (Exception $e) {
		return false;
	}
}

function helperValidatePassageMediaRequirements($part, $audioUrl, $imageUrl)
{
	$part = intval($part);
	switch ($part) {
		case 1:
			if (empty($imageUrl) && empty($audioUrl))
				throw new Exception("Part 1: Cần có ít nhất hình ảnh hoặc âm thanh");
			break;
		case 2:
		case 3:
		case 4:
			// audio is optional for listening parts now
			break;
	}
}

function helperValidatePartRequirements($part, $content, $audioUrl, $imageUrl, $passageId, $isSubQuestion = false)
{
	$part = intval($part);
	switch ($part) {
		case 1:
			if (!$isSubQuestion && empty($imageUrl) && empty($audioUrl))
				throw new Exception("Part 1: Cần có ít nhất hình ảnh hoặc âm thanh");
			break;
		case 2:
			// part 2 does not need question content nor audio
			break;
		case 3:
		case 4:
			// audio is optional for listening parts now
			if (empty($content))
				throw new Exception("Part $part: Nội dung câu hỏi là bắt buộc");
			break;
		case 5:
		case 6:
		case 7:
			if (empty($content))
				throw new Exception("Part $part: Nội dung câu hỏi là bắt buộc");
			break;
	}
}

/**
 * cập nhật một câu hỏi
 */
function apiUpdateQuestion(PDO $db, $questionId)
{
	try {
		// kiểm tra câu hỏi tồn tại
		$question = questionGetById($db, $questionId);
		if (!$question) {
			throw new Exception("Câu hỏi không tồn tại");
		}

		$part = helperGetPostValue('part', $question['part']);
		$questionNumber = helperGetPostValue('question_number', $question['question_number']);
		$passageId = helperGetPostValue('passage_id', $question['passage_id']);
		$content = helperGetPostValue('content', $question['content']);
		$correctAnswer = helperGetPostValue('correct_answer', $question['correct_answer']);
		$explanation = helperGetPostValue('explanation', $question['explanation']);
		$isSubQuestion = !empty($passageId);

		if ($content === 'null' || $content === 'NULL')
			$content = null;
		if ($explanation === 'null' || $explanation === 'NULL')
			$explanation = null;

		$optionsJson = helperGetPostValue('options');
		$options = $optionsJson ? json_decode($optionsJson, true) : null;

		validateToeicPart($part);
		validateQuestionNumber($questionNumber, $part);
		if (isset($content)) {
			validateQuestionContent($content, $part);
		}
		if (isset($correctAnswer)) {
			validateCorrectAnswer($correctAnswer);
		}
		if (isset($options)) {
			validateOptions($options, $part);
		}

		if (!empty($passageId)) {
			validatePassageExists($db, $passageId, $question['test_id']);
		}

		$audioUrl = $question['audio_url'];
		$imageUrl = $question['image_url'];

		if (isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] === UPLOAD_ERR_OK) {
			try {
				if ($audioUrl) {
					fh_delete_file($audioUrl);
				}
				$audioUrl = fh_upload_file($_FILES['audio_file'], 'audio');
			} catch (Exception $e) {
				throw new Exception("Lỗi upload audio: " . $e->getMessage());
			}
		} else {
			$audioUrl = helperGetPostValue('audio_url', $audioUrl);
		}
		if ($audioUrl === 'null' || $audioUrl === 'NULL')
			$audioUrl = null;

		if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
			try {
				if ($imageUrl) {
					fh_delete_file($imageUrl);
				}
				$imageUrl = fh_upload_file($_FILES['image_file'], 'image');
			} catch (Exception $e) {
				throw new Exception("Lỗi upload hình ảnh: " . $e->getMessage());
			}
		} else {
			$imageUrl = helperGetPostValue('image_url', $imageUrl);
		}
		if ($imageUrl === 'null' || $imageUrl === 'NULL')
			$imageUrl = null;

		helperValidatePartRequirements($part, $content, $audioUrl, $imageUrl, $passageId, $isSubQuestion);

		$questionData = [
			'part' => $part,
			'question_number' => $questionNumber,
			'passage_id' => !empty($passageId) ? $passageId : null,
			'content' => isset($content) ? trim($content) : null,
			'correct_answer' => strtoupper($correctAnswer),
			'audio_url' => $audioUrl,
			'image_url' => $imageUrl,
			'explanation' => isset($explanation) ? trim($explanation) : null
		];

		questionUpdate($db, $questionId, $questionData);

		if (isset($options)) {
			// xóa các đáp án cũ để lưu lại
			$db->prepare("DELETE FROM options WHERE question_id = ?")->execute([$questionId]);
			$optionsData = helperNormalizeOptionsData($options, $part);
			// lưu các đáp án mới
			$stmtOpt = $db->prepare("INSERT INTO options (question_id, label, content, translation) VALUES (:question_id, :label, :content, :translation)");
			foreach ($optionsData as $opt) {
				$stmtOpt->execute([
					'question_id' => $questionId,
					'label' => $opt['label'],
					'content' => $opt['content'] ?? '',
					'translation' => $opt['translation'] ?? null
				]);
			}
		}

		return [
			'success' => true,
			'message' => 'Câu hỏi đã được cập nhật thành công'
		];
	} catch (Exception $e) {
		return [
			'success' => false,
			'message' => $e->getMessage(),
			'code' => 'VALIDATION_ERROR'
		];
	}
}

/**
 * cập nhật đoạn văn
 */
function apiUpdatePassage(PDO $db, $passageId)
{
	try {
		$passage = passageGetById($db, $passageId);
		if (!$passage) {
			throw new Exception("Đoạn văn không tồn tại");
		}

		$part = helperGetPostValue('part');
		$content = helperGetPostValue('content', $passage['content']);
		if ($content === 'null' || $content === 'NULL')
			$content = null;

		$audioUrl = $passage['audio_url'];
		$imageUrl = $passage['image_url'];

		if (isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] === UPLOAD_ERR_OK) {
			try {
				if ($audioUrl) {
					fh_delete_file($audioUrl);
				}
				$audioUrl = fh_upload_file($_FILES['audio_file'], 'audio');
			} catch (Exception $e) {
				throw new Exception("Lỗi upload audio: " . $e->getMessage());
			}
		} else {
			$audioUrl = helperGetPostValue('audio_url', $audioUrl);
		}
		if ($audioUrl === 'null' || $audioUrl === 'NULL')
			$audioUrl = null;

		if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
			try {
				if ($imageUrl) {
					fh_delete_file($imageUrl);
				}
				$imageUrl = fh_upload_file($_FILES['image_file'], 'image');
			} catch (Exception $e) {
				throw new Exception("Lỗi upload hình ảnh: " . $e->getMessage());
			}
		} else {
			$imageUrl = helperGetPostValue('image_url', $imageUrl);
		}
		if ($imageUrl === 'null' || $imageUrl === 'NULL')
			$imageUrl = null;

		if (!empty($part)) {
			helperValidatePassageMediaRequirements($part, $audioUrl, $imageUrl);
		}

		$passageData = [
			'content' => ($content !== null && $content !== '') ? trim($content) : null,
			'audio_url' => $audioUrl,
			'image_url' => $imageUrl
		];

		passageUpdate($db, $passageId, $passageData);

		return [
			'success' => true,
			'message' => 'Đoạn văn đã được cập nhật thành công',
			'data' => [
				'passage_id' => $passageId
			]
		];
	} catch (Exception $e) {
		return [
			'success' => false,
			'message' => $e->getMessage(),
			'code' => 'VALIDATION_ERROR'
		];
	}
}

?>