<?php
// admin/receptionists.php
// Manage receptionist staff and login profiles

$page_title = "Manage Staff (Receptionists)";
require_once dirname(__DIR__) . '/includes/dashboard_header.php';

checkAccess(['admin']);

$success = "";
$error = "";

// 1. Process Add Receptionist Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_recep'])) {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($first_name) || empty($last_name) || empty($phone) || empty($username) || empty($email) || empty($password)) {
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
                $u_stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'receptionist')");
                $u_stmt->execute([$username, $email, $hashed_password]);
                $user_id = $pdo->lastInsertId();

                // B. Insert Receptionist
                $staff_number = 'R-' . str_pad($user_id, 5, '0', STR_PAD_LEFT);
                $r_stmt = $pdo->prepare("INSERT INTO receptionists (user_id, staff_number, first_name, last_name, phone) VALUES (?, ?, ?, ?, ?)");
                $r_stmt->execute([$user_id, $staff_number, $first_name, $last_name, $phone]);

                $pdo->commit();
                $success = "Receptionist $first_name $last_name registered successfully. ID: **$staff_number**";
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Failed to add receptionist: " . $e->getMessage();
        }
    }
}

// 2. Process Edit Receptionist Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_recep'])) {
    $recep_id = intval($_POST['recep_id'] ?? 0);
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($recep_id <= 0 || empty($first_name) || empty($last_name) || empty($phone) || empty($email)) {
        $error = "Please fill in all details for editing.";
    } else {
        try {
            // Fetch User ID
            $usr_stmt = $pdo->prepare("SELECT user_id FROM receptionists WHERE id = ?");
            $usr_stmt->execute([$recep_id]);
            $user_id = $usr_stmt->fetchColumn();

            if ($user_id) {
                // Verify email conflict
                $chk_email = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $chk_email->execute([$email, $user_id]);
                
                if ($chk_email->fetch()) {
                    $error = "Email address is already in use by another account.";
                } else {
                    $pdo->beginTransaction();

                    // A. Update Users email
                    $u_up = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
                    $u_up->execute([$email, $user_id]);

                    // B. Update Receptionists
                    $r_up = $pdo->prepare("UPDATE receptionists SET first_name = ?, last_name = ?, phone = ? WHERE id = ?");
                    $r_up->execute([$first_name, $last_name, $phone, $recep_id]);

                    $pdo->commit();
                    $success = "Receptionist $first_name updated successfully.";
                }
            } else {
                $error = "Receptionist profile not found.";
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Failed to update receptionist: " . $e->getMessage();
        }
    }
}

// 3. Fetch all receptionists
try {
    $recep_stmt = $pdo->query("SELECT r.*, u.email, u.username 
                               FROM receptionists r 
                               JOIN users u ON r.user_id = u.id
                               ORDER BY r.first_name ASC");
    $receptionists = $recep_stmt->fetchAll();
} catch (PDOException $e) {
    $receptionists = [];
}
?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div style="display:grid; grid-template-columns: 1fr 1.8fr; gap:30px; align-items: start;">
    <!-- Add/Edit Receptionist Panel -->
    <div class="db-card" id="formCard">
        <div class="db-card-header">
            <h3 id="formTitle"><i class="fa-solid fa-user-plus text-primary"></i> Add Receptionist Staff</h3>
        </div>

        <form action="" method="POST" id="recepForm">
            <input type="hidden" name="recep_id" id="edit_recep_id">
            <input type="hidden" name="add_recep" id="form_action_flag" value="1">

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                <div class="form-group">
                    <label for="first_name">First Name *</label>
                    <input type="text" name="first_name" id="first_name" required class="form-control" placeholder="Kavindi">
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name *</label>
                    <input type="text" name="last_name" id="last_name" required class="form-control" placeholder="Perera">
                </div>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number *</label>
                <input type="text" name="phone" id="phone" required class="form-control" placeholder="0711122334">
            </div>

            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" name="email" id="email" required class="form-control" placeholder="recep@prasanna.com">
            </div>

            <!-- Credentials (hidden on edit) -->
            <div id="credentialsWrapper">
                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" name="username" id="username" class="form-control" placeholder="receptionist">
                </div>
                <div class="form-group">
                    <label for="password">Initial Password *</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Set password">
                </div>
            </div>

            <div style="margin-top: 25px; display:flex; gap:10px;">
                <button type="button" class="btn btn-secondary" onclick="resetRecepForm()" style="flex:1;">Cancel / Reset</button>
                <button type="submit" class="btn btn-primary" id="submitBtn" style="flex:1.5;">Register Staff</button>
            </div>
        </form>
    </div>

    <!-- Staff Table -->
    <div class="db-card">
        <div class="db-card-header">
            <h3><i class="fa-solid fa-user-tie text-primary"></i> Clinic Receptionists Directory</h3>
        </div>

        <div class="table-responsive">
            <table class="db-table" style="font-size: 0.85rem;">
                <thead>
                    <tr>
                        <th>Staff ID</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Username</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($receptionists)): ?>
                        <?php foreach ($receptionists as $rep): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($rep['staff_number']); ?></strong></td>
                                <td><strong><?php echo htmlspecialchars($rep['first_name'] . ' ' . $rep['last_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($rep['phone']); ?></td>
                                <td><?php echo htmlspecialchars($rep['email']); ?></td>
                                <td><?php echo htmlspecialchars($rep['username']); ?></td>
                                <td>
                                    <button class="btn btn-secondary" style="padding: 6px 12px; font-size:0.75rem; border-radius:4px;" 
                                            onclick="editRecep(<?php echo htmlspecialchars(json_encode($rep)); ?>)">
                                        <i class="fa-solid fa-edit"></i> Edit
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align:center; padding: 20px;">No staff records available.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function editRecep(rep) {
    document.getElementById('formTitle').innerHTML = '<i class="fa-solid fa-user-edit text-primary"></i> Edit Staff Profile';
    document.getElementById('edit_recep_id').value = rep.id;
    document.getElementById('form_action_flag').name = 'edit_recep';
    document.getElementById('form_action_flag').value = '1';
    
    document.getElementById('first_name').value = rep.first_name;
    document.getElementById('last_name').value = rep.last_name;
    document.getElementById('phone').value = rep.phone;
    document.getElementById('email').value = rep.email;

    // Hide credentials wrapper
    document.getElementById('credentialsWrapper').style.display = 'none';
    
    document.getElementById('username').required = false;
    document.getElementById('password').required = false;

    document.getElementById('submitBtn').textContent = 'Save Changes';
    
    // Scroll to form card
    document.getElementById('formCard').scrollIntoView({ behavior: 'smooth' });
}

function resetRecepForm() {
    document.getElementById('formTitle').innerHTML = '<i class="fa-solid fa-user-plus text-primary"></i> Add Receptionist Staff';
    document.getElementById('edit_recep_id').value = '';
    document.getElementById('form_action_flag').name = 'add_recep';
    document.getElementById('form_action_flag').value = '1';
    
    document.getElementById('recepForm').reset();

    // Reset visibility
    document.getElementById('credentialsWrapper').style.display = 'block';
    
    document.getElementById('username').required = true;
    document.getElementById('password').required = true;

    document.getElementById('submitBtn').textContent = 'Register Staff';
}
</script>

<?php require_once dirname(__DIR__) . '/includes/dashboard_footer.php'; ?>
