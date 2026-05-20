<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (str_starts_with($path, '/api/')) {
    require __DIR__ . '/server/index.php';
    exit();
}

header("Location: /client/");
exit();
