<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../lib/EventRepository.php'; 

$pdo = $GLOBALS['pdo'];
$eventRepo = new App\Lib\EventRepository($pdo);

$events = $eventRepo->getAllEvents(); 

$success_message = $_SESSION['success_message'] ?? null;
unset($_SESSION['success_message']);
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['error_message']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Etkinlik Yönetimi</title>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-200">
    <div class="flex min-h-screen">
        <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
        <div class="flex-1 p-10">
            <h1 class="text-3xl font-bold text-gray-800 mb-8">Etkinlik Yönetimi</h1>
            
            <?php if ($success_message): ?><div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"><?php echo htmlspecialchars($success_message); ?></div><?php endif; ?>
            <?php if ($error_message): ?><div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"><?php echo htmlspecialchars($error_message); ?></div><?php endif; ?>

            <div class="bg-white p-6 rounded-lg shadow-lg mb-8">
                <h2 class="text-xl font-semibold mb-4">Mevcut Etkinlikler
                    <a href="event_form.php" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-md text-sm float-right ml-4">+ Yeni Etkinlik Ekle</a>
                </h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left">
                        <thead>
                            <tr class="border-b bg-gray-50">
                                <th class="p-4">Başlık</th>
                                <th class="p-4">Resim</th>
                                <th class="p-4">Yayınlandı mı?</th>
                                <th class="p-4 text-right">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($events)): ?>
                                <tr><td colspan="4" class="p-4 text-center text-gray-500">Hiç etkinlik bulunamadı.</td></tr>
                            <?php else: ?>
                                <?php foreach ($events as $event): ?>
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="p-4 font-semibold"><?php echo htmlspecialchars($event['title']); ?></td>
                                        <td class="p-4">
                                            <?php if (!empty($event['featured_image'])): ?><img src="<?php echo BASE_URL . '/uploads/' . htmlspecialchars($event['featured_image']); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>" class="h-16 w-16 object-cover rounded"><?php endif; ?>
                                        </td>
                                        <td class="p-4">
                                            <span class="px-2 py-1 rounded-full text-xs font-semibold <?php echo $event['is_published'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>"><?php echo $event['is_published'] ? 'Evet' : 'Hayır'; ?></span>
                                        </td>
                                        <td class="p-4 text-right space-x-4">
                                            <a href="event_form.php?id=<?php echo htmlspecialchars($event['id']); ?>" class="font-semibold text-blue-600 hover:underline">Düzenle</a>
                                            <a href="event_actions.php?action=delete&id=<?php echo htmlspecialchars($event['id']); ?>" class="font-semibold text-red-600 hover:underline" onclick="return confirm('Bu etkinliği silmek istediğinizden emin misiniz?');">Sil</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>