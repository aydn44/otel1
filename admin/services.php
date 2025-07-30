<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../lib/ServiceRepository.php'; // Yeni eklenen repository

$pdo = $GLOBALS['pdo'];
$serviceRepo = new App\Lib\ServiceRepository($pdo);

$services = $serviceRepo->getAllServices(); // Tüm hizmetleri çek

// Durum mesajlarını kontrol et
$success_message = $_SESSION['success_message'] ?? null;
unset($_SESSION['success_message']);
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['error_message']);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Hizmet Yönetimi</title>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>.sidebar-link.active { background-color: #1D4ED8; }</style>
</head>
<body class="bg-gray-200">
    <div class="flex min-h-screen">
        <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
        <div class="flex-1 p-10">
            <h1 class="text-3xl font-bold text-gray-800 mb-8">Hizmet Yönetimi</h1>
            
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
                <h2 class="text-xl font-semibold mb-4">Mevcut Hizmetler
                    <a href="service_form.php" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-md text-sm float-right ml-4">+ Yeni Hizmet Ekle</a>
                </h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left">
                        <thead>
                            <tr class="border-b bg-gray-50">
                                <th class="p-4">Adı</th>
                                <th class="p-4">Fiyat</th>
                                <th class="p-4">İkon Sınıfı</th>
                                <th class="p-4">Açıklama (TR)</th>
                                <th class="p-4 text-right">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($services)): ?>
                                <tr><td colspan="5" class="p-4 text-center text-gray-500">Hiç hizmet bulunamadı.</td></tr>
                            <?php else: ?>
                                <?php foreach ($services as $service): ?>
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="p-4 font-semibold"><?php echo htmlspecialchars($service['name']); ?></td>
                                        <td class="p-4"><?php echo htmlspecialchars(number_format($service['price'], 2) . ' TL'); ?></td>
                                        <td class="p-4"><i class="<?php echo htmlspecialchars($service['icon_class']); ?>"></i> <?php echo htmlspecialchars($service['icon_class']); ?></td>
                                        <td class="p-4"><?php echo htmlspecialchars(mb_strimwidth(strip_tags($service['description_tr']), 0, 100, "...")); ?></td>
                                        <td class="p-4 text-right space-x-4">
                                            <a href="service_form.php?id=<?php echo htmlspecialchars($service['id']); ?>" class="font-semibold text-blue-600 hover:underline">Düzenle</a>
                                            <a href="service_actions.php?action=delete&id=<?php echo htmlspecialchars($service['id']); ?>" class="font-semibold text-red-600 hover:underline" onclick="return confirm('Bu hizmeti silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.');">Sil</a>
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