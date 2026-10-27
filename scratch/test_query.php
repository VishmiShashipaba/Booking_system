<?php
require_once dirname(__DIR__) . '/config/db.php';

try {
    $stmt = $pdo->query("SELECT * FROM departments");
    $departments = $stmt->fetchAll();
    echo "Departments list:\n";
    print_r($departments);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
