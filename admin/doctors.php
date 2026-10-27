<?php
// admin/doctors.php
// Manage doctor profiles & credentials

$page_title = "Manage Doctors";
require_once dirname(__DIR__) . '/includes/dashboard_header.php';

checkAccess(['admin']);

$success = "";
$error = "";

// Fetch departments for form selection
try {
    $depts = $pdo->query("SELECT * FROM departments ORDER BY name ASC")->fetchAll();
} catch (PDOException $e) {
    $depts = [];
}

// 1. Process Add Doctor Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_doctor'])) {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $dept_id = intval($_POST['department_id'] ?? 0);
    $specialization = trim($_POST['specialization'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $charge = floatval($_POST['charge'] ?? 0.00);
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($first_name) || empty($last_name) || $dept_id <= 0 || empty($specialization) || empty($phone) || empty($username) || empty($email) || empty($password)) {
        $error = "Please fill in all required fields.";
    } else {
        try {
            // Verify duplicates
            $chk = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $chk->execute([$username, $email]);
            if ($chk->fetch()) {
                $error = "Username or email is already in use.";
            } else {
                $pdo->beginTransaction();

                // A. Insert User
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $u_stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'doctor')");
                $u_stmt->execute([$username, $email, $hashed_password]);
                $user_id = $pdo->lastInsertId();

                // B. Insert Doctor
                $doctor_number = 'D-' . str_pad($user_id, 5, '0', STR_PAD_LEFT);
                $d_stmt = $pdo->prepare("INSERT INTO doctors (user_id, doctor_number, first_name, last_name, department_id, specialization, phone, charge, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Active')");
                $d_stmt->execute([$user_id, $doctor_number, $first_name, $last_name, $dept_id, $specialization, $phone, $charge]);

                $pdo->commit();
                $success = "Doctor Dr. $first_name $last_name registered successfully. ID: **$doctor_number**";
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Failed to add doctor: " . $e->getMessage();
        }
    }
}

