<?php
namespace App\Lib;

class BookingRepository
{
    private $pdo;
    public function __construct(\PDO $pdo) { $this->pdo = $pdo; }

    public function getBookingById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM bookings WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    public function updateBookingStatus($id, $status) {
        $stmt = $this->pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }
    public function rejectBooking($id, $reason) {
        $stmt = $this->pdo->prepare("UPDATE bookings SET status = 'rejected', rejection_reason = ? WHERE id = ?");
        return $stmt->execute([$reason, $id]);
    }
    public function createBooking(array $data) {
        $sql_find_room = "SELECT id FROM rooms WHERE room_type_id = :room_type_id AND id NOT IN (SELECT DISTINCT room_id FROM booked_rooms br JOIN bookings b ON br.booking_id = b.id WHERE NOT (b.check_out_date <= :check_in OR b.check_in_date >= :check_out)) LIMIT 1";
        $this->pdo->beginTransaction();
        try {
            $stmt_find = $this->pdo->prepare($sql_find_room);
            $stmt_find->execute([':room_type_id' => $data['room_type_id'], ':check_in' => $data['check_in'], ':check_out' => $data['check_out']]);
            $available_room = $stmt_find->fetch();
            if (!$available_room) { $this->pdo->rollBack(); return false; }
            $roomIdToBook = $available_room['id'];

            // INSERT sorgusu ve parametreleri güncellendi: currency, transfer_service, transfer_details, notes, guest_phone, country_code, guest_nationality
            $sql_insert_booking = "INSERT INTO bookings (guest_name, guest_email, guest_phone, country_code, guest_nationality, check_in_date, check_out_date, num_adults, total_price, currency, transfer_service, transfer_details, notes, status, num_children) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)";
            $stmt_booking = $this->pdo->prepare($sql_insert_booking);
            $stmt_booking->execute([
                $data['guest_name'],
                $data['guest_email'],
                $data['guest_phone'],
                $data['country_code'],
                $data['guest_nationality'],
                $data['check_in'],
                $data['check_out'],
                $data['num_adults'],
                $data['total_price'],
                $data['currency'],
                $data['transfer_service'],
                $data['transfer_details'],
                $data['notes'],
                $data['num_children'] // num_children eklendi
            ]);

            $bookingId = $this->pdo->lastInsertId();
            $stmt_book_room = $this->pdo->prepare("INSERT INTO booked_rooms (booking_id, room_id) VALUES (?, ?)");
            $stmt_book_room->execute([$bookingId, $roomIdToBook]);
            $this->pdo->commit();
            return $bookingId;
        } catch (\Exception $e) { $this->pdo->rollBack(); error_log($e->getMessage()); return false; }
    }
    
    // getDashboardStats() fonksiyonu güncellendi - Soru işaretli (?) parametreler kullanıldı
    public function getDashboardStats() {
        $stats = [];
        $today = date('Y-m-d'); 

        // Aktif Rezervasyonlar: Bugün otelde olanlar (check_in_date <= bugün VE check_out_date > bugün OLAN onaylanmış)
        $stmt_total_bookings = $this->pdo->prepare("SELECT COUNT(*) FROM bookings WHERE status = 'confirmed' AND check_in_date <= ? AND check_out_date > ?");
        $stmt_total_bookings->execute([$today, $today]); 
        $stats['total_bookings'] = $stmt_total_bookings->fetchColumn(); 

        // Bugünkü Girişler (check_in_date = bugün OLAN onaylanmış)
        $stmt_today_checkins = $this->pdo->prepare("SELECT COUNT(*) FROM bookings WHERE status = 'confirmed' AND check_in_date = ?");
        $stmt_today_checkins->execute([$today]); 
        $stats['today_checkins'] = $stmt_today_checkins->fetchColumn();

        // Onay Bekleyen (status = 'pending' OLAN)
        $stmt_pending_bookings = $this->pdo->prepare("SELECT COUNT(*) FROM bookings WHERE status = 'pending'");
        $stmt_pending_bookings->execute(); 
        $stats['pending_bookings'] = $stmt_pending_bookings->fetchColumn();

        return $stats;
    }

    public function getFilteredBookings(array $filters = [], $limit = 15, $offset = 0) {
        $sql = "SELECT * FROM bookings WHERE 1=1";
        $params = [];

        if (!empty($filters['start_date'])) { $sql .= " AND check_in_date >= ?"; $params[] = $filters['start_date']; }
        if (!empty($filters['end_date'])) { $sql .= " AND check_out_date <= ?"; $params[] = $filters['end_date']; }
        if (!empty($filters['guest_name'])) { $sql .= " AND guest_name LIKE ?"; $params[] = '%' . $filters['guest_name'] . '%'; }

        $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params); 
        return $stmt->fetchAll();
    }

    public function getFilteredBookingsCount(array $filters = []) {
        $sql = "SELECT COUNT(*) FROM bookings WHERE 1=1";
        $params = [];
        if (!empty($filters['start_date'])) { $sql .= " AND check_in_date >= ?"; $params[] = $filters['start_date']; }
        if (!empty($filters['end_date'])) { $sql .= " AND check_out_date <= ?"; $params[] = $filters['end_date']; }
        if (!empty($filters['guest_name'])) { $sql .= " AND guest_name LIKE ?"; $params[] = '%' . $filters['guest_name'] . '%'; }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
    public function getTodayIncome() {
        $stmt = $this->pdo->prepare("SELECT SUM(total_price) as income FROM bookings WHERE status = 'confirmed' AND DATE(created_at) = CURDATE()");
        $stmt->execute();
        return $stmt->fetchColumn() ?? 0;
    }
}