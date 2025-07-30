<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers/countries.php';
require_once __DIR__ . '/lib/RoomRepository.php';
require_once __DIR__ . '/lib/BookingRepository.php';
require_once __DIR__ . '/lib/Mailer.php';
require_once __DIR__ . '/lib/View.php';
require_once __DIR__ . '/lib/PageRepository.php';

$pdo = $GLOBALS['pdo']; 

$roomRepo = new App\Lib\RoomRepository($pdo);
$bookingRepo = new App\Lib\BookingRepository($pdo);
$pageRepo = new App\Lib\PageRepository($pdo);

$oda_id = $_GET['oda_id'] ?? null;
$checkin = $_GET['checkin'] ?? null;
$checkout = $_GET['checkout'] ?? null;
$adults = $_GET['adults'] ?? 1;
$children = $_GET['children'] ?? 0; // Çocuk sayısını da alalım

if (!$oda_id || !$checkin || !$checkout) { header('Location: index.php'); exit; }

$room_type = $roomRepo->getRoomTypeById($oda_id);
if (!$room_type) { header('Location: index.php'); exit; }

$error_message = '';
$success_message = '';

$checkin_date = new DateTime($checkin);
$checkout_date = new DateTime($checkout);
$night_count = $checkout_date->diff($checkin_date)->days;
$total_price = $night_count > 0 ? ($night_count * $room_type['base_price']) : $room_type['base_price'];

$room_currency = $room_type['currency'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $guest_name = trim($_POST['guest_name'] ?? '');
    $guest_email = trim($_POST['guest_email'] ?? '');
    $transfer_service = isset($_POST['transfer_service']) && $_POST['transfer_service'] == '1' ? 1 : 0; 
    $transfer_details = trim($_POST['transfer_details'] ?? ''); 
    $guest_phone = trim($_POST['guest_phone'] ?? ''); 
    $guest_nationality = trim($_POST['guest_nationality'] ?? ''); 
    $country_code = trim($_POST['country_code'] ?? ''); 
    $notes = trim($_POST['notes'] ?? ''); 


    if (empty($guest_name) || empty($guest_email)) {
        $error_message = "Zorunlu alanları doldurun.";
    } else {
        $bookingData = [
            'room_type_id' => $oda_id,
            'check_in' => $checkin,
            'check_out' => $checkout,
            'guest_name' => $guest_name,
            'guest_email' => $guest_email,
            'guest_phone' => $guest_phone, 
            'country_code' => $country_code,
            'guest_nationality' => $guest_nationality, 
            'num_adults' => $adults,
            'num_children' => $children, // Çocuk sayısını ekledik
            'total_price' => $total_price,
            'currency' => $room_currency,
            'transfer_service' => $transfer_service, 
            'transfer_details' => $transfer_details, 
            'notes' => $notes
        ];
        
        $bookingId = $bookingRepo->createBooking($bookingData);

        if ($bookingId) {
            $success_message = "Rezervasyonunuz başarıyla alınmıştır!";
            // E-posta gönderme kısmı buraya eklenebilir.
        } else {
            $error_message = "Seçtiğiniz odalar doldu. Lütfen tekrar deneyin.";
        }
    }
}

$view = new App\Lib\View($pdo);
$data = [
    'title' => 'Rezervasyon Onayı',
    'menu_pages' => $pageRepo->getMenuPages(),
    'room_type' => $room_type,
    'checkin' => $checkin,
    'checkout' => $checkout,
    'night_count' => $night_count,
    'total_price' => $total_price,
    'adults' => $adults,
    'children' => $children, // Çocuk sayısını da data'ya ekledik
    'error_message' => $error_message,
    'success_message' => $success_message,
    'room_currency' => $room_currency,
    // Formdan gelen POST verilerini tekrar yansıtabilmek için (form hata verdiğinde verilerin kaybolmaması için)
    'guest_name' => $_POST['guest_name'] ?? '',
    'guest_email' => $_POST['guest_email'] ?? '',
    'guest_phone' => $_POST['guest_phone'] ?? '',
    'country_code' => $_POST['country_code'] ?? '+90', 
    'guest_nationality' => $_POST['guest_nationality'] ?? 'Türkiye', 
    'transfer_service_checked' => (isset($_POST['transfer_service']) && $_POST['transfer_service'] == '1'),
    'transfer_details' => $_POST['transfer_details'] ?? '',
    'notes' => $_POST['notes'] ?? ''
];
$view->render('pages/rezervasyon_onay_form.php', $data);