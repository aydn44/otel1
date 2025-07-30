<div class="bg-white p-6 md:p-8 rounded-lg shadow-md">
    <?php if ($error_message): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md mb-6"><?php echo htmlspecialchars($error_message); ?></div>
    <?php elseif (empty($available_room_types) && $checkin && $checkout): ?>
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 p-4 rounded-md">Belirttiğiniz tarihler için uygun oda bulunamadı.</div>
    <?php elseif (empty($available_room_types) && (!$checkin || !$checkout)): ?>
        <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-800 p-4 rounded-md">Lütfen giriş ve çıkış tarihleri ile kişi sayısını seçerek uygun odaları arayın.</div>
    <?php else: /* (!empty($available_room_types)) */ ?>
        <div class="space-y-6">
            <?php foreach ($available_room_types as $room): ?>
            <div class="border rounded-lg p-4 flex flex-col md:flex-row items-center gap-6 transition hover:shadow-lg">
                <img src="<?php echo BASE_URL . '/uploads/' . htmlspecialchars($room['featured_image'] ?: 'placeholder.png'); ?>" alt="<?php echo htmlspecialchars($room['name']); ?>" class="w-full md:w-64 h-48 object-cover rounded-md flex-shrink-0">
                <div class="flex-grow">
                    <h2 class="text-2xl font-semibold text-gray-900"><?php echo htmlspecialchars($room['name']); ?></h2>
                    <p class="text-gray-600 mt-1"><?php echo htmlspecialchars($room['description']); ?></p>
                </div>
                <div class="text-center md:text-right flex-shrink-0 md:pl-6">
                    <p class="text-3xl font-bold text-blue-600"><?php echo htmlspecialchars(number_format($room['base_price'], 2)) . ' ' . htmlspecialchars($room['currency']); ?></p>
                    <p class="text-sm text-gray-500">/ gecelik</p>
                    <a href="rezervasyon_onay.php?oda_id=<?php echo $room['id']; ?>&checkin=<?php echo htmlspecialchars($checkin); ?>&checkout=<?php echo htmlspecialchars($checkout); ?>&adults=<?php echo htmlspecialchars($adults); ?>&children=<?php echo htmlspecialchars($children); ?>" class="mt-2 inline-block w-full md:w-auto bg-blue-600 text-white font-bold py-3 px-6 rounded-lg hover:bg-blue-700 transition-colors">Şimdi Rezerve Et</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>