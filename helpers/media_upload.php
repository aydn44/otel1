<?php
// public_html/helpers/media_upload.php
/**
 * Yüklenen bir resmi belirtilen dizine kaydeder.
 * @param array $file $_FILES süper globalinden dosya bilgisi.
 * @param string $upload_dir Yüklenecek dizinin mutlak yolu.
 * @return string|false Yüklenen dosyanın adı veya hata durumunda false.
 */
function upload_image(array $file, string $upload_dir) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        error_log("Resim yükleme hatası: " . $file['error']);
        return false;
    }

    // Dosya türü kontrolü
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed_types)) {
        error_log("Geçersiz resim türü: " . $file['type']);
        return false;
    }

    // Rastgele benzersiz bir dosya adı oluştur
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_file_name = uniqid() . '_' . time() . '.' . $extension;
    $destination = $upload_dir . $new_file_name;

    // Klasör yoksa oluştur
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Dosyayı taşı
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return $new_file_name;
    } else {
        error_log("Resim taşıma hatası: " . $file['tmp_name'] . " -> " . $destination);
        return false;
    }
}