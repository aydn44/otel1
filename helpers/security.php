<?php
/**
 * CSRF (Cross-Site Request Forgery) saldırılarına karşı koruma sağlayan
 * güvenlik fonksiyonlarını içerir.
 */

// Oturumun başlatıldığından emin oluyoruz, çünkü token'ları session'da saklayacağız.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Benzersiz bir CSRF token'ı oluşturur, session'a kaydeder ve döndürür.
 * Eğer session'da zaten bir token varsa, onu kullanır. Bu, aynı sayfa içinde
 * birden fazla form veya link için aynı token'ın kullanılabilmesini sağlar.
 *
 * @return string CSRF token.
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        // `random_bytes` kriptografik olarak güvenli baytlar üretir.
        // `bin2hex` bu baytları okunabilir bir formata çevirir.
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Gönderilen CSRF token'ını session'daki ile karşılaştırarak doğrular.
 *
 * @param string $token Formdan veya URL'den gelen token.
 * @return bool Token geçerliyse true, değilse false döner.
 */
function validate_csrf_token($token) {
    if (!empty($token) && !empty($_SESSION['csrf_token'])) {
        // `hash_equals` fonksiyonu, stringleri zamanlama saldırılarına karşı
        // güvenli bir şekilde karşılaştırır. Sıradan bir `==` karşılaştırmasından daha güvenlidir.
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    return false;
}

/**
 * CSRF doğrulaması başarısız olursa çalışacak varsayılan fonksiyon.
 * İşlemi durdurur ve kullanıcıya bir hata mesajı basar.
 */
function csrf_fail() {
    http_response_code(403); // HTTP "Forbidden" durum kodunu gönderir.
    die('<h1>Güvenlik Hatası</h1><p>Geçersiz veya süresi dolmuş bir istekte bulundunuz. Lütfen sayfayı yenileyip tekrar deneyin.</p>');
}
?>