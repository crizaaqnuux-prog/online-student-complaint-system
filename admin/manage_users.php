<?php
$page_title = 'Manage Users';
require_once 'includes/header.php';

// Handle user actions (same logic as before)
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action == 'add_user') {
        $username = sanitizeInput($_POST['username']);
        $email = sanitizeInput($_POST['email']);
        $password = $_POST['password'];
        $role = $_POST['role'];
        
        if (empty($username) || empty($email) || empty($password) || empty($role)) {
            $error = 'Please fill in all fields.';
        } else {
            try {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                
                if ($stmt->fetch()) {
                    $error = 'Email already exists.';
                } else {
                    $hashedPassword = hashPassword($password);
                    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$username, $email, $hashedPassword, $role]);
                    $success = 'User added successfully!';
                }
            } catch(PDOException $e) {
                $error = 'Error adding user.';
            }
        }
    } elseif ($action == 'update_role') {
        $user_id = (int)$_POST['user_id'];
        $new_role = $_POST['role'];
        
        try {
            $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->execute([$new_role, $user_id]);
            $success = 'User role updated successfully!';
        } catch(PDOException $e) {
            $error = 'Error updating user role.';
        }
    } elseif ($action == 'delete_user') {
        $user_id = (int)$_POST['user_id'];
        
        try {
            if ($user_id == $_SESSION['user_id']) {
                $error = 'You cannot delete your own account.';
            } else {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $success = 'User deleted successfully!';
            }
        } catch(PDOException $e) {
            $error = 'Error deleting user. User may have associated complaints.';
        }
    }
}

