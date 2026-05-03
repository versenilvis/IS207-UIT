<?php

require_once __DIR__ . '/../controllers/test-controller.php';
require_once __DIR__ . '/../middleware/auth.php';

// ở biến $method và $part, vì file api.php nó import file tests.php,
// nên là tất cả các biến trong api.php đều có thể được sử dụng trong file tests.php
// nói chung $method và $parts được kế thừa từ api.php

if ($method === 'GET') {
    // GET /api/tests - danh sách đề thi, công khai
	// như đã giải thích ở file api.php, $parts chia ra thành các string theo dấu "/"
	// nếu như phần $parts[1] trống nghĩa là chỉ đơn giản là /api/tests không kèm theo 1 uuid cụ thể nào
	// thì trả về getTestList tức là các đề không cần đăng nhập cũng xem được
    if (empty($parts[1])) {
        getTestList();
    }
    // GET /api/tests/{uuid} - chi tiết đề thi, cần đăng nhập
	// $part[1] là uuid, truyền tham số uuid vào getTestCore lấy đề theo uuid
    else {
        requireAuth();
        getTestCore($parts[1]);
    }
} elseif ($method === 'POST') {
    // POST /api/tests - tạo đề mới, chỉ admin
    requireAdmin();
    createTest();
} elseif ($method === 'PUT') {
    // PUT /api/tests/{uuid} - cập nhật đề, chỉ admin
    requireAdmin();
    if (!empty($parts[1])) {
        updateTest($parts[1]);
    } else {
        sendError("Thiếu ID đề thi", 400);
    }
} elseif ($method === 'DELETE') {
    // DELETE /api/tests/{uuid} - xóa đề, chỉ admin
    requireAdmin();
    if (!empty($parts[1])) {
        deleteTest($parts[1]);
    } else {
        sendError("Thiếu ID đề thi", 400);
    }
} else {
    sendError("Phương thức không được hỗ trợ cho Tests", 405);
}