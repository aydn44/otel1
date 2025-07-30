<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../lib/InquiryRepository.php';

$pdo = $GLOBALS['pdo'];
$inquiryRepo = new App\Lib\InquiryRepository($pdo);

$action = $_GET['action'] ?? null;
$inquiryId = $_GET['id'] ?? null;

if (!$action || !$inquiryId) {
    header('Location: inquiries.php');
    exit;
}

if ($action === 'delete') {
    if ($inquiryRepo->deleteInquiry($inquiryId)) {
        $_SESSION['success_message'] = "Talep başarıyla silindi.";
    } else {
        $_SESSION['error_message'] = "Talep silinirken bir hata oluştu.";
    }
}

header('Location: inquiries.php');
exit;