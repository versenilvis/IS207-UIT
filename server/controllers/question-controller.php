<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/question.php';
require_once __DIR__ . '/../models/passage.php';
require_once __DIR__ . '/../utils/validator.php';
require_once __DIR__ . '/../utils/fileHandler.php';
require_once __DIR__ . '/../utils/response.php';

class QuestionController {
    private $db;
    private $questionModel;
    private $passageModel;

    public function __construct(PDO $dbConnection) {
        $this->db = $dbConnection;
        $this->questionModel = new QuestionModel($dbConnection);
        $this->passageModel = new PassageModel($dbConnection);
    }

    /**
     * Tạo một câu hỏi mới với các tệp phương tiện tùy chọn
     * 
     * Dữ liệu POST dự kiến:
     * - test_id (bắt buộc)
     * - part (bắt buộc, 1-7)
     * - question_number (bắt buộc)
     * - passage_id (tùy chọn, cho Part 3, 4, 7)
     * - content (tùy chọn cho Part 1, bắt buộc cho Part 2-7)
     * - options (bắt buộc) - mảng với các khóa A, B, C, D
     * - correct_answer (bắt buộc) - A/B/C/D
     * - explanation (tùy chọn)
     * - audio_file (tùy chọn, trong $_FILES)
     * - image_file (tùy chọn, trong $_FILES)
     * 
     * @return array - Phản hồi JSON
     */
    public function createQuestion() {
        try {
            // Bước 1: Lấy và xác thực dữ liệu yêu cầu
            $testId = $this->getPostValue('test_id');
            $part = $this->getPostValue('part');
            $questionNumber = $this->getPostValue('question_number');
            $passageId = $this->getPostValue('passage_id');
            $content = $this->getPostValue('content');
            $correctAnswer = $this->getPostValue('correct_answer');
            $explanation = $this->getPostValue('explanation');
            $isSubQuestion = !empty($passageId); // Nếu có passage_id, đây là sub-question
            
            // Lấy các đáp án từ POST
            $options = json_decode($this->getPostValue('options', '{}'), true);
            if (!is_array($options)) {
                throw new Exception("Định dạng đáp án không hợp lệ");
            }

            // Bước 2: Xác thực tất cả các đầu vào bằng Validator
            Validator::validateToeicPart($part);
            Validator::validateQuestionNumber($questionNumber, $part);
            Validator::validateQuestionContent($content, $part);
            Validator::validateCorrectAnswer($correctAnswer);
            Validator::validateOptions($options);
            
            // Xác thực giải thích nếu được cung cấp
            if (!empty($explanation)) {
                Validator::validateExplanation($explanation);
            }

            // Xác thực bài kiểm tra tồn tại
            if (!$this->testExists($testId)) {
                throw new Exception("Đề thi không tồn tại hoặc không hoạt động");
            }

            // Bước 3: Xác thực đoạn văn nếu được cung cấp
            $passage = null;
            if (!empty($passageId)) {
                Validator::validatePassageExists($this->db, $passageId, $testId);
            }

            // Bước 4: Xử lý tải tệp lên hoặc giữ các URL hiện có
            $audioUrl = null;
            $imageUrl = null;

            // Tải tệp âm thanh lên nếu được cung cấp
            if (isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] === UPLOAD_ERR_OK) {
                try {
                    $audioUrl = FileHandler::uploadFile($_FILES['audio_file'], 'audio');
                } catch (Exception $e) {
                    throw new Exception("Lỗi upload audio: " . $e->getMessage());
                }
            } else {
                // Keep existing audio URL if provided
                $audioUrl = $this->getPostValue('audio_url');
            }

            // Tải tệp hình ảnh lên nếu được cung cấp
            if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
                try {
                    $imageUrl = FileHandler::uploadFile($_FILES['image_file'], 'image');
                } catch (Exception $e) {
                    // Xóa tệp âm thanh đã tải lên nếu tải hình ảnh không thành công
                    if ($audioUrl && isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] === UPLOAD_ERR_OK) {
                        FileHandler::deleteFile($audioUrl);
                    }
                    throw new Exception("Lỗi upload hình ảnh: " . $e->getMessage());
                }
            } else {
                // Keep existing image URL if provided
                $imageUrl = $this->getPostValue('image_url');
            }

            // Bước 5: Xác thực yêu cầu cụ thể cho phần
            $this->validatePartRequirements($part, $content, $audioUrl, $imageUrl, $passageId, $isSubQuestion);

            // Bước 6: Chuẩn bị dữ liệu câu hỏi
            $questionData = [
                'test_id' => $testId,
                'part' => $part,
                'question_number' => $questionNumber,
                'passage_id' => !empty($passageId) ? $passageId : null,
                'content' => !empty($content) ? trim($content) : null,
                'correct_answer' => strtoupper($correctAnswer),
                'audio_url' => $audioUrl,
                'image_url' => $imageUrl,
                'explanation' => !empty($explanation) ? trim($explanation) : null
            ];

            // Chuẩn bị dữ liệu đáp án
            $optionsData = [
                ['label' => 'A', 'content' => $options['A']],
                ['label' => 'B', 'content' => $options['B']],
                ['label' => 'C', 'content' => $options['C']],
                ['label' => 'D', 'content' => $options['D']]
            ];

            // Bước 7: Tạo câu hỏi với các đáp án trong cơ sở dữ liệu
            $questionId = $this->questionModel->createQuestionWithOptions($questionData, $optionsData);

            // Trả về phản hồi thành công
            return [
                'success' => true,
                'message' => 'Câu hỏi đã được lưu thành công',
                'data' => [
                    'question_id' => $questionId,
                    'test_id' => $testId,
                    'part' => $part,
                    'question_number' => $questionNumber
                ]
            ];

        } catch (Exception $e) {
            // Dọn dẹp các tệp đã tải lên nếu xảy ra lỗi
            if (isset($audioUrl)) {
                FileHandler::deleteFile($audioUrl);
            }
            if (isset($imageUrl)) {
                FileHandler::deleteFile($imageUrl);
            }

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'code' => 'VALIDATION_ERROR'
            ];
        }
    }

    /**
     * Tạo nhiều câu hỏi với hỗ trợ tải tệp lên
     * Chấp nhận FormData với nhiều câu hỏi
     * 
     * @return array - Phản hồi JSON
     */
    public function createQuestionsFromForm() {
        try {
            $testId = $this->getPostValue('test_id');
            $part = $this->getPostValue('part');
            $questionsJson = $this->getPostValue('questions', '[]');
            
            $questions = json_decode($questionsJson, true);
            if (!is_array($questions)) {
                throw new Exception("Định dạng dữ liệu câu hỏi không hợp lệ");
            }

            // Xác thực bài kiểm tra
            if (!$this->testExists($testId)) {
                throw new Exception("Đề thi không tồn tại");
            }

            Validator::validateToeicPart($part);

            $createdQuestions = [];
            $errors = [];

            // Xử lý từng câu hỏi
            foreach ($questions as $index => $questionData) {
                try {
                    // Xác thực các trường bắt buộc
                    if (empty($questionData['question_number'])) {
                        throw new Exception("Câu " . ($index + 1) . ": Thiếu số thứ tự câu hỏi");
                    }
                    
                    if (empty($questionData['options'])) {
                        throw new Exception("Câu " . ($index + 1) . ": Thiếu đáp án");
                    }

                    // Chuẩn bị dữ liệu câu hỏi
                    $qData = [
                        'test_id' => $testId,
                        'part' => $part,
                        'question_number' => $questionData['question_number'],
                        'passage_id' => $questionData['passage_id'] ?? null,
                        'content' => $questionData['content'] ?? null,
                        'correct_answer' => strtoupper($questionData['correct_answer'] ?? ''),
                        'audio_url' => $questionData['audio_url'] ?? null,
                        'image_url' => $questionData['image_url'] ?? null,
                        'explanation' => $questionData['explanation'] ?? null
                    ];

                    // Xác thực
                    Validator::validateQuestionContent($qData['content'], $part);
                    Validator::validateCorrectAnswer($qData['correct_answer']);
                    Validator::validateOptions($questionData['options']);

                    // Tạo câu hỏi
                    $questionId = $this->questionModel->create($qData);
                    $createdQuestions[] = [
                        'question_id' => $questionId,
                        'question_number' => $qData['question_number']
                    ];

                } catch (Exception $e) {
                    $errors[] = "Câu " . ($index + 1) . ": " . $e->getMessage();
                }
            }

            $response = [
                'success' => count($errors) === 0,
                'message' => count($createdQuestions) . ' câu hỏi được tạo thành công',
                'created_count' => count($createdQuestions),
                'created_questions' => $createdQuestions
            ];

            if (count($errors) > 0) {
                $response['errors'] = $errors;
                $response['message'] .= ' (' . count($errors) . ' lỗi)';
            }

            return $response;

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage(),
                'code' => 'VALIDATION_ERROR'
            ];
        }
    }

    /**
     * Xác thực yêu cầu phương tiện cho các đoạn văn dựa trên phần
     * 
     * @param int $part
     * @param string $audioUrl
     * @param string $imageUrl
     * @throws Exception
     */
    private function validatePassageMediaRequirements($part, $audioUrl, $imageUrl) {
        $part = intval($part);

        switch ($part) {
            case 1: // Ảnh
                if (empty($imageUrl) && empty($audioUrl)) {
                    throw new Exception("Part 1: Cần có ít nhất hình ảnh hoặc âm thanh");
                }
                break;

            case 2: // Câu hỏi ngắn
            case 3: // Hội thoại
            case 4: // Độc thoại
                if (empty($audioUrl)) {
                    throw new Exception("Part $part: Âm thanh là bắt buộc cho cụm câu hỏi");
                }
                break;

            case 5: // Đọc câu hoàn chỉnh
            case 6: // Điền từ
            case 7: // Đọc hiểu
                // Phương tiện tùy chọn cho các phần này
                break;
        }
    }

    /**
     * Xác thực yêu cầu cụ thể cho phần
     * 
     * @param int $part
     * @param string|null $content
     * @param string|null $audioUrl
     * @param string|null $imageUrl
     * @param int|null $passageId
     * @param bool $isSubQuestion - Nếu true, bỏ qua xác thực phương tiện (phương tiện ở cấp đoạn văn)
     * @throws Exception
     */
    private function validatePartRequirements($part, $content, $audioUrl, $imageUrl, $passageId, $isSubQuestion = false) {
        $part = intval($part);

        switch ($part) {
            case 1: // Ảnh
                // Với câu hỏi con, không xác thực phương tiện (phương tiện nằm ở đoạn văn)
                if (!$isSubQuestion && empty($imageUrl) && empty($audioUrl)) {
                    throw new Exception("Part 1: Cần có ít nhất hình ảnh hoặc âm thanh");
                }
                break;

            case 2: // Câu hỏi ngắn
            case 3: // Hội thoại
            case 4: // Độc thoại
                // Với câu hỏi con, không xác thực phương tiện (phương tiện nằm ở đoạn văn)
                if (!$isSubQuestion) {
                    if (empty($audioUrl)) {
                        throw new Exception("Part $part: Âm thanh là bắt buộc");
                    }
                }
                if (empty($content)) {
                    throw new Exception("Part $part: Nội dung câu hỏi là bắt buộc");
                }
                break;

            case 5: // Đọc câu hoàn chỉnh
            case 6: // Điền từ
                if (empty($content)) {
                    throw new Exception("Part $part: Nội dung câu hỏi là bắt buộc");
                }
                break;

            case 7: // Đọc hiểu
                // Tùy chọn: đoạn văn
                if (empty($content)) {
                    throw new Exception("Part 7: Nội dung câu hỏi là bắt buộc");
                }
                break;
        }
    }

    /**
     * Kiểm tra xem bài kiểm tra có tồn tại và hoạt động không
     * 
     * @param int $testId
     * @return bool
     */
    private function testExists($testId) {
        try {
            $sql = "SELECT id FROM tests WHERE id = :id AND is_active = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $testId]);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Lấy giá trị POST với giá trị mặc định tùy chọn
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    private function getPostValue($key, $default = null) {
        return isset($_POST[$key]) ? $_POST[$key] : $default;
    }

    /**
     * Lấy tất cả các câu hỏi cho một bài kiểm tra
     * 
     * @param int $testId
     * @return array
     */
    public function getQuestions($testId) {
        try {
            if (!$this->testExists($testId)) {
                throw new Exception("Đề thi không tồn tại");
            }

            $sql = "SELECT * FROM questions 
                    WHERE test_id = :test_id 
                    ORDER BY part ASC, question_number ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':test_id' => $testId]);
            $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Lấy các đáp án cho mỗi câu hỏi
            $optionsSql = "SELECT id, label, content FROM options WHERE question_id = :question_id ORDER BY label";
            $optionsStmt = $this->db->prepare($optionsSql);
            
            foreach ($questions as &$question) {
                $optionsStmt->execute([':question_id' => $question['id']]);
                $question['options'] = $optionsStmt->fetchAll(PDO::FETCH_ASSOC);
                // GỠ LỖI: Ghi nhật ký giá trị phần của mỗi câu hỏi
                error_log("Question {$question['id']}: part = {$question['part']}, question_number = {$question['question_number']}");
            }

            error_log("getQuestions trả về " . count($questions) . " câu hỏi cho test_id $testId");

            return [
                'success' => true,
                'data' => $questions,
                'count' => count($questions)
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Lấy một câu hỏi duy nhất theo ID
     * 
     * @param int $questionId
     * @return array
     */
    public function getQuestion($questionId) {
        try {
            $sql = "SELECT * FROM questions WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $questionId]);
            $question = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$question) {
                throw new Exception("Câu hỏi không tồn tại");
            }

            // Lấy các đáp án
            $optionsSql = "SELECT id, label, content FROM options WHERE question_id = :question_id ORDER BY label";
            $optionsStmt = $this->db->prepare($optionsSql);
            $optionsStmt->execute([':question_id' => $questionId]);
            $question['options'] = $optionsStmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $question
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Lấy tất cả các bài kiểm tra hoạt động (để chọn từ danh sách thả xuống)
     * 
     * @return array
     */
    public function getTests() {
        try {
            $sql = "SELECT id, title, description, is_premium, is_active, created_at FROM tests ORDER BY created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $tests = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Convert is_premium and is_active to integers for consistency
            foreach ($tests as &$test) {
                $test['is_premium'] = (int)$test['is_premium'];
                $test['is_active'] = (int)$test['is_active'];
            }

            return [
                'success' => true,
                'data' => $tests,
                'count' => count($tests)
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Lấy một bài kiểm tra duy nhất theo ID
     * 
     * @param int $testId
     * @return array
     */
    public function getTest($testId) {
        try {
            // Lấy chi tiết bài kiểm tra
            $sql = "SELECT * FROM tests WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $testId]);
            $test = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$test) {
                throw new Exception("Đề thi không tồn tại");
            }

            // Lấy tất cả các câu hỏi cho bài kiểm tra này
            $questionsSql = "SELECT * FROM questions WHERE test_id = :test_id ORDER BY part ASC, question_number ASC";
            $questionsStmt = $this->db->prepare($questionsSql);
            $questionsStmt->execute([':test_id' => $testId]);
            $questions = $questionsStmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => [
                    'test' => $test,
                    'questions' => $questions,
                    'question_count' => count($questions)
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Tạo một đoạn văn mới
     * 
     * Dữ liệu POST dự kiến:
     * - test_id (bắt buộc)
     * - content (tùy chọn)
     * - audio_file (tùy chọn, trong $_FILES)
     * - image_file (tùy chọn, trong $_FILES)
     * 
     * @return array
     */
    public function createPassage() {
        try {
            $testId = $this->getPostValue('test_id');
            $part = $this->getPostValue('part');
            $content = $this->getPostValue('content');
            
            if (empty($testId)) {
                throw new Exception("test_id là bắt buộc");
            }

            if (!$this->testExists($testId)) {
                throw new Exception("Đề thi không tồn tại");
            }

            // Xử lý tải tệp lên hoặc giữ các URL hiện có
            $audioUrl = null;
            $imageUrl = null;

            if (isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] === UPLOAD_ERR_OK) {
                try {
                    $audioUrl = FileHandler::uploadFile($_FILES['audio_file'], 'audio');
                } catch (Exception $e) {
                    throw new Exception("Lỗi upload audio: " . $e->getMessage());
                }
            } else {
                // Keep existing audio URL if provided
                $audioUrl = $this->getPostValue('audio_url');
            }

            if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
                try {
                    $imageUrl = FileHandler::uploadFile($_FILES['image_file'], 'image');
                } catch (Exception $e) {
                    if ($audioUrl) {
                        FileHandler::deleteFile($audioUrl);
                    }
                    throw new Exception("Lỗi upload hình ảnh: " . $e->getMessage());
                }
            } else {
                // Giữ URL hình ảnh hiện có nếu được cung cấp
                $imageUrl = $this->getPostValue('image_url');
            }

            // Xác thực yêu cầu phương tiện cụ thể cho phần
            if (!empty($part)) {
                $this->validatePassageMediaRequirements($part, $audioUrl, $imageUrl);
            }

            // Chuẩn bị dữ liệu đoạn văn
            $passageData = [
                'test_id' => $testId,
                'content' => !empty($content) ? trim($content) : null,
                'audio_url' => $audioUrl,
                'image_url' => $imageUrl
            ];

            // Tạo đoạn văn
            $passageId = $this->passageModel->create($passageData);

            return [
                'success' => true,
                'message' => 'Đoạn văn đã được tạo thành công',
                'data' => [
                    'passage_id' => $passageId,
                    'test_id' => $testId
                ]
            ];

        } catch (Exception $e) {
            if (isset($audioUrl)) {
                FileHandler::deleteFile($audioUrl);
            }
            if (isset($imageUrl)) {
                FileHandler::deleteFile($imageUrl);
            }

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'code' => 'VALIDATION_ERROR'
            ];
        }
    }

    /**
     * Lấy các đoạn văn cho một bài kiểm tra
     * 
     * @param int $testId
     * @return array
     */
    public function getPassages($testId) {
        try {
            if (!$this->testExists($testId)) {
                throw new Exception("Đề thi không tồn tại");
            }

            $sql = "SELECT id, test_id, content, audio_url, image_url FROM passages 
                    WHERE test_id = :test_id 
                    ORDER BY id ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':test_id' => $testId]);
            $passages = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $passages,
                'count' => count($passages)
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Xóa một câu hỏi
     * 
     * @param int $questionId
     * @return array
     */
    public function deleteQuestion($questionId) {
        try {
            // Kiểm tra xem câu hỏi có tồn tại không
            $sql = "SELECT id, audio_url, image_url FROM questions WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $questionId]);
            $question = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$question) {
                throw new Exception("Câu hỏi không tồn tại");
            }

            // Xóa các tệp
            if ($question['audio_url']) {
                FileHandler::deleteFile($question['audio_url']);
            }
            if ($question['image_url']) {
                FileHandler::deleteFile($question['image_url']);
            }

            // Xóa câu hỏi
            $deleteSql = "DELETE FROM questions WHERE id = :id";
            $deleteStmt = $this->db->prepare($deleteSql);
            $deleteStmt->execute([':id' => $questionId]);

            return [
                'success' => true,
                'message' => 'Câu hỏi đã được xóa thành công'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Xóa một đoạn văn
     * 
     * @param int $passageId
     * @return array
     */
    public function deletePassage($passageId) {
        try {
            // Lấy đoạn văn để lấy đường dẫn tệp
            $sql = "SELECT audio_url, image_url FROM passages WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $passageId]);
            $passage = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$passage) {
                throw new Exception("Đoạn văn không tồn tại");
            }

            // Xóa các tệp
            if ($passage['audio_url']) {
                FileHandler::deleteFile($passage['audio_url']);
            }
            if ($passage['image_url']) {
                FileHandler::deleteFile($passage['image_url']);
            }

            // Lấy tất cả các câu hỏi được liên kết với đoạn văn này trước khi xóa chúng
            $questionsSql = "SELECT id, audio_url, image_url FROM questions WHERE passage_id = :passage_id";
            $questionsStmt = $this->db->prepare($questionsSql);
            $questionsStmt->execute([':passage_id' => $passageId]);
            $questions = $questionsStmt->fetchAll(PDO::FETCH_ASSOC);

            // Xóa các tệp phương tiện và đáp án cho mỗi câu hỏi
            foreach ($questions as $question) {
                // Xóa các tệp phương tiện của câu hỏi
                if ($question['audio_url']) {
                    FileHandler::deleteFile($question['audio_url']);
                }
                if ($question['image_url']) {
                    FileHandler::deleteFile($question['image_url']);
                }
                
                // Xóa các đáp án câu hỏi
                $deleteOptionsSql = "DELETE FROM options WHERE question_id = :question_id";
                $deleteOptionsStmt = $this->db->prepare($deleteOptionsSql);
                $deleteOptionsStmt->execute([':question_id' => $question['id']]);
            }

            // Xóa tất cả các câu hỏi được liên kết với đoạn văn này
            $deleteQuestionsSql = "DELETE FROM questions WHERE passage_id = :passage_id";
            $deleteQuestionsStmt = $this->db->prepare($deleteQuestionsSql);
            $deleteQuestionsStmt->execute([':passage_id' => $passageId]);

            // Xóa đoạn văn
            $deleteSql = "DELETE FROM passages WHERE id = :id";
            $deleteStmt = $this->db->prepare($deleteSql);
            $deleteStmt->execute([':id' => $passageId]);

            return [
                'success' => true,
                'message' => 'Đoạn văn đã được xóa thành công'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
