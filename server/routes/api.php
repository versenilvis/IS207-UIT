<?php

$method = $_SERVER['REQUEST_METHOD'];
// explode sẽ chia request thành 1 mảng các string bằng việc cắt dấu "/"
$parts = explode('/', trim($request, '/'));
// NOTE: vì .htaccess nó đã có sẵn /api/* rồi nên resource sẽ lấy từ phần tiếp theo. VD: /api/[...] -> lấy phần [...]
$resource = $parts[0] ?? '';

switch ($resource) {
	case 'auth':
		require_once __DIR__ . '/auth.php';
		break;

	case 'tests':
		require_once __DIR__ . '/tests.php';
		break;

	case 'questions':
		require_once __DIR__ . '/questions_route.php';
		break;

	case 'passages':
		require_once __DIR__ . '/passages.php';
		break;

	default:
		http_response_code(404);
		echo json_encode([
			'success' => false,
			'message' => 'API Route not found',
			'debug' => ['resource' => $resource]
		]);
		break;
}