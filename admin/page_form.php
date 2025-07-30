<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../lib/PageRepository.php';
require_once __DIR__ . '/../helpers/security.php'; // CSRF token için

$pdo = $GLOBALS['pdo'];
$pageRepo = new App\Lib\PageRepository($pdo);

$page_id = $_GET['id'] ?? null;
$page = null; // Sayfa verisi için değişken
$form_title = "Yeni Sayfa Ekle"; // Form başlığı varsayılanı

// Mevcut bir sayfayı düzenliyorsak
if ($page_id) {
    $page = $pageRepo->getPageById($page_id);
    if (!$page) {
        $_SESSION['error_message'] = "Düzenlenecek sayfa bulunamadı.";
        header('Location: page-management.php');
        exit;
    }
    $form_title = "Sayfa Düzenle: " . htmlspecialchars($page['title'] ?? '');
}

// Form alanları için mevcut veya varsayılan değerler
$slug = $page['slug'] ?? '';
$title = $page['title'] ?? '';
$content = $page['content'] ?? '';
$sort_order = $page['sort_order'] ?? 0;
$is_published = $page['is_published'] ?? 1; // Yeni sayfa varsayılan olarak yayınlanır
$background_type = $page['background_type'] ?? 'none';
$background_value = $page['background_value'] ?? '';

// Durum mesajları (page_actions.php'den gelebilir)
$success_message = $_SESSION['success_message'] ?? null;
unset($_SESSION['success_message']);
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['error_message']);

