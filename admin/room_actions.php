<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth_check.php'; // Yönetici yetkilendirme kontrolü için
require_once __DIR__ . '/../lib/RoomRepository.php'; //

$pdo = $GLOBALS['pdo'];
$roomRepo = new App\Lib\RoomRepository($pdo); //

$action = $_GET['action'] ?? $_POST['action'] ?? null;
$roomId = $_GET['id'] ?? $_POST['room_id'] ?? null;

// Oda ekleme işlemi
if ($action === 'add_physical_room' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $roomNumber = trim($_POST['room_number'] ?? '');
    $roomTypeId = (int)($_POST['room_type_id'] ?? 0);

    if (empty($roomNumber) || $roomTypeId <= 0) {
        $_SESSION['error_message'] = "Oda numarası ve oda tipi seçimi zorunludur.";
    } else {
        if ($roomRepo->addPhysicalRoom($roomNumber, $roomTypeId)) { //
            $_SESSION['success_message'] = "Fiziksel oda başarıyla eklendi.";
        } else {
            $_SESSION['error_message'] = "Fiziksel oda eklenirken bir hata oluştu.";
        }
    }
    header('Location: rooms.php'); // Oda Yönetimi sayfasına geri dön
    exit;
}

// Oda silme işlemi
if ($action === 'delete_physical_room' && $roomId) {
    if ($roomRepo->deletePhysicalRoom($roomId)) { //
        $_SESSION['success_message'] = "Fiziksel oda başarıyla silindi.";
    } else {
        $_SESSION['error_message'] = "Fiziksel oda silinirken bir hata oluştu. İlişkili rezervasyonlar olabilir.";
    }
    header('Location: rooms.php'); // Oda Yönetimi sayfasına geri dön
    exit;
}

// Oda düzenleme işlemi (POST isteği)
if ($action === 'update_physical_room' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $roomId = (int)($_POST['room_id'] ?? 0);
    $roomNumber = trim($_POST['room_number'] ?? '');
    $roomTypeId = (int)($_POST['room_type_id'] ?? 0);

    if ($roomId <= 0 || empty($roomNumber) || $roomTypeId <= 0) {
        $_SESSION['error_message'] = "Geçersiz oda bilgileri.";
    } else {
        if ($roomRepo->updatePhysicalRoom($roomId, $roomNumber, $roomTypeId)) { //
            $_SESSION['success_message'] = "Fiziksel oda başarıyla güncellendi.";
        } else {
            $_SESSION['error_message'] = "Fiziksel oda güncellenirken bir hata oluştu.";
        }
    }
    header('Location: rooms.php'); // Oda Yönetimi sayfasına geri dön
    exit;
}

// Geçersiz istek durumunda
header('Location: rooms.php');
exit;