<?php
// doctors.php
// Public Doctors directory and schedules

$page_title = "Doctors & Schedules - Prasanna Channel Center";
include_once 'includes/header.php';

// Fetch departments for filter dropdown
try {
    $depts = $pdo->query("SELECT * FROM departments ORDER BY name ASC")->fetchAll();
} catch (PDOException $e) {
    $depts = [];
}

// Build Search query
$where = ["d.status = 'Active'"];
$params = [];

if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search = trim($_GET['search']);
    $where[] = "(d.first_name LIKE :search OR d.last_name LIKE :search OR d.specialization LIKE :search)";
    $params['search'] = '%' . $search . '%';
}

if (isset($_GET['department_id']) && !empty(trim($_GET['department_id']))) {
    $dept_id = intval($_GET['department_id']);
    $where[] = "d.department_id IN (SELECT id FROM departments WHERE name = (SELECT name FROM departments WHERE id = :dept_id))";
    $params['dept_id'] = $dept_id;
}

$sql = "SELECT d.*, dept.name AS dept_name 
        FROM doctors d 
        JOIN departments dept ON d.department_id = dept.id 
        WHERE " . implode(' AND ', $where) . " 
        ORDER BY d.first_name ASC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $doctors_list = $stmt->fetchAll();
} catch (PDOException $e) {
    $doctors_list = [];
}
?>

<section class="py-5" style="background: linear-gradient(135deg, var(--secondary-color) 0%, #ffffff 100%);">
    <div class="container text-center">
        <h1 style="font-size: 2.8rem; margin-bottom: 15px;">Our Specialists & Schedules</h1>
        <p style="font-size: 1.1rem; color: var(--grey-color); max-width: 800px; margin: 0 auto;">Browse through our consulting doctors and find a schedule that fits your timing. Book an appointment online to secure your queue slot.</p>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <!-- Search & Filter Bar -->
        <form method="GET" action="" style="background: white; padding: 20px; border-radius: 12px; box-shadow: var(--shadow-sm); border: 1px solid var(--light-grey); margin-bottom: 40px; display: flex; flex-wrap: wrap; gap: 15px; align-items: center;">
            <div style="flex: 1.5; min-width: 250px;">
                <label for="search" style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:5px;">Search Doctor Name or Specialization</label>
                <div style="position:relative;">
                    <input type="text" name="search" id="search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" placeholder="e.g. Prasanna or Cardiologist" class="form-control" style="padding-left: 40px;">
                    <i class="fa-solid fa-search" style="position:absolute; left:15px; top:15px; color:var(--grey-color);"></i>
                </div>
            </div>
            
            <div style="flex: 1; min-width: 200px;">
                <label for="department_id" style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:5px;">Filter by Department</label>
                <select name="department_id" id="department_id" class="form-control">
                    <option value="">All Departments</option>
                    <?php foreach ($depts as $dept): ?>
                        <option value="<?php echo $dept['id']; ?>" <?php echo (isset($_GET['department_id']) && $_GET['department_id'] == $dept['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($dept['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary" style="padding: 10px 24px;"><i class="fa-solid fa-filter"></i> Apply Filters</button>
                <a href="doctors.php" class="btn btn-secondary" style="padding: 10px 20px;">Reset</a>
            </div>
        </form>

        <!-- Doctors Grid -->
        <div class="grid-3">
        <?php if (!empty($doctors_list)): ?>
                <?php 
                $female_names = ['sanduni','amara','dilani','priya','kumari','nirosha','sachini'];
                foreach ($doctors_list as $doc): 
                    if (!empty($doc['image_path'])) {
                        $doc_photo = BASE_URL . 'images/' . $doc['image_path'];
                    } else {
                        $is_female = in_array(strtolower($doc['first_name']), $female_names);
                        $doc_photo = BASE_URL . 'assets/images/' . ($is_female ? 'doctor_female.png' : 'doctor_male.png');
                    }
                ?>
                    <div class="doctor-card">
                        <div class="doctor-img" style="padding:0; overflow:hidden; height:220px; position:relative;">
                            <img src="<?php echo $doc_photo; ?>" 
                                 alt="Dr. <?php echo htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']); ?>"
                                 style="width:100%; height:100%; object-fit:cover; object-position:top; display:block; transition:transform 0.3s ease;"
                                 onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                        </div>
                        <div class="doctor-info">
                            <h3 style="margin-bottom:2px;">Dr. <?php echo htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']); ?></h3>
                            <div class="spec" style="margin-bottom: 12px;"><?php echo htmlspecialchars($doc['specialization']); ?></div>
                            
                            <div style="font-size: 0.9rem; margin-bottom: 15px; display:flex; justify-content: space-between; border-bottom: 1px solid var(--light-grey); padding-bottom: 10px;">
                                <span style="color:var(--grey-color);">Consultation Fee:</span>
                                <strong class="text-primary">LKR <?php echo number_format($doc['charge'], 2); ?></strong>
                            </div>

                            <h5 style="font-size: 0.85rem; font-weight:600; text-transform: uppercase; color: var(--grey-color); margin-bottom: 10px;"><i class="fa-regular fa-clock"></i> Weekly Consultation Slots</h5>
                            
                            <div class="doctor-schedule-list" style="border:none; padding:0; margin-bottom:20px;">
                                <?php
                                    // Fetch schedules for this doctor
                                    try {
                                        $sched_stmt = $pdo->prepare("SELECT * FROM doctor_schedules WHERE doctor_id = :doc_id ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')");
                                        $sched_stmt->execute(['doc_id' => $doc['id']]);
                                        $schedules = $sched_stmt->fetchAll();
                                    } catch (PDOException $e) {
                                        $schedules = [];
                                    }
                                ?>
                                <?php if (!empty($schedules)): ?>
                                    <?php foreach ($schedules as $sched): ?>
                                        <div class="schedule-row" style="margin-bottom: 8px;">
                                            <span class="day"><?php echo $sched['day_of_week']; ?></span>
                                            <span>
                                                <?php echo date("g:i A", strtotime($sched['start_time'])); ?> - <?php echo date("g:i A", strtotime($sched['end_time'])); ?>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p style="font-size: 0.8rem; font-style: italic; color: var(--danger-color); margin: 0;">No active schedule slots defined.</p>
                                <?php endif; ?>
                            </div>

                            <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'patient'): ?>
                                <a href="<?php echo BASE_URL; ?>patient/book.php?doctor_id=<?php echo $doc['id']; ?>" class="btn btn-primary" style="width:100%; font-size: 0.9rem; padding: 10px 0;">Book Appointment</a>
                            <?php else: ?>
                                <a href="<?php echo BASE_URL; ?>login.php?redirect=book&doctor_id=<?php echo $doc['id']; ?>" class="btn btn-primary" style="width:100%; font-size: 0.9rem; padding: 10px 0;">Book Appointment</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="card text-center" style="grid-column: 1 / -1; padding: 50px;">
                    <img src="<?php echo BASE_URL; ?>assets/images/empty_appointments.png" alt="No doctors found" style="width:140px; opacity:0.75; margin-bottom:20px; display:block; margin-left:auto; margin-right:auto;">
                    <h3 style="margin-bottom: 10px;">No Doctors Found</h3>
                    <p style="margin:0; color:var(--grey-color);">No active specialists matched your search queries. Try resetting filters.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include_once 'includes/footer.php'; ?>
