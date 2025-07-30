<?php
namespace App\Lib;

class View
{
    private $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    
    // Bu render metodu SADECE görünüm dosyasını render etmeli ve
    // çıktısı index.php'deki ana çıktı tamponu tarafından yakalanmalıdır.
    public function render($viewFile, $data = []) // $layoutFile parametresi artık burada gerekli değil
    {
        // $data dizisindeki anahtarları değişkenlere dönüştürür
        extract($data);

        $viewPath = ROOT_PATH . '/' . $viewFile;
        // Bu kısımda ob_start/ob_get_clean yok, çünkü index.php ana çıktı tamponlamasını yönetiyor.
        // Bu kısımda layoutPath dahil etmesi de yok, çünkü index.php layout sarmalamasını yönetiyor.

        if (file_exists($viewPath)) {
            // Görünüm dosyasını doğrudan dahil et. Çıktısı index.php'deki ana çıktı tamponuna gidecek.
            include $viewPath;
        } else {
            // Görünüm dosyası bulunamazsa, 404 sayfasını doğrudan dahil et.
            http_response_code(404);
            include ROOT_PATH . '/pages/404.php';
        }
        // Geri dönüş değeri yok, çıktı doğrudan çıktı tamponuna gider.
    }
}