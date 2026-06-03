<?php
// import_answers.php
// usage: php scripts/import_answers.php --json <path> --test-id <id> [--activate]
//        php scripts/import_answers.php --json <path> --test-uuid <uuid> [--activate]

function loadEnv($file) {
	if (!file_exists($file)) return;
	foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
		if (str_starts_with(trim($line), '#')) continue;
		[$name, $value] = array_pad(explode('=', $line, 2), 2, '');
		$name  = trim($name);
		$value = trim($value);
		if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
			putenv("{$name}={$value}");
			$_ENV[$name] = $value;
			$_SERVER[$name] = $value;
		}
	}
}

loadEnv(__DIR__ . '/../.env');

// ── parse CLI args ────────────────────────────────────────────

$opts = getopt('', ['json:', 'test-id:', 'test-uuid:', 'activate']);

$jsonFile = $opts['json']      ?? null;
$testId   = $opts['test-id']   ?? null;
$testUuid = $opts['test-uuid'] ?? null;
$activate = isset($opts['activate']);

if (!$jsonFile) {
	fwrite(STDERR, "usage: php import_answers.php --json <file> [--test-id <id> | --test-uuid <uuid>] [--activate]\n");
	exit(1);
}

if (!file_exists($jsonFile)) {
	fwrite(STDERR, "file not found: {$jsonFile}\n");
	exit(1);
}

$data = json_decode(file_get_contents($jsonFile), true);
if (!$data || !isset($data['answers'])) {
	fwrite(STDERR, "invalid json or missing 'answers' key\n");
	exit(1);
}

// ── db connection ─────────────────────────────────────────────

$host = getenv('DB_HOST')        ?: '127.0.0.1';
$db   = getenv('MYSQL_DATABASE') ?: 'prephub';
$user = getenv('MYSQL_USER')     ?: 'root';
$pass = getenv('MYSQL_PASSWORD') ?: '';

try {
	$pdo = new PDO("mysql:host={$host};dbname={$db};charset=utf8mb4", $user, $pass);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
	fwrite(STDERR, "connection failed: " . $e->getMessage() . "\n");
	exit(1);
}

// ── resolve test_id ───────────────────────────────────────────

if (!$testId && $testUuid) {
	$stmt = $pdo->prepare("SELECT id FROM tests WHERE uuid = ?");
	$stmt->execute([$testUuid]);
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	if (!$row) {
		fwrite(STDERR, "no test found with uuid: {$testUuid}\n");
		exit(1);
	}
	$testId = $row['id'];
}

if (!$testId) {
	fwrite(STDERR, "must provide --test-id or --test-uuid\n");
	exit(1);
}

// ── verify test exists ────────────────────────────────────────

$stmt = $pdo->prepare("SELECT id, title FROM tests WHERE id = ?");
$stmt->execute([$testId]);
$test = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$test) {
	fwrite(STDERR, "no test found with id: {$testId}\n");
	exit(1);
}

echo "test: [{$test['id']}] {$test['title']}\n";
echo "answer source: {$data['exam_title']}\n";
echo str_repeat('-', 50) . "\n";

// ── update correct answers ────────────────────────────────────

