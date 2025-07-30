<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../helpers/security.php'; // Güvenlik fonksiyonlarını dahil ediyoruz.

// --- GÜVENLİK GÜNCELLEMESİ ---
// Gelen isteğin geçerli bir token içerip içermediğini kontrol et.
if (!validate_csrf_token($_GET['token'] ?? '')) {
    csrf_fail(); // Token geçersiz veya yoksa işlemi durdur.
}

// Token doğrulandıktan sonra, eski token'ı geçersiz kılmak için session'dan siliyoruz.
// Bu, aynı token'ın tekrar kullanılmasını engeller (replay attack).
unset($_SESSION['csrf_token']);
// --- GÜVENLİK GÜNCELLEMESİ SONU ---

$page_id = $_GET['id'] ?? null;

if ($page_id) {
    try {
        // İlgili sayfayı 'pages' tablosundan sil.
        // Veritabanı (CASCADE ON DELETE sayesinde) 'page_translations' tablosundaki ilgili kayıtları da otomatik olarak silecektir.
        $stmt = $pdo->prepare("DELETE FROM pages WHERE id = ?");
        $stmt->execute([$page_id]);
    } catch (Exception $e) {
        // Hata olursa, bir session mesajı ayarlayıp yönlendirebiliriz.
        // Şimdilik sadece yönlendirme yapalım.
    }
}

// Silme işleminden sonra liste sayfasına geri dön.
header('Location: page-management.php');
exit;
?>