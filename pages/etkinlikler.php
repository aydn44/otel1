<?php
// Gerekli dosyaları ve veritabanı bağlantısını dahil ediyoruz.
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/EventRepository.php';

if (!isset($pdo)) { $pdo = $GLOBALS['pdo']; }
if (!isset($eventRepo)) { $eventRepo = new App\Lib\EventRepository($pdo); }

// Yayınlanmış tüm etkinlikleri çek
$events = $eventRepo->getAllPublishedEvents();
?>

<style>
    .event-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        flex-shrink: 0; 
        contain: layout;
        height: fit-content;
        align-self: start;
    }
    .event-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
    
    .description-container {
        position: relative;
    }

    .event-description-text { 
        transition: max-height 0.3s ease-out;
        will-change: max-height;
        white-space: pre-wrap;
        overflow: hidden;
        transform: translateZ(0);
        backface-visibility: hidden;
    }

    .event-gradient {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 48px;
        background: linear-gradient(to top, white, transparent);
        pointer-events: none;
        transition: opacity 0.3s ease-out;
    }

    .event-read-more-btn {
        display: block;
        cursor: pointer;
    }
</style>

<div class="container mx-auto px-4 py-12">
    <div class="text-center mb-12">
        <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900">Keşfedilecek Etkinlikler</h1>
        <p class="text-xl text-gray-600 mt-4 max-w-2xl mx-auto">Bölgemizin sunduğu eşsiz güzellikleri ve maceraları profesyonel etkinliklerimizle deneyimleyin.</p>
    </div>

    <?php if (empty($events)): ?>
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 p-4 rounded-md col-span-full text-center">
            Şu an için aktif etkinlik bulunmamaktadır.
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" style="align-items: start;">
            <?php foreach ($events as $index => $event): ?>
                <div class="event-card bg-white rounded-lg shadow-lg overflow-hidden flex flex-col" 
                     data-card-index="<?php echo $index; ?>" 
                     style="break-inside: avoid; position: relative;">
                    <img src="<?php echo BASE_URL . '/uploads/' . htmlspecialchars($event['featured_image'] ?: 'placeholder.png'); ?>" 
                         alt="<?php echo htmlspecialchars($event['title']); ?>" 
                         class="w-full h-56 object-cover">
                    
                    <div class="p-6 flex flex-col flex-grow">
                        <h3 class="text-2xl font-bold text-gray-800 mb-3"><?php echo htmlspecialchars($event['title']); ?></h3>
                        
                        <div class="text-gray-700 leading-relaxed mb-4 flex-grow prose">
                            <div class="description-container relative">
                                <div class="event-description-text" data-card-index="<?php echo $index; ?>">
                                    <?php echo nl2br(htmlspecialchars($event['description'])); ?>
                                </div>
                                <div class="event-gradient" data-card-index="<?php echo $index; ?>"></div>
                            </div>
                            <button class="event-read-more-btn mt-2 text-blue-600 hover:underline text-sm focus:outline-none" 
                                    data-card-index="<?php echo $index; ?>"
                                    onclick="toggleEventDescription(<?php echo $index; ?>)">
                                Daha Fazlası
                            </button>
                        </div>
                        
                        <div class="border-t border-gray-200 pt-4 mt-auto text-center">
                            <a href="<?php echo BASE_URL; ?>/index.php?page=iletisim&subject=<?php echo urlencode($event['title']); ?>" class="w-full block bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-5 rounded-lg transition duration-300">
                                Detaylı Bilgi Al
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
// Global fonksiyon - her etkinlik için ayrı çağrılacak
function toggleEventDescription(cardIndex) {
    console.log(`\n=== toggleEventDescription çağrıldı - Card Index: ${cardIndex} ===`);
    
    const COLLAPSED_HEIGHT = 96;
    
    // Sadece bu index'e sahip elemanları bul
    const textElement = document.querySelector(`.event-description-text[data-card-index="${cardIndex}"]`);
    const gradientElement = document.querySelector(`.event-gradient[data-card-index="${cardIndex}"]`);
    const buttonElement = document.querySelector(`.event-read-more-btn[data-card-index="${cardIndex}"]`);
    
    console.log(`Card ${cardIndex} - Text element:`, textElement);
    console.log(`Card ${cardIndex} - Gradient element:`, gradientElement);
    console.log(`Card ${cardIndex} - Button element:`, buttonElement);
    
    if (!textElement || !gradientElement || !buttonElement) {
        console.error(`Card ${cardIndex} için elemanlar bulunamadı!`);
        return;
    }
    
    // Mevcut durumu kontrol et
    const currentMaxHeight = textElement.style.maxHeight;
    const fullHeight = textElement.scrollHeight;
    
    console.log(`Card ${cardIndex} - Current maxHeight: ${currentMaxHeight}`);
    console.log(`Card ${cardIndex} - Full height: ${fullHeight}px`);
    
    // Başlangıç durumunda maxHeight boşsa, daraltılmış kabul et
    const isCurrentlyCollapsed = !currentMaxHeight || currentMaxHeight === `${COLLAPSED_HEIGHT}px`;
    
    console.log(`Card ${cardIndex} - Is currently collapsed: ${isCurrentlyCollapsed}`);
    
    if (isCurrentlyCollapsed) {
        // Genişlet
        console.log(`Card ${cardIndex} GENİŞLETİLİYOR`);
        
        // Animasyon için scrollHeight'ı hesapla
        const tempMaxHeight = textElement.style.maxHeight;
        textElement.style.maxHeight = 'none';
        const scrollHeight = textElement.scrollHeight;
        textElement.style.maxHeight = tempMaxHeight;
        
        // Kısa bir gecikme sonrası animasyonlu genişletme
        requestAnimationFrame(() => {
            textElement.style.maxHeight = scrollHeight + 'px';
            textElement.style.overflow = 'visible';
        });
        
        gradientElement.style.opacity = '0';
        setTimeout(() => gradientElement.style.display = 'none', 300);
        buttonElement.textContent = 'Daha Azı';
    } else {
        // Daralt
        console.log(`Card ${cardIndex} DARALTILIYOR`);
        
        // Önce tam yüksekliği ayarla, sonra daralt
        const scrollHeight = textElement.scrollHeight;
        textElement.style.maxHeight = scrollHeight + 'px';
        textElement.style.overflow = 'hidden';
        
        requestAnimationFrame(() => {
            textElement.style.maxHeight = COLLAPSED_HEIGHT + 'px';
        });
        
        gradientElement.style.display = 'block';
        setTimeout(() => gradientElement.style.opacity = '1', 10);
        buttonElement.textContent = 'Daha Fazlası';
    }
    
    console.log(`Card ${cardIndex} işlemi tamamlandı.\n`);
}

