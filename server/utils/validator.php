<?php

class Validator {

    /**
     * Kiểm tra nội dung câu hỏi
     * - Yêu cầu: Không được rỗng, độ dài hợp lý (vd: từ 5 đến 5000 ký tự)
     * - Part 1 cho phép null content
     */
    public static function validateQuestionContent($content, $part = null) {
        // Part 1 cho phép content null
        if ($part == 1) {
            if (empty($content)) {
                return true;
            }
        } else {
            // Part 2-7 yêu cầu content không được rỗng
            if (!isset($content) || empty(trim($content))) {
                throw new InvalidArgumentException("Nội dung câu hỏi không được để trống.");
            }
        }

        // Nếu có content, kiểm tra độ dài
        if (!empty($content)) {
            $length = mb_strlen(trim($content), 'UTF-8');
            
            if ($length < 5) {
                throw new InvalidArgumentException("Nội dung câu hỏi quá ngắn (tối thiểu 5 ký tự).");
            }
            
            if ($length > 5000) {
                throw new InvalidArgumentException("Nội dung câu hỏi quá dài (tối đa 5000 ký tự).");
            }
        }

        return true;
    }

    /**
     * Kiểm tra mảng đáp án
     * - Yêu cầu: Phải là mảng có đúng 4 phần tử, nội dung mỗi đáp án không được rỗng
     */
    public static function validateOptions($options) {
        // Phải là array và có chính xác 4 phần tử
        if (!is_array($options) || count($options) !== 4) {
            throw new InvalidArgumentException("Câu hỏi phải có chính xác 4 đáp án.");
        }

        $labels = ['A', 'B', 'C', 'D'];
        $index = 0;

        foreach ($options as $option) {
            // Hỗ trợ cả trường hợp option là string (từ form) hoặc array (như format của Model)
            $content = is_array($option) ? ($option['content'] ?? '') : $option;

            if (empty(trim($content))) {
                $label = $labels[$index] ?? '?';
                throw new InvalidArgumentException("Nội dung của đáp án {$label} không được để trống.");
            }
            $index++;
        }

        return true;
    }

    /**
     * Kiểm tra đáp án đúng
     * - Yêu cầu: Phải là một trong các ký tự A, B, C, D
     */
    public static function validateCorrectAnswer($answer) {
        if (empty($answer)) {
            throw new InvalidArgumentException("Chưa chọn đáp án đúng cho câu hỏi.");
        }

        // In hoa và xóa khoảng trắng thừa để chuẩn hóa (vd ' a ' -> 'A')
        $normalizedAnswer = strtoupper(trim($answer));
        
        // Theo schema bảng `questions`, cột `correct_answer` là CHAR(1)
        $validOptions = ['A', 'B', 'C', 'D'];
        
        if (!in_array($normalizedAnswer, $validOptions, true)) {
            throw new InvalidArgumentException("Đáp án đúng không hợp lệ (chỉ chấp nhận A, B, C, hoặc D).");
        }

        return true;
    }

    /**
     * Kiểm tra Test (Đề thi) có tồn tại không
     * - Yêu cầu: test_id phải là số hợp lệ và phải tồn tại trong bảng `tests`
     */
    public static function validateTestExists(PDO $db, $testId) {
        // Kiểm tra đầu vào cơ bản (phải là số nguyên dương)
        if (!is_numeric($testId) || $testId <= 0) {
            throw new InvalidArgumentException("ID của bài test không hợp lệ.");
        }

        // Truy vấn kiểm tra ID trong bảng tests
        $sql = "SELECT id FROM tests WHERE id = :id AND is_active = 1 LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $testId]);

        $exists = $stmt->fetchColumn();

        if (!$exists) {
            throw new Exception("Bài test không tồn tại hoặc đã bị vô hiệu hóa.");
        }

        return true;
    }

    /**
     * Kiểm tra Part (Phần thi TOEIC)
     * - Yêu cầu: phải là số từ 1 đến 7 (TOEIC có 7 phần)
     */
    public static function validateToeicPart($part) {
        if (!is_numeric($part) || $part < 1 || $part > 7) {
            throw new InvalidArgumentException("Toeic Part phải là số từ 1 đến 7.");
        }
        return true;
    }

    /**
     * Kiểm tra Question Number (Số thứ tự câu hỏi)
     * - Yêu cầu: phải là số nguyên dương
     */
    public static function validateQuestionNumber($questionNumber, $part = null) {
        if (!is_numeric($questionNumber) || $questionNumber <= 0) {
            throw new InvalidArgumentException("Số thứ tự câu hỏi phải là số dương.");
        }
        
        // Kiểm tra range cơ bản (1-200)
        if ($questionNumber > 200) {
            throw new InvalidArgumentException("Số thứ tự câu hỏi không được vượt quá 200.");
        }
        
        return true;
    }

