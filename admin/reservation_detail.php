<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../lib/BookingRepository.php';
require_once __DIR__ . '/../lib/PageRepository.php'; 


$pdo = $GLOBALS['pdo'];
$bookingRepo = new App\Lib\BookingRepository($pdo);
$pageRepo = new App\Lib\PageRepository($pdo);


$bookingId = $_GET['id'] ?? null;

if (!$bookingId) {
    header('Location: reservations.php');
    exit;
}

$booking = $bookingRepo->getBookingById($bookingId);


if (!$booking) {
    $_SESSION['error_message'] = "Rezervasyon bulunamadı.";
    header('Location: reservations.php');
    exit;
}

$menu_pages = $pageRepo->getMenuPages();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Rezervasyon Detayı #<?php echo htmlspecialchars($booking['id']); ?></title>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>.sidebar-link.active { background-color: #1D4ED8; }</style>
</head>
<body class="bg-gray-200">
    <div class="flex min-h-screen">
        <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
        <div class="flex-1 p-10">
            <a href="reservations.php" class="text-blue-600 hover:underline mb-4 inline-block">&larr; Tüm Rezervasyonlara Geri Dön</a>
            <h1 class="text-3xl font-bold text-gray-800 mb-8">Rezervasyon Detayı #<?php echo htmlspecialchars($booking['id']); ?></h1>
            
            <div class="bg-white p-6 rounded-lg shadow-lg grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <h2 class="text-xl font-semibold mb-4 border-b pb-2">Misafir Bilgileri</h2>
                    <p class="mb-2"><strong>Ad Soyad:</strong> <?php echo htmlspecialchars($booking['guest_name']); ?></p>
                    <p class="mb-2"><strong>E-posta:</strong> <?php echo htmlspecialchars($booking['guest_email']); ?></p>
                    <p class="mb-2"><strong>Telefon:</strong> <?php echo htmlspecialchars($booking['guest_phone']); ?></p>
                    <p class="mb-2"><strong>Uyruk:</strong> <?php echo htmlspecialchars($booking['guest_nationality']); ?></p>
                    <p class="mb-2"><strong>Ülke Kodu:</strong> <?php echo htmlspecialchars($booking['country_code']); ?></p>
                </div>
                <div>
                    <h2 class="text-xl font-semibold mb-4 border-b pb-2">Rezervasyon Bilgileri</h2>
                    <p class="mb-2"><strong>Giriş Tarihi:</strong> <?php echo date('d M Y', strtotime($booking['check_in_date'])); ?></p>
                    <p class="mb-2"><strong>Çıkış Tarihi:</strong> <?php echo date('d M Y', strtotime($booking['check_out_date'])); ?></p>
                    <p class="mb-2"><strong>Yetişkin Sayısı:</strong> <?php echo htmlspecialchars($booking['num_adults']); ?></p>
                    <p class="mb-2"><strong>Çocuk Sayısı:</strong> <?php echo htmlspecialchars($booking['num_children']); ?></p>
                    <p class="mb-2"><strong>Toplam Tutar:</strong> <?php echo number_format($booking['total_price'], 2) . ' ' . htmlspecialchars($booking['currency']); ?></p>
                    <p class="mb-2"><strong>Durum:</strong> 
                        <?php 
                        $status_class = '';
                        $status_text = '';
                        switch ($booking['status']) {
                            case 'pending': $status_class = 'bg-yellow-100 text-yellow-800'; $status_text = 'Beklemede'; break;
                            case 'confirmed': $status_class = 'bg-green-100 text-green-800'; $status_text = 'Onaylandı'; break;
                            case 'cancelled': $status_class = 'bg-red-100 text-red-800'; $status_text = 'İptal Edildi'; break;
                            case 'rejected': $status_class = 'bg-gray-100 text-gray-800'; $status_text = 'Reddedildi'; break;
                            case 'completed': $status_class = 'bg-blue-100 text-blue-800'; $status_text = 'Tamamlandı'; break;
                        }
                        echo '<span class="px-2 py-1 font-semibold leading-tight text-sm rounded-full ' . $status_class . '">' . htmlspecialchars($status_text) . '</span>';
                        ?>
                    </p>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-lg mt-8">
                <h2 class="text-xl font-semibold mb-4 border-b pb-2">Transfer Bilgileri</h2>
                <p class="mb-2"><strong>Transfer İsteği:</strong> 
                    <?php echo ($booking['transfer_service'] == 1) ? 'EVET' : 'HAYIR'; ?>
                </p>
                <?php if ($booking['transfer_service'] == 1 && !empty($booking['transfer_details'])): ?>
                    <p class="mb-2"><strong>Transfer Detayları:</strong> <?php echo htmlspecialchars($booking['transfer_details']); ?></p>
                <?php endif; ?>
            </div>

            <?php if (!empty($booking['notes'])): ?>
            <div class="bg-white p-6 rounded-lg shadow-lg mt-8">
                <h2 class="text-xl font-semibold mb-4 border-b pb-2">Misafir Notları</h2>
                <p><?php echo nl2br(htmlspecialchars($booking['notes'])); ?></p>
            </div>
            <?php endif; ?>

        </div>
    </div>
</body>
</html>