<?php
// receptionist/patients.php
// Register walk-in patients and view patient directory

$page_title = "Manage Walk-in Patients";
require_once dirname(__DIR__) . '/includes/dashboard_header.php';

checkAccess(['receptionist']);

$success = "";
$error = "";

// 1. Handle Walk-in Registration Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_walkin'])) {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $nic = trim($_POST['nic'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $address = trim($_POST['address'] ?? '');

    if (empty($first_name) || empty($last_name) || empty($nic) || empty($phone) || empty($dob) || empty($gender)) {
        $error = "Please fill in all required fields.";
    } else {
        try {
            // Check if patient with NIC already exists
            $check_nic = $pdo->prepare("SELECT id FROM patients WHERE nic = ?");
            $check_nic->execute([$nic]);
            
            if ($check_nic->fetch()) {
                $error = "A patient with this NIC is already registered in the system.";
            } else {
                $pdo->beginTransaction();

                // Generate username and password for walk-in patient
                $username = strtolower($first_name) . rand(100, 999) . '_' . substr($nic, -4);
                $email = $username . '@prasannapcc.com';
                $default_password = 'pccwalkin123'; // Default password for walk-ins
                $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);

                // A. Insert into users
                $user_stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'patient')");
                $user_stmt->execute([$username, $email, $hashed_password]);
                $user_id = $pdo->lastInsertId();

                // B. Insert into patients
                $patient_number = 'P-' . str_pad($user_id, 5, '0', STR_PAD_LEFT);
                $pat_stmt = $pdo->prepare("INSERT INTO patients (user_id, patient_number, first_name, last_name, nic, dob, gender, phone, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $pat_stmt->execute([$user_id, $patient_number, $first_name, $last_name, $nic, $dob, $gender, $phone, $address]);

                // C. Add welcome notification
                $notif_msg = "Account created by receptionist. Username: $username, Default Password: $default_password. Please update your email and password inside your profile.";
                $notif_stmt = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                $notif_stmt->execute([$user_id, $notif_msg]);

                $pdo->commit();
                $success = "Patient successfully registered! Patient Number: **$patient_number** | Credentials: Username: **$username**, Password: **$default_password**";
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Registration failed: " . $e->getMessage();
        }
    }
}

// 2. Fetch/Search patient directory
$search = trim($_GET['search'] ?? '');
$params = [];
$sql = "SELECT * FROM patients";

if (!empty($search)) {
    $sql .= " WHERE first_name LIKE :search OR last_name LIKE :search OR nic LIKE :search OR phone LIKE :search OR patient_number LIKE :search";
    $params['search'] = '%' . $search . '%';
}
$sql .= " ORDER BY created_at DESC LIMIT 50";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $patients = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Failed to fetch patients list: " . $e->getMessage();
    $patients = [];
}
?>

<div style="display:grid; grid-template-columns: 1.2fr 1.8fr; gap:30px;">
    <!-- Registration Form -->
    <div class="db-card">
        <div class="db-card-header">
            <h3><i class="fa-solid fa-user-plus text-primary"></i> Register Walk-in Patient</h3>
        </div>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success" style="font-size:0.85rem;"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if (!empty($error) && isset($_POST['register_walkin'])): ?>
            <div class="alert alert-danger" style="font-size:0.85rem;"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <input type="hidden" name="register_walkin" value="1">

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                <div class="form-group">
                    <label for="first_name">First Name *</label>
                    <input type="text" name="first_name" id="first_name" required class="form-control" placeholder="Ruwan">
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name *</label>
                    <input type="text" name="last_name" id="last_name" required class="form-control" placeholder="Silva">
                </div>
            </div>

            <div class="form-group">
                <label for="nic">NIC Number *</label>
                <input type="text" name="nic" id="nic" required class="form-control" placeholder="e.g. 199012345678">
            </div>

            <div class="form-group">
                <label for="phone">Phone Number *</label>
                <input type="text" name="phone" id="phone" required class="form-control" placeholder="e.g. 0771234567">
            </div>

            <div style="display:grid; grid-template-columns: 1.2fr 1fr; gap:15px;">
                <div class="form-group">
                    <label for="dob">Date of Birth *</label>
                    <input type="date" name="dob" id="dob" required class="form-control">
                </div>
                <div class="form-group">
                    <label for="gender">Gender *</label>
                    <select name="gender" id="gender" required class="form-control">
                        <option value="">Select</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <textarea name="address" id="address" rows="2" class="form-control" placeholder="Padiyatalawa Road, Mahiyanganaya"></textarea>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%; padding:12px; margin-top:15px;"><i class="fa-solid fa-user-plus"></i> Register Walk-in</button>
        </form>
    </div>

    <!-- Patient Directory -->
    <div class="db-card">
        <div class="db-card-header">
            <h3><i class="fa-solid fa-users text-primary"></i> Patient Directory</h3>
        </div>

        <form method="GET" action="" style="display:flex; gap:10px; margin-bottom: 20px;">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by ID, Name, NIC, Phone..." class="form-control" style="flex:1;">
            <button type="submit" class="btn btn-primary" style="padding:10px 20px;"><i class="fa-solid fa-search"></i> Search</button>
            <a href="patients.php" class="btn btn-secondary" style="padding:10px 15px;">Clear</a>
        </form>

        <div class="table-responsive" style="max-height: 520px; overflow-y: auto;">
            <table class="db-table" style="font-size:0.85rem;">
                <thead>
                    <tr>
                        <th>Patient ID</th>
                        <th>Name</th>
                        <th>NIC</th>
                        <th>Phone</th>
                        <th>Age / Gender</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($patients)): ?>
                        <?php foreach ($patients as $pat): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($pat['patient_number']); ?></strong></td>
                                <td><?php echo htmlspecialchars($pat['first_name'] . ' ' . $pat['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($pat['nic']); ?></td>
                                <td><?php echo htmlspecialchars($pat['phone']); ?></td>
                                <td>
                                    <?php 
                                        $age = date_diff(date_create($pat['dob']), date_create('today'))->y;
                                        echo $age . ' Yrs (' . $pat['gender'] . ')';
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 20px; color: var(--grey-color);">No patients found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/dashboard_footer.php'; ?>
