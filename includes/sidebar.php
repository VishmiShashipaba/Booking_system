<?php
// includes/sidebar.php
// Contextual sidebar navigation for dashboard portals

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__DIR__) . '/config/db.php';

$role = $_SESSION['role'] ?? '';
$active_page = basename($_SERVER['PHP_SELF']);
// Get path folder name (e.g. 'admin', 'patient', 'doctor', 'receptionist')
$path_parts = explode('/', str_replace('\\', '/', dirname($_SERVER['PHP_SELF'])));
$current_folder = end($path_parts);
?>
<div class="db-sidebar">
    <div class="db-brand">
        <a href="<?php echo BASE_URL; ?>index.php" style="color: white; display: flex; align-items: center; gap:10px;">
            <i class="fa-solid fa-heart-pulse"></i>
            <span style="font-size:0.95rem; font-family:'Outfit'; font-weight:700; letter-spacing:0.5px;">PRASANNA CENTER</span>
        </a>
    </div>
    
    <ul class="db-menu">
        <?php if ($role === 'patient'): ?>
            <li class="db-menu-item <?php echo ($active_page === 'index.php' && $current_folder === 'patient') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>patient/index.php" class="db-menu-link">
                    <i class="fa-solid fa-home"></i>
                    <span>Overview</span>
                </a>
            </li>
            <li class="db-menu-item <?php echo ($active_page === 'profile.php') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>patient/profile.php" class="db-menu-link">
                    <i class="fa-solid fa-user-edit"></i>
                    <span>Edit Profile</span>
                </a>
            </li>
            <li class="db-menu-item <?php echo ($active_page === 'book.php') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>patient/book.php" class="db-menu-link">
                    <i class="fa-solid fa-calendar-plus"></i>
                    <span>Book Appointment</span>
                </a>
            </li>
            <li class="db-menu-item <?php echo ($active_page === 'history.php') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>patient/history.php" class="db-menu-link">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span>Booking History</span>
                </a>
            </li>
            <li class="db-menu-item <?php echo ($active_page === 'prescriptions.php') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>patient/prescriptions.php" class="db-menu-link">
                    <i class="fa-solid fa-file-prescription"></i>
                    <span>Prescriptions</span>
                </a>
            </li>

        <?php elseif ($role === 'doctor'): ?>
            <li class="db-menu-item <?php echo ($active_page === 'index.php' && $current_folder === 'doctor') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>doctor/index.php" class="db-menu-link">
                    <i class="fa-solid fa-clipboard-list"></i>
                    <span>Today's Queue</span>
                </a>
            </li>
            <li class="db-menu-item <?php echo ($active_page === 'prescription.php') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>doctor/prescription.php" class="db-menu-link">
                    <i class="fa-solid fa-file-signature"></i>
                    <span>Consult / Prescribe</span>
                </a>
            </li>

        <?php elseif ($role === 'receptionist'): ?>
            <li class="db-menu-item <?php echo ($active_page === 'index.php' && $current_folder === 'receptionist') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>receptionist/index.php" class="db-menu-link">
                    <i class="fa-solid fa-list-ol"></i>
                    <span>Daily Queue</span>
                </a>
            </li>
            <li class="db-menu-item <?php echo ($active_page === 'patients.php') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>receptionist/patients.php" class="db-menu-link">
                    <i class="fa-solid fa-hospital-user"></i>
                    <span>Register Walk-in</span>
                </a>
            </li>
            <li class="db-menu-item <?php echo ($active_page === 'book.php') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>receptionist/book.php" class="db-menu-link">
                    <i class="fa-solid fa-calendar-check"></i>
                    <span>Create Bookings</span>
                </a>
            </li>
            <li class="db-menu-item <?php echo ($active_page === 'payments.php') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>receptionist/payments.php" class="db-menu-link">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                    <span>Manage Payments</span>
                </a>
            </li>

        <?php elseif ($role === 'admin'): ?>
            <li class="db-menu-item <?php echo ($active_page === 'index.php' && $current_folder === 'admin') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/index.php" class="db-menu-link">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Overview Stats</span>
                </a>
            </li>
            <li class="db-menu-item <?php echo ($active_page === 'doctors.php') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/doctors.php" class="db-menu-link">
                    <i class="fa-solid fa-user-md"></i>
                    <span>Manage Doctors</span>
                </a>
            </li>
            <li class="db-menu-item <?php echo ($active_page === 'patients.php') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/patients.php" class="db-menu-link">
                    <i class="fa-solid fa-users"></i>
                    <span>Manage Patients</span>
                </a>
            </li>
            <li class="db-menu-item <?php echo ($active_page === 'receptionists.php') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/receptionists.php" class="db-menu-link">
                    <i class="fa-solid fa-user-tie"></i>
                    <span>Manage Staff</span>
                </a>
            </li>
            <li class="db-menu-item <?php echo ($active_page === 'departments.php') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/departments.php" class="db-menu-link">
                    <i class="fa-solid fa-building-user"></i>
                    <span>Departments</span>
                </a>
            </li>
            <li class="db-menu-item <?php echo ($active_page === 'schedules.php') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/schedules.php" class="db-menu-link">
                    <i class="fa-solid fa-calendar-days"></i>
                    <span>Doctor Shifts</span>
                </a>
            </li>
            <li class="db-menu-item <?php echo ($active_page === 'appointments.php') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/appointments.php" class="db-menu-link">
                    <i class="fa-solid fa-calendar-check"></i>
                    <span>Appointments</span>
                </a>
            </li>
            <li class="db-menu-item <?php echo ($active_page === 'payments.php') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/payments.php" class="db-menu-link">
                    <i class="fa-solid fa-credit-card"></i>
                    <span>Payments</span>
                </a>
            </li>
            <li class="db-menu-item <?php echo ($active_page === 'reports.php') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/reports.php" class="db-menu-link">
                    <i class="fa-solid fa-file-invoice"></i>
                    <span>Reports</span>
                </a>
            </li>
            <li class="db-menu-item <?php echo ($active_page === 'db_backup.php') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/db_backup.php" class="db-menu-link">
                    <i class="fa-solid fa-database"></i>
                    <span>Database Backup</span>
                </a>
            </li>
        <?php endif; ?>
    </ul>
    
    <div class="db-footer">
        <span style="font-size:0.75rem;">Account:</span>
        <strong style="color: white; display:block; font-size:0.85rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']); ?></strong>
    </div>
</div>
