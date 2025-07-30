<div class="container mx-auto px-4 py-8 md:py-12">
    <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 text-center mb-8 md:mb-12"><?php echo htmlspecialchars($title); ?></h1>
    <p class="text-xl text-gray-700 text-center max-w-3xl mx-auto mb-12">Konaklamanızı unutulmaz kılmak için tasarladığımız birinci sınıf olanaklarımızı ve size özel hizmetlerimizi keşfedin. Her anınızı konfor ve lüks içinde geçirmeniz için buradayız.</p>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php if (empty($services)): ?>
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 p-4 rounded-md col-span-full text-center">Şu an için aktif hizmet bulunmamaktadır.</div>
        <?php else: ?>
            <?php foreach ($services as $index => $service): ?>
                <div class="bg-white rounded-xl shadow-lg overflow-hidden transform hover:-translate-y-2 transition-transform duration-300 flex flex-col">
                    <?php /* Eğer hizmetlerin de featured_image'i olacaksa buraya eklenebilir */ ?>
                    <div class="p-6 flex flex-col flex-grow">
                        <h2 class="text-2xl font-bold text-gray-900 mb-3 flex items-center">
                            <?php if (!empty($service['icon_class'])): ?>
                                <i class="<?php echo htmlspecialchars($service['icon_class']); ?> text-blue-500 mr-3 text-3xl"></i>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($service['name']); ?>
                        </h2>
                        <div class="text-gray-700 mb-4 flex-grow">
                            <?php 
                            $description_text_original = $service['description_tr'];
                            $plain_text_length = mb_strlen(strip_tags($description_text_original));
                            $char_limit = 150; 
                            $needs_read_more = ($plain_text_length > $char_limit);
                            ?>
                            <div class="description-container relative">
                                <div id="service-description-<?php echo $index; ?>" class="description-content-item">
                                    <?php echo $description_text_original; ?>
                                    <?php 
                                    // Test metni artık tamamen gizli bir div içinde
                                    if (mb_strlen(strip_tags($description_text_original)) < 300) { // Orijinal metin kısaysa test metnini ekle
                                        echo "<div class='hidden-test-content'>"; // CSS ile gizlenecek
                                        echo "<p>Bu uzun bir açıklama metnidir. Bu açıklama, 'Daha Fazlası' butonunun nasıl çalıştığını test etmek için eklenmiştir. Normalde bu kadar uzun bir metin olmayabilir, ancak taşma sorununu gözlemlemek için gereklidir. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p><p>Bu ikinci paragraf, metnin daha da uzun olmasını sağlar. Lütfen bu metnin daraltılıp genişletilebildiğini kontrol edin. Eğer hala taşma oluyorsa, sorun CSS önceliğinde veya JavaScript'in DOM'u manipüle etmesindedir.</p><p>Üçüncü paragraf, içeriğin kesinlikle yeterince uzun olmasını garanti eder. Test için bu metin kasıtlı olarak uzatılmıştır.</p>";
                                        echo "</div>";
                                        $needs_read_more = true; // Test metni varsa buton görünsün
                                    }
                                    ?>
                                </div>
                                <?php if ($needs_read_more): ?>
                                    <div id="service-gradient-<?php echo $index; ?>" class="gradient-overlay-item"></div>
                                <?php endif; ?>
                            </div>
                            <?php if ($needs_read_more): ?>
                                <button data-target="service-description-<?php echo $index; ?>" data-gradient="service-gradient-<?php echo $index; ?>" class="read-more-btn-item mt-2 text-blue-600 hover:underline text-sm focus:outline-none">Daha Fazlası</button>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($service['price']) && $service['price'] > 0): ?>
                        <div class="border-t border-gray-200 pt-4 mt-auto">
                            <div class="flex justify-between items-center">
                                <span class="text-2xl font-bold text-blue-700"><?php echo htmlspecialchars(number_format($service['price'], 2)); ?> TL</span>
                                <a href="<?php echo BASE_URL; ?>/iletisim.php?subject=Hizmet Bilgi Talebi: <?php echo urlencode($service['name']); ?>&service_id=<?php echo htmlspecialchars($service['id']); ?>" class="bg-blue-600 text-white font-bold py-2 px-5 rounded-lg hover:bg-blue-700 transition duration-300">Bilgi Al</a>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<style>
