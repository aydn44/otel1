<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth_check.php';
// DÜZELTME: Doğru dosya olan EventRepository.php çağrılıyor.
require_once __DIR__ . '/../lib/EventRepository.php'; 
require_once __DIR__ . '/../helpers/media_upload.php';

$pdo = $GLOBALS['pdo'];
// DÜZELTME: Doğru nesne olan EventRepository oluşturuluyor.
$eventRepo = new App\Lib\EventRepository($pdo);

$action = $_POST['action'] ?? $_GET['action'] ?? null;
$eventId = $_POST['id'] ?? $_GET['id'] ?? null;

// Etkinlik oluşturma/güncelleme işlemleri
if (in_array($action, ['create', 'update']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $is_published = (int)($_POST['is_published'] ?? 0);
    $featured_image_name = $_POST['current_image'] ?? null;

    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = ROOT_PATH . '/uploads/';
        $new_image_name = upload_image($_FILES['featured_image'], $upload_dir);
        if ($new_image_name) {
            if ($action === 'update' && $featured_image_name) {
                @unlink($upload_dir . $featured_image_name);
            }
            $featured_image_name = $new_image_name;
        }
    }

    $eventData = [
        'title' => $title,
        'description' => $description,
        'featured_image' => $featured_image_name,
        'is_published' => $is_published
    ];

    if (empty($title)) {
        $_SESSION['error_message'] = "Başlık alanı zorunludur.";
    } else {
        if ($action === 'create') {
            if ($eventRepo->createEvent($eventData)) {
                $_SESSION['success_message'] = "Etkinlik başarıyla eklendi.";
            } else {
                $_SESSION['error_message'] = "Etkinlik eklenirken bir hata oluştu.";
            }
        } elseif ($action === 'update' && $eventId) {
            if ($eventRepo->updateEvent($eventId, $eventData)) {
                $_SESSION['success_message'] = "Etkinlik başarıyla güncellendi.";
            } else {
                $_SESSION['error_message'] = "Etkinlik güncellenirken bir hata oluştu.";
            }
        }
    }
    header('Location: events.php');
    exit;
}

// Etkinlik silme işlemi
if ($action === 'delete' && $eventId) {
    $event = $eventRepo->getEventById($eventId);
    if ($event && $eventRepo->deleteEvent($eventId)) {
        if (!empty($event['featured_image'])) {
            @unlink(ROOT_PATH . '/uploads/' . $event['featured_image']);
        }
        $_SESSION['success_message'] = "Etkinlik başarıyla silindi.";
    } else {
        $_SESSION['error_message'] = "Etkinlik silinirken bir hata oluştu.";
    }
    header('Location: events.php');
    exit;
}

header('Location: events.php');
exit;