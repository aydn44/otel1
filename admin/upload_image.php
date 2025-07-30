<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth_check.php';

// PHP ayarlarını kod içinde ayarla
ini_set('upload_max_filesize', '10M');
ini_set('post_max_size', '10M');
ini_set('max_execution_time', 300);
ini_set('memory_limit', '256M');

header('Content-Type: application/json');

// Sadece POST isteklerine izin ver
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => ['message' => 'Method not allowed']]);
    exit;
}

// Dosya gönderildi mi kontrol et
if (!isset($_FILES['upload']) || $_FILES['upload']['error'] !== UPLOAD_ERR_OK) {
    $error_message = 'Dosya yükleme hatası';
    if (isset($_FILES['upload']['error'])) {
        switch ($_FILES['upload']['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $error_message = 'Dosya boyutu çok büyük';
                break;
            case UPLOAD_ERR_PARTIAL:
                $error_message = 'Dosya kısmen yüklendi';
                break;
            case UPLOAD_ERR_NO_FILE:
                $error_message = 'Dosya seçilmedi';
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $error_message = 'Geçici klasör bulunamadı';
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $error_message = 'Dosya yazılamadı';
                break;
        }
    }
    echo json_encode(['error' => ['message' => $error_message]]);
    exit;
}

try {
    // uploads klasörünün yolunu belirle
    $upload_dir = ROOT_PATH . '/uploads/';
    
    // uploads klasörü yoksa oluştur
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            echo json_encode(['error' => ['message' => 'Upload klasörü oluşturulamadı']]);
            exit;
        }
    }
    
    $file = $_FILES['upload'];
    
    // Dosya türü kontrolü
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $file_info = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($file_info, $file['tmp_name']);
    finfo_close($file_info);
    
    if (!in_array($mime_type, $allowed_types)) {
        echo json_encode(['error' => ['message' => 'Geçersiz dosya türü. Sadece resim dosyaları (JPG, PNG, GIF, WebP) yükleyebilirsiniz.']]);
        exit;
    }
    
    // Dosya boyutu kontrolü (5MB)
    $max_size = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $max_size) {
        echo json_encode(['error' => ['message' => 'Dosya boyutu çok büyük. Maksimum 5MB olmalıdır.']]);
        exit;
    }
    
    // Güvenli dosya adı oluştur
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '_' . time() . '.' . $file_extension;
    $upload_path = $upload_dir . $new_filename;
    
    // Dosyayı yükle
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        $file_url = BASE_URL . '/uploads/' . $new_filename;
        
        // CKEditor için gerekli format
        echo json_encode([
            'url' => $file_url,
            'uploaded' => 1,
            'fileName' => $new_filename
        ]);
    } else {
        echo json_encode(['error' => ['message' => 'Dosya sunucuya yüklenirken bir hata oluştu.']]);
    }
    
} catch (Exception $e) {
    echo json_encode(['error' => ['message' => 'Sunucu hatası: ' . $e->getMessage()]]);
}
?>