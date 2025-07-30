<?php
// Bu dosya, `View` sınıfı tarafından render edildiği için,
// $homepage_settings ve $featured_room_types değişkenlerine erişimi varsayıyoruz.
// Eğer doğrudan erişim hatası alırsanız, bu dosyanın başına `require_once __DIR__ . '/../config.php';` eklemeniz gerekebilir.
// Ancak genelde View sistemi bu bağımlılıkları yönetir.
?>

<!-- Hero Section -->
<section class="relative bg-cover bg-center h-screen flex items-center justify-center text-white" style="background-image: url('<?php echo BASE_URL . '/' . htmlspecialchars($homepage_settings['homepage_hero_image'] ?? 'placeholder.png'); ?>');">
    <div class="absolute inset-0 bg-black opacity-50"></div>
    <div class="relative z-10 text-center px-4">
        <h1 class="text-5xl md:text-7xl font-extrabold leading-tight mb-4 animate-fade-in-up">
            <?php echo htmlspecialchars($homepage_settings['homepage_hero_title'] ?? 'Konfor ve Zarafetin Buluştuğu Yer'); ?>
        </h1>
        <p class="text-xl md:text-2xl mb-8 animate-fade-in-up delay-100">
            <?php echo htmlspecialchars($homepage_settings['homepage_hero_subtitle'] ?? 'Hayallerinizdeki tatil için doğru adrestesiniz.'); ?>
        </p>
        <!-- Bu buton, sayfa içindeki arama formuna yönlendirir -->
        <!-- NOT: Eğer üstteki arama modülü header.php gibi başka bir dosyadan geliyorsa, -->
        <!-- bu butonun hedefi `#search-section` yerine doğrudan `rezervasyon.php` olabilir. -->
        <!-- Şimdilik bu butonu kaldırıyoruz, çünkü zaten üstte bir arama modülü var gibi görünüyor. -->
        <!-- <a href="#search-section" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-full text-lg transition duration-300 animate-fade-in-up delay-200">
            Hemen Rezervasyon Yap
        </a> -->
    </div>
</section>

<!-- Search Section (Ana sayfanın üst kısmındaki arama formu) -->
<!-- Bu blok tamamen kaldırılmıştır, çünkü görselde iki tane olduğu ve ikincisinin kaldırılması istendiği için. -->
<!-- Eğer bir arama modülü istiyorsanız ve bu modül başka bir dosyadan gelmiyorsa, bu bloğu geri getirmelisiniz. -->
<!-- <section id="search-section" class="bg-gray-800 py-8 md:py-12 -mt-20 relative z-20 shadow-xl rounded-t-lg">
    <div class="container mx-auto px-4">
        <form action="rezervasyon.php" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
            <div>
                <label for="checkin" class="block text-white text-sm font-semibold mb-2">Giriş Tarihi</label>
                <input type="date" id="checkin" name="checkin" class="w-full p-3 rounded-md bg-gray-700 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div>
                <label for="checkout" class="block text-white text-sm font-semibold mb-2">Çıkış Tarihi</label>
                <input type="date" id="checkout" name="checkout" class="w-full p-3 rounded-md bg-gray-700 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div>
                <label for="adults" class="block text-white text-sm font-semibold mb-2">Yetişkin</label>
                <select id="adults" name="adults" class="w-full p-3 rounded-md bg-gray-700 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <?php for ($i = 1; $i <= 10; $i++): ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div>
                <label for="children" class="block text-white text-sm font-semibold mb-2">Çocuk</label>
                <select id="children" name="children" class="w-full p-3 rounded-md bg-gray-700 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <?php for ($i = 0; $i <= 5; $i++): ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <button type="submit" class="w-full bg-yellow-500 hover:bg-yellow-600 text-gray-900 font-bold py-3 rounded-md transition duration-300">
                Oda Ara
            </button>
        </form>
    </div>
</section> -->

