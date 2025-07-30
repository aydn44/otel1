<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../lib/InquiryRepository.php';
require_once __DIR__ . '/../lib/PageRepository.php'; // Sidebar için

$pdo = $GLOBALS['pdo'];
$inquiryRepo = new App\Lib\InquiryRepository($pdo);
$pageRepo = new App\Lib\PageRepository($pdo);

$inquiries = $inquiryRepo->getAllInquiries();
$menu_pages = $pageRepo->getMenuPages(); // Sidebar menüsü için

$success_message = $_SESSION['success_message'] ?? null;
unset($_SESSION['success_message']);
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['error_message']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Gelen Talepler</title>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>.sidebar-link.active { background-color: #1D4ED8; }</style>
</head>
<body class="bg-gray-200">
    <div class="flex min-h-screen">
        <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
        <div class="flex-1 p-10">
            <h1 class="text-3xl font-bold text-gray-800 mb-8">Gelen Bilgi Talepleri</h1>

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

            <div class="bg-white p-6 rounded-lg shadow-lg overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b bg-gray-50">
                            <th class="p-4">Okundu</th>
                            <th class="p-4">Gönderen</th>
                            <th class="p-4">Konu</th>
                            <th class="p-4">Tip</th>
                            <th class="p-4">Tarih</th>
                            <th class="p-4 text-right">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($inquiries)): ?>
                            <tr><td colspan="6" class="p-4 text-center text-gray-500">Henüz hiç bilgi talebi bulunamadı.</td></tr>
                        <?php else: ?>
                            <?php foreach ($inquiries as $inquiry): ?>
                                <tr class="border-b hover:bg-gray-50 <?php echo $inquiry['is_read'] ? 'text-gray-500' : 'font-semibold text-gray-800'; ?>">
                                    <td class="p-4">
                                        <?php if ($inquiry['is_read']): ?>
                                            <i class="fas fa-check-circle text-green-500" title="Okundu"></i>
                                        <?php else: ?>
                                            <i class="fas fa-circle text-blue-500" title="Yeni"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4"><?php echo htmlspecialchars($inquiry['name']); ?></td>
                                    <td class="p-4"><?php echo htmlspecialchars(mb_strimwidth($inquiry['subject'], 0, 50, "...")); ?></td>
                                    <td class="p-4"><?php echo htmlspecialchars(ucfirst($inquiry['type'])); ?></td>
                                    <td class="p-4"><?php echo date('d M Y H:i', strtotime($inquiry['created_at'])); ?></td>
                                    <td class="p-4 text-right space-x-2">
                                        <a href="inquiry_detail.php?id=<?php echo htmlspecialchars($inquiry['id']); ?>" class="text-blue-600 hover:underline">Detay</a>
                                        <a href="inquiry_actions.php?action=delete&id=<?php echo htmlspecialchars($inquiry['id']); ?>" class="text-red-600 hover:underline" onclick="return confirm('Bu talebi silmek istediğinizden emin misiniz?');">Sil</a>
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