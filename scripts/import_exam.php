<?php
// load environment variables from env file
function loadEnv($file) {
	if (file_exists($file)) {
		$lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		foreach ($lines as $line) {
			if (strpos(trim($line), '#') === 0) {
				continue;
			}
			list($name, $value) = explode('=', $line, 2);
			$name = trim($name);
			$value = trim($value);
			if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
				putenv("{$name}={$value}");
				$_ENV[$name] = $value;
				$_SERVER[$name] = $value;
			}
		}
	}
}

loadEnv(__DIR__ . '/../.env');

// set database credentials
$host = getenv('DB_HOST') ?: '127.0.0.1';
$db = getenv('MYSQL_DATABASE') ?: 'prephub';
$user = getenv('MYSQL_USER') ?: 'root';
$pass = getenv('MYSQL_PASSWORD') ?: '';

try {
	// create pdo connection
	$pdo = new PDO("mysql:host={$host};dbname={$db};charset=utf8mb4", $user, $pass);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
	echo "connection failed: " . $e->getMessage() . "\n";
	exit(1);
}

// read json file
$jsonFile = __DIR__ . '/../tests/decade_listening.json';
if (!file_exists($jsonFile)) {
	echo "file not found: {$jsonFile}\n";
	exit(1);
}

$data = json_decode(file_get_contents($jsonFile), true);
if (!$data) {
	echo "failed to parse json file\n";
	exit(1);
}

// slugify title for test directory
function slugify($text) {
	$unicode = array(
		'a'=>'á|à|ả|ã|ạ|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ|Á|À|Ả|Ã|Ạ|Ă|Ắ|Ằ|Ẳ|Ẵ|Ặ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
		'd'=>'đ|Đ',
		'e'=>'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ|É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
		'i'=>'í|ì|ỉ|ĩ|ị|Í|Ì|Ỉ|Ĩ|Ị',
		'o'=>'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ|Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
		'u'=>'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự|Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
		'y'=>'ý|ỳ|ỷ|ỹ|ỵ|Ý|Ỳ|Ỷ|Ỹ|Ỵ'
	);
	foreach ($unicode as $nonUnicode => $uni) {
		$text = preg_replace("/($uni)/i", $nonUnicode, $text);
	}
	$text = preg_replace('~[^\pL\d]+~u', '-', $text);
	$text = preg_replace('~[^-\w]+~', '', $text);
	$text = trim($text, '-');
	$text = preg_replace('~-+~', '-', $text);
	$text = strtolower($text);
	return empty($text) ? 'n-a' : $text;
}

