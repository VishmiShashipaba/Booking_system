<?php
ob_start();
// patient/book.php
// Book doctor appointment online

$page_title = "Book Appointment";
require_once dirname(__DIR__) . '/includes/dashboard_header.php';
require_once dirname(__DIR__) . '/config/mailer.php';

checkAccess(['patient']);

$patient_id = $_SESSION['patient_id'];
$user_id = $_SESSION['user_id'];

$error = "";
$success = "";

// Get pre-selected doctor if passed
$pre_doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;
$pre_dept_id = 0;

if ($pre_doctor_id > 0) {
    try {
        $pre_stmt = $pdo->prepare("SELECT department_id FROM doctors WHERE id = ?");
        $pre_stmt->execute([$pre_doctor_id]);
        $pre_dept_id = $pre_stmt->fetchColumn();
    } catch (PDOException $e) {
        $pre_dept_id = 0;
    }
}

// Fetch clinical departments
try {
    $departments = $pdo->query("SELECT * FROM departments ORDER BY name ASC")->fetchAll();
} catch (PDOException $e) {
    $departments = [];
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dept_id = intval($_POST['department_id'] ?? 0);
    $doctor_id = intval($_POST['doctor_id'] ?? 0);
    $appt_date = trim($_POST['appointment_date'] ?? '');
    $schedule_id = intval($_POST['schedule_id'] ?? 0);
    $payment_method = $_POST['payment_method'] ?? '';

    if ($dept_id <= 0 || $doctor_id <= 0 || empty($appt_date) || $schedule_id <= 0 || empty($payment_method)) {
        $error = "Please fill in all details to book your channel.";
    } elseif (strtotime($appt_date) < strtotime(date('Y-m-d'))) {
        $error = "Appointments cannot be booked for past dates.";
    } else {
        try {
            // 1. Fetch Doctor fee and details
            $doc_stmt = $pdo->prepare("SELECT d.*, u.email FROM doctors d JOIN users u ON d.user_id = u.id WHERE d.id = ? AND d.status = 'Active'");
            $doc_stmt->execute([$doctor_id]);
            $doctor = $doc_stmt->fetch();

            // 2. Fetch Schedule Slot details
            $sched_stmt = $pdo->prepare("SELECT * FROM doctor_schedules WHERE id = ? AND doctor_id = ?");
            $sched_stmt->execute([$schedule_id, $doctor_id]);
            $schedule = $sched_stmt->fetch();

            if (!$doctor || !$schedule) {
                $error = "Selected doctor or schedule slot is invalid.";
            } else {
                // Determine day of week
                $day_name = date('l', strtotime($appt_date));
                if (strcasecmp($day_name, $schedule['day_of_week']) !== 0) {
                    $error = "The selected date ($appt_date, $day_name) does not match the schedule day ({$schedule['day_of_week']}).";
                } else {
                    // Check slot vacancy
                    $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND schedule_id = ? AND status != 'Cancelled'");
                    $count_stmt->execute([$doctor_id, $appt_date, $schedule_id]);
                    $booked_count = $count_stmt->fetchColumn();

                    if ($booked_count >= $schedule['max_patients']) {
                        $error = "This schedule slot is fully booked for $appt_date. Please choose another date or slot.";
                    } else {
                        // Begin Transaction
                        $pdo->beginTransaction();

                        // 3. Create Appointment Number (Temporary place holder then update or pad ID)
                        $temp_num = 'A-' . time();
                        $queue_num = $booked_count + 1;

                        $appt_stmt = $pdo->prepare("INSERT INTO appointments (appointment_number, patient_id, doctor_id, schedule_id, appointment_date, queue_number, status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
                        $appt_stmt->execute([$temp_num, $patient_id, $doctor_id, $schedule_id, $appt_date, $queue_num]);
                        $appointment_id = $pdo->lastInsertId();

                        // Update with correct appointment number pad A-XXXXX
                        $correct_num = 'A-' . str_pad($appointment_id, 5, '0', STR_PAD_LEFT);
                        $update_appt = $pdo->prepare("UPDATE appointments SET appointment_number = ? WHERE id = ?");
                        $update_appt->execute([$correct_num, $appointment_id]);

                        // 4. Create Payment Invoice
                        // Total charge = Doctor charge + 500 LKR center processing fee
                        $total_charge = $doctor['charge'] + 500.00;
                        $payment_status = ($payment_method === 'Card') ? 'Paid' : 'Pending';
                        $transaction_id = ($payment_method === 'Card') ? 'CARD-' . strtoupper(uniqid()) : null;

                        $pay_stmt = $pdo->prepare("INSERT INTO payments (appointment_id, amount, payment_method, payment_status, transaction_id) VALUES (?, ?, ?, ?, ?)");
                        $pay_stmt->execute([$appointment_id, $total_charge, $payment_method, $payment_status, $transaction_id]);

                        // 5. Send notifications
                        // Notification for Patient
                        $pat_msg = "Appointment booked successfully! Doctor: Dr. {$doctor['first_name']} {$doctor['last_name']}, Date: $appt_date, Queue Number: $queue_num. Appt No: $correct_num. Payment Method: $payment_method ($payment_status).";
                        $notif_stmt = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                        $notif_stmt->execute([$user_id, $pat_msg]);

                        // Notification for Doctor
                        $doc_msg = "New appointment booked for $appt_date. Queue No: $queue_num. Patient: {$_SESSION['full_name']}.";
                        $notif_stmt->execute([$doctor['user_id'], $doc_msg]);

                        $pdo->commit();

                        // Send appointment confirmation email
                        $patient_email = $_SESSION['email'];
                        $patient_name  = $_SESSION['full_name'];
                        $doctor_name   = $doctor['first_name'] . ' ' . $doctor['last_name'];
                        Mailer::sendAppointmentConfirmation(
                            $patient_email,
                            $patient_name,
                            $correct_num,
                            $doctor_name,
                            $doctor['specialization'],
                            $appt_date,
                            $schedule['day_of_week'],
                            $schedule['start_time'],
                            $schedule['end_time'],
                            $queue_num,
                            $payment_method,
                            $payment_status,
                            $total_charge
                        );

                        // Redirect to History with success parameter
                        header("Location: " . BASE_URL . "patient/history.php?booked=success&no=" . $correct_num . "&q=" . $queue_num);
                        exit();
                    }
                }
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Failed to book appointment: " . $e->getMessage();
        }
    }
}
?>

<div class="db-card" style="max-width: 750px; margin: 0 auto;">
    <div class="db-card-header">
        <h3><i class="fa-solid fa-calendar-plus text-primary"></i> Doctor Channel Appointment Booking Form</h3>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        <!-- 1. Select Department -->
        <div class="form-group">
            <label for="department_id">Clinical Department *</label>
            <select name="department_id" id="department_id" required class="form-control">
                <option value="">Select Department</option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?php echo $dept['id']; ?>" <?php echo ($pre_dept_id == $dept['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($dept['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- 2. Select Doctor -->
        <div class="form-group">
            <label for="doctor_id">Consulting Doctor *</label>
            <select name="doctor_id" id="doctor_id" required class="form-control">
                <option value="">Select Doctor</option>
                <?php if ($pre_doctor_id > 0): ?>
                    <?php
                        // If pre-selected, fetch doctors in that dept to pre-populate list
                        $docs_stmt = $pdo->prepare("SELECT id, first_name, last_name, charge FROM doctors WHERE department_id = ? AND status = 'Active'");
                        $docs_stmt->execute([$pre_dept_id]);
                        $pre_docs = $docs_stmt->fetchAll();
                        foreach ($pre_docs as $pdoc):
                    ?>
                        <option value="<?php echo $pdoc['id']; ?>" data-charge="<?php echo $pdoc['charge']; ?>" <?php echo ($pre_doctor_id == $pdoc['id']) ? 'selected' : ''; ?>>
                            Dr. <?php echo htmlspecialchars($pdoc['first_name'] . ' ' . $pdoc['last_name']); ?> (Fee: LKR <?php echo $pdoc['charge']; ?>)
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <!-- 3. Select Appointment Date -->
            <div class="form-group">
                <label for="appointment_date">Appointment Date *</label>
                <input type="date" name="appointment_date" id="appointment_date" required class="form-control" min="<?php echo date('Y-m-d'); ?>">
                <small id="doctor_days_hint" style="display:block; margin-top:5px; color:var(--primary-color); font-weight:600;"></small>
            </div>

            <!-- 4. Select Schedule Slot -->
            <div class="form-group">
                <label for="schedule_id">Available Weekly Slots *</label>
                <select name="schedule_id" id="schedule_id" required class="form-control">
                    <option value="">Select Date First</option>
                </select>
            </div>
        </div>

        <!-- 5. Payment details -->
        <h4 style="border-bottom: 1.5px solid var(--light-grey); padding-bottom: 5px; margin-top: 25px; margin-bottom: 15px; font-size: 1.05rem; color: var(--primary-color);">Payment Option Details</h4>
        
        <div class="form-group">
            <label for="payment_method">Select Payment Method *</label>
            <select name="payment_method" id="payment_method" required class="form-control">
                <option value="">Select Method</option>
                <option value="Cash">Cash (Pay at Clinic Reception desk on arrival)</option>
                <option value="Card">Visa/Mastercard Credit/Debit Card (Simulated Instant Clearance)</option>
            </select>
        </div>

        <!-- Dynamic Billing summary -->
        <div id="bookingDetailsSummary"></div>

        <div style="margin-top: 30px; display:flex; justify-content: flex-end; gap:15px;">
            <a href="index.php" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary" style="padding: 12px 30px;">Confirm & Book Appointment</button>
        </div>
    </form>
</div>

<!-- Initialize script selectors on load -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    initBookingSelectors('<?php echo BASE_URL; ?>', 'department_id', 'doctor_id', 'appointment_date', 'schedule_id');
});
</script>

<?php require_once dirname(__DIR__) . '/includes/dashboard_footer.php'; ?>