    /**
     * Kiểm tra Passage ID (nếu có)
     * - Yêu cầu: nếu được pass, passage_id phải tồn tại và thuộc test đó
     */
    public static function validatePassageExists(PDO $db, $passageId, $testId) {
        if (empty($passageId)) {
            return true; // passage_id là optional
        }

        if (!is_numeric($passageId) || $passageId <= 0) {
            throw new InvalidArgumentException("ID của passage không hợp lệ.");
        }

        $sql = "SELECT id FROM passages WHERE id = :id AND test_id = :test_id LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $passageId, ':test_id' => $testId]);

        if (!$stmt->fetchColumn()) {
            throw new Exception("Passage không tồn tại hoặc không thuộc bài test này.");
        }

        return true;
    }

    /**
     * Kiểm tra URL format (cho audio_url, image_url)
     * - Yêu cầu: nếu được pass, phải là URL hợp lệ
     */
    public static function validateUrl($url) {
        if (empty($url)) {
            return true; // URL là optional
        }

        // Kiểm tra format URL cơ bản
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException("Định dạng URL không hợp lệ: {$url}");
        }

        return true;
    }

    /**
     * Kiểm tra Audio URL
     * - Yêu cầu: nếu được pass, phải là URL/path hợp lệ, có extension audio
     */
    public static function validateAudioUrl($url) {
        if (empty($url)) {
            return true; // URL là optional
        }

        // Kiểm tra format URL hoặc relative path
        if (!filter_var($url, FILTER_VALIDATE_URL) && !preg_match('#^/uploads/audio/.*\.(mp3|wav|ogg|m4a)$#i', $url)) {
            throw new InvalidArgumentException("Định dạng URL audio không hợp lệ: {$url}");
        }

        // Kiểm tra extension
        $validExtensions = ['mp3', 'wav', 'ogg', 'm4a'];
        $pathInfo = pathinfo($url);
        $extension = strtolower($pathInfo['extension'] ?? '');
        
        if (!in_array($extension, $validExtensions)) {
            throw new InvalidArgumentException("Định dạng audio không hỗ trợ (chấp nhận: " . implode(', ', $validExtensions) . ")");
        }

        return true;
    }

    /**
     * Kiểm tra Image URL
     * - Yêu cầu: nếu được pass, phải là URL/path hợp lệ, có extension image
     */
    public static function validateImageUrl($url) {
        if (empty($url)) {
            return true; // URL là optional
        }

        // Kiểm tra format URL hoặc relative path
        if (!filter_var($url, FILTER_VALIDATE_URL) && !preg_match('#^/uploads/image/.*\.(jpg|jpeg|png|gif)$#i', $url)) {
            throw new InvalidArgumentException("Định dạng URL hình ảnh không hợp lệ: {$url}");
        }

        // Kiểm tra extension
        $validExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $pathInfo = pathinfo($url);
        $extension = strtolower($pathInfo['extension'] ?? '');
        
        if (!in_array($extension, $validExtensions)) {
            throw new InvalidArgumentException("Định dạng hình ảnh không hỗ trợ (chấp nhận: " . implode(', ', $validExtensions) . ")");
        }

        return true;
    }

    /**
     * Kiểm tra Explanation (giải thích)
     * - Yêu cầu: nếu được pass, không quá dài (tối đa 5000 ký tự)
     */
    public static function validateExplanation($explanation) {
        if (empty($explanation)) {
            return true; // explanation là optional
        }

        $length = mb_strlen(trim($explanation), 'UTF-8');
        if ($length > 5000) {
            throw new InvalidArgumentException("Giải thích quá dài (tối đa 5000 ký tự).");
        }

        return true;
    }

    /**
     * Validate Part-specific requirements
     * - Part 1: image_url bắt buộc, content NULL OK
     * - Part 2-4: audio_url bắt buộc, content bắt buộc
     * - Part 5-6: content bắt buộc, không cần media
     * - Part 7: passage_id bắt buộc, content bắt buộc
     */
    public static function validatePartSpecificRequirements($part, $data) {
        $part = (int)$part;

        switch ($part) {
            case 1:
                // Part 1: image_url bắt buộc, content có thể null
                if (empty($data['image_url'])) {
                    throw new InvalidArgumentException("Part 1 yêu cầu phải có hình ảnh (image_url).");
                }
                break;

            case 2:
            case 3:
            case 4:
                // Part 2-4: audio_url bắt buộc, content bắt buộc
                if (empty($data['audio_url'])) {
                    throw new InvalidArgumentException("Part {$part} yêu cầu phải có âm thanh (audio_url).");
                }
                if (empty($data['content'])) {
                    throw new InvalidArgumentException("Part {$part} yêu cầu phải có nội dung câu hỏi.");
                }
                break;

            case 5:
            case 6:
                // Part 5-6: content bắt buộc, không cần media
                if (empty($data['content'])) {
                    throw new InvalidArgumentException("Part {$part} yêu cầu phải có nội dung câu hỏi.");
                }
                break;

            case 7:
                // Part 7: passage_id bắt buộc, content bắt buộc
                if (empty($data['passage_id'])) {
                    throw new InvalidArgumentException("Part 7 yêu cầu phải chọn passage (đoạn văn).");
                }
                if (empty($data['content'])) {
                    throw new InvalidArgumentException("Part 7 yêu cầu phải có nội dung câu hỏi.");
                }
                break;

            default:
                throw new InvalidArgumentException("Part không hợp lệ: {$part}");
        }

        return true;
    }
}