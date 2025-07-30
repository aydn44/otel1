<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../helpers/security.php';

// Formdan gelen token'ı ve dosya adını al
$token = $_POST['token'] ?? '';
$filename = $_POST['filename'] ?? '';

// CSRF token kontrolü ile isteğin güvenli olduğundan emin ol
if (!validate_csrf_token($token)) {
    csrf_fail();
}
unset($_SESSION['csrf_token']);

if (!empty($filename)) {
    // Güvenlik: Kullanıcının ../ gibi ifadelerle üst dizinlere çıkmasını engelle
    if (strpos($filename, '..') !== false || strpos($filename, '/') !== false || strpos($filename, '\\') !== false) {
        die('Geçersiz dosya adı.');
    }

    $filepath = ROOT_PATH . '/uploads/' . $filename;

    // Dosya var mı ve silinebilir mi kontrol et
    if (file_exists($filepath) && is_writable($filepath)) {
        unlink($filepath); // Dosyayı sil
    }
}

// İşlem bittikten sonra medya kütüphanesine geri dön
header('Location: medya-yoneticisi.php');
exit;