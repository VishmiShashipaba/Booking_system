<?php
// admin/payments.php
// View clinic financial logs

$page_title = "Billing & Financial Registry";
require_once dirname(__DIR__) . '/includes/dashboard_header.php';

checkAccess(['admin']);

$error = "";

// Fetch totals
try {
    $paid_total = $pdo->query("SELECT SUM(amount) FROM payments WHERE payment_status = 'Paid'")->fetchColumn() ?? 0.00;
    $pending_total = $pdo->query("SELECT SUM(amount) FROM payments WHERE payment_status = 'Pending'")->fetchColumn() ?? 0.00;
    $refunded_total = $pdo->query("SELECT SUM(amount) FROM payments WHERE payment_status = 'Refunded'")->fetchColumn() ?? 0.00;
} catch (PDOException $e) {
    $paid_total = $pending_total = $refunded_total = 0.00;
}

// Filters
$search = trim($_GET['search'] ?? '');
$method = trim($_GET['method'] ?? '');
$status = trim($_GET['status'] ?? '');

$params = [];
$sql = "SELECT pay.*, a.appointment_number, a.appointment_date,
               p.first_name AS pat_first, p.last_name AS pat_last, p.nic,
               d.first_name AS doc_first, d.last_name AS doc_last 
        FROM payments pay
        JOIN appointments a ON pay.appointment_id = a.id
        JOIN patients p ON a.patient_id = p.id
        JOIN doctors d ON a.doctor_id = d.id";

$where = [];
if (!empty($search)) {
    $where[] = "(p.first_name LIKE :search OR p.last_name LIKE :search OR a.appointment_number LIKE :search OR pay.transaction_id LIKE :search)";
    $params['search'] = '%' . $search . '%';
}
if (!empty($method)) {
    $where[] = "pay.payment_method = :method";
    $params['method'] = $method;
}
if (!empty($status)) {
    $where[] = "pay.payment_status = :status";
    $params['status'] = $status;
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
    $error = "Failed to load financial registry: " . $e->getMessage();
    $payments = [];
}
?>

<!-- Financial Stats Cards -->
<div class="metrics-grid">
    <div class="metric-card success">
        <div class="metric-card-left">
            <h4>Total Cleared Revenue</h4>
            <div class="value" style="font-size:1.5rem;">LKR <?php echo number_format($paid_total, 2); ?></div>
        </div>
        <div class="metric-card-icon"><i class="fa-solid fa-circle-check"></i></div>
    </div>
    
    <div class="metric-card warning">
        <div class="metric-card-left">
            <h4>Total Pending Payments</h4>
            <div class="value" style="font-size:1.5rem;">LKR <?php echo number_format($pending_total, 2); ?></div>
        </div>
        <div class="metric-card-icon"><i class="fa-solid fa-file-invoice"></i></div>
    </div>
    
    <div class="metric-card danger">
        <div class="metric-card-left">
            <h4>Total Refunded Amount</h4>
            <div class="value" style="font-size:1.5rem;">LKR <?php echo number_format($refunded_total, 2); ?></div>
        </div>
        <div class="metric-card-icon"><i class="fa-solid fa-undo"></i></div>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="db-card">
    <div class="db-card-header">
        <h3><i class="fa-solid fa-file-invoice-dollar text-primary"></i> Master Billing Logs</h3>
    </div>

    <!-- Filters Form -->
    <form method="GET" action="" style="display:flex; flex-wrap:wrap; gap:15px; margin-bottom:20px; align-items:center;">
        <div style="flex:2; min-width:250px;">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search Patient, Appt No, Txn ID..." class="form-control">
        </div>
        <div style="flex:1; min-width:150px;">
            <select name="method" class="form-control">
                <option value="">All Methods</option>
                <option value="Cash" <?php echo ($method === 'Cash') ? 'selected' : ''; ?>>Cash</option>
                <option value="Card" <?php echo ($method === 'Card') ? 'selected' : ''; ?>>Card</option>
            </select>
        </div>
        <div style="flex:1; min-width:150px;">
            <select name="status" class="form-control">
                <option value="">All Statuses</option>
                <option value="Pending" <?php echo ($status === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                <option value="Paid" <?php echo ($status === 'Paid') ? 'selected' : ''; ?>>Paid</option>
                <option value="Refunded" <?php echo ($status === 'Refunded') ? 'selected' : ''; ?>>Refunded</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary" style="padding:10px 20px;"><i class="fa-solid fa-filter"></i> Apply</button>
        <a href="payments.php" class="btn btn-secondary" style="padding:10px 15px;">Clear</a>
    </form>

    <div class="table-responsive">
        <table class="db-table" style="font-size:0.85rem;">
            <thead>
                <tr>
                    <th>Invoice ID</th>
                    <th>Appt No</th>
                    <th>Appt Date</th>
                    <th>Patient Name (NIC)</th>
                    <th>Doctor</th>
                    <th>Method</th>
                    <th>Status</th>
                    <th>Total Amount</th>
                    <th>Transaction ID</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($payments)): ?>
                    <?php foreach ($payments as $pay): ?>
                        <tr>
                            <td><strong>#INV-<?php echo str_pad($pay['id'], 5, '0', STR_PAD_LEFT); ?></strong></td>
                            <td><strong><?php echo htmlspecialchars($pay['appointment_number']); ?></strong></td>
                            <td><?php echo date('d-M-Y', strtotime($pay['appointment_date'])); ?></td>
                            <td>
                                <strong style="display:block;"><?php echo htmlspecialchars($pay['pat_first'] . ' ' . $pay['pat_last']); ?></strong>
                                <span style="font-size:0.75rem; color:var(--grey-color);">NIC: <?php echo htmlspecialchars($pay['nic']); ?></span>
                            </td>
                            <td>Dr. <?php echo htmlspecialchars($pay['doc_first'] . ' ' . $pay['doc_last']); ?></td>
                            <td><?php echo htmlspecialchars($pay['payment_method']); ?></td>
                            <td>
                                <?php
                                    $c = '';
                                    if ($pay['payment_status'] === 'Pending') $c = 'badge-pending';
                                    elseif ($pay['payment_status'] === 'Paid') $c = 'badge-paid';
                                    elseif ($pay['payment_status'] === 'Refunded') $c = 'badge-refunded';
                                ?>
                                <span class="badge <?php echo $c; ?>"><?php echo $pay['payment_status']; ?></span>
                            </td>
                            <td><strong>LKR <?php echo number_format($pay['amount'], 2); ?></strong></td>
                            <td style="font-family: monospace; font-size: 0.8rem;"><?php echo $pay['transaction_id'] ? $pay['transaction_id'] : '<span class="text-muted">N/A</span>'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="text-align:center; padding: 20px; color:var(--grey-color);">No payment logs found matching criteria.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/dashboard_footer.php'; ?>
