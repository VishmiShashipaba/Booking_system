<?php
// ajax/get_schedules.php
// Returns JSON array of doctor schedules matching the day of the week of a selected date

header('Content-Type: application/json');
require_once dirname(__DIR__) . '/config/db.php';

$doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;
$date_str = isset($_GET['date']) ? trim($_GET['date']) : '';

if ($doctor_id <= 0 || empty($date_str)) {
    echo json_encode([]);
    exit();
}

try {
    $timestamp = strtotime($date_str);
    if (!$timestamp) {
        echo json_encode(['error' => 'Invalid date format']);
        exit();
    }
    
    // Get day of the week (e.g. 'Monday', 'Tuesday', ...)
    $day_of_week = date('l', $timestamp);
    
    // Query schedules
    $stmt = $pdo->prepare("SELECT id, day_of_week, start_time, end_time, max_patients 
                           FROM doctor_schedules 
                           WHERE doctor_id = ? AND day_of_week = ?");
    $stmt->execute([$doctor_id, $day_of_week]);
    $schedules = $stmt->fetchAll();
    
    echo json_encode($schedules);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
