<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../lib/RoomRepository.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_id = $_POST['room_id'] ?? null;
    $status = $_POST['status'] ?? null;
    $allowed_statuses = ['available', 'occupied', 'blocked', 'maintenance'];

    if ($room_id && $status && in_array($status, $allowed_statuses)) {
        try {
            $roomRepo = new App\Lib\RoomRepository($pdo);
            $success = $roomRepo->updateRoomStatus($room_id, $status);
            echo json_encode(['success' => $success]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Veritabanı hatası.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Geçersiz veri.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek metodu.']);
}
exit;
?>