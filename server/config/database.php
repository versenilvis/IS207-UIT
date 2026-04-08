<?php
$servername = getenv('DB_HOST') ?: "db";
$username = getenv('MYSQL_USER') ?: "root";
$password = getenv('MYSQL_PASSWORD') ?: "";
$dbname = getenv('MYSQL_DATABASE') ?: "prephub";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("connection failed: " . $conn->connect_error);
}