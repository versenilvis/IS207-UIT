<?php

// Các biến cấu hình (Global hoặc dùng hằng số)
define('FH_UPLOAD_BASE_DIR', dirname(__DIR__) . '/uploads');
define('FH_MAX_AUDIO_SIZE', 50 * 1024 * 1024);
define('FH_MAX_IMAGE_SIZE', 5 * 1024 * 1024);
define('FH_MAX_JSON_SIZE', 10 * 1024 * 1024);
define('FH_MAX_EXCEL_SIZE', 10 * 1024 * 1024);

/**
 * Khởi tạo thư mục upload
 */
function fh_init_upload_dir() {
    if (!is_dir(FH_UPLOAD_BASE_DIR)) {
        mkdir(FH_UPLOAD_BASE_DIR, 0777, true);
    }
}

/**
 * Xử lý upload file chung
 */
function fh_upload_file($file, $type = 'audio') {
    try {
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            throw new Exception("Không nhận được file từ client.");
        }

        switch (strtolower($type)) {
            case 'audio':
                fh_validate_audio($file);
                $dir = 'audio';
                $extension = fh_get_extension($file['name'], ['mp3', 'wav', 'ogg', 'm4a'], 'mp3');
                break;
            case 'image':
                fh_validate_image($file);
                $dir = 'image';
                $extension = fh_get_extension($file['name'], ['jpg', 'jpeg', 'png', 'gif'], 'jpg');
                break;
            case 'json':
                fh_validate_json($file);
                $dir = 'json';
                $extension = 'json';
                break;
            case 'excel':
                fh_validate_excel($file);
                $dir = 'excel';
                $extension = 'xlsx';
                break;
            default:
                throw new Exception("Loại file không được hỗ trợ: {$type}");
        }

        return fh_save_uploaded_file($file, $dir, $extension);
    } catch (Exception $e) {
        throw new Exception($e->getMessage());
    }
}

/**
 * Xác thực âm thanh
 */
function fh_validate_audio($file) {
    if ($file['size'] > FH_MAX_AUDIO_SIZE) throw new Exception("File âm thanh quá lớn (Tối đa 50MB).");
    
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowed = ['audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/mp3'];
    
    if (!in_array($mime, $allowed)) throw new Exception("MIME âm thanh không hợp lệ: {$mime}");
}

/**
 * Xác thực hình ảnh
 */
function fh_validate_image($file) {
    if ($file['size'] > FH_MAX_IMAGE_SIZE) throw new Exception("File hình ảnh quá lớn (Tối đa 5MB).");
    
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!str_starts_with($mime, 'image/')) throw new Exception("MIME hình ảnh không hợp lệ.");
    
    if (@getimagesize($file['tmp_name']) === false) throw new Exception("File không phải hình ảnh hợp lệ.");
}

/**
 * Xác thực JSON
 */
function fh_validate_json($file) {
    if ($file['size'] > FH_MAX_JSON_SIZE) throw new Exception("File JSON quá lớn.");
    if (strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) !== 'json') throw new Exception("Yêu cầu file .json");
}

/**
 * Xác thực Excel
 */
function fh_validate_excel($file) {
    if ($file['size'] > FH_MAX_EXCEL_SIZE) throw new Exception("File Excel quá lớn.");
}

/**
 * Lưu file vật lý
 */
function fh_save_uploaded_file($file, $directory, $extension) {
    fh_init_upload_dir();
    $fullDir = FH_UPLOAD_BASE_DIR . '/' . $directory;

    if (!is_dir($fullDir)) {
        if (!mkdir($fullDir, 0777, true)) throw new Exception("Không thể tạo thư mục: {$directory}");
    }

    $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
    
    $filename = "{$uuid}.{$extension}";
    $fullPath = $fullDir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $fullPath)) throw new Exception("Lỗi di chuyển file.");

    return "/server/uploads/{$directory}/{$filename}";
}

/**
 * Xóa file
 */
function fh_delete_file($relativePath) {
    if (empty($relativePath)) return true;
    
    // Convert /server/uploads/... to absolute path
    $path = str_replace('/server/uploads', FH_UPLOAD_BASE_DIR, $relativePath);
    
    if (file_exists($path) && is_file($path)) {
        return unlink($path);
    }
    return true;
}

/**
 * Lấy phần mở rộng
 */
function fh_get_extension($filename, $allowed, $default) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($ext, $allowed) ? $ext : $default;
}
