<?php

require_once __DIR__ . '/../controllers/test-controller.php';
$controller = new TestController($db_connection);

// POST /api/passages - Create a new passage
if ($path === '/api/passages' && $method === 'POST') {
    $response = $controller->createPassage();
    http_response_code($response['success'] ? 201 : 400);
    echo json_encode($response);
}
// GET /api/passages - Get passages filtered by test_id
elseif ($path === '/api/passages' && $method === 'GET') {
    $testId = $_GET['test_id'] ?? null;
    
    if ($testId) {
        $response = $controller->getPassages($testId);
    } else {
        $response = ['success' => false, 'message' => 'test_id parameter is required'];
    }
    
    http_response_code($response['success'] ? 200 : 400);
    echo json_encode($response);
}
// DELETE /api/passages/:id - Delete a passage
elseif (preg_match('/\/api\/passages\/(\d+)$/', $path, $matches) && $method === 'DELETE') {
    $response = $controller->deletePassage($matches[1]);
    http_response_code($response['success'] ? 200 : 404);
    echo json_encode($response);
}
?>