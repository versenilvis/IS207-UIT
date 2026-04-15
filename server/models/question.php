<?php

class QuestionModel {
    private $db;

    // Khởi tạo model với kết nối PDO
    public function __construct(PDO $dbConnection) {
        $this->db = $dbConnection;
    }

    /**
     * Hàm chính để tạo câu hỏi và đáp án cùng lúc, sử dụng Transaction
     */
    public function createQuestionWithOptions($questionData, $optionsData) {
        try {
            // 1. Bắt đầu Transaction
            $this->db->beginTransaction();

            // 2. Tạo câu hỏi và lấy ID vừa được tạo
            $questionId = $this->create($questionData);

            // 3. Thêm 4 đáp án dựa vào ID của câu hỏi
            $this->addOptions($questionId, $optionsData);

            // 4. Nếu mọi thứ thành công, lưu vĩnh viễn vào database
            $this->db->commit();

            return $questionId; // Trả về ID của câu hỏi mới tạo
        } catch (Exception $e) {
            // 5. Nếu có bất kỳ lỗi nào xảy ra (ở bước 2 hoặc 3), hoàn tác mọi thay đổi
            $this->db->rollBack();
            // Ném lỗi ra ngoài để Controller xử lý (VD: trả về lỗi 500 cho Frontend)
            throw new Exception("Lỗi khi lưu câu hỏi: " . $e->getMessage());
        }
    }

    /**
     * Insert question with all fields
     */
    public function create($data) {
        // Chuẩn bị câu lệnh SQL dựa trên schema `questions`
        $sql = "INSERT INTO questions 
                (test_id, passage_id, part, question_number, content, audio_url, image_url, correct_answer, explanation) 
                VALUES 
                (:test_id, :passage_id, :part, :question_number, :content, :audio_url, :image_url, :correct_answer, :explanation)";

        $stmt = $this->db->prepare($sql);

        // Bind dữ liệu (Gán null cho các trường không bắt buộc nếu không có data)
        $result = $stmt->execute([
            ':test_id'         => $data['test_id'],
            ':passage_id'      => $data['passage_id'] ?? null, // Có thể NULL
            ':part'            => $data['part'],
            ':question_number' => $data['question_number'],
            ':content'         => $data['content'] ?? null,
            ':audio_url'       => $data['audio_url'] ?? null,
            ':image_url'       => $data['image_url'] ?? null,
            ':correct_answer'  => $data['correct_answer'],
            ':explanation'     => $data['explanation'] ?? null
        ]);

        // Check nếu execute thất bại
        if (!$result) {
            throw new Exception("Không thể insert câu hỏi vào database");
        }

        // Trả về ID của dòng dữ liệu vừa được insert (id tự tăng)
        $questionId = $this->db->lastInsertId();
        if (!$questionId) {
            throw new Exception("Lỗi: Không thể lấy ID câu hỏi vừa tạo");
        }

        return $questionId;
    }

    /**
     * Insert 4 answer options
     */
    public function addOptions($questionId, $options) {
        // Chuẩn bị câu lệnh SQL dựa trên schema `options`
        $sql = "INSERT INTO options (question_id, label, content) 
                VALUES (:question_id, :label, :content)";
        
        $stmt = $this->db->prepare($sql);

        // Lặp qua mảng 4 đáp án để insert
        foreach ($options as $option) {
            $result = $stmt->execute([
                ':question_id' => $questionId,
                ':label'       => $option['label'], // Ví dụ: 'A', 'B', 'C', 'D'
                ':content'     => $option['content']
            ]);

            if (!$result) {
                throw new Exception("Không thể insert đáp án cho câu hỏi ID: " . $questionId);
            }
        }
        
        return true;
    }

    /**
     * Lấy thông tin câu hỏi theo ID (bao gồm cả 4 đáp án)
     */
    public function getQuestionWithOptions($questionId) {
        // Query lấy thông tin câu hỏi
        $questionSql = "SELECT * FROM questions WHERE id = :id";
        $stmt = $this->db->prepare($questionSql);
        $stmt->execute([':id' => $questionId]);
        $question = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$question) {
            return null;
        }

        // Query lấy 4 đáp án của câu hỏi
        $optionsSql = "SELECT id, label, content, image_url FROM options WHERE question_id = :question_id ORDER BY label ASC";
        $stmt = $this->db->prepare($optionsSql);
        $stmt->execute([':question_id' => $questionId]);
        $question['options'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $question;
    }

    /**
     * Kiểm tra test_id có tồn tại hay không
     */
    public function testExists($testId) {
        $sql = "SELECT id FROM tests WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $testId]);
        return $stmt->fetch() !== false;
    }

