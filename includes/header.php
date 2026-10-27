<?php
// includes/header.php
// Common header for public website pages

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__DIR__) . '/config/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . " - Prasanna Channel Center" : "Prasanna Channel Center - Appointment Booking System"; ?></title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom StyleSheets -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>
    <header>
        <div class="container navbar">
            <a href="<?php echo BASE_URL; ?>index.php" class="logo">
                <i class="fa-solid fa-heart-pulse"></i>
                <span style="font-family: 'Outfit', sans-serif;">Prasanna Channel Center</span>
            </a>
            
            <ul class="nav-links">
                <li><a href="<?php echo BASE_URL; ?>index.php">Home </a></li>
                <li><a href="<?php echo BASE_URL; ?>about.php">About Us</a></li>
                <li><a href="<?php echo BASE_URL; ?>services.php">Services</a></li>
                <li><a href="<?php echo BASE_URL; ?>doctors.php">Doctors</a></li>
                <li><a href="<?php echo BASE_URL; ?>contact.php">Contact Us</a></li>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php 
                        $dashboard_path = BASE_URL;
                        if ($_SESSION['role'] === 'patient') $dashboard_path .= 'patient/index.php';
                        elseif ($_SESSION['role'] === 'doctor') $dashboard_path .= 'doctor/index.php';
                        elseif ($_SESSION['role'] === 'receptionist') $dashboard_path .= 'receptionist/index.php';
                        elseif ($_SESSION['role'] === 'admin') $dashboard_path .= 'admin/index.php';
                    ?>
                    <li><a href="<?php echo $dashboard_path; ?>" style="color: var(--primary-color); font-weight: 600;"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
                    <li><a href="<?php echo BASE_URL; ?>logout.php" style="color: var(--danger-color);"><i class="fa-solid fa-sign-out-alt"></i> Logout</a></li>
                <?php else: ?>
                    <li><a href="<?php echo BASE_URL; ?>login.php">Login</a></li>
                    <li><a href="<?php echo BASE_URL; ?>register.php">Register</a></li>
                <?php endif; ?>
            </ul>

            <div class="nav-btn">
                <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'patient'): ?>
                    <a href="<?php echo BASE_URL; ?>patient/book.php" class="btn btn-primary">Book Appointment</a>
                <?php elseif (!isset($_SESSION['user_id'])): ?>
                    <a href="<?php echo BASE_URL; ?>login.php?redirect=book" class="btn btn-primary">Book Appointment</a>
                <?php endif; ?>
            </div>

            <div class="menu-toggle">
                <i class="fa-solid fa-bars"></i>
            </div>
        </div>
    </header>
