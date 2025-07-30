<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../lib/GalleryRepository.php';
require_once __DIR__ . '/../helpers/media_upload.php';

$pdo = $GLOBALS['pdo'];
$galleryRepo = new App\Lib\GalleryRepository($pdo);

$action = $_POST['action'] ?? $_GET['action'] ?? null;
$imageId = $_POST['id'] ?? $_GET['id'] ?? null;

if (in_array($action, ['create', 'update']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // GÜNCELLENDİ: Formdan gelen tüm veriler alınıyor.
    $data = [
        'title' => trim($_POST['title'] ?? ''),
        'category_id' => !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null,
        'sort_order' => (int)($_POST['sort_order'] ?? 0),
        'is_published' => (int)($_POST['is_published'] ?? 0)
    ];

    // Resim Yükleme Mantığı
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = ROOT_PATH . '/uploads/';
        $new_image_name = upload_image($_FILES['image_file'], $upload_dir);
        if ($new_image_name) {
            $data['image_path'] = $new_image_name;
            // Güncelleme ise ve eski resim varsa, sunucudan sil
            if ($action === 'update' && !empty($_POST['current_image_path'])) {
                @unlink($upload_dir . $_POST['current_image_path']);
            }
        }
    }

    if ($action === 'create') {
        if (empty($data['image_path'])) {
            $_SESSION['error_message'] = "Yeni resim eklemek için bir dosya seçmelisiniz.";
            header('Location: gallery_form.php'); exit;
        }
        $galleryRepo->createGalleryImage($data);
        $_SESSION['success_message'] = "Galeri resmi başarıyla eklendi.";
    } 
    elseif ($action === 'update' && $imageId) {
        $galleryRepo->updateGalleryImage($imageId, $data);
        $_SESSION['success_message'] = "Galeri resmi başarıyla güncellendi.";
    }

    header('Location: gallery.php');
    exit;
}

if ($action === 'delete' && $imageId) {
    // Silme işlemi için mevcut kodunuz zaten iyi, olduğu gibi kalabilir.
    $image = $galleryRepo->getGalleryImageById($imageId);
    if ($image && $galleryRepo->deleteGalleryImage($imageId)) {
        if (!empty($image['image_path'])) {
            @unlink(ROOT_PATH . '/uploads/' . $image['image_path']);
        }
        $_SESSION['success_message'] = "Galeri resmi başarıyla silindi.";
    } else {
        $_SESSION['error_message'] = "Galeri resmi silinirken bir hata oluştu.";
    }
    header('Location: gallery.php');
    exit;
}