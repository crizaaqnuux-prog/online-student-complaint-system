<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is a staff member
if (!isLoggedIn() || !hasRole('staff')) {
    echo '<div class="alert alert-danger">Access denied.</div>';
    exit;
}

$complaint_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($complaint_id <= 0) {
    echo '<div class="alert alert-danger">Invalid complaint ID.</div>';
    exit;
}

// Get complaint details - ensure it's assigned to this staff member
$stmt = $pdo->prepare("
    SELECT c.*, s.username as student_name, s.email as student_email
    FROM complaints c 
    JOIN users s ON c.student_id = s.id 
    WHERE c.id = ? AND (c.assigned_to = ? OR (c.send_to = 'staff' AND c.assigned_to IS NULL))
");
$stmt->execute([$complaint_id, $_SESSION['user_id']]);
$complaint = $stmt->fetch();

if (!$complaint) {
    echo '<div class="alert alert-danger">Complaint not found or not accessible to you.</div>';
    exit;
}

$categories = getComplaintCategories();
?>

<div class="complaint-details animate__animated animate__fadeIn">
    <!-- Header Info -->
    <div class="d-flex justify-content-between align-items-start mb-4 bg-light p-3 rounded-4 border">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary">
                <i data-lucide="hash"></i>
            </div>
            <div>
                <h5 class="fw-bold mb-0"><?php echo $complaint['id']; ?></h5>
                <span class="text-muted small">Tracking Number</span>
            </div>
        </div>
        <div class="text-end">
            <span class="badge-status <?php echo getStatusBadge($complaint['status']); ?> d-block mb-1">
                <?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?>
            </span>
            <small class="text-muted"><i data-lucide="calendar" class="me-1" style="width: 14px;"></i> <?php echo formatDate($complaint['created_at']); ?></small>
        </div>
    </div>

    <!-- Student & Category -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="p-3 border rounded-4">
                <h6 class="text-muted small fw-bold text-uppercase mb-3"><i data-lucide="user" class="me-2 text-primary" style="width: 16px;"></i> Student Information</h6>
                <p class="mb-0 fw-bold"><?php echo htmlspecialchars($complaint['student_name']); ?></p>
                <p class="text-muted small mb-0"><?php echo htmlspecialchars($complaint['student_email']); ?></p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="p-3 border rounded-4">
                <h6 class="text-muted small fw-bold text-uppercase mb-3"><i data-lucide="tag" class="me-2 text-primary" style="width: 16px;"></i> Category</h6>
                <span class="badge bg-purple bg-opacity-10 text-purple px-3 py-2 rounded-3" style="color: #6C63FF; font-weight: 600;">
                    <?php echo isset($categories[$complaint['category']]) ? $categories[$complaint['category']] : ucfirst($complaint['category']); ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Description -->
    <div class="mb-4">
        <h6 class="text-muted small fw-bold text-uppercase mb-3"><i data-lucide="align-left" class="me-2 text-primary" style="width: 16px;"></i> Complaint Description</h6>
        <div class="p-4 bg-white border rounded-4 shadow-sm" style="line-height: 1.6;">
            <?php echo nl2br(htmlspecialchars($complaint['description'])); ?>
        </div>
    </div>

    <!-- Staff Response if exists -->
    <?php if ($complaint['admin_remarks']): ?>
        <div class="mb-4">
            <h6 class="text-muted small fw-bold text-uppercase mb-3"><i data-lucide="message-square" class="me-2 text-success" style="width: 16px;"></i> Your Response</h6>
            <div class="p-4 bg-success bg-opacity-10 border-start border-success border-4 rounded-4 shadow-sm">
                <?php echo nl2br(htmlspecialchars($complaint['admin_remarks'])); ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Timeline -->
    <div class="mt-5">
        <h6 class="text-muted small fw-bold text-uppercase mb-4"><i data-lucide="clock" class="me-2 text-primary" style="width: 16px;"></i> Process Timeline</h6>
        <div class="timeline-container ps-4 border-start position-relative ms-2">
            
            <div class="timeline-item mb-4 position-relative">
                <div class="timeline-dot position-absolute bg-primary rounded-circle" style="left: -32px; top: 0; width: 14px; height: 14px; border: 3px solid white; box-shadow: 0 0 0 4px #e0e7ff;"></div>
                <div class="ps-3">
                    <h6 class="fw-bold mb-1">Submitted</h6>
                    <p class="text-muted extra-small mb-1"><?php echo formatDateTime($complaint['created_at']); ?></p>
                    <p class="small text-muted mb-0">Original complaint created by <?php echo htmlspecialchars($complaint['student_name']); ?>.</p>
                </div>
            </div>

            <?php if ($complaint['updated_at'] != $complaint['created_at']): ?>
            <div class="timeline-item mb-4 position-relative">
                <div class="timeline-dot position-absolute bg-info rounded-circle" style="left: -32px; top: 0; width: 14px; height: 14px; border: 3px solid white; box-shadow: 0 0 0 4px #e0f2fe;"></div>
                <div class="ps-3">
                    <h6 class="fw-bold mb-1">Active Updates</h6>
                    <p class="text-muted extra-small mb-1"><?php echo formatDateTime($complaint['updated_at']); ?></p>
                    <p class="small text-muted mb-0">Status changed or remarks added by staff.</p>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($complaint['status'] == 'resolved'): ?>
            <div class="timeline-item position-relative">
                <div class="timeline-dot position-absolute bg-success rounded-circle" style="left: -32px; top: 0; width: 14px; height: 14px; border: 3px solid white; box-shadow: 0 0 0 4px #dcfce7;"></div>
                <div class="ps-3">
                    <h6 class="fw-bold mb-1">Successfully Resolved</h6>
                    <p class="small text-muted mb-0">Issue has been fully addressed.</p>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<style>
.extra-small { font-size: 0.75rem; }
.timeline-container::before {
    content: '';
    position: absolute;
    left: -1px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e5e7eb;
}
</style>
<script>
    lucide.createIcons();
</script>