// 2. Process Edit Doctor Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_doctor'])) {
    $doc_id = intval($_POST['doc_id'] ?? 0);
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $dept_id = intval($_POST['department_id'] ?? 0);
    $specialization = trim($_POST['specialization'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $charge = floatval($_POST['charge'] ?? 0.00);
    $status = $_POST['status'] ?? 'Active';
    $email = trim($_POST['email'] ?? '');

    if ($doc_id <= 0 || empty($first_name) || empty($last_name) || $dept_id <= 0 || empty($specialization) || empty($phone) || empty($email)) {
        $error = "Please fill in all details for editing.";
    } else {
        try {
            // Fetch User ID
            $usr_stmt = $pdo->prepare("SELECT user_id FROM doctors WHERE id = ?");
            $usr_stmt->execute([$doc_id]);
            $user_id = $usr_stmt->fetchColumn();

            if ($user_id) {
                // Verify email conflict
                $chk_email = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $chk_email->execute([$email, $user_id]);
                
                if ($chk_email->fetch()) {
                    $error = "Email address is already in use by another account.";
                } else {
                    $pdo->beginTransaction();

                    // A. Update Users table email
                    $u_up = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
                    $u_up->execute([$email, $user_id]);

                    // B. Update Doctors table
                    $d_up = $pdo->prepare("UPDATE doctors SET first_name = ?, last_name = ?, department_id = ?, specialization = ?, phone = ?, charge = ?, status = ? WHERE id = ?");
                    $d_up->execute([$first_name, $last_name, $dept_id, $specialization, $phone, $charge, $status, $doc_id]);

                    $pdo->commit();
                    $success = "Doctor Dr. $first_name updated successfully.";
                }
            } else {
                $error = "Doctor profile not found.";
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Failed to update doctor: " . $e->getMessage();
        }
    }
}

// 3. Fetch all doctors
try {
    $doctors_stmt = $pdo->query("SELECT d.*, dept.name AS dept_name, u.email, u.username 
                                 FROM doctors d 
                                 JOIN departments dept ON d.department_id = dept.id 
                                 JOIN users u ON d.user_id = u.id
                                 ORDER BY d.first_name ASC");
    $doctors = $doctors_stmt->fetchAll();
} catch (PDOException $e) {
    $doctors = [];
}
?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div style="display:grid; grid-template-columns: 1fr 2fr; gap:30px; align-items: start;">
    <!-- Add Doctor Panel -->
    <div class="db-card" id="formCard">
        <div class="db-card-header">
            <h3 id="formTitle"><i class="fa-solid fa-user-plus text-primary"></i> Register New Specialist</h3>
        </div>

        <form action="" method="POST" id="docForm">
            <!-- Hidden inputs for edit mode -->
            <input type="hidden" name="doc_id" id="edit_doc_id">
            <input type="hidden" name="add_doctor" id="form_action_flag" value="1">

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                <div class="form-group">
                    <label for="first_name">First Name *</label>
                    <input type="text" name="first_name" id="first_name" required class="form-control" placeholder="Prasanna">
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name *</label>
                    <input type="text" name="last_name" id="last_name" required class="form-control" placeholder="Jayamaha">
                </div>
            </div>

            <div class="form-group">
                <label for="department_id">Clinical Department *</label>
                <select name="department_id" id="department_id" required class="form-control">
                    <option value="">Select Department</option>
                    <?php foreach ($depts as $d): ?>
                        <option value="<?php echo $d['id']; ?>"><?php echo htmlspecialchars($d['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="specialization">Specialization / Designation *</label>
                <input type="text" name="specialization" id="specialization" required class="form-control" placeholder="Consultant Cardiologist">
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1.2fr; gap:15px;">
                <div class="form-group">
                    <label for="phone">Phone *</label>
                    <input type="text" name="phone" id="phone" required class="form-control" placeholder="0771234567">
                </div>
                <div class="form-group">
                    <label for="charge">Consultation Fee (LKR) *</label>
                    <input type="number" name="charge" id="charge" step="50.00" required class="form-control" placeholder="1500.00">
                </div>
            </div>

            <div class="form-group" id="statusGroup" style="display:none;">
                <label for="status">Account Status *</label>
                <select name="status" id="status" class="form-control">
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>

            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" name="email" id="email" required class="form-control" placeholder="prasanna@prasanna.com">
            </div>

            <!-- Credentials Wrapper (hidden on edit) -->
            <div id="credentialsWrapper">
                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" name="username" id="username" class="form-control" placeholder="drprasanna">
                </div>
                <div class="form-group">
                    <label for="password">Initial Password *</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Set password">
                </div>
            </div>

            <div style="margin-top: 25px; display:flex; gap:10px;">
                <button type="button" class="btn btn-secondary" onclick="resetDoctorForm()" style="flex:1;">Cancel / Reset</button>
                <button type="submit" class="btn btn-primary" id="submitBtn" style="flex:1.5;">Register Doctor</button>
            </div>
        </form>
    </div>

    <!-- Doctors Table -->
    <div class="db-card">
        <div class="db-card-header">
            <h3><i class="fa-solid fa-user-md text-primary"></i> Consultant Doctors Registry</h3>
        </div>

        <div class="table-responsive">
            <table class="db-table" style="font-size: 0.85rem;">
                <thead>
                    <tr>
                        <th>Doc ID</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Specialization</th>
                        <th>Fee</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($doctors)): ?>
                        <?php foreach ($doctors as $doc): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($doc['doctor_number']); ?></strong></td>
                                <td>
                                    <strong style="display:block;">Dr. <?php echo htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']); ?></strong>
                                    <span style="font-size:0.75rem; color:var(--grey-color);">Tel: <?php echo htmlspecialchars($doc['phone']); ?><br>Email: <?php echo htmlspecialchars($doc['email']); ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($doc['dept_name']); ?></td>
                                <td><?php echo htmlspecialchars($doc['specialization']); ?></td>
                                <td>LKR <?php echo number_format($doc['charge'], 2); ?></td>
                                <td>
                                    <span class="badge <?php echo ($doc['status'] === 'Active') ? 'badge-completed' : 'badge-cancelled'; ?>">
                                        <?php echo $doc['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display:flex; flex-direction:column; gap:5px;">
                                        <button class="btn btn-secondary" style="padding: 4px 8px; font-size:0.75rem; border-radius:4px;" 
                                                onclick="editDoctor(<?php echo htmlspecialchars(json_encode($doc)); ?>)">
                                            <i class="fa-solid fa-edit"></i> Edit Profile
                                        </button>
                                        <a href="schedules.php?doctor_id=<?php echo $doc['id']; ?>" class="btn btn-primary" style="padding: 4px 8px; font-size:0.75rem; border-radius:4px; text-align:center;">
                                            <i class="fa-solid fa-calendar-days"></i> Shifts
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align:center; padding: 20px;">No doctor records available.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function editDoctor(doc) {
    document.getElementById('formTitle').innerHTML = '<i class="fa-solid fa-user-edit text-primary"></i> Edit Doctor Profile';
    document.getElementById('edit_doc_id').value = doc.id;
    document.getElementById('form_action_flag').name = 'edit_doctor';
    document.getElementById('form_action_flag').value = '1';
    
    document.getElementById('first_name').value = doc.first_name;
    document.getElementById('last_name').value = doc.last_name;
    document.getElementById('department_id').value = doc.department_id;
    document.getElementById('specialization').value = doc.specialization;
    document.getElementById('phone').value = doc.phone;
    document.getElementById('charge').value = doc.charge;
    document.getElementById('email').value = doc.email;
    document.getElementById('status').value = doc.status;

    // Show status group and hide credentials wrapper
    document.getElementById('statusGroup').style.display = 'block';
    document.getElementById('credentialsWrapper').style.display = 'none';
    
    // Disable required flags for username/password on edit
    document.getElementById('username').required = false;
    document.getElementById('password').required = false;

    document.getElementById('submitBtn').textContent = 'Save Changes';
    
    // Scroll to form card
    document.getElementById('formCard').scrollIntoView({ behavior: 'smooth' });
}

function resetDoctorForm() {
    document.getElementById('formTitle').innerHTML = '<i class="fa-solid fa-user-plus text-primary"></i> Register New Specialist';
    document.getElementById('edit_doc_id').value = '';
    document.getElementById('form_action_flag').name = 'add_doctor';
    document.getElementById('form_action_flag').value = '1';
    
    document.getElementById('docForm').reset();

    // Reset visibility
    document.getElementById('statusGroup').style.display = 'none';
    document.getElementById('credentialsWrapper').style.display = 'block';
    
    document.getElementById('username').required = true;
    document.getElementById('password').required = true;

    document.getElementById('submitBtn').textContent = 'Register Doctor';
}
</script>

<?php require_once dirname(__DIR__) . '/includes/dashboard_footer.php'; ?>
