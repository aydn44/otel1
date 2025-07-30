<?php
// Bu dosya View sınıfı tarafından render edildiği için
// $title, $intro_content, $form_name, $form_email, $form_subject, $tour_id_param, $service_id_param vb. değişkenlere erişimi varsayıyoruz.
// Ayrıca get_country_codes() ve get_country_list() helper fonksiyonlarına da erişim olmalı.
// Yeni eklenenler: $whatsapp_number, $map_iframe_code, $contact_phone, $contact_email, $contact_address, $whatsapp_message, $debug_output
?>

<div class="container mx-auto px-4 py-8 md:py-12">
    <div class="bg-white p-6 md:p-8 rounded-lg shadow-md max-w-3xl mx-auto">
        <h1 class="text-3xl font-bold mb-6 text-gray-800 text-center"><?php echo htmlspecialchars($title); ?></h1>
        <div class="text-gray-600 text-center mb-8">
            <?php echo $intro_content; // Admin panelinden gelen giriş metni ?>
        </div>

        <?php // Hata mesajı kutusu kaldırıldı/gizlendi. Sadece loglara yazmaya devam edecek.
        /* if ($error_message): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md mb-6"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; */ ?>

        <?php if ($success_message): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-6 rounded-lg shadow-md text-center">
                <h2 class="text-2xl font-bold mb-2">Teşekkür Ederiz!</h2>
                <p><?php echo htmlspecialchars($success_message); ?></p>
                <a href="<?php echo BASE_URL; ?>/index.php" class="mt-4 inline-block bg-blue-600 text-white font-bold py-2 px-6 rounded hover:bg-blue-700">Ana Sayfaya Dön</a>
            </div>
        <?php else: // Başarı mesajı yoksa formu göster ?>
            <form method="POST" action="<?php echo BASE_URL; ?>/index.php?page=iletisim">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="name" class="block font-semibold text-gray-700 mb-1">Ad Soyad (*)</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($form_name); ?>" class="w-full p-3 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    <div>
                        <label for="email" class="block font-semibold text-gray-700 mb-1">E-posta Adresi (*)</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($form_email); ?>" class="w-full p-3 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    <div>
                        <label for="country_code" class="block font-semibold text-gray-700 mb-1">Ülke Kodu (*)</label>
                        <select id="country_code" name="country_code" class="w-full p-3 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
                            <?php foreach (get_country_codes() as $code => $name): ?>
                                <option value="<?php echo htmlspecialchars($code); ?>" <?php if($code == $form_country_code) echo 'selected'; ?>><?php echo htmlspecialchars($name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="phone" class="block font-semibold text-gray-700 mb-1">Telefon Numarası (*)</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($form_phone); ?>" class="w-full p-3 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                </div>

                <div class="mb-6">
                    <label for="subject" class="block font-semibold text-gray-700 mb-1">Konu (*)</label>
                    <input type="text" id="subject" name="subject" value="<?php echo htmlspecialchars($form_subject); ?>" class="w-full p-3 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
                    <?php if ($tour_id_param): ?>
                        <input type="hidden" name="tour_id" value="<?php echo htmlspecialchars($tour_id_param); ?>">
                        <p class="text-sm text-gray-500 mt-1">Tur ID: <?php echo htmlspecialchars($tour_id_param); ?> ile ilgili bilgi talep ediyorsunuz.</p>
                    <?php endif; ?>
                    <?php if ($service_id_param): ?>
                        <input type="hidden" name="service_id" value="<?php echo htmlspecialchars($service_id_param); ?>">
                        <p class="text-sm text-gray-500 mt-1">Hizmet ID: <?php echo htmlspecialchars($service_id_param); ?> ile ilgili bilgi talep ediyorsunuz.</p>
                    <?php endif; ?>
                </div>

                <div class="mb-6">
                    <label for="message" class="block font-semibold text-gray-700 mb-1">Mesajınız (*)</label>
                    <textarea id="message" name="message" rows="6" class="w-full p-3 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" required><?php echo htmlspecialchars($form_message); ?></textarea>
                </div>

                <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 rounded-lg hover:bg-blue-700 transition duration-300">
                    Bilgi Talebi Gönder
                </button>
            </form>
        <?php endif; // Başarı mesajı endif ?>

        <?php if (!empty($debug_output) && ini_get('display_errors')): // Debug çıktısı sadece display_errors açıksa gösterilir ?>
        <div class="mt-8 p-4 bg-gray-100 rounded-lg text-sm text-gray-700 overflow-x-auto" style="font-family: monospace;">
            <h3 class="font-bold text-gray-800 mb-2">PHPMailer Hata Ayıklama Çıktısı (Geçici)</h3>
            <pre><?php echo htmlspecialchars($debug_output); ?></pre>
        </div>
        <?php endif; ?>

        <div class="mt-8 text-center">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Hızlı İletişim</h2>
            <p class="text-gray-600 mb-4">Aşağıdaki butona tıklayarak WhatsApp üzerinden bizimle iletişime geçebilirsiniz.</p>
            <a href="https://wa.me/<?php echo htmlspecialchars(str_replace([' ', '+'], '', $whatsapp_number ?: '+905551234567')); ?>?text=<?php echo urlencode($whatsapp_message); ?>" 
               target="_blank" 
               class="inline-flex items-center bg-green-500 text-white font-bold py-3 px-6 rounded-lg hover:bg-green-600 transition duration-300 text-lg">
                <i class="fab fa-whatsapp mr-3"></i> WhatsApp ile İletişime Geç
            </a>
            </div>
    </div>
</div>

<?php if (!empty($map_iframe_code)): ?>
<div class="w-full mt-[-2rem] mb-12"> 
    <div class="container mx-auto text-center px-4">
         <h2 class="text-3xl font-bold text-center text-gray-800 mb-6">Konumumuz</h2>
    </div>
    <div class="w-full flex justify-center">
        <?php echo $map_iframe_code; ?>
    </div>
</div>
<?php endif; ?>