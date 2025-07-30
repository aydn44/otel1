<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../lib/ServiceRepository.php'; // Yeni eklenen repository

$pdo = $GLOBALS['pdo'];
$serviceRepo = new App\Lib\ServiceRepository($pdo);

$service_id = $_GET['id'] ?? null;
$service = null;
$form_title = "Yeni Hizmet Ekle";

// Eğer bir ID varsa, mevcut hizmeti düzenliyoruz
if ($service_id) {
    $service = $serviceRepo->getServiceById($service_id);
    if (!$service) {
        $_SESSION['error_message'] = "Düzenlenecek hizmet bulunamadı.";
        header('Location: services.php');
        exit;
    }
    $form_title = "Hizmet Düzenle: " . htmlspecialchars($service['name']);
}

// Form gönderildiğinde olası hata/başarı mesajlarını kontrol et
$success_message = $_SESSION['success_message'] ?? null;
unset($_SESSION['success_message']);
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['error_message']);

// Form verileri (hata durumunda yeniden doldurmak için)
$name = $service['name'] ?? '';
$price = $service['price'] ?? '';
$icon_class = $service['icon_class'] ?? 'fas fa-star'; // Varsayılan ikon
$description_tr = $service['description_tr'] ?? ''; // services tablosundaki description_tr alanı

?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($form_title); ?></title>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>.sidebar-link.active { background-color: #1D4ED8; }</style>
    <!-- CKEditor için CDN -->
    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
</head>
<body class="bg-gray-200">
    <div class="flex min-h-screen">
        <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
        <div class="flex-1 p-10">
            <h1 class="text-3xl font-bold text-gray-800 mb-8"><?php echo htmlspecialchars($form_title); ?></h1>
            
            <?php if ($success_message): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <div class="bg-white p-6 rounded-lg shadow-lg">
                <form action="service_actions.php" method="POST">
                    <input type="hidden" name="action" value="<?php echo $service ? 'update' : 'create'; ?>">
                    <?php if ($service): ?>
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($service['id']); ?>">
                    <?php endif; ?>

                    <div class="mb-4">
                        <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Hizmet Adı:</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>

                    <div class="mb-4">
                        <label for="price" class="block text-gray-700 text-sm font-bold mb-2">Fiyat (TL):</label>
                        <input type="number" step="0.01" id="price" name="price" value="<?php echo htmlspecialchars($price); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>

                    <div class="mb-4">
                        <label for="icon_class" class="block text-gray-700 text-sm font-bold mb-2">İkon Sınıfı (Font Awesome):</label>
                        <input type="text" id="icon_class" name="icon_class" value="<?php echo htmlspecialchars($icon_class); ?>" placeholder="örn: fas fa-utensils" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <p class="text-xs text-gray-500 mt-1">Font Awesome ikon sınıflarını kullanın (örn: fas fa-utensils, fas fa-spa). <a href="https://fontawesome.com/icons" target="_blank" class="text-blue-600 hover:underline">İkonları buradan bulabilirsiniz.</a></p>
                    </div>

                    <div class="mb-4">
                        <label for="description_tr" class="block text-gray-700 text-sm font-bold mb-2">Açıklama (Türkçe):</label>
                        <textarea id="description_tr" name="description_tr" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"><?php echo htmlspecialchars($description_tr); ?></textarea>
                    </div>

                    <div class="flex items-center justify-between">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            <?php echo $service ? 'Hizmeti Güncelle' : 'Hizmet Ekle'; ?>
                        </button>
                        <a href="services.php" class="inline-block align-baseline font-bold text-sm text-gray-600 hover:text-gray-800">
                            İptal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        // CKEditor'ı başlat
        ClassicEditor
            .create( document.querySelector( '#description_tr' ) )
            .catch( error => {
                console.error( error );
            } );
    </script>
</body>
</html>