<?php
// receptionist/index.php
// Receptionist Dashboard Daily Queue Management

$page_title = "Receptionist Dashboard - Daily Queue";
require_once dirname(__DIR__) . '/includes/dashboard_header.php';

checkAccess(['receptionist']);

$today = date('Y-m-d');
$success = "";
$error = "";

// 1. Handle Check-in Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkin_appt_id'])) {
    $appt_id = intval($_POST['checkin_appt_id']);
    try {
        $pdo->beginTransaction();
        
        // Update status to Checked-In
        $up_stmt = $pdo->prepare("UPDATE appointments SET status = 'Checked-In' WHERE id = ?");
        $up_stmt->execute([$appt_id]);

        // Get patient user id
        $pat_stmt = $pdo->prepare("SELECT a.appointment_number, p.user_id, d.first_name AS doc_first, d.last_name AS doc_last 
                                   FROM appointments a 
                                   JOIN patients p ON a.patient_id = p.id 
                                   JOIN doctors d ON a.doctor_id = d.id
                                   WHERE a.id = ?");
        $pat_stmt->execute([$appt_id]);
        $appt = $pat_stmt->fetch();

        if ($appt) {
            $msg = "Your appointment {$appt['appointment_number']} with Dr. {$appt['doc_first']} {$appt['doc_last']} is now Checked-In. Please proceed to the waiting hall.";
            $notif_stmt = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
            $notif_stmt->execute([$appt['user_id'], $msg]);
        }

        $pdo->commit();
        $success = "Patient checked in successfully.";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Check-in failed: " . $e->getMessage();
    }
}

// 2. Handle Payment Check-off Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay_appt_id'])) {
    $pay_appt_id = intval($_POST['pay_appt_id']);
    try {
        $pdo->beginTransaction();

        $up_stmt = $pdo->prepare("UPDATE payments SET payment_status = 'Paid', payment_date = CURRENT_TIMESTAMP WHERE appointment_id = ?");
        $up_stmt->execute([$pay_appt_id]);

        // Get patient details for notification
        $pat_stmt = $pdo->prepare("SELECT a.appointment_number, p.user_id, pay.amount 
                                   FROM appointments a 
                                   JOIN patients p ON a.patient_id = p.id 
                                   JOIN payments pay ON a.id = pay.appointment_id
                                   WHERE a.id = ?");
        $pat_stmt->execute([$pay_appt_id]);
        $appt = $pat_stmt->fetch();

        if ($appt) {
            $msg = "Payment of LKR " . number_format($appt['amount'], 2) . " for appointment {$appt['appointment_number']} has been cleared successfully at clinic reception.";
            $notif_stmt = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
            $notif_stmt->execute([$appt['user_id'], $msg]);
        }

        $pdo->commit();
        $success = "Payment recorded and bill cleared successfully.";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Payment recording failed: " . $e->getMessage();
    }
}

// 3. Fetch Metrics
try {
    $total_appt_today = $pdo->query("SELECT COUNT(*) FROM appointments WHERE appointment_date = '$today' AND status != 'Cancelled'")->fetchColumn();
    $checked_in_today = $pdo->query("SELECT COUNT(*) FROM appointments WHERE appointment_date = '$today' AND status = 'Checked-In'")->fetchColumn();
    $pending_pay_today = $pdo->query("SELECT COUNT(*) FROM appointments a JOIN payments p ON a.id = p.appointment_id WHERE a.appointment_date = '$today' AND p.payment_status = 'Pending' AND a.status != 'Cancelled'")->fetchColumn();
    $total_rev_today = $pdo->query("SELECT SUM(amount) FROM payments WHERE payment_status = 'Paid' AND DATE(payment_date) = '$today'")->fetchColumn() ?? 0;
} catch (PDOException $e) {
    $total_appt_today = $checked_in_today = $pending_pay_today = $total_rev_today = 0;
}

