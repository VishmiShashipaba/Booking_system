<?php
require_once dirname(__DIR__) . '/config/db.php';

$mapping = [
    'Amara Silva' => '5.jpeg',
    'Chathuri Silva' => '7.jpeg',
    'Kamal Fernando' => '11.jpeg',
    'Nimal Perera' => '6.jpeg',
    'Prasanna Jayamaha' => '4.jpeg',
    'Sanduni Silva' => '8.jpeg',
    'Sunil Perera' => '10.jpeg'
];

foreach ($mapping as $name => $filename) {
    $parts = explode(' ', $name);
    $first_name = $parts[0];
    $last_name = $parts[1];
    
    try {
        $stmt = $pdo->prepare("UPDATE doctors SET image_path = ? WHERE first_name = ? AND last_name = ?");
        $stmt->execute([$filename, $first_name, $last_name]);
        echo "Updated Dr. $name to image_path = $filename\n";
    } catch (Exception $e) {
        echo "Error updating Dr. $name: " . $e->getMessage() . "\n";
    }
}
?>
