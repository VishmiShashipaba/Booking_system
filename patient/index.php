<?php
// patient/index.php
// Patient Dashboard Main Overview

$page_title = "Patient Dashboard";
require_once dirname(__DIR__) . '/includes/dashboard_header.php';

// Access Control
checkAccess(['patient']);

$patient_id = $_SESSION['patient_id'];
$user_id = $_SESSION['user_id'];

// 1. Fetch Patient details
$p_stmt = $pdo->prepare("SELECT * FROM patients WHERE id = ?");
$p_stmt->execute([$patient_id]);
$patient = $p_stmt->fetch();

// 2. Fetch metrics
// Total bookings
$b_stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE patient_id = ?");
$b_stmt->execute([$patient_id]);
$total_bookings = $b_stmt->fetchColumn();

// Completed consultations
$c_stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE patient_id = ? AND status = 'Completed'");
$c_stmt->execute([$patient_id]);
$completed_bookings = $c_stmt->fetchColumn();

// Upcoming appointments (Pending or Checked-In)
$u_stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE patient_id = ? AND status IN ('Pending', 'Checked-In')");
$u_stmt->execute([$patient_id]);
$upcoming_bookings = $u_stmt->fetchColumn();

// 3. Fetch recent notifications
$notif_stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 6");
$notif_stmt->execute([$user_id]);
$notifications_list = $notif_stmt->fetchAll();

// Mark notifications as read
$mark_stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
$mark_stmt->execute([$user_id]);
?>

<!-- Welcome Banner with Dashboard Illustration -->
<div class="db-card" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%); color: white; border: none; padding: 0; border-radius: 16px; overflow:hidden; display:flex; align-items:stretch; min-height:140px;">
    <div style="flex:1; padding: 30px 35px; display:flex; flex-direction:column; justify-content:center;">
        <h2 style="color: white; font-family: 'Outfit'; margin-bottom: 8px; font-size:1.6rem;">
            <i class="fa-solid fa-hand-wave" style="margin-right:8px;"></i> Hello, <?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?>!
        </h2>
        <p style="margin: 0; font-size: 1rem; opacity: 0.9;">Welcome to your health channeling dashboard. Search doctors, manage appointments, and download digital prescriptions.</p>
        <div style="margin-top:18px; display:flex; gap:10px; flex-wrap:wrap;">
            <a href="book.php" style="background:white; color:var(--primary-color); padding:8px 18px; border-radius:8px; font-size:0.85rem; font-weight:600; text-decoration:none;"><i class="fa-solid fa-calendar-plus"></i> Book Appointment</a>
            <a href="history.php" style="background:rgba(255,255,255,0.15); color:white; padding:8px 18px; border-radius:8px; font-size:0.85rem; font-weight:600; text-decoration:none; border:1px solid rgba(255,255,255,0.3);"><i class="fa-solid fa-clock-rotate-left"></i> View History</a>
        </div>
    </div>
    <div style="width:200px; flex-shrink:0; overflow:hidden; display:flex; align-items:flex-end; justify-content:center;">
        <img src="<?php echo BASE_URL; ?>assets/images/dashboard_banner.png" 
             alt="Healthcare Dashboard" 
             style="height:140px; width:200px; object-fit:cover; object-position:center; opacity:0.9;">
    </div>
</div>

<!-- Metrics Cards -->
<div class="metrics-grid">
    <div class="metric-card">
        <div class="metric-card-left">
            <h4>Total Appointments</h4>
            <div class="value"><?php echo $total_bookings; ?></div>
        </div>
        <div class="metric-card-icon"><i class="fa-solid fa-calendar-check"></i></div>
    </div>
    
    <div class="metric-card success">
        <div class="metric-card-left">
            <h4>Completed Consultations</h4>
            <div class="value"><?php echo $completed_bookings; ?></div>
        </div>
        <div class="metric-card-icon"><i class="fa-solid fa-circle-check"></i></div>
    </div>
    
    <div class="metric-card warning">
        <div class="metric-card-left">
            <h4>Upcoming Visits</h4>
            <div class="value"><?php echo $upcoming_bookings; ?></div>
        </div>
        <div class="metric-card-icon"><i class="fa-solid fa-clock"></i></div>
    </div>
</div>

