<?php
// response json & helpers

function sendJson($data, $status = 200) {
    header("Content-Type: application/json; charset=UTF-8");
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

function sendError($message, $status = 400) {
    sendJson(["error" => $message], $status);
}