<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../lib/InquiryRepository.php';
require_once __DIR__ . '/../lib/PageRepository.php'; // Sidebar için

$pdo = $GLOBALS['pdo'];
$inquiryRepo = new App\Lib\InquiryRepository($pdo);
$pageRepo = new App\Lib\PageRepository($pdo);

$inquiryId = $_GET['id'] ?? null;

if (!$inquiryId) {
    header('Location: inquiries.php');
    exit;
}

$inquiry = $inquiryRepo->getInquiryById($inquiryId);

if (!$inquiry) {
    $_SESSION['error_message'] = "Talep bulunamadı.";
    header('Location: inquiries.php');
    exit;
}

// Talebi okundu olarak işaretle
if (!$inquiry['is_read']) {
    $inquiryRepo->markAsRead($inquiryId);
    // Sayfayı yeniden yükleyerek okundu durumunu yansıtabiliriz
    header('Location: inquiry_detail.php?id=' . $inquiryId);
    exit;
}

$menu_pages = $pageRepo->getMenuPages(); // Sidebar menüsü için
?>
<!DOCTYPE html>
<html>
<head>
    <title>Talep Detayı #<?php echo htmlspecialchars($inquiry['id']); ?></title>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>.sidebar-link.active { background-color: #1D4ED8; }</style>
</head>
<body class="bg-gray-200">
    <div class="flex min-h-screen">
        <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
        <div class="flex-1 p-10">
            <a href="inquiries.php" class="text-blue-600 hover:underline mb-4 inline-block">&larr; Tüm Taleplere Geri Dön</a>
            <h1 class="text-3xl font-bold text-gray-800 mb-8">Talep Detayı #<?php echo htmlspecialchars($inquiry['id']); ?></h1>

            <div class="bg-white p-6 rounded-lg shadow-lg grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <h2 class="text-xl font-semibold mb-4 border-b pb-2">Talep Bilgileri</h2>
                    <p class="mb-2"><strong>Gönderen:</strong> <?php echo htmlspecialchars($inquiry['name']); ?></p>
                    <p class="mb-2"><strong>E-posta:</strong> <?php echo htmlspecialchars($inquiry['email']); ?></p>
                    <p class="mb-2"><strong>Telefon:</strong> <?php echo htmlspecialchars($inquiry['country_code'] . ' ' . $inquiry['phone']); ?></p>
                    <p class="mb-2"><strong>Konu:</strong> <?php echo htmlspecialchars($inquiry['subject']); ?></p>
                    <p class="mb-2"><strong>Tip:</strong> <?php echo htmlspecialchars(ucfirst($inquiry['type'])); ?></p>
                    <?php if ($inquiry['source_id']): ?>
                    <p class="mb-2"><strong>İlgili ID:</strong> <?php echo htmlspecialchars($inquiry['source_id']); ?></p>
                    <?php endif; ?>
                    <p class="mb-2"><strong>Gönderilme Tarihi:</strong> <?php echo date('d M Y H:i', strtotime($inquiry['created_at'])); ?></p>
                </div>
                <div class="md:col-span-2">
                    <h2 class="text-xl font-semibold mb-4 border-b pb-2">Mesaj</h2>
                    <p><?php echo nl2br(htmlspecialchars($inquiry['message'])); ?></p>
                </div>
            </div>
            <div class="mt-8 text-right">
                <a href="inquiry_actions.php?action=delete&id=<?php echo htmlspecialchars($inquiry['id']); ?>" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded" onclick="return confirm('Bu talebi silmek istediğinizden emin misiniz?');">Talebi Sil</a>
            </div>
        </div>
    </div>
</body>
</html>