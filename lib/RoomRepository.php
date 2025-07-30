<?php
namespace App\Lib;

class RoomRepository
{
    private $pdo;
    public function __construct(\PDO $pdo) { $this->pdo = $pdo; }

    public function getAllRoomTypes() {
        return $this->pdo->query("SELECT rt.id, rtt.name FROM room_types rt JOIN room_type_translations rtt ON rt.id = rtt.room_type_id WHERE rtt.language_code = 'tr' ORDER BY rt.id")->fetchAll();
    }
    public function getRoomTypeById($id) {
        $stmt = $this->pdo->prepare("SELECT rt.*, rtt.name, rtt.description FROM room_types rt LEFT JOIN room_type_translations rtt ON rt.id = rtt.room_type_id AND rtt.language_code = 'tr' WHERE rt.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    public function updateRoomType($id, $data) {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("UPDATE room_types SET capacity = ?, base_price = ?, currency = ?, featured_image = ? WHERE id = ?");
            $stmt->execute([$data['capacity'], $data['base_price'], $data['currency'], $data['image_name'], $id]);
            $stmt_trans = $this->pdo->prepare("UPDATE room_type_translations SET name = ?, description = ? WHERE room_type_id = ? AND language_code = 'tr'");
            $stmt_trans->execute([$data['name'], $data['description'], $id]);
            $this->pdo->commit();
            return true;
        } catch (\Exception $e) { $this->pdo->rollBack(); return false; }
    }
    public function getAllPhysicalRooms() {
        return $this->pdo->query("SELECT r.id, r.room_number, r.status, rtt.name as type_name FROM rooms r JOIN room_types rt ON r.room_type_id = rt.id JOIN room_type_translations rtt ON rt.id = rtt.room_type_id WHERE rtt.language_code = 'tr' ORDER BY r.room_number ASC")->fetchAll();
    }
    public function updateRoomStatus($roomId, $status) {
        $stmt = $this->pdo->prepare("UPDATE rooms SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $roomId]);
    }
    public function getTotalRoomsCount() { return $this->pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn(); }
    public function getOccupiedRoomsCount() { return $this->pdo->query("SELECT COUNT(*) FROM rooms WHERE status = 'occupied'")->fetchColumn(); }
    public function getFeaturedRoomTypes($limit = 3) {
        $sql = "SELECT rt.id, rtt.name, rt.base_price, rt.currency, rt.featured_image, rtt.description FROM room_types rt JOIN room_type_translations rtt ON rt.id = rtt.room_type_id WHERE rtt.language_code = 'tr' ORDER BY rt.base_price ASC LIMIT :limit";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Yeni bir fiziksel oda ekler.
     * @param string $roomNumber Oda numarası.
     * @param int $roomTypeId Oda tipi ID'si.
     * @return bool Başarılı olursa true.
     */
    public function addPhysicalRoom($roomNumber, $roomTypeId) {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO rooms (room_number, room_type_id, status) VALUES (?, ?, 'available')");
            return $stmt->execute([$roomNumber, $roomTypeId]);
        } catch (\Exception $e) {
            error_log('Fiziksel oda ekleme hatası: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Bir fiziksel odanın bilgilerini günceller.
     * @param int $roomId Güncellenecek oda ID'si.
     * @param string $roomNumber Yeni oda numarası.
     * @param int $roomTypeId Yeni oda tipi ID'si.
     * @return bool Başarılı olursa true.
     */
    public function updatePhysicalRoom($roomId, $roomNumber, $roomTypeId) {
        try {
            $stmt = $this->pdo->prepare("UPDATE rooms SET room_number = ?, room_type_id = ? WHERE id = ?");
            return $stmt->execute([$roomNumber, $roomTypeId, $roomId]);
        } catch (\Exception $e) {
            error_log('Fiziksel oda güncelleme hatası: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Bir fiziksel odayı siler.
     * @param int $roomId Silinecek oda ID'si.
     * @return bool Başarılı olursa true.
     */
    public function deletePhysicalRoom($roomId) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM rooms WHERE id = ?");
            return $stmt->execute([$roomId]);
        } catch (\Exception $e) {
            error_log('Fiziksel oda silme hatası: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Belirli bir fiziksel odanın detaylarını getirir.
     * @param int $roomId Oda ID'si.
     * @return array|false Oda detayları veya false.
     */
    public function getPhysicalRoomById($roomId) {
        $stmt = $this->pdo->prepare("SELECT * FROM rooms WHERE id = ?");
        $stmt->execute([$roomId]);
        return $stmt->fetch();
    }
}