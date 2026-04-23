<?php

require_once __DIR__ . '/../controllers/question-controller.php';

// POST /api/questions - tạo một câu hỏi mới
if ($path === '/api/questions' && $method === 'POST') {
	$response = apiCreateQuestion($db_connection);
	http_response_code($response['success'] ? 201 : 400);
	echo json_encode($response);
}
// GET /api/questions - lấy tất cả câu hỏi theo test_id
elseif ($path === '/api/questions' && $method === 'GET') {
	$testId = $_GET['test_id'] ?? null;

	if ($testId) {
		$response = apiGetQuestions($db_connection, $testId);
	} else {
		$response = ['success' => false, 'message' => 'test_id parameter is required'];
	}

	http_response_code($response['success'] ? 200 : 400);
	echo json_encode($response);
}
// GET /api/questions/:id - lấy một câu hỏi duy nhất
elseif (preg_match('/\/api\/questions\/(\d+)$/', $path, $matches) && $method === 'GET') {
	$response = apiGetQuestion($db_connection, $matches[1]);
	http_response_code($response['success'] ? 200 : 404);
	echo json_encode($response);
}
// DELETE /api/questions/:id - xóa một câu hỏi
elseif (preg_match('/\/api\/questions\/(\d+)$/', $path, $matches) && $method === 'DELETE') {
	$response = apiDeleteQuestion($db_connection, $matches[1]);
	http_response_code($response['success'] ? 200 : 404);
	echo json_encode($response);
}