<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers/countries.php'; // Ülke kodları için
require_once __DIR__ . '/lib/View.php';
require_once __DIR__ . '/lib/PageRepository.php'; // Menü ve sayfa başlığı için
require_once __DIR__ . '/lib/Mailer.php'; // E-posta göndermek için
require_once __DIR__ . '/lib/InquiryRepository.php'; // Yeni: Talep kaydı için

$pdo = $GLOBALS['pdo'];
$view = new App\Lib\View($pdo);
$pageRepo = new App\Lib\PageRepository($pdo);
$inquiryRepo = new App\Lib\InquiryRepository($pdo); // Yeni: InquiryRepository nesnesi

$error_message = '';
$success_message = '';

// Admin panelinden "İletişim" sayfası için belirlenen içeriği çek
$contact_page_content = $pageRepo->findPublishedBySlug('iletisim');
$contact_page_title = $contact_page_content['title'] ?? 'İletişim';
$contact_page_intro_content = $contact_page_content['content'] ?? '<p class="text-center text-gray-600 mb-12 max-w-2xl mx-auto">Sorularınız, önerileriniz veya rezervasyon talepleriniz için bizimle iletişime geçmekten çekinmeyin. Size yardımcı olmaktan mutluluk duyarız.</p>';


// Formdan gelen verileri işle
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $country_code = trim($_POST['country_code'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? 'Genel Bilgi Talebi'); // Konu alanı
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($message)) {
        $error_message = "Adınız, E-posta adresiniz ve Mesajınız zorunludur.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Geçerli bir e-posta adresi giriniz.";
    } else {
        // Talebi veritabanına kaydetme (YENİ EKLENEN KISIM)
        $inquiryType = 'contact';
        $inquirySourceId = null;

        if (isset($_POST['tour_id']) && !empty($_POST['tour_id'])) {
            $inquiryType = 'event'; // 'tour' yerine 'event' kullanılıyor
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

        // *** BURADAKİ TRY-CATCH BLOĞU EKLENDİ ***
        try {
            echo ""; // Bu satır eklendi
            if ($inquiryRepo->createInquiry($inquiryData)) {
                echo ""; // Bu satır eklendi
                // E-posta gönderme mantığı (mevcut kodunuz)
                $mail = new App\Lib\Mailer($pdo); // PDO bağımlılığı eklendi

                $admin_notification_email = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'mail_notification_address'")->fetchColumn();
                if ($admin_notification_email) {
                     $mail->addAddress($admin_notification_email);
                } else {
                     error_log("Mail bildirim adresi ayarlanmamış. E-posta gönderilemedi.");
                }

                $mail->Subject = "Web Sitesinden Yeni Bilgi Talebi: " . $subject;
                $mail->Body = "Ad Soyad: " . $name . "<br>"
                            . "E-posta: " . $email . "<br>"
                            . "Telefon: " . $country_code . " " . $phone . "<br>"
                            . "Konu: " . $subject . "<br>"
                            . "Mesaj: " . nl2br(htmlspecialchars($message)); // HTML formatında gösterim için nl2br eklendi
                $mail->AltBody = strip_tags($mail->Body);

                if ($mail->send()) {
                    $success_message = "Bilgi talebiniz başarıyla gönderildi. En kısa sürede size geri dönüş yapacağız.";
                    // Formu temizlemek için POST verilerini unset yapabiliriz
                    $_POST = [];
                } else {
                    $error_message = "E-posta gönderilirken bir hata oluştu: " . $mail->ErrorInfo;
                    error_log("Mail gönderme hatası: " . $mail->ErrorInfo);
                }
            } else {
                // createInquiry false dönerse (PDOException fırlatılmazsa)
                echo ""; // Bu satır eklendi
                $error_message = "Bilgi talebi kaydedilirken beklenmeyen bir hata oluştu.";
            }
        } catch (\PDOException $e) {
            // Veritabanı hatasını yakala ve kullanıcıya göster
            echo ""; // Bu satır eklendi
            $error_message = "Veritabanı Hatası: " . $e->getMessage();
            error_log("Bilgi talebi veritabanına kaydedilirken PDO hatası: " . $e->getMessage());
        } catch (\Exception $e) {
            // Diğer olası hataları yakala (örneğin Mailer hatası)
            echo ""; // Bu satır eklendi
            $error_message = "Bir hata oluştu: " . $e->getMessage();
            error_log("Genel hata: " . $e->getMessage());
        }
    }
}

// Menü sayfalarını çek
$menu_pages = $pageRepo->getMenuPages();

// Formun varsayılan değerleri (GET parametrelerinden veya POST hatasından)
$form_name = $_POST['name'] ?? '';
$form_email = $_POST['email'] ?? '';
$form_country_code = $_POST['country_code'] ?? '+90';
$form_phone = $_POST['phone'] ?? '';
$form_subject = $_GET['subject'] ?? ($_POST['subject'] ?? ''); // URL'den konu alabiliriz
$form_message = $_POST['message'] ?? '';

// Tur veya Hizmetten gelen ID'leri alalım (formda gizli alan olarak kullanmak için)
$tour_id_param = $_GET['tour_id'] ?? null;
$service_id_param = $_GET['service_id'] ?? null;

// Başlık ve diğer verileri şablona gönder
$data = [
    'title' => $contact_page_title, // Admin panelinden gelen başlık
    'intro_content' => $contact_page_intro_content, // Admin panelinden gelen giriş metni
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
];

$view->render('pages/iletisim_form.php', $data);