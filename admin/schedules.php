<?php
// admin/schedules.php
// Manage doctor weekly schedules

$page_title = "Manage Doctor Schedules";
require_once dirname(__DIR__) . '/includes/dashboard_header.php';

checkAccess(['admin']);

$success = "";
$error = "";

$doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;

// Fetch all active doctors for filter/selection
try {
    $doctors_list = $pdo->query("SELECT id, first_name, last_name, specialization FROM doctors WHERE status = 'Active' ORDER BY first_name ASC")->fetchAll();
} catch (PDOException $e) {
    $doctors_list = [];
}

// 1. Process Add Schedule Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_schedule'])) {
    $sched_doc_id = intval($_POST['doc_id'] ?? 0);
    $day_of_week = $_POST['day_of_week'] ?? '';
    $start_time = trim($_POST['start_time'] ?? '');
    $end_time = trim($_POST['end_time'] ?? '');
    $max_patients = intval($_POST['max_patients'] ?? 15);

    if ($sched_doc_id <= 0 || empty($day_of_week) || empty($start_time) || empty($end_time) || $max_patients <= 0) {
        $error = "Please fill in all schedule parameters.";
    } elseif (strtotime($start_time) >= strtotime($end_time)) {
        $error = "Session start time must be before end time.";
    } else {
        try {
            // Check if slot overlaps exactly
            $chk = $pdo->prepare("SELECT id FROM doctor_schedules WHERE doctor_id = ? AND day_of_week = ? AND start_time = ? AND end_time = ?");
            $chk->execute([$sched_doc_id, $day_of_week, $start_time, $end_time]);
            if ($chk->fetch()) {
                $error = "An identical schedule slot already exists for this doctor.";
            } else {
                $ins = $pdo->prepare("INSERT INTO doctor_schedules (doctor_id, day_of_week, start_time, end_time, max_patients) VALUES (?, ?, ?, ?, ?)");
                $ins->execute([$sched_doc_id, $day_of_week, $start_time, $end_time, $max_patients]);
                $success = "Schedule slot successfully registered.";
                
                // Keep doctor selected in view
                $doctor_id = $sched_doc_id;
            }
        } catch (PDOException $e) {
            $error = "Failed to register schedule: " . $e->getMessage();
        }
    }
}

// 2. Process Delete Schedule
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_schedule_id'])) {
    $del_id = intval($_POST['delete_schedule_id']);
    try {
        // First check if there are appointments booked for this schedule
        $chk_appt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE schedule_id = ? AND status != 'Cancelled'");
        $chk_appt->execute([$del_id]);
        
        if ($chk_appt->fetchColumn() > 0) {
            $error = "Cannot delete schedule slot. Active patient bookings are scheduled on this shift.";
        } else {
            $del = $pdo->prepare("DELETE FROM doctor_schedules WHERE id = ?");
            $del->execute([$del_id]);
            $success = "Schedule slot deleted successfully.";
        }
    } catch (PDOException $e) {
        $error = "Failed to delete schedule: " . $e->getMessage();
    }
}

