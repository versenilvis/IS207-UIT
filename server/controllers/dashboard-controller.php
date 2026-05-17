<?php
/**
 * @var PDO $conn
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../db/config.php';
require_once __DIR__ . '/../utils/response.php';

// Lấy 5 đề thi gần đây nhất để hiển thị ở trang dashboard.php
function getPastTest(){
    global $conn;
    try{
        $sql = "SELECT t.title, a.listening_score, a.reading_score, a.total_score, a.time_spent
                FROM tests t
                JOIN attempts a ON a.test_id = t.id
                WHERE a.user_id = :id
                ORDER BY a.created_at DESC
                LIMIT 5";
        $stmt = $conn->prepare($sql);
        $id = $_SESSION['user_id'];
        $stmt->execute([
            ':id'      => $id
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }catch(PDOException $e){
        sendError("Lỗi database: " . $e->getMessage(), 500);
        return false;
    }
}


//Hiển thị thời gian trung bình của tất cả bài làm
function getAvgTime(){
    global $conn;
    try{
        $sql = "SELECT ROUND(AVG(time_spent))
                FROM attempts
                WHERE user_id = :id";
        $stmt = $conn->prepare($sql);
        $id = $_SESSION['user_id'];
        $stmt->execute([
            ':id'      => $id
        ]);
        return $stmt->fetchColumn();
    }catch(PDOException $e){
        sendError("Lỗi database: " . $e->getMessage(), 500);
        return false;
    }
}

?>
