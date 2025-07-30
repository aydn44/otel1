<?php
$current_file = basename($_SERVER['PHP_SELF']);
?>
<div class="w-64 bg-gray-800 text-white flex-shrink-0">
    <div class="p-6 text-2xl font-bold border-b border-gray-700">
        <a href="dashboard.php">Otel Yönetimi</a>
    </div>
    <nav class="p-4 space-y-2">
        <a href="dashboard.php" class="flex items-center py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 sidebar-link <?php if($current_file == 'dashboard.php') echo 'active'; ?>">
            <i class="fas fa-tachometer-alt w-6 mr-2"></i>Kontrol Paneli
        </a>
        <a href="reservations.php" class="flex items-center py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 sidebar-link <?php if($current_file == 'reservations.php') echo 'active'; ?>">
            <i class="fas fa-calendar-check w-6 mr-2"></i>Rezervasyonlar
        </a>
        <a href="inquiries.php" class="flex items-center py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 sidebar-link <?php if($current_file == 'inquiries.php') echo 'active'; ?>">
            <i class="fas fa-inbox w-6 mr-2"></i>Gelen Talepler
        </a>
        <a href="room-management.php" class="flex items-center py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 sidebar-link <?php if($current_file == 'room-management.php') echo 'active'; ?>">
            <i class="fas fa-bed w-6 mr-2"></i>Oda Yönetimi
        </a>
        <a href="page-management.php" class="flex items-center py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 sidebar-link <?php if($current_file == 'page-management.php') echo 'active'; ?>">
            <i class="fas fa-file-alt w-6 mr-2"></i>Sayfa Yönetimi
        </a>
        <a href="events.php" class="flex items-center py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 sidebar-link <?php if($current_file == 'events.php') echo 'active'; ?>">
            <i class="fas fa-calendar-star w-6 mr-2"></i>Etkinlik Yönetimi
        </a>
         <a href="service-management.php" class="flex items-center py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 sidebar-link <?php if($current_file == 'service-management.php') echo 'active'; ?>">
            <i class="fas fa-concierge-bell w-6 mr-2"></i>Hizmet Yönetimi
        </a>
        <a href="gallery.php" class="flex items-center py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 sidebar-link <?php if($current_file == 'gallery.php') echo 'active'; ?>">
            <i class="fas fa-images w-6 mr-2"></i>Galeri Yönetimi
        </a>
        <a href="gallery_categories.php" class="flex items-center py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 sidebar-link <?php if($current_file == 'gallery_categories.php') echo 'active'; ?>">
            <i class="fas fa-tags w-6 mr-2"></i>Galeri Kategorileri
        </a>
        <a href="media-library.php" class="flex items-center py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 sidebar-link <?php if($current_file == 'media-library.php') echo 'active'; ?>">
            <i class="fas fa-photo-video w-6 mr-2"></i>Medya Kütüphanesi
        </a>
        <a href="mail-settings.php" class="flex items-center py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 sidebar-link <?php if($current_file == 'mail-settings.php') echo 'active'; ?>">
            <i class="fas fa-envelope w-6 mr-2"></i>Mail Ayarları
        </a>
        <a href="site-settings.php" class="flex items-center py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 sidebar-link <?php if($current_file == 'site-settings.php') echo 'active'; ?>">
            <i class="fas fa-cogs w-6 mr-2"></i>Site Ayarları
        </a>
        <a href="logout.php" class="flex items-center py-2.5 px-4 rounded transition duration-200 hover:bg-red-700 text-red-400 hover:text-white mt-8">
            <i class="fas fa-sign-out-alt w-6 mr-2"></i>Çıkış Yap
        </a>
    </nav>
</div>