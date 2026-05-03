<?php

require_once __DIR__ . '/../controllers/question-controller.php';
require_once __DIR__ . '/../middleware/auth.php';

// toàn bộ /api/passages có admin được truy cập
requireAdmin();

// POST /api/passages - tạo một đoạn văn mới
if ($path === '/api/passages' && $method === 'POST') {
	$response = apiCreatePassage($db_connection);
	http_response_code($response['success'] ? 201 : 400);
	echo json_encode($response);
}
// GET /api/passages - lấy các đoạn văn theo test_id
elseif ($path === '/api/passages' && $method === 'GET') {
	$testId = $_GET['test_id'] ?? null;

	if ($testId) {
		$response = apiGetPassages($db_connection, $testId);
	} else {
		$response = ['success' => false, 'message' => 'test_id parameter is required'];
	}

	http_response_code($response['success'] ? 200 : 400);
	echo json_encode($response);
}
// DELETE /api/passages/:id - xóa một đoạn văn
elseif (preg_match('/\/api\/passages\/(\d+)$/', $path, $matches) && $method === 'DELETE') {
	$response = apiDeletePassage($db_connection, $matches[1]);
	http_response_code($response['success'] ? 200 : 404);
	echo json_encode($response);
}
?>