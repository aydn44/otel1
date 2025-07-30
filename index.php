<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/PageRepository.php';
require_once __DIR__ . '/lib/RoomRepository.php';
require_once __DIR__ . '/lib/EventRepository.php'; 
require_once __DIR__ . '/lib/GalleryRepository.php';
require_once __DIR__ . '/lib/SettingsRepository.php';

$pdo = $GLOBALS['pdo'];
$pageRepo = new App\Lib\PageRepository($pdo);
$roomRepo = new App\Lib\RoomRepository($pdo);
$eventRepo = new App\Lib\EventRepository($pdo);
$galleryRepo = new App\Lib\GalleryRepository($pdo);
$settingsRepo = new App\Lib\SettingsRepository($pdo);

// YENİ EKLENDİ: Tüm site ayarlarını her sayfa için çekiyoruz.
$site_settings = $settingsRepo->getAllSettings();
$menu_pages = $pageRepo->getMenuPages();

$page_slug = $_GET['page'] ?? 'anasayfa';

ob_start();

switch ($page_slug) {
    case 'anasayfa':
        $data['title'] = 'Ana Sayfa';
        $homepage_settings = $pageRepo->getHomepageSettings();
        $featured_room_types = $roomRepo->getFeaturedRoomTypes();
        include __DIR__ . '/pages/anasayfa.php';
        break;

    case 'galeri':
        $data['title'] = 'Galeri';
        include __DIR__ . '/pages/galeri.php';
        break;

    case 'etkinlikler':
        $data['title'] = 'Etkinlikler';
        include __DIR__ . '/pages/etkinlikler.php';
        break;
    
    case 'iletisim':
        $data['title'] = 'İletişim';
        $contact_settings = $settingsRepo->getContactSettings();
        $subject_from_url = $_GET['subject'] ?? '';
        $whatsapp_number = preg_replace('/[^0-9]/', '', $contact_settings['whatsapp_number'] ?? '');
        $map_iframe_code = $contact_settings['contact_map_iframe'] ?? '';
        $whatsapp_message = "Merhaba, '" . htmlspecialchars($subject_from_url) . "' hakkında bilgi almak istiyorum.";
        include __DIR__ . '/pages/iletisim.php';
        break;

    default:
        $page_db_data = $pageRepo->findPublishedBySlug($page_slug);
        if ($page_db_data) {
            $data['title'] = $page_db_data['title'];
            echo '<div class="prose max-w-none lg:prose-xl p-4">';
            echo $page_db_data['content'] ?? '';
            echo '</div>';
        } else {
            $data['title'] = 'Sayfa Bulunamadı';
            include __DIR__ . '/pages/404.php';
        }
        break;
}

$content = ob_get_clean();
include __DIR__ . '/includes/layout.php';
?>