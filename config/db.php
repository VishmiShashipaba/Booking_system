<?php
// config/db.php
// Database configuration and base URL path auto-detection for Prasanna Channel Center

$host = '127.0.0.1';
$db   = 'prasanna_channel_center';
$user = 'root';
$pass = ''; // Default XAMPP MySQL password is empty
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     die("Database connection failed: " . $e->getMessage());
}

// Auto-detect project base directory relative path
$doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$dir_path = str_replace('\\', '/', dirname(__DIR__));

$base_path = str_ireplace($doc_root, '', $dir_path);
if ($base_path === $dir_path) {
    // Fallback if document root match fails
    $base_path = '/';
} else {
    $base_path = '/' . trim($base_path, '/') . '/';
    $base_path = str_replace('//', '/', $base_path);
}

define('BASE_URL', $base_path);
?>
