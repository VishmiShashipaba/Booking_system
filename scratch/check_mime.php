<?php
$dir = dirname(__DIR__) . '/images/';
$files = scandir($dir);
foreach ($files as $file) {
    if ($file === '.' || $file === '..') continue;
    $path = $dir . $file;
    $img_info = getimagesize($path);
    echo "File: $file | MIME: " . ($img_info['mime'] ?? 'unknown') . "\n";
}
?>
