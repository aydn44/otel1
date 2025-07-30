<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../lib/BookingRepository.php';

$action = $_GET['action'] ?? null;
$id = $_GET['id'] ?? null;

if (!$action || !$id) {
    header('Location: reservations.php');
    exit;
}

$bookingRepo = new App\Lib\BookingRepository($pdo);

if ($action === 'confirm') {
    $bookingRepo->updateBookingStatus($id, 'confirmed');
} 

header('Location: reservations.php');
exit;
?>