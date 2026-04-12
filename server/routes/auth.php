<?php

// gộp các route liên quan đến auth vào đây
// login, register, logout, oauth (tương lai)

require_once __DIR__ . '/../controllers/auth-controller.php';

$action = $parts[1] ?? '';

switch ($action) {
    case 'login':
        if ($method === 'POST') handleLogin();
        else sendError("Method not allowed", 405);
        break;

    case 'register':
        if ($method === 'POST') handleRegister();
        else sendError("Method not allowed", 405);
        break;

    case 'logout':
        handleLogout();
        break;

    default:
        sendError("Chức năng xác thực không tồn tại", 404);
        break;
}
