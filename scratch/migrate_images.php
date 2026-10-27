<?php
require_once dirname(__DIR__) . '/config/db.php';

$images_dir = dirname(__DIR__) . '/images/';
$assets_dir = dirname(__DIR__) . '/assets/images/';

// Mapping: Doctor Full Name -> [Source image name, Target image name]
$mapping = [
    'Amara Silva' => ['5.jpeg', 'doctor_amara.jpeg'],
    'Chathuri Silva' => ['7.jpeg', 'doctor_chathuri.jpeg'],
    'Kamal Fernando' => ['11.jpeg', 'doctor_kamal.jpeg'],
    'Nimal Perera' => ['6.jpeg', 'doctor_nimal.jpeg'],
    'Prasanna Jayamaha' => ['4.jpeg', 'doctor_prasanna.jpeg'],
    'Sanduni Silva' => ['8.jpeg', 'doctor_sanduni.jpeg'],
    'Sunil Perera' => ['10.jpeg', 'doctor_sunil.jpeg']
];

foreach ($mapping as $name => $files) {
    $src = $images_dir . $files[0];
    $dest = $assets_dir . $files[1];
    
    if (file_exists($src)) {
        if (copy($src, $dest)) {
            echo "Successfully copied {$files[0]} to {$files[1]}\n";
            
            // Split name
            $parts = explode(' ', $name);
            $first_name = $parts[0];
            $last_name = $parts[1];
            
            try {
                $stmt = $pdo->prepare("UPDATE doctors SET image_path = ? WHERE first_name = ? AND last_name = ?");
                $stmt->execute([$files[1], $first_name, $last_name]);
                if ($stmt->rowCount() > 0) {
                    echo "Updated database for Dr. {$name} with image path: {$files[1]}\n";
                } else {
                    echo "No database record updated for Dr. {$name} (might already be matching or doctor not found).\n";
                }
            } catch (Exception $e) {
                echo "Database error for Dr. {$name}: " . $e->getMessage() . "\n";
            }
        } else {
            echo "Failed to copy {$files[0]} to {$files[1]}\n";
        }
    } else {
        echo "Source file does not exist: {$src}\n";
    }
}
?>
