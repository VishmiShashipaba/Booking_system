<?php
// receptionist/payments.php
// View and record payments for appointments

$page_title = "Billing & Payments Ledger";
require_once dirname(__DIR__) . '/includes/dashboard_header.php';

checkAccess(['receptionist']);

$success = "";
$error = "";

// 1. Handle Payment Clearance
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_pay_id'])) {
    $appt_id = intval($_POST['clear_pay_id']);
    $pay_method = $_POST['pay_method'] ?? 'Cash';
    
    try {
        $pdo->beginTransaction();
        
        $transaction_id = ($pay_method === 'Card') ? 'CARD-' . strtoupper(uniqid()) : 'CASH-' . strtoupper(uniqid());

        // Update payment status
        $up_stmt = $pdo->prepare("UPDATE payments SET payment_status = 'Paid', payment_method = ?, transaction_id = ?, payment_date = CURRENT_TIMESTAMP WHERE appointment_id = ?");
        $up_stmt->execute([$pay_method, $transaction_id, $appt_id]);

        // Get patient details for notification
        $pat_stmt = $pdo->prepare("SELECT a.appointment_number, p.user_id, pay.amount 
                                   FROM appointments a 
                                   JOIN patients p ON a.patient_id = p.id 
                                   JOIN payments pay ON a.id = pay.appointment_id
                                   WHERE a.id = ?");
        $pat_stmt->execute([$appt_id]);
        $appt = $pat_stmt->fetch();

        if ($appt) {
            $msg = "Payment of LKR " . number_format($appt['amount'], 2) . " for appointment {$appt['appointment_number']} has been cleared successfully at clinic reception.";
            $notif_stmt = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
            $notif_stmt->execute([$appt['user_id'], $msg]);
        }

        $pdo->commit();
        $success = "Payment of LKR " . number_format($appt['amount'], 2) . " cleared successfully. Txn ID: **$transaction_id**";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Payment clearance failed: " . $e->getMessage();
    }
}

// 2. Fetch payments list (with search and status filters)
$search = trim($_GET['search'] ?? '');
$status_filter = trim($_GET['status'] ?? '');

$params = [];
$sql = "SELECT pay.*, a.appointment_number, a.appointment_date, a.queue_number,
               p.first_name AS pat_first, p.last_name AS pat_last, p.nic, p.patient_number,
               d.first_name AS doc_first, d.last_name AS doc_last 
        FROM payments pay
        JOIN appointments a ON pay.appointment_id = a.id
        JOIN patients p ON a.patient_id = p.id
        JOIN doctors d ON a.doctor_id = d.id";

$where = [];
if (!empty($search)) {
    $where[] = "(p.first_name LIKE :search OR p.last_name LIKE :search OR p.nic LIKE :search OR a.appointment_number LIKE :search)";
    $params['search'] = '%' . $search . '%';
}
if (!empty($status_filter)) {
    $where[] = "pay.payment_status = :status";
    $params['status'] = $status_filter;
}

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY pay.payment_date DESC, a.appointment_date DESC LIMIT 100";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $payments = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Failed to load payment logs: " . $e->getMessage();
    $payments = [];
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
        <h3><i class="fa-solid fa-file-invoice-dollar text-primary"></i> Clinic Payments registry</h3>
    </div>

    <!-- Filters -->
    <form method="GET" action="" style="display:flex; flex-wrap:wrap; gap:15px; margin-bottom:20px; align-items:center;">
        <div style="flex:2; min-width:250px;">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by Patient Name, NIC, Appt No..." class="form-control">
        </div>
        <div style="flex:1; min-width:150px;">
            <select name="status" class="form-control">
                <option value="">All Payment Statuses</option>
                <option value="Pending" <?php echo ($status_filter === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                <option value="Paid" <?php echo ($status_filter === 'Paid') ? 'selected' : ''; ?>>Paid</option>
                <option value="Refunded" <?php echo ($status_filter === 'Refunded') ? 'selected' : ''; ?>>Refunded</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary" style="padding:10px 20px;"><i class="fa-solid fa-filter"></i> Filter</button>
        <a href="payments.php" class="btn btn-secondary" style="padding:10px 15px;">Clear</a>
    </form>

    <div class="table-responsive">
        <table class="db-table">
            <thead>
                <tr>
                    <th>Invoice ID</th>
                    <th>Appt No / Date</th>
                    <th>Patient Name</th>
                    <th>Doctor</th>
                    <th>Total Charge</th>
                    <th>Method</th>
                    <th>Status</th>
                    <th>Transaction ID</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($payments)): ?>
                    <?php foreach ($payments as $pay): ?>
                        <tr>
                            <td><strong>#INV-<?php echo str_pad($pay['id'], 5, '0', STR_PAD_LEFT); ?></strong></td>
                            <td>
                                <strong style="display:block;"><?php echo htmlspecialchars($pay['appointment_number']); ?></strong>
                                <span style="font-size:0.8rem; color:var(--grey-color);"><?php echo date('d-M-Y', strtotime($pay['appointment_date'])); ?></span>
                            </td>
                            <td>
                                <strong style="display:block; font-size:0.9rem;"><?php echo htmlspecialchars($pay['pat_first'] . ' ' . $pay['pat_last']); ?></strong>
                                <span style="font-size:0.75rem; color:var(--grey-color);">NIC: <?php echo htmlspecialchars($pay['nic']); ?></span>
                            </td>
                            <td>Dr. <?php echo htmlspecialchars($pay['doc_first'] . ' ' . $pay['doc_last']); ?></td>
                            <td><strong>LKR <?php echo number_format($pay['amount'], 2); ?></strong></td>
                            <td><?php echo htmlspecialchars($pay['payment_method']); ?></td>
                            <td>
                                <?php
                                    $class = '';
                                    if ($pay['payment_status'] === 'Pending') $class = 'badge-pending';
                                    elseif ($pay['payment_status'] === 'Paid') $class = 'badge-paid';
                                    elseif ($pay['payment_status'] === 'Refunded') $class = 'badge-refunded';
                                ?>
                                <span class="badge <?php echo $class; ?>"><?php echo $pay['payment_status']; ?></span>
                            </td>
                            <td style="font-family: monospace; font-size: 0.8rem;">
                                <?php echo $pay['transaction_id'] ? $pay['transaction_id'] : '<span class="text-muted">N/A</span>'; ?>
                            </td>
                            <td>
                                <?php if ($pay['payment_status'] === 'Pending'): ?>
                                    <form action="" method="POST" style="display:inline-flex; gap:5px;">
                                        <input type="hidden" name="clear_pay_id" value="<?php echo $pay['appointment_id']; ?>">
                                        <select name="pay_method" class="form-control" style="padding: 4px 8px; font-size:0.75rem; height:30px; width:80px;">
                                            <option value="Cash">Cash</option>
                                            <option value="Card">Card</option>
                                        </select>
                                        <button type="submit" class="btn btn-primary" style="padding:4px 10px; font-size:0.75rem; border-radius:4px;"><i class="fa-solid fa-check"></i> Collect</button>
                                    </form>
                                <?php else: ?>
                                    <span style="font-size:0.8rem; color:var(--success-color); font-weight:600;"><i class="fa-solid fa-circle-check"></i> Cleared</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 30px; color: var(--grey-color);">No payment logs found matching criteria.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/dashboard_footer.php'; ?>
