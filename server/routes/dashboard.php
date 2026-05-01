<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../config/database.php';

// chỉ user đã đăng nhập mới được xem dashboard
// nếu chưa đăng nhập, requireAuth() sẽ trả về 401 và dừng luôn
requireAuth(); // NOTE: hàm này trong auth

// $user_id lấy từ session phía server, không phải từ URL hay body
// điều này đảm bảo user này không thể xem dashboard của user khác
// bằng cách truyền ?user_id=<another_user_id> vào request
//
// IMPORTANT: kể cả admin cũng không thể xem được dashboard của người dùng
// lí do là vì nó lấy id từ session user đăng nhập, kể cả admin, lấy id từ session đó chính là lấy của chính admin
// thì chỉ có hiện dashboard cho admin đó thôi, và cũng như đã nói ở trên, ta không thể truyền tham số để xem theo
// user_id được, nếu muốn phải viết thêm code check if admin
$user_id = $_SESSION['user_id'];

try {
    // lấy điểm cao nhất, số lần thi, thời gian làm bài trung bình của user này
    $stmtOverview = $conn->prepare("SELECT 
        MAX(total_score) as maxScore, 
        COUNT(*) as totalTests, 
        AVG(time_spent) as avgTime 
        FROM attempts WHERE user_id = ?");
    $stmtOverview->execute([$user_id]);
    $overview = $stmtOverview->fetch(PDO::FETCH_ASSOC);

    // lấy dữ liệu cho biểu đồ điểm theo thời gian (10 lần thi gần nhất, sắp xếp cũ -> mới)
    // frontend sẽ dùng mảng này để vẽ line chart
    $stmtChart = $conn->prepare("SELECT 
        DATE_FORMAT(created_at, '%d/%m') as date, 
        total_score, 
        listening_score, 
        reading_score 
        FROM attempts WHERE user_id = ? 
        ORDER BY created_at ASC LIMIT 10");
    $stmtChart->execute([$user_id]);
    $chartData = $stmtChart->fetchAll(PDO::FETCH_ASSOC);

    // lấy 5 lần làm bài gần nhất để hiển thị trong bảng lịch sử
    // LEFT JOIN tests để lấy tên đề thi (nếu đề đã bị xóa thì fallback về 'Đề thi TOEIC')
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