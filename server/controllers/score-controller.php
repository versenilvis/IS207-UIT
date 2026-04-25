<?php
// xử lí logic api nộp bài, chấm điểm, thống kê
// server/controllers/score-controller.php
require_once '../db/mysql.php';
require_once '../models/attempt.php';

// Cấp quyền CORS nếu client gọi từ port khác
header('Content-Type: application/json; charset=utf-8');

try {
    // Lấy attempt_id từ URL (VD: api.php?action=get_results&attempt_id=123)
    $attempt_id = isset($_GET['attempt_id']) ? (int)$_GET['attempt_id'] : 0;

    if ($attempt_id === 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Thiếu attempt_id']);
        exit;
    }

    $db = getDatabaseConnection();
    $attemptModel = new Attempt($db);
    
    // Gọi hàm fetch data
    $results = $attemptModel->getReviewDetails($attempt_id);

    // Trả về JSON 
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'data' => $results
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Lỗi server: ' . $e->getMessage()]);
}
?>