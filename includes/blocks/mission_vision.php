<?php
$m_title = $data['mission_title'] ?? 'Misyonumuz';
$v_title = $data['vision_title'] ?? 'Vizyonumuz';
$m_text = $data['mission_text'] ?? '';
$v_text = $data['vision_text'] ?? '';
?>
<section class="bg-gray-100 py-16">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
            <div>
                <h3 class="text-3xl font-bold text-gray-800 mb-4"><?php echo htmlspecialchars($m_title); ?></h3>
                <div class="prose max-w-none text-gray-600"><?php echo nl2br(htmlspecialchars($m_text)); ?></div>
            </div>
            <div>
                <h3 class="text-3xl font-bold text-gray-800 mb-4"><?php echo htmlspecialchars($v_title); ?></h3>
                <div class="prose max-w-none text-gray-600"><?php echo nl2br(htmlspecialchars($v_text)); ?></div>
            </div>
        </div>
    </div>
</section>