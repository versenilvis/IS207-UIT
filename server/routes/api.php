<?php

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/IS207-UIT/server', '', $path);

// Route theo resource
if (strpos($path, '/api/questions') === 0) {
    require 'routes/questions.php';
} elseif (strpos($path, '/api/passages') === 0) {
    require 'routes/passages.php';
} elseif (strpos($path, '/api/tests') === 0) {
    require 'routes/tests.php';
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Route not found']);
}
?>