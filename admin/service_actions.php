<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../lib/ServiceRepository.php'; // Yeni eklenen repository

$pdo = $GLOBALS['pdo'];
$serviceRepo = new App\Lib\ServiceRepository($pdo);

$action = $_POST['action'] ?? $_GET['action'] ?? null;
$serviceId = $_POST['id'] ?? $_GET['id'] ?? null;

// Hizmet oluşturma/güncelleme işlemleri
if (in_array($action, ['create', 'update']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $icon_class = trim($_POST['icon_class'] ?? 'fas fa-star');
    $description_tr = trim($_POST['description_tr'] ?? '');

    $serviceData = [
        'name' => $name,
        'price' => $price,
        'icon_class' => $icon_class,
        'description_tr' => $description_tr
    ];

    if (empty($name) || empty($description_tr)) {
        $_SESSION['error_message'] = "Hizmet adı ve açıklaması zorunludur.";
    } else {
        if ($action === 'create') {
            if ($serviceRepo->createService($serviceData)) {
                $_SESSION['success_message'] = "Hizmet başarıyla eklendi.";
            } else {
                $_SESSION['error_message'] = "Hizmet eklenirken bir hata oluştu.";
            }
        } elseif ($action === 'update' && $serviceId) {
            if ($serviceRepo->updateService($serviceId, $serviceData)) {
                $_SESSION['success_message'] = "Hizmet başarıyla güncellendi.";
            } else {
                $_SESSION['error_message'] = "Hizmet güncellenirken bir hata oluştu.";
            }
        }
    }
    header('Location: services.php');
    exit;
}

// Hizmet silme işlemi
if ($action === 'delete' && $serviceId && $_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($serviceRepo->deleteService($serviceId)) {
        $_SESSION['success_message'] = "Hizmet başarıyla silindi.";
    } else {
        $_SESSION['error_message'] = "Hizmet silinirken bir hata oluştu. İlişkili rezervasyonlar veya başka bağımlılıklar olabilir.";
    }
    header('Location: services.php');
    exit;
}

// Geçersiz istek durumunda
header('Location: services.php');
exit;