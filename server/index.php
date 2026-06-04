<?php
// file này gọi là head redirector hay gọi tắt là redirector, sau này nếu thấy commit có ghi
// redirector thì chính là file này

// in ra lỗi PHP, dễ lộ thông tin nhạy cảm và trả về data không cần thiết
ini_set('display_errors', 0);
// ghi lỗi vào file log
ini_set('log_errors', 1);

header('Content-Type: application/json; charset=utf-8');

// handler trả về lỗi chung
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
	if (!(error_reporting() & $errno))
		return;
	http_response_code(500);
	echo json_encode([
		'success' => false,
		'message' => $errstr,
		'error' => ['file' => $errfile, 'line' => $errline]
	]);
	exit();
});

set_exception_handler(function ($exception) {
	http_response_code(500);
	echo json_encode([
		'success' => false,
		'message' => $exception->getMessage()
	]);
	exit();
});

try {
	require_once __DIR__ . '/db/config.php';
	require_once __DIR__ . '/utils/response.php';

	// đầu tiên ta vẫn lấy phần param mà ta cần thông qua .htaccess. 
	// Ví dụ như : api/tests thì ta lấy phần param là "tests"
	// nếu như cấu hình .htaccess bị lỗi hay có lý do nào đó mà không xử lí được
	// ta qua phương án B
	// xoá phần server/api hoặc api/ và chỉ giữ lại phần sau thôi
	$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
	$request = $_GET['request'] ?? '';
	if (empty($request)) {
		$request = str_replace(['/server/api/', '/api/'], '', $path);
	}

	// để api.php định tuyến các routes thay cho redirector
	require_once __DIR__ . '/routes/api.php';

} catch (Exception $e) {
	http_response_code(500);
	echo json_encode([
		'success' => false,
		'message' => $e->getMessage()
	]);
}
