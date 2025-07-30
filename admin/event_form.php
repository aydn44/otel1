<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../lib/EventRepository.php';

$pdo = $GLOBALS['pdo'];
$eventRepo = new App\Lib\EventRepository($pdo);

$eventId = $_GET['id'] ?? null;
$event = null;
$form_title = "Yeni Etkinlik Ekle";

if ($eventId) {
    $event = $eventRepo->getEventById($eventId);
    if (!$event) {
        $_SESSION['error_message'] = "Düzenlenecek etkinlik bulunamadı.";
        header('Location: events.php');
        exit;
    }
    $form_title = "Etkinlik Düzenle: " . htmlspecialchars($event['title']);
}

$title = $event['title'] ?? '';
$description = $event['description'] ?? '';
$featured_image = $event['featured_image'] ?? '';
$is_published = $event['is_published'] ?? 1;
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($form_title); ?></title>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-200">
    <div class="flex min-h-screen">
        <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
        <div class="flex-1 p-10">
            <h1 class="text-3xl font-bold text-gray-800 mb-8"><?php echo htmlspecialchars($form_title); ?></h1>
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <form action="event_actions.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="<?php echo $event ? 'update' : 'create'; ?>">
                    <?php if ($event): ?><input type="hidden" name="id" value="<?php echo htmlspecialchars($event['id']); ?>"><?php endif; ?>

                    <div class="mb-4">
                        <label for="title" class="block text-gray-700 text-sm font-bold mb-2">Etkinlik Başlığı:</label>
                        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required>
                    </div>

                    <div class="mb-4">
                        <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Açıklama:</label>
                        <textarea id="description" name="description" rows="10" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700"><?php echo htmlspecialchars($description); ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="featured_image" class="block text-gray-700 text-sm font-bold mb-2">Öne Çıkan Resim:</label>
                        <input type="file" id="featured_image" name="featured_image" accept="image/*" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                        <?php if ($featured_image): ?>
                            <p class="text-sm text-gray-600 mt-2">Mevcut Resim: <img src="<?php echo BASE_URL . '/uploads/' . htmlspecialchars($featured_image); ?>" alt="Mevcut Resim" class="h-24 w-24 object-cover"></p>
                            <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($featured_image); ?>">
                        <?php endif; ?>
                    </div>

                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Yayınla:</label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="is_published" value="1" class="form-radio" <?php echo ($is_published == 1) ? 'checked' : ''; ?>>
                            <span class="ml-2">Evet</span>
                        </label>
                        <label class="inline-flex items-center ml-6">
                            <input type="radio" name="is_published" value="0" class="form-radio" <?php echo ($is_published == 0) ? 'checked' : ''; ?>>
                            <span class="ml-2">Hayır</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-between">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            <?php echo $event ? 'Etkinliği Güncelle' : 'Etkinlik Ekle'; ?>
                        </button>
                        <a href="events.php" class="font-bold text-sm text-gray-600 hover:text-gray-800">İptal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>