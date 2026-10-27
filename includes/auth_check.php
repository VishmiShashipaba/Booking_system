<?php
// includes/auth_check.php
// Authentication and authorization helper functions

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__) . '/config/db.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserRole() {
    return $_SESSION['role'] ?? null;
}

function checkAccess($allowed_roles = []) {
    if (!isLoggedIn()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header("Location: " . BASE_URL . "login.php");
        exit();
    }
    
    if (!empty($allowed_roles)) {
        $role = getUserRole();
        if (!in_array($role, $allowed_roles)) {
            // Unauthorized access
            header("HTTP/1.1 403 Forbidden");
            
            // Render 403 view
            ?>
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Access Denied - Prasanna Channel Center</title>
                <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
                <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
            </head>
            <body style="display: flex; align-items: center; justify-content: center; min-height: 100vh; background-color: var(--secondary-color); margin: 0; font-family: 'Inter', sans-serif;">
                <div class="card" style="max-width: 500px; width: 90%; text-align: center; padding: 40px; border-radius: 12px; background: white; box-shadow: 0 10px 30px rgba(0,0,0,0.05);">
                    <div style="font-size: 5rem; color: var(--danger-color); margin-bottom: 20px;">
                        &#x26A0;
                    </div>
                    <h1 style="font-family: 'Outfit', sans-serif; color: var(--dark-color); margin-bottom: 10px;">Access Denied</h1>
                    <p style="color: #666; margin-bottom: 30px; line-height: 1.5;">You do not have the required permissions to access this dashboard. Please log in with an authorized account.</p>
                    <div style="display: flex; gap: 15px; justify-content: center;">
                        <a href="<?php echo BASE_URL; ?>" class="btn btn-secondary" style="text-decoration: none; padding: 10px 20px; border-radius: 6px; font-weight: 500;">Go Home</a>
                        <a href="<?php echo BASE_URL; ?>logout.php" class="btn btn-primary" style="text-decoration: none; padding: 10px 20px; border-radius: 6px; font-weight: 500;">Log Out</a>
                    </div>
                </div>
            </body>
            </html>
            <?php
            exit();
        }
    }
}
?>
