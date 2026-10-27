<?php
// doctor/prescription.php
// Add digital prescriptions for consulted patients

$page_title = "Write Prescription";
require_once dirname(__DIR__) . '/includes/dashboard_header.php';

checkAccess(['doctor']);

$doctor_id = $_SESSION['doctor_id'];
$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');

$error = "";
$success = "";
$appt_id = isset($_GET['appointment_id']) ? intval($_GET['appointment_id']) : 0;
$appointment = null;

// 1. Fetch checked-in appointments for dropdown (backup selection)
try {
    $active_stmt = $pdo->prepare("SELECT a.id, a.appointment_number, p.first_name, p.last_name, p.patient_number 
                                   FROM appointments a
                                   JOIN patients p ON a.patient_id = p.id
                                   WHERE a.doctor_id = ? AND a.appointment_date = ? AND a.status = 'Checked-In'
                                   ORDER BY a.queue_number ASC");
    $active_stmt->execute([$doctor_id, $today]);
    $active_consultations = $active_stmt->fetchAll();
} catch (PDOException $e) {
    $active_consultations = [];
}

// 2. Fetch specific appointment details if ID is provided
if ($appt_id > 0) {
    try {
        $appt_stmt = $pdo->prepare("SELECT a.*, p.first_name, p.last_name, p.patient_number, p.id AS patient_primary_id, p.user_id AS pat_user_id
                                    FROM appointments a
                                    JOIN patients p ON a.patient_id = p.id
                                    WHERE a.id = ? AND a.doctor_id = ? AND a.appointment_date = ? AND a.status = 'Checked-In'");
        $appt_stmt->execute([$appt_id, $doctor_id, $today]);
        $appointment = $appt_stmt->fetch();
        
        if (!$appointment) {
            $error = "Selected consultation slot is not active or has not been Checked-In.";
            $appt_id = 0;
        }
    } catch (PDOException $e) {
        $error = "Failed to load appointment details: " . $e->getMessage();
        $appt_id = 0;
    }
}

// 3. Process Prescription form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_prescription'])) {
    $submit_appt_id = intval($_POST['appointment_id'] ?? 0);
    $diagnosis = trim($_POST['diagnosis'] ?? '');
    $medicines = trim($_POST['medicines'] ?? '');
    $instructions = trim($_POST['instructions'] ?? '');

    if ($submit_appt_id <= 0 || empty($diagnosis) || empty($medicines)) {
        $error = "Please fill in the Diagnosis and Medicine list.";
    } else {
        try {
            // Re-fetch details to verify and get patient ID
            $stmt = $pdo->prepare("SELECT a.patient_id, a.appointment_number, p.user_id 
                                   FROM appointments a 
                                   JOIN patients p ON a.patient_id = p.id
                                   WHERE a.id = ? AND a.doctor_id = ?");
            $stmt->execute([$submit_appt_id, $doctor_id]);
            $appt_record = $stmt->fetch();

            if ($appt_record) {
                $pdo->beginTransaction();

                // A. Insert into prescriptions table
                $pres_stmt = $pdo->prepare("INSERT INTO prescriptions (appointment_id, doctor_id, patient_id, diagnosis, medicines, instructions) VALUES (?, ?, ?, ?, ?, ?)");
                $pres_stmt->execute([$submit_appt_id, $doctor_id, $appt_record['patient_id'], $diagnosis, $medicines, $instructions]);

                // B. Update appointment status to Completed
                $up_stmt = $pdo->prepare("UPDATE appointments SET status = 'Completed' WHERE id = ?");
                $up_stmt->execute([$submit_appt_id]);

                // C. Notify Patient
                $notif_msg = "A new digital prescription has been issued for your consultation {$appt_record['appointment_number']} by Dr. {$_SESSION['full_name']}. You can view and print this prescription from your dashboard.";
                $ins_stmt = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                $ins_stmt->execute([$appt_record['user_id'], $notif_msg]);

                $pdo->commit();
                
                // Redirect back to today's queue with success message
                header("Location: " . BASE_URL . "doctor/index.php?presc=success");
                exit();
            } else {
                $error = "Validation mismatch. Please try again.";
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Failed to save prescription: " . $e->getMessage();
        }
    }
}
?>

<div class="db-card" style="max-width: 800px; margin: 0 auto;">
    <div class="db-card-header">
        <h3><i class="fa-solid fa-file-prescription text-primary"></i> Prescription Composer</h3>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <!-- A. Select patient if not pre-provided -->
    <?php if ($appt_id <= 0): ?>
        <div style="background-color: var(--secondary-color); padding: 25px; border-radius: 8px; margin-bottom: 25px; border: 1px solid var(--light-grey);">
            <h4 style="margin-bottom: 15px;"><i class="fa-solid fa-clipboard-question"></i> Select Checked-In Patient</h4>
            <form action="" method="GET">
                <div class="form-group">
                    <label for="appointment_id">Active Consultations</label>
                    <select name="appointment_id" id="appointment_id" onchange="this.form.submit()" class="form-control">
                        <option value="">-- Choose Patient --</option>
                        <?php foreach ($active_consultations as $active): ?>
                            <option value="<?php echo $active['id']; ?>">
                                <?php echo htmlspecialchars($active['first_name'] . ' ' . $active['last_name']); ?> (Appt: <?php echo $active['appointment_number']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
            <p style="font-size:0.8rem; color:var(--grey-color); margin:0;">Note: Only patients checked in by the receptionist appear here. Set the patient to "Checked-In" from Today's Queue first.</p>
        </div>
    <?php endif; ?>

    <!-- B. Write Prescription form -->
    <?php if ($appointment): ?>
        <div style="background-color: #fafbfc; border: 1.5px solid var(--light-grey); border-radius: 8px; padding: 20px; margin-bottom: 25px;">
            <h4 style="margin-bottom: 12px; color:var(--primary-color);">Consultation Header Summary</h4>
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; font-size:0.9rem;">
                <div><strong>Patient Name:</strong> <?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?></div>
                <div><strong>Patient Number:</strong> <?php echo htmlspecialchars($appointment['patient_number']); ?></div>
                <div><strong>Appointment No:</strong> <?php echo htmlspecialchars($appointment['appointment_number']); ?></div>
                <div><strong>Queue Number:</strong> #<?php echo htmlspecialchars($appointment['queue_number']); ?></div>
            </div>
        </div>

        <form action="" method="POST">
            <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">

            <div class="form-group">
                <label for="diagnosis">Diagnosis / Findings *</label>
                <input type="text" name="diagnosis" id="diagnosis" required class="form-control" placeholder="e.g. Essential Hypertension / Common Flu / Mild Gastroesophageal Reflux" value="<?php echo isset($_POST['diagnosis']) ? htmlspecialchars($_POST['diagnosis']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="medicines">Prescribed Drugs & Dosages *</label>
                <textarea name="medicines" id="medicines" rows="8" required class="form-control" style="font-family: monospace;" placeholder="Format example:&#10;1. Paracetamol 500mg - 2 tabs - 3 times daily - 5 days&#10;2. Amoxicillin 250mg - 1 cap - 3 times daily - 7 days&#10;3. Cetirizine 10mg - 1 tab - Nightly - 10 days"><?php echo isset($_POST['medicines']) ? htmlspecialchars($_POST['medicines']) : ''; ?></textarea>
            </div>

            <div class="form-group">
                <label for="instructions">Special Advice / Instructions</label>
                <textarea name="instructions" id="instructions" rows="4" class="form-control" placeholder="e.g. Avoid cold drinks, take medicine after food, review in 7 days."><?php echo isset($_POST['instructions']) ? htmlspecialchars($_POST['instructions']) : ''; ?></textarea>
            </div>

            <div style="margin-top: 30px; display:flex; justify-content: flex-end; gap:15px;">
                <a href="index.php" class="btn btn-secondary">Cancel</a>
                <button type="submit" name="submit_prescription" class="btn btn-primary" style="padding:12px 25px;"><i class="fa-solid fa-save"></i> Save & Complete Consultation</button>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php require_once dirname(__DIR__) . '/includes/dashboard_footer.php'; ?>
