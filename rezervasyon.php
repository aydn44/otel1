<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/View.php';
require_once __DIR__ . '/lib/PageRepository.php';
require_once __DIR__ . '/lib/RoomRepository.php';

$pdo = $GLOBALS['pdo'];
$view = new App\Lib\View($pdo);
$pageRepo = new App\Lib\PageRepository($pdo);
$roomRepo = new App\Lib\RoomRepository($pdo);

$checkin = $_GET['checkin'] ?? null;
$checkout = $_GET['checkout'] ?? null;
$adults = $_GET['adults'] ?? 1;
$children = $_GET['children'] ?? 0;

$available_room_types = [];
$error_message = '';

// Yalnızca checkin ve checkout tarihleri varsa oda müsaitlik sorgusunu çalıştır
if ($checkin && $checkout) {
    if (new DateTime($checkin) >= new DateTime($checkout)) {
        $error_message = "Çıkış tarihi, giriş tarihinden sonra olmalıdır.";
    } else {
        $sql = "
            SELECT 
                rt.id, rtt.name, rtt.description, rt.base_price, rt.currency, rt.capacity, rt.featured_image,
                COUNT(r_available.id) as available_room_count
            FROM room_types rt
            JOIN room_type_translations rtt ON rt.id = rtt.room_type_id AND rtt.language_code = 'tr'
            JOIN rooms r_available ON r_available.room_type_id = rt.id
            WHERE 
                rt.capacity >= ? AND rt.capacity >= (? + ?) -- Kapasite yetişkin + çocuk sayısına göre
                AND r_available.id NOT IN (
                    SELECT DISTINCT br.room_id
                    FROM booked_rooms br
                    JOIN bookings b ON br.booking_id = b.id
                    WHERE NOT (b.check_out_date <= ? OR b.check_in_date >= ?)
                )
            GROUP BY rt.id
            ORDER BY rt.base_price ASC
        ";

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$adults, $adults, $children, $checkin, $checkout]);
            $available_room_types = $stmt->fetchAll();
        } catch (PDOException $e) {
            $error_message = "Veritabanı sorgusu sırasında bir hata oluştu: " . $e->getMessage();
            error_log($e->getMessage());
        }
    }
} 
// else {
//     // Eğer checkin/checkout tarihleri yoksa, burada bir varsayılan mesaj veya davranış tanımlayabilirsiniz.
//     // Örneğin: $error_message = "Lütfen giriş ve çıkış tarihlerini seçerek uygun odaları arayın.";
// }


$data = [
    'title' => 'Müsait Odalar',
    'menu_pages' => $pageRepo->getMenuPages(),
    'available_room_types' => $available_room_types,
    'error_message' => $error_message,
    'checkin' => $checkin,
    'checkout' => $checkout,
    'adults' => $adults,
    'children' => $children
];

$view->render('pages/rezervasyon_list.php', $data);