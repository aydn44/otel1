<?php
// $data değişkeni render_blocks fonksiyonundan geliyor.
$image_url = $data['hero_image'] ?? '/uploads/placeholder.png';
$title = $data['hero_title'] ?? 'Başlık Girilmedi';
?>
<section class="relative bg-cover bg-center h-screen flex items-center justify-center text-white" style="background-image: url('<?php echo BASE_URL . htmlspecialchars($image_url); ?>');">
    <div class="absolute inset-0 bg-black opacity-50"></div>
    <div class="relative z-10 text-center px-4">
        <h1 class="text-5xl md:text-7xl font-extrabold"><?php echo htmlspecialchars($title); ?></h1>
    </div>
</section>