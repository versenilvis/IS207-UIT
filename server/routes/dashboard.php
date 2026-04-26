<?php
header('Content-Type: application/json; charset=utf-8');

// 1. Nhúng file kết nối Database 
// Vui lòng sửa lại đường dẫn/tên biến $conn cho đúng với project của bạn nhé.
require_once __DIR__ . '/../config/database.php'; 
// Giả sử biến kết nối của bạn là $conn (mysqli) hoặc $conn

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 2; // Tạm để 2 theo đúng data của bạn

try {
    // Nếu bạn dùng PDO, code sẽ như thế này (Nếu dùng mysqli thì đổi lại cú pháp fetch nhé):
    
    // --- LẤY THỐNG KÊ TỔNG QUAN ---
    $stmtOverview = $conn->prepare("SELECT 
        MAX(total_score) as maxScore, 
        COUNT(*) as totalTests, 
        AVG(time_spent) as avgTime 
        FROM attempts WHERE user_id = ?");
    $stmtOverview->execute([$user_id]);
    $overview = $stmtOverview->fetch(PDO::FETCH_ASSOC);

    // --- LẤY DỮ LIỆU BIỂU ĐỒ (Sắp xếp từ cũ tới mới) ---
    $stmtChart = $conn->prepare("SELECT 
        DATE_FORMAT(created_at, '%d/%m') as date, 
        total_score, 
        listening_score, 
        reading_score 
        FROM attempts WHERE user_id = ? 
        ORDER BY created_at ASC LIMIT 10");
    $stmtChart->execute([$user_id]);
    $chartData = $stmtChart->fetchAll(PDO::FETCH_ASSOC);

    // --- LẤY LỊCH SỬ LÀM BÀI GẦN ĐÂY (Sắp xếp từ mới nhất xuống) ---
    // Giả sử bạn có bảng `tests` để lấy tên đề thi
    $stmtHistory = $conn->prepare("SELECT 
        a.id as attempt_id, 
        a.created_at, 
        COALESCE(t.title, 'Đề thi TOEIC') as test_name, 
        a.listening_score, 
        a.reading_score, 
        a.total_score, 
        a.time_spent as time_taken 
        FROM attempts a 
        LEFT JOIN tests t ON a.test_id = t.id 
        WHERE a.user_id = ? 
        ORDER BY a.created_at DESC LIMIT 5");
    $stmtHistory->execute([$user_id]);
    $history = $stmtHistory->fetchAll(PDO::FETCH_ASSOC);

    // --- TRẢ VỀ JSON ---
    echo json_encode([
        "status" => "success",
        "data" => [
            "overview" => [
                "maxScore" => round($overview['maxScore'] ?? 0),
                "totalTests" => $overview['totalTests'] ?? 0,
                "avgTimeMinutes" => round($overview['avgTime'] ?? 0)
            ],
            "chartData" => $chartData,
            "history" => $history
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Lỗi truy vấn: " . $e->getMessage()]);
}
exit;