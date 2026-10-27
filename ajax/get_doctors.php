<?php
// ajax/get_doctors.php
// Returns JSON array of active doctors in a clinical department

header('Content-Type: application/json');
require_once dirname(__DIR__) . '/config/db.php';

$dept_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;

if ($dept_id <= 0) {
    echo json_encode([]);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT d.id, d.first_name, d.last_name, d.charge 
                           FROM doctors d 
                           JOIN departments dept ON d.department_id = dept.id 
                           WHERE dept.name = (SELECT name FROM departments WHERE id = ?) AND d.status = 'Active' 
                           ORDER BY d.first_name ASC");
    $stmt->execute([$dept_id]);
    $doctors = $stmt->fetchAll();
    
    echo json_encode($doctors);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