// 4. Fetch Today's Queue list
try {
    $list_stmt = $pdo->prepare("SELECT a.*, p.first_name AS pat_first, p.last_name AS pat_last, p.patient_number, p.nic, 
                                       d.first_name AS doc_first, d.last_name AS doc_last, dept.name AS dept_name,
                                       pay.amount, pay.payment_method, pay.payment_status 
                                FROM appointments a
                                JOIN patients p ON a.patient_id = p.id
                                JOIN doctors d ON a.doctor_id = d.id
                                JOIN departments dept ON d.department_id = dept.id
                                JOIN payments pay ON a.id = pay.appointment_id
                                WHERE a.appointment_date = ? 
                                ORDER BY a.status DESC, a.queue_number ASC");
    $list_stmt->execute([$today]);
    $daily_appointments = $list_stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Failed to load daily appointments: " . $e->getMessage();
    $daily_appointments = [];
}
?>

<!-- Metrics Summary Grid -->
<div class="metrics-grid">
    <div class="metric-card">
        <div class="metric-card-left">
            <h4>Today's Bookings</h4>
            <div class="value"><?php echo $total_appt_today; ?></div>
        </div>
        <div class="metric-card-icon"><i class="fa-solid fa-calendar-days"></i></div>
    </div>
    
    <div class="metric-card success">
        <div class="metric-card-left">
            <h4>Checked-In Patients</h4>
            <div class="value"><?php echo $checked_in_today; ?></div>
        </div>
        <div class="metric-card-icon"><i class="fa-solid fa-user-check"></i></div>
    </div>
    
    <div class="metric-card danger">
        <div class="metric-card-left">
            <h4>Pending Billings</h4>
            <div class="value"><?php echo $pending_pay_today; ?></div>
        </div>
        <div class="metric-card-icon"><i class="fa-solid fa-file-invoice"></i></div>
    </div>

    <div class="metric-card success">
        <div class="metric-card-left">
            <h4>Collected Revenue</h4>
            <div class="value" style="font-size:1.5rem;">LKR <?php echo number_format($total_rev_today, 2); ?></div>
        </div>
        <div class="metric-card-icon"><i class="fa-solid fa-sack-dollar"></i></div>
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
        <h3><i class="fa-solid fa-list-check text-primary"></i> Daily Clinic Appointments Queue</h3>
    </div>

    <div class="table-responsive">
        <table class="db-table">
            <thead>
                <tr>
                    <th>Queue No</th>
                    <th>Appt No</th>
                    <th>Patient Name</th>
                    <th>Consulting Doctor</th>
                    <th>Status</th>
                    <th>Payment Info</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($daily_appointments)): ?>
                    <?php foreach ($daily_appointments as $appt): ?>
                        <tr style="<?php echo ($appt['status'] === 'Cancelled' || $appt['status'] === 'Completed') ? 'opacity:0.65;' : ''; ?>">
                            <td>
                                <div style="font-weight:bold; font-size:1.2rem; color:var(--primary-color); text-align:center; padding:3px; background-color:var(--secondary-color); border-radius:6px; border:1px solid rgba(15, 82, 186, 0.1); width: 50px;">
                                    #<?php echo $appt['queue_number']; ?>
                                </div>
                            </td>
                            <td><strong><?php echo htmlspecialchars($appt['appointment_number']); ?></strong></td>
                            <td>
                                <strong style="display:block; font-size:0.95rem;"><?php echo htmlspecialchars($appt['pat_first'] . ' ' . $appt['pat_last']); ?></strong>
                                <span style="font-size:0.8rem; color:var(--grey-color);">NIC: <?php echo htmlspecialchars($appt['nic']); ?> | ID: <?php echo htmlspecialchars($appt['patient_number']); ?></span>
                            </td>
                            <td>
                                <strong style="display:block; font-size:0.9rem;">Dr. <?php echo htmlspecialchars($appt['doc_first'] . ' ' . $appt['doc_last']); ?></strong>
                                <span style="font-size:0.8rem; color:var(--grey-color);"><?php echo htmlspecialchars($appt['dept_name']); ?></span>
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
                                <span style="display:block; font-weight:600; font-size:0.9rem;">LKR <?php echo number_format($appt['amount'], 2); ?></span>
                                <span class="badge <?php echo ($appt['payment_status'] === 'Paid') ? 'badge-paid' : (($appt['payment_status'] === 'Refunded') ? 'badge-refunded' : 'badge-pending'); ?>" style="font-size:0.7rem; padding: 2px 8px;"><?php echo $appt['payment_status']; ?></span>
                                <span style="display:block; font-size: 0.7rem; color:var(--grey-color);"><?php echo $appt['payment_method']; ?></span>
                            </td>
                            <td>
                                <div style="display:flex; gap:8px;">
                                    <!-- Check-In Button -->
                                    <?php if ($appt['status'] === 'Pending'): ?>
                                        <form action="" method="POST" style="display:inline;">
                                            <input type="hidden" name="checkin_appt_id" value="<?php echo $appt['id']; ?>">
                                            <button type="submit" class="btn btn-primary" style="padding:6px 12px; font-size:0.75rem; border-radius:4px;"><i class="fa-solid fa-door-open"></i> Check In</button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <!-- Clear Bill Button -->
                                    <?php if ($appt['payment_status'] === 'Pending' && $appt['status'] !== 'Cancelled'): ?>
                                        <form action="" method="POST" onsubmit="return confirm('Clear payment for this bill?');" style="display:inline;">
                                            <input type="hidden" name="pay_appt_id" value="<?php echo $appt['id']; ?>">
                                            <button type="submit" class="btn btn-accent" style="padding:6px 12px; font-size:0.75rem; border-radius:4px; background-color:var(--success-color); border-color:var(--success-color); color:white;"><i class="fa-solid fa-dollar-sign"></i> Pay Cash</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 30px; color: var(--grey-color);">
                            No appointments booked for today.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/dashboard_footer.php'; ?>
