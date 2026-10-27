<?php
// admin/index.php
// Admin Dashboard Overview

$page_title = "Admin Dashboard";
require_once dirname(__DIR__) . '/includes/dashboard_header.php';

checkAccess(['admin']);

// 1. Fetch metrics
try {
    $doc_count = $pdo->query("SELECT COUNT(*) FROM doctors")->fetchColumn();
    $pat_count = $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();
    $recep_count = $pdo->query("SELECT COUNT(*) FROM receptionists")->fetchColumn();
    $tot_rev = $pdo->query("SELECT SUM(amount) FROM payments WHERE payment_status = 'Paid'")->fetchColumn() ?? 0;
} catch (PDOException $e) {
    $doc_count = $pat_count = $recep_count = $tot_rev = 0;
}

// 2. Fetch recent appointments
try {
    $appt_stmt = $pdo->query("SELECT a.*, p.first_name AS pat_first, p.last_name AS pat_last,
                                     d.first_name AS doc_first, d.last_name AS doc_last, pay.amount
                              FROM appointments a
                              JOIN patients p ON a.patient_id = p.id
                              JOIN doctors d ON a.doctor_id = d.id
                              JOIN payments pay ON a.id = pay.appointment_id
                              ORDER BY a.created_at DESC
                              LIMIT 5");
    $recent_appointments = $appt_stmt->fetchAll();
} catch (PDOException $e) {
    $recent_appointments = [];
}

// 3. Fetch departments
try {
    $dept_stmt = $pdo->query("SELECT * FROM departments ORDER BY name ASC");
    $departments = $dept_stmt->fetchAll();
} catch (PDOException $e) {
    $departments = [];
}
?>

<!-- Welcome Greeting -->
<div class="db-card" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%); color: white; border: none; padding: 35px; border-radius: 16px;">
    <h2 style="color: white; font-family: 'Outfit'; margin-bottom: 8px;">Welcome, System Administrator!</h2>
    <p style="margin: 0; font-size: 1.05rem; opacity: 0.9;">Manage specialists, clinic staff, patient profiles, departments, weekly schedules, payments, and print clinical reports.</p>
</div>

<!-- Metrics Cards -->
<div class="metrics-grid">
    <div class="metric-card">
        <div class="metric-card-left">
            <h4>Consulting Doctors</h4>
            <div class="value"><?php echo $doc_count; ?></div>
        </div>
        <div class="metric-card-icon"><i class="fa-solid fa-user-md"></i></div>
    </div>
    
    <div class="metric-card success">
        <div class="metric-card-left">
            <h4>Total Revenue</h4>
            <div class="value" style="font-size:1.6rem;">LKR <?php echo number_format($tot_rev, 2); ?></div>
        </div>
        <div class="metric-card-icon"><i class="fa-solid fa-money-bill-trend-up"></i></div>
    </div>
    
    <div class="metric-card warning">
        <div class="metric-card-left">
            <h4>Registered Patients</h4>
            <div class="value"><?php echo $pat_count; ?></div>
        </div>
        <div class="metric-card-icon"><i class="fa-solid fa-users"></i></div>
    </div>

    <div class="metric-card">
        <div class="metric-card-left">
            <h4>Receptionist Staff</h4>
            <div class="value"><?php echo $recep_count; ?></div>
        </div>
        <div class="metric-card-icon"><i class="fa-solid fa-user-tie"></i></div>
    </div>
</div>

<div class="details-grid" style="grid-template-columns: 1.6fr 1fr; gap: 30px;">
    <!-- Recent Appointments -->
    <div class="db-card">
        <div class="db-card-header">
            <h3><i class="fa-solid fa-calendar-check text-primary"></i> Recent System Bookings</h3>
            <a href="appointments.php" class="btn btn-secondary" style="padding:5px 12px; font-size:0.75rem; border-radius:4px;">Manage All</a>
        </div>
        
        <div class="table-responsive">
            <table class="db-table" style="font-size: 0.85rem;">
                <thead>
                    <tr>
                        <th>Appt No</th>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recent_appointments)): ?>
                        <?php foreach ($recent_appointments as $ra): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($ra['appointment_number']); ?></strong></td>
                                <td><?php echo htmlspecialchars($ra['pat_first'] . ' ' . $ra['pat_last']); ?></td>
                                <td>Dr. <?php echo htmlspecialchars($ra['doc_first'] . ' ' . $ra['doc_last']); ?></td>
                                <td><?php echo date('d-M-Y', strtotime($ra['appointment_date'])); ?></td>
                                <td>
                                    <?php 
                                        $cls = '';
                                        if ($ra['status'] === 'Pending') $cls = 'badge-pending';
                                        elseif ($ra['status'] === 'Checked-In') $cls = 'badge-checked-in';
                                        elseif ($ra['status'] === 'Completed') $cls = 'badge-completed';
                                        elseif ($ra['status'] === 'Cancelled') $cls = 'badge-cancelled';
                                    ?>
                                    <span class="badge <?php echo $cls; ?>"><?php echo $ra['status']; ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align:center; padding: 20px; color: var(--grey-color);">No bookings found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Clinical Departments -->
    <div class="db-card">
        <div class="db-card-header">
            <h3><i class="fa-solid fa-sitemap text-primary"></i> Clinical Departments</h3>
            <a href="departments.php" class="btn btn-secondary" style="padding:5px 12px; font-size:0.75rem; border-radius:4px;">Manage</a>
        </div>
        
        <div class="table-responsive">
            <table class="db-table" style="font-size:0.85rem;">
                <thead>
                    <tr>
                        <th>Dept Name</th>
                        <th>Desc</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($departments)): ?>
                        <?php foreach ($departments as $dept): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($dept['name']); ?></strong></td>
                                <td>
                                    <div style="max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                        <?php echo htmlspecialchars($dept['description']); ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2" style="text-align:center; padding: 20px; color: var(--grey-color);">No departments found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/dashboard_footer.php'; ?>
