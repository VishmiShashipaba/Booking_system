<?php
// admin/patients.php
// Manage patient profiles

$page_title = "Manage Patients";
require_once dirname(__DIR__) . '/includes/dashboard_header.php';

checkAccess(['admin']);

$success = "";
$error = "";

// 1. Process Edit Patient Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_patient'])) {
    $patient_id = intval($_POST['patient_id'] ?? 0);
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $nic = trim($_POST['nic'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($patient_id <= 0 || empty($first_name) || empty($last_name) || empty($nic) || empty($dob) || empty($gender) || empty($phone) || empty($email)) {
        $error = "Please fill in all required fields.";
    } else {
        try {
            // Fetch User ID
            $usr_stmt = $pdo->prepare("SELECT user_id FROM patients WHERE id = ?");
            $usr_stmt->execute([$patient_id]);
            $user_id = $usr_stmt->fetchColumn();

            if ($user_id) {
                // Verify email and NIC conflict
                $chk_email = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $chk_email->execute([$email, $user_id]);

                $chk_nic = $pdo->prepare("SELECT id FROM patients WHERE nic = ? AND id != ?");
                $chk_nic->execute([$nic, $patient_id]);
                
                if ($chk_email->fetch()) {
                    $error = "Email address is already in use by another account.";
                } elseif ($chk_nic->fetch()) {
                    $error = "NIC number is already registered for another patient.";
                } else {
                    $pdo->beginTransaction();

                    // A. Update Users table email
                    $u_up = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
                    $u_up->execute([$email, $user_id]);

                    // B. Update Patients table
                    $p_up = $pdo->prepare("UPDATE patients SET first_name = ?, last_name = ?, nic = ?, dob = ?, gender = ?, phone = ?, address = ? WHERE id = ?");
                    $p_up->execute([$first_name, $last_name, $nic, $dob, $gender, $phone, $address, $patient_id]);

                    $pdo->commit();
                    $success = "Patient profile for $first_name $last_name updated successfully.";
                }
            } else {
                $error = "Patient profile not found.";
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Failed to update patient: " . $e->getMessage();
        }
    }
}

// 2. Fetch all patients (with search filter)
$search = trim($_GET['search'] ?? '');
$params = [];
$sql = "SELECT p.*, u.email, u.username 
        FROM patients p 
        JOIN users u ON p.user_id = u.id";

if (!empty($search)) {
    $sql .= " WHERE p.first_name LIKE :search OR p.last_name LIKE :search OR p.nic LIKE :search OR p.phone LIKE :search OR p.patient_number LIKE :search";
    $params['search'] = '%' . $search . '%';
}
$sql .= " ORDER BY p.created_at DESC LIMIT 100";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $patients = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Failed to load patient ledger: " . $e->getMessage();
    $patients = [];
}
?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div style="display:grid; grid-template-columns: 1.2fr 2fr; gap:30px; align-items: start;">
    <!-- Edit Patient Panel (Dynamic form) -->
    <div class="db-card" id="formCard">
        <div class="db-card-header">
            <h3 id="formTitle"><i class="fa-solid fa-user-gear text-primary"></i> Patient Profile Editor</h3>
        </div>
        <p style="font-size:0.85rem; color:var(--grey-color); margin-bottom: 20px;">Click the <strong>Edit Profile</strong> button on the patient list to populate this form.</p>

        <form action="" method="POST" id="patForm">
            <input type="hidden" name="patient_id" id="edit_patient_id">
            <input type="hidden" name="edit_patient" value="1">

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                <div class="form-group">
                    <label for="first_name">First Name *</label>
                    <input type="text" name="first_name" id="first_name" required disabled class="form-control" style="background-color:#f1f5f9;">
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name *</label>
                    <input type="text" name="last_name" id="last_name" required disabled class="form-control" style="background-color:#f1f5f9;">
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                <div class="form-group">
                    <label for="nic">NIC Number *</label>
                    <input type="text" name="nic" id="nic" required disabled class="form-control" style="background-color:#f1f5f9;">
                </div>
                <div class="form-group">
                    <label for="phone">Phone *</label>
                    <input type="text" name="phone" id="phone" required disabled class="form-control" style="background-color:#f1f5f9;">
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                <div class="form-group">
                    <label for="dob">Date of Birth *</label>
                    <input type="date" name="dob" id="dob" required disabled class="form-control" style="background-color:#f1f5f9;">
                </div>
                <div class="form-group">
                    <label for="gender">Gender *</label>
                    <select name="gender" id="gender" required disabled class="form-control" style="background-color:#f1f5f9;">
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="address">Postal Address</label>
                <textarea name="address" id="address" rows="2" disabled class="form-control" style="background-color:#f1f5f9;"></textarea>
            </div>

            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" name="email" id="email" required disabled class="form-control" style="background-color:#f1f5f9;">
            </div>

            <div style="margin-top: 25px; display:flex; gap:10px;">
                <button type="button" class="btn btn-secondary" onclick="resetPatientForm()" style="flex:1;">Cancel / Reset</button>
                <button type="submit" class="btn btn-primary" id="submitBtn" disabled style="flex:1.5; background-color:var(--grey-color); border-color:var(--grey-color);">Save Changes</button>
            </div>
        </form>
    </div>

    <!-- Patients List -->
    <div class="db-card">
        <div class="db-card-header">
            <h3><i class="fa-solid fa-users text-primary"></i> Patients Registry</h3>
        </div>

        <form method="GET" action="" style="display:flex; gap:10px; margin-bottom: 20px;">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by ID, Name, NIC, Phone..." class="form-control" style="flex:1;">
            <button type="submit" class="btn btn-primary" style="padding:10px 20px;"><i class="fa-solid fa-search"></i> Search</button>
            <a href="patients.php" class="btn btn-secondary" style="padding:10px 15px;">Clear</a>
        </form>

        <div class="table-responsive">
            <table class="db-table" style="font-size: 0.85rem;">
                <thead>
                    <tr>
                        <th>Patient ID</th>
                        <th>Name</th>
                        <th>NIC / Phone</th>
                        <th>Age / Gender</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($patients)): ?>
                        <?php foreach ($patients as $pat): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($pat['patient_number']); ?></strong></td>
                                <td><strong><?php echo htmlspecialchars($pat['first_name'] . ' ' . $pat['last_name']); ?></strong></td>
                                <td>
                                    <span>NIC: <strong><?php echo htmlspecialchars($pat['nic']); ?></strong></span><br>
                                    <span style="font-size:0.75rem; color:var(--grey-color);">Tel: <?php echo htmlspecialchars($pat['phone']); ?></span>
                                </td>
                                <td>
                                    <?php 
                                        $age = date_diff(date_create($pat['dob']), date_create('today'))->y;
                                        echo $age . ' Yrs (' . $pat['gender'] . ')';
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($pat['email']); ?></td>
                                <td>
                                    <button class="btn btn-secondary" style="padding: 6px 12px; font-size:0.75rem; border-radius:4px;" 
                                            onclick="editPatient(<?php echo htmlspecialchars(json_encode($pat)); ?>)">
                                        <i class="fa-solid fa-edit"></i> Edit Profile
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align:center; padding: 20px;">No patient records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function editPatient(pat) {
    document.getElementById('formTitle').innerHTML = '<i class="fa-solid fa-user-gear text-primary"></i> Edit Patient Profile';
    document.getElementById('edit_patient_id').value = pat.id;
    
    // Enable fields and remove styled bg-color
    const inputs = ['first_name', 'last_name', 'nic', 'phone', 'dob', 'gender', 'address', 'email'];
    inputs.forEach(id => {
        const el = document.getElementById(id);
        el.disabled = false;
        el.style.backgroundColor = 'white';
        el.value = pat[id];
    });

    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = false;
    submitBtn.style.backgroundColor = 'var(--primary-color)';
    submitBtn.style.borderColor = 'var(--primary-color)';
    
    // Scroll to form card
    document.getElementById('formCard').scrollIntoView({ behavior: 'smooth' });
}

function resetPatientForm() {
    document.getElementById('formTitle').innerHTML = '<i class="fa-solid fa-user-gear text-primary"></i> Patient Profile Editor';
    document.getElementById('edit_patient_id').value = '';
    document.getElementById('patForm').reset();

    // Disable fields and add disabled style back
    const inputs = ['first_name', 'last_name', 'nic', 'phone', 'dob', 'gender', 'address', 'email'];
    inputs.forEach(id => {
        const el = document.getElementById(id);
        el.disabled = true;
        el.style.backgroundColor = '#f1f5f9';
    });

    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.style.backgroundColor = 'var(--grey-color)';
    submitBtn.style.borderColor = 'var(--grey-color)';
}
</script>

<?php require_once dirname(__DIR__) . '/includes/dashboard_footer.php'; ?>
