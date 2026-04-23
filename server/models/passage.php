<?php

/**
 * tạo passage (đoạn văn/audio dùng chung cho nhóm câu hỏi)
 */
function passageCreate(PDO $db, $data) {
	try {
		if (empty($data['test_id'])) {
			throw new Exception("test_id là bắt buộc");
		}

		$sql = "INSERT INTO passages 
				(test_id, content, audio_url, image_url) 
				VALUES 
				(:test_id, :content, :audio_url, :image_url)";

		$stmt = $db->prepare($sql);

		$result = $stmt->execute([
			':test_id' => $data['test_id'] ?? null,
			':content' => $data['content'] ?? null,
			':audio_url' => $data['audio_url'] ?? null,
			':image_url' => $data['image_url'] ?? null,
		]);

		if (!$result) {
			throw new Exception("Lỗi khi lưu passage");
		}

		return (int)$db->lastInsertId();
	} catch (Exception $e) {
		throw new Exception("Lỗi tạo passage: " . $e->getMessage());
	}
}

/**
 * lấy passage theo id
 */
function passageGetById(PDO $db, $passageId) {
	try {
		$sql = "SELECT id, test_id, content, audio_url, image_url 
				FROM passages 
				WHERE id = :id";

		$stmt = $db->prepare($sql);
		$stmt->execute([':id' => $passageId]);

		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		return $result ? $result : null;
	} catch (Exception $e) {
		throw new Exception("Lỗi khi lấy passage: " . $e->getMessage());
	}
}

/**
 * lấy tất cả passages của một bài thi
 */
function passageGetByTestId(PDO $db, $testId) {
	try {
		$sql = "SELECT id, test_id, content, audio_url, image_url 
				FROM passages 
				WHERE test_id = :test_id 
				ORDER BY id ASC";

		$stmt = $db->prepare($sql);
		$stmt->execute([':test_id' => $testId]);

		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	} catch (Exception $e) {
		throw new Exception("Lỗi khi lấy passages: " . $e->getMessage());
	}
}

/**
 * cập nhật passage
 */
function passageUpdate(PDO $db, $passageId, $data) {
	try {
		$updates = [];
		$params = [':id' => $passageId];

		if (isset($data['content'])) {
			$updates[] = "content = :content";
			$params[':content'] = $data['content'];
		}

		if (isset($data['audio_url'])) {
			$updates[] = "audio_url = :audio_url";
			$params[':audio_url'] = $data['audio_url'];
		}

		if (isset($data['image_url'])) {
			$updates[] = "image_url = :image_url";
			$params[':image_url'] = $data['image_url'];
		}

		if (empty($updates)) {
			return true;
		}

		$sql = "UPDATE passages SET " . implode(", ", $updates) . " WHERE id = :id";
		$stmt = $db->prepare($sql);
		
		return $stmt->execute($params);
	} catch (Exception $e) {
		throw new Exception("Lỗi khi cập nhật passage: " . $e->getMessage());
	}
}

/**
 * xóa passage
 */
function passageDelete(PDO $db, $passageId) {
	try {
		$sql = "DELETE FROM passages WHERE id = :id";
		$stmt = $db->prepare($sql);
		
		return $stmt->execute([':id' => $passageId]);
	} catch (Exception $e) {
		throw new Exception("Lỗi khi xóa passage: " . $e->getMessage());
	}
}

/**
 * kiểm tra passage có tồn tại không
 */
function passageExists(PDO $db, $passageId, $testId = null) {
	try {
		if ($testId !== null) {
			$sql = "SELECT 1 FROM passages WHERE id = :id AND test_id = :test_id LIMIT 1";
			$params = [':id' => $passageId, ':test_id' => $testId];
		} else {
			$sql = "SELECT 1 FROM passages WHERE id = :id LIMIT 1";
			$params = [':id' => $passageId];
		}

		$stmt = $db->prepare($sql);
		$stmt->execute($params);

		return $stmt->rowCount() > 0;
	} catch (Exception $e) {
		throw new Exception("Lỗi khi kiểm tra passage: " . $e->getMessage());
	}
}

/**
 * lấy số lượng passages của một bài thi
 */
function passageCountByTestId(PDO $db, $testId) {
	try {
		$sql = "SELECT COUNT(*) as count FROM passages WHERE test_id = :test_id";
		$stmt = $db->prepare($sql);
		$stmt->execute([':test_id' => $testId]);

		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		return (int)$result['count'];
	} catch (Exception $e) {
		throw new Exception("Lỗi khi đếm passages: " . $e->getMessage());
	}
}
