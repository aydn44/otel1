<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../lib/RoomRepository.php'; //
require_once __DIR__ . '/../lib/PageRepository.php'; // Menü için

$pdo = $GLOBALS['pdo'];
$roomRepo = new App\Lib\RoomRepository($pdo); //
$pageRepo = new App\Lib\PageRepository($pdo); //

$allPhysicalRooms = $roomRepo->getAllPhysicalRooms(); // Tüm fiziksel odaları çek
$allRoomTypes = $roomRepo->getAllRoomTypes(); // Tüm oda tiplerini çek (yeni oda ekleme formu için)

// Durum mesajlarını kontrol et
$success_message = $_SESSION['success_message'] ?? null;
unset($_SESSION['success_message']);
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['error_message']);

$menu_pages = $pageRepo->getMenuPages(); // Sol menü için sayfaları çek

?>
<!DOCTYPE html>
<html>
<head>
    <title>Oda Yönetimi</title>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>.sidebar-link.active { background-color: #1D4ED8; }</style>
</head>
<body class="bg-gray-200">
    <div class="flex min-h-screen">
        <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
        <div class="flex-1 p-10">
            <h1 class="text-3xl font-bold text-gray-800 mb-8">Oda Durum Paneli</h1>
            
            <?php if ($success_message): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <div class="bg-white p-6 rounded-lg shadow-lg mb-8">
                <h2 class="text-xl font-semibold mb-4">Oda Durumları</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    <?php foreach ($allPhysicalRooms as $room): ?>
                        <div class="border rounded-lg p-4 text-center">
                            <p class="font-bold text-lg"><?php echo htmlspecialchars($room['room_number']); ?></p>
                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($room['type_name']); ?></p>
                            <div class="mt-2">
                                <form action="room_actions.php" method="POST" class="inline-block">
                                    <input type="hidden" name="action" value="update_room_status">
                                    <input type="hidden" name="room_id" value="<?php echo htmlspecialchars($room['id']); ?>">
                                    <select name="status" onchange="this.form.submit()" class="bg-gray-100 border rounded-md p-1 text-sm <?php 
                                        if ($room['status'] == 'available') echo 'text-green-600'; 
                                        elseif ($room['status'] == 'occupied') echo 'text-red-600'; 
                                        else echo 'text-gray-600';
                                    ?>">
                                        <option value="available" <?php echo ($room['status'] == 'available') ? 'selected' : ''; ?>>Boş</option>
                                        <option value="occupied" <?php echo ($room['status'] == 'occupied') ? 'selected' : ''; ?>>Dolu</option>
                                        <option value="maintenance" <?php echo ($room['status'] == 'maintenance') ? 'selected' : ''; ?>>Bakımda</option>
                                    </select>
                                </form>
                                <a href="room_edit_form.php?id=<?php echo htmlspecialchars($room['id']); ?>" class="ml-2 text-blue-600 hover:text-blue-800" title="Oda Detaylarını Düzenle"><i class="fas fa-edit"></i></a>
                                <a href="room_actions.php?action=delete_physical_room&id=<?php echo htmlspecialchars($room['id']); ?>" class="ml-2 text-red-600 hover:text-red-800" title="Odayı Sil" onclick="return confirm('Bu odayı silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.');"><i class="fas fa-trash-alt"></i></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-lg mb-8">
                <h2 class="text-xl font-semibold mb-4">Yeni Fiziksel Oda Ekle</h2>
                <form action="room_actions.php" method="POST">
                    <input type="hidden" name="action" value="add_physical_room">
                    <div class="mb-4">
                        <label for="room_number_new" class="block text-gray-700 text-sm font-bold mb-2">Oda Numarası:</label>
                        <input type="text" id="room_number_new" name="room_number" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>
                    <div class="mb-6">
                        <label for="room_type_id_new" class="block text-gray-700 text-sm font-bold mb-2">Oda Tipi:</label>
                        <select id="room_type_id_new" name="room_type_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                            <option value="">Seçiniz...</option>
                            <?php foreach ($allRoomTypes as $type): ?>
                                <option value="<?php echo htmlspecialchars($type['id']); ?>"><?php echo htmlspecialchars($type['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex items-center justify-between">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Fiziksel Oda Ekle
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-lg">
                <h2 class="text-xl font-semibold mb-4">Oda Tipleri
                    <a href="room_type_form.php" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-md text-sm float-right ml-4">+ Yeni Tip Ekle</a>
                </h2>
                <table class="w-full text-left table-auto">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="px-4 py-2">Adı</th>
                            <th class="px-4 py-2 text-right">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Oda tiplerini RoomRepository'den tekrar çekiyoruz
                        $room_types_list = $roomRepo->getAllRoomTypes();
                        if (empty($room_types_list)): ?>
                            <tr><td colspan="2" class="px-4 py-2 text-center text-gray-500">Hiç oda tipi bulunamadı.</td></tr>
                        <?php else: ?>
                            <?php foreach ($room_types_list as $room_type): ?>
                            <tr>
                                <td class="border px-4 py-2"><?php echo htmlspecialchars($room_type['name']); ?></td>
                                <td class="border px-4 py-2 text-right">
                                    <a href="room_type_form.php?id=<?php echo htmlspecialchars($room_type['id']); ?>" class="text-blue-600 hover:underline">Düzenle</a>
                                    <a href="room_type_delete.php?id=<?php echo htmlspecialchars($room_type['id']); ?>" class="text-red-600 hover:underline ml-2" onclick="return confirm('Bu oda tipini ve ilişkili tüm fiziksel odaları silmek istediğinizden emin misiniz? Bu işlem geri alınamaz!');">Sil</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>