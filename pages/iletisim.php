<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../helpers/countries.php';
require_once __DIR__ . '/../lib/View.php';
require_once __DIR__ . '/../lib/PageRepository.php';
require_once __DIR__ . '/../lib/Mailer.php';
require_once __DIR__ . '/../lib/InquiryRepository.php';
require_once __DIR__ . '/../lib/SettingsRepository.php';

$pdo = $GLOBALS['pdo'];
$view = new App\Lib\View($pdo);
$pageRepo = new App\Lib\PageRepository($pdo);
$inquiryRepo = new App\Lib\InquiryRepository($pdo);
$settingsRepo = new App\Lib\SettingsRepository($pdo);

$error_message = '';
$success_message = '';
$debug_output = ''; // Hata ayıklama çıktısı için değişken

$contact_page_content = $pageRepo->findPublishedBySlug('iletisim');
$contact_page_title = $contact_page_content['title'] ?? 'İletişim';
$contact_page_intro_content = $contact_page_content['content'] ?? '<p class="text-center text-gray-600 mb-12 max-w-2xl mx-auto">Sorularınız, önerileriniz veya rezervasyon talepleriniz için bizimle iletişime geçmekten çekinmeyin. Size yardımcı olmaktan mutluluk duyarız.</p>';

$contact_settings = $settingsRepo->getContactSettings();
$whatsapp_number = preg_replace('/[^0-9]/', '', $contact_settings['whatsapp_number'] ?? '');
$map_iframe_code = $contact_settings['contact_map_iframe'] ?? '';
$contact_phone = $contact_settings['contact_phone'] ?? '';
$contact_email = $settingsRepo->getSetting('contact_email') ?? '';
$contact_address = $settingsRepo->getSetting('contact_address') ?? '';

