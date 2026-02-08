<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !hasRole('admin')) {
    echo '<div class="alert alert-danger px-4 py-3 border-0 rounded-4">Access denied.</div>';
    exit;
}

$complaint_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($complaint_id <= 0) {
    echo '<div class="alert alert-danger px-4 py-3 border-0 rounded-4">Invalid complaint ID.</div>';
    exit;
}

// Get complaint details
$stmt = $pdo->prepare("
    SELECT c.*, s.username as student_name, s.email as student_email, u.username as assigned_to_name
    FROM complaints c 
    JOIN users s ON c.student_id = s.id 
    LEFT JOIN users u ON c.assigned_to = u.id 
    WHERE c.id = ?
");
$stmt->execute([$complaint_id]);
$complaint = $stmt->fetch();

if (!$complaint) {
    echo '<div class="alert alert-danger px-4 py-3 border-0 rounded-4">Complaint not found.</div>';
    exit;
}

// Get staff members for assignment
$stmt = $pdo->prepare("SELECT id, username FROM users WHERE role = 'staff' ORDER BY username");
$stmt->execute();
$staff_members = $stmt->fetchAll();
$categories = getComplaintCategories();
?>

<div class="complaint-manage-wrapper">
    <!-- Profile Section -->
    <div class="d-flex align-items-center mb-4 p-3 bg-light rounded-4 border">
        <div class="rounded-circle d-flex align-items-center justify-content-center text-white me-3" 
             style="width: 56px; height: 56px; background: var(--primary-gradient); font-size: 1.2rem; font-weight: bold;">
            <?php echo strtoupper(substr($complaint['student_name'], 0, 1)); ?>
        </div>
        <div>
            <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($complaint['student_name']); ?></h6>
            <p class="text-muted small mb-0"><?php echo htmlspecialchars($complaint['student_email']); ?></p>
        </div>
        <div class="ms-auto text-end">
            <span class="badge bg-white text-dark border px-3 py-2 rounded-3 mb-1">
                <?php echo isset($categories[$complaint['category']]) ? $categories[$complaint['category']] : ucfirst($complaint['category']); ?>
            </span>
            <div class="mb-1">
                <span class="badge <?php echo $complaint['send_to'] == 'admin' ? 'bg-info' : 'bg-secondary'; ?> px-3 py-1 rounded-pill small">
                    For: <?php echo ucfirst($complaint['send_to']); ?>
                </span>
            </div>
            <div class="small text-muted"><i class="far fa-clock me-1"></i> <?php echo formatDate($complaint['created_at']); ?></div>
        </div>
    </div>

    <!-- Description -->
    <div class="mb-4">
        <label class="form-label small fw-bold text-muted uppercase">Issue Description</label>
        <div class="p-4 bg-white border rounded-4 shadow-sm" style="font-size: 1.05rem; line-height: 1.6;">
            <?php echo nl2br(htmlspecialchars($complaint['description'])); ?>
        </div>
    </div>

    <!-- Management Action Form -->
    <div class="card border-0 bg-light rounded-4 mb-4">
        <div class="card-body p-4">
            <h6 class="fw-bold mb-4">Resolution Action Center</h6>
            <form method="POST" action="complaints.php">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="complaint_id" value="<?php echo $complaint['id']; ?>">
                
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Update Lifecycle Status</label>
                        <select class="form-select border-0 shadow-sm py-2" name="status" required>
                            <option value="pending" <?php echo $complaint['status'] == 'pending' ? 'selected' : ''; ?>>Pending Approval</option>
                            <option value="in_progress" <?php echo $complaint['status'] == 'in_progress' ? 'selected' : ''; ?>>In Deployment / Process</option>
                            <option value="resolved" <?php echo $complaint['status'] == 'resolved' ? 'selected' : ''; ?>>Resolved Successfully</option>
                            <option value="rejected" <?php echo $complaint['status'] == 'rejected' ? 'selected' : ''; ?>>Closed / Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Delegate to Official</label>
                        <select class="form-select border-0 shadow-sm py-2" name="assigned_to">
                            <option value="">-- No Assignment --</option>
                            <?php foreach ($staff_members as $staff): ?>
                                <option value="<?php echo $staff['id']; ?>" <?php echo $complaint['assigned_to'] == $staff['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($staff['username']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold">Official Remarks (Visible to Student)</label>
                    <textarea class="form-control border-0 shadow-sm py-3" name="admin_remarks" rows="3" 
                            placeholder="Provide details on the resolution progress..."><?php echo htmlspecialchars($complaint['admin_remarks']); ?></textarea>
                </div>

                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light border flex-grow-1 fw-bold py-3" data-bs-dismiss="modal">Close Window</button>
                    <button type="submit" class="btn btn-primary flex-grow-1 fw-bold py-3 shadow-sm">Commit Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Activity Log -->
    <div class="activity-timeline ps-3 border-start">
        <div class="timeline-point mb-4 ps-4 position-relative">
            <div class="marker position-absolute rounded-circle bg-primary" style="width: 12px; height: 12px; left: -6px; top: 6px;"></div>
            <div class="small fw-bold">Complaint Registered</div>
            <div class="extra-small text-muted mb-2"><?php echo formatDateTime($complaint['created_at']); ?></div>
            <div class="p-3 bg-white border rounded-3 small text-muted">Initial submission by student.</div>
        </div>

        <?php if ($complaint['status'] != 'pending'): ?>
            <div class="timeline-point mb-0 ps-4 position-relative">
                <div class="marker position-absolute rounded-circle <?php echo $complaint['status'] == 'resolved' ? 'bg-success' : 'bg-warning'; ?>" style="width: 12px; height: 12px; left: -6px; top: 6px;"></div>
                <div class="small fw-bold">Latest System Update</div>
                <div class="extra-small text-muted mb-2"><?php echo formatDateTime($complaint['updated_at']); ?></div>
                <div class="p-3 bg-white border rounded-3 small text-muted">
                    Moved to <strong class="text-dark"><?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?></strong>.
                    <?php if ($complaint['assigned_to_name']): ?>
                        Currently handled by: <strong class="text-dark"><?php echo $complaint['assigned_to_name']; ?></strong>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>