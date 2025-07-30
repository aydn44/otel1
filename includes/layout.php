<?php
// Gerekli tüm ayarları $site_settings dizisinden alıp değişkenlere atıyoruz.
$site_name = $site_settings['site_name'] ?? 'Otel Adı';
$title = $data['title'] ?? 'Sayfa';

// Header Ayarları
$site_logo = $site_settings['site_logo'] ?? '';
$header_bg_color = $site_settings['header_bg_color'] ?? '#374151';
$header_transparent = !empty($site_settings['header_transparent']);
$header_style = $header_transparent ? 'background-color: transparent;' : 'background-color: ' . htmlspecialchars($header_bg_color) . ';';
$header_text_color_class = $header_transparent ? 'text-gray-800' : 'text-white';

// Rezervasyon Modülü Ayarları
$reservation_bar_active = !empty($site_settings['reservation_bar_active']);
$reservation_bar_bg_color = $site_settings['reservation_bar_bg_color'] ?? '#4a5568';
$reservation_bar_text_color = $site_settings['reservation_bar_text_color'] ?? '#ffffff';
$reservation_bar_transparent = !empty($site_settings['reservation_bar_transparent']);

// === GÜNCELLEME BURADA ===
// Şeffaf seçeneği işaretlendiğinde, daha garantili bir yöntem olan rgba(0,0,0,0) kullanıyoruz.
// Ayrıca stilin ezilmemesi için '!important' ekliyoruz.
$reservation_bar_style = $reservation_bar_transparent 
    ? 'background-color: rgba(0, 0, 0, 0) !important;' 
    : 'background-color: ' . htmlspecialchars($reservation_bar_bg_color) . ';';
// ==========================

// Footer Ayarları
$footer_bg_color = $site_settings['footer_bg_color'] ?? '#1e293b';
$footer_transparent = !empty($site_settings['footer_transparent']);
$footer_style = $footer_transparent ? 'background-color: transparent; color: #374151;' : 'background-color: ' . htmlspecialchars($footer_bg_color) . '; color: white;';
$contact_phone = $site_settings['contact_phone'] ?? '';
$contact_email = $site_settings['contact_email'] ?? '';
$contact_address = $site_settings['contact_address'] ?? '';
$social_facebook = $site_settings['social_facebook'] ?? '';
$social_instagram = $site_settings['social_instagram'] ?? '';
$social_twitter = $site_settings['social_twitter'] ?? '';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($title) . ' - ' . htmlspecialchars($site_name); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
    <style>.nav-link { text-transform: uppercase; font-weight: 600; letter-spacing: 0.05em; }</style>
