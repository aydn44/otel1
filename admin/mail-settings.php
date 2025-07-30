<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../lib/SettingsRepository.php'; // Ayarları çekmek için
require_once __DIR__ . '/../lib/Mailer.php'; // Mail gönderimi için

$pdo = $GLOBALS['pdo'];
$settingsRepo = new App\Lib\SettingsRepository($pdo);

// Ayarları veritabanından çek
$settings_stmt = $pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'mail_%'");
$settings = $settings_stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$success_message = $_SESSION['success_message'] ?? null;
unset($_SESSION['success_message']);
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['error_message']);

$test_mail_result_message = '';
$test_mail_result_type = ''; // 'success' or 'error'


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_settings') {
        $input_settings = $_POST['settings'];
        $validation_errors = [];

        // --- Doğrulama Kuralları ---
        // SMTP Host boş olmamalı
        if (empty($input_settings['mail_host'])) {
            $validation_errors[] = "SMTP Sunucusu (Host) boş bırakılamaz.";
        }
        // Port sayısal olmalı ve geçerli aralıkta olmalı
        if (empty($input_settings['mail_port']) || !is_numeric($input_settings['mail_port']) || $input_settings['mail_port'] <= 0 || $input_settings['mail_port'] > 65535) {
            $validation_errors[] = "Port numarası geçerli bir sayı olmalıdır (1-65535).";
        }
        // Kullanıcı Adı boş olmamalı
        if (empty($input_settings['mail_username'])) {
            $validation_errors[] = "Kullanıcı Adı boş bırakılamaz.";
        }
        // Gönderen E-posta Adresi geçerli e-posta formatında olmalı
        if (empty($input_settings['mail_from_address']) || !filter_var($input_settings['mail_from_address'], FILTER_VALIDATE_EMAIL)) {
            $validation_errors[] = "Geçerli bir Gönderen E-posta Adresi girilmelidir.";
        }
        // Bildirim Alacak E-posta geçerli e-posta formatında olmalı
        if (empty($input_settings['mail_notification_address']) || !filter_var($input_settings['mail_notification_address'], FILTER_VALIDATE_EMAIL)) {
            $validation_errors[] = "Geçerli bir Bildirim Alacak E-posta adresi girilmelidir.";
        }
        // --- Doğrulama Kuralları Sonu ---


        if (count($validation_errors) > 0) {
            // Hatalar varsa, mesajları ayarla
            $_SESSION['error_message'] = implode('<br>', $validation_errors);
            // Girilen ayarları tekrar formda göstermek için $settings dizisini güncelle
            $settings = array_merge($settings, $input_settings);
        } else {
            // Hata yoksa, veritabanına kaydet
            try {
                $pdo->beginTransaction();
                foreach ($input_settings as $key => $value) {
                    // Sadece mail_ ile başlayan anahtarları güncelle
                    if (strpos($key, 'mail_') === 0) {
                        $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
                        $stmt->execute([$value, $key]);
                    }
                }
                $pdo->commit();
                $_SESSION['success_message'] = 'Ayarlar başarıyla güncellendi.';
                // Ayarları yeniden yükle (güncel değerleri almak için)
                $settings_stmt = $pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'mail_%'");
                $settings = $settings_stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            } catch (Exception $e) {
                $pdo->rollBack();
                $_SESSION['error_message'] = 'Ayarlar güncellenirken bir hata oluştu: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'test_mail') {
        // Test maili gönderme işlemi
        try {
            $mail = new App\Lib\Mailer($pdo);
            
            // Ayarları veritabanından çek (en güncel kaydedilmiş ayarları kullanmak için)
            // Ya da doğrudan POST edilen verileri kullanmak için:
            // $test_settings = $_POST['settings_for_test'] ?? $settings; // Eğer test formu ayrı gönderiliyorsa
            $current_mail_settings = $settingsRepo->getAllSettings();

            // PHPMailer ayarlarını test için geçici olarak override et
            $mail->setHost($current_mail_settings['mail_host'] ?? '');
            $mail->setPort($current_mail_settings['mail_port'] ?? '');
            $mail->setUsername($current_mail_settings['mail_username'] ?? '');
            $mail->setPassword($current_mail_settings['mail_password'] ?? '');
            $mail->setFromAddress(
                $current_mail_settings['mail_from_address'] ?? 'noreply@example.com', 
                $current_mail_settings['mail_from_name'] ?? 'Otel'
            );
            // SMTP Secure ayarını da manuel olarak belirtebiliriz veya Mailer sınıfının configure'undan bırakabiliriz.
            // Varsayılan SMTPSecure değerini kullanırız.

            // Hata ayıklama çıktısını göster (sadece test için)
            $mail->setDebugLevel(\PHPMailer\PHPMailer\SMTP::DEBUG_SERVER); // DEBUG_SERVER seviyesinde detayları göster
            $mail->setDebugOutput('html'); // Çıktıyı HTML formatında sayfada göster

            // Kendi bildirim e-posta adresimize test maili gönderelim
            $test_recipient_email = $current_mail_settings['mail_notification_address'] ?? '';
            
            $test_mail_debug_output = '';
            ob_start(); // Debug çıktısını yakalamak için tamponu başlat

            if (empty($test_recipient_email) || !filter_var($test_recipient_email, FILTER_VALIDATE_EMAIL)) {
                $test_mail_result_message = "Test maili alıcı adresi (Bildirim Alacak E-posta) geçersiz veya boş. Lütfen ayarları kaydedip tekrar deneyin.";
                $test_mail_result_type = 'error';
                ob_end_clean(); // Tamponu temizle, çıktıyı gösterme
            } else {
                if ($mail->send($test_recipient_email, "TEST MAILI: " . date('Y-m-d H:i:s'), "Bu, otel web sitenizden gönderilen bir test e-postasıdır. Ayarlarınız doğru çalışıyor demektir.")) {
                    $test_mail_result_message = "Test maili başarıyla gönderildi. Lütfen '" . htmlspecialchars($test_recipient_email) . "' adresini kontrol edin.";
                    $test_mail_result_type = 'success';
                    $test_mail_debug_output = ob_get_clean(); // Başarılıysa tamponu temizle ve çıktıyı al
                } else {
                    $test_mail_result_message = "Test maili gönderilemedi: " . $mail->getErrorInfo();
                    $test_mail_result_type = 'error';
                    $test_mail_debug_output = ob_get_clean(); // Hata oluşursa tamponu temizle ve debug çıktısını al
                    $test_mail_result_message .= '<br><br><b>PHPMailer Debug Çıktısı:</b><pre>' . htmlspecialchars($test_mail_debug_output) . '</pre>';
                }
            }
            $_SESSION['test_mail_result_message'] = $test_mail_result_message;
            $_SESSION['test_mail_result_type'] = $test_mail_result_type;

        } catch (\Exception $e) {
            $_SESSION['test_mail_result_message'] = "Test maili gönderilirken beklenmeyen bir hata oluştu: " . $e->getMessage();
            $_SESSION['test_mail_result_type'] = 'error';
        }
    }
    // Yönlendirme yapmadan önce session mesajlarının kaydedilmesi
    header('Location: mail-settings.php');
    exit;
}

