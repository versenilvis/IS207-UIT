<?php

require_once __DIR__ . '/../controllers/test-controller.php';
$controller = new TestController($db_connection);

// GET /api/tests - Get all active tests (for dropdown)
if ($path === '/api/tests' && $method === 'GET') {
    $response = $controller->getTests();
    http_response_code($response['success'] ? 200 : 400);
    echo json_encode($response);
}
// GET /api/tests/:id - Get a single test
elseif (preg_match('/\/api\/tests\/(\d+)$/', $path, $matches) && $method === 'GET') {
    $response = $controller->getTest($matches[1]);
    http_response_code($response['success'] ? 200 : 404);
    echo json_encode($response);
}
?>