try {
	$pdo->beginTransaction();

	$stmtUpdate = $pdo->prepare(
		"UPDATE questions SET correct_answer = ? WHERE test_id = ? AND question_number = ?"
	);
	$stmtCheck = $pdo->prepare(
		"SELECT id FROM questions WHERE test_id = ? AND question_number = ? LIMIT 1"
	);
	$stmtTranslation = $pdo->prepare(
		"UPDATE options SET translation = ? WHERE question_id = ? AND label = ?"
	);
	$stmtPassageId = $pdo->prepare(
		"SELECT passage_id FROM questions WHERE id = ? LIMIT 1"
	);
	$stmtUpdatePassage = $pdo->prepare(
		"UPDATE passages SET translation = ? WHERE id = ?"
	);
	$stmtUpdatePassageEn = $pdo->prepare(
		"UPDATE passages SET translation_en = ? WHERE id = ?"
	);
	$stmtUpdateQuestionAudio = $pdo->prepare(
		"UPDATE questions SET audio_url = ? WHERE id = ?"
	);
	$stmtUpdatePassageAudio = $pdo->prepare(
		"UPDATE passages SET audio_url = ? WHERE id = ?"
	);

	$updated         = 0;
	$translations    = 0;
	$passagesUpdated = 0;
	$notFound        = [];

	foreach ($data['answers'] as $entry) {
		$num    = (int) $entry['question_number'];
		$answer = strtoupper(trim($entry['correct_answer']));

		if (!in_array($answer, ['A', 'B', 'C', 'D'])) {
			echo "  skip q{$num}: invalid answer '{$answer}'\n";
			continue;
		}

		// check existence and get question id
		$stmtCheck->execute([$testId, $num]);
		$qRow = $stmtCheck->fetch(PDO::FETCH_ASSOC);
		if (!$qRow) {
			$notFound[] = $num;
			continue;
		}

		$questionId = $qRow['id'];
		$stmtUpdate->execute([$answer, $testId, $num]);
		$updated++;

		// update translations for each option if present
		if (!empty($entry['options'])) {
			foreach ($entry['options'] as $opt) {
				$label       = strtoupper(trim($opt['label']));
				$translation = trim($opt['translation'] ?? '');
				if ($label && $translation) {
					$stmtTranslation->execute([$translation, $questionId, $label]);
					if ($stmtTranslation->rowCount() > 0) $translations++;
				}
			}
		}

		// update question audio if present
		$qAudio = trim($entry['audio_url'] ?? '');
		if ($qAudio) {
			$stmtUpdateQuestionAudio->execute([$qAudio, $questionId]);
		}

		// update passage translation, english transcript, and passage audio if present
		$passageTrans = trim($entry['passage_translation'] ?? '');
		$passageEng   = trim($entry['passage_english'] ?? '');
		$passageAudio = trim($entry['passage_audio'] ?? '');
		if ($passageTrans || $passageEng || $passageAudio) {
			$stmtPassageId->execute([$questionId]);
			$pRow = $stmtPassageId->fetch(PDO::FETCH_ASSOC);
			if ($pRow && $pRow['passage_id']) {
				if ($passageTrans) {
					$stmtUpdatePassage->execute([$passageTrans, $pRow['passage_id']]);
					if ($stmtUpdatePassage->rowCount() > 0) $passagesUpdated++;
				}
				if ($passageEng) {
					$stmtUpdatePassageEn->execute([$passageEng, $pRow['passage_id']]);
					$stmtGetPassage = $pdo->prepare("SELECT content FROM passages WHERE id = ?");
					$stmtGetPassage->execute([$pRow['passage_id']]);
					$pContent = $stmtGetPassage->fetchColumn();
					if (str_contains($pContent, '<')) {
						$qStmt = $pdo->prepare("SELECT question_number FROM questions WHERE passage_id = ? ORDER BY question_number ASC");
						$qStmt->execute([$pRow['passage_id']]);
						$nums = $qStmt->fetchAll(PDO::FETCH_COLUMN);
						$newHeader = 'Questions:';
						if (!empty($nums)) {
							$min = min($nums);
							$max = max($nums);
							$newHeader = "Questions {$min} - {$max}:";
						}
						$pdo->prepare("UPDATE passages SET content = ? WHERE id = ?")->execute([$newHeader, $pRow['passage_id']]);
					}
				}
				if ($passageAudio) {
					$stmtUpdatePassageAudio->execute([$passageAudio, $pRow['passage_id']]);
				}
			}
		}
	}

	// activate test if requested
	if ($activate) {
		$pdo->prepare("UPDATE tests SET is_active = 1, description = NULL WHERE id = ?")
		    ->execute([$testId]);
		echo "✓ test activated\n";
	}

	$pdo->commit();

} catch (Exception $e) {
	if ($pdo->inTransaction()) $pdo->rollBack();
	fwrite(STDERR, "failed: " . $e->getMessage() . "\n");
	exit(1);
}

// ── summary ───────────────────────────────────────────────────

echo "✓ updated:   {$updated} questions\n";
echo "✓ translations: {$translations} options\n";
echo "✓ passages:     {$passagesUpdated} translated\n";

if (!empty($notFound)) {
	echo "✗ not found: " . count($notFound) . " questions → [" . implode(', ', $notFound) . "]\n";
} else {
	echo "✓ all questions matched\n";
}

// verify: count questions where answer is a valid letter
$stmt = $pdo->prepare(
	"SELECT COUNT(*) as total,
	        SUM(correct_answer IN ('A','B','C','D')) as valid
	 FROM questions WHERE test_id = ?"
);
$stmt->execute([$testId]);
$counts = $stmt->fetch(PDO::FETCH_ASSOC);
echo "── verify: {$counts['valid']}/{$counts['total']} questions have valid answer\n";
