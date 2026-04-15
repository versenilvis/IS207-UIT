<?php

class FileHandler {
    
    // Thư mục upload - sử dụng realpath để chuẩn hóa
    private static $uploadDir = null;
    private static $audioDir = 'audio';
    private static $imageDir = 'image';
    private static $jsonDir = 'json';
    private static $excelDir = 'excel';
    
    /**
     * Khởi tạo thư mục upload khi sử dụng lần đầu
     */
    private static function initUploadDir() {
        if (self::$uploadDir === null) {
            self::$uploadDir = realpath(__DIR__ . '/../uploads');
            if (!self::$uploadDir) {
                self::$uploadDir = __DIR__ . '/../uploads';
            }
        }
    }

    // Giới hạn kích thước file
    private static $audioMaxSize = 50 * 1024 * 1024;      // 50MB
    private static $imageMaxSize = 5 * 1024 * 1024;       // 5MB
    private static $jsonMaxSize = 10 * 1024 * 1024;       // 10MB
    private static $excelMaxSize = 10 * 1024 * 1024;      // 10MB

    // Các loại MIME được phép
    private static $audioMimes = ['audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/mp3'];
    private static $imageMimes = ['image/jpeg', 'image/png', 'image/gif'];
    private static $jsonMimes = ['application/json'];
    private static $excelMimes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];

    /**
     * Xử lý upload file chung
     * 
     * @param array $file - phần tử $_FILES
     * @param string $type - 'audio', 'image', 'json', 'excel'
     * @return string - đường dẫn tương đối (vd: /uploads/audio/uuid-123.mp3)
     * @throws Exception
     */
    public static function uploadFile($file, $type = 'audio') {
        try {
            // Xác thực file tồn tại và hợp lệ
            if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
                throw new Exception("Không nhận được file từ client.");
            }

            // Xác thực theo loại file
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

            // Lưu file và lấy đường dẫn tương đối
            $relativePath = self::saveUploadedFile($file, $dir, $extension);
            return $relativePath;

        } catch (Exception $e) {
            throw new Exception("Lỗi upload file: " . $e->getMessage());
        }
    }

    /**
     * Xác thực file âm thanh
     * 
     * @param array $file - phần tử $_FILES
     * @throws Exception
     */
    public static function validateAudio($file) {
        // Kiểm tra kích thước file
        if ($file['size'] > self::$audioMaxSize) {
            throw new Exception("File âm thanh quá lớn. Tối đa 50MB, được cấp {$file['size']} bytes");
        }

        // Kiểm tra loại MIME
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, self::$audioMimes)) {
            throw new Exception("Định dạng âm thanh không hợp lệ. Chỉ hỗ trợ: MP3, WAV, OGG. MIME nhận được: {$mimeType}");
        }

        // Xác thực phần mở rộng file
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['mp3', 'wav', 'ogg', 'm4a'])) {
            throw new Exception("Phần mở rộng file không hợp lệ: {$ext}");
        }
    }

    /**
     * Xác thực file hình ảnh
     * 
     * @param array $file - phần tử $_FILES
     * @throws Exception
     */
    public static function validateImage($file) {
        // Kiểm tra kích thước file
        if ($file['size'] > self::$imageMaxSize) {
            throw new Exception("File hình ảnh quá lớn. Tối đa 5MB, được cấp {$file['size']} bytes");
        }

        // Kiểm tra loại MIME
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, self::$imageMimes)) {
            throw new Exception("Định dạng hình ảnh không hợp lệ. Chỉ hỗ trợ: JPG, PNG, GIF. MIME nhận được: {$mimeType}");
        }

        // Xác thực phần mở rộng file
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
            throw new Exception("Phần mở rộng file không hợp lệ: {$ext}");
        }

        // Xác thực là hình ảnh hợp lệ
        if (@getimagesize($file['tmp_name']) === false) {
            throw new Exception("File không phải là hình ảnh hợp lệ.");
        }
    }

    /**
     * Xác thực file JSON
     * 
     * @param array $file - phần tử $_FILES
     * @throws Exception
     */
    public static function validateJsonFile($file) {
        // Kiểm tra kích thước file
        if ($file['size'] > self::$jsonMaxSize) {
            throw new Exception("File JSON quá lớn. Tối đa 10MB, được cấp {$file['size']} bytes");
        }

        // Kiểm tra phần mở rộng file
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'json') {
            throw new Exception("File phải có phần mở rộng .json");
        }

        // Xác thực định dạng JSON
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
     * Xác thực file Excel
     * 
     * @param array $file - phần tử $_FILES
     * @throws Exception
     */
    public static function validateExcelFile($file) {
        // Kiểm tra kích thước file
        if ($file['size'] > self::$excelMaxSize) {
            throw new Exception("File Excel quá lớn. Tối đa 10MB, được cấp {$file['size']} bytes");
        }

        // Kiểm tra phần mở rộng file
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'xlsx') {
            throw new Exception("File phải có phần mở rộng .xlsx (Excel 2007+)");
        }

        // Kiểm tra loại MIME
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, self::$excelMimes)) {
            throw new Exception("Định dạng Excel không hợp lệ. MIME nhận được: {$mimeType}");
        }
    }

    /**
     * Lưu file đã tải lên với tên file UUID
     * 
     * @param array $file - phần tử $_FILES
     * @param string $directory - thư mục đích (vd: /uploads/audio)
     * @param string $extension - phần mở rộng file
     * @return string - đường dẫn tương đối
     * @throws Exception
     */
    public static function saveUploadedFile($file, $directory, $extension) {
        // Khởi tạo thư mục upload
        self::initUploadDir();
        
        // Tạo đường dẫn thư mục đầy đủ
        $fullDir = self::$uploadDir . '/' . $directory;

        // Tạo thư mục nếu không tồn tại
        if (!is_dir($fullDir)) {
            if (!mkdir($fullDir, 0755, true)) {
                throw new Exception("Không thể tạo thư mục upload: {$fullDir}");
            }
        }

        // Tạo tên file UUID
        $uuid = self::generateUUID();
        $filename = "{$uuid}.{$extension}";
        $fullPath = $fullDir . '/' . $filename;

        // Di chuyển file đã tải lên
        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            throw new Exception("Không thể di chuyển file tới: {$fullPath}");
        }

        // Trả về đường dẫn tương đối để lưu trong database (truy cập qua server/uploads/)
        return "/IS207-UIT/server/uploads/{$directory}/{$filename}";
    }

    /**
     * Tạo UUID v4
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
     * Lấy phần mở rộng file âm thanh
     * 
     * @param string $filename
     * @return string
     */
    private static function getAudioExtension($filename) {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($ext, ['mp3', 'wav', 'ogg', 'm4a']) ? $ext : 'mp3';
    }

    /**
     * Lấy phần mở rộng file hình ảnh
     * 
     * @param string $filename
     * @return string
     */
    private static function getImageExtension($filename) {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($ext, ['jpg', 'jpeg', 'png', 'gif']) ? $ext : 'jpg';
    }

    /**
     * Xóa file khỏi lưu trữ
     * 
     * @param string $relativePath - đường dẫn tương đối (vd: /uploads/audio/uuid-123.mp3)
     * @return bool
     */
    public static function deleteFile($relativePath) {
        try {
            $fullPath = self::$uploadDir . $relativePath;
            
            if (file_exists($fullPath)) {
                return unlink($fullPath);
            }
            
            return true; // File đã được xóa
        } catch (Exception $e) {
            // Ghi lỗi nhưng không ném ngoại lệ
            error_log("Error deleting file: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Kiểm tra file có tồn tại không
     * 
     * @param string $relativePath - đường dẫn tương đối
     * @return bool
     */
    public static function fileExists($relativePath) {
        $fullPath = self::$uploadDir . $relativePath;
        return file_exists($fullPath) && is_file($fullPath);
    }

    /**
     * Lấy kích thước file
     * 
     * @param string $relativePath - đường dẫn tương đối
     * @return int - kích thước tính bằng byte, 0 nếu không tồn tại
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