$whatsapp_message_subject = $_GET['subject'] ?? ($_POST['subject'] ?? '');
$whatsapp_message = "Merhaba, '" . htmlspecialchars($whatsapp_message_subject) . "' hakkında bilgi almak istiyorum.";


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? ''); // Kullanıcının mail adresi
    $country_code = trim($_POST['country_code'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? 'Genel Bilgi Talebi');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($message)) {
        $error_message = "Adınız, E-posta adresiniz ve Mesajınız zorunludur.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Geçerli bir e-posta adresi giriniz.";
    } else {
        $inquiryType = 'contact';
        $inquirySourceId = null;

        if (isset($_POST['tour_id']) && !empty($_POST['tour_id'])) {
            $inquiryType = 'event';
            $inquirySourceId = (int)$_POST['tour_id'];
        } elseif (isset($_POST['service_id']) && !empty($_POST['service_id'])) {
            $inquiryType = 'service';
            $inquirySourceId = (int)$_POST['service_id'];
        }

        $inquiryData = [
            'name' => $name,
            'email' => $email,
            'country_code' => $country_code,
            'phone' => $phone,
            'subject' => $subject,
            'message' => $message,
            'type' => $inquiryType,
            'source_id' => $inquirySourceId
        ];

        try {
            if ($inquiryRepo->createInquiry($inquiryData)) {
                $success_message = "Bilgi talebiniz başarıyla alınmıştır. En kısa sürede size geri dönüş yapacağız.";

                // Admin'e mail gönderme
                $mail_to_admin = new App\Lib\Mailer($pdo);
                $mail_to_user = new App\Lib\Mailer($pdo); // Kullanıcıya göndermek için ayrı bir mailer nesnesi (isteğe bağlı, tek nesne de kullanılabilir)

                // Admin'e gönderilecek mail ayarları
                $admin_notification_email = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'mail_notification_address'")->fetchColumn();
                $hotel_name = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'site_name'")->fetchColumn(); // Otel adını çek

                if ($admin_notification_email && filter_var($admin_notification_email, FILTER_VALIDATE_EMAIL)) {
                    $admin_email_subject = "Web Sitesinden Yeni Bilgi Talebi: " . $subject;
                    $admin_email_body = "<h3>Yeni Bir İletişim Mesajı Aldınız</h3>"
                                        . "<p><strong>Gönderen Adı:</strong> " . htmlspecialchars($name) . "</p>"
                                        . "<p><strong>Gönderen E-posta:</strong> " . htmlspecialchars($email) . "</p>"
                                        . "<p><strong>Telefon:</strong> " . htmlspecialchars($country_code) . " " . htmlspecialchars($phone) . "</p>"
                                        . "<p><strong>Konu:</strong> " . htmlspecialchars($subject) . "</p>"
                                        . "<hr>"
                                        . "<h4>Mesaj:</h4>"
                                        . "<p>" . nl2br(htmlspecialchars($message)) . "</p>";
                    
                    if (!$mail_to_admin->send($admin_notification_email, $admin_email_subject, $admin_email_body)) {
                        error_log("Admin mail gönderme hatası: " . $mail_to_admin->getErrorInfo());
                    }
                } else {
                    error_log("Admin bildirim adresi geçersiz veya ayarlanmamış. Admin mail gönderilemedi.");
                }

                // Kullanıcıya onay maili gönderme (YENİ EKLENEN KISIM)
                if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $user_email_subject = $hotel_name . " - Bilgi Talebiniz Alındı";
                    $user_email_body = "<h3>Sayın " . htmlspecialchars($name) . ",</h3>"
                                       . "<p>Bilgi talebiniz başarıyla alınmıştır. En kısa sürede size geri dönüş yapacağız.</p>"
                                       . "<p>Talebinizin detayları:</p>"
                                       . "<ul>"
                                       . "<li><strong>Konu:</strong> " . htmlspecialchars($subject) . "</li>"
                                       . "<li><strong>Mesajınız:</strong> " . nl2br(htmlspecialchars(mb_strimwidth($message, 0, 200, '...'))) . "</li>" // Mesajın ilk 200 karakteri
                                       . "</ul>"
                                       . "<p>Herhangi bir sorunuz olursa lütfen bizimle tekrar iletişime geçmekten çekinmeyin.</p>"
                                       . "<p>Teşekkür ederiz,<br><strong>" . htmlspecialchars($hotel_name) . " Ekibi</strong></p>";

                    if (!$mail_to_user->send($email, $user_email_subject, $user_email_body)) {
                        error_log("Kullanıcıya onay maili gönderme hatası: " . $mail_to_user->getErrorInfo());
                    }
                } else {
                    error_log("Kullanıcı e-posta adresi geçersiz veya boş. Kullanıcıya onay maili gönderilemedi.");
                }

            } else {
                $error_message = "Bilgi talebi kaydedilirken beklenmeyen bir hata oluştu.";
            }
        } catch (\PDOException $e) {
            $error_message = "Veritabanı Hatası oluştu. Lütfen daha sonra tekrar deneyin.";
            error_log("Bilgi talebi veritabanına kaydedilirken PDO hatası: " . $e->getMessage());
        } catch (\Exception $e) {
            $error_message = "Mesajınız gönderilirken beklenmeyen bir sorun oluştu. Lütfen daha sonra tekrar deneyin.";
            error_log("Genel hata (muhtemelen Mailer): " . $e->getMessage());
        }
    }
}

$menu_pages = $pageRepo->getMenuPages();

$form_name = $_POST['name'] ?? '';
$form_email = $_POST['email'] ?? '';
$form_country_code = $_POST['country_code'] ?? '+90';
$form_phone = $_POST['phone'] ?? '';
$form_subject = $_GET['subject'] ?? ($_POST['subject'] ?? '');
$form_message = $_POST['message'] ?? '';

$tour_id_param = $_GET['tour_id'] ?? null;
$service_id_param = $_GET['service_id'] ?? null;

$data = [
    'title' => $contact_page_title,
    'intro_content' => $contact_page_intro_content,
    'menu_pages' => $menu_pages,
    'error_message' => $error_message,
    'success_message' => $success_message,
    'form_name' => $form_name,
    'form_email' => $form_email,
    'form_country_code' => $form_country_code,
    'form_phone' => $form_phone,
    'form_subject' => $form_subject,
    'form_message' => $form_message,
    'tour_id_param' => $tour_id_param,
    'service_id_param' => $service_id_param,
    'whatsapp_number' => $whatsapp_number,
    'map_iframe_code' => $map_iframe_code,
    'contact_phone' => $contact_phone,
    'contact_email' => $contact_email,
    'contact_address' => $contact_address,
    'whatsapp_message' => $whatsapp_message
];

$view->render('pages/iletisim_form.php', $data);