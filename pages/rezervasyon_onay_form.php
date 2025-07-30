<?php if ($success_message): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-6 rounded-lg shadow-md text-center">
        <h1 class="text-2xl font-bold mb-2">Teşekkür Ederiz!</h1>
        <p><?php echo htmlspecialchars($success_message); ?></p>
        <a href="index.php" class="mt-4 inline-block bg-blue-600 text-white font-bold py-2 px-6 rounded hover:bg-blue-700">Ana Sayfaya Dön</a>
    </div>
<?php else: ?>
    <form method="POST">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2">
                <div class="bg-white p-8 rounded-lg shadow-md">
                    <h2 class="text-2xl font-bold mb-6">Misafir Bilgileri</h2>
                    <?php if ($error_message): ?>
                        <div class="bg-red-100 text-red-700 p-4 rounded-md mb-6"><?php echo htmlspecialchars($error_message); ?></div>
                    <?php endif; ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block font-semibold">Ad Soyad (*)</label>
                            <input type="text" name="guest_name" value="<?php echo htmlspecialchars($guest_name); ?>" class="w-full p-2 border rounded mt-1" required>
                        </div>
                        <div>
                            <label class="block font-semibold">E-posta Adresi (*)</label>
                            <input type="email" name="guest_email" value="<?php echo htmlspecialchars($guest_email); ?>" class="w-full p-2 border rounded mt-1" required>
                        </div>
                        <div>
                            <label class="block font-semibold">Ülke Kodu</label>
                            <select name="country_code" class="w-full p-2 border rounded mt-1">
                                <?php foreach (get_country_codes() as $code => $name): ?>
                                    <option value="<?php echo htmlspecialchars($code); ?>" <?php if($code == $country_code) echo 'selected'; ?>><?php echo htmlspecialchars($name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold">Telefon</label>
                            <input type="tel" name="guest_phone" value="<?php echo htmlspecialchars($guest_phone); ?>" class="w-full p-2 border rounded mt-1">
                        </div>
                        <div>
                            <label class="block font-semibold">Uyruk</label>
                            <select name="guest_nationality" class="w-full p-2 border rounded mt-1">
                                <?php foreach (get_country_list() as $code => $name): ?>
                                    <option value="<?php echo htmlspecialchars($name); ?>" <?php if($name == $guest_nationality) echo 'selected'; ?>><?php echo htmlspecialchars($name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mt-6">
                        <label class="block font-semibold">Özel Notlarınız</label>
                        <textarea name="notes" rows="3" class="w-full p-2 border rounded mt-1"><?php echo htmlspecialchars($notes); ?></textarea>
                    </div>

                    <div class="mt-6">
                        <label class="flex items-center">
                            <input type="checkbox" name="transfer_service" value="1" class="h-5 w-5" <?php echo ($transfer_service_checked ? 'checked' : ''); ?>>
                            <span class="ml-2">Transfer Hizmeti İstiyorum</span>
                        </label>
                        <textarea name="transfer_details" rows="2" class="w-full p-2 border rounded mt-2" placeholder="Lütfen transfer detaylarınızı (örn: uçuş numarası, otogar, alınacak adres vb.) buraya yazınız..."><?php echo htmlspecialchars($transfer_details); ?></textarea>
                    </div>
                </div>
            </div>
            <div class="lg:col-span-1">
                <div class="bg-white p-8 rounded-lg shadow-md sticky top-24">
                    <h2 class="text-2xl font-bold mb-4 border-b pb-4">Rezervasyon Özeti</h2>
                    <img src="<?php echo BASE_URL . '/uploads/' . htmlspecialchars($room_type['featured_image']); ?>" class="rounded-lg mb-4">
                    <h3 class="text-xl font-semibold"><?php echo htmlspecialchars($room_type['name']); ?></h3>
                    <div class="text-gray-600 mt-4 space-y-2">
                        <p><strong>Giriş:</strong> <?php echo date('d M Y', strtotime($checkin)); ?></p>
                        <p><strong>Çıkış:</strong> <?php echo date('d M Y', strtotime($checkout)); ?></p>
                        <p><strong>Süre:</strong> <?php echo $night_count; ?> Gece</p>
                        <p><strong>Kişi:</strong> <?php echo $adults; ?> Yetişkin</p>
                        <p><strong>Çocuk:</strong> <?php echo $children; ?> Çocuk</p> </div>
                    <div class="mt-4 pt-4 border-t">
                        <p class="flex justify-between"><span>Oda Fiyatı (<?php echo $night_count; ?> gece)</span> <span><?php echo number_format($total_price, 2); ?> <?php echo htmlspecialchars($room_currency); ?></span></p>
                        <p class="flex justify-between text-2xl font-bold mt-2"><span>TOPLAM</span> <span><?php echo number_format($total_price, 2); ?> <?php echo htmlspecialchars($room_currency); ?></span></p>
                    </div>
                    <button type="submit" class="w-full mt-6 bg-blue-600 text-white font-bold py-3 rounded-lg hover:bg-blue-700">Onayla ve Bitir</button>
                </div>
            </div>
        </div>
    </form>
<?php endif; ?>