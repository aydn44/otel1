<?php
namespace App\Lib;

// PHPMailer sınıflarını çağırıyoruz.
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Bu dosyaların config.php'de tanımladığımız ROOT_PATH üzerinden çağrılması gerekiyor.
require_once ROOT_PATH . '/PHPMailer/src/Exception.php';
require_once ROOT_PATH . '/PHPMailer/src/PHPMailer.php';
require_once ROOT_PATH . '/PHPMailer/src/SMTP.php';

class Mailer
{
    private $pdo;
    private $mail;

    // Sınıf oluşturulurken veritabanı bağlantısını (PDO) alıyoruz.
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->mail = new PHPMailer(true); // true, hatalar için Exception fırlatmasını sağlar.
        $this->configure(); // Yapıcı metot çağrıldığında ayarlar otomatik veritabanından çekilir
    }

    // Mail ayarlarını veritabanından çekip PHPMailer'ı yapılandırır.
    private function configure()
    {
        $settings_stmt = $this->pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'mail_%'");
        $mail_settings = $settings_stmt->fetchAll(\PDO::FETCH_KEY_PAIR);

        // Sunucu Ayarları
        $this->mail->isSMTP();
        $this->mail->Host       = $mail_settings['mail_host'] ?? '';
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = $mail_settings['mail_username'] ?? '';
        $this->mail->Password   = $mail_settings['mail_password'] ?? '';
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Varsayılan olarak SSL/SMTPS
        $this->mail->Port       = $mail_settings['mail_port'] ?? 465;
        $this->mail->CharSet    = 'UTF-8';
        $this->mail->isHTML(true);

        // Gönderen bilgisi
        $this->mail->setFrom(
            $mail_settings['mail_from_address'] ?? 'noreply@example.com', 
            $mail_settings['mail_from_name'] ?? 'Otel'
        );
    }

    /**
     * E-posta gönderir.
     * @param string $to Alıcı e-posta adresi
     * @param string $subject E-posta konusu
     * @param string $body E-posta içeriği (HTML olabilir)
     * @return bool Gönderim başarılıysa true döner
     * @throws Exception Gönderim sırasında hata olursa Exception fırlatır.
     */
    public function send($to, $subject, $body)
    {
        // Her gönderimde alıcı listesini ve ekleri temizle
        $this->mail->clearAddresses();
        $this->mail->clearAttachments();
        
        $this->mail->addAddress($to);
        $this->mail->Subject = $subject;
        $this->mail->Body    = $body;

        return $this->mail->send();
    }

    // --- Yeni Eklenen Metotlar: PHPMailer ayarlarını dışarıdan yapılandırmak için ---

    public function setHost($host) {
        $this->mail->Host = $host;
    }

    public function setPort($port) {
        $this->mail->Port = $port;
    }

    public function setUsername($username) {
        $this->mail->Username = $username;
    }

    public function setPassword($password) {
        $this->mail->Password = $password;
    }

    public function setFromAddress($address, $name = '') {
        $this->mail->setFrom($address, $name);
    }

    public function setDebugLevel($level) {
        $this->mail->SMTPDebug = $level;
    }

    public function setDebugOutput($output_type) {
        $this->mail->Debugoutput = $output_type;
    }

    // Gönderim sırasında oluşan son hatayı almak için PHPMailer'ın ErrorInfo özelliğini döndürür
    public function getErrorInfo() {
        return $this->mail->ErrorInfo;
    }

    // SMTPSecure ayarını dinamik olarak değiştirmek için
    public function setSMTPSecure($secure_type) {
        if (in_array($secure_type, ['', PHPMailer::ENCRYPTION_SMTPS, PHPMailer::ENCRYPTION_STARTTLS])) {
            $this->mail->SMTPSecure = $secure_type;
        }
    }
}