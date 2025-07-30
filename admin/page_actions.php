<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../lib/PageRepository.php';
require_once __DIR__ . '/../helpers/security.php';

header('Content-Type: application/json');

$pdo = $GLOBALS['pdo'];
$pageRepo = new App\Lib\PageRepository($pdo);
$response = ['success' => false, 'message' => 'Geçersiz işlem veya istek metodu.'];

// --- DEBUG LOGLAMA BAŞLANGICI ---
error_log("--- page_actions.php ---");
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
error_log("Raw POST Data: " . file_get_contents('php://input')); // Gelen tüm POST verisini yakala
error_log("POST Array: " . print_r($_POST, true)); // $_POST dizisini yazdır
// --- DEBUG LOGLAMA SONU ---

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null;
    $pageId = (int)($_POST['page_id'] ?? 0); 
    $token = $_POST['token'] ?? '';

    // --- DEBUG LOGLAMA ---
    error_log("Action: " . $action);
    error_log("Page ID (int): " . $pageId);
    error_log("Token: " . $token);
    // --- DEBUG LOGLAMA SONU ---

    // CSRF token doğrulaması (tüm POST AJAX işlemleri için)
    if ($action !== 'update_content' && $action !== 'create_page_via_form') {
        if (!validate_csrf_token($token)) {
            $response['message'] = 'Güvenlik hatası: Geçersiz CSRF token. Lütfen sayfayı yenileyip tekrar deneyin.';
            echo json_encode($response);
            exit;
        }
        // Başarılı doğrulamadan sonra token'ı temizle (tek kullanımlık olması için)
        // DİKKAT: Eğer aynı token ile arka arkaya birden fazla AJAX çağrısı yapılıyorsa sorun çıkarabilir.
        // Bu durumda bu satırı yorum satırı yapmayı düşünebilirsiniz, ancak güvenlik riski oluşturur.
        unset($_SESSION['csrf_token']); 
    }

    // Check for invalid pageId BEFORE the switch
    // Bu kontrolün create_page_via_form action'ında atlanması gerekiyor.
    if ($pageId <= 0 && $action !== 'create_page_via_form') {
        $response['message'] = 'Geçersiz sayfa ID.';
        echo json_encode($response);
        exit;
    }

    try {
        switch ($action) {
            case 'update_status':
                $status = (int)($_POST['status'] ?? 0);
                $page_data = $pageRepo->getPageById($pageId);
                if ($page_data) {
                    $page_data['is_published'] = $status;
                    if ($pageRepo->updatePage(
                        $pageId,
                        [
                            'slug' => $page_data['slug'],
                            'is_published' => $page_data['is_published'],
                            'sort_order' => $page_data['sort_order'],
                            'background_type' => $page_data['background_type'],
                            'background_value' => $page_data['background_value'],
                            'title' => $page_data['title'],
                            'content' => $page_data['content']
                        ]
                    )) {
                        $response['success'] = true;
                        $response['message'] = 'Sayfa durumu başarıyla güncellendi.';
                    } else {
                        $response['message'] = 'Sayfa durumu güncellenirken bir hata oluştu.';
                    }
                } else {
                    $response['message'] = 'Sayfa bulunamadı.';
                }
                break;

            case 'update_order':
                $sort_order = (int)($_POST['sort_order'] ?? 0);
                $page_data = $pageRepo->getPageById($pageId);
                if ($page_data) {
                    $page_data['sort_order'] = $sort_order;
                     if ($pageRepo->updatePage(
                        $pageId,
                        [
                            'slug' => $page_data['slug'],
                            'is_published' => $page_data['is_published'],
                            'sort_order' => $page_data['sort_order'],
                            'background_type' => $page_data['background_type'],
                            'background_value' => $page_data['background_value'],
                            'title' => $page_data['title'],
                            'content' => $page_data['content']
                        ]
                    )) {
                        $response['success'] = true;
                        $response['message'] = 'Sayfa sıralaması başarıyla güncellendi.';
                    } else {
                        $response['message'] = 'Sayfa sıralaması güncellenirken bir hata oluştu.';
                    }
                } else {
                    $response['message'] = 'Sayfa bulunamadı.';
                }
                break;

            case 'delete_page':
                if ($pageRepo->deletePage($pageId)) {
                    $response['success'] = true;
                    $response['message'] = 'Sayfa başarıyla silindi.';
                } else {
                    $response['message'] = 'Sayfa silinirken bir hata oluştu. Lütfen ilişkili verileri kontrol edin.';
                }
                break;

            case 'update_content': // Bu işlem AJAX değildir, form gönderimi ile gelir.
                // CSRF token kontrolü bu formda (page_form.php) ayrı bir şekilde yapılmalı (eğer varsa).
                $content = $_POST['content'] ?? '';
                $page_data = $pageRepo->getPageById($pageId);
                if ($page_data) {
                    if ($pageRepo->updatePage(
                        $pageId,
                        [
                            'slug' => $page_data['slug'],
                            'is_published' => $page_data['is_published'],
                            'sort_order' => $page_data['sort_order'],
                            'background_type' => $page_data['background_type'],
                            'background_value' => $page_data['background_value'],
                            'title' => $page_data['title'],
                            'content' => $content
                        ]
                    )) {
                        $_SESSION['success_message'] = "Sayfa içeriği başarıyla güncellendi.";
                    } else {
                        $_SESSION['error_message'] = "Sayfa içeriği güncellenirken bir hata oluştu.";
                    }
                } else {
                    $_SESSION['error_message'] = "Sayfa bulunamadı.";
                }
                header('Location: page-form.php?id=' . $pageId); // Yönlendirme yapılır
                exit; 
                break;

            case 'create_page_via_form': // Bu işlem AJAX değildir, form gönderimi ile gelir.
                // CSRF token kontrolü bu formda (page_form.php) ayrı bir şekilde yapılmalı (eğer varsa).
                 $data_for_create = [
                    'slug' => $_POST['slug'] ?? '',
                    'is_published' => (int)($_POST['is_published'] ?? 0),
                    'sort_order' => (int)($_POST['sort_order'] ?? 0),
                    'background_type' => $_POST['background_type'] ?? 'none',
                    'background_value' => $_POST['background_value'] ?? '',
                    'title' => $_POST['title'] ?? '',
                    'content' => $_POST['content'] ?? ''
                ];
                if (empty($data_for_create['slug']) || empty($data_for_create['title'])) {
                    $_SESSION['error_message'] = "Sayfa başlığı ve slug boş bırakılamaz.";
                } elseif ($pageRepo->createPage($data_for_create)) {
                    $_SESSION['success_message'] = "Yeni sayfa başarıyla oluşturuldu.";
                } else {
                    $_SESSION['error_message'] = "Yeni sayfa oluşturulurken bir hata oluştu.";
                }
                header('Location: page-management.php');
                exit;
                break;

            default:
                $response['message'] = 'Bilinmeyen işlem.';
                break;
        }
    } catch (\Exception $e) {
        $response['message'] = 'Sunucu hatası: ' . $e->getMessage();
        error_log('page_actions.php hatası: ' . $e->getMessage());
    }
} else {
    // GET isteği veya POST olmayan istekler için
    $response['message'] = 'Geçersiz istek metodu.';
}

echo json_encode($response);
exit;