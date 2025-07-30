<?php
$title = $data['baslik'] ?? '';
$paragraph = $data['intro_paragraph_1'] ?? '';
$image_url = $data['side_image'] ?? '';
?>
<section class="bg-white py-16"><div class="container mx-auto px-4 grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
        <div><h2 class="text-4xl font-bold text-gray-900 mb-6"><?php echo htmlspecialchars($title); ?></h2><div class="prose max-w-none text-lg text-gray-700"><?php echo nl2br(htmlspecialchars($paragraph)); ?></div></div>
        <?php if($image_url): ?><div><img src="<?php echo BASE_URL . htmlspecialchars($image_url); ?>" alt="<?php echo htmlspecialchars($title); ?>" class="rounded-lg shadow-xl w-full"></div><?php endif; ?>
</div></section>