<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../lib/GalleryRepository.php';

$pdo = $GLOBALS['pdo'];
$galleryRepo = new App\Lib\GalleryRepository($pdo);

$image_id = $_GET['id'] ?? null;
$image = null;
$form_title = "Yeni Galeri Resmi Ekle";

if ($image_id) {
    $image = $galleryRepo->getGalleryImageById($image_id);
    if (!$image) { /* Hata yönetimi */ exit; }
    $form_title = "Resim Düzenle: " . htmlspecialchars($image['title']);
}

// YENİ EKLENDİ: Kategorileri veritabanından çekiyoruz.
$categories = $galleryRepo->getAllCategories();

$title = $image['title'] ?? '';
$image_path = $image['image_path'] ?? '';
$sort_order = $image['sort_order'] ?? 0;
$is_published = $image['is_published'] ?? 1;
$category_id = $image['category_id'] ?? null;
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($form_title); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-200">
    <div class="flex min-h-screen">
        <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
        <div class="flex-1 p-10">
            <h1 class="text-3xl font-bold text-gray-800 mb-8"><?php echo htmlspecialchars($form_title); ?></h1>
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <form action="gallery_actions.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="<?php echo $image ? 'update' : 'create'; ?>">
                    <?php if ($image): ?><input type="hidden" name="id" value="<?php echo htmlspecialchars($image['id']); ?>"><?php endif; ?>

                    <div class="mb-4">
                        <label for="title" class="block text-gray-700 text-sm font-bold mb-2">Resim Başlığı:</label>
                        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>

                    <div class="mb-4">
                        <label for="category_id" class="block text-gray-700 text-sm font-bold mb-2">Kategori:</label>
                        <select id="category_id" name="category_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            <option value="">-- Kategori Yok --</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo ($category_id == $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                         <p class="text-xs text-gray-500 mt-1">Kategorileri <a href="gallery_categories.php" class="text-blue-600 underline">buradan</a> yönetebilirsiniz.</p>
                    </div>

                    <div class="mb-4">
                        <label for="image_file" class="block text-gray-700 text-sm font-bold mb-2">Resim Dosyası:</label>
                        <input type="file" id="image_file" name="image_file" accept="image/*" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <?php if ($image_path): ?>
                            <p class="text-sm text-gray-600 mt-2">Mevcut Resim: <img src="<?php echo BASE_URL . '/uploads/' . htmlspecialchars($image_path); ?>" alt="Mevcut Resim" class="h-24 w-24 object-cover"></p>
                            <input type="hidden" name="current_image_path" value="<?php echo htmlspecialchars($image_path); ?>">
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-4">
                        <label for="sort_order" class="block text-gray-700 text-sm font-bold mb-2">Sıralama:</label>
                        <input type="number" id="sort_order" name="sort_order" value="<?php echo htmlspecialchars($sort_order); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                    </div>

                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Yayınla:</label>
                        <input type="hidden" name="is_published" value="0"> <input type="checkbox" id="is_published" name="is_published" value="1" class="form-checkbox" <?php echo ($is_published == 1) ? 'checked' : ''; ?>>
                    </div>

                    <div class="flex items-center justify-between">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Kaydet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>