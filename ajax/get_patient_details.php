<?php
// ajax/get_patient_details.php
// Lookup patient profile by NIC. Returns JSON data to auto-fill walk-in forms.

header('Content-Type: application/json');
require_once dirname(__DIR__) . '/config/db.php';

$nic = isset($_GET['nic']) ? trim($_GET['nic']) : '';

if (empty($nic)) {
    echo json_encode(['found' => false]);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT id, patient_number, first_name, last_name, phone, address, dob, gender 
                           FROM patients 
                           WHERE nic = ?");
    $stmt->execute([$nic]);
    $patient = $stmt->fetch();
    
    if ($patient) {
        echo json_encode([
            'found' => true,
            'patient' => $patient
        ]);
    } else {
        echo json_encode(['found' => false]);
    }
} catch (PDOException $e) {
    echo json_encode(['found' => false, 'error' => $e->getMessage()]);
}
?>
