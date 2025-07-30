<?php
// Gerekli dosyaları ve veritabanı bağlantısını dahil ediyoruz.
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/GalleryRepository.php';
require_once __DIR__ . '/../lib/PageRepository.php';

if (!isset($pdo)) { $pdo = $GLOBALS['pdo']; }
$galleryRepo = new App\Lib\GalleryRepository($pdo);
$pageRepo = new App\Lib\PageRepository($pdo);

// Admin panelindeki "galeri" sayfasının içeriğini (başlık, metin vb.) çekiyoruz.
$page_data = $pageRepo->findPublishedBySlug('galeri');

// Veritabanından tüm yayınlanmış kategorileri ve resimleri çekiyoruz.
$categories = $galleryRepo->getAllCategories();
$images = $galleryRepo->getAllGalleryImages(true); 
?>

<style>
    /* Stil kodları */
    .gallery-filter-btn { background-color: #e5e7eb; color: #374151; padding: 8px 16px; border-radius: 9999px; font-weight: 600; cursor: pointer; transition: all 0.2s; border: 2px solid transparent; }
    .gallery-filter-btn:hover { background-color: #d1d5db; }
    .gallery-filter-btn.active { background-color: #3b82f6; color: white; border-color: #2563eb; }
    .gallery-item { position: relative; overflow: hidden; border-radius: 0.5rem; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); display: block; animation: fadeIn 0.5s; }
    .gallery-item.hidden { display: none; }
    .gallery-item img { width: 100%; height: 250px; object-fit: cover; transition: transform 0.3s; cursor: pointer; }
    .gallery-item:hover img { transform: scale(1.05); }
    #lightbox { position: fixed; inset: 0; background-color: rgba(0,0,0,0.85); display: flex; align-items: center; justify-content: center; z-index: 9999; }
    #lightbox-content { position: relative; background-color: white; border-radius: 0.5rem; padding: 1rem; max-width: 90%; max-height: 90vh; overflow: auto; display: flex; flex-direction: column; align-items: center; justify-content: center; }
    #lightbox-img { max-width: 100%; max-height: 80vh; object-fit: contain; }
    #lightbox-close-btn { position: absolute; top: 0.5rem; right: 0.5rem; color: white; font-size: 2rem; background-color: #4b5563; border-radius: 9999px; width: 2rem; height: 2rem; display: flex; align-items: center; justify-content: center; cursor: pointer; line-height: 1; padding-bottom: 4px; }
    @keyframes fadeIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
</style>

<div class="container mx-auto px-4 py-8 md:py-12">

    <div class="prose max-w-none lg:prose-xl text-center mb-12">
        <?php
            if (!empty($page_data['content'])) {
                echo $page_data['content'];
            }
        ?>
    </div>

    <div id="gallery-filters" class="flex flex-wrap justify-center gap-4 mb-12">
        <button class="gallery-filter-btn active" data-category="all">Tümü</button>
        <?php foreach ($categories as $category): ?>
            <?php if($category['is_published']): ?>
                <button class="gallery-filter-btn" data-category="<?php echo $category['id']; ?>">
                    <?php echo htmlspecialchars($category['name']); ?>
                </button>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <div id="gallery-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <?php foreach ($images as $image): ?>
            <div class="gallery-item" data-category="<?php echo $image['category_id'] ?? 'none'; ?>">
                <img src="<?php echo BASE_URL . '/uploads/' . htmlspecialchars($image['image_path'] ?: 'placeholder.png'); ?>" 
                     alt="<?php echo htmlspecialchars($image['title']); ?>"
                     class="gallery-thumbnail"
                     data-full-src="<?php echo BASE_URL . '/uploads/' . htmlspecialchars($image['image_path']); ?>">
            </div>
        <?php endforeach; ?>
    </div>

    <div id="lightbox" style="display: none;">
        <div id="lightbox-content">
            <button id="lightbox-close-btn">&times;</button>
            <img id="lightbox-img" src="">
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // --- Değişkenleri Tanımla ---
    const filterContainer = document.getElementById('gallery-filters');
    const galleryGrid = document.getElementById('gallery-grid');
    const lightbox = document.getElementById('lightbox');
    const lightboxImg = document.getElementById('lightbox-img');
    const lightboxCloseBtn = document.getElementById('lightbox-close-btn');

    // --- Filtreleme Olay Dinleyicisi ---
    if (filterContainer) {
        filterContainer.addEventListener('click', function(event) {
            // Sadece butonlara tıklandığında çalış
            if (event.target.tagName !== 'BUTTON') return;

            // Aktif buton stilini ayarla
            filterContainer.querySelector('.active')?.classList.remove('active');
            event.target.classList.add('active');

            const selectedCategory = event.target.getAttribute('data-category');
            const galleryItems = document.querySelectorAll('#gallery-grid .gallery-item');

            // Resimleri filtrele
            galleryItems.forEach(function(item) {
                if (selectedCategory === 'all' || item.getAttribute('data-category') === selectedCategory) {
                    item.classList.remove('hidden');
                } else {
                    item.classList.add('hidden');
                }
            });
        });
    }

    // --- Lightbox Olay Dinleyicileri ---
    if (galleryGrid && lightbox) {
        const openLightbox = (imageSrc) => {
            lightboxImg.src = imageSrc;
            lightbox.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        };

        const closeLightbox = () => {
            lightbox.style.display = 'none';
            document.body.style.overflow = '';
            lightboxImg.src = '';
        };
        
        // Resimlere tıklama olayını dinle
        galleryGrid.addEventListener('click', function(event) {
            if (event.target.classList.contains('gallery-thumbnail')) {
                openLightbox(event.target.dataset.fullSrc);
            }
        });

        // Kapatma olaylarını dinle
        lightboxCloseBtn.addEventListener('click', closeLightbox);
        lightbox.addEventListener('click', e => { if (e.target === lightbox) closeLightbox(); });
        document.addEventListener('keydown', e => { if (e.key === 'Escape' && lightbox.style.display !== 'none') closeLightbox(); });
    }
});
</script>