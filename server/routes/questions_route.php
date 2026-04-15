<?php

require_once __DIR__ . '/../controllers/question-controller.php';
$controller = new QuestionController($db_connection);

// POST /api/questions - Tạo một câu hỏi mới
if ($path === '/api/questions' && $method === 'POST') {
    $response = $controller->createQuestion();
    http_response_code($response['success'] ? 201 : 400);
    echo json_encode($response);
}
// GET /api/questions - Lấy tất cả câu hỏi với các bộ lọc tùy chọn
elseif ($path === '/api/questions' && $method === 'GET') {
    $testId = $_GET['test_id'] ?? null;
    
    if ($testId) {
        $response = $controller->getQuestions($testId);
    } else {
        $response = ['success' => false, 'message' => 'test_id parameter is required'];
    }
    
    http_response_code($response['success'] ? 200 : 400);
    echo json_encode($response);
}
// GET /api/questions/:id - Lấy một câu hỏi duy nhất
elseif (preg_match('/\/api\/questions\/(\d+)$/', $path, $matches) && $method === 'GET') {
    $response = $controller->getQuestion($matches[1]);
    http_response_code($response['success'] ? 200 : 404);
    echo json_encode($response);
}
// DELETE /api/questions/:id - Xóa một câu hỏi
elseif (preg_match('/\/api\/questions\/(\d+)$/', $path, $matches) && $method === 'DELETE') {
    $response = $controller->deleteQuestion($matches[1]);
    http_response_code($response['success'] ? 200 : 404);
    echo json_encode($response);
}
?>