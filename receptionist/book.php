<?php
// receptionist/book.php
// Receptionist Appointment Booking panel (with Patient Lookup)

$page_title = "Create Patient Booking";
require_once dirname(__DIR__) . '/includes/dashboard_header.php';
require_once dirname(__DIR__) . '/config/mailer.php';

checkAccess(['receptionist']);

$error = "";
$success = "";

// Fetch departments for dynamic selectors
try {
    $departments = $pdo->query("SELECT * FROM departments ORDER BY name ASC")->fetchAll();
} catch (PDOException $e) {
    $departments = [];
}

// Process receptionist booking submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_booking'])) {
    $patient_id = intval($_POST['patient_id'] ?? 0);
    $dept_id = intval($_POST['department_id'] ?? 0);
    $doctor_id = intval($_POST['doctor_id'] ?? 0);
    $appt_date = trim($_POST['appointment_date'] ?? '');
    $schedule_id = intval($_POST['schedule_id'] ?? 0);
    $payment_method = $_POST['payment_method'] ?? 'Cash';
    $payment_status = $_POST['payment_status'] ?? 'Pending';

    if ($patient_id <= 0 || $dept_id <= 0 || $doctor_id <= 0 || empty($appt_date) || $schedule_id <= 0) {
        $error = "Please fill in all clinical and booking details.";
    } elseif (strtotime($appt_date) < strtotime(date('Y-m-d'))) {
        $error = "Appointments cannot be booked for past dates.";
    } else {
        try {
            // Fetch Doctor fee and details
            $doc_stmt = $pdo->prepare("SELECT d.*, u.email FROM doctors d JOIN users u ON d.user_id = u.id WHERE d.id = ? AND d.status = 'Active'");
            $doc_stmt->execute([$doctor_id]);
            $doctor = $doc_stmt->fetch();

            // Fetch Schedule Slot details
            $sched_stmt = $pdo->prepare("SELECT * FROM doctor_schedules WHERE id = ? AND doctor_id = ?");
            $sched_stmt->execute([$schedule_id, $doctor_id]);
            $schedule = $sched_stmt->fetch();

            // Get Patient User ID for notification
            $pat_stmt = $pdo->prepare("SELECT p.user_id, p.first_name, p.last_name, u.email FROM patients p JOIN users u ON p.user_id = u.id WHERE p.id = ?");
            $pat_stmt->execute([$patient_id]);
            $patient = $pat_stmt->fetch();

            if (!$doctor || !$schedule || !$patient) {
                $error = "The selected doctor, schedule, or patient is invalid.";
            } else {
                // Verify Day of week matches
                $day_name = date('l', strtotime($appt_date));
                if (strcasecmp($day_name, $schedule['day_of_week']) !== 0) {
                    $error = "The chosen date ($appt_date, $day_name) does not match the weekly schedule day ({$schedule['day_of_week']}).";
                } else {
                    // Check slot vacancy
                    $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND schedule_id = ? AND status != 'Cancelled'");
                    $count_stmt->execute([$doctor_id, $appt_date, $schedule_id]);
                    $booked_count = $count_stmt->fetchColumn();

                    if ($booked_count >= $schedule['max_patients']) {
                        $error = "This schedule slot is fully booked for $appt_date. Please choose another slot.";
                    } else {
                        // Begin Transaction
                        $pdo->beginTransaction();

                        $temp_num = 'A-' . time();
                        $queue_num = $booked_count + 1;

                        // 1. Insert appointment
                        $appt_stmt = $pdo->prepare("INSERT INTO appointments (appointment_number, patient_id, doctor_id, schedule_id, appointment_date, queue_number, status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
                        $appt_stmt->execute([$temp_num, $patient_id, $doctor_id, $schedule_id, $appt_date, $queue_num]);
                        $appointment_id = $pdo->lastInsertId();

                        // Update appointment number
                        $correct_num = 'A-' . str_pad($appointment_id, 5, '0', STR_PAD_LEFT);
                        $update_appt = $pdo->prepare("UPDATE appointments SET appointment_number = ? WHERE id = ?");
                        $update_appt->execute([$correct_num, $appointment_id]);

                        // 2. Insert payment
                        $total_charge = $doctor['charge'] + 500.00;
                        $transaction_id = ($payment_status === 'Paid') ? (($payment_method === 'Card') ? 'CARD-' . strtoupper(uniqid()) : 'CASH-' . strtoupper(uniqid())) : null;

                        $pay_stmt = $pdo->prepare("INSERT INTO payments (appointment_id, amount, payment_method, payment_status, transaction_id) VALUES (?, ?, ?, ?, ?)");
                        $pay_stmt->execute([$appointment_id, $total_charge, $payment_method, $payment_status, $transaction_id]);

                        // 3. Send notifications
                        $pat_msg = "Appointment created by receptionist! Dr. {$doctor['first_name']} {$doctor['last_name']}, Date: $appt_date, Queue Number: $queue_num. Appt No: $correct_num. Payment Method: $payment_method ($payment_status).";
                        $notif_stmt = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                        $notif_stmt->execute([$patient['user_id'], $pat_msg]);

                        $doc_msg = "New walk-in appointment booked for $appt_date. Queue No: $queue_num. Patient: {$patient['first_name']} {$patient['last_name']}.";
                        $notif_stmt->execute([$doctor['user_id'], $doc_msg]);

                        $pdo->commit();
                        $success = "Booking created successfully! Appt No: **$correct_num** | Queue No: **#$queue_num** | Total Fee: **LKR $total_charge**";

                        // Send email confirmation to patient
                        if (!empty($patient['email'])) {
                            $doctor_full_name  = $doctor['first_name'] . ' ' . $doctor['last_name'];
                            $patient_full_name = $patient['first_name'] . ' ' . $patient['last_name'];
                            Mailer::sendAppointmentConfirmation(
                                $patient['email'],
                                $patient_full_name,
                                $correct_num,
                                $doctor_full_name,
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
                        }
                    }
                }
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Failed to create booking: " . $e->getMessage();
        }
    }
}
?>

<div class="db-card" style="max-width: 800px; margin: 0 auto;">
    <div class="db-card-header">
        <h3><i class="fa-solid fa-calendar-check text-primary"></i> Create Patient Appointment Booking</h3>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <!-- 1. Patient lookup search bar -->
    <div style="background-color: var(--secondary-color); padding: 20px; border-radius: 8px; border: 1px solid var(--light-grey); margin-bottom: 25px;">
        <h4 style="margin-bottom:12px;"><i class="fa-solid fa-search"></i> Step 1: Lookup Patient by NIC</h4>
        <div style="display:flex; gap:10px;">
            <input type="text" id="lookup_nic" placeholder="Enter Patient NIC (e.g. 199012345678)" class="form-control" style="flex:1;">
            <button type="button" class="btn btn-primary" onclick="lookupPatient()" style="padding:10px 20px;">Lookup Patient</button>
        </div>
        <div id="lookup_message" style="margin-top:10px; font-weight:600; font-size:0.9rem;"></div>
    </div>

    <!-- 2. Booking details form (Hidden/Disabled by default until lookup succeeds) -->
    <form action="" method="POST" id="bookingForm" style="display:none;">
        <input type="hidden" name="create_booking" value="1">
        <!-- Hidden Patient ID field populated by lookup -->
        <input type="hidden" name="patient_id" id="patient_id_field">

        <h4 style="border-bottom: 1.5px solid var(--light-grey); padding-bottom: 5px; margin-bottom: 15px; font-size: 1.05rem; color: var(--primary-color);">Step 2: Channeling Details</h4>

        <div class="form-group">
            <label for="department_id">Clinical Department *</label>
            <select name="department_id" id="department_id" required class="form-control">
                <option value="">Select Department</option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="doctor_id">Consulting Doctor *</label>
            <select name="doctor_id" id="doctor_id" required class="form-control">
                <option value="">Select Doctor</option>
            </select>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="appointment_date">Appointment Date *</label>
                <input type="date" name="appointment_date" id="appointment_date" required class="form-control" min="<?php echo date('Y-m-d'); ?>">
            </div>

            <div class="form-group">
                <label for="schedule_id">Available Weekly Slots *</label>
                <select name="schedule_id" id="schedule_id" required class="form-control">
                    <option value="">Select Date First</option>
                </select>
            </div>
        </div>

        <h4 style="border-bottom: 1.5px solid var(--light-grey); padding-bottom: 5px; margin-top: 25px; margin-bottom: 15px; font-size: 1.05rem; color: var(--primary-color);">Step 3: Billing & Payments</h4>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="payment_method">Payment Method *</label>
                <select name="payment_method" id="payment_method" required class="form-control">
                    <option value="Cash">Cash</option>
                    <option value="Card">Card</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="payment_status">Payment Status *</label>
                <select name="payment_status" id="payment_status" required class="form-control">
                    <option value="Pending">Pending (Pay at Reception)</option>
                    <option value="Paid">Paid (Clears bill immediately)</option>
                </select>
            </div>
        </div>

        <!-- Dynamic Billing summary -->
        <div id="bookingDetailsSummary"></div>

        <div style="margin-top: 30px; display:flex; justify-content: flex-end; gap:15px;">
            <button type="button" class="btn btn-secondary" onclick="resetForm()">Reset</button>
            <button type="submit" class="btn btn-primary" style="padding: 12px 30px;"><i class="fa-solid fa-save"></i> Save Booking</button>
        </div>
    </form>
</div>

<!-- Dynamic selectors initialization -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    initBookingSelectors('<?php echo BASE_URL; ?>', 'department_id', 'doctor_id', 'appointment_date', 'schedule_id');
});

