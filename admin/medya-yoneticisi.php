<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../helpers/security.php';

$upload_dir = ROOT_PATH . '/uploads/';
$upload_url = BASE_URL . '/uploads/';
$message = '';
$message_type = 'success';
$uploaded_file_url = '';

// Dosya Yükleme İşlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['media_file'])) {
    if ($_FILES['media_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['media_file'];
        $safe_filename = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '', basename($file['name']));
        $destination = $upload_dir . $safe_filename;
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            $message = "Resim başarıyla yüklendi!";
            $uploaded_file_url = $upload_url . $safe_filename;
        } else {
            $message = "Dosya taşınırken bir hata oluştu.";
            $message_type = 'error';
        }
    } else {
        $message = "Yükleme sırasında bir hata oluştu: Hata kodu " . $_FILES['media_file']['error'];
        $message_type = 'error';
    }
}

// Yüklenmiş dosyaları tarihe göre en yeniden eskiye doğru sırala
$files = [];
if (is_dir($upload_dir)) {
    $file_list = array_diff(scandir($upload_dir, SCANDIR_SORT_DESCENDING), ['.', '..']);
    foreach ($file_list as $file) {
        if (is_file($upload_dir . $file)) {
            $files[] = $file;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Medya Kütüphanesi</title><meta charset="UTF-8"><script src="https://cdn.tailwindcss.com"></script><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>body { background-color: #f9fafb; }</style>
</head>
<body class="p-4 md:p-8">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Medya Kütüphanesi</h1>
        
        <?php if ($message): ?>
            <div class="p-4 mb-6 rounded-lg <?php echo $message_type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 class="text-xl font-semibold mb-4">Yeni Medya Yükle</h2>
            <form action="medya-yoneticisi.php" method="post" enctype="multipart/form-data">
                <input type="file" name="media_file" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" required>
                <button type="submit" class="mt-4 bg-indigo-600 text-white font-bold py-2 px-5 rounded-lg hover:bg-indigo-700">Yükle</button>
            </form>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold mb-4">Yüklenmiş Dosyalar</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                <?php if (empty($files)): ?>
                    <p class="text-gray-500 col-span-full">Henüz hiç dosya yüklenmemiş.</p>
                <?php else: ?>
                    <?php foreach ($files as $file): ?>
                    <div class="border rounded-lg p-3 flex flex-col justify-between">
                        <img src="<?php echo $upload_url . htmlspecialchars($file); ?>" class="w-full h-24 object-cover rounded-md mb-2">
                        <div>
                            <input type="text" value="<?php echo $upload_url . htmlspecialchars($file); ?>" readonly onclick="this.select(); document.execCommand('copy');" class="w-full text-xs bg-gray-100 p-1 rounded text-center select-all cursor-pointer mb-2" title="Kopyalamak için tıkla">
                            <div class="flex justify-between items-center">
                                <button id="select-btn-<?php echo pathinfo($file, PATHINFO_FILENAME); ?>" onclick="selectImage('<?php echo $upload_url . htmlspecialchars($file); ?>')" class="hidden bg-blue-500 text-white font-bold py-1 px-2 rounded text-xs hover:bg-blue-600">Seç</button>
                                <form action="delete_media.php" method="POST" onsubmit="return confirm('Bu resmi kalıcı olarak silmek istediğinizden emin misiniz?');" class="inline-block">
                                    <input type="hidden" name="token" value="<?php echo generate_csrf_token(); ?>">
                                    <input type="hidden" name="filename" value="<?php echo htmlspecialchars($file); ?>">
                                    <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-semibold">Sil</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function selectImage(url) {
            if (window.opener && window.opener.insertImage) {
                window.opener.insertImage(url);
                window.close();
            } else {
                alert("Bu resmi seçecek bir editör penceresi bulunamadı.");
            }
        }
        
        if (window.opener) {
            document.querySelectorAll('[id^="select-btn-"]').forEach(button => {
                button.classList.remove('hidden');
            });
        }
        
        <?php if (!empty($uploaded_file_url)): ?>
            alert("Resim yüklendi. Şimdi bu resmi editöre eklemek için 'Seç' butonuna tıklayabilirsiniz veya linki kopyalayabilirsiniz.");
        <?php endif; ?>
    </script>
</body>
</html>