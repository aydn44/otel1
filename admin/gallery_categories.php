<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../lib/GalleryRepository.php';

$pdo = $GLOBALS['pdo'];
$galleryRepo = new App\Lib\GalleryRepository($pdo);
$categories = $galleryRepo->getAllCategories();

// Durum mesajlarını kontrol et
$success_message = $_SESSION['success_message'] ?? null;
unset($_SESSION['success_message']);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <title>Galeri Kategorileri</title>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-100">
<div class="flex min-h-screen">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <div class="flex-1 p-10">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold">Galeri Kategorileri</h1>
            <a href="gallery_category_form.php" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg">+ Yeni Kategori Ekle</a>
        </div>

        <?php if ($success_message): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                <p><?php echo htmlspecialchars($success_message); ?></p>
            </div>
        <?php endif; ?>

        <div class="bg-white p-8 rounded-lg shadow-lg">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left p-4">Kategori Adı</th>
                        <th class="text-left p-4">Sıralama</th>
                        <th class="text-left p-4">Durum</th>
                        <th class="text-right p-4">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="4" class="text-center p-6 text-gray-500">Henüz hiç kategori oluşturulmamış.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($categories as $category): ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-4 font-semibold"><?php echo htmlspecialchars($category['name']); ?></td>
                            <td class="p-4"><?php echo htmlspecialchars($category['sort_order']); ?></td>
                            <td class="p-4"><?php echo $category['is_published'] ? '<span class="text-green-600 font-semibold">Yayınlandı</span>' : '<span class="text-red-600 font-semibold">Gizli</span>'; ?></td>
                            <td class="p-4 text-right">
                                <a href="gallery_category_form.php?id=<?php echo $category['id']; ?>" class="text-blue-600 hover:underline">Düzenle</a>
                                <a href="gallery_category_actions.php?action=delete&id=<?php echo $category['id']; ?>" onclick="return confirm('Bu kategoriyi silmek istediğinizden emin misiniz?')" class="text-red-600 hover:underline ml-4">Sil</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>