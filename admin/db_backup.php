<?php
// admin/db_backup.php
// Pure PHP Database Backup Utility

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/auth_check.php';

checkAccess(['admin']);

// Process download request
if (isset($_GET['download']) && $_GET['download'] == 1) {
    try {
        $tables = ['users', 'departments', 'patients', 'doctors', 'receptionists', 'doctor_schedules', 'appointments', 'payments', 'prescriptions', 'notifications', 'feedback'];
        
        $sql_dump = "-- Prasanna Channel Center Appointment Booking System\n";
        $sql_dump .= "-- Database SQL Backup Dump\n";
        $sql_dump .= "-- Generated on: " . date('d-M-Y H:i:s') . "\n";
        $sql_dump .= "-- Location: Mahiyanganaya, Sri Lanka\n\n";
        
        $sql_dump .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
        
        foreach ($tables as $table) {
            // Get Create Table syntax
            $stmt = $pdo->query("SHOW CREATE TABLE `$table`");
            $row = $stmt->fetch(PDO::FETCH_NUM);
            
            $sql_dump .= "DROP TABLE IF EXISTS `$table`;\n";
            $sql_dump .= $row[1] . ";\n\n";
            
            // Get Table contents
            $stmt_data = $pdo->query("SELECT * FROM `$table`");
            $rows = $stmt_data->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($rows) > 0) {
                $sql_dump .= "INSERT INTO `$table` VALUES \n";
                $inserts = [];
                foreach ($rows as $r) {
                    $escaped_values = [];
                    foreach ($r as $key => $val) {
                        if (is_null($val)) {
                            $escaped_values[] = 'NULL';
                        } else {
                            $escaped_values[] = $pdo->quote($val);
                        }
                    }
                    $inserts[] = "(" . implode(", ", $escaped_values) . ")";
                }
                $sql_dump .= implode(",\n", $inserts) . ";\n\n";
            }
        }
        
        $sql_dump .= "SET FOREIGN_KEY_CHECKS=1;\n";
        
        // Output headers to trigger direct browser file download
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="prasanna_backup_' . date('Y-m-d_H-i-s') . '.sql"');
        header('Content-Length: ' . strlen($sql_dump));
        header('Pragma: no-cache');
        header('Expires: 0');
        
        echo $sql_dump;
        exit();
    } catch (PDOException $e) {
        $error = "Backup generation failed: " . $e->getMessage();
    }
}

// Render administrative page chrome if no download was processed
$page_title = "Database Backup System";
require_once dirname(__DIR__) . '/includes/dashboard_header.php';
?>

<div class="db-card" style="max-width: 600px; margin: 0 auto; text-align: center; padding: 45px 30px;">
    <div style="background-color: var(--secondary-color); color: var(--primary-color); width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin: 0 auto 25px auto;">
        <i class="fa-solid fa-database"></i>
    </div>
    
    <h3 style="font-family:'Outfit'; font-size: 1.4rem; margin-bottom: 12px;">Database Exporter Utility</h3>
    <p style="color: var(--grey-color); font-size: 0.95rem; line-height: 1.6; margin-bottom: 30px;">
        Generate and download a complete SQL backup file for the Prasanna Channel Center system. 
        This backup records all table schemas, keys, constraints, and rows (including accounts, patients, doctor schedules, appointments, prescriptions, and financial payments).
    </p>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger" style="margin-bottom:20px;"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div style="display:flex; flex-direction:column; gap:12px; align-items:center;">
        <a href="db_backup.php?download=1" class="btn btn-primary" style="padding: 12px 30px; font-weight:600;"><i class="fa-solid fa-download"></i> Download SQL Backup File</a>
        <span style="font-size:0.75rem; color:var(--grey-color);">Format: standard `.sql` script | Encoded in UTF-8</span>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/dashboard_footer.php'; ?>
