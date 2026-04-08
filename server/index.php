<?php

// Suppress display of errors, log them instead
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Set error handler to catch fatal errors
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $errstr,
        'error' => true,
        'file' => $errfile,
        'line' => $errline
    ]);
    exit();
});

// Set exception handler
set_exception_handler(function($exception) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $exception->getMessage(),
        'error' => true
    ]);
    exit();
});

try {
    require_once __DIR__ . '/config/database.php';

    // Parse request
    $method = $_SERVER['REQUEST_METHOD'];

    // Get path from query parameter or from REQUEST_URI
    if (isset($_GET['path'])) {
        $path = $_GET['path'];
    } else {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $path = str_replace('/IS207-UIT/server', '', $path);
    }

    // Ensure path starts with /
    if (strpos($path, '/') !== 0) {
        $path = '/' . $path;
    }

    // Route according to resource
    if (strpos($path, '/api/questions') === 0) {
        require __DIR__ . '/routes/questions.php';
    } elseif (strpos($path, '/api/passages') === 0) {
        require __DIR__ . '/routes/passages.php';
    } elseif (strpos($path, '/api/tests') === 0) {
        require __DIR__ . '/routes/tests.php';
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Route not found', 'path' => $path]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error' => true
    ]);
}

?>