<?php

require_once __DIR__ . '/../controllers/question-controller.php';
$controller = new QuestionController($db_connection);

// GET /api/tests - Lấy tất cả các bài kiểm tra hoạt động (để chọn từ danh sách thả xuống)
if ($path === '/api/tests' && $method === 'GET') {
    $response = $controller->getTests();
    http_response_code($response['success'] ? 200 : 400);
    echo json_encode($response);
}
// GET /api/tests/:id - Lấy một bài kiểm tra duy nhất
elseif (preg_match('/\/api\/tests\/(\d+)$/', $path, $matches) && $method === 'GET') {
    $response = $controller->getTest($matches[1]);
    http_response_code($response['success'] ? 200 : 404);
    echo json_encode($response);
}
?>