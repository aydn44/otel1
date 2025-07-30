<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../lib/SettingsRepository.php';
require_once __DIR__ . '/../helpers/security.php';
require_once __DIR__ . '/../helpers/media_upload.php';

$pdo = $GLOBALS['pdo'];
$settingsRepo = new App\Lib\SettingsRepository($pdo);
$message = '';
$message_type = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'])) { csrf_fail(); }
    
    $settings_data = $_POST['settings'];
    $settings_data['header_transparent'] = $settings_data['header_transparent'] ?? 0;
    $settings_data['footer_transparent'] = $settings_data['footer_transparent'] ?? 0;
    $settings_data['reservation_bar_active'] = $settings_data['reservation_bar_active'] ?? 0;
    $settings_data['reservation_bar_transparent'] = $settings_data['reservation_bar_transparent'] ?? 0;

    if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = ROOT_PATH . '/uploads/';
        $current_logo = $settingsRepo->getSetting('site_logo');
        $new_logo_name = upload_image($_FILES['site_logo'], $upload_dir);
        if ($new_logo_name) {
            $settings_data['site_logo'] = $new_logo_name;
            if ($current_logo && file_exists($upload_dir . $current_logo)) {
                @unlink($upload_dir . $current_logo);
            }
        }
    }

    if ($settingsRepo->updateSettings($settings_data)) {
        $message = 'Ayarlar başarıyla güncellendi.';
    } else {
        $message = 'Ayarlar güncellenirken bir hata oluştu.';
        $message_type = 'error';
    }
}

