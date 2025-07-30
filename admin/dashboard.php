<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../lib/BookingRepository.php';
require_once __DIR__ . '/../lib/RoomRepository.php';
require_once __DIR__ . '/../lib/PageRepository.php';

$pdo = $GLOBALS['pdo'];
$bookingRepo = new App\Lib\BookingRepository($pdo);
$roomRepo = new App\Lib\RoomRepository($pdo);
$pageRepo = new App\Lib\PageRepository($pdo);

// Dashboard istatistiklerini BookingRepository'den çek
$stats = $bookingRepo->getDashboardStats();

// RoomRepository'den toplam ve dolu oda sayılarını çekerek doluluk oranını hesapla
$total_rooms = $roomRepo->getTotalRoomsCount();
$occupied_rooms = $roomRepo->getOccupiedRoomsCount();

$occupancy_rate = 0; // Varsayılan değer
if ($total_rooms > 0) {
    $occupancy_rate = round(($occupied_rooms / $total_rooms) * 100);
}

// Menü sayfalarını çek (admin_sidebar.php içinde kullanılıyor olabilir)
$menu_pages = $pageRepo->getMenuPages();

// Oturum başlatılmışsa kullanıcı adını alalım.
// auth_check.php zaten session_start() yapmış ve user_name'i set etmiş olmalı.
$userName = $_SESSION['user_name'] ?? 'Yönetici';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Kontrol Paneli</title><meta charset="UTF-8"><script src="https://cdn.tailwindcss.com"></script><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>.sidebar-link.active { background-color: #1D4ED8; }</style>
</head>
<body class="bg-gray-200">
    <div class="flex min-h-screen">
        <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
        <div class="flex-1 p-10">
            <header class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800">Hoş geldiniz, <?php echo htmlspecialchars($userName); ?>!</h1>
                <p class="text-gray-600">Sitenizin genel durumuna buradan göz atabilirsiniz.</p>
            </header>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <h3>Aktif Rezervasyon</h3>
                    <p class="text-3xl font-bold mt-2"><?php echo $stats['total_bookings'] ?? 0; ?></p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <h3>Bugünkü Girişler</h3>
                    <p class="text-3xl font-bold mt-2"><?php echo $stats['today_checkins'] ?? 0; ?></p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <h3>Anlık Doluluk Oranı</h3>
                    <p class="text-3xl font-bold mt-2"><?php echo $occupancy_rate; ?>%</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <h3>Onay Bekleyen</h3>
                    <p class="text-3xl font-bold mt-2"><?php echo $stats['pending_bookings'] ?? 0; ?></p>
                </div>
            </div>
            </div>
    </div>
</body>
</html>