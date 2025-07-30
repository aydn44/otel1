<?php
// Hataları göster
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Veritabanı bağlantısı için config dosyasını çağır
require_once __DIR__ . '/config.php';

echo "<h1 style='font-family: sans-serif; border-bottom: 2px solid #ccc; padding-bottom: 10px;'>Harita Test Sayfası</h1>";

try {
    $pdo = $GLOBALS['pdo'];
    echo "<p style='font-family: sans-serif; color:green;'>✅ Veritabanı bağlantısı başarılı.</p>";

    // Doğrudan veritabanından harita kodunu çekiyoruz
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'contact_map_iframe'");
    $stmt->execute();
    $map_iframe_code = $stmt->fetchColumn();

    if ($map_iframe_code) {
        echo "<p style='font-family: sans-serif; color:green;'>✅ Veritabanından harita kodu başarıyla çekildi.</p>";
        echo "<hr>";

        echo "<h3 style='font-family: sans-serif;'>Kodun Metin Hali (Görünmesi gereken):</h3>";
        echo "<textarea rows='8' style='width: 100%; font-family: monospace; border: 1px solid #ccc; padding: 5px;'>" . htmlspecialchars($map_iframe_code) . "</textarea>";

        echo "<hr>";

        echo "<h3 style='font-family: sans-serif;'>Haritanın Görünmesi Gereken Hali:</h3>";
        // Kodu doğrudan ekrana basıyoruz
        echo $map_iframe_code; 

    } else {
        echo "<p style='font-family: sans-serif; color:red;'>❌ Veritabanında 'contact_map_iframe' anahtarı için bir değer bulunamadı. Lütfen Site Ayarları'ndan kaydedip tekrar deneyin.</p>";
    }

} catch (Exception $e) {
    echo "<p style='font-family: sans-serif; color:red;'>❌ Bir hata oluştu: " . $e->getMessage() . "</p>";
}
?>