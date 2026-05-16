<?php
// server/controllers/attempts-controller.php
require_once __DIR__ . '/../db/config.php';
require_once __DIR__ . '/../models/attempt.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Lấy user_id từ session, ở đây mình set tĩnh để bạn dễ test nếu chưa làm xong logic Login
    $user_id = $_SESSION['user_id'] ?? 2; 

    if (!$user_id) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Vui lòng đăng nhập!']);
        exit;
    }

    try {
        // Gọi các hàm từ Model
        $history = getAttemptHistory($conn, $user_id);

        echo json_encode([
            'status' => 'success',
            'data' => [
                'history' => $history
            ]
        ]);
        exit;

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Lỗi server: ' . $e->getMessage()]);
    }
}