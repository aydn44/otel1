<?php
namespace App\Lib;

class SettingsRepository
{
    private $pdo;
    public function __construct(\PDO $pdo) { $this->pdo = $pdo; }

    public function getAllSettings()
    {
        return $this->pdo->query("SELECT setting_key, setting_value FROM settings")
                         ->fetchAll(\PDO::FETCH_KEY_PAIR);
    }

    public function updateSettings(array $settings)
    {
        $this->pdo->beginTransaction();
        try {
            $upsert_stmt = $this->pdo->prepare(
                "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
            );
            
            foreach ($settings as $key => $value) {
                $upsert_stmt->execute([$key, $value]);
            }
            
            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            error_log('Ayarlar güncellenirken hata: ' . $e->getMessage());
            return false;
        }
    }

    // === YENİ EKLENEN FONKSİYON ===
    /**
     * Veritabanından tek bir ayar değerini çeker.
     * @param string $key Ayar anahtarı (örn: 'site_logo')
     * @return string|false
     */
    public function getSetting($key)
    {
        $stmt = $this->pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        return $stmt->fetchColumn();
    }
    // =============================
    
    public function getContactSettings()
    {
        $keys = [
            'contact_phone',
            'whatsapp_number',
            'contact_map_iframe',
            'contact_recipient_email'
        ];
        
        $settings = [];
        foreach ($keys as $key) {
            $value = $this->getSetting($key); // Artık yeni fonksiyonumuzu kullanabiliriz
            if ($value !== false) { 
                $settings[$key] = $value; 
            } else {
                $settings[$key] = ''; // Değer bulunamazsa boş döndür
            }
        }
        return $settings;
    }
}