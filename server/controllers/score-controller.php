<?php
// Xử lý logic API nộp bài, chấm điểm, thống kê
require_once __DIR__ . '/../db/config.php';
require_once __DIR__ . '/../models/Attempt.php';

header('Content-Type: application/json; charset=utf-8');

$attemptModel = new Attempt($conn);
$method = $_SERVER['REQUEST_METHOD'];

try {
    // ==========================================
    // LUỒNG 1: XỬ LÝ NỘP BÀI (METHOD POST)
    // ==========================================
    if ($method === 'POST') {
        // Đọc dữ liệu JSON từ Frontend gửi lên
        $inputJSON = file_get_contents('php://input');
        $data = json_decode($inputJSON, true);

        // Kiểm tra xem có test_uuid hay không
        $test_uuid = isset($data['test_uuid']) ? $data['test_uuid'] : '';
        $answers = isset($data['answers']) ? $data['answers'] : [];

        if (empty($test_uuid)) {
            http_response_code(400);
            echo json_encode(['error' => 'Thiếu test_uuid để chấm điểm']);
            exit;
        }

        // 1. Khởi động Session an toàn (Chỉ mở nếu chưa mở)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 2. Lấy thẳng user_id vì logic đã bắt đăng nhập từ đầu
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

        // Bắt lỗi hy hữu: Đang làm bài mà rớt mạng hoặc Session bị hết hạn (Timeout)
        if (!$user_id) {
            http_response_code(401); 
            echo json_encode(['error' => 'Phiên đăng nhập đã hết hạn. Vui lòng tải lại trang và đăng nhập lại!']);
            exit;
        }

        // 3. Gọi hàm chấm điểm và lưu DB trong Model
        $real_attempt_id = $attemptModel->submitAndGrade($user_id, $test_uuid, $answers);

        // Xử lý kết quả trả về
        if (!$real_attempt_id) {
            http_response_code(500);
            echo json_encode(['error' => 'Chấm điểm thất bại hoặc lỗi lưu Database!']);
            exit;
        }

        // 4. Trả về cho JS ID thật để nó chuyển trang
        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'message' => 'Nộp bài và chấm điểm thành công!',
            'attempt_id' => $real_attempt_id 
        ]);
        exit;
    } 
    // ==========================================
    // LUỒNG 2: XỬ LÝ XEM KẾT QUẢ (METHOD GET - CODE CŨ CỦA ÔNG)
    // ==========================================
    else if ($method === 'GET') {
        // Có thể trang results.php sẽ truyền uuid chứ không phải số int
        $attempt_id = isset($_GET['attempt_id']) ? $_GET['attempt_id'] : '';

        if (empty($attempt_id)) {
            http_response_code(400);
            echo json_encode(['error' => 'Thiếu attempt_id']);
            exit;
        }

        // Gọi hàm fetch data từ Model của ông
        $results = $attemptModel->getReviewDetails($attempt_id);

        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'data' => $results
        ]);
        exit;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Lỗi server: ' . $e->getMessage()]);
}
?>