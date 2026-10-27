<?php
// login.php
// Unified login page for Patients, Doctors, Receptionists, and Admins

$page_title = "Login - Prasanna Channel Center";
include_once 'includes/header.php';

// Redirect if already logged in — allow switching via ?switch=1
if (isset($_SESSION['user_id']) && !isset($_GET['switch'])) {
    $dashboard = BASE_URL;
    if ($_SESSION['role'] === 'patient') $dashboard .= 'patient/index.php';
    elseif ($_SESSION['role'] === 'doctor') $dashboard .= 'doctor/index.php';
    elseif ($_SESSION['role'] === 'receptionist') $dashboard .= 'receptionist/index.php';
    elseif ($_SESSION['role'] === 'admin') $dashboard .= 'admin/index.php';
    header("Location: " . $dashboard);
    exit();
}

// If switching roles, destroy the old session cleanly first
if (isset($_GET['switch']) || isset($_POST['role'])) {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    session_start();
}

$error = "";
$selected_role = $_GET['role'] ?? 'patient';
if (!in_array($selected_role, ['patient', 'doctor', 'receptionist', 'admin'])) {
    $selected_role = 'patient';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_input = trim($_POST['username_email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'patient';

    if (empty($login_input) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        try {
            // Find user
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$login_input, $login_input]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                if ($user['role'] === $role) {
                    // Regenerate session ID to prevent session fixation
                    session_regenerate_id(true);

                    // Start user session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];

                    // Fetch role-specific details
                    if ($role === 'patient') {
                        $profile_stmt = $pdo->prepare("SELECT id, first_name, last_name FROM patients WHERE user_id = ?");
                        $profile_stmt->execute([$user['id']]);
                        $profile = $profile_stmt->fetch();
                        if ($profile) {
                            $_SESSION['patient_id'] = $profile['id'];
                            $_SESSION['full_name'] = $profile['first_name'] . ' ' . $profile['last_name'];
                        }
                    } elseif ($role === 'doctor') {
                        $profile_stmt = $pdo->prepare("SELECT id, first_name, last_name FROM doctors WHERE user_id = ?");
                        $profile_stmt->execute([$user['id']]);
                        $profile = $profile_stmt->fetch();
                        if ($profile) {
                            $_SESSION['doctor_id'] = $profile['id'];
                            $_SESSION['full_name'] = 'Dr. ' . $profile['first_name'] . ' ' . $profile['last_name'];
                        }
                    } elseif ($role === 'receptionist') {
                        $profile_stmt = $pdo->prepare("SELECT id, first_name, last_name FROM receptionists WHERE user_id = ?");
                        $profile_stmt->execute([$user['id']]);
                        $profile = $profile_stmt->fetch();
                        if ($profile) {
                            $_SESSION['receptionist_id'] = $profile['id'];
                            $_SESSION['full_name'] = $profile['first_name'] . ' ' . $profile['last_name'];
                        }
                    } elseif ($role === 'admin') {
                        $_SESSION['full_name'] = 'System Administrator';
                    }

                    // Redirect logic
                    if (isset($_GET['redirect']) && $_GET['redirect'] === 'book' && $role === 'patient') {
                        $doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : '';
                        header("Location: " . BASE_URL . "patient/book.php" . ($doctor_id ? "?doctor_id=" . $doctor_id : ""));
                        exit();
                    }

                    // Standard Dashboard Redirect
                    $redirect_url = BASE_URL;
                    if ($role === 'patient') $redirect_url .= 'patient/index.php';
                    elseif ($role === 'doctor') $redirect_url .= 'doctor/index.php';
                    elseif ($role === 'receptionist') $redirect_url .= 'receptionist/index.php';
                    elseif ($role === 'admin') $redirect_url .= 'admin/index.php';

                    header("Location: " . $redirect_url);
                    exit();
                } else {
                    $error = "Incorrect role selected for this account.";
                }
            } else {
                $error = "Invalid username/email or password.";
            }
        } catch (PDOException $e) {
            $error = "Login failed: " . $e->getMessage();
        }
    }
}
?>

<div class="auth-wrapper">
    <div class="auth-container">
        <div class="text-center mb-4">
            <h2 style="font-family: 'Outfit', sans-serif; color: var(--primary-color);">Secure Portal Login</h2>
            <p style="color: var(--grey-color);">Please select your role and enter credentials to access your dashboard.</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Role Tab Selection -->
        <div class="auth-tabs">
            <div class="auth-tab <?php echo $selected_role === 'patient' ? 'active' : ''; ?>" onclick="selectRole('patient')">Patient</div>
            <div class="auth-tab <?php echo $selected_role === 'doctor' ? 'active' : ''; ?>" onclick="selectRole('doctor')">Doctor</div>
            <div class="auth-tab <?php echo $selected_role === 'receptionist' ? 'active' : ''; ?>" onclick="selectRole('receptionist')">Staff</div>
            <div class="auth-tab <?php echo $selected_role === 'admin' ? 'active' : ''; ?>" onclick="selectRole('admin')">Admin</div>
        </div>

        <form action="" method="POST" id="loginForm">
            <!-- Hidden Role Input -->
            <input type="hidden" name="role" id="roleInput" value="<?php echo $selected_role; ?>">

            <div class="form-group">
                <label for="username_email">Username or Email Address</label>
                <input type="text" name="username_email" id="username_email" required class="form-control" placeholder="Enter username or email" value="<?php echo isset($_POST['username_email']) ? htmlspecialchars($_POST['username_email']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required class="form-control" placeholder="Enter password">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; margin-top: 10px;">Log In</button>
        </form>

        <div id="registerPrompt" class="text-center" style="margin-top: 20px; font-size: 0.9rem; color: var(--grey-color); display: <?php echo $selected_role === 'patient' ? 'block' : 'none'; ?>;">
            Don't have a patient account? <a href="register.php" style="color: var(--primary-color); font-weight: 600;">Register here</a>
        </div>
    </div>
</div>

<script>
function selectRole(role) {
    // Update active tab styling
    const tabs = document.querySelectorAll('.auth-tab');
    tabs.forEach(tab => {
        if (tab.textContent.toLowerCase() === role || (tab.textContent.toLowerCase() === 'staff' && role === 'receptionist')) {
            tab.classList.add('active');
        } else {
            tab.classList.remove('active');
        }
    });

    // Update hidden field value
    document.getElementById('roleInput').value = role;

    // Toggle registration prompt
    const regPrompt = document.getElementById('registerPrompt');
    if (role === 'patient') {
        regPrompt.style.display = 'block';
    } else {
        regPrompt.style.display = 'none';
    }
}
</script>

<?php include_once 'includes/footer.php'; ?>