// Session'da kaydedilmiş test maili sonuçlarını çek
if (isset($_SESSION['test_mail_result_message'])) {
    $test_mail_result_message = $_SESSION['test_mail_result_message'];
    $test_mail_result_type = $_SESSION['test_mail_result_type'];
    unset($_SESSION['test_mail_result_message']);
    unset($_SESSION['test_mail_result_type']);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Mail Ayarları</title><meta charset="UTF-8"><script src="https://cdn.tailwindcss.com"></script><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>.sidebar-link.active { background-color: #1D4ED8; }</style>
</head>
<body class="bg-gray-200">
    <div class="flex min-h-screen">
        <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
        <div class="flex-1 p-10">
            <h1 class="text-3xl font-bold text-gray-800 mb-8">Mail Ayarları (SMTP)</h1>
            <?php if ($success_message): ?>
            <div class="bg-green-100 text-green-800 p-4 rounded-lg mb-6"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>
            <?php if ($error_message): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
            <?php endif; ?>

            <?php if ($test_mail_result_message): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $test_mail_result_type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                <?php echo $test_mail_result_message; ?>
            </div>
            <?php endif; ?>

            <div class="bg-white p-8 rounded-lg shadow-lg">
                <form action="mail-settings.php" method="POST" class="mb-8">
                    <input type="hidden" name="action" value="save_settings">
                    <div class="space-y-4">
                        <div><label for="mail_host" class="block font-bold">SMTP Sunucusu (Host):</label><input type="text" id="mail_host" name="settings[mail_host]" value="<?php echo htmlspecialchars($settings['mail_host'] ?? ''); ?>" class="w-full p-2 border rounded mt-1" required></div>
                        <div><label for="mail_port" class="block font-bold">Port:</label><input type="text" id="mail_port" name="settings[mail_port]" value="<?php echo htmlspecialchars($settings['mail_port'] ?? ''); ?>" class="w-full p-2 border rounded mt-1" required></div>
                        <div><label for="mail_username" class="block font-bold">Kullanıcı Adı:</label><input type="text" id="mail_username" name="settings[mail_username]" value="<?php echo htmlspecialchars($settings['mail_username'] ?? ''); ?>" class="w-full p-2 border rounded mt-1" required></div>
                        <div><label for="mail_password" class="block font-bold">Şifre:</label><input type="password" id="mail_password" name="settings[mail_password]" value="<?php echo htmlspecialchars($settings['mail_password'] ?? ''); ?>" class="w-full p-2 border rounded mt-1"></div>
                        <div><label for="mail_from_address" class="block font-bold">Gönderen E-posta Adresi:</label><input type="email" id="mail_from_address" name="settings[mail_from_address]" value="<?php echo htmlspecialchars($settings['mail_from_address'] ?? ''); ?>" class="w-full p-2 border rounded mt-1" required></div>
                        <div><label for="mail_from_name" class="block font-bold">Gönderen Adı:</label><input type="text" id="mail_from_name" name="settings[mail_from_name]" value="<?php echo htmlspecialchars($settings['mail_from_name'] ?? ''); ?>" class="w-full p-2 border rounded mt-1"></div>
                        <div><label for="mail_notification_address" class="block font-bold">Bildirim Alacak E-posta:</label><input type="email" id="mail_notification_address" name="settings[mail_notification_address]" value="<?php echo htmlspecialchars($settings['mail_notification_address'] ?? ''); ?>" class="w-full p-2 border rounded mt-1" required></div>
                    </div>
                    <div class="text-right mt-6">
                        <button type="submit" class="bg-blue-600 text-white font-bold py-2 px-6 rounded hover:bg-blue-700">Ayarları Kaydet</button>
                    </div>
                </form>

                <hr class="my-8">

                <h2 class="text-xl font-semibold mb-4">Test Maili Gönder</h2>
                <p class="text-gray-700 mb-4">Kaydedilmiş mail ayarlarını kullanarak bir test maili gönderebilirsiniz. Sonuçlar bu sayfanın üstünde gösterilecektir.</p>
                <form action="mail-settings.php" method="POST">
                    <input type="hidden" name="action" value="test_mail">
                    <button type="submit" class="bg-purple-600 text-white font-bold py-2 px-6 rounded hover:bg-purple-700">Test Maili Gönder</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>