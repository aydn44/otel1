<?php
namespace App\Lib;

class ServiceRepository
{
    private $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Tüm hizmetleri ve Türkçe çevirilerini getirir.
     * @return array
     */
    public function getAllServices() {
        $stmt = $this->pdo->prepare("
            SELECT s.id, s.price, s.icon_class, s.description_tr, st.name
            FROM services s
            JOIN service_translations st ON s.id = st.service_id
            WHERE st.language_code = 'tr'
            ORDER BY s.id DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * ID'ye göre bir hizmeti ve Türkçe çevirisini getirir.
     * @param int $id
     * @return array|false
     */
    public function getServiceById($id) {
        $stmt = $this->pdo->prepare("
            SELECT s.id, s.price, s.icon_class, s.description_tr, st.name
            FROM services s
            LEFT JOIN service_translations st ON s.id = st.service_id AND st.language_code = 'tr'
            WHERE s.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Yeni bir hizmet ekler.
     * @param array $data Hizmet verileri (name, price, icon_class, description_tr)
     * @return bool
     */
    public function createService(array $data) {
        $this->pdo->beginTransaction();
        try {
            // services tablosuna ekle
            $stmt = $this->pdo->prepare("INSERT INTO services (price, icon_class, description_tr) VALUES (?, ?, ?)");
            $stmt->execute([$data['price'], $data['icon_class'], $data['description_tr']]);
            $serviceId = $this->pdo->lastInsertId();

            // service_translations tablosuna ekle (Türkçe için)
            $stmt_trans = $this->pdo->prepare("INSERT INTO service_translations (service_id, language_code, name) VALUES (?, 'tr', ?)");
            $stmt_trans->execute([$serviceId, $data['name']]);

            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            error_log('Hizmet oluşturma hatası: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Mevcut bir hizmeti günceller.
     * @param int $id
     * @param array $data Hizmet verileri (name, price, icon_class, description_tr)
     * @return bool
     */
    public function updateService($id, array $data) {
        $this->pdo->beginTransaction();
        try {
            // services tablosunu güncelle
            $stmt = $this->pdo->prepare("UPDATE services SET price = ?, icon_class = ?, description_tr = ? WHERE id = ?");
            $stmt->execute([$data['price'], $data['icon_class'], $data['description_tr'], $id]);

            // service_translations tablosunu güncelle veya ekle (Türkçe için)
            $stmt_check = $this->pdo->prepare("SELECT service_id FROM service_translations WHERE service_id = ? AND language_code = 'tr'");
            $stmt_check->execute([$id]);
            if ($stmt_check->fetch()) {
                $stmt_trans = $this->pdo->prepare("UPDATE service_translations SET name = ? WHERE service_id = ? AND language_code = 'tr'");
                $stmt_trans->execute([$data['name'], $id]);
            } else {
                // Eğer çeviri yoksa, yeni bir kayıt ekle
                $stmt_trans = $this->pdo->prepare("INSERT INTO service_translations (service_id, language_code, name) VALUES (?, 'tr', ?)");
                $stmt_trans->execute([$id, $data['name']]);
            }

            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            error_log('Hizmet güncelleme hatası: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Bir hizmeti siler.
     * @param int $id
     * @return bool
     */
    public function deleteService($id) {
        // service_translations kayıtları ON DELETE CASCADE ile silineceği varsayılıyor.
        try {
            $stmt = $this->pdo->prepare("DELETE FROM services WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (\Exception $e) {
            error_log('Hizmet silme hatası: ' . $e->getMessage());
            return false;
        }
    }
}