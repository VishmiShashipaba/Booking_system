<?php
require_once dirname(__DIR__) . '/config/db.php';

try {
    $stmt = $pdo->query("SELECT id, username, role FROM users");
    $users = $stmt->fetchAll();
    echo "All Users:\n";
    print_r($users);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
