<?php

class FileHandler {
    
    // Upload directories - use realpath to normalize
    private static $uploadDir = null;
    private static $audioDir = 'audio';
    private static $imageDir = 'image';
    private static $jsonDir = 'json';
    private static $excelDir = 'excel';
    
    /**
     * Initialize upload directory on first use
     */
    private static function initUploadDir() {
        if (self::$uploadDir === null) {
            self::$uploadDir = realpath(__DIR__ . '/../uploads');
            if (!self::$uploadDir) {
                self::$uploadDir = __DIR__ . '/../uploads';
            }
        }
    }

    // File size limits
    private static $audioMaxSize = 50 * 1024 * 1024;      // 50MB
    private static $imageMaxSize = 5 * 1024 * 1024;       // 5MB
    private static $jsonMaxSize = 10 * 1024 * 1024;       // 10MB
    private static $excelMaxSize = 10 * 1024 * 1024;      // 10MB

    // Allowed MIME types
    private static $audioMimes = ['audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/mp3'];
    private static $imageMimes = ['image/jpeg', 'image/png', 'image/gif'];
    private static $jsonMimes = ['application/json'];
    private static $excelMimes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];

    /**
     * Generic file upload handler
     * 
     * @param array $file - $_FILES element
     * @param string $type - 'audio', 'image', 'json', 'excel'
     * @return string - relative path (e.g., /uploads/audio/uuid-123.mp3)
     * @throws Exception
     */
    public static function uploadFile($file, $type = 'audio') {
        try {
            // Validate file exists and is valid
            if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
                throw new Exception("Không nhận được file từ client.");
            }

            // Validate by type
            switch (strtolower($type)) {
                case 'audio':
                    self::validateAudio($file);
                    $dir = self::$audioDir;
                    $extension = self::getAudioExtension($file['name']);
                    break;
                case 'image':
                    self::validateImage($file);
                    $dir = self::$imageDir;
                    $extension = self::getImageExtension($file['name']);
                    break;
                case 'json':
                    self::validateJsonFile($file);
                    $dir = self::$jsonDir;
                    $extension = 'json';
                    break;
                case 'excel':
                    self::validateExcelFile($file);
                    $dir = self::$excelDir;
                    $extension = 'xlsx';
                    break;
                default:
                    throw new Exception("Loại file không được hỗ trợ: {$type}");
            }

            // Save file and get relative path
            $relativePath = self::saveUploadedFile($file, $dir, $extension);
            return $relativePath;

        } catch (Exception $e) {
            throw new Exception("Lỗi upload file: " . $e->getMessage());
        }
    }

    /**
     * Validate audio file
     * 
     * @param array $file - $_FILES element
     * @throws Exception
     */
    public static function validateAudio($file) {
        // Check file size
        if ($file['size'] > self::$audioMaxSize) {
            throw new Exception("File âm thanh quá lớn. Tối đa 50MB, được cấp {$file['size']} bytes");
        }

        // Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, self::$audioMimes)) {
            throw new Exception("Định dạng âm thanh không hợp lệ. Chỉ hỗ trợ: MP3, WAV, OGG. MIME nhận được: {$mimeType}");
        }

        // Validate file extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['mp3', 'wav', 'ogg', 'm4a'])) {
            throw new Exception("Phần mở rộng file không hợp lệ: {$ext}");
        }
    }

    /**
     * Validate image file
     * 
     * @param array $file - $_FILES element
     * @throws Exception
     */
    public static function validateImage($file) {
        // Check file size
        if ($file['size'] > self::$imageMaxSize) {
            throw new Exception("File hình ảnh quá lớn. Tối đa 5MB, được cấp {$file['size']} bytes");
        }

        // Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, self::$imageMimes)) {
            throw new Exception("Định dạng hình ảnh không hợp lệ. Chỉ hỗ trợ: JPG, PNG, GIF. MIME nhận được: {$mimeType}");
        }

        // Validate file extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
            throw new Exception("Phần mở rộng file không hợp lệ: {$ext}");
        }

        // Validate is valid image
        if (@getimagesize($file['tmp_name']) === false) {
            throw new Exception("File không phải là hình ảnh hợp lệ.");
        }
    }

    /**
     * Validate JSON file
     * 
     * @param array $file - $_FILES element
     * @throws Exception
     */
    public static function validateJsonFile($file) {
        // Check file size
        if ($file['size'] > self::$jsonMaxSize) {
            throw new Exception("File JSON quá lớn. Tối đa 10MB, được cấp {$file['size']} bytes");
        }

        // Check file extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'json') {
            throw new Exception("File phải có phần mở rộng .json");
        }

        // Validate JSON format
        $content = file_get_contents($file['tmp_name']);
        if ($content === false) {
            throw new Exception("Không thể đọc nội dung file JSON.");
        }

        $decoded = json_decode($content, true);
        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("File JSON không hợp lệ: " . json_last_error_msg());
        }
    }

    /**
     * Validate Excel file
     * 
     * @param array $file - $_FILES element
     * @throws Exception
     */
    public static function validateExcelFile($file) {
        // Check file size
        if ($file['size'] > self::$excelMaxSize) {
            throw new Exception("File Excel quá lớn. Tối đa 10MB, được cấp {$file['size']} bytes");
        }

        // Check file extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'xlsx') {
            throw new Exception("File phải có phần mở rộng .xlsx (Excel 2007+)");
        }

        // Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, self::$excelMimes)) {
            throw new Exception("Định dạng Excel không hợp lệ. MIME nhận được: {$mimeType}");
        }
    }

    /**
     * Save uploaded file with UUID filename
     * 
     * @param array $file - $_FILES element
     * @param string $directory - target directory (e.g., /uploads/audio)
     * @param string $extension - file extension
     * @return string - relative path
     * @throws Exception
     */
    public static function saveUploadedFile($file, $directory, $extension) {
        // Initialize upload directory
        self::initUploadDir();
        
        // Create full directory path
        $fullDir = self::$uploadDir . '/' . $directory;

        // Create directory if not exists
        if (!is_dir($fullDir)) {
            if (!mkdir($fullDir, 0755, true)) {
                throw new Exception("Không thể tạo thư mục upload: {$fullDir}");
            }
        }

        // Generate UUID filename
        $uuid = self::generateUUID();
        $filename = "{$uuid}.{$extension}";
        $fullPath = $fullDir . '/' . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            throw new Exception("Không thể di chuyển file tới: {$fullPath}");
        }

        // Return relative path for storing in database (accessible via server/uploads/)
        return "/IS207-UIT/server/uploads/{$directory}/{$filename}";
    }

    /**
     * Generate UUID v4
     * 
     * @return string
     */
    private static function generateUUID() {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * Get audio file extension
     * 
     * @param string $filename
     * @return string
     */
    private static function getAudioExtension($filename) {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($ext, ['mp3', 'wav', 'ogg', 'm4a']) ? $ext : 'mp3';
    }

    /**
     * Get image file extension
     * 
     * @param string $filename
     * @return string
     */
    private static function getImageExtension($filename) {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($ext, ['jpg', 'jpeg', 'png', 'gif']) ? $ext : 'jpg';
    }

    /**
     * Delete file from storage
     * 
     * @param string $relativePath - relative path (e.g., /uploads/audio/uuid-123.mp3)
     * @return bool
     */
    public static function deleteFile($relativePath) {
        try {
            $fullPath = self::$uploadDir . $relativePath;
            
            if (file_exists($fullPath)) {
                return unlink($fullPath);
            }
            
            return true; // File already deleted
        } catch (Exception $e) {
            // Log error but don't throw
            error_log("Error deleting file: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if file exists
     * 
     * @param string $relativePath - relative path
     * @return bool
     */
    public static function fileExists($relativePath) {
        $fullPath = self::$uploadDir . $relativePath;
        return file_exists($fullPath) && is_file($fullPath);
    }

    /**
     * Get file size
     * 
     * @param string $relativePath - relative path
     * @return int - size in bytes, 0 if not exists
     */
    public static function getFileSize($relativePath) {
        $fullPath = self::$uploadDir . $relativePath;
        if (file_exists($fullPath)) {
            return filesize($fullPath);
        }
        return 0;
    }
}

?>
