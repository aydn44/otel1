<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// HATA RAPORLAMAYI AÇIK HALE GETİRİYORUZ (SADECE TEST AMAÇLI - SORUN ÇÖZÜLÜNCE KAPATMAYI UNUTMAYIN)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
define('BASE_URL', $protocol . "://" . $_SERVER['HTTP_HOST']);
define('ROOT_PATH', dirname(__FILE__));

define('DB_HOST', 'localhost');
define('DB_NAME', 'u226523160_marina');
define('DB_USER', 'u226523160_marina');
define('DB_PASS', '31Ultra31.');
define('DB_CHAR', 'utf8mb4');

global $pdo; $pdo = null;
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHAR;
    $options = [ PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (\PDOException $e) {
    error_log('Veritabanı Hatası: ' . $e->getMessage());
    die('<h1>Sistem Hatası</h1><p>Veritabanı bağlantısında bir sorun oluştu. Detaylar için hata günlüklerini kontrol edin.</p>');
}