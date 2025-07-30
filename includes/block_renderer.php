<?php
// includes/block_renderer.php

function render_blocks($json_content) {
    if (empty($json_content)) return;

    $clean_json = stripslashes($json_content);
    $blocks = json_decode($clean_json, true);

    if (!is_array($blocks) || json_last_error() !== JSON_ERROR_NONE) {
        echo '<div class="prose max-w-none">' . $json_content . '</div>';
        return;
    }

    foreach ($blocks as $block) {
        $type = $block['tip'] ?? 'unknown';
        $data = $block['veri'] ?? [];
        
        $block_file = __DIR__ . "/blocks/{$type}.php";

        if (file_exists($block_file)) {
            // $data değişkenini blok dosyasının içinde erişilebilir yapıyoruz.
            include $block_file;
        }
    }
}
?>