<?php
// ajax/get_doctor_days.php
header('Content-Type: application/json');
require_once dirname(__DIR__) . '/config/db.php';

$doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;

if ($doctor_id <= 0) {
    echo json_encode([]);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT DISTINCT day_of_week FROM doctor_schedules WHERE doctor_id = ? ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')");
    $stmt->execute([$doctor_id]);
    $days = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo json_encode($days);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
