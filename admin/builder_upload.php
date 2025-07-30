<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth_check.php';

header('Content-Type: application/json');

if (isset($_FILES['image'])) {
    $file = $_FILES['image'];

    // Hata kontrolü
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Yükleme sırasında bir hata oluştu. Hata kodu: ' . $file['error']]);
        exit;
    }

    // Dosya tipi kontrolü
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed_types)) {
        echo json_encode(['success' => false, 'message' => 'Geçersiz dosya tipi. Sadece JPG, PNG, GIF, WEBP dosyaları yüklenebilir.']);
        exit;
    }

    // Dosya boyutu kontrolü (örn: 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'Dosya boyutu çok büyük. Maksimum 5MB.']);
        exit;
    }

    // Güvenli ve benzersiz bir dosya adı oluştur
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = uniqid('img_', true) . '.' . $extension;

    // Yüklenecek dizin (ana dizindeki uploads klasörü)
    $upload_dir = ROOT_PATH . '/uploads/';

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $destination = $upload_dir . $new_filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        // Başarılı yükleme durumunda tam URL'yi geri döndür
        $file_url = BASE_URL . '/uploads/' . $new_filename;
        echo json_encode(['success' => true, 'url' => $file_url]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Dosya sunucuya taşınamadı.']);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Yüklenecek dosya bulunamadı.']);
}
exit;