document.addEventListener('DOMContentLoaded', function() {
    console.log("Etkinlikler sayfası yüklendi!");
    
    const COLLAPSED_HEIGHT = 96;
    
    // Tüm kartları bul ve başlangıç durumunu ayarla
    const eventCards = document.querySelectorAll('.event-card');
    console.log(`Toplam ${eventCards.length} etkinlik kartı bulundu`);
    
    eventCards.forEach((card) => {
        const cardIndex = card.getAttribute('data-card-index');
        console.log(`\n--- Card ${cardIndex} başlangıç ayarları ---`);
        
        const textElement = card.querySelector('.event-description-text');
        const gradientElement = card.querySelector('.event-gradient');
        const buttonElement = card.querySelector('.event-read-more-btn');
        
        if (!textElement || !gradientElement || !buttonElement) {
            console.error(`Card ${cardIndex} elemanları bulunamadı!`);
            return;
        }
        
        // Tam yüksekliği ölç
        textElement.style.maxHeight = 'none';
        textElement.style.overflow = 'visible';
        const fullHeight = textElement.scrollHeight;
        
        console.log(`Card ${cardIndex} - Tam yükseklik: ${fullHeight}px`);
        
        if (fullHeight <= COLLAPSED_HEIGHT) {
            // Kısa içerik - butonu gizle
            buttonElement.style.display = 'none';
            gradientElement.style.display = 'none';
            console.log(`Card ${cardIndex} - Kısa içerik, buton gizlendi`);
        } else {
            // Uzun içerik - daraltılmış başlat
            textElement.style.maxHeight = COLLAPSED_HEIGHT + 'px';
            textElement.style.overflow = 'hidden';
            gradientElement.style.display = 'block';
            gradientElement.style.opacity = '1';
            console.log(`Card ${cardIndex} - Uzun içerik, daraltılmış başlatıldı`);
        }
    });
    
    console.log("Tüm kartların başlangıç ayarları tamamlandı!");
});
</script>