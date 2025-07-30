<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kesin Sunucu Yükleme Testi</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-2xl mx-auto bg-white p-6 rounded-lg shadow-md">
        <h1 class="text-2xl font-bold mb-4">Kesin Sunucu Dosya Yükleme Testi</h1>
        <p class="mb-4 text-gray-700">Bu araç, sunucunuzun temel dosya yükleme yeteneğini, başka hiçbir dosyaya bağlı olmadan test eder. Sorunun kaynağını bulmak için bu kritik bir adımdır.</p>
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 p-4 mb-6">
            <p>Lütfen bir resim dosyası seçin ve "Yüklemeyi Test Et" butonuna basın. Karşınıza çıkacak sonuç sayfasının <strong>tamamının</strong> ekran görüntüsünü paylaşın.</p>
        </div>
        <form action="test_upload.php" method="post" enctype="multipart/form-data">
            <input type="file" name="file_to_test" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" required>
            <button type="submit" class="mt-4 w-full bg-indigo-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-indigo-700">Yüklemeyi Test Et</button>
        </form>
    </div>
</body>
</html>
<?php
    exit; // Form gösterildikten sonra script'i durdur.
}

// --- FORM GÖNDERİLDİKTEN SONRAKİ KISIM ---
// Sonuçları göstermek için HTML başlığı oluştur.
header('Content-Type: text/html; charset=utf-8');
echo '<!DOCTYPE html><html lang="tr"><head><title>Test Sonucu</title><style>body{font-family:-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; padding: 2em; background-color: #f8f9fa;} div{background-color:white; padding: 1.5em; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 1.5em;} h1{border-bottom: 2px solid #e2e8f0; padding-bottom: 0.5em; font-size: 1.875rem;} h2{font-size: 1.25rem; color: #1a202c; margin-bottom: 0.5rem;} p{line-height: 1.6; color: #4a5568;} code{background-color: #e2e8f0; padding: 0.2em 0.4em; border-radius: 4px; font-family: "Courier New", Courier, monospace;} .success{color: #2f855a; font-weight: bold;} .error{color: #c53030; font-weight: bold;}</style></head><body>';
echo '<h1>Yükleme Testi Sonuç Raporu</h1>';

// Adım 1: PHP.INI Ayarlarını Kontrol Et
echo '<div><h2>Adım 1: Sunucu Ayarları (php.ini)</h2>';
$file_uploads = ini_get('file_uploads');
if ($file_uploads) {
    echo '<p class="success">file_uploads direktifi: AÇIK</p>';
} else {
    echo '<p class="error">KRİTİK HATA: Sunucunuzda dosya yükleme özelliği (file_uploads) kapalı. Bu ayarın hosting panelinizden veya php.ini dosyasından açılması gerekiyor.</p></div></body></html>';
    exit;
}
echo '<p>upload_max_filesize: ' . ini_get('upload_max_filesize') . '</p>';
echo '<p>post_max_size: ' . ini_get('post_max_size') . '</p></div>';

// Adım 2: Dosyanın Sunucuya Ulaşıp Ulaşmadığını Kontrol Et
echo '<div><h2>Adım 2: Dosya Alımı</h2>';
if (!isset($_FILES['file_to_test']) || !is_uploaded_file($_FILES['file_to_test']['tmp_name'])) {
    echo '<p class="error">HATA: Sunucu dosyayı alamadı veya geçici klasöre kaydedemedi. Olası Sebep: Yüklenen dosya boyutu `post_max_size` limitini aşıyor olabilir.</p>';
    if(isset($_FILES['file_to_test']['error']) && $_FILES['file_to_test']['error'] != 0) {
        echo '<p>PHP Hata Kodu: ' . $_FILES['file_to_test']['error'] . '</p>';
    }
    echo '</div></body></html>';
    exit;
}
echo '<p class="success">BAŞARILI:</p> Sunucu dosyayı aldı ve geçici olarak kaydetti. Detaylar: <pre style="background-color:#e2e8f0; padding:10px; border-radius:5px;">' . htmlspecialchars(print_r($_FILES['file_to_test'], true)) . '</pre></div>';

// Adım 3: Hedef Klasörün Yolunu Hesapla ve Kontrol Et
echo '<div><h2>Adım 3: Hedef Klasör</h2>';
$project_root = dirname(__DIR__);
$upload_dir = $project_root . '/uploads/';
echo '<p>Hesaplanan Kök Dizin: <code>' . htmlspecialchars($project_root) . '</code></p>';
echo '<p>Hedef Klasör: <code>' . htmlspecialchars($upload_dir) . '</code></p>';
if (!is_dir($upload_dir)) {
    echo '<p class="error">HATA: `uploads` klasörü bulunamadı. Lütfen `admin` klasörüyle aynı dizinde `uploads` adında bir klasör oluşturun.</p></div></body></html>';
    exit;
}
echo '<p class="success">BAŞARILI:</p> `uploads` klasörü mevcut.<br>';
if (!is_writable($upload_dir)) {
    echo '<p class="error">KRİTİK HATA: `uploads` klasörüne yazma izni yok. Lütfen dosya yöneticisinden izinlerini (CHMOD) 755 veya 777 yapın.</p></div></body></html>';
    exit;
}
echo '<p class="success">BAŞARILI:</p> `uploads` klasörüne yazma izni var.</div>';

// Adım 4: Dosyayı Geçici Konumdan Hedefe Taşımayı Dene
echo '<div><h2>Adım 4: Dosya Taşıma</h2>';
$temp_path = $_FILES['file_to_test']['tmp_name'];
$destination_path = $upload_dir . basename($_FILES['file_to_test']['name']);
echo '<p>Kaynak (Geçici): <code>' . htmlspecialchars($temp_path) . '</code></p>';
echo '<p>Hedef: <code>' . htmlspecialchars($destination_path) . '</code></p>';
if (move_uploaded_file($temp_path, $destination_path)) {
    echo '<div style="margin-top:1em;padding:1em;background-color:#dcfce7;border:1px solid #16a34a;"><h2 style="font-size:1.5em;color:#15803d;">TEST BAŞARILI!</h2><p>Dosya başarıyla `uploads` klasörüne taşındı. Bu, sunucunuzun temel dosya yükleme işlevinin çalıştığı anlamına gelir. Sorun, önceki kodların bu temel işlevi kullanma biçimindeydi.</p></div>';
} else {
    echo '<div style="margin-top:1em;padding:1em;background-color:#fee2e2;border:1px solid #dc2626;"><h2 style="font-size:1.5em;color:#b91c1c;">TEST BAŞARISIZ!</h2><p><code>move_uploaded_file()</code> fonksiyonu başarısız oldu. Bu genellikle sunucunun `open_basedir` gibi güvenlik kısıtlamalarından kaynaklanır. Bu raporun tamamı, sorunu çözmek için kritik öneme sahiptir.</p></div>';
}
echo '</div></body></html>';

?>