<?php
// includes/dashboard_header.php
// Common dashboard header (implements layout grid and topbar stats)

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__) . '/includes/auth_check.php';

// Prevent direct access if not logged in
if (!isLoggedIn()) {
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// Fetch unread notifications count
$unread_notifs = 0;
try {
    $n_stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $n_stmt->execute([$_SESSION['user_id']]);
    $unread_notifs = $n_stmt->fetchColumn();
} catch (PDOException $e) {
    $unread_notifs = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . " - Portal" : "Prasanna PCC Portal"; ?></title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- StyleSheets -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/dashboard.css?v=<?php echo time(); ?>">

    <!-- Custom JS -->
    <script src="<?php echo BASE_URL; ?>assets/js/dashboard.js?v=<?php echo time(); ?>" defer></script>
</head>
<body class="dashboard-body">
    <div class="db-wrapper">
        <!-- Sidebar navigation -->
        <?php include_once dirname(__DIR__) . '/includes/sidebar.php'; ?>
        
        <!-- Main content area wrapper -->
        <div class="db-content">
            <!-- Topbar header -->
            <div class="db-topbar">
                <div class="db-topbar-left">
                    <div class="sidebar-toggle"><i class="fa-solid fa-bars-staggered"></i></div>
                    <div class="db-page-title"><?php echo isset($page_title) ? $page_title : 'Dashboard'; ?></div>
                </div>
                
                <div class="db-topbar-right">
                    <!-- Notifications Indicator -->
                    <div class="notification-bell" onclick="window.location.href='<?php echo BASE_URL; ?><?php echo ($_SESSION['role'] === 'patient') ? 'patient/index.php#notifications' : '#'; ?>'">
                        <i class="fa-regular fa-bell"></i>
                        <?php if ($unread_notifs > 0): ?>
                            <span class="notification-count"><?php echo $unread_notifs; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Logged-in User Profile + Logout -->
                    <div class="user-profile-menu" style="display:flex; align-items:center; gap:12px;">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div class="user-avatar">
                                <?php 
                                    $initials = '';
                                    if (isset($_SESSION['full_name']) && !empty($_SESSION['full_name'])) {
                                        $parts = explode(' ', trim($_SESSION['full_name']));
                                        $initials .= substr($parts[0], 0, 1);
                                        if (count($parts) > 1) {
                                            $initials .= substr(end($parts), 0, 1);
                                        }
                                    } else {
                                        $initials = substr($_SESSION['username'], 0, 2);
                                    }
                                    echo strtoupper(substr($initials, 0, 2));
                                ?>
                            </div>
                            <div class="user-info-text">
                                <span class="user-name"><?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']); ?></span>
                                <span class="user-role"><?php echo ucfirst(htmlspecialchars($_SESSION['role'])); ?></span>
                            </div>
                        </div>
                        <a href="<?php echo BASE_URL; ?>logout.php" title="Logout / Switch Account" style="color: var(--grey-color); font-size:1.1rem; text-decoration:none; padding: 5px 8px; border-radius:6px; transition: background 0.2s;" onmouseover="this.style.background='rgba(0,0,0,0.05)'" onmouseout="this.style.background='transparent'">
                            <i class="fa-solid fa-right-from-bracket"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="db-main-content">
