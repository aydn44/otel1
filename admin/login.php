<?php
require_once __DIR__ . '/../config.php';
if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/admin/dashboard.php');
    exit;
}
$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    if (empty($email) || empty($password)) {
        $error_message = 'E-posta ve şifre boş bırakılamaz.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            header('Location: ' . BASE_URL . '/admin/dashboard.php');
            exit;
        } else {
            $error_message = 'Giriş bilgileri hatalı.';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Admin Girişi</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-gray-200 h-screen flex justify-center items-center">
    <div class="bg-white p-8 rounded shadow-md w-full max-w-xs">
        <h1 class="text-2xl font-bold mb-6 text-center">Admin Girişi</h1>
        <?php if($error_message): ?><p class="bg-red-200 text-red-800 p-3 mb-4 rounded"><?php echo $error_message; ?></p><?php endif; ?>
        <form method="POST" action="<?php echo BASE_URL; ?>/admin/login.php">
            <div class="mb-4"><label class="block mb-1">E-posta</label><input type="email" name="email" class="w-full p-2 border rounded"></div>
            <div class="mb-6"><label class="block mb-1">Şifre</label><input type="password" name="password" class="w-full p-2 border rounded"></div>
            <button type="submit" class="w-full bg-blue-500 text-white p-2 rounded">Giriş Yap</button>
        </form>
    </div>
</body>
</html>