<?php
// register.php
// Patient registration page

$page_title = "Patient Registration - Prasanna Channel Center";
include_once 'includes/header.php';
require_once 'config/mailer.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    $dashboard = BASE_URL;
    if ($_SESSION['role'] === 'patient') $dashboard .= 'patient/index.php';
    elseif ($_SESSION['role'] === 'doctor') $dashboard .= 'doctor/index.php';
    elseif ($_SESSION['role'] === 'receptionist') $dashboard .= 'receptionist/index.php';
    elseif ($_SESSION['role'] === 'admin') $dashboard .= 'admin/index.php';
    header("Location: " . $dashboard);
    exit();
}

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $nic = trim($_POST['nic'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Basic Validation
    if (empty($first_name) || empty($last_name) || empty($username) || empty($email) || empty($nic) || empty($dob) || empty($gender) || empty($phone) || empty($password)) {
        $error = "Please fill in all required fields.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        try {
            // Check if username, email or NIC already exists
            $check_stmt = $pdo->prepare("SELECT username, email FROM users WHERE username = ? OR email = ?");
            $check_stmt->execute([$username, $email]);
            $existing_user = $check_stmt->fetch();

            $check_nic = $pdo->prepare("SELECT nic FROM patients WHERE nic = ?");
            $check_nic->execute([$nic]);
            $existing_nic = $check_nic->fetch();

            if ($existing_user) {
                if ($existing_user['username'] === $username) {
                    $error = "Username is already taken.";
                } else {
                    $error = "Email address is already registered.";
                }
            } elseif ($existing_nic) {
                $error = "NIC number is already registered.";
            } else {
                // Begin Transaction
                $pdo->beginTransaction();

                // 1. Insert into Users Table
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $user_stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'patient')");
                $user_stmt->execute([$username, $email, $hashed_password]);
                $user_id = $pdo->lastInsertId();

                // 2. Insert into Patients Table
                // Generate Patient Number P-XXXXX (padded user_id)
                $patient_number = 'P-' . str_pad($user_id, 5, '0', STR_PAD_LEFT);
                
                $patient_stmt = $pdo->prepare("INSERT INTO patients (user_id, patient_number, first_name, last_name, nic, dob, gender, phone, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $patient_stmt->execute([$user_id, $patient_number, $first_name, $last_name, $nic, $dob, $gender, $phone, $address]);
                $patient_id = $pdo->lastInsertId();

                // 3. Create welcome notification
                $notif_stmt = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                $notif_stmt->execute([$user_id, "Welcome to Prasanna Channel Center! You can now book doctor appointments and view schedules online."]);

                $pdo->commit();

                // 4. Send welcome email (non-blocking – failure does not stop registration)
                Mailer::sendWelcomeEmail(
                    $email,
                    $first_name . ' ' . $last_name,
                    $patient_number,
                    $username
                );

                // Log the patient in automatically
                $_SESSION['user_id'] = $user_id;
                $_SESSION['patient_id'] = $patient_id;
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;
                $_SESSION['role'] = 'patient';
                $_SESSION['full_name'] = $first_name . ' ' . $last_name;

                // Redirect to Patient Dashboard
                header("Location: " . BASE_URL . "patient/index.php");
                exit();
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Registration failed. Please try again. Details: " . $e->getMessage();
        }
    }
}
?>

<div class="auth-wrapper">
    <div class="auth-container" style="max-width: 650px;">
        <div class="text-center mb-4">
            <h2 style="font-family: 'Outfit', sans-serif; color: var(--primary-color);">Patient Registration</h2>
            <p style="color: var(--grey-color);">Create your account to start booking doctor appointments online.</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <h4 style="border-bottom: 1.5px solid var(--light-grey); padding-bottom: 5px; margin-bottom: 15px; font-size: 1.05rem; color: var(--primary-color);">Personal Information</h4>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label for="first_name">First Name *</label>
                    <input type="text" name="first_name" id="first_name" required class="form-control" placeholder="e.g. Chaminda" value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="last_name">Last Name *</label>
                    <input type="text" name="last_name" id="last_name" required class="form-control" placeholder="e.g. Bandara" value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label for="nic">NIC Number (National Identity Card) *</label>
                    <input type="text" name="nic" id="nic" required class="form-control" placeholder="e.g. 199012345678" value="<?php echo isset($_POST['nic']) ? htmlspecialchars($_POST['nic']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number *</label>
                    <input type="text" name="phone" id="phone" required class="form-control" placeholder="e.g. 0771234567" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label for="dob">Date of Birth *</label>
                    <input type="date" name="dob" id="dob" required class="form-control" value="<?php echo isset($_POST['dob']) ? htmlspecialchars($_POST['dob']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="gender">Gender *</label>
                    <select name="gender" id="gender" required class="form-control">
                        <option value="">Select Gender</option>
                        <option value="Male" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                        <option value="Other" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="address">Postal Address</label>
                <textarea name="address" id="address" rows="2" class="form-control" placeholder="No. 45, Temple Road, Mahiyanganaya"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
            </div>

            <h4 style="border-bottom: 1.5px solid var(--light-grey); padding-bottom: 5px; margin-top: 25px; margin-bottom: 15px; font-size: 1.05rem; color: var(--primary-color);">Account Credentials</h4>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" name="username" id="username" required class="form-control" placeholder="e.g. chaminda90" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" name="email" id="email" required class="form-control" placeholder="e.g. chaminda@gmail.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label for="password">Password * (Min 6 chars)</label>
                    <input type="password" name="password" id="password" required class="form-control" placeholder="Create Password">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <input type="password" name="confirm_password" id="confirm_password" required class="form-control" placeholder="Verify Password">
                </div>
            </div>

            <div style="margin-top: 30px;">
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px;">Register Account</button>
            </div>
        </form>
        
        <div class="text-center style-row" style="margin-top: 20px; font-size: 0.9rem; color: var(--grey-color);">
            Already have an account? <a href="login.php" style="color: var(--primary-color); font-weight: 600;">Login here</a>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
