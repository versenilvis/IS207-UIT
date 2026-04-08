<?php

require_once __DIR__ . '/../controllers/test-controller.php';
$controller = new TestController($db_connection);

// POST /api/questions - Create a new question
if ($path === '/api/questions' && $method === 'POST') {
    $response = $controller->createQuestion();
    http_response_code($response['success'] ? 201 : 400);
    echo json_encode($response);
}
// GET /api/questions - Get all questions with optional filters
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
// GET /api/questions/:id - Get a single question
elseif (preg_match('/\/api\/questions\/(\d+)$/', $path, $matches) && $method === 'GET') {
    $response = $controller->getQuestion($matches[1]);
    http_response_code($response['success'] ? 200 : 404);
    echo json_encode($response);
}
// DELETE /api/questions/:id - Delete a question
elseif (preg_match('/\/api\/questions\/(\d+)$/', $path, $matches) && $method === 'DELETE') {
    $response = $controller->deleteQuestion($matches[1]);
    http_response_code($response['success'] ? 200 : 404);
    echo json_encode($response);
}
?>