/* CSS for Read More functionality */
.description-container {
    position: relative;
    min-height: 120px;
}

.description-content-item {
    transition: height 0.3s ease-out, max-height 0.3s ease-out;
    overflow: hidden;
}

.gradient-overlay-item {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 48px;
    background: linear-gradient(to top, white, transparent);
    pointer-events: none;
    transition: opacity 0.3s ease-out;
}

/* Test metnini tamamen gizlemek için */
.hidden-test-content {
    display: none !important;
    height: 0 !important;
    overflow: hidden !important;
    opacity: 0 !important;
    visibility: hidden !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log("Hizmetler sayfası yüklendi ve JavaScript çalışıyor!"); 

    const readMoreButtons = document.querySelectorAll('.read-more-btn-item'); 
    const COLLAPSED_HEIGHT = 96; // 96px, Tailwind'in h-24 karşılığı

    readMoreButtons.forEach(button => {
        const targetId = button.dataset.target;
        const gradientId = button.dataset.gradient;

        const descriptionContent = document.getElementById(targetId);
        const gradientOverlay = document.getElementById(gradientId);

        if (!descriptionContent) {
            console.error("Açıklama içeriği elementi bulunamadı: " + targetId);
            return;
        }

        // İçeriğin orijinal yüksekliğini al (tüm kısıtlamaları kaldırarak)
        descriptionContent.style.maxHeight = 'none'; 
        descriptionContent.style.height = 'auto'; 
        descriptionContent.style.overflow = 'visible'; 
        const originalHeight = descriptionContent.scrollHeight; // Gerçek yüksekliği al
        
        // Eğer içerik zaten kısa ise (daraltılmış yükseklikten küçük veya eşitse)
        if (originalHeight <= COLLAPSED_HEIGHT) { 
            button.style.display = 'none'; // Butonu gizle
            if (gradientOverlay) {
                gradientOverlay.style.display = 'none'; 
            }
            descriptionContent.style.maxHeight = 'none'; 
            descriptionContent.style.height = 'auto';
            descriptionContent.style.overflow = 'visible'; 
            return; 
        } else {
            // Eğer içerik uzunsa, başlangıçta daraltılmış olarak ayarla
            descriptionContent.style.maxHeight = COLLAPSED_HEIGHT + 'px';
            descriptionContent.style.height = COLLAPSED_HEIGHT + 'px'; 
            descriptionContent.style.overflow = 'hidden'; 
            if (gradientOverlay) {
                gradientOverlay.style.display = 'block'; 
            }
        }

        // Buton tıklama olay dinleyicisi
        button.addEventListener('click', function() {
            // Mevcut durum daraltılmış mı? (height değeri COLLAPSED_HEIGHT'e eşitse)
            if (descriptionContent.style.height === COLLAPSED_HEIGHT + 'px') {
                // Genişlet
                descriptionContent.style.maxHeight = originalHeight + 'px'; 
                descriptionContent.style.height = originalHeight + 'px'; 
                descriptionContent.style.overflow = 'visible';
                if (gradientOverlay) {
                    gradientOverlay.style.display = 'none';
                }
                this.textContent = 'Daha Azı'; 
            } else {
                // Daralt
                descriptionContent.style.maxHeight = COLLAPSED_HEIGHT + 'px'; 
                descriptionContent.style.height = COLLAPSED_HEIGHT + 'px'; 
                descriptionContent.style.overflow = 'hidden';
                if (gradientOverlay) {
                    gradientOverlay.style.display = 'block';
                }
                this.textContent = 'Daha Fazlası'; 
            }
        });
    });
});
</script>