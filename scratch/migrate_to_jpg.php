<?php
require_once dirname(__DIR__) . '/config/db.php';

$assets_dir = dirname(__DIR__) . '/assets/images/';

// Mapping: Doctor Full Name -> target .jpg name
$mapping = [
    'Amara Silva' => 'doctor_amara.jpg',
    'Chathuri Silva' => 'doctor_chathuri.jpg',
    'Kamal Fernando' => 'doctor_kamal.jpg',
    'Nimal Perera' => 'doctor_nimal.jpg',
    'Prasanna Jayamaha' => 'doctor_prasanna.jpg',
    'Sanduni Silva' => 'doctor_sanduni.jpg',
    'Sunil Perera' => 'doctor_sunil.jpg'
];

foreach ($mapping as $name => $jpg_name) {
    $jpeg_name = str_replace('.jpg', '.jpeg', $jpg_name);
    
    $src = $assets_dir . $jpeg_name;
    $dest = $assets_dir . $jpg_name;
    
    // Check if .jpeg exists and rename to .jpg
    if (file_exists($src)) {
        if (rename($src, $dest)) {
            echo "Renamed $jpeg_name to $jpg_name\n";
        } else {
            echo "Failed to rename $jpeg_name to $jpg_name\n";
        }
    } else {
        echo "File $jpeg_name does not exist, checking if $jpg_name already exists...\n";
    }
    
    // Update database
    $parts = explode(' ', $name);
    $first_name = $parts[0];
    $last_name = $parts[1];
    
    try {
        $stmt = $pdo->prepare("UPDATE doctors SET image_path = ? WHERE first_name = ? AND last_name = ?");
        $stmt->execute([$jpg_name, $first_name, $last_name]);
        echo "Updated database for Dr. {$name} with image path: {$jpg_name}\n";
    } catch (Exception $e) {
        echo "Database error for Dr. {$name}: " . $e->getMessage() . "\n";
    }
}
?>
