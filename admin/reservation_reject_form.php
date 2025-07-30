<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../lib/BookingRepository.php';

$bookingRepo = new App\Lib\BookingRepository($pdo);
$id = $_GET['id'] ?? null;
if(!$id) { header('Location: reservations.php'); exit; }

$booking = $bookingRepo->getBookingById($id);
if(!$booking || $booking['status'] !== 'pending') { 
    header('Location: reservations.php'); exit; 
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rejection_reason = trim($_POST['rejection_reason']);
    if (!empty($rejection_reason)) {
        $bookingRepo->rejectBooking($id, $rejection_reason);
        header('Location: reservations.php'); exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Rezervasyon Reddet</title><meta charset="UTF-8"><script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-200">
    <div class="flex min-h-screen">
        <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
        <div class="flex-1 p-10">
            <h1 class="text-3xl font-bold text-gray-800 mb-8">Rezervasyonu Reddet (#<?php echo htmlspecialchars($booking['id']); ?>)</h1>
            <div class="bg-white p-8 rounded-lg shadow-lg max-w-lg mx-auto">
                <p class="mb-4"><strong>Misafir:</strong> <?php echo htmlspecialchars($booking['guest_name']); ?></p>
                <form method="POST">
                    <div class="mb-4">
                        <label for="rejection_reason" class="block font-bold text-gray-700">Reddetme Sebebi:</label>
                        <textarea name="rejection_reason" rows="4" class="w-full p-2 border rounded mt-1" placeholder="Lütfen reddetme sebebini buraya yazın..." required></textarea>
                    </div>
                    <div class="flex justify-end space-x-4">
                        <a href="reservations.php" class="bg-gray-500 text-white font-bold py-2 px-4 rounded hover:bg-gray-600">İptal</a>
                        <button type="submit" class="bg-red-600 text-white font-bold py-2 px-4 rounded hover:bg-red-700">Rezervasyonu Reddet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>