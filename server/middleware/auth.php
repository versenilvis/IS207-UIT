<?php

/*
Lý do viết thêm hàm này ngoài hàm requireAuth ở dưới:
Nếu người dùng truy cập vào các trang mà chưa đăng nhập thì sẽ hiện lỗi. Lỗi này sẽ làm lộ ra cấu trúc folder bên trong. 
Tốt nhất là redirect thẳng người dùng về trang home.php nếu chưa đăng nhập
 */
function homeRedirect(){
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // kiểm tra đã đăng nhập hay chưa dựa vào session
    if (!isset($_SESSION['user_id'])) {
        header("Location: home.php");
        exit();
    }

    // kiểm tra tài khoản bị khóa
    global $conn;
    if (isset($conn)) {
        $stmt = $conn->prepare("SELECT is_banned FROM users WHERE id = :id");
        $stmt->execute(['id' => $_SESSION['user_id']]);
        if ((int)$stmt->fetchColumn() === 1) {
            session_destroy();
            header("Location: home.php");
            exit();
        }
    }
}

// + requireAuth nếu cần check đã đăng nhập
function requireAuth() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // kiểm tra đã đăng nhập hay chưa dựa vào session
    if (!isset($_SESSION['user_id'])) {
        sendError("Unauthorized: Vui lòng đăng nhập để xem phần này", 401);
    }

    // kiểm tra tài khoản bị khóa
    global $conn;
    if (isset($conn)) {
        $stmt = $conn->prepare("SELECT is_banned FROM users WHERE id = :id");
        $stmt->execute(['id' => $_SESSION['user_id']]);
        if ((int)$stmt->fetchColumn() === 1) {
            session_destroy();
            sendError("Tài khoản của bạn đã bị đình chỉ hoạt động do vi phạm điều khoản dịch vụ", 403);
        }
    }
}
// + requireAdmin: check chỉ có admin mới được làm
function requireAdmin() {
    requireAuth();
    
    if (($_SESSION['role'] ?? 'user') !== 'admin') {
        sendError("Forbidden: Bạn không có quyền truy cập", 403);
    }
}