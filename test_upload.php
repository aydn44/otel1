<?php
// test_upload.php - Ana dizine yerleştirin
echo "<h3>PHP Upload Ayarları</h3>";
echo "Upload Max Filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "Post Max Size: " . ini_get('post_max_size') . "<br>";
echo "Max Execution Time: " . ini_get('max_execution_time') . "<br>";
echo "Memory Limit: " . ini_get('memory_limit') . "<br>";
echo "File Uploads: " . (ini_get('file_uploads') ? 'Enabled' : 'Disabled') . "<br>";

echo "<h3>Klasör Kontrolleri</h3>";
echo "Ana Dizin: " . __DIR__ . "<br>";
echo "Uploads Klasörü Var mı: " . (is_dir('uploads') ? 'EVET' : 'HAYIR') . "<br>";
echo "Uploads Klasörü Yazılabilir mi: " . (is_writable('uploads') ? 'EVET' : 'HAYIR') . "<br>";
echo "Admin Klasörü Var mı: " . (is_dir('admin') ? 'EVET' : 'HAYIR') . "<br>";

echo "<h3>Dosya Kontrolleri</h3>";
echo "upload_image.php Var mı: " . (file_exists('admin/upload_image.php') ? 'EVET' : 'HAYIR') . "<br>";
echo ".htaccess Var mı: " . (file_exists('.htaccess') ? 'EVET' : 'HAYIR') . "<br>";

echo "<h3>BASE_URL</h3>";
if (defined('BASE_URL')) {
    echo "BASE_URL: " . BASE_URL . "<br>";
} else {
    echo "BASE_URL tanımlı değil<br>";
}

echo "<h3>Test Upload Formu</h3>";
?>

<form action="admin/upload_image.php" method="post" enctype="multipart/form-data" target="_blank">
    <input type="file" name="upload" accept="image/*" required>
    <button type="submit">Test Upload</button>
</form>

<script>
console.log('Test sayfası yüklendi');
</script>