$settings = $settingsRepo->getAllSettings();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <title>Site Ayarları</title><meta charset="UTF-8"><script src="https://cdn.tailwindcss.com"></script><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>.sidebar-link.active { background-color: #1D4ED8; }</style>
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
        <div class="flex-1 p-10">
            <h1 class="text-3xl font-bold text-gray-800 mb-8">Site Ayarları</h1>
            <?php if ($message): ?>
            <div class="p-4 mb-6 rounded-lg <?php echo $message_type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <div class="bg-white p-8 rounded-lg shadow-lg">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

                    <div class="mb-6 border-b pb-6"><h2 class="text-xl font-semibold mb-4 text-gray-700">Genel Site ve Header Ayarları</h2><div class="space-y-4"><div><label for="site_name" class="block text-gray-700 font-bold mb-1">Otel Adı:</label><input type="text" id="site_name" name="settings[site_name]" value="<?php echo htmlspecialchars($settings['site_name'] ?? 'Otel Adı'); ?>" class="w-full p-2 border rounded"></div><div><label for="site_logo" class="block text-gray-700 font-bold mb-1">Site Logosu:</label><input type="file" id="site_logo" name="site_logo" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"><?php if (!empty($settings['site_logo'])): ?><div class="mt-2"><img src="<?php echo BASE_URL . '/uploads/' . htmlspecialchars($settings['site_logo']); ?>" alt="Mevcut Logo" class="h-16 bg-gray-200 p-1 rounded"></div><?php endif; ?></div><div><label for="header_bg_color" class="block text-gray-700 font-bold mb-1">Header Arka Plan Rengi:</label><input type="color" id="header_bg_color" name="settings[header_bg_color]" value="<?php echo htmlspecialchars($settings['header_bg_color'] ?? '#374151'); ?>" class="w-24 h-10 p-1 border rounded"></div><div><label class="flex items-center"><input type="checkbox" name="settings[header_transparent]" value="1" <?php echo !empty($settings['header_transparent']) ? 'checked' : ''; ?> class="form-checkbox h-5 w-5 text-blue-600"><span class="ml-2 text-gray-700">Header Arka Planı Şeffaf Olsun mu?</span></label></div></div></div>

                    <div class="mb-6 border-b pb-6"><h2 class="text-xl font-semibold mb-4 text-gray-700">Rezervasyon Modülü Ayarları</h2><div class="space-y-4"><div><label class="flex items-center"><input type="checkbox" name="settings[reservation_bar_active]" value="1" <?php echo !empty($settings['reservation_bar_active']) ? 'checked' : ''; ?> class="form-checkbox h-5 w-5 text-blue-600"><span class="ml-2 text-gray-700 font-bold">Rezervasyon Modülü Aktif mi?</span></label></div><div><label for="reservation_bar_bg_color" class="block text-gray-700 font-bold mb-1">Arka Plan Rengi:</label><input type="color" id="reservation_bar_bg_color" name="settings[reservation_bar_bg_color]" value="<?php echo htmlspecialchars($settings['reservation_bar_bg_color'] ?? '#4a5568'); ?>" class="w-24 h-10 p-1 border rounded"></div><div><label for="reservation_bar_text_color" class="block text-gray-700 font-bold mb-1">Yazı Rengi:</label><input type="color" id="reservation_bar_text_color" name="settings[reservation_bar_text_color]" value="<?php echo htmlspecialchars($settings['reservation_bar_text_color'] ?? '#ffffff'); ?>" class="w-24 h-10 p-1 border rounded"></div><div><label class="flex items-center"><input type="checkbox" name="settings[reservation_bar_transparent]" value="1" <?php echo !empty($settings['reservation_bar_transparent']) ? 'checked' : ''; ?> class="form-checkbox h-5 w-5 text-blue-600"><span class="ml-2 text-gray-700">Arka Plan Şeffaf Olsun mu?</span></label></div></div></div>

                    <div class="mb-6 border-b pb-6"><h2 class="text-xl font-semibold mb-4 text-gray-700">İletişim ve Footer Ayarları</h2><div class="space-y-4"><div><label for="footer_bg_color" class="block text-gray-700 font-bold mb-1">Footer Arka Plan Rengi:</label><input type="color" id="footer_bg_color" name="settings[footer_bg_color]" value="<?php echo htmlspecialchars($settings['footer_bg_color'] ?? '#1e2d3b'); ?>" class="w-24 h-10 p-1 border rounded"></div><div><label class="flex items-center"><input type="checkbox" name="settings[footer_transparent]" value="1" <?php echo !empty($settings['footer_transparent']) ? 'checked' : ''; ?> class="form-checkbox h-5 w-5 text-blue-600"><span class="ml-2 text-gray-700">Footer Arka Planı Şeffaf Olsun mu?</span></label></div><div><label for="contact_phone" class="block text-gray-700 font-bold mb-1">İletişim Telefonu:</label><input type="text" id="contact_phone" name="settings[contact_phone]" value="<?php echo htmlspecialchars($settings['contact_phone'] ?? ''); ?>" class="w-full p-2 border rounded"></div><div><label for="whatsapp_number" class="block text-gray-700 font-bold mb-1">WhatsApp Numarası:</label><input type="text" id="whatsapp_number" name="settings[whatsapp_number]" value="<?php echo htmlspecialchars($settings['whatsapp_number'] ?? ''); ?>" class="w-full p-2 border rounded" placeholder="905551234567"></div><div><label for="contact_email" class="block text-gray-700 font-bold mb-1">İletişim E-postası:</label><input type="email" id="contact_email" name="settings[contact_email]" value="<?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?>" class="w-full p-2 border rounded"></div><div><label for="contact_address" class="block text-gray-700 font-bold mb-1">Adres:</label><textarea id="contact_address" name="settings[contact_address]" rows="3" class="w-full p-2 border rounded"><?php echo htmlspecialchars($settings['contact_address'] ?? ''); ?></textarea></div><div><label for="contact_map_iframe" class="block text-gray-700 font-bold mb-1">Google Harita Gömme Kodu:</label><textarea id="contact_map_iframe" name="settings[contact_map_iframe]" rows="4" class="w-full p-2 border rounded font-mono text-sm"><?php echo htmlspecialchars($settings['contact_map_iframe'] ?? ''); ?></textarea></div><div><label for="social_facebook" class="block text-gray-700 font-bold mb-1">Facebook Linki:</label><input type="url" id="social_facebook" name="settings[social_facebook]" value="<?php echo htmlspecialchars($settings['social_facebook'] ?? ''); ?>" class="w-full p-2 border rounded"></div><div><label for="social_instagram" class="block text-gray-700 font-bold mb-1">Instagram Linki:</label><input type="url" id="social_instagram" name="settings[social_instagram]" value="<?php echo htmlspecialchars($settings['social_instagram'] ?? ''); ?>" class="w-full p-2 border rounded"></div><div><label for="social_twitter" class="block text-gray-700 font-bold mb-1">Twitter (X) Linki:</label><input type="url" id="social_twitter" name="settings[social_twitter]" value="<?php echo htmlspecialchars($settings['social_twitter'] ?? ''); ?>" class="w-full p-2 border rounded"></div></div></div>

                    <div class="text-right">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg text-lg"><i class="fas fa-save mr-2"></i>Tüm Ayarları Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>