<?php
// doctor/index.php
// Doctor Dashboard Overview (Today's Queue)

$page_title = "Doctor Dashboard - Today's Queue";
require_once dirname(__DIR__) . '/includes/dashboard_header.php';

checkAccess(['doctor']);

$doctor_id = $_SESSION['doctor_id'];
$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');

$success = "";
$error = "";

// 1. Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['appointment_id'])) {
    $appt_id = intval($_POST['appointment_id']);
    $new_status = $_POST['action'];

    if (in_array($new_status, ['Checked-In', 'Completed', 'Cancelled'])) {
        try {
            $pdo->beginTransaction();

            // Fetch patient user id for notification
            $notif_stmt = $pdo->prepare("SELECT a.appointment_number, p.user_id, a.appointment_date 
                                          FROM appointments a 
                                          JOIN patients p ON a.patient_id = p.id 
                                          WHERE a.id = ? AND a.doctor_id = ?");
            $notif_stmt->execute([$appt_id, $doctor_id]);
            $appt = $notif_stmt->fetch();

            if ($appt) {
                // Update status
                $up_stmt = $pdo->prepare("UPDATE appointments SET status = ? WHERE id = ?");
                $up_stmt->execute([$new_status, $appt_id]);

                // Create patient notification
                $msg = "Your appointment {$appt['appointment_number']} for date {$appt['appointment_date']} status was updated to: **$new_status** by the consulting doctor.";
                $ins_stmt = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                $ins_stmt->execute([$appt['user_id'], $msg]);

                $pdo->commit();
                $success = "Appointment status successfully updated to: $new_status.";
            } else {
                $error = "Appointment records mismatch.";
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Failed to update status: " . $e->getMessage();
        }
    }
}

// 2. Fetch today's appointments queue
try {
    $stmt = $pdo->prepare("SELECT a.*, p.first_name, p.last_name, p.patient_number, p.nic, p.dob, p.gender, p.phone, p.address,
                                  pay.amount, pay.payment_status, pay.payment_method 
                           FROM appointments a
                           JOIN patients p ON a.patient_id = p.id
                           JOIN payments pay ON a.id = pay.appointment_id
                           WHERE a.doctor_id = ? AND a.appointment_date = ? 
                           ORDER BY a.queue_number ASC");
    $stmt->execute([$doctor_id, $today]);
    $today_appointments = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Failed to load doctor queue: " . $e->getMessage();
    $today_appointments = [];
}
?>

<!-- Statistics Overview Cards -->
<div class="metrics-grid">
    <div class="metric-card">
        <div class="metric-card-left">
            <h4>Today's Total Patients</h4>
            <div class="value"><?php echo count($today_appointments); ?></div>
        </div>
        <div class="metric-card-icon"><i class="fa-solid fa-users"></i></div>
    </div>
    
    <div class="metric-card success">
        <div class="metric-card-left">
            <h4>Consultations Completed</h4>
            <div class="value">
                <?php 
                    $comp = 0;
                    foreach ($today_appointments as $a) {
                        if ($a['status'] === 'Completed') $comp++;
                    }
                    echo $comp;
                ?>
            </div>
        </div>
        <div class="metric-card-icon"><i class="fa-solid fa-circle-check"></i></div>
    </div>

    <div class="metric-card warning">
        <div class="metric-card-left">
            <h4>Active Queue Waiting</h4>
            <div class="value">
                <?php 
                    $wait = 0;
                    foreach ($today_appointments as $a) {
                        if ($a['status'] === 'Checked-In' || $a['status'] === 'Pending') $wait++;
                    }
                    echo $wait;
                ?>
            </div>
        </div>
        <div class="metric-card-icon"><i class="fa-solid fa-hospital-user"></i></div>
    </div>
</div>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="db-card">
    <div class="db-card-header">
        <h3><i class="fa-solid fa-list-ol text-primary"></i> Today's Queue Details (<?php echo date('d-M-Y', strtotime($today)); ?>)</h3>
    </div>

    <div class="table-responsive">
        <table class="db-table">
            <thead>
                <tr>
                    <th>Queue No</th>
                    <th>Appt No</th>
                    <th>Patient Name</th>
                    <th>NIC / Contact</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($today_appointments)): ?>
                    <?php foreach ($today_appointments as $appt): ?>
                        <tr style="<?php echo ($appt['status'] === 'Completed') ? 'opacity:0.75; background-color:#fafbfc;' : ''; ?>">
                            <td>
                                <div style="font-weight:bold; font-size:1.3rem; color:var(--primary-color); text-align:center; padding:3px; background-color:var(--secondary-color); border-radius:6px; border:1px solid rgba(15, 82, 186, 0.1); width: 60px;">
                                    #<?php echo $appt['queue_number']; ?>
                                </div>
                            </td>
                            <td><strong><?php echo htmlspecialchars($appt['appointment_number']); ?></strong></td>
                            <td>
                                <strong style="display:block; font-size:0.95rem;"><?php echo htmlspecialchars($appt['first_name'] . ' ' . $appt['last_name']); ?></strong>
                                <span style="font-size:0.8rem; color:var(--grey-color);">ID: <?php echo htmlspecialchars($appt['patient_number']); ?></span>
                            </td>
                            <td>
                                <span style="display:block; font-size:0.85rem;">NIC: <strong><?php echo htmlspecialchars($appt['nic']); ?></strong></span>
                                <span style="font-size:0.8rem; color:var(--grey-color);">Tel: <?php echo htmlspecialchars($appt['phone']); ?></span>
                            </td>
                            <td>
                                <?php 
                                    $class = '';
                                    if ($appt['status'] === 'Pending') $class = 'badge-pending';
                                    elseif ($appt['status'] === 'Checked-In') $class = 'badge-checked-in';
                                    elseif ($appt['status'] === 'Completed') $class = 'badge-completed';
                                    elseif ($appt['status'] === 'Cancelled') $class = 'badge-cancelled';
                                ?>
                                <span class="badge <?php echo $class; ?>"><?php echo $appt['status']; ?></span>
                            </td>
                            <td>
                                <span class="badge <?php echo ($appt['payment_status'] === 'Paid') ? 'badge-paid' : 'badge-pending'; ?>" style="font-size:0.75rem;">
                                    <?php echo $appt['payment_status']; ?>
                                </span>
                                <span style="display:block; font-size:0.75rem; color:var(--grey-color);"><?php echo $appt['payment_method']; ?></span>
                            </td>
                            <td>
                                <div style="display:flex; gap:8px;">
                                    <!-- View Details Button -->
                                    <button class="btn btn-secondary" style="padding:6px 12px; font-size:0.75rem; border-radius:4px;" 
                                            onclick="viewPatient(<?php echo htmlspecialchars(json_encode($appt)); ?>)">
                                        <i class="fa-solid fa-id-card"></i> Profile
                                    </button>

                                    <!-- Quick Consultation status update buttons -->
                                    <?php if ($appt['status'] === 'Pending'): ?>
                                        <form action="" method="POST" style="display:inline;">
                                            <input type="hidden" name="appointment_id" value="<?php echo $appt['id']; ?>">
                                            <input type="hidden" name="action" value="Checked-In">
                                            <button type="submit" class="btn btn-primary" style="padding:6px 12px; font-size:0.75rem; border-radius:4px;"><i class="fa-solid fa-door-open"></i> Check In</button>
                                        </form>
                                    <?php elseif ($appt['status'] === 'Checked-In'): ?>
                                        <a href="prescription.php?appointment_id=<?php echo $appt['id']; ?>" class="btn btn-accent" style="padding:6px 12px; font-size:0.75rem; border-radius:4px;">
                                            <i class="fa-solid fa-file-prescription"></i> Cons./Prescribe
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: var(--grey-color);">
                            <i class="fa-solid fa-calendar-times" style="font-size: 3rem; margin-bottom: 15px; display:block; opacity:0.3;"></i>
                            No appointments scheduled for today.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Patient Info Modal -->
<div class="modal-overlay" id="patientModal">
    <div class="modal-container">
        <div class="modal-header">
            <h3>Patient Clinical Record</h3>
            <span class="modal-close" onclick="closeModal('patientModal')">&times;</span>
        </div>
        <div class="modal-body">
            <div class="details-grid">
                <div class="detail-item">
                    <label>Patient ID</label>
                    <p id="p_id"></p>
                </div>
                <div class="detail-item">
                    <label>Full Name</label>
                    <p id="p_name"></p>
                </div>
                <div class="detail-item">
                    <label>NIC Number</label>
                    <p id="p_nic"></p>
                </div>
                <div class="detail-item">
                    <label>Contact Phone</label>
                    <p id="p_phone"></p>
                </div>
                <div class="detail-item">
                    <label>Date of Birth</label>
                    <p id="p_dob"></p>
                </div>
                <div class="detail-item">
                    <label>Gender</label>
                    <p id="p_gender"></p>
                </div>
                <div class="detail-item" style="grid-column: 1 / -1;">
                    <label>Postal Address</label>
                    <p id="p_address"></p>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('patientModal')">Close</button>
        </div>
    </div>
</div>

<script>
function viewPatient(appt) {
    document.getElementById('p_id').textContent = appt.patient_number;
    document.getElementById('p_name').textContent = appt.first_name + ' ' + appt.last_name;
    document.getElementById('p_nic').textContent = appt.nic;
    document.getElementById('p_phone').textContent = appt.phone;
    
    // Calculate birth date
    const dob = new Date(appt.dob);
    document.getElementById('p_dob').textContent = dob.toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric' });
    document.getElementById('p_gender').textContent = appt.gender;
    document.getElementById('p_address').textContent = appt.address ? appt.address : 'Not Provided';
    
    openModal('patientModal');
}
</script>

<?php require_once dirname(__DIR__) . '/includes/dashboard_footer.php'; ?>
