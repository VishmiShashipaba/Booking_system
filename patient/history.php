<?php
// patient/history.php
// Patient appointment history & cancellation panel

$page_title = "My Booking History";
require_once dirname(__DIR__) . '/includes/dashboard_header.php';

checkAccess(['patient']);

$patient_id = $_SESSION['patient_id'];
$user_id = $_SESSION['user_id'];

$success = "";
$error = "";

// 1. Handle Cancellation Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_appt_id'])) {
    $cancel_id = intval($_POST['cancel_appt_id']);
    
    try {
        // Verify this appointment belongs to this patient and is cancelable (Pending status)
        $verify_stmt = $pdo->prepare("SELECT a.*, p.payment_status, p.payment_method, d.user_id AS doc_user_id 
                                      FROM appointments a 
                                      JOIN payments p ON a.id = p.appointment_id 
                                      JOIN doctors d ON a.doctor_id = d.id
                                      WHERE a.id = ? AND a.patient_id = ?");
        $verify_stmt->execute([$cancel_id, $patient_id]);
        $appt = $verify_stmt->fetch();
        
        if ($appt && $appt['status'] === 'Pending') {
            $pdo->beginTransaction();
            
            // A. Update appointment status
            $up_stmt = $pdo->prepare("UPDATE appointments SET status = 'Cancelled' WHERE id = ?");
            $up_stmt->execute([$cancel_id]);
            
            // B. Update payment status if paid via card (refund simulation)
            if ($appt['payment_method'] === 'Card' && $appt['payment_status'] === 'Paid') {
                $pay_up = $pdo->prepare("UPDATE payments SET payment_status = 'Refunded' WHERE appointment_id = ?");
                $pay_up->execute([$cancel_id]);
                $refund_msg = " Your payment has been refunded to your card.";
            } else {
                $refund_msg = "";
            }
            
            // C. Add notifications
            $notif_msg = "Appointment {$appt['appointment_number']} for date {$appt['appointment_date']} was Cancelled.{$refund_msg}";
            $notif_stmt = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
            $notif_stmt->execute([$user_id, $notif_msg]);
            
            // Notification for Doctor
            $doc_notif = "Appointment queue {$appt['queue_number']} for {$appt['appointment_date']} was cancelled by patient.";
            $notif_stmt->execute([$appt['doc_user_id'], $doc_notif]);
            
            $pdo->commit();
            $success = "Appointment {$appt['appointment_number']} has been successfully cancelled.{$refund_msg}";
        } else {
            $error = "Appointment cannot be cancelled. Only Pending appointments can be cancelled.";
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Cancellation failed: " . $e->getMessage();
    }
}

// 2. Fetch list of appointments
try {
    $appt_stmt = $pdo->prepare("SELECT a.*, d.first_name AS doc_first, d.last_name AS doc_last, d.specialization, 
                                       dept.name AS dept_name, p.amount, p.payment_method, p.payment_status, 
                                       s.start_time, s.end_time 
                                FROM appointments a
                                JOIN doctors d ON a.doctor_id = d.id
                                JOIN departments dept ON d.department_id = dept.id
                                JOIN doctor_schedules s ON a.schedule_id = s.id
                                JOIN payments p ON a.id = p.appointment_id
                                WHERE a.patient_id = ?
                                ORDER BY a.appointment_date DESC, a.queue_number ASC");
    $appt_stmt->execute([$patient_id]);
    $appointments = $appt_stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Failed to load appointments: " . $e->getMessage();
    $appointments = [];
}
?>

<!-- Success parameter from book.php redirect -->
<?php if (isset($_GET['booked']) && $_GET['booked'] === 'success'): ?>
    <div class="alert alert-success" style="margin-bottom: 25px;">
        <strong>Appointment Booked Successfully!</strong><br>
        Your Appointment Number is <strong><?php echo htmlspecialchars($_GET['no']); ?></strong>. 
        Your Queue Number is <strong>#<?php echo htmlspecialchars($_GET['q']); ?></strong>. 
        Please check the list below.
    </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="db-card">
    <div class="db-card-header">
        <h3><i class="fa-solid fa-history text-primary"></i> My Channel Bookings</h3>
    </div>

    <div class="table-responsive">
        <table class="db-table">
            <thead>
                <tr>
                    <th>Appt No</th>
                    <th>Doctor</th>
                    <th>Dept / Spec</th>
                    <th>Date / Time Slot</th>
                    <th>Queue No</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th class="no-print">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($appointments)): ?>
                    <?php foreach ($appointments as $appt): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($appt['appointment_number']); ?></strong></td>
                            <td>Dr. <?php echo htmlspecialchars($appt['doc_first'] . ' ' . $appt['doc_last']); ?></td>
                            <td>
                                <span style="display:block; font-weight:600; font-size:0.8rem;"><?php echo htmlspecialchars($appt['dept_name']); ?></span>
                                <span style="font-size:0.8rem; color:var(--grey-color);"><?php echo htmlspecialchars($appt['specialization']); ?></span>
                            </td>
                            <td>
                                <span style="display:block; font-weight:600;"><?php echo date('d-M-Y', strtotime($appt['appointment_date'])); ?></span>
                                <span style="font-size:0.8rem; color:var(--grey-color);">
                                    <?php echo date('g:i A', strtotime($appt['start_time'])); ?> - <?php echo date('g:i A', strtotime($appt['end_time'])); ?>
                                </span>
                            </td>
                            <td>
                                <div style="background-color: var(--secondary-color); color: var(--primary-color); font-weight:bold; font-size: 1.1rem; text-align:center; padding:5px; border-radius:6px; border: 1px dashed rgba(15, 82, 186, 0.2);">
                                    #<?php echo $appt['queue_number']; ?>
                                </div>
                            </td>
                            <td>
                                <?php 
                                    $status_class = '';
                                    if ($appt['status'] === 'Pending') $status_class = 'badge-pending';
                                    elseif ($appt['status'] === 'Checked-In') $status_class = 'badge-checked-in';
                                    elseif ($appt['status'] === 'Completed') $status_class = 'badge-completed';
                                    elseif ($appt['status'] === 'Cancelled') $status_class = 'badge-cancelled';
                                ?>
                                <span class="badge <?php echo $status_class; ?>"><?php echo $appt['status']; ?></span>
                            </td>
                            <td>
                                <span style="display:block; font-weight:600;">LKR <?php echo number_format($appt['amount'], 2); ?></span>
                                <?php
                                    $pay_class = '';
                                    if ($appt['payment_status'] === 'Pending') $pay_class = 'badge-pending';
                                    elseif ($appt['payment_status'] === 'Paid') $pay_class = 'badge-paid';
                                    elseif ($appt['payment_status'] === 'Refunded') $pay_class = 'badge-refunded';
                                ?>
                                <span class="badge <?php echo $pay_class; ?>" style="font-size:0.7rem; padding: 2px 8px;"><?php echo $appt['payment_status']; ?></span>
                                <span style="display:block; font-size: 0.7rem; color:var(--grey-color);"><?php echo $appt['payment_method']; ?></span>
                            </td>
                            <td class="no-print">
                                <?php if ($appt['status'] === 'Pending'): ?>
                                    <form action="" method="POST" onsubmit="return confirm('Are you sure you want to cancel this appointment?');" style="display:inline;">
                                        <input type="hidden" name="cancel_appt_id" value="<?php echo $appt['id']; ?>">
                                        <button type="submit" class="btn btn-outline" style="padding: 6px 12px; font-size:0.75rem; border-color:var(--danger-color); color:var(--danger-color); border-radius:4px;"><i class="fa-solid fa-ban"></i> Cancel</button>
                                    </form>
                                <?php else: ?>
                                    <span style="font-size:0.8rem; color:var(--grey-color); font-style:italic;">No Actions</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 30px; color: var(--grey-color);">
                            You have no appointments booked yet. <a href="book.php" style="color:var(--primary-color); font-weight:600;">Book one now</a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/dashboard_footer.php'; ?>
