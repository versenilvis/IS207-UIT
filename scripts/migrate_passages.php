<?php
// scripts/migrate_passages.php

require_once __DIR__ . '/../server/db/config.php';

try {
	// lấy tất cả passages
	$stmt = $conn->query("SELECT id, content, translation_en FROM passages");
	$passages = $stmt->fetchAll(PDO::FETCH_ASSOC);

	$updatedCount = 0;
	$conn->beginTransaction();

	foreach ($passages as $passage) {
		$passageId = $passage['id'];
		$content = $passage['content'] ?? '';
		$translationEn = $passage['translation_en'] ?? '';

		// kiểm tra xem có phải là passage cũ chứa nội dung html không
		$isLegacy = str_contains($content, '<') || !preg_match('/^Questions \d+ - \d+:$/', trim($content));

		if ($isLegacy && !empty($content)) {
			// lấy các câu hỏi thuộc passage này để tính range
			$qStmt = $conn->prepare("SELECT question_number FROM questions WHERE passage_id = ? ORDER BY question_number ASC");
			$qStmt->execute([$passageId]);
			$nums = $qStmt->fetchAll(PDO::FETCH_COLUMN);

			$newHeader = 'Questions:';
			if (!empty($nums)) {
				$min = min($nums);
				$max = max($nums);
				$newHeader = "Questions {$min} - {$max}:";
			}

			// nếu translation_en rỗng thì sao chép content cũ sang
			$newTranslationEn = empty($translationEn) ? $content : $translationEn;

			// cập nhật lại db
			$uStmt = $conn->prepare("UPDATE passages SET content = ?, translation_en = ? WHERE id = ?");
			$uStmt->execute([$newHeader, $newTranslationEn, $passageId]);
			$updatedCount++;
		}
	}

	$conn->commit();
	echo "migrated: {$updatedCount} passages\n";

} catch (Exception $e) {
	if ($conn->inTransaction()) {
		$conn->rollBack();
	}
	echo "migration failed: " . $e->getMessage() . "\n";
}
