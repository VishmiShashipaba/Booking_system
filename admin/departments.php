<?php
// admin/departments.php
// Manage clinical departments

$page_title = "Manage Departments";
require_once dirname(__DIR__) . '/includes/dashboard_header.php';

checkAccess(['admin']);

$success = "";
$error = "";

// 1. Process Add Department
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_dept'])) {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($name)) {
        $error = "Department name is required.";
    } else {
        try {
            $chk = $pdo->prepare("SELECT id FROM departments WHERE name = ?");
            $chk->execute([$name]);
            if ($chk->fetch()) {
                $error = "A department with this name already exists.";
            } else {
                $ins = $pdo->prepare("INSERT INTO departments (name, description) VALUES (?, ?)");
                $ins->execute([$name, $description]);
                $success = "Department '$name' added successfully.";
            }
        } catch (PDOException $e) {
            $error = "Failed to add department: " . $e->getMessage();
        }
    }
}

// 2. Process Edit Department
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_dept'])) {
    $dept_id = intval($_POST['dept_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($dept_id <= 0 || empty($name)) {
        $error = "Please fill in all required fields.";
    } else {
        try {
            $chk = $pdo->prepare("SELECT id FROM departments WHERE name = ? AND id != ?");
            $chk->execute([$name, $dept_id]);
            if ($chk->fetch()) {
                $error = "Another department already has this name.";
            } else {
                $up = $pdo->prepare("UPDATE departments SET name = ?, description = ? WHERE id = ?");
                $up->execute([$name, $description, $dept_id]);
                $success = "Department '$name' updated successfully.";
            }
        } catch (PDOException $e) {
            $error = "Failed to update department: " . $e->getMessage();
        }
    }
}

// 3. Fetch all departments
try {
    $departments = $pdo->query("SELECT * FROM departments ORDER BY name ASC")->fetchAll();
} catch (PDOException $e) {
    $departments = [];
}
?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div style="display:grid; grid-template-columns: 1fr 1.5fr; gap:30px; align-items: start;">
    <!-- Add/Edit Department -->
    <div class="db-card" id="formCard">
        <div class="db-card-header">
            <h3 id="formTitle"><i class="fa-solid fa-sitemap text-primary"></i> Add Clinical Department</h3>
        </div>

        <form action="" method="POST" id="deptForm">
            <input type="hidden" name="dept_id" id="edit_dept_id">
            <input type="hidden" name="add_dept" id="form_action_flag" value="1">

            <div class="form-group">
                <label for="name">Department Name *</label>
                <input type="text" name="name" id="name" required class="form-control" placeholder="e.g. Cardiology">
            </div>

            <div class="form-group">
                <label for="description">Department Description</label>
                <textarea name="description" id="description" rows="5" class="form-control" placeholder="Describe the clinical specialties..."></textarea>
            </div>

            <div style="margin-top: 25px; display:flex; gap:10px;">
                <button type="button" class="btn btn-secondary" onclick="resetDeptForm()" style="flex:1;">Cancel / Reset</button>
                <button type="submit" class="btn btn-primary" id="submitBtn" style="flex:1.5;">Save Department</button>
            </div>
        </form>
    </div>

    <!-- Departments List -->
    <div class="db-card">
        <div class="db-card-header">
            <h3><i class="fa-solid fa-list text-primary"></i> Clinic Departments Directory</h3>
        </div>

        <div class="table-responsive">
            <table class="db-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Department Name</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($departments)): ?>
                        <?php foreach ($departments as $d): ?>
                            <tr>
                                <td><strong>#<?php echo $d['id']; ?></strong></td>
                                <td><strong><?php echo htmlspecialchars($d['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($d['description']); ?></td>
                                <td>
                                    <button class="btn btn-secondary" style="padding: 6px 12px; font-size:0.75rem; border-radius:4px;" 
                                            onclick="editDept(<?php echo htmlspecialchars(json_encode($d)); ?>)">
                                        <i class="fa-solid fa-edit"></i> Edit
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align:center; padding: 20px;">No clinical departments listed.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function editDept(d) {
    document.getElementById('formTitle').innerHTML = '<i class="fa-solid fa-edit text-primary"></i> Edit Department';
    document.getElementById('edit_dept_id').value = d.id;
    document.getElementById('form_action_flag').name = 'edit_dept';
    document.getElementById('form_action_flag').value = '1';
    
    document.getElementById('name').value = d.name;
    document.getElementById('description').value = d.description;

    document.getElementById('submitBtn').textContent = 'Save Changes';
    
    // Scroll to form card
    document.getElementById('formCard').scrollIntoView({ behavior: 'smooth' });
}

function resetDeptForm() {
    document.getElementById('formTitle').innerHTML = '<i class="fa-solid fa-sitemap text-primary"></i> Add Clinical Department';
    document.getElementById('edit_dept_id').value = '';
    document.getElementById('form_action_flag').name = 'add_dept';
    document.getElementById('form_action_flag').value = '1';
    
    document.getElementById('deptForm').reset();
    document.getElementById('submitBtn').textContent = 'Save Department';
}
</script>

<?php require_once dirname(__DIR__) . '/includes/dashboard_footer.php'; ?>
