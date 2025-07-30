<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../lib/RoomRepository.php';

$roomRepo = new App\Lib\RoomRepository($pdo);
$id = $_GET['id'] ?? null;
$is_editing = (bool)$id;

$room_type = ['name' => '', 'description' => '', 'capacity' => '', 'base_price' => '', 'currency' => 'TRY', 'featured_image' => ''];

if ($is_editing) {
    $room_type_data = $roomRepo->getRoomTypeById($id);
    if ($room_type_data) {
        $room_type = array_merge($room_type, $room_type_data);
    } else { 
        header('Location: rooms.php'); 
        exit; 
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_data = [
        'name' => $_POST['name'],
        'description' => $_POST['description'],
        'capacity' => $_POST['capacity'],
        'base_price' => $_POST['base_price'],
        'currency' => $_POST['currency'], // Para birimi alanı eklendi
        'image_name' => $_POST['current_image']
    ];

    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] == 0) {
        $upload_dir = ROOT_PATH . '/uploads/';
        $safe_filename = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '', basename($_FILES['featured_image']['name']));
        $destination = $upload_dir . $safe_filename;
        if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $destination)) {
            $post_data['image_name'] = $safe_filename;
        }
    }

    if ($is_editing) {
        $roomRepo->updateRoomType($id, $post_data);
    } else {
        $roomRepo->createRoomType($post_data);
    }
    
    header('Location: rooms.php'); 
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $is_editing ? 'Oda Tipini Düzenle' : 'Yeni Oda Tipi Ekle'; ?></title>
    <meta charset="UTF-8"><script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>.sidebar-link.active { background-color: #1D4ED8; }</style>
</head>
<body class="bg-gray-200">
    <div class="flex min-h-screen">
        <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
        <div class="flex-1 p-10">
            <h1 class="text-3xl font-bold text-gray-800 mb-8"><?php echo $is_editing ? 'Oda Tipini Düzenle' : 'Yeni Oda Tipi Ekle'; ?></h1>
            <form method="POST" enctype="multipart/form-data" class="bg-white p-8 rounded-lg shadow-lg max-w-2xl mx-auto">
                <div class="mb-4"><label for="name" class="block font-bold mb-1">Oda Tipi Adı</label><input type="text" name="name" value="<?php echo htmlspecialchars($room_type['name']); ?>" class="w-full p-2 border rounded mt-1" required></div>
                <div class="mb-4"><label for="description" class="block font-bold mb-1">Açıklama</label><textarea name="description" class="w-full p-2 border rounded mt-1" rows="4"><?php echo htmlspecialchars($room_type['description']); ?></textarea></div>
                <div class="mb-4"><label for="capacity" class="block font-bold mb-1">Kapasite (Kişi)</label><input type="number" name="capacity" value="<?php echo htmlspecialchars($room_type['capacity']); ?>" class="w-full p-2 border rounded mt-1" required></div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="base_price" class="block font-bold mb-1">Gecelik Fiyat</label>
                        <input type="text" name="base_price" value="<?php echo htmlspecialchars($room_type['base_price']); ?>" class="w-full p-2 border rounded mt-1" required>
                    </div>
                    <div class="mb-4">
                        <label for="currency" class="block font-bold mb-1">Para Birimi</label>
                        <select name="currency" id="currency" class="w-full p-2 border rounded mt-1 bg-white">
                            <option value="TRY" <?php if($room_type['currency'] == 'TRY') echo 'selected'; ?>>TL (Türk Lirası)</option>
                            <option value="USD" <?php if($room_type['currency'] == 'USD') echo 'selected'; ?>>USD (Dolar)</option>
                            <option value="EUR" <?php if($room_type['currency'] == 'EUR') echo 'selected'; ?>>EUR (Euro)</option>
                        </select>
                    </div>
                </div>

                <div class="mb-6"><label for="featured_image" class="block font-bold mb-1">Oda Görseli</label>
                    <?php if ($room_type['featured_image']): ?>
                    <img src="<?php echo BASE_URL . '/uploads/' . htmlspecialchars($room_type['featured_image']); ?>" class="w-48 h-auto rounded-md my-2 border p-1">
                    <?php endif; ?>
                    <input type="file" name="featured_image" class="w-full p-2 border rounded mt-1">
                    <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($room_type['featured_image']); ?>">
                </div>
                <div class="text-right"><button type="submit" class="bg-blue-600 text-white py-2 px-6 rounded-lg hover:bg-blue-700 font-bold"><i class="fas fa-save mr-2"></i>Kaydet</button></div>
            </form>
        </div>
    </div>
</body>
</html>