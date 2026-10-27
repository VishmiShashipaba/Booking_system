<?php
// admin/reports.php
// Clinic Reports Generator

$page_title = "Clinic Reports Center";
require_once dirname(__DIR__) . '/includes/dashboard_header.php';

checkAccess(['admin']);

$success = "";
$error = "";

$report_type = $_GET['type'] ?? 'daily';
$today = date('Y-m-d');
$this_month = date('Y-m');

// Fetch doctors for filters
try {
    $doctors_list = $pdo->query("SELECT id, first_name, last_name FROM doctors ORDER BY first_name ASC")->fetchAll();
} catch (PDOException $e) {
    $doctors_list = [];
}

// Initialize report variables
$report_title = "";
$metrics = [];
$records = [];

// 1. DAILY APPOINTMENT REPORT
if ($report_type === 'daily') {
    $date = trim($_GET['date'] ?? $today);
    $report_title = "Daily Appointment Report for " . date('d-M-Y', strtotime($date));
    
    try {
        $stmt = $pdo->prepare("SELECT a.*, p.first_name AS pat_first, p.last_name AS pat_last, p.nic,
                                      d.first_name AS doc_first, d.last_name AS doc_last, pay.amount
                               FROM appointments a
                               JOIN patients p ON a.patient_id = p.id
                               JOIN doctors d ON a.doctor_id = d.id
                               JOIN payments pay ON a.id = pay.appointment_id
                               WHERE a.appointment_date = ? 
                               ORDER BY a.queue_number ASC");
        $stmt->execute([$date]);
        $records = $stmt->fetchAll();

        // Calculate metrics
        $total = count($records);
        $completed = $pending = $cancelled = $revenue = 0;
        foreach ($records as $r) {
            if ($r['status'] === 'Completed') $completed++;
            elseif ($r['status'] === 'Pending' || $r['status'] === 'Checked-In') $pending++;
            elseif ($r['status'] === 'Cancelled') $cancelled++;
            
            // Revenue from paid bills
            $pay_chk = $pdo->prepare("SELECT amount FROM payments WHERE appointment_id = ? AND payment_status = 'Paid'");
            $pay_chk->execute([$r['id']]);
            $revenue += $pay_chk->fetchColumn() ?? 0;
        }

        $metrics = [
            ['title' => 'Total Bookings', 'value' => $total, 'icon' => 'fa-calendar-days', 'class' => ''],
            ['title' => 'Completed Consultations', 'value' => $completed, 'icon' => 'fa-circle-check', 'class' => 'success'],
            ['title' => 'Pending / Checked-In', 'value' => $pending, 'icon' => 'fa-clock', 'class' => 'warning'],
            ['title' => 'Total Revenue Collected', 'value' => 'LKR ' . number_format($revenue, 2), 'icon' => 'fa-sack-dollar', 'class' => 'success']
        ];
    } catch (PDOException $e) {
        $error = "Report generation failed: " . $e->getMessage();
    }
}

// 2. MONTHLY APPOINTMENT REPORT
elseif ($report_type === 'monthly') {
    $month = trim($_GET['month'] ?? $this_month);
    $report_title = "Monthly Appointment Report for " . date('F Y', strtotime($month . '-01'));

    try {
        $stmt = $pdo->prepare("SELECT a.*, p.first_name AS pat_first, p.last_name AS pat_last,
                                      d.first_name AS doc_first, d.last_name AS doc_last, pay.amount
                               FROM appointments a
                               JOIN patients p ON a.patient_id = p.id
                               JOIN doctors d ON a.doctor_id = d.id
                               JOIN payments pay ON a.id = pay.appointment_id
                               WHERE DATE_FORMAT(a.appointment_date, '%Y-%m') = ? 
                               ORDER BY a.appointment_date ASC, a.queue_number ASC");
        $stmt->execute([$month]);
        $records = $stmt->fetchAll();

        // Calculate metrics
        $total = count($records);
        $completed = $cancelled = $revenue = 0;
        foreach ($records as $r) {
            if ($r['status'] === 'Completed') $completed++;
            elseif ($r['status'] === 'Cancelled') $cancelled++;
            
            $pay_chk = $pdo->prepare("SELECT amount FROM payments WHERE appointment_id = ? AND payment_status = 'Paid'");
            $pay_chk->execute([$r['id']]);
            $revenue += $pay_chk->fetchColumn() ?? 0;
        }

        $metrics = [
            ['title' => 'Monthly Total Bookings', 'value' => $total, 'icon' => 'fa-calendar-days', 'class' => ''],
            ['title' => 'Completed Visits', 'value' => $completed, 'icon' => 'fa-circle-check', 'class' => 'success'],
            ['title' => 'Cancelled Shifts', 'value' => $cancelled, 'icon' => 'fa-ban', 'class' => 'danger'],
            ['title' => 'Monthly Revenue', 'value' => 'LKR ' . number_format($revenue, 2), 'icon' => 'fa-sack-dollar', 'class' => 'success']
        ];
    } catch (PDOException $e) {
        $error = "Report generation failed: " . $e->getMessage();
    }
}

// 3. DOCTOR-WISE REPORT
elseif ($report_type === 'doctor') {
    $doc_id = intval($_GET['doctor_id'] ?? 0);
    
    if ($doc_id > 0) {
        try {
            $doc_info = $pdo->prepare("SELECT first_name, last_name, specialization FROM doctors WHERE id = ?");
            $doc_info->execute([$doc_id]);
            $d_profile = $doc_info->fetch();
            $report_title = "Doctor Consultations Report: Dr. " . htmlspecialchars($d_profile['first_name'] . ' ' . $d_profile['last_name']) . " (" . htmlspecialchars($d_profile['specialization']) . ")";

            $stmt = $pdo->prepare("SELECT a.*, p.first_name AS pat_first, p.last_name AS pat_last, p.nic, pay.amount, pay.payment_status 
                                   FROM appointments a
                                   JOIN patients p ON a.patient_id = p.id
                                   JOIN payments pay ON a.id = pay.appointment_id
                                   WHERE a.doctor_id = ? 
                                   ORDER BY a.appointment_date DESC");
            $stmt->execute([$doc_id]);
            $records = $stmt->fetchAll();

            $total = count($records);
            $completed = $cancelled = $revenue = 0;
            foreach ($records as $r) {
                if ($r['status'] === 'Completed') $completed++;
                elseif ($r['status'] === 'Cancelled') $cancelled++;
                
                if ($r['payment_status'] === 'Paid') {
                    $revenue += $r['amount'];
                }
            }

            $metrics = [
                ['title' => 'Total Booked Consults', 'value' => $total, 'icon' => 'fa-user-md', 'class' => ''],
                ['title' => 'Completed Sessions', 'value' => $completed, 'icon' => 'fa-circle-check', 'class' => 'success'],
                ['title' => 'Cancelled Sessions', 'value' => $cancelled, 'icon' => 'fa-ban', 'class' => 'danger'],
                ['title' => 'Total Paid Revenue', 'value' => 'LKR ' . number_format($revenue, 2), 'icon' => 'fa-sack-dollar', 'class' => 'success']
            ];
        } catch (PDOException $e) {
            $error = "Report generation failed: " . $e->getMessage();
        }
    } else {
        $report_title = "Doctor-wise Consultations Report";
    }
}

// 4. PATIENT REGISTRATION REPORT
elseif ($report_type === 'patient') {
    $start_date = trim($_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days')));
    $end_date = trim($_GET['end_date'] ?? $today);
    
    $report_title = "Patient Registration & Bookings Report (From $start_date To $end_date)";

    try {
        $stmt = $pdo->prepare("SELECT p.*, u.email, u.created_at AS reg_date,
                                      (SELECT COUNT(*) FROM appointments WHERE patient_id = p.id) AS total_appts 
                               FROM patients p
                               JOIN users u ON p.user_id = u.id
                               WHERE DATE(u.created_at) BETWEEN ? AND ?
                               ORDER BY u.created_at DESC");
        $stmt->execute([$start_date, $end_date]);
        $records = $stmt->fetchAll();

        $total_reg = count($records);
        $total_booked = 0;
        foreach ($records as $r) {
            $total_booked += $r['total_appts'];
        }

        $metrics = [
            ['title' => 'New Registrations', 'value' => $total_reg, 'icon' => 'fa-users', 'class' => 'success'],
            ['title' => 'Associated Appointments', 'value' => $total_booked, 'icon' => 'fa-calendar-check', 'class' => '']
        ];
    } catch (PDOException $e) {
        $error = "Report generation failed: " . $e->getMessage();
    }
}

// 5. PAYMENT / REVENUE REPORT
elseif ($report_type === 'payment') {
    $start_date = trim($_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days')));
    $end_date = trim($_GET['end_date'] ?? $today);

    $report_title = "Payment and Revenue Audit Report (From $start_date To $end_date)";

    try {
        $stmt = $pdo->prepare("SELECT pay.*, a.appointment_number, a.appointment_date,
                                      p.first_name AS pat_first, p.last_name AS pat_last,
                                      d.first_name AS doc_first, d.last_name AS doc_last 
                               FROM payments pay
                               JOIN appointments a ON pay.appointment_id = a.id
                               JOIN patients p ON a.patient_id = p.id
                               JOIN doctors d ON a.doctor_id = d.id
                               WHERE DATE(pay.payment_date) BETWEEN ? AND ? OR (pay.payment_status = 'Pending' AND a.appointment_date BETWEEN ? AND ?)
                               ORDER BY pay.payment_date DESC");
        $stmt->execute([$start_date, $end_date, $start_date, $end_date]);
        $records = $stmt->fetchAll();

        $total_bills = count($records);
        $cash_total = $card_total = $pending_total = $refund_total = $net_total = 0;
        foreach ($records as $r) {
            if ($r['payment_status'] === 'Paid') {
                if ($r['payment_method'] === 'Cash') $cash_total += $r['amount'];
                elseif ($r['payment_method'] === 'Card') $card_total += $r['amount'];
                $net_total += $r['amount'];
            } elseif ($r['payment_status'] === 'Pending') {
                $pending_total += $r['amount'];
            } elseif ($r['payment_status'] === 'Refunded') {
                $refund_total += $r['amount'];
            }
        }

        $metrics = [
            ['title' => 'Net Cleared Revenue', 'value' => 'LKR ' . number_format($net_total, 2), 'icon' => 'fa-sack-dollar', 'class' => 'success'],
            ['title' => 'Cash Collections', 'value' => 'LKR ' . number_format($cash_total, 2), 'icon' => 'fa-money-bill-wave', 'class' => ''],
            ['title' => 'Card Settlements', 'value' => 'LKR ' . number_format($card_total, 2), 'icon' => 'fa-credit-card', 'class' => ''],
            ['title' => 'Refunded Settlements', 'value' => 'LKR ' . number_format($refund_total, 2), 'icon' => 'fa-undo', 'class' => 'danger']
        ];
    } catch (PDOException $e) {
        $error = "Report generation failed: " . $e->getMessage();
    }
}

// 6. CANCELLED APPOINTMENTS REPORT
elseif ($report_type === 'cancelled') {
    $report_title = "Cancelled Appointments & Refunds Registry";

    try {
        $stmt = $pdo->query("SELECT a.*, p.first_name AS pat_first, p.last_name AS pat_last, p.nic,
                                    d.first_name AS doc_first, d.last_name AS doc_last, pay.amount, pay.payment_status 
                             FROM appointments a
                             JOIN patients p ON a.patient_id = p.id
                             JOIN doctors d ON a.doctor_id = d.id
                             JOIN payments pay ON a.id = pay.appointment_id
                             WHERE a.status = 'Cancelled' 
                             ORDER BY a.appointment_date DESC");
        $records = $stmt->fetchAll();

        $total_cancelled = count($records);
        $total_refunded = 0;
        foreach ($records as $r) {
            if ($r['payment_status'] === 'Refunded') {
                $total_refunded += $r['amount'];
            }
        }

        $metrics = [
            ['title' => 'Total Cancelled Channels', 'value' => $total_cancelled, 'icon' => 'fa-calendar-times', 'class' => 'danger'],
            ['title' => 'Refunded Card Amount', 'value' => 'LKR ' . number_format($total_refunded, 2), 'icon' => 'fa-undo', 'class' => 'warning']
        ];
    } catch (PDOException $e) {
        $error = "Report generation failed: " . $e->getMessage();
    }
}
?>

<!-- Report Category Selector (Tabs) -->
<div class="db-card no-print" style="margin-bottom:25px; padding: 15px 25px;">
    <h4 class="mb-3">Select Report Category</h4>
    <div style="display:flex; flex-wrap:wrap; gap:10px;">
        <a href="reports.php?type=daily" class="btn <?php echo ($report_type === 'daily') ? 'btn-primary' : 'btn-secondary'; ?>" style="font-size:0.8rem; padding:8px 16px;">Daily Bookings</a>
        <a href="reports.php?type=monthly" class="btn <?php echo ($report_type === 'monthly') ? 'btn-primary' : 'btn-secondary'; ?>" style="font-size:0.8rem; padding:8px 16px;">Monthly Bookings</a>
        <a href="reports.php?type=doctor" class="btn <?php echo ($report_type === 'doctor') ? 'btn-primary' : 'btn-secondary'; ?>" style="font-size:0.8rem; padding:8px 16px;">Doctor-wise</a>
        <a href="reports.php?type=patient" class="btn <?php echo ($report_type === 'patient') ? 'btn-primary' : 'btn-secondary'; ?>" style="font-size:0.8rem; padding:8px 16px;">Patient Registrations</a>
        <a href="reports.php?type=payment" class="btn <?php echo ($report_type === 'payment') ? 'btn-primary' : 'btn-secondary'; ?>" style="font-size:0.8rem; padding:8px 16px;">Payment & Revenue</a>
        <a href="reports.php?type=cancelled" class="btn <?php echo ($report_type === 'cancelled') ? 'btn-primary' : 'btn-secondary'; ?>" style="font-size:0.8rem; padding:8px 16px;">Cancelled / Refunds</a>
    </div>
</div>

<!-- Category Specific Filter Card -->
<div class="db-card no-print" style="margin-bottom: 25px;">
    <h4 style="margin-bottom:12px;"><i class="fa-solid fa-sliders"></i> Filter Parameters</h4>
    <form method="GET" action="">
        <input type="hidden" name="type" value="<?php echo $report_type; ?>">
        
        <div style="display:flex; flex-wrap:wrap; gap:15px; align-items:flex-end;">
            <?php if ($report_type === 'daily'): ?>
                <div>
                    <label style="display:block; font-size:0.8rem; font-weight:600; margin-bottom:5px;">Select Date</label>
                    <input type="date" name="date" value="<?php echo htmlspecialchars($_GET['date'] ?? $today); ?>" class="form-control" style="width:200px;">
                </div>
            <?php elseif ($report_type === 'monthly'): ?>
                <div>
                    <label style="display:block; font-size:0.8rem; font-weight:600; margin-bottom:5px;">Select Month</label>
                    <input type="month" name="month" value="<?php echo htmlspecialchars($_GET['month'] ?? $this_month); ?>" class="form-control" style="width:200px;">
                </div>
            <?php elseif ($report_type === 'doctor'): ?>
                <div>
                    <label style="display:block; font-size:0.8rem; font-weight:600; margin-bottom:5px;">Select Doctor</label>
                    <select name="doctor_id" class="form-control" style="width:250px;">
                        <option value="">-- Select Doctor --</option>
                        <?php foreach ($doctors_list as $doc): ?>
                            <option value="<?php echo $doc['id']; ?>" <?php echo (isset($_GET['doctor_id']) && $_GET['doctor_id'] == $doc['id']) ? 'selected' : ''; ?>>
                                Dr. <?php echo htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php elseif ($report_type === 'patient' || $report_type === 'payment'): ?>
                <div>
                    <label style="display:block; font-size:0.8rem; font-weight:600; margin-bottom:5px;">Start Date</label>
                    <input type="date" name="start_date" value="<?php echo htmlspecialchars($_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'))); ?>" class="form-control" style="width:180px;">
                </div>
                <div>
                    <label style="display:block; font-size:0.8rem; font-weight:600; margin-bottom:5px;">End Date</label>
                    <input type="date" name="end_date" value="<?php echo htmlspecialchars($_GET['end_date'] ?? $today); ?>" class="form-control" style="width:180px;">
                </div>
            <?php endif; ?>
            
            <button type="submit" class="btn btn-primary" style="padding:10px 20px;"><i class="fa-solid fa-cog"></i> Generate Report</button>
        </div>
    </form>
</div>

<!-- Generated Report View -->
<?php if ($report_title): ?>
    <!-- Printable Header (Visible only on print) -->
    <div class="print-only" style="display:none; border-bottom: 2px solid black; padding-bottom: 15px; margin-bottom: 30px;">
        <h2 style="font-family:'Outfit'; color:var(--primary-color); margin:0;">Prasanna Channel Center</h2>
        <p style="margin:5px 0 0 0; font-size:0.85rem; color:#444;">No. 120, Padiyatalawa Road, Mahiyanganaya, Sri Lanka<br>Tel: +94 55 123 4567 | info@prasannachannel.com</p>
    </div>

    <div class="db-card-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom: 2px solid var(--primary-color); padding-bottom: 10px;">
        <h2 style="font-family:'Outfit'; font-size: 1.5rem; margin:0;" class="text-primary"><?php echo $report_title; ?></h2>
        <button class="btn btn-accent no-print" onclick="window.print()" style="padding: 8px 18px; font-size:0.8rem;"><i class="fa-solid fa-print"></i> Print Report</button>
    </div>

    <!-- Summary Metrics Panel -->
    <?php if (!empty($metrics)): ?>
        <div class="metrics-grid">
            <?php foreach ($metrics as $m): ?>
                <div class="metric-card <?php echo $m['class']; ?>" style="border: 1px solid var(--light-grey);">
                    <div class="metric-card-left">
                        <h4><?php echo $m['title']; ?></h4>
                        <div class="value" style="font-size:1.5rem;"><?php echo $m['value']; ?></div>
                    </div>
                    <div class="metric-card-icon"><i class="fa-solid <?php echo $m['icon']; ?>"></i></div>
                </div>
            <?php endstyle; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Records Table -->
    <div class="db-card" style="border:1px solid var(--light-grey); padding: 20px;">
        <div class="table-responsive">
            <table class="db-table" style="font-size:0.8rem;">
                <!-- Table headers based on report type -->
                <?php if ($report_type === 'daily' || $report_type === 'monthly' || $report_type === 'doctor'): ?>
                    <thead>
                        <tr>
                            <th>Appt No</th>
                            <th>Queue No</th>
                            <th>Patient Name</th>
                            <th>Doctor Name</th>
                            <th>Date Scheduled</th>
                            <th>Consult Status</th>
                            <th>Paid Charge</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($records)): ?>
                            <?php foreach ($records as $r): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($r['appointment_number']); ?></strong></td>
                                    <td>#<?php echo $r['queue_number']; ?></td>
                                    <td><?php echo htmlspecialchars($r['pat_first'] . ' ' . $r['pat_last']); ?></td>
                                    <td>Dr. <?php echo htmlspecialchars($r['doc_first'] . ' ' . $r['doc_last']); ?></td>
                                    <td><?php echo date('d-M-Y', strtotime($r['appointment_date'])); ?></td>
                                    <td><span class="badge <?php echo ($r['status'] === 'Completed') ? 'badge-completed' : (($r['status'] === 'Cancelled') ? 'badge-cancelled' : 'badge-pending'); ?>"><?php echo $r['status']; ?></span></td>
                                    <td>LKR <?php echo number_format($r['amount'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align:center; padding:20px;">No consultations match this timeframe.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                
                <?php elseif ($report_type === 'patient'): ?>
                    <thead>
                        <tr>
                            <th>Patient ID</th>
                            <th>Patient Name</th>
                            <th>NIC Number</th>
                            <th>Phone</th>
                            <th>Gender / DOB</th>
                            <th>Registration Date</th>
                            <th>Total Visits</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($records)): ?>
                            <?php foreach ($records as $r): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($r['patient_number']); ?></strong></td>
                                    <td><strong><?php echo htmlspecialchars($r['first_name'] . ' ' . $r['last_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($r['nic']); ?></td>
                                    <td><?php echo htmlspecialchars($r['phone']); ?></td>
                                    <td><?php echo $r['gender']; ?> (<?php echo date('d-M-Y', strtotime($r['dob'])); ?>)</td>
                                    <td><?php echo date('d-M-Y g:i A', strtotime($r['reg_date'])); ?></td>
                                    <td><strong><?php echo $r['total_appts']; ?> Bookings</strong></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align:center; padding:20px;">No patient registrations in this period.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>

                <?php elseif ($report_type === 'payment'): ?>
                    <thead>
                        <tr>
                            <th>Invoice ID</th>
                            <th>Appt No</th>
                            <th>Appt Date</th>
                            <th>Patient Details</th>
                            <th>Consultant Doctor</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Date Audited</th>
                            <th>Fee</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($records)): ?>
                            <?php foreach ($records as $r): ?>
                                <tr>
                                    <td><strong>#INV-<?php echo str_pad($r['id'], 5, '0', STR_PAD_LEFT); ?></strong></td>
                                    <td><?php echo htmlspecialchars($r['appointment_number']); ?></td>
                                    <td><?php echo date('d-M-Y', strtotime($r['appointment_date'])); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($r['pat_first'] . ' ' . $r['pat_last']); ?></strong>
                                        <span style="display:block; font-size:0.7rem; color:var(--grey-color);">NIC: <?php echo htmlspecialchars($r['nic']); ?></span>
                                    </td>
                                    <td>Dr. <?php echo htmlspecialchars($r['doc_first'] . ' ' . $r['doc_last']); ?></td>
                                    <td><?php echo htmlspecialchars($r['payment_method']); ?></td>
                                    <td><span class="badge <?php echo ($r['payment_status'] === 'Paid') ? 'badge-paid' : (($r['payment_status'] === 'Refunded') ? 'badge-refunded' : 'badge-pending'); ?>"><?php echo $r['payment_status']; ?></span></td>
                                    <td><?php echo $r['payment_date'] ? date('d-M-Y g:i A', strtotime($r['payment_date'])) : 'N/A'; ?></td>
                                    <td><strong>LKR <?php echo number_format($r['amount'], 2); ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" style="text-align:center; padding:20px;">No financial statements meet these boundaries.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>

                <?php elseif ($report_type === 'cancelled'): ?>
                    <thead>
                        <tr>
                            <th>Appt No</th>
                            <th>Patient Name</th>
                            <th>NIC</th>
                            <th>Consultant Doctor</th>
                            <th>Date Scheduled</th>
                            <th>Queue</th>
                            <th>Refund Status</th>
                            <th>Cancellation Date</th>
                            <th>Fee</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($records)): ?>
                            <?php foreach ($records as $r): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($r['appointment_number']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($r['pat_first'] . ' ' . $r['pat_last']); ?></td>
                                    <td><?php echo htmlspecialchars($r['nic']); ?></td>
                                    <td>Dr. <?php echo htmlspecialchars($r['doc_first'] . ' ' . $r['doc_last']); ?></td>
                                    <td><?php echo date('d-M-Y', strtotime($r['appointment_date'])); ?></td>
                                    <td>#<?php echo $r['queue_number']; ?></td>
                                    <td><span class="badge <?php echo ($r['payment_status'] === 'Refunded') ? 'badge-refunded' : 'badge-pending'; ?>"><?php echo $r['payment_status']; ?></span></td>
                                    <td><?php echo date('d-M-Y g:i A', strtotime($r['created_at'])); ?></td>
                                    <td>LKR <?php echo number_format($r['amount'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" style="text-align:center; padding:20px;">No cancelled appointments registered in the ledger.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                <?php endif; ?>
            </table>
        </div>
        
        <!-- Print Authorizations Row (Only printed) -->
        <div class="print-only" style="display:none; margin-top:50px; justify-content:space-between; align-items:center; font-size:0.85rem; border-top:1px dashed #333; padding-top:20px;">
            <span>Report generated on: <?php echo date('d-M-Y g:i A'); ?> by Prasanna Admin Portal.</span>
            <div style="text-align:center; min-width:180px;">
                <div style="border-bottom:1px solid #000; height:30px; margin-bottom:5px;"></div>
                <strong>Director Signature</strong>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once dirname(__DIR__) . '/includes/dashboard_footer.php'; ?>
