<?php
// server/models/attempt.php

class Attempt {
    private $db;

    public function __construct($db_connection) {
        $this->db = $db_connection;
    }

    public function getReviewDetails($attempt_id) {
        // Bước 1: Lấy danh sách 200 câu hỏi và lựa chọn của người dùng
        $sqlQuestions = "
            SELECT 
                q.id AS question_id, 
                q.part, 
                q.content AS question_content, 
                q.image_url, 
                q.audio_url, 
                q.correct_answer AS correct_option,
                ua.selected_answer AS user_choice
            FROM questions q
            JOIN attempts a ON a.id = :attempt_id_1 AND q.test_id = a.test_id
            LEFT JOIN attempt_answers ua ON q.id = ua.question_id AND ua.attempt_id = :attempt_id_2
            ORDER BY q.part ASC, q.question_number ASC
        ";

        $stmtQ = $this->db->prepare($sqlQuestions);
        $stmtQ->execute(['attempt_id_1' => $attempt_id, 'attempt_id_2' => $attempt_id]);
        $questions = $stmtQ->fetchAll(PDO::FETCH_ASSOC);

        // Lấy danh sách ID các câu hỏi để lấy đáp án
        $questionIds = array_column($questions, 'question_id');

        if (empty($questionIds)) {
            return []; // Không có câu hỏi nào
        }

        // Bước 2: Lấy tất cả options (A, B, C, D) của các câu hỏi trên
        // Tạo chuỗi '?, ?, ?...' tương ứng với số lượng ID
        $placeholders = implode(',', array_fill(0, count($questionIds), '?'));
        
        $sqlOptions = "
            SELECT question_id, label, content AS option_content 
            FROM options 
            WHERE question_id IN ($placeholders)
            ORDER BY question_id ASC, label ASC
        ";
        
        $stmtO = $this->db->prepare($sqlOptions);
        $stmtO->execute($questionIds);
        $optionsRaw = $stmtO->fetchAll(PDO::FETCH_ASSOC);

        // Gom nhóm options theo từng question_id cho dễ xử lý
        $groupedOptions = [];
        foreach ($optionsRaw as $opt) {
            $qId = $opt['question_id'];
            if (!isset($groupedOptions[$qId])) {
                $groupedOptions[$qId] = [];
            }
            // Chỉ lấy label và nội dung
            $groupedOptions[$qId][$opt['label']] = $opt['option_content'];
        }

        // Bước 3: Gắn options vào từng câu hỏi
        foreach ($questions as &$q) {
            $qId = $q['question_id'];
            $q['options'] = isset($groupedOptions[$qId]) ? $groupedOptions[$qId] : [];
        }

        return $questions;
    }
    public function submitAndGrade($user_id, $test_uuid, $user_answers) {
        try {
            // Bước 1: Lấy ID thật của đề thi
            $stmt = $this->db->prepare("SELECT id FROM tests WHERE uuid = ?");
            $stmt->execute([$test_uuid]);
            $test = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$test) return false;
            $test_id = $test['id'];

            // Bước 2: Lấy đáp án chuẩn 
            $stmt = $this->db->prepare("SELECT id as question_id, question_number, correct_answer, part FROM questions WHERE test_id = ?");
            $stmt->execute([$test_id]);
            $correct_answers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $listening_correct = 0;
            $reading_correct = 0;
            
            // Mảng lưu chi tiết từng câu để chuẩn bị insert
            $details_data = [];

            // Bước 3: Vòng lặp chấm điểm
            foreach ($correct_answers as $row) {
                $qId = $row['question_id'];
                $qNum = $row['question_number'];
                $correctOption = $row['correct_answer'];
                $part = (int)$row['part'];

                // Kiểm tra xem user có chọn câu này không
                $user_choice = isset($user_answers[$qNum]) ? $user_answers[$qNum] : null;
                $is_correct = ($user_choice === $correctOption) ? 1 : 0;

                // Cộng điểm nếu đúng
                if ($is_correct) {
                    if ($part >= 1 && $part <= 4) {
                        $listening_correct++;
                    } else if ($part >= 5 && $part <= 7) {
                        $reading_correct++;
                    }
                }

                // Đưa vào danh sách chi tiết
                $details_data[] = [
                    'question_id' => $qId,
                    'selected_answer' => $user_choice,
                    'is_correct' => $is_correct
                ];
            }

            // Bước 4: Quy đổi điểm chuẩn
            $listening_score = $listening_correct * 5;
            $reading_score = $reading_correct * 5;
            $total_score = $listening_score + $reading_score;

            // Bước 5: LƯU TỔNG ĐIỂM VÀO BẢNG ATTEMPTS
            $insert_sql = "INSERT INTO attempts (uuid, user_id, test_id, listening_correct, reading_correct, listening_score, reading_score, total_score, time_spent, created_at) 
                           VALUES (UUID(), ?, ?, ?, ?, ?, ?, ?, 0, NOW())";
            $stmt = $this->db->prepare($insert_sql);
            $stmt->execute([$user_id, $test_id, $listening_correct, $reading_correct, $listening_score, $reading_score, $total_score]);
            
            // Lấy ID của bài thi vừa lưu xong
            $attempt_id = $this->db->lastInsertId();

            // ==========================================
            // BƯỚC 6 (MỚI): LƯU CHI TIẾT VÀO BẢNG ATTEMPT_ANSWERS
            // Để trang Review biết câu nào đúng, câu nào sai
            // ==========================================
            if (!empty($details_data)) {
                $insert_detail_sql = "INSERT INTO attempt_answers (attempt_id, question_id, selected_answer, is_correct) VALUES (?, ?, ?, ?)";
                $detail_stmt = $this->db->prepare($insert_detail_sql);
                
                foreach ($details_data as $detail) {
                    $detail_stmt->execute([
                        $attempt_id,
                        $detail['question_id'],
                        $detail['selected_answer'],
                        $detail['is_correct']
                    ]);
                }
            }

            return $attempt_id; 

        } catch (PDOException $e) {
            throw new Exception("Lỗi SQL: " . $e->getMessage());
        }
    }
}
?>