<div class="details-grid" style="grid-template-columns: 1.5fr 1fr; gap: 30px;">
    <!-- Profile Summary -->
    <div class="db-card">
        <div class="db-card-header">
            <h3><i class="fa-solid fa-user-circle text-primary"></i> Patient Profile Summary</h3>
            <a href="profile.php" class="btn btn-secondary" style="padding: 6px 14px; font-size:0.8rem; border-radius:4px;"><i class="fa-solid fa-edit"></i> Edit</a>
        </div>
        
        <!-- Patient Avatar + ID Banner -->
        <div style="display:flex; align-items:center; gap:18px; padding:20px; background:var(--secondary-color); border-radius:10px; margin-bottom:18px;">
            <div style="width:64px; height:64px; border-radius:50%; background:var(--primary-color); color:white; display:flex; align-items:center; justify-content:center; font-size:1.5rem; font-weight:700; font-family:'Outfit'; flex-shrink:0; box-shadow:0 4px 12px rgba(15,82,186,0.3);">
                <?php
                    $initials = strtoupper(substr($patient['first_name'],0,1) . substr($patient['last_name'],0,1));
                    echo $initials;
                ?>
            </div>
            <div>
                <h4 style="margin:0; font-size:1.05rem;"><?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></h4>
                <span style="font-size:0.8rem; color:var(--primary-color); font-weight:600; background:white; padding:3px 10px; border-radius:20px; display:inline-block; margin-top:4px;">
                    <i class="fa-solid fa-id-card"></i> <?php echo htmlspecialchars($patient['patient_number']); ?>
                </span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="db-table" style="font-size:0.95rem;">
                <tr>
                    <td style="font-weight:600; width:35%; color:var(--grey-color);"><i class="fa-solid fa-phone fa-sm"></i> Phone</td>
                    <td><?php echo htmlspecialchars($patient['phone']); ?></td>
                </tr>
                <tr>
                    <td style="font-weight:600; color:var(--grey-color);"><i class="fa-solid fa-id-badge fa-sm"></i> NIC</td>
                    <td><?php echo htmlspecialchars($patient['nic']); ?></td>
                </tr>
                <tr>
                    <td style="font-weight:600; color:var(--grey-color);"><i class="fa-solid fa-cake-candles fa-sm"></i> Date of Birth</td>
                    <td><?php echo date('d-M-Y', strtotime($patient['dob'])); ?></td>
                </tr>
                <tr>
                    <td style="font-weight:600; color:var(--grey-color);"><i class="fa-solid fa-venus-mars fa-sm"></i> Gender</td>
                    <td><?php echo htmlspecialchars($patient['gender']); ?></td>
                </tr>
                <tr>
                    <td style="font-weight:600; color:var(--grey-color);"><i class="fa-solid fa-location-dot fa-sm"></i> Address</td>
                    <td><?php echo htmlspecialchars($patient['address'] ? $patient['address'] : 'Not provided'); ?></td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Notifications Widget -->
    <div class="db-card" id="notifications">
        <div class="db-card-header">
            <h3><i class="fa-solid fa-bell text-primary"></i> Notifications & Reminders</h3>
        </div>
        
        <ul class="notification-list">
            <?php if (!empty($notifications_list)): ?>
                <?php foreach ($notifications_list as $notif): ?>
                    <li class="notification-item <?php echo $notif['is_read'] == 0 ? 'unread' : ''; ?>">
                        <div style="font-size: 0.88rem; line-height: 1.4; color: var(--dark-color);">
                            <?php echo htmlspecialchars($notif['message']); ?>
                        </div>
                        <div class="notification-time">
                            <i class="fa-regular fa-clock"></i> <?php echo date('d M Y, g:i A', strtotime($notif['created_at'])); ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li style="padding: 30px 0; text-align: center; color: var(--grey-color); list-style:none;">
                    <img src="<?php echo BASE_URL; ?>assets/images/empty_appointments.png" alt="No notifications" style="width:90px; opacity:0.6; margin-bottom:12px; display:block; margin-left:auto; margin-right:auto;">
                    <strong style="display:block; margin-bottom:4px;">All caught up!</strong>
                    <span style="font-size:0.85rem;">No notifications or reminders at this time.</span>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/dashboard_footer.php'; ?>
