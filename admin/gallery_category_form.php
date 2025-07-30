<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../lib/GalleryRepository.php';

$pdo = $GLOBALS['pdo'];
$galleryRepo = new App\Lib\GalleryRepository($pdo);
$category_id = $_GET['id'] ?? null;
$category = null;
$form_title = "Yeni Kategori Ekle";

if ($category_id) {
    $category = $galleryRepo->getCategoryById($category_id);
    $form_title = "Kategoriyi Düzenle";
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <title><?php echo $form_title; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<div class="flex min-h-screen">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <div class="flex-1 p-10">
        <h1 class="text-3xl font-bold mb-8"><?php echo $form_title; ?></h1>
        <div class="bg-white p-8 rounded-lg shadow-lg">
            <form action="gallery_category_actions.php" method="POST">
                <input type="hidden" name="action" value="<?php echo $category_id ? 'update' : 'create'; ?>">
                <?php if ($category_id): ?><input type="hidden" name="id" value="<?php echo $category['id']; ?>"><?php endif; ?>

                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2" for="name">Kategori Adı</label>
                    <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($category['name'] ?? ''); ?>" class="w-full p-2 border rounded-md" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2" for="sort_order">Sıralama</label>
                    <input type="number" name="sort_order" id="sort_order" value="<?php echo htmlspecialchars($category['sort_order'] ?? '0'); ?>" class="w-full p-2 border rounded-md">
                </div>
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_published" value="1" <?php echo (isset($category['is_published']) && $category['is_published']) || !$category_id ? 'checked' : ''; ?> class="form-checkbox h-5 w-5 text-blue-600">
                        <span class="ml-2 text-gray-700">Yayınlandı mı?</span>
                    </label>
                </div>
                <div class="mt-8">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>