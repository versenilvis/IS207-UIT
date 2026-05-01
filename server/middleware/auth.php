<?php


// + requirAuth nếu cần check đã đăng nhập
function requireAuth() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // kiểm tra đã đăng nhập hay chưa dựa vào session
    if (!isset($_SESSION['user_id'])) {
        sendError("Unauthorized: Vui lòng đăng nhập để xem phần này", 401);
    }
}
// + requireAdmin: check chỉ có admin mới được làm
function requireAdmin() {
    requireAuth();
    
    if (($_SESSION['role'] ?? 'user') !== 'admin') {
        sendError("Forbidden: Bạn không có quyền truy cập", 403);
    }
}