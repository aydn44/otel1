<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../lib/GalleryRepository.php';

$pdo = $GLOBALS['pdo'];
$galleryRepo = new App\Lib\GalleryRepository($pdo);
$action = $_POST['action'] ?? $_GET['action'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'create') {
    $galleryRepo->createCategory(['name' => $_POST['name'], 'sort_order' => $_POST['sort_order'], 'is_published' => isset($_POST['is_published']) ? 1 : 0]);
    $_SESSION['success_message'] = "Kategori başarıyla oluşturuldu.";
    header('Location: gallery_categories.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update') {
    $galleryRepo->updateCategory($_POST['id'], ['name' => $_POST['name'], 'sort_order' => $_POST['sort_order'], 'is_published' => isset($_POST['is_published']) ? 1 : 0]);
    $_SESSION['success_message'] = "Kategori başarıyla güncellendi.";
    header('Location: gallery_categories.php');
    exit;
}

if ($action === 'delete') {
    $galleryRepo->deleteCategory($_GET['id']);
    $_SESSION['success_message'] = "Kategori başarıyla silindi.";
    header('Location: gallery_categories.php');
    exit;
}