    /**
     * Lấy câu hỏi theo ID (không bao gồm options)
     */
    public function getById($questionId) {
        try {
            $sql = "SELECT id, test_id, passage_id, part, question_number, content, audio_url, image_url, correct_answer, explanation 
                    FROM questions 
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $questionId]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result : null;
        } catch (Exception $e) {
            throw new Exception("Lỗi khi lấy câu hỏi: " . $e->getMessage());
        }
    }

    /**
     * Lấy tất cả câu hỏi của một test
     */
    public function getByTestId($testId) {
        try {
            $sql = "SELECT id, test_id, passage_id, part, question_number, content, audio_url, image_url, correct_answer, explanation 
                    FROM questions 
                    WHERE test_id = :test_id 
                    ORDER BY question_number ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':test_id' => $testId]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Lỗi khi lấy câu hỏi: " . $e->getMessage());
        }
    }

    /**
     * Lấy câu hỏi theo test và part
     */
    public function getByTestAndPart($testId, $part) {
        try {
            $sql = "SELECT id, test_id, passage_id, part, question_number, content, audio_url, image_url, correct_answer, explanation 
                    FROM questions 
                    WHERE test_id = :test_id AND part = :part 
                    ORDER BY question_number ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':test_id' => $testId,
                ':part' => $part
            ]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Lỗi khi lấy câu hỏi: " . $e->getMessage());
        }
    }

    /**
     * Cập nhật câu hỏi
     */
    public function update($questionId, $data) {
        try {
            $updates = [];
            $params = [':id' => $questionId];

            if (isset($data['passage_id'])) {
                $updates[] = "passage_id = :passage_id";
                $params[':passage_id'] = $data['passage_id'];
            }

            if (isset($data['part'])) {
                $updates[] = "part = :part";
                $params[':part'] = $data['part'];
            }

            if (isset($data['question_number'])) {
                $updates[] = "question_number = :question_number";
                $params[':question_number'] = $data['question_number'];
            }

            if (isset($data['content'])) {
                $updates[] = "content = :content";
                $params[':content'] = $data['content'];
            }

            if (isset($data['audio_url'])) {
                $updates[] = "audio_url = :audio_url";
                $params[':audio_url'] = $data['audio_url'];
            }

            if (isset($data['image_url'])) {
                $updates[] = "image_url = :image_url";
                $params[':image_url'] = $data['image_url'];
            }

            if (isset($data['correct_answer'])) {
                $updates[] = "correct_answer = :correct_answer";
                $params[':correct_answer'] = $data['correct_answer'];
            }

            if (isset($data['explanation'])) {
                $updates[] = "explanation = :explanation";
                $params[':explanation'] = $data['explanation'];
            }

            if (empty($updates)) {
                return true;
            }

            $sql = "UPDATE questions SET " . implode(", ", $updates) . " WHERE id = :id";
            $stmt = $this->db->prepare($sql);

            return $stmt->execute($params);
        } catch (Exception $e) {
            throw new Exception("Lỗi khi cập nhật câu hỏi: " . $e->getMessage());
        }
    }

    /**
     * Xóa câu hỏi (cascade delete options)
     */
    public function delete($questionId) {
        try {
            // Xóa options trước (vì có FK constraint)
            $sql = "DELETE FROM options WHERE question_id = :question_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':question_id' => $questionId]);

            // Xóa câu hỏi
            $sql = "DELETE FROM questions WHERE id = :id";
            $stmt = $this->db->prepare($sql);

            return $stmt->execute([':id' => $questionId]);
        } catch (Exception $e) {
            throw new Exception("Lỗi khi xóa câu hỏi: " . $e->getMessage());
        }
    }

    /**
     * Kiểm tra câu hỏi có tồn tại không
     */
    public function exists($questionId) {
        try {
            $sql = "SELECT 1 FROM questions WHERE id = :id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $questionId]);

            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            throw new Exception("Lỗi khi kiểm tra câu hỏi: " . $e->getMessage());
        }
    }

    /**
     * Đếm số câu hỏi trong một test
     */
    public function countByTestId($testId) {
        try {
            $sql = "SELECT COUNT(*) as count FROM questions WHERE test_id = :test_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':test_id' => $testId]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['count'];
        } catch (Exception $e) {
            throw new Exception("Lỗi khi đếm câu hỏi: " . $e->getMessage());
        }
    }

    /**
     * Validate đáp án đúng (phải là A, B, C, hoặc D)
     */
    public function validateCorrectAnswer($answer) {
        return in_array(strtoupper($answer), ['A', 'B', 'C', 'D']);
    }
}
