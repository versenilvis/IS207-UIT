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
}
?>