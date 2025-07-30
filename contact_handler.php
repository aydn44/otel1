<?php
// Bu dosya, AJAX isteklerini işler ve JSON formatında yanıt döner.
header('Content-Type: application/json');

// Gerekli dosyaları ve sınıfları yüklüyoruz.
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/Mailer.php';

// Güvenlik: Sadece POST metodu ile gelen istekleri kabul et.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek metodu.']);
    exit;
}

// Formdan gelen verileri al ve temizle.
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

// Sunucu tarafı doğrulama (validation).
if (empty($name) || empty($email) || empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Lütfen tüm alanları doldurun.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Lütfen geçerli bir e-posta adresi girin.']);
    exit;
}

try {
    // Admin'e gönderilecek e-postanın hedefini veritabanından al.
    $admin_email_to = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'mail_notification_address'")->fetchColumn();

    if (!$admin_email_to) {
        throw new \Exception('Bildirim e-posta adresi ayarlanmamış.');
    }
    
    // E-posta konusunu ve içeriğini oluştur.
    $subject = "Yeni İletişim Formu Mesajı - " . htmlspecialchars($name);
    $body = "
        <h2>Yeni Bir İletişim Mesajı Aldınız</h2>
        <p><strong>Gönderen Adı:</strong> " . htmlspecialchars($name) . "</p>
        <p><strong>Gönderen E-posta:</strong> " . htmlspecialchars($email) . "</p>
        <hr>
        <h3>Mesaj:</h3>
        <p>" . nl2br(htmlspecialchars($message)) . "</p>
    ";

    // Mailer sınıfını kullanarak e-postayı gönder.
    $mailer = new \App\Lib\Mailer($pdo);
    $mailer->send($admin_email_to, $subject, $body);

    // Başarılı olursa, başarı mesajı döndür.
    echo json_encode(['success' => true, 'message' => 'Mesajınız başarıyla gönderildi. En kısa sürede size geri döneceğiz.']);

} catch (\Exception $e) {
    // Hata olursa, hatayı logla ve genel bir hata mesajı döndür.
    error_log('İletişim Formu Hatası: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Mesajınız gönderilirken bir sunucu hatası oluştu. Lütfen daha sonra tekrar deneyin.']);
}