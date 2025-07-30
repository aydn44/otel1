<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/PageRepository.php';
// Yeni ekleyeceğimiz özel şablonlar için diğer repository'leri de dahil edelim
require_once __DIR__ . '/lib/GalleryRepository.php';
require_once __DIR__ . '/lib/EventRepository.php'; // Adını EventRepository olarak değiştirdik

$pdo = $GLOBALS['pdo'];
$pageRepo = new App\Lib\PageRepository($pdo);
// Diğer repository nesnelerini de oluşturalım ki şablonlar içinde kullanılabilsin
$galleryRepo = new App\Lib\GalleryRepository($pdo);
$eventRepo = new App\Lib\EventRepository($pdo);

// Menü navigasyonu için sayfa listesini çeker.
$menu_pages = $pageRepo->getMenuPages();

$page_slug = $_GET['slug'] ?? null;

if (!$page_slug) { 
    header('Location: index.php'); 
    exit; 
}

// Veritabanından sayfa bilgilerini (şablon adı dahil) çekiyoruz
$page_db_data = $pageRepo->findPublishedBySlug($page_slug);

if (!$page_db_data) {
    // Sayfa bulunamadıysa 404 hatası verip çıkalım
    header("HTTP/1.0 404 Not Found");
    // Basit bir hata mesajı veya özel bir 404.php sayfası çağırılabilir
    include __DIR__ . '/pages/404.php'; 
    exit;
}

// Sayfa başlığını veritabanından gelen veriye göre ayarlıyoruz.
$data['title'] = $page_db_data['title'] ?? 'Sayfa Bulunamadı';

ob_start();

// === YENİ AKILLI YAPI ===
// Veritabanından gelen şablon adını kontrol ediyoruz.
$template_name = $page_db_data['template'] ?? 'default.php';
$template_file = __DIR__ . '/pages/' . $template_name;

if ($template_name !== 'default.php' && !empty($template_name) && file_exists($template_file)) {
    // Eğer özel bir şablon belirtilmişse ve bu dosya mevcutsa, o dosyayı çağırır.
    // Bu, 'galeri.php', 'etkinlikler.php' gibi dosyaları otomatik olarak çalıştırır.
    include $template_file;
} else {
    // Eğer şablon 'default.php' ise veya boşsa, standart içeriği (TinyMCE editör içeriği) gösterir.
    echo '<div class="prose max-w-none lg:prose-xl p-4">';
    echo $page_db_data['content'] ?? '';
    echo '</div>';
}

$content = ob_get_clean();

// Ana şablonu (header, footer ve menüyü içeren) çağırıyoruz.
include __DIR__ . '/includes/layout.php';
?>