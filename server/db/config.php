<?php
// DB: MySQL - v8.0
$servername = getenv('DB_HOST') ?: "db";
$username = getenv('MYSQL_USER') ?: "root";
$password = getenv('MYSQL_PASSWORD') ?: "";
$dbname = getenv('MYSQL_DATABASE') ?: "prephub";

// Tạo kết nối (NOTE: dùng cú pháp PDO)
try {
	$conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
	die("Connection failed: " . $e->getMessage());
}