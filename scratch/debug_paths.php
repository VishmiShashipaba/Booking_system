<?php
// Simulate what the web server would set
$_SERVER['DOCUMENT_ROOT'] = 'C:/xampp/htdocs';
require_once dirname(__DIR__) . '/config/db.php';
echo "BASE_URL = '" . BASE_URL . "'\n";

// Now show what the img src would be for a doctor with image_path = '4.jpeg'
$img_path = '4.jpeg';
echo "Custom image URL = '" . BASE_URL . "images/" . $img_path . "'\n";
echo "Fallback image URL = '" . BASE_URL . "assets/images/doctor_male.png'\n";

// Check if images folder exists with correct files
$images_dir = dirname(__DIR__) . '/images/';
echo "\nImages directory: " . $images_dir . "\n";
echo "Directory exists: " . (is_dir($images_dir) ? 'YES' : 'NO') . "\n";

if (is_dir($images_dir)) {
    foreach (scandir($images_dir) as $f) {
        if ($f === '.' || $f === '..') continue;
        echo "  File: $f (exists: " . (file_exists($images_dir . $f) ? 'YES' : 'NO') . ")\n";
    }
}
?>