<!-- About Section -->
<section class="bg-white py-12 md:py-16">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-4xl font-bold text-gray-900 mb-6"><?php echo htmlspecialchars($homepage_settings['homepage_about_title'] ?? 'Otelimiz Hakkında'); ?></h2>
        <p class="text-lg text-gray-700 max-w-3xl mx-auto mb-8">
            <?php echo htmlspecialchars($homepage_settings['homepage_about_text'] ?? 'Şehrin merkezinde, tarihi dokuyla modern mimarinin eşsiz birleşimini sunan Grand Elysium, misafirlerine birinci sınıf hizmet ve unutulmaz bir konaklama deneyimi vaat ediyor.'); ?>
        </p>
        <a href="<?php echo htmlspecialchars($homepage_settings['homepage_about_button_link'] ?? BASE_URL . '/index.php?page=hakkimizda'); ?>" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-full text-lg transition duration-300">
            <?php echo htmlspecialchars($homepage_settings['homepage_about_button_text'] ?? 'Daha Fazlasını Keşfet'); ?>
        </a>
    </div>
</section>

<!-- Featured Rooms Section -->
<section class="bg-gray-100 py-12 md:py-16">
    <div class="container mx-auto px-4">
        <h2 class="text-4xl font-bold text-gray-900 text-center mb-10">Öne Çıkan Odalarımız</h2>
        
        <?php if (empty($featured_room_types)): ?>
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 p-4 rounded-md text-center">
                Şu an için öne çıkarılmış oda bulunmamaktadır.
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($featured_room_types as $room_type): ?>
                <div class="bg-white rounded-xl shadow-lg overflow-hidden transform hover:-translate-y-2 transition-transform duration-300 flex flex-col">
                    <img src="<?php echo BASE_URL . '/uploads/' . htmlspecialchars($room_type['featured_image'] ?: 'placeholder.png'); ?>" alt="<?php echo htmlspecialchars($room_type['name']); ?>" class="w-full h-60 object-cover">
                    <div class="p-6 flex flex-col flex-grow">
                        <h3 class="text-2xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($room_type['name']); ?></h3>
                        <p class="text-gray-700 mb-4 flex-grow"><?php echo htmlspecialchars($room_type['description']); ?></p>
                        <div class="border-t border-gray-200 pt-4 mt-auto">
                            <p class="text-xl font-bold text-blue-600 mb-2">
                                <?php echo htmlspecialchars(number_format($room_type['base_price'], 2)) . ' ' . htmlspecialchars($room_type['currency']); ?> / gecelik
                            </p>
                            <a href="rezervasyon_onay.php?oda_id=<?php echo $room_type['id']; ?>&checkin=<?php echo date('d.m.Y'); ?>&checkout=<?php echo date('d.m.Y', strtotime('+1 day')); ?>&adults=<?php echo htmlspecialchars($room_type['capacity']); ?>&children=0" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-5 rounded-lg transition duration-300">
                                Rezerve Et
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Services Section (from homepage_sections) -->
<section class="bg-white py-12 md:py-16">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-4xl font-bold text-gray-900 mb-10"><?php echo htmlspecialchars($homepage_settings['homepage_services_title'] ?? 'Öne Çıkan Hizmetlerimiz'); ?></h2>
        <!-- Bu kısım dinamik hizmetleri listeleyecek, şimdilik boş bırakıyoruz veya statik bir placeholder kullanıyoruz -->
        <p class="text-lg text-gray-700 max-w-3xl mx-auto">Hizmetlerimiz yakında burada listelenecektir.</p>
        <a href="<?php echo BASE_URL; ?>/index.php?page=hizmetler" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-full text-lg transition duration-300 mt-8 inline-block">
            Tüm Hizmetleri Gör
        </a>
    </div>
</section>

<!-- Tours Section (from homepage_sections) -->
<section class="bg-gray-100 py-12 md:py-16">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-4xl font-bold text-gray-900 mb-10">Turlarımız</h2>
        <!-- Bu kısım dinamik turları listeleyecek, şimdilik boş bırakıyoruz veya statik bir placeholder kullanıyoruz -->
        <p class="text-lg text-gray-700 max-w-3xl mx-auto">Turlarımız yakında burada listelenecektir.</p>
        <a href="<?php echo BASE_URL; ?>/index.php?page=turlar" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-full text-lg transition duration-300 mt-8 inline-block">
            Tüm Turları Gör
        </a>
    </div>
</section>