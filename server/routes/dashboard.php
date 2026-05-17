<?php
//Gửi api data để vẽ chart trong dashboard

require_once __DIR__ . '/../controllers/dashboard-controller.php';

//Vẽ chart cho từng mục điểm số. Lấy 10 tests gần đây nhất thôi
header('Content-Type: application/json');

//Reverse là do mình phải vẽ chart từ xưa->nay.
$pastTests = array_reverse(getPastTests(10));
//Các array này sẽ được pass qua cho dashboard.js

echo json_encode([
    'labels' => array_map(function ($test) { 
        return date('d/m', strtotime($test['created_at'])); 
    }, $pastTests),
    
    //Đưa tất cả điểm số vào 1 array
    'listening' => array_map(function ($test) {
        return (int) $test['listening_score'];
    }, $pastTests),

    'reading' => array_map(function ($test) {
        return (int) $test['reading_score'];
    }, $pastTests),

    'total' => array_map(function ($test) {
        return (int) $test['total_score'];
    }, $pastTests),

]);
