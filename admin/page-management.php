<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../lib/PageRepository.php';
require_once __DIR__ . '/../helpers/security.php'; // CSRF token için dahil edildi

$pdo = $GLOBALS['pdo'];
$pageRepo = new App\Lib\PageRepository($pdo);

// Tüm sayfaları çek
try {
    $stmt = $pdo->query("
        SELECT p.*, pt.title 
        FROM pages p 
        LEFT JOIN page_translations pt ON p.id = pt.page_id AND pt.language_code = 'tr' 
        ORDER BY p.sort_order ASC, p.id ASC
    ");
    $pages = $stmt->fetchAll();
} catch (Exception $e) {
    $pages = [];
}

// Durum mesajları
$success_message = $_SESSION['success_message'] ?? null;
unset($_SESSION['success_message']);
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['error_message']);

// CSRF token oluştur
$csrf_token = generate_csrf_token();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Sayfa Yönetimi</title>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>.sidebar-link.active { background-color: #1D4ED8; }</style>
</head>
<body class="bg-gray-200">
    <div class="flex min-h-screen">
        <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
        <div class="flex-1 p-10">
            <h1 class="text-3xl font-bold text-gray-800 mb-8">Sayfa Yönetimi</h1>
            
            <?php if ($success_message): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            <div id="ajax-message" class="hidden mb-4"></div>

            <div class="bg-white p-6 rounded-lg shadow-lg mb-8">
                <h2 class="text-xl font-semibold mb-4">Mevcut Sayfalar
                    <a href="page_form.php" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-md text-sm float-right ml-4">+ Yeni Sayfa Ekle</a>
                </h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left">
                        <thead>
                            <tr class="border-b bg-gray-50">
                                <th class="p-4">Başlık</th>
                                <th class="p-4">Slug</th>
                                <th class="p-4">Sıra</th>
                                <th class="p-4">Yayınlandı mı?</th>
                                <th class="p-4 text-right">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pages)): ?>
                                <tr><td colspan="5" class="p-4 text-center text-gray-500">Hiç sayfa bulunamadı.</td></tr>
                            <?php else: ?>
                                <?php foreach ($pages as $page): ?>
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="p-4 font-semibold"><?php echo htmlspecialchars($page['title'] ?? 'Başlık Yok'); ?></td>
                                        <td class="p-4"><?php echo htmlspecialchars($page['slug']); ?></td>
                                        <td class="p-4">
                                            <input type="number" class="form-control w-20 p-1 border rounded text-center" 
                                                   value="<?php echo $page['sort_order']; ?>" 
                                                   onchange="updateOrder(<?php echo $page['id']; ?>, this.value)">
                                        </td>
                                        <td class="p-4">
                                            <select onchange="updateStatus(<?php echo $page['id']; ?>, this.value, this)" class="p-1 border rounded <?php echo ($page['is_published'] ?? 0) ? 'bg-green-100 border-green-300' : 'bg-red-100 border-red-300'; ?>">
                                                <option value="1" <?php echo ($page['is_published'] ?? 0) ? 'selected' : ''; ?>>Evet</option>
                                                <option value="0" <?php echo !($page['is_published'] ?? 0) ? 'selected' : ''; ?>>Hayır</option>
                                            </select>
                                        </td>
                                        <td class="p-4 text-right space-x-2">
                                            <a href="page_form.php?id=<?php echo htmlspecialchars($page['id']); ?>" class="inline-block bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm"><i class="fas fa-edit"></i> Düzenle</a>
                                            <button onclick="deletePage(<?php echo $page['id']; ?>)" class="inline-block bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm"><i class="fas fa-trash"></i> Sil</button>
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

    <script>
    const csrfToken = "<?php echo $csrf_token; ?>"; // CSRF token JavaScript'e aktarıldı

    // `selectElement` parametresi eklendi
    function updateStatus(pageId, newStatus, selectElement) { 
        const messageBox = document.getElementById('ajax-message');
        messageBox.style.display = 'none'; // Önceki mesajı gizle

        fetch('page_actions.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `action=update_status&page_id=${pageId}&status=${newStatus}&token=${csrfToken}`
        })
        .then(response => {
            if (!response.ok) {
                // HTTP hatası (400, 500 vb.) durumunda
                return response.text().then(text => { throw new Error(text) });
            }
            return response.json();
        })
        .then(data => {
            if(data.success) {
                messageBox.className = 'bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative';
                messageBox.textContent = data.message;
                // `selectElement` doğrudan kullanıldı, `event.target` kaldırıldı
                selectElement.className = newStatus == 1 ? 'p-1 border rounded bg-green-100 border-green-300' : 'p-1 border rounded bg-red-100 border-red-300';
            } else {
                messageBox.className = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative';
                messageBox.textContent = 'Hata: ' + (data.message || 'Bilinmeyen bir hata oluştu.');
            }
            messageBox.style.display = 'block';
            setTimeout(() => { messageBox.style.display = 'none'; }, 3000);
        })
        .catch(error => {
            messageBox.className = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative';
            messageBox.textContent = 'AJAX Hatası: ' + error.message;
            messageBox.style.display = 'block';
            console.error('AJAX hatası:', error);
            setTimeout(() => { messageBox.style.display = 'none'; }, 5000);
        });
    }

    function updateOrder(pageId, newOrder) {
        const messageBox = document.getElementById('ajax-message');
        messageBox.style.display = 'none'; // Önceki mesajı gizle

        fetch('page_actions.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `action=update_order&page_id=${pageId}&sort_order=${newOrder}&token=${csrfToken}`
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => { throw new Error(text) });
            }
            return response.json();
        })
        .then(data => {
            if(data.success) {
                location.reload(); // Sıralama değiştiğinde sayfayı yenilemek en iyisi
            } else {
                messageBox.className = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative';
                messageBox.textContent = 'Hata: ' + (data.message || 'Bilinmeyen bir hata oluştu.');
                messageBox.style.display = 'block';
                setTimeout(() => { messageBox.style.display = 'none'; }, 3000);
            }
        })
        .catch(error => {
            messageBox.className = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative';
            messageBox.textContent = 'AJAX Hatası: ' + error.message;
            messageBox.style.display = 'block';
            console.error('AJAX hatası:', error);
            setTimeout(() => { messageBox.style.display = 'none'; }, 5000);
        });
    }

    function deletePage(pageId) {
        if(confirm('Bu sayfayı silmek istediğinizden emin misiniz?\n\nBu işlem geri alınamaz!')) {
            const messageBox = document.getElementById('ajax-message');
            messageBox.style.display = 'none'; // Önceki mesajı gizle

            fetch('page_actions.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=delete_page&page_id=${pageId}&token=${csrfToken}`
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => { throw new Error(text) });
                }
                return response.json();
            })
            .then(data => {
                if(data.success) {
                    location.reload(); // Sayfa silindiğinde sayfayı yenile
                } else {
                    messageBox.className = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative';
                    messageBox.textContent = 'Hata: ' + (data.message || 'Bilinmeyen bir hata oluştu.');
                    messageBox.style.display = 'block';
                    setTimeout(() => { messageBox.style.display = 'none'; }, 3000);
                }
            })
            .catch(error => {
                messageBox.className = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative';
                messageBox.textContent = 'AJAX Hatası: ' + error.message;
                messageBox.style.display = 'block';
                console.error('AJAX hatası:', error);
                setTimeout(() => { messageBox.style.display = 'none'; }, 5000);
            });
        }
    }
    </script>
</body>
</html>