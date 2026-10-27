<?php
// admin/appointments.php
// Manage clinic appointments

$page_title = "Manage Appointments";
require_once dirname(__DIR__) . '/includes/dashboard_header.php';

checkAccess(['admin']);

$success = "";
$error = "";

// 1. Process Cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_appt_id'])) {
    $appt_id = intval($_POST['cancel_appt_id']);
    
    try {
        // Fetch details for verification
        $verify = $pdo->prepare("SELECT a.*, pay.payment_status, pay.payment_method, p.user_id AS pat_user_id, d.user_id AS doc_user_id 
                                 FROM appointments a 
                                 JOIN payments pay ON a.id = pay.appointment_id 
                                 JOIN patients p ON a.patient_id = p.id
                                 JOIN doctors d ON a.doctor_id = d.id
                                 WHERE a.id = ?");
        $verify->execute([$appt_id]);
        $appt = $verify->fetch();

        if ($appt && $appt['status'] === 'Pending') {
            $pdo->beginTransaction();

            // A. Update status to Cancelled
            $up = $pdo->prepare("UPDATE appointments SET status = 'Cancelled' WHERE id = ?");
            $up->execute([$appt_id]);

            // B. Process refund if Card/Paid
            $refund_msg = "";
            if ($appt['payment_method'] === 'Card' && $appt['payment_status'] === 'Paid') {
                $pay_up = $pdo->prepare("UPDATE payments SET payment_status = 'Refunded' WHERE appointment_id = ?");
                $pay_up->execute([$appt_id]);
                $refund_msg = " A refund has been issued to the card.";
            }

            // C. Send notifications
            $pat_msg = "Your appointment {$appt['appointment_number']} for date {$appt['appointment_date']} was Cancelled by clinic administration.{$refund_msg}";
            $notif_stmt = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
            $notif_stmt->execute([$appt['pat_user_id'], $pat_msg]);

            $doc_msg = "Appointment queue {$appt['queue_number']} for {$appt['appointment_date']} was cancelled by administrator.";
            $notif_stmt->execute([$appt['doc_user_id'], $doc_msg]);

            $pdo->commit();
            $success = "Appointment {$appt['appointment_number']} cancelled successfully.{$refund_msg}";
        } else {
            $error = "Only Pending appointments can be cancelled.";
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Failed to cancel appointment: " . $e->getMessage();
    }
}

// 2. Fetch doctors for filter dropdown
try {
    $doctors_list = $pdo->query("SELECT id, first_name, last_name FROM doctors ORDER BY first_name ASC")->fetchAll();
} catch (PDOException $e) {
    $doctors_list = [];
}

// 3. Build filter variables and SQL query
$search = trim($_GET['search'] ?? '');
$doc_filter = intval($_GET['doctor_id'] ?? 0);
$date_filter = trim($_GET['date'] ?? '');
$status_filter = trim($_GET['status'] ?? '');

$params = [];
$sql = "SELECT a.*, p.first_name AS pat_first, p.last_name AS pat_last, p.nic, p.patient_number,
               d.first_name AS doc_first, d.last_name AS doc_last, dept.name AS dept_name,
               pay.amount, pay.payment_method, pay.payment_status 
        FROM appointments a
        JOIN patients p ON a.patient_id = p.id
        JOIN doctors d ON a.doctor_id = d.id
        JOIN departments dept ON d.department_id = dept.id
        JOIN payments pay ON a.id = pay.appointment_id";

$where = [];

if (!empty($search)) {
    $where[] = "(p.first_name LIKE :search OR p.last_name LIKE :search OR p.nic LIKE :search OR a.appointment_number LIKE :search)";
    $params['search'] = '%' . $search . '%';
}
if ($doc_filter > 0) {
    $where[] = "a.doctor_id = :doc_filter";
    $params['doc_filter'] = $doc_filter;
}
if (!empty($date_filter)) {
    $where[] = "a.appointment_date = :date_filter";
    $params['date_filter'] = $date_filter;
}
if (!empty($status_filter)) {
    $where[] = "a.status = :status_filter";
    $params['status_filter'] = $status_filter;
}

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY a.appointment_date DESC, a.queue_number ASC LIMIT 150";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $appointments = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Failed to load appointments: " . $e->getMessage();
    $appointments = [];
}
?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="db-card">
    <div class="db-card-header">
        <h3><i class="fa-solid fa-calendar-check text-primary"></i> Master Appointment Registry</h3>
    </div>

    <!-- Filters -->
    <form method="GET" action="" style="display:flex; flex-wrap:wrap; gap:15px; margin-bottom:20px; align-items:center;">
        <div style="flex:1.5; min-width:200px;">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search Patient, NIC, Appt No..." class="form-control">
        </div>
        <div style="flex:1; min-width:150px;">
            <select name="doctor_id" class="form-control">
                <option value="">All Doctors</option>
                <?php foreach ($doctors_list as $doc): ?>
                    <option value="<?php echo $doc['id']; ?>" <?php echo ($doc_filter === $doc['id']) ? 'selected' : ''; ?>>
                        Dr. <?php echo htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="flex:1; min-width:150px;">
            <input type="date" name="date" value="<?php echo htmlspecialchars($date_filter); ?>" class="form-control">
        </div>
        <div style="flex:1; min-width:150px;">
            <select name="status" class="form-control">
                <option value="">All Statuses</option>
                <option value="Pending" <?php echo ($status_filter === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                <option value="Checked-In" <?php echo ($status_filter === 'Checked-In') ? 'selected' : ''; ?>>Checked-In</option>
                <option value="Completed" <?php echo ($status_filter === 'Completed') ? 'selected' : ''; ?>>Completed</option>
                <option value="Cancelled" <?php echo ($status_filter === 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary" style="padding:10px 20px;"><i class="fa-solid fa-filter"></i> Apply</button>
        <a href="appointments.php" class="btn btn-secondary" style="padding:10px 15px;">Clear</a>
    </form>

    <div class="table-responsive">
        <table class="db-table" style="font-size:0.85rem;">
            <thead>
                <tr>
                    <th>Appt No</th>
                    <th>Patient Name</th>
                    <th>Doctor</th>
                    <th>Dept / Spec</th>
                    <th>Date</th>
                    <th>Queue</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($appointments)): ?>
                    <?php foreach ($appointments as $appt): ?>
                        <tr style="<?php echo ($appt['status'] === 'Cancelled' || $appt['status'] === 'Completed') ? 'opacity:0.65;' : ''; ?>">
                            <td><strong><?php echo htmlspecialchars($appt['appointment_number']); ?></strong></td>
                            <td>
                                <strong style="display:block;"><?php echo htmlspecialchars($appt['pat_first'] . ' ' . $appt['pat_last']); ?></strong>
                                <span style="font-size:0.75rem; color:var(--grey-color);">NIC: <?php echo htmlspecialchars($appt['nic']); ?> | ID: <?php echo htmlspecialchars($appt['patient_number']); ?></span>
                            </td>
                            <td>Dr. <?php echo htmlspecialchars($appt['doc_first'] . ' ' . $appt['doc_last']); ?></td>
                            <td><?php echo htmlspecialchars($appt['dept_name']); ?></td>
                            <td><strong><?php echo date('d-M-Y', strtotime($appt['appointment_date'])); ?></strong></td>
                            <td>#<?php echo $appt['queue_number']; ?></td>
                            <td>
                                <?php
                                    $c = '';
                                    if ($appt['status'] === 'Pending') $c = 'badge-pending';
                                    elseif ($appt['status'] === 'Checked-In') $c = 'badge-checked-in';
                                    elseif ($appt['status'] === 'Completed') $c = 'badge-completed';
                                    elseif ($appt['status'] === 'Cancelled') $c = 'badge-cancelled';
                                ?>
                                <span class="badge <?php echo $c; ?>"><?php echo $appt['status']; ?></span>
                            </td>
                            <td>
                                <span style="display:block; font-weight:600;">LKR <?php echo number_format($appt['amount'], 2); ?></span>
                                <span class="badge <?php echo ($appt['payment_status'] === 'Paid') ? 'badge-paid' : (($appt['payment_status'] === 'Refunded') ? 'badge-refunded' : 'badge-pending'); ?>" style="font-size:0.7rem; padding:1px 6px;"><?php echo $appt['payment_status']; ?></span>
                            </td>
                            <td>
                                <?php if ($appt['status'] === 'Pending'): ?>
                                    <form action="" method="POST" onsubmit="return confirm('Cancel this appointment?');">
                                        <input type="hidden" name="cancel_appt_id" value="<?php echo $appt['id']; ?>">
                                        <button type="submit" class="btn btn-outline" style="padding:4px 8px; font-size:0.72rem; border-color:var(--danger-color); color:var(--danger-color); border-radius:4px;"><i class="fa-solid fa-ban"></i> Cancel</button>
                                    </form>
                                <?php else: ?>
                                    <span style="font-size:0.8rem; color:var(--grey-color); font-style:italic;">No Actions</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="text-align:center; padding:20px; color:var(--grey-color);">No appointments found matching filters.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/dashboard_footer.php'; ?>
