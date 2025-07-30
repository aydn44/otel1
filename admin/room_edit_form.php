<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../lib/RoomRepository.php'; //
require_once __DIR__ . '/../lib/PageRepository.php'; // Menü için

$pdo = $GLOBALS['pdo'];
$roomRepo = new App\Lib\RoomRepository($pdo); //
$pageRepo = new App\Lib\PageRepository($pdo); //

$roomId = $_GET['id'] ?? null;
$room = null;
$allRoomTypes = $roomRepo->getAllRoomTypes(); // Tüm oda tiplerini çekiyoruz

if ($roomId) {
    $room = $roomRepo->getPhysicalRoomById($roomId); // Odayı ID'sine göre getir
    if (!$room) {
        $_SESSION['error_message'] = "Düzenlenecek oda bulunamadı.";
        header('Location: rooms.php');
        exit;
    }
} else {
    $_SESSION['error_message'] = "Düzenlenecek oda ID'si belirtilmedi.";
    header('Location: rooms.php');
    exit;
}

$menu_pages = $pageRepo->getMenuPages(); // Sol menü için sayfaları çek

?>
<!DOCTYPE html>
<html>
<head>
    <title>Fiziksel Oda Düzenle</title>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>.sidebar-link.active { background-color: #1D4ED8; }</style>
</head>
<body class="bg-gray-200">
    <div class="flex min-h-screen">
        <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
        <div class="flex-1 p-10">
            <h1 class="text-3xl font-bold text-gray-800 mb-8">Fiziksel Oda Düzenle</h1>
            
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
                </div>
            <?php endif; ?>

            <div class="bg-white p-6 rounded-lg shadow-lg">
                <form action="room_actions.php" method="POST">
                    <input type="hidden" name="action" value="update_physical_room">
                    <input type="hidden" name="room_id" value="<?php echo htmlspecialchars($room['id']); ?>">

                    <div class="mb-4">
                        <label for="room_number" class="block text-gray-700 text-sm font-bold mb-2">Oda Numarası:</label>
                        <input type="text" id="room_number" name="room_number" value="<?php echo htmlspecialchars($room['room_number']); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>

                    <div class="mb-6">
                        <label for="room_type_id" class="block text-gray-700 text-sm font-bold mb-2">Oda Tipi:</label>
                        <select id="room_type_id" name="room_type_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                            <option value="">Seçiniz...</option>
                            <?php foreach ($allRoomTypes as $type): ?>
                                <option value="<?php echo htmlspecialchars($type['id']); ?>" <?php echo ($type['id'] == $room['room_type_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($type['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="flex items-center justify-between">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Odayı Güncelle
                        </button>
                        <a href="rooms.php" class="inline-block align-baseline font-bold text-sm text-gray-600 hover:text-gray-800">
                            İptal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>