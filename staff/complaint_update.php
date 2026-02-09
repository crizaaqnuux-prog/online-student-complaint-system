<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is a staff member
if (!isLoggedIn() || !hasRole('staff')) {
    echo '<div class="alert alert-danger">Access denied.</div>';
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $complaint_id = (int)$_POST['complaint_id'];
    $new_status = $_POST['status'];
    $admin_remarks = sanitizeInput($_POST['admin_remarks']);
    
    try {
        // Update the complaint
        $stmt = $pdo->prepare("
            UPDATE complaints 
            SET status = ?, admin_remarks = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ? AND assigned_to = ?
        ");
        
        if ($stmt->execute([$new_status, $admin_remarks, $complaint_id, $_SESSION['user_id']])) {
            // Send notification to student
            $stmt_notif = $pdo->prepare("SELECT student_id FROM complaints WHERE id = ?");
            $stmt_notif->execute([$complaint_id]);
            $complaint_data = $stmt_notif->fetch();
            
            if ($complaint_data) {
                $status_text = ucfirst(str_replace('_', ' ', $new_status));
                sendNotification($complaint_data['student_id'], "Your complaint #$complaint_id has been updated to '$status_text' by staff.");
            }
            
            echo '<div class="alert alert-success border-0 shadow-lg rounded-4 p-4 mb-0 animate__animated animate__bounceIn">
                    <div class="d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 rounded-circle p-2 me-3">
                            <i data-lucide="check-circle-2" class="text-success" style="width: 24px; height: 24px;"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1 text-success">Update Successful!</h6>
                            <p class="small mb-0 text-success-emphasis">The complaint status has been set to <strong>' . $status_text . '</strong>.</p>
                        </div>
                    </div>
                  </div>';
            echo '<script>
                    if(typeof lucide !== "undefined") lucide.createIcons();
                    setTimeout(function(){ location.reload(); }, 1500);
                  </script>';
        } else {
            echo '<div class="alert alert-danger border-0 shadow-sm rounded-4">Failed to execute update. Please try again.</div>';
        }
        exit;
    } catch(PDOException $e) {
        echo '<div class="alert alert-danger border-0 shadow-sm rounded-4">Error: ' . $e->getMessage() . '</div>';
        exit;
    }
}

$complaint_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$initial_status = isset($_GET['status']) ? $_GET['status'] : '';

if ($complaint_id <= 0) {
    echo '<div class="alert alert-danger">Invalid complaint ID.</div>';
    exit;
}

// Get complaint details - ensure it's assigned to this staff member
$stmt = $pdo->prepare("
    SELECT c.*, s.username as student_name, s.email as student_email
    FROM complaints c 
    JOIN users s ON c.student_id = s.id 
    WHERE c.id = ? AND c.assigned_to = ?
");
$stmt->execute([$complaint_id, $_SESSION['user_id']]);
$complaint = $stmt->fetch();

if (!$complaint) {
    echo '<div class="alert alert-danger">Complaint not found or not assigned to you.</div>';
    exit;
}

// Override current status with initial status if provided
$display_status = $initial_status ?: $complaint['status'];
?>

<div class="complaint-update animate__animated animate__fadeIn">
    <!-- Header Summary -->
    <div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-light rounded-4 border">
        <div>
            <span class="text-muted extra-small text-uppercase fw-bold">Currently Updating</span>
            <h6 class="fw-bold mb-0 text-primary">Complaint #<?php echo $complaint['id']; ?></h6>
        </div>
        <div class="text-end">
            <span class="badge-status <?php echo getStatusBadge($complaint['status']); ?>">
                Current: <?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?>
            </span>
        </div>
    </div>

    <!-- Main Update Form -->
    <form method="POST" id="mainUpdateForm" action="complaint_update.php" onsubmit="submitUpdateForm(event)">
        <input type="hidden" name="complaint_id" value="<?php echo $complaint['id']; ?>">
        
        <div class="mb-4">
            <label class="form-label fw-bold small text-muted text-uppercase mb-3">Choose New Status</label>
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <input type="radio" class="btn-check" name="status" id="status_pending" value="pending" <?php echo $display_status == 'pending' ? 'checked' : ''; ?>>
                    <label class="btn btn-outline-warning w-100 py-3 rounded-4 shadow-sm border-2 status-option-label" for="status_pending" onclick="updateTemplate('pending')">
                        <i data-lucide="clock" class="d-block mb-1 mx-auto"></i>
                        <span class="small fw-bold">Pending</span>
                    </label>
                </div>
                <div class="col-6 col-md-3">
                    <input type="radio" class="btn-check" name="status" id="status_progress" value="in_progress" <?php echo $display_status == 'in_progress' ? 'checked' : ''; ?>>
                    <label class="btn btn-outline-info w-100 py-3 rounded-4 shadow-sm border-2 status-option-label" for="status_progress" onclick="updateTemplate('in_progress')">
                        <i data-lucide="activity" class="d-block mb-1 mx-auto"></i>
                        <span class="small fw-bold">In Progress</span>
                    </label>
                </div>
                <div class="col-6 col-md-3">
                    <input type="radio" class="btn-check" name="status" id="status_resolved" value="resolved" <?php echo $display_status == 'resolved' ? 'checked' : ''; ?>>
                    <label class="btn btn-outline-success w-100 py-3 rounded-4 shadow-sm border-2 status-option-label" for="status_resolved" onclick="updateTemplate('resolved')">
                        <i data-lucide="check-circle" class="d-block mb-1 mx-auto"></i>
                        <span class="small fw-bold">Resolved</span>
                    </label>
                </div>
                <div class="col-6 col-md-3">
                    <input type="radio" class="btn-check" name="status" id="status_rejected" value="rejected" <?php echo $display_status == 'rejected' ? 'checked' : ''; ?>>
                    <label class="btn btn-outline-danger w-100 py-3 rounded-4 shadow-sm border-2 status-option-label" for="status_rejected" onclick="updateTemplate('rejected')">
                        <i data-lucide="x-circle" class="d-block mb-1 mx-auto"></i>
                        <span class="small fw-bold">Rejected</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label for="admin_remarks" class="form-label fw-bold small text-muted text-uppercase mb-0">Response / Resolution Remarks</label>
                <div class="dropdown">
                    <button class="btn btn-link btn-sm text-decoration-none dropdown-toggle p-0" type="button" data-bs-toggle="dropdown">
                        <i data-lucide="list" class="me-1" style="width: 14px;"></i> Templates
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg p-2 rounded-3">
                        <li><a class="dropdown-item rounded-2 mb-1" href="javascript:void(0)" onclick="applyTemplate('General inquiry received. We are investigating.')">Investigating</a></li>
                        <li><a class="dropdown-item rounded-2 mb-1" href="javascript:void(0)" onclick="applyTemplate('The issue has been resolved successfully. Academic records updated.')">Resolved Academic</a></li>
                        <li><a class="dropdown-item rounded-2 mb-1" href="javascript:void(0)" onclick="applyTemplate('Documentation provided is insufficient. Please resubmit with details.')">Need Details</a></li>
                        <li><a class="dropdown-item rounded-2 text-danger" href="javascript:void(0)" onclick="applyTemplate('This request does not meet university guidelines and is rejected.')">Reject Policy</a></li>
                    </ul>
                </div>
            </div>
            <textarea class="form-control border-light-subtle shadow-sm rounded-4 p-4" id="admin_remarks" name="admin_remarks" rows="5" 
                    placeholder="Enter resolution notes here... The student will see this response." required><?php echo htmlspecialchars($complaint['admin_remarks']); ?></textarea>
        </div>

        <div class="d-flex gap-2 justify-content-end pt-2 border-top">
            <button type="button" class="btn btn-light px-4 py-2 rounded-pill fw-bold" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary px-5 py-2 rounded-pill fw-bold shadow-sm d-flex align-items-center gap-2" id="submitBtn">
                Save & Update Status
            </button>
        </div>
    </form>
</div>

<script>
    // Initialize icons for the dynamic content
    lucide.createIcons();

    function applyTemplate(text) {
        const textarea = document.getElementById('admin_remarks');
        textarea.value = text;
        textarea.focus();
    }

    function updateTemplate(status) {
        const textarea = document.getElementById('admin_remarks');
        if (textarea.value.trim() === '') {
            if (status === 'in_progress') {
                textarea.value = 'We have received your complaint and are currently working on a resolution. We will update you shortly.';
            } else if (status === 'resolved') {
                textarea.value = 'Resolution complete: The issues reported in this complaint have been fully addressed. Thank you for your patience.';
            } else if (status === 'rejected') {
                textarea.value = 'We have reviewed your complaint but are unable to proceed at this time as it does not meet the necessary criteria.';
            }
        }
    }

    function submitUpdateForm(e) {
        e.preventDefault();
        const form = e.target;
        const btn = document.getElementById('submitBtn');
        const originalBtnContent = btn.innerHTML;
        
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Updating...';
        
        const formData = new FormData(form);
        
        fetch('complaint_update.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            document.getElementById('updateComplaintContent').innerHTML = data;
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the complaint.');
            btn.disabled = false;
            btn.innerHTML = originalBtnContent;
        });
    }
</script>