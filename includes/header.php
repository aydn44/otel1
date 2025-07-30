<?php
$page_title = $data['title'] ?? 'Otel Adı';

// Bu header dosyası, menüyü oluşturmak için $pdo değişkeninin ana dosyada
// (index.php, page_view.php vb.) tanımlandığını varsayar.
$menu_pages = [];
if (isset($pdo)) {
    try {
        $stmt = $pdo->query("SELECT p.slug, pt.title FROM pages p JOIN page_translations pt ON p.id = pt.page_id WHERE p.is_published = 1 AND pt.language_code = 'tr' ORDER BY p.sort_order ASC, p.id ASC");
        $menu_pages = $stmt->fetchAll();
    } catch (Exception $e) { $menu_pages = []; }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
      html, body { height: 100%; margin: 0; padding: 0; }
      #site-container { display: flex; flex-direction: column; min-height: 100%; }
      main { flex-grow: 1; }
    </style>
</head>
<body class="bg-gray-100">

<div id="site-container">

    <header class="bg-gray-800 text-white p-4 shadow-lg sticky top-0 z-50">
        <div class="container mx-auto flex justify-between items-center">
            <a href="index.php" class="text-xl font-bold">Otel Adı</a>
            <nav class="space-x-4">
                <a href="index.php" class="hover:text-yellow-400">Anasayfa</a>
                <?php foreach ($menu_pages as $page_link): ?>
                    <a href="page_view.php?slug=<?php echo htmlspecialchars($page_link['slug']); ?>" class="hover:text-yellow-400">
                        <?php echo htmlspecialchars(strtoupper($page_link['title'])); ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>
    </header>

    <div class="bg-gray-700 p-4 sticky top-[72px] z-40 shadow-md">
        <div class="container mx-auto">
            <form action="rezervasyon.php" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                <div>
                    <label class="text-white text-sm">Giriş Tarihi</label>
                    <input type="date" name="checkin" class="w-full p-2 rounded-md bg-gray-600 text-white border-gray-500" required>
                </div>
                <div>
                    <label class="text-white text-sm">Çıkış Tarihi</label>
                    <input type="date" name="checkout" class="w-full p-2 rounded-md bg-gray-600 text-white border-gray-500" required>
                </div>
                <div>
                    <label class="text-white text-sm">Yetişkin</label>
                    <select name="adults" class="w-full p-2 rounded-md bg-gray-600 text-white border-gray-500">
                        <option>1</option><option selected>2</option><option>3</option><option>4</option>
                    </select>
                </div>
                <div>
                    <label class="text-white text-sm">Çocuk</label>
                    <select name="children" class="w-full p-2 rounded-md bg-gray-600 text-white border-gray-500">
                        <option selected>0</option><option>1</option><option>2</option>
                    </select>
                </div>
                <button type="submit" class="w-full bg-yellow-500 hover:bg-yellow-600 text-black font-bold p-2 rounded-md">Oda Ara</button>
            </form>
        </div>
    </div>
    <main class="container mx-auto p-4 mt-4">