<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../lib/RoomRepository.php'; // Repository sınıfını dahil et

$roomRepo = new App\Lib\RoomRepository($pdo);
$id = $_GET['id'] ?? null;

if ($id) {
    try {
        // Silmeden önce, bu oda tipine bağlı fiziksel oda var mı diye kontrol et.
        if ($roomRepo->hasPhysicalRooms($id)) {
            // Eğer varsa, kullanıcıyı bir hata mesajıyla durdur.
            // Daha gelişmiş bir sistem için session'a bir hata mesajı koyup yönlendirebiliriz.
            die("<h1>Silme Hatası</h1><p>Bu oda tipine bağlı fiziksel odalar varken bu tipi silemezsiniz. Lütfen önce ilgili odaları silin veya başka bir tipe taşıyın.</p><a href='rooms.php'>Geri Dön</a>");
        }
        
        // Fiziksel oda yoksa, güvenle silebiliriz.
        $roomRepo->deleteRoomType($id);

    } catch (Exception $e) {
        // Hata olursa, hata mesajını loglayabilir ve kullanıcıya genel bir hata gösterebiliriz.
        // error_log("Oda tipi silme hatası: " . $e->getMessage());
        die("<h1>Hata</h1><p>Oda tipi silinirken bir sorun oluştu.</p>");
    }
}

// İşlem tamamlandıktan veya ID yoksa, ana sayfaya yönlendir.
header('Location: rooms.php');
exit;
?>