// Get all users with stats
$stmt = $pdo->prepare("
    SELECT u.*, 
           COUNT(c.id) as complaint_count,
           COUNT(CASE WHEN c.status = 'resolved' THEN 1 END) as resolved_count
    FROM users u 
    LEFT JOIN complaints c ON u.id = c.student_id 
    GROUP BY u.id 
    ORDER BY u.created_at DESC
");
$stmt->execute();
$users = $stmt->fetchAll();

// Get summary stats
$stmt = $pdo->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
$user_counts = [];
while ($row = $stmt->fetch()) {
    $user_counts[$row['role']] = $row['count'];
}

require_once 'includes/sidebar.php';
require_once 'includes/navbar.php';
?>

<?php if ($success): ?>
    <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show mb-4">
        <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show mb-4">
        <i class="fas fa-exclamation-triangle me-2"></i> <?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Statistics -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stats-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="p-3 rounded-4 bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-users fs-4"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0"><?php echo array_sum($user_counts); ?></h3>
                <p class="text-muted small uppercase fw-semibold">Total Users</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stats-card h-100" style="--primary-gradient: var(--secondary-gradient);">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="p-3 rounded-4 bg-info bg-opacity-10 text-info">
                        <i class="fas fa-user-graduate fs-4"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0" style="background: var(--secondary-gradient); -webkit-background-clip: text; background-clip: text;"><?php echo $user_counts['student'] ?? 0; ?></h3>
                <p class="text-muted small uppercase fw-semibold">Students</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stats-card h-100" style="--primary-gradient: var(--accent-gradient);">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="p-3 rounded-4 bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-user-tie fs-4"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0" style="background: var(--accent-gradient); -webkit-background-clip: text; background-clip: text;"><?php echo $user_counts['staff'] ?? 0; ?></h3>
                <p class="text-muted small uppercase fw-semibold">Staff Members</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card h-100 border-0 shadow-sm overflow-hidden" style="background: var(--primary-gradient);">
            <div class="card-body d-flex flex-column justify-content-center align-items-center text-white p-4">
                <button class="btn btn-light text-primary fw-bold w-100 py-2 rounded-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="fas fa-user-plus me-2"></i> Add New User
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Users List -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold">User Directory</h5>
        <div class="input-group w-auto">
            <input type="text" id="userFilter" class="form-control form-control-sm border-light bg-light" placeholder="Quick search...">
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="userTable">
                <thead>
                    <tr>
                        <th class="ps-4">Profile</th>
                        <th>Status</th>
                        <th>User Activity</th>
                        <th>Joined</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-4 d-flex align-items-center justify-content-center text-white me-3" 
                                         style="width: 48px; height: 48px; background: <?php echo $user['role'] == 'admin' ? 'var(--accent-gradient)' : ($user['role'] == 'staff' ? 'var(--secondary-gradient)' : 'var(--primary-gradient)'); ?>">
                                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold fs-6">
                                            <?php echo htmlspecialchars($user['username']); ?>
                                            <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                                <small class="badge bg-primary-subtle text-primary border-primary border-opacity-25 ms-1">You</small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-muted small"><?php echo htmlspecialchars($user['email']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge border fw-bold <?php 
                                    echo $user['role'] == 'admin' ? 'bg-danger-subtle text-danger border-danger' : 
                                        ($user['role'] == 'staff' ? 'bg-info-subtle text-info border-info' : 'bg-success-subtle text-success border-success'); 
                                ?> py-2 px-3">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($user['role'] == 'student'): ?>
                                    <div class="small">
                                        <span class="text-muted">Complaints:</span> <strong class="text-dark"><?php echo $user['complaint_count']; ?></strong>
                                        <span class="mx-2 text-divider">|</span>
                                        <span class="text-muted">Resolved:</span> <strong class="text-success"><?php echo $user['resolved_count']; ?></strong>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted italic small">Staff Record</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="text-muted small"><?php echo formatDate($user['created_at']); ?></span></td>
                            <td class="text-end pe-4">
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <button class="btn btn-icon btn-light border text-primary me-2" onclick="editUser(<?php echo $user['id']; ?>, '<?php echo $user['role']; ?>')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-icon btn-light border text-danger" onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                <?php else: ?>
                                    <span class="text-muted small italic">System Account</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modals -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-bold">Create New Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="action" value="add_user">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Full Display Name</label>
                        <input type="text" class="form-control bg-light border-0 py-2" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Corporate Email Address</label>
                        <input type="email" class="form-control bg-light border-0 py-2" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Secure Password</label>
                        <input type="password" class="form-control bg-light border-0 py-2" name="password" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-bold text-muted">System Authority Role</label>
                        <select class="form-select bg-light border-0 py-2" name="role" required>
                            <option value="student">Student User</option>
                            <option value="staff">Resolving Staff</option>
                            <option value="admin">System Administrator</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="submit" class="btn btn-primary w-100 fw-bold py-3 shadow-sm">Provision Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <form method="POST">
                <div class="modal-body p-4 pt-5 text-center">
                    <input type="hidden" name="action" value="update_role">
                    <input type="hidden" name="user_id" id="edit_user_id">
                    <div class="mb-4">
                        <div class="d-inline-block p-3 rounded-circle bg-primary bg-opacity-10 text-primary mb-3">
                            <i class="fas fa-user-shield fa-2x"></i>
                        </div>
                        <h5 class="fw-bold mb-1">Update Privileges</h5>
                        <p class="text-muted small">Change user access level within the system</p>
                    </div>
                    <select class="form-select border-light py-2 mb-4" id="edit_role" name="role">
                        <option value="student">Student</option>
                        <option value="staff">Staff Member</option>
                        <option value="admin">Administrator</option>
                    </select>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light w-100 fw-bold" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary w-100 fw-bold">Apply Changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-body p-5 text-center">
                <div class="text-danger mb-4">
                    <i class="fas fa-trash-alt fa-3x"></i>
                </div>
                <h5 class="fw-bold mb-2">Confirm Removal</h5>
                <p class="text-muted smaller mb-4">Are you absolutely sure you want to delete <strong id="delete_username" class="text-dark"></strong>? This action is permanent and will remove all associated records.</p>
                <form method="POST">
                    <input type="hidden" name="action" value="delete_user">
                    <input type="hidden" name="user_id" id="delete_user_id">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light flex-grow-1 fw-bold" data-bs-dismiss="modal">Abort</button>
                        <button type="submit" class="btn btn-danger flex-grow-1 fw-bold">Confirm Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php 
$extra_js = "
<script>
    function editUser(id, currentRole) {
        document.getElementById('edit_user_id').value = id;
        document.getElementById('edit_role').value = currentRole;
        new bootstrap.Modal(document.getElementById('editUserModal')).show();
    }
    function deleteUser(id, name) {
        document.getElementById('delete_user_id').value = id;
        document.getElementById('delete_username').textContent = name;
        new bootstrap.Modal(document.getElementById('deleteUserModal')).show();
    }
    document.getElementById('userFilter').addEventListener('keyup', function() {
        let val = this.value.toLowerCase();
        let rows = document.querySelectorAll('#userTable tbody tr');
        rows.forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(val) ? '' : 'none';
        });
    });
</script>";
require_once 'includes/footer.php';
?>