try {
	// generate slugified directory
	$testTitle = $data['title'] ?? 'scraped exam';
	$slug = slugify($testTitle);
	$targetImageDir = __DIR__ . '/../server/uploads/image/' . $slug;
	if (!is_dir($targetImageDir)) {
		mkdir($targetImageDir, 0777, true);
	}

	// copy image to directory and return database path
	$copyImage = function($imgName, $newBaseName) use ($slug, $targetImageDir) {
		if (empty($imgName)) return null;
		$ext = pathinfo($imgName, PATHINFO_EXTENSION);
		$newFileName = $newBaseName . '.' . $ext;
		
		$sourceFile = __DIR__ . '/../tests/Đề thi thử TOEIC_ Mã đề DECADE _ Thi thử TOEIC Online_files/' . $imgName;
		if (!file_exists($sourceFile)) {
			$sourceFile = __DIR__ . '/../server/uploads/image/' . $imgName;
		}
		if (file_exists($sourceFile)) {
			copy($sourceFile, $targetImageDir . '/' . $newFileName);
		}
		return "/server/uploads/image/" . $slug . "/" . $newFileName;
	};

	// start database transaction
	$pdo->beginTransaction();

	// insert new test in draft mode
	$stmt = $pdo->prepare("INSERT INTO tests (uuid, title, description, duration, audio_url, is_premium, is_active) VALUES (UUID(), :title, :desc, :duration, :audio_url, 0, 0)");
	$stmt->execute([
		'title' => $testTitle,
		'desc' => 'đề thi cào tự động, cần điền đáp án đúng trước khi kích hoạt',
		'duration' => $data['duration'] ?? 2700,
		'audio_url' => (!empty($data['audio_url'])) ? "/server/uploads/audio/" . $data['audio_url'] : null
	]);
	$testId = $pdo->lastInsertId();

	$questionsCount = 0;
	$passagesCount = 0;
	$optionsCount = 0;

	// process parts
	if (isset($data['parts']) && is_array($data['parts'])) {
		foreach ($data['parts'] as $part) {
			$partNumber = $part['part_number'] ?? 1;

			// process single questions
			if (isset($part['questions']) && is_array($part['questions'])) {
				foreach ($part['questions'] as $q) {
					$stmtQ = $pdo->prepare("INSERT INTO questions (test_id, part, question_number, content, image_url, audio_url, correct_answer) VALUES (:test_id, :part, :question_number, :content, :image_url, :audio_url, :correct_answer)");
					$stmtQ->execute([
						'test_id' => $testId,
						'part' => $partNumber,
						'question_number' => $q['question_number'],
						'content' => $q['content'] ?: null,
						'image_url' => $copyImage($q['image_url'] ?? null, 'question_' . $q['question_number']),
						'audio_url' => (!empty($q['audio_url'])) ? "/server/uploads/audio/" . $q['audio_url'] : null,
						'correct_answer' => $q['correct_answer'] ?? 'A'
					]);
					$questionId = $pdo->lastInsertId();
					$questionsCount++;

					// insert options
					if (isset($q['options']) && is_array($q['options'])) {
						$stmtOpt = $pdo->prepare("INSERT INTO options (question_id, label, content) VALUES (:question_id, :label, :content)");
						foreach ($q['options'] as $opt) {
							$stmtOpt->execute([
								'question_id' => $questionId,
								'label' => $opt['label'],
								'content' => $opt['content'] ?? ''
							]);
							$optionsCount++;
						}
					}
				}
			}

			// process passage groups
			if (isset($part['passages']) && is_array($part['passages'])) {
				foreach ($part['passages'] as $passage) {
					$newBaseName = 'passage';
					if (!empty($passage['questions'])) {
						$nums = array_column($passage['questions'], 'question_number');
						if (!empty($nums)) {
							$newBaseName = 'question_' . min($nums) . '_' . max($nums);
						}
					}
					
					$isPlaceholder = preg_match('/^Questions \d+ - \d+:$/i', trim($passage['content'] ?? ''));
					$contentVal = null;
					$translationEnVal = null;

					if ($isPlaceholder) {
						$contentVal = trim($passage['content']);
					} else {
						$nums = array_column($passage['questions'] ?? [], 'question_number');
						if (!empty($nums)) {
							$contentVal = "Questions " . min($nums) . " - " . max($nums) . ":";
						} else {
							$contentVal = "Questions:";
						}
						$translationEnVal = trim($passage['content']);
					}

					$stmtPass = $pdo->prepare("INSERT INTO passages (test_id, content, translation_en, image_url, audio_url) VALUES (:test_id, :content, :translation_en, :image_url, :audio_url)");
					$stmtPass->execute([
						'test_id' => $testId,
						'content' => $contentVal,
						'translation_en' => $translationEnVal,
						'image_url' => $copyImage($passage['image_url'] ?? null, $newBaseName),
						'audio_url' => (!empty($passage['audio_url'])) ? "/server/uploads/audio/" . $passage['audio_url'] : null
					]);
					$passageId = $pdo->lastInsertId();
					$passagesCount++;

					// insert sub questions
					if (isset($passage['questions']) && is_array($passage['questions'])) {
						foreach ($passage['questions'] as $q) {
							$stmtQ = $pdo->prepare("INSERT INTO questions (test_id, passage_id, part, question_number, content, image_url, audio_url, correct_answer) VALUES (:test_id, :passage_id, :part, :question_number, :content, :image_url, :audio_url, :correct_answer)");
							$stmtQ->execute([
								'test_id' => $testId,
								'passage_id' => $passageId,
								'part' => $partNumber,
								'question_number' => $q['question_number'],
								'content' => $q['content'] ?: null,
								'image_url' => $copyImage($q['image_url'] ?? null, 'question_' . $q['question_number']),
								'audio_url' => (!empty($q['audio_url'])) ? "/server/uploads/audio/" . $q['audio_url'] : null,
								'correct_answer' => $q['correct_answer'] ?? 'A'
							]);
							$questionId = $pdo->lastInsertId();
							$questionsCount++;

							// insert options for sub question
							if (isset($q['options']) && is_array($q['options'])) {
								$stmtOpt = $pdo->prepare("INSERT INTO options (question_id, label, content) VALUES (:question_id, :label, :content)");
								foreach ($q['options'] as $opt) {
									$stmtOpt->execute([
										'question_id' => $questionId,
										'label' => $opt['label'],
										'content' => $opt['content'] ?? ''
									]);
									$optionsCount++;
								}
							}
						}
					}
				}
			}
		}
	}

	// commit database transaction
	$pdo->commit();

	echo "import completed successfully\n";
	echo "test id: {$testId}\n";
	echo "passages imported: {$passagesCount}\n";
	echo "questions imported: {$questionsCount}\n";
	echo "options imported: {$optionsCount}\n";

} catch (Exception $e) {
	// rollback transaction in case of error
	if ($pdo->inTransaction()) {
		$pdo->rollBack();
	}
	echo "failed to import: " . $e->getMessage() . "\n";
	exit(1);
}
