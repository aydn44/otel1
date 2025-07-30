<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../lib/BookingRepository.php';

$bookingRepo = new App\Lib\BookingRepository($pdo);

$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if ($page < 1) $page = 1;
$limit = 15;
$offset = ($page - 1) * $limit;

$filters = [
    'start_date' => $_GET['start_date'] ?? '',
    'end_date' => $_GET['end_date'] ?? '',
    'guest_name' => $_GET['guest_name'] ?? ''
];

$reservations = $bookingRepo->getFilteredBookings($filters, $limit, $offset);
$total_reservations = $bookingRepo->getFilteredBookingsCount($filters);
$total_pages = ceil($total_reservations / $limit);

$status_map = [
    'confirmed' => ['text' => 'Onaylandı', 'color' => 'bg-green-100 text-green-800'],
    'pending' => ['text' => 'Beklemede', 'color' => 'bg-yellow-100 text-yellow-800'],
    'rejected' => ['text' => 'Reddedildi', 'color' => 'bg-red-100 text-red-800'],
    'cancelled' => ['text' => 'İptal Edildi', 'color' => 'bg-gray-100 text-gray-800'],
    'completed' => ['text' => 'Tamamlandı', 'color' => 'bg-blue-100 text-blue-800'],
];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Rezervasyon Yönetimi</title><meta charset="UTF-8"><script src="https://cdn.tailwindcss.com"></script><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>.sidebar-link.active { background-color: #1D4ED8; }</style>
</head>
<body class="bg-gray-200">
    <div class="flex min-h-screen">
        <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
        <div class="flex-1 p-10">
            <h1 class="text-3xl font-bold text-gray-800 mb-8">Rezervasyon Raporları ve Yönetimi</h1>
            
            <div class="bg-white p-6 rounded-lg shadow-lg mb-8">
                <h2 class="text-xl font-semibold mb-4">Sorgulama ve Filtreleme</h2>
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div><label for="start_date" class="block text-sm font-medium">Başlangıç Tarihi</label><input type="date" name="start_date" id="start_date" value="<?php echo htmlspecialchars($filters['start_date']); ?>" class="mt-1 block w-full p-2 border rounded-md"></div>
                    <div><label for="end_date" class="block text-sm font-medium">Bitiş Tarihi</label><input type="date" name="end_date" id="end_date" value="<?php echo htmlspecialchars($filters['end_date']); ?>" class="mt-1 block w-full p-2 border rounded-md"></div>
                    <div><label for="guest_name" class="block text-sm font-medium">Misafir Adı</label><input type="text" name="guest_name" id="guest_name" value="<?php echo htmlspecialchars($filters['guest_name']); ?>" placeholder="Misafir adını yazın..." class="mt-1 block w-full p-2 border rounded-md"></div>
                    <div class="flex space-x-2"><button type="submit" class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded-md hover:bg-blue-700">Filtrele</button><a href="reservations.php" class="w-full text-center bg-gray-500 text-white font-bold py-2 px-4 rounded-md hover:bg-gray-600">Temizle</a></div>
                </form>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-lg overflow-x-auto">
                <table class="w-full text-left">
                    <thead><tr class="border-b bg-gray-50"><th class="p-4">Misafir Adı</th><th class="p-4">Tarihler</th><th class="p-4">Tutar</th><th class="p-4">Durum</th><th class="p-4 text-right">İşlemler</th></tr></thead>
                    <tbody>
                        <?php if(empty($reservations)): ?>
                            <tr><td colspan="5" class="p-4 text-center text-gray-500">Bu kriterlere uygun rezervasyon bulunamadı.</td></tr>
                        <?php else: ?>
                            <?php foreach ($reservations as $res): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="p-4 font-semibold"><?php echo htmlspecialchars($res['guest_name']); ?></td>
                                <td class="p-4 text-gray-600"><?php echo date('d/m/y', strtotime($res['check_in_date'])) . ' - ' . date('d/m/y', strtotime($res['check_out_date'])); ?></td>
                                <td class="p-4 font-semibold">
                                    <?php echo number_format($res['total_price'], 2) . ' ' . htmlspecialchars($res['currency']); ?>
                                </td>
                                <td class="p-4"><span class="px-2 py-1 font-semibold leading-tight text-sm rounded-full <?php echo $status_map[$res['status']]['color'] ?? 'bg-gray-100 text-gray-800'; ?>"><?php echo $status_map[$res['status']]['text'] ?? 'Bilinmiyor'; ?></span></td>
                                <td class="p-4 text-right space-x-4">
                                    <?php if($res['status'] == 'pending'): ?>
                                        <a href="reservation_actions.php?action=confirm&id=<?php echo $res['id']; ?>" class="font-semibold text-green-600 hover:underline">Onayla</a>
                                        <a href="reservation_reject_form.php?id=<?php echo $res['id']; ?>" class="font-semibold text-red-600 hover:underline">Reddet</a>
                                    <?php endif; ?>
                                    <a href="reservation_detail.php?id=<?php echo $res['id']; ?>" class="font-semibold text-blue-600 hover:underline">Detay Gör</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div class="mt-6 flex justify-between items-center">
                    <p class="text-sm text-gray-600">Toplam <?php echo $total_reservations; ?> kayıttan <?php echo count($reservations); ?> tanesi gösteriliyor.</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>