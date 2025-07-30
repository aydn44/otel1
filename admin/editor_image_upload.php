<?php
// CKEditor 5 image upload handler

require_once __DIR__ . '/../config.php';

/*
// +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// GEÇİCİ TEST İÇİN KİMLİK DOĞRULAMA ADIMI DEVRE DIŞI BIRAKILDI
// +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
if (!isset($_SESSION['user_id'])) {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => ['message' => 'Kimlik doğrulama başarısız. Lütfen tekrar giriş yapın.']]);
    exit;
}
*/


if (isset($_FILES['upload']) && $_FILES['upload']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['upload'];

    $upload_dir = __DIR__ . '/../uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $file_info = pathinfo($file['name']);
    $file_name = preg_replace("/[^a-zA-Z0-9_-]/", "", $file_info['filename']);
    $extension = $file_info['extension'];
    $safe_file_name = time() . '_' . $file_name . '.' . $extension;
    $destination = $upload_dir . $safe_file_name;

    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array(mime_content_type($file['tmp_name']), $allowed_types)) {
        echo json_encode(['error' => ['message' => 'Geçersiz dosya türü. Sadece JPG, PNG, GIF, WEBP dosyalarına izin verilir.']]);
        exit;
    }
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        $url = BASE_URL . '/uploads/' . $safe_file_name;
        
        echo json_encode([
            'uploaded' => 1,
            'fileName' => $safe_file_name,
            'url' => $url
        ]);
    } else {
        echo json_encode(['error' => ['message' => 'Sunucu hatası: Dosya hedefe taşınamadı. Klasör izinlerini kontrol edin.']]);
    }
} else {
    $error_code = $_FILES['upload']['error'] ?? UPLOAD_ERR_NO_FILE;
    echo json_encode(['error' => ['message' => 'Yükleme sırasında bir hata oluştu. Hata Kodu: ' . $error_code]]);
}
