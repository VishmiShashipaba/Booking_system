<?php
// patient/profile.php
// Edit patient profile

$page_title = "Edit Profile";
require_once dirname(__DIR__) . '/includes/dashboard_header.php';

checkAccess(['patient']);

$patient_id = $_SESSION['patient_id'];
$user_id = $_SESSION['user_id'];

$success = "";
$error = "";

// 1. Fetch current profile data
try {
    $stmt = $pdo->prepare("SELECT p.*, u.email, u.username FROM patients p JOIN users u ON p.user_id = u.id WHERE p.id = ?");
    $stmt->execute([$patient_id]);
    $profile = $stmt->fetch();
} catch (PDOException $e) {
    $error = "Failed to load profile: " . $e->getMessage();
}

// 2. Handle profile update submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $address = trim($_POST['address'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($first_name) || empty($last_name) || empty($phone) || empty($dob) || empty($gender) || empty($email)) {
        $error = "Please fill in all required fields.";
    } elseif (!empty($new_password) && strlen($new_password) < 6) {
        $error = "New password must be at least 6 characters long.";
    } elseif (!empty($new_password) && $new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        try {
            // Check if email belongs to someone else
            $check_email = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $check_email->execute([$email, $user_id]);
            if ($check_email->fetch()) {
                $error = "Email address is already in use by another user.";
            } else {
                $pdo->beginTransaction();

                // A. Update Patients table
                $pat_stmt = $pdo->prepare("UPDATE patients SET first_name = ?, last_name = ?, phone = ?, dob = ?, gender = ?, address = ? WHERE id = ?");
                $pat_stmt->execute([$first_name, $last_name, $phone, $dob, $gender, $address, $patient_id]);

                // B. Update Users table (email)
                $usr_stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
                $usr_stmt->execute([$email, $user_id]);

                // C. Update password if filled
                if (!empty($new_password)) {
                    $hashed_pwd = password_hash($new_password, PASSWORD_DEFAULT);
                    $pwd_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $pwd_stmt->execute([$hashed_pwd, $user_id]);
                }

                $pdo->commit();

                // Update session variable
                $_SESSION['full_name'] = $first_name . ' ' . $last_name;
                $_SESSION['email'] = $email;

                $success = "Profile details updated successfully.";

                // Reload fresh profile data
                $stmt = $pdo->prepare("SELECT p.*, u.email, u.username FROM patients p JOIN users u ON p.user_id = u.id WHERE p.id = ?");
                $stmt->execute([$patient_id]);
                $profile = $stmt->fetch();
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Failed to update profile details: " . $e->getMessage();
        }
    }
}
?>

<div class="db-card" style="max-width: 800px; margin: 0 auto;">
    <div class="db-card-header">
        <h3><i class="fa-solid fa-user-edit text-primary"></i> Edit Profile Information</h3>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        <h4 style="border-bottom: 1.5px solid var(--light-grey); padding-bottom: 5px; margin-bottom: 15px; font-size: 1.05rem; color: var(--primary-color);">Personal details</h4>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="first_name">First Name *</label>
                <input type="text" name="first_name" id="first_name" required class="form-control" value="<?php echo htmlspecialchars($profile['first_name']); ?>">
            </div>
            
            <div class="form-group">
                <label for="last_name">Last Name *</label>
                <input type="text" name="last_name" id="last_name" required class="form-control" value="<?php echo htmlspecialchars($profile['last_name']); ?>">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label>NIC Number (National ID Card)</label>
                <input type="text" disabled class="form-control" style="background-color:#f1f5f9; cursor:not-allowed;" value="<?php echo htmlspecialchars($profile['nic']); ?>">
                <span style="font-size:0.75rem; color:var(--grey-color);">NIC cannot be changed. Contact clinical staff to correct errors.</span>
            </div>
            
            <div class="form-group">
                <label for="phone">Phone Number *</label>
                <input type="text" name="phone" id="phone" required class="form-control" value="<?php echo htmlspecialchars($profile['phone']); ?>">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="dob">Date of Birth *</label>
                <input type="date" name="dob" id="dob" required class="form-control" value="<?php echo htmlspecialchars($profile['dob']); ?>">
            </div>
            
            <div class="form-group">
                <label for="gender">Gender *</label>
                <select name="gender" id="gender" required class="form-control">
                    <option value="Male" <?php echo ($profile['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                    <option value="Female" <?php echo ($profile['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                    <option value="Other" <?php echo ($profile['gender'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="address">Postal Address</label>
            <textarea name="address" id="address" rows="2" class="form-control"><?php echo htmlspecialchars($profile['address']); ?></textarea>
        </div>

        <h4 style="border-bottom: 1.5px solid var(--light-grey); padding-bottom: 5px; margin-top: 25px; margin-bottom: 15px; font-size: 1.05rem; color: var(--primary-color);">Account Settings</h4>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label>Username</label>
                <input type="text" disabled class="form-control" style="background-color:#f1f5f9; cursor:not-allowed;" value="<?php echo htmlspecialchars($profile['username']); ?>">
            </div>
            
            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" name="email" id="email" required class="form-control" value="<?php echo htmlspecialchars($profile['email']); ?>">
            </div>
        </div>

        <h4 style="border-bottom: 1.5px solid var(--light-grey); padding-bottom: 5px; margin-top: 25px; margin-bottom: 15px; font-size: 1.05rem; color: var(--primary-color);">Change Password <span style="font-size:0.8rem; font-weight:normal; color:var(--grey-color);">(Leave empty if unchanged)</span></h4>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" name="new_password" id="new_password" class="form-control" placeholder="Enter new password">
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Verify New Password</label>
                <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Re-type new password">
            </div>
        </div>

        <div style="margin-top: 30px; display:flex; justify-content: flex-end; gap:15px;">
            <a href="index.php" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
    </form>
</div>

<?php require_once dirname(__DIR__) . '/includes/dashboard_footer.php'; ?>