// CSRF token oluştur
$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <title><?php echo htmlspecialchars($form_title); ?></title>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"></script>
    <style>.sidebar-link.active { background-color: #1D4ED8; }</style>
    <script src="https://cdn.tiny.cloud/1/t5e40i7gwkr3z6ote8g03ab2iwjtwyjfnz2zxmao89pips2e/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
</head>
<body class="bg-gray-100">
<div class="flex min-h-screen">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <div class="flex-1 p-10">
        <h1 class="text-3xl font-bold mb-8"><?php echo $form_title; ?></h1>

        <?php if ($success_message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <form action="page_actions.php" method="POST">
            <input type="hidden" name="action" value="<?php echo $page_id ? 'update_content' : 'create_page_via_form'; ?>">
            <?php if ($page_id): ?>
                <input type="hidden" name="page_id" value="<?php echo htmlspecialchars($page_id); ?>">
            <?php endif; ?>
            <input type="hidden" name="token" value="<?php echo $csrf_token; ?>"> <div class="bg-white p-8 rounded-lg shadow-lg">
                <div class="mb-4">
                    <label for="title" class="block text-gray-700 text-sm font-bold mb-2">Sayfa Başlığı:</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required>
                </div>
                <div class="mb-4">
                    <label for="slug" class="block text-gray-700 text-sm font-bold mb-2">Sayfa URL Adresi (Slug):</label>
                    <input type="text" id="slug" name="slug" value="<?php echo htmlspecialchars($slug); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required>
                    <p class="text-xs text-gray-500 mt-1">Sadece küçük harf, rakam ve tire (-) kullanın. Örn: "hakkimizda", "iletisim".</p>
                </div>
                <div class="mb-4">
                    <label for="sort_order" class="block text-gray-700 text-sm font-bold mb-2">Sıralama:</label>
                    <input type="number" id="sort_order" name="sort_order" value="<?php echo htmlspecialchars($sort_order); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                    <p class="text-xs text-gray-500 mt-1">Menüdeki ve sayfa listesindeki sıralamayı belirler.</p>
                </div>
                <div class="mb-4">
                    <label for="is_published" class="block text-gray-700 text-sm font-bold mb-2">Yayınla:</label>
                    <select id="is_published" name="is_published" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                        <option value="1" <?php echo ($is_published == 1) ? 'selected' : ''; ?>>Evet</option>
                        <option value="0" <?php echo ($is_published == 0) ? 'selected' : ''; ?>>Hayır</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="background_type" class="block text-gray-700 text-sm font-bold mb-2">Arka Plan Tipi:</label>
                    <select id="background_type" name="background_type" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                        <option value="none" <?php echo ($background_type == 'none') ? 'selected' : ''; ?>>Yok</option>
                        <option value="color" <?php echo ($background_type == 'color') ? 'selected' : ''; ?>>Renk</option>
                        <option value="image" <?php echo ($background_type == 'image') ? 'selected' : ''; ?>>Resim</option>
                    </select>
                </div>
                <div class="mb-4" id="background_value_container" style="display: <?php echo ($background_type == 'none') ? 'none' : 'block'; ?>;">
                    <label for="background_value" class="block text-gray-700 text-sm font-bold mb-2" id="background_value_label">Arka Plan Değeri:</label>
                    <input type="<?php echo ($background_type == 'color') ? 'color' : 'text'; ?>" id="background_value" name="background_value" value="<?php echo htmlspecialchars($background_value); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                    <?php if ($background_type == 'image'): ?>
                        <p class="text-xs text-gray-500 mt-1">Resim URL'si girin veya Medya Kütüphanesinden seçin. Örn: `/uploads/resim.jpg`</p>
                        <button type="button" onclick="openMediaLibrary('background_value')" class="mt-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-1 px-3 rounded text-sm">Medya Kütüphanesi</button>
                    <?php endif; ?>
                </div>

                <label for="icerik-editoru" class="block text-xl font-semibold mb-4 text-gray-700">Sayfa İçeriği</label>
                <textarea id="icerik-editoru" name="content" style="height: 600px;">
                    <?php echo htmlspecialchars($content); ?>
                </textarea>
            </div>
            
            <div class="mt-8">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg text-lg">
                    <?php echo $page_id ? 'Sayfayı Güncelle' : 'Sayfa Ekle'; ?>
                </button>
                <a href="page-management.php" class="ml-4 text-gray-600 hover:text-gray-800 font-bold">İptal</a>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const backgroundTypeSelect = document.getElementById('background_type');
        const backgroundValueContainer = document.getElementById('background_value_container');
        const backgroundValueInput = document.getElementById('background_value');
        const backgroundValueLabel = document.getElementById('background_value_label');

        function updateBackgroundInput() {
            const selectedType = backgroundTypeSelect.value;
            if (selectedType === 'none') {
                backgroundValueContainer.style.display = 'none';
            } else {
                backgroundValueContainer.style.display = 'block';
                if (selectedType === 'color') {
                    backgroundValueInput.type = 'color';
                    backgroundValueLabel.textContent = 'Arka Plan Rengi:';
                } else if (selectedType === 'image') {
                    backgroundValueInput.type = 'text';
                    backgroundValueLabel.textContent = 'Arka Plan Resim URL\'si:';
                }
            }
        }

        backgroundTypeSelect.addEventListener('change', updateBackgroundInput);
        updateBackgroundInput(); // Sayfa yüklendiğinde başlangıç durumunu ayarla

        // TinyMCE editörünü başlatıyoruz
        tinymce.init({
            selector: 'textarea#icerik-editoru',
            plugins: 'preview importcss searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media template codesample table charmap pagebreak nonbreaking anchor insertdatetime advlist lists wordcount help charmap quickbars emoticons',
            toolbar: 'undo redo | bold italic underline strikethrough | fontfamily fontsize blocks | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | fullscreen  preview save print | insertfile image media template link anchor codesample | ltr rtl',
            height: 600,
            image_title: true,
            automatic_uploads: true,
            file_picker_types: 'image',
            file_picker_callback: (cb, value, meta) => {
                const input = document.createElement('input');
                input.setAttribute('type', 'file');
                input.setAttribute('accept', 'image/*');
                input.addEventListener('change', (e) => {
                    const file = e.target.files[0];
                    const reader = new FileReader();
                    reader.addEventListener('load', () => {
                        const id = 'blobid' + (new Date()).getTime();
                        const blobCache = tinymce.activeEditor.editorUpload.blobCache;
                        const base64 = reader.result.split(',')[1];
                        const blobInfo = blobCache.create(id, file, base64);
                        blobCache.add(blobInfo);
                        cb(blobInfo.blobUri(), { title: file.name });
                    });
                    reader.readAsDataURL(file);
                });
                input.click();
            },
        });
    });

    // Medya Kütüphanesi entegrasyonu için fonksiyon
    function openMediaLibrary(targetInputId) {
        // Medya kütüphanesini yeni bir pencerede aç
        const mediaWindow = window.open('medya-yoneticisi.php', 'MediaLibrary', 'width=900,height=600');

        // Medya kütüphanesi penceresinden bir resim seçildiğinde çağrılacak fonksiyon
        window.insertImage = function(imageUrl) {
            const targetInput = document.getElementById(targetInputId);
            if (targetInput) {
                targetInput.value = imageUrl;
            }
            mediaWindow.close();
        };
    }
</script>

</body>
</html>