</head>
<body class="bg-gray-100">
    <header class="p-4 shadow-lg sticky top-0 z-50 <?php echo $header_text_color_class;?>" style="<?php echo $header_style; ?>">
        <div class="container mx-auto flex justify-between items-center">
            <a href="<?php echo BASE_URL; ?>" class="text-xl font-bold flex items-center">
                <?php if ($site_logo): ?>
                    <img src="<?php echo BASE_URL . '/uploads/' . htmlspecialchars($site_logo); ?>" alt="<?php echo htmlspecialchars($site_name); ?> Logosu" class="h-12 mr-3">
                <?php else: ?>
                    <span><?php echo htmlspecialchars($site_name); ?></span>
                <?php endif; ?>
            </a>
            <nav class="space-x-4">
                <a href="<?php echo BASE_URL; ?>/index.php?page=anasayfa" class="hover:text-yellow-400 nav-link">Anasayfa</a>
                <?php foreach ($menu_pages as $page_link): ?>
                    <a href="<?php echo BASE_URL; ?>/index.php?page=<?php echo htmlspecialchars($page_link['slug']); ?>" class="hover:text-yellow-400 nav-link"><?php echo htmlspecialchars($page_link['title']); ?></a>
                <?php endforeach; ?>
            </nav>
        </div>
    </header>
    
    <?php if ($reservation_bar_active): ?>
    <div class="p-4 sticky top-[80px] z-40 shadow-md" style="<?php echo $reservation_bar_style; ?>">
        <div class="container mx-auto">
             <form action="<?php echo BASE_URL; ?>/rezervasyon.php" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                <div><label for="checkin" class="text-sm font-semibold block mb-1" style="color: <?php echo htmlspecialchars($reservation_bar_text_color); ?>;">Giriş Tarihi</label><input type="date" id="checkin" name="checkin" class="w-full p-2 rounded-md bg-gray-600 text-white border border-gray-500" required></div>
                <div><label for="checkout" class="text-sm font-semibold block mb-1" style="color: <?php echo htmlspecialchars($reservation_bar_text_color); ?>;">Çıkış Tarihi</label><input type="date" id="checkout" name="checkout" class="w-full p-2 rounded-md bg-gray-600 text-white border border-gray-500" required></div>
                <div><label for="adults" class="text-sm font-semibold block mb-1" style="color: <?php echo htmlspecialchars($reservation_bar_text_color); ?>;">Yetişkin</label><select id="adults" name="adults" class="w-full p-2 rounded-md bg-gray-600 text-white border border-gray-500"><option>1</option><option selected>2</option><option>3</option><option>4</option></select></div>
                <div><label for="children" class="text-sm font-semibold block mb-1" style="color: <?php echo htmlspecialchars($reservation_bar_text_color); ?>;">Çocuk</label><select id="children" name="children" class="w-full p-2 rounded-md bg-gray-600 text-white border border-gray-500"><option selected>0</option><option>1</option><option>2</option></select></div>
                <button type="submit" class="w-full bg-yellow-500 hover:bg-yellow-600 text-black font-bold p-2 rounded-md">Oda Ara</button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <main class="min-h-screen"><?php echo $content ?? ''; ?></main>

    <footer class="p-8" style="<?php echo $footer_style; ?>">
        <div class="container mx-auto grid grid-cols-1 md:grid-cols-3 gap-8 text-center md:text-left">
            <div class="space-y-2">
                <h3 class="text-lg font-bold mb-2">İletişim</h3>
                <p><strong>Telefon:</strong> <a href="tel:<?php echo htmlspecialchars($contact_phone); ?>" class="hover:underline"><?php echo htmlspecialchars($contact_phone); ?></a></p>
                <p><strong>E-posta:</strong> <a href="mailto:<?php echo htmlspecialchars($contact_email); ?>" class="hover:underline"><?php echo htmlspecialchars($contact_email); ?></a></p>
                <p><strong>Adres:</strong> <?php echo nl2br(htmlspecialchars($contact_address)); ?></p>
            </div>
            <div class="flex flex-col items-center">
                <h3 class="text-lg font-bold mb-2">Bizi Takip Edin</h3>
                <div class="flex space-x-4">
                    <?php if (!empty($social_facebook)): ?><a href="<?php echo htmlspecialchars($social_facebook); ?>" target="_blank" rel="noopener noreferrer" class="hover:opacity-75"><i class="fab fa-facebook-f fa-2x"></i></a><?php endif; ?>
                    <?php if (!empty($social_instagram)): ?><a href="<?php echo htmlspecialchars($social_instagram); ?>" target="_blank" rel="noopener noreferrer" class="hover:opacity-75"><i class="fab fa-instagram fa-2x"></i></a><?php endif; ?>
                    <?php if (!empty($social_twitter)): ?><a href="<?php echo htmlspecialchars($social_twitter); ?>" target="_blank" rel="noopener noreferrer" class="hover:opacity-75"><i class="fab fa-twitter fa-2x"></i></a><?php endif; ?>
                </div>
            </div>
            <div class="text-center md:text-right">
                <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($site_name); ?> | Tüm Hakları Saklıdır.</p>
                <p class="text-sm opacity-80 mt-2">
                    <a href="http://www.uzmnbilisim.org" target="_blank" class="hover:underline">aydın dıvarcı</a>
                </p>
            </div>
        </div>
    </footer>
</body>
</html>