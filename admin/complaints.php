<?php
$page_title = 'Manage Complaints';
require_once 'includes/header.php';

// Handle complaint status update (same logic as before)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $complaint_id = (int)$_POST['complaint_id'];
    $action = $_POST['action'];
    
    switch($action) {
        case 'update_status':
            $new_status = $_POST['status'];
            $assigned_to = !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;
            $admin_remarks = sanitizeInput($_POST['admin_remarks']);
            
            $stmt = $pdo->prepare("
                UPDATE complaints 
                SET status = ?, assigned_to = ?, admin_remarks = ? 
                WHERE id = ?
            ");
            $stmt->execute([$new_status, $assigned_to, $admin_remarks, $complaint_id]);
            
            // Send notification to student
            $stmt = $pdo->prepare("SELECT student_id FROM complaints WHERE id = ?");
            $stmt->execute([$complaint_id]);
            $complaint = $stmt->fetch();
            
            if ($complaint) {
                sendNotification($complaint['student_id'], "Your complaint #$complaint_id status has been updated to: " . ucfirst(str_replace('_', ' ', $new_status)));
            }
            
            $_SESSION['success'] = 'Complaint updated successfully!';
            break;
    }
    
    redirect('complaints.php');
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

// Build query with filters
$where_conditions = ["1=1"];
$params = [];

if ($status_filter) {
    $where_conditions[] = "c.status = ?";
    $params[] = $status_filter;
}

if ($category_filter) {
    $where_conditions[] = "c.category = ?";
    $params[] = $category_filter;
}

if ($search) {
    $where_conditions[] = "(s.username LIKE ? OR c.description LIKE ? OR c.id = ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = $search;
}

$where_clause = implode(' AND ', $where_conditions);

// Get complaints with filters
$stmt = $pdo->prepare("
    SELECT c.*, s.username as student_name, s.email as student_email, u.username as assigned_to_name 
    FROM complaints c 
    JOIN users s ON c.student_id = s.id 
    LEFT JOIN users u ON c.assigned_to = u.id 
    WHERE $where_clause
    ORDER BY 
        CASE WHEN c.status = 'pending' THEN 1 
             WHEN c.status = 'in_progress' THEN 2 
             WHEN c.status = 'resolved' THEN 3 
             ELSE 4 END,
        c.created_at DESC
");
$stmt->execute($params);
$complaints = $stmt->fetchAll();

$categories = getComplaintCategories();
$success = isset($_SESSION['success']) ? $_SESSION['success'] : '';
if ($success) unset($_SESSION['success']);

require_once 'includes/sidebar.php';
require_once 'includes/navbar.php';
?>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4">
        <div class="d-flex align-items-center">
            <i class="fas fa-check-circle fs-4 me-3"></i>
            <div><?php echo $success; ?></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted">Status</label>
                <select class="form-select border-light bg-light" name="status">
                    <option value="">All Statuses</option>
                    <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="in_progress" <?php echo $status_filter == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                    <option value="resolved" <?php echo $status_filter == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                    <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted">Category</label>
                <select class="form-select border-light bg-light" name="category">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $key => $value): ?>
                        <option value="<?php echo $key; ?>" <?php echo $category_filter == $key ? 'selected' : ''; ?>>
                            <?php echo $value; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold text-muted">Search</label>
                <div class="input-group">
                    <span class="input-group-text border-light bg-light"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" class="form-control border-light bg-light" name="search" 
                           value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Name, ID, or keyword...">
                </div>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100 fw-bold">Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- Complaints List -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold text-dark">Complaints Repository</h5>
        <span class="badge bg-primary rounded-pill px-3"><?php echo count($complaints); ?> Records</span>
    </div>
    <div class="card-body p-0">
        <?php if (count($complaints) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Student Details</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Handler</th>
                            <th>Submitted</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($complaints as $complaint): ?>
                            <tr onclick="manageComplaint(<?php echo $complaint['id']; ?>)" style="cursor: pointer;">
                                <td class="ps-4 fw-bold text-primary">#<?php echo $complaint['id']; ?></td>
                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($complaint['student_name']); ?></div>
                                    <div class="text-muted small"><?php echo htmlspecialchars($complaint['student_email']); ?></div>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?php echo ucfirst($complaint['category']); ?></span></td>
                                <td><span class="<?php echo getStatusBadge($complaint['status']); ?>"><?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?></span></td>
                                <td><?php echo $complaint['assigned_to_name'] ?: '<span class="text-muted italic small">Unassigned</span>'; ?></td>
                                <td><span class="text-muted small"><?php echo formatDate($complaint['created_at']); ?></span></td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-primary px-3 fw-bold" onclick="event.stopPropagation(); manageComplaint(<?php echo $complaint['id']; ?>)">
                                        Manage
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-search fa-3x text-muted opacity-25 mb-3"></i>
                <h6 class="text-muted">Cabasho Arday: No complaints match your search filters</h6>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Manage Complaint Modal -->
<div class="modal fade" id="manageComplaintModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Complaint Management</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="complaintManageContent">
                <!-- Data loaded via JS -->
            </div>
        </div>
    </div>
</div>

<?php 
$extra_js = "
<script>
    function manageComplaint(id) {
        const modalBody = document.getElementById('complaintManageContent');
        modalBody.innerHTML = '<div class=\"text-center p-5\"><div class=\"spinner-border text-primary\" role=\"status\"><span class=\"visually-hidden\">Loading...</span></div><p class=\"mt-2 text-muted\">Loading Cabasho Arday details...</p></div>';
        
        const modalElement = document.getElementById('manageComplaintModal');
        const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
        modal.show();

        fetch('complaint_manage.php?id=' + id)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.text();
            })
            .then(data => {
                modalBody.innerHTML = data;
            })
            .catch(error => {
                console.error('Error:', error);
                modalBody.innerHTML = '<div class=\"alert alert-danger mx-3 my-3\">Cabasho Arday: Failed to load complaint details. Please check your connection and try again.</div>';
            });
    }
</script>";
require_once 'includes/footer.php';
?>