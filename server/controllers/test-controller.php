<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/question.php';
require_once __DIR__ . '/../models/passage.php';
require_once __DIR__ . '/../utils/validator.php';
require_once __DIR__ . '/../utils/fileHandler.php';
require_once __DIR__ . '/../utils/response.php';

class TestController {
    private $db;
    private $questionModel;
    private $passageModel;

    public function __construct(PDO $dbConnection) {
        $this->db = $dbConnection;
        $this->questionModel = new QuestionModel($dbConnection);
        $this->passageModel = new PassageModel($dbConnection);
    }

    /**
     * Create a new question with optional media files
     * 
     * Expected POST data:
     * - test_id (required)
     * - part (required, 1-7)
     * - question_number (required)
     * - passage_id (optional, for Part 3, 4, 7)
     * - content (optional for Part 1, required for Part 2-7)
     * - options (required) - array with keys A, B, C, D
     * - correct_answer (required) - A/B/C/D
     * - explanation (optional)
     * - audio_file (optional, in $_FILES)
     * - image_file (optional, in $_FILES)
     * 
     * @return array - JSON response
     */
    public function createQuestion() {
        try {
            // Step 1: Get and validate request data
            $testId = $this->getPostValue('test_id');
            $part = $this->getPostValue('part');
            $questionNumber = $this->getPostValue('question_number');
            $passageId = $this->getPostValue('passage_id');
            $content = $this->getPostValue('content');
            $correctAnswer = $this->getPostValue('correct_answer');
            $explanation = $this->getPostValue('explanation');
            $isSubQuestion = !empty($passageId); // Nếu có passage_id, đây là sub-question
            
            // Get options from POST
            $options = json_decode($this->getPostValue('options', '{}'), true);
            if (!is_array($options)) {
                throw new Exception("Định dạng đáp án không hợp lệ");
            }

            // Step 2: Validate all inputs using Validator
            Validator::validateToeicPart($part);
            Validator::validateQuestionNumber($questionNumber, $part);
            Validator::validateQuestionContent($content, $part);
            Validator::validateCorrectAnswer($correctAnswer);
            Validator::validateOptions($options);
            
            // Validate explanation if provided
            if (!empty($explanation)) {
                Validator::validateExplanation($explanation);
            }

            // Validate test exists
            if (!$this->testExists($testId)) {
                throw new Exception("Đề thi không tồn tại hoặc không hoạt động");
            }

            // Step 3: Validate passage if provided
            $passage = null;
            if (!empty($passageId)) {
                Validator::validatePassageExists($this->db, $passageId, $testId);
            }

            // Step 4: Handle file uploads or keep existing URLs
            $audioUrl = null;
            $imageUrl = null;

            // Upload audio file if provided
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

            // Upload image file if provided
            if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
                try {
                    $imageUrl = FileHandler::uploadFile($_FILES['image_file'], 'image');
                } catch (Exception $e) {
                    // Delete uploaded audio if image upload fails
                    if ($audioUrl && isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] === UPLOAD_ERR_OK) {
                        FileHandler::deleteFile($audioUrl);
                    }
                    throw new Exception("Lỗi upload hình ảnh: " . $e->getMessage());
                }
            } else {
                // Keep existing image URL if provided
                $imageUrl = $this->getPostValue('image_url');
            }

            // Step 5: Validate part-specific requirements
            $this->validatePartRequirements($part, $content, $audioUrl, $imageUrl, $passageId, $isSubQuestion);

            // Step 6: Prepare question data
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

            // Prepare options data
            $optionsData = [
                ['label' => 'A', 'content' => $options['A']],
                ['label' => 'B', 'content' => $options['B']],
                ['label' => 'C', 'content' => $options['C']],
                ['label' => 'D', 'content' => $options['D']]
            ];

            // Step 7: Create question with options in database
            $questionId = $this->questionModel->createQuestionWithOptions($questionData, $optionsData);

            // Return success response
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
            // Clean up uploaded files if error occurs
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
     * Create multiple questions with file upload support
     * Accepts FormData with multiple questions
     * 
     * @return array - JSON response
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

            // Validate test
            if (!$this->testExists($testId)) {
                throw new Exception("Đề thi không tồn tại");
            }

            Validator::validateToeicPart($part);

            $createdQuestions = [];
            $errors = [];

            // Process each question
            foreach ($questions as $index => $questionData) {
                try {
                    // Validate required fields
                    if (empty($questionData['question_number'])) {
                        throw new Exception("Câu " . ($index + 1) . ": Thiếu số thứ tự câu hỏi");
                    }
                    
                    if (empty($questionData['options'])) {
                        throw new Exception("Câu " . ($index + 1) . ": Thiếu đáp án");
                    }

                    // Prepare question data
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

                    // Validate
                    Validator::validateQuestionContent($qData['content'], $part);
                    Validator::validateCorrectAnswer($qData['correct_answer']);
                    Validator::validateOptions($questionData['options']);

                    // Create question
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
     * Validate media requirements for passages based on part
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
                // Media tùy chọn cho các part này
                break;
        }
    }

    /**
     * Validate part-specific requirements
     * 
     * @param int $part
     * @param string|null $content
     * @param string|null $audioUrl
     * @param string|null $imageUrl
     * @param int|null $passageId
     * @param bool $isSubQuestion - Nếu true, bỏ qua validate media (media nằm ở passage level)
     * @throws Exception
     */
    private function validatePartRequirements($part, $content, $audioUrl, $imageUrl, $passageId, $isSubQuestion = false) {
        $part = intval($part);

        switch ($part) {
            case 1: // Ảnh
                // Với sub-questions, không validate media (media nằm ở passage)
                if (!$isSubQuestion && empty($imageUrl) && empty($audioUrl)) {
                    throw new Exception("Part 1: Cần có ít nhất hình ảnh hoặc âm thanh");
                }
                break;

            case 2: // Câu hỏi ngắn
            case 3: // Hội thoại
            case 4: // Độc thoại
                // Với sub-questions, không validate media (media nằm ở passage)
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
     * Check if test exists and is active
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
     * Get POST value with optional default
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    private function getPostValue($key, $default = null) {
        return isset($_POST[$key]) ? $_POST[$key] : $default;
    }

    /**
     * Get all questions for a test
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

            // Get options for each question
            $optionsSql = "SELECT id, label, content FROM options WHERE question_id = :question_id ORDER BY label";
            $optionsStmt = $this->db->prepare($optionsSql);
            
            foreach ($questions as &$question) {
                $optionsStmt->execute([':question_id' => $question['id']]);
                $question['options'] = $optionsStmt->fetchAll(PDO::FETCH_ASSOC);
                // DEBUG: Log each question's part value
                error_log("Question {$question['id']}: part = {$question['part']}, question_number = {$question['question_number']}");
            }

            error_log("getQuestions returning " . count($questions) . " questions for test_id $testId");

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
     * Get single question by ID
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

            // Get options
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
     * Get all active tests (for dropdown selection)
     * 
     * @return array
     */
    public function getTests() {
        try {
            $sql = "SELECT id, title, description, is_active FROM tests WHERE is_active = 1 ORDER BY created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $tests = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
     * Get single test by ID
     * 
     * @param int $testId
     * @return array
     */
    public function getTest($testId) {
        try {
            // Get test details
            $sql = "SELECT * FROM tests WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $testId]);
            $test = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$test) {
                throw new Exception("Đề thi không tồn tại");
            }

            // Get all questions for this test
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
     * Create a new passage
     * 
     * Expected POST data:
     * - test_id (required)
     * - content (optional)
     * - audio_file (optional, in $_FILES)
     * - image_file (optional, in $_FILES)
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

            // Handle file uploads or keep existing URLs
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
                // Keep existing image URL if provided
                $imageUrl = $this->getPostValue('image_url');
            }

            // Validate part-specific media requirements
            if (!empty($part)) {
                $this->validatePassageMediaRequirements($part, $audioUrl, $imageUrl);
            }

            // Prepare passage data
            $passageData = [
                'test_id' => $testId,
                'content' => !empty($content) ? trim($content) : null,
                'audio_url' => $audioUrl,
                'image_url' => $imageUrl
            ];

            // Create passage
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
     * Get passages for a test
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
     * Delete a question
     * 
     * @param int $questionId
     * @return array
     */
    public function deleteQuestion($questionId) {
        try {
            // Check if question exists
            $sql = "SELECT id, audio_url, image_url FROM questions WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $questionId]);
            $question = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$question) {
                throw new Exception("Câu hỏi không tồn tại");
            }

            // Delete files
            if ($question['audio_url']) {
                FileHandler::deleteFile($question['audio_url']);
            }
            if ($question['image_url']) {
                FileHandler::deleteFile($question['image_url']);
            }

            // Delete question
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
     * Delete a passage
     * 
     * @param int $passageId
     * @return array
     */
    public function deletePassage($passageId) {
        try {
            // Get passage to retrieve file paths
            $sql = "SELECT audio_url, image_url FROM passages WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $passageId]);
            $passage = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$passage) {
                throw new Exception("Đoạn văn không tồn tại");
            }

            // Delete files
            if ($passage['audio_url']) {
                FileHandler::deleteFile($passage['audio_url']);
            }
            if ($passage['image_url']) {
                FileHandler::deleteFile($passage['image_url']);
            }

            // Get all questions linked to this passage before deleting them
            $questionsSql = "SELECT id, audio_url, image_url FROM questions WHERE passage_id = :passage_id";
            $questionsStmt = $this->db->prepare($questionsSql);
            $questionsStmt->execute([':passage_id' => $passageId]);
            $questions = $questionsStmt->fetchAll(PDO::FETCH_ASSOC);

            // Delete media files and options for each question
            foreach ($questions as $question) {
                // Delete question media files
                if ($question['audio_url']) {
                    FileHandler::deleteFile($question['audio_url']);
                }
                if ($question['image_url']) {
                    FileHandler::deleteFile($question['image_url']);
                }
                
                // Delete question options
                $deleteOptionsSql = "DELETE FROM options WHERE question_id = :question_id";
                $deleteOptionsStmt = $this->db->prepare($deleteOptionsSql);
                $deleteOptionsStmt->execute([':question_id' => $question['id']]);
            }

            // Delete all questions linked to this passage
            $deleteQuestionsSql = "DELETE FROM questions WHERE passage_id = :passage_id";
            $deleteQuestionsStmt = $this->db->prepare($deleteQuestionsSql);
            $deleteQuestionsStmt->execute([':passage_id' => $passageId]);

            // Delete passage
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