function lookupPatient() {
    const nic = document.getElementById('lookup_nic').value.trim();
    const messageDiv = document.getElementById('lookup_message');
    const form = document.getElementById('bookingForm');
    const patientIdField = document.getElementById('patient_id_field');

    messageDiv.innerHTML = '';
    form.style.display = 'none';

    if (!nic) {
        messageDiv.innerHTML = '<span style="color:var(--danger-color);">Please enter a valid Patient NIC.</span>';
        return;
    }

    messageDiv.innerHTML = '<span style="color:var(--grey-color);"><i class="fa-solid fa-spinner fa-spin"></i> Searching...</span>';

    fetch(`<?php echo BASE_URL; ?>ajax/get_patient_details.php?nic=${nic}`)
        .then(res => res.json())
        .then(data => {
            if (data.found) {
                const pat = data.patient;
                messageDiv.innerHTML = `
                    <span style="color:var(--success-color);">
                        <i class="fa-solid fa-circle-check"></i> Patient Found! 
                        <strong>${pat.first_name} ${pat.last_name}</strong> (No: ${pat.patient_number}, Tel: ${pat.phone})
                    </span>
                `;
                patientIdField.value = pat.id;
                form.style.display = 'block';
            } else {
                messageDiv.innerHTML = `
                    <span style="color:var(--danger-color);">
                        <i class="fa-solid fa-circle-xmark"></i> Patient not found in system. 
                        Please <a href="patients.php" style="font-weight:700; text-decoration:underline;">register them as a walk-in patient first</a>.
                    </span>
                `;
            }
        })
        .catch(err => {
            console.error('Error looking up patient:', err);
            messageDiv.innerHTML = '<span style="color:var(--danger-color);">An error occurred. Please try again.</span>';
        });
}

function resetForm() {
    document.getElementById('lookup_nic').value = '';
    document.getElementById('lookup_message').innerHTML = '';
    document.getElementById('bookingForm').reset();
    document.getElementById('bookingForm').style.display = 'none';
    document.getElementById('bookingDetailsSummary').innerHTML = '';
}
</script>

<?php require_once dirname(__DIR__) . '/includes/dashboard_footer.php'; ?>