// 3. Fetch doctor schedules based on selection
$schedules = [];
if ($doctor_id > 0) {
    try {
        $sched_stmt = $pdo->prepare("SELECT s.*, d.first_name, d.last_name 
                                     FROM doctor_schedules s 
                                     JOIN doctors d ON s.doctor_id = d.id 
                                     WHERE s.doctor_id = ? 
                                     ORDER BY FIELD(s.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), s.start_time ASC");
        $sched_stmt->execute([$doctor_id]);
        $schedules = $sched_stmt->fetchAll();
    } catch (PDOException $e) {
        $error = "Failed to fetch schedules: " . $e->getMessage();
    }
}
?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="db-card" style="margin-bottom: 25px;">
    <div class="db-card-header">
        <h3>Select Doctor to Manage Shifts</h3>
    </div>
    <form method="GET" action="">
        <div style="display:flex; gap:15px; align-items:center;">
            <select name="doctor_id" onchange="this.form.submit()" class="form-control" style="flex:1;">
                <option value="">-- Select Specialist Doctor --</option>
                <?php foreach ($doctors_list as $doc): ?>
                    <option value="<?php echo $doc['id']; ?>" <?php echo ($doctor_id == $doc['id']) ? 'selected' : ''; ?>>
                        Dr. <?php echo htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']); ?> (<?php echo htmlspecialchars($doc['specialization']); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <a href="schedules.php" class="btn btn-secondary" style="padding:10px 15px;">Clear</a>
        </div>
    </form>
</div>

<?php if ($doctor_id > 0): ?>
    <div style="display:grid; grid-template-columns: 1fr 1.5fr; gap:30px; align-items: start;">
        <!-- Add Schedule Slot Form -->
        <div class="db-card">
            <div class="db-card-header">
                <h3>Add Shift Slot</h3>
            </div>
            
            <form action="" method="POST">
                <input type="hidden" name="doc_id" value="<?php echo $doctor_id; ?>">
                <input type="hidden" name="add_schedule" value="1">

                <div class="form-group">
                    <label for="day_of_week">Day of Week *</label>
                    <select name="day_of_week" id="day_of_week" required class="form-control">
                        <option value="Monday">Monday</option>
                        <option value="Tuesday">Tuesday</option>
                        <option value="Wednesday">Wednesday</option>
                        <option value="Thursday">Thursday</option>
                        <option value="Friday">Friday</option>
                        <option value="Saturday">Saturday</option>
                        <option value="Sunday">Sunday</option>
                    </select>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                    <div class="form-group">
                        <label for="start_time">Start Time *</label>
                        <input type="time" name="start_time" id="start_time" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="end_time">End Time *</label>
                        <input type="time" name="end_time" id="end_time" required class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label for="max_patients">Maximum Patients Limit *</label>
                    <input type="number" name="max_patients" id="max_patients" required min="1" max="100" value="15" class="form-control">
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%; padding:12px; margin-top:10px;"><i class="fa-solid fa-calendar-plus"></i> Save Shift Slot</button>
            </form>
        </div>

        <!-- Schedules List for Doctor -->
        <div class="db-card">
            <div class="db-card-header">
                <h3>Shift List</h3>
            </div>
            
            <div class="table-responsive">
                <table class="db-table">
                    <thead>
                        <tr>
                            <th>Day</th>
                            <th>Shift Timings</th>
                            <th>Max Patients Limit</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($schedules)): ?>
                            <?php foreach ($schedules as $sched): ?>
                                <tr>
                                    <td><strong><?php echo $sched['day_of_week']; ?></strong></td>
                                    <td>
                                        <?php echo date("g:i A", strtotime($sched['start_time'])); ?> - <?php echo date("g:i A", strtotime($sched['end_time'])); ?>
                                    </td>
                                    <td><?php echo $sched['max_patients']; ?> Patients</td>
                                    <td>
                                        <form action="" method="POST" onsubmit="return confirm('Delete this schedule slot?');" style="display:inline;">
                                            <input type="hidden" name="delete_schedule_id" value="<?php echo $sched['id']; ?>">
                                            <button type="submit" class="btn btn-outline" style="padding:6px 12px; font-size:0.75rem; border-color:var(--danger-color); color:var(--danger-color); border-radius:4px;"><i class="fa-solid fa-trash-alt"></i> Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align:center; padding:20px; color:var(--grey-color);">No shifts registered for this doctor. Add one using the form on the left.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="card text-center" style="padding: 50px;">
        <i class="fa-solid fa-calendar-alt" style="font-size: 3.5rem; color:var(--grey-color); opacity:0.3; margin-bottom: 20px; display:block;"></i>
        <h4>Select a consulting doctor from the dropdown above to manage their weekly shifts.</h4>
    </div>
<?php endif; ?>

<?php require_once dirname(__DIR__) . '/includes/dashboard_footer.php'; ?>
