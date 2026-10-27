<?php
require_once dirname(__DIR__) . '/config/db.php';

try {
    $stmt = $pdo->query("SELECT d.id AS doc_id, d.user_id, d.first_name, d.last_name, d.image_path, u.username, u.role FROM doctors d JOIN users u ON d.user_id = u.id");
    $res = $stmt->fetchAll();
    echo "Doctors with User IDs:\n";
    foreach ($res as $row) {
        echo "Doc ID: {$row['doc_id']}, User ID: {$row['user_id']}, Name: {$row['first_name']} {$row['last_name']}, Username: {$row['username']}, Image Path: {$row['image_path']}\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
