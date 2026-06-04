<?php
// file bootstrap cho testing
// thiết lập môi trường và nạp các models

require_once __DIR__ . '/../server/config/env.php';
require_once __DIR__ . '/../server/db/config.php';
require_once __DIR__ . '/../server/models/attempt.php';

// khởi tạo session nếu chưa được bật
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class TestTracker {
    public static $assertions = 0;
}
