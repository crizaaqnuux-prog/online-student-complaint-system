<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assigned Complaints - Staff Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php
    require_once '../includes/config.php';
    require_once '../includes/functions.php';

    // Check if user is logged in and is a staff member
    if (!isLoggedIn() || !hasRole('staff')) {
        redirect('../index.php');
    }

    // Handle complaint pickup via POST or GET
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'pickup') {
        $complaint_id = (int)$_POST['complaint_id'];
        $stmt = $pdo->prepare("UPDATE complaints SET assigned_to = ?, status = 'in_progress' WHERE id = ? AND send_to = 'staff' AND assigned_to IS NULL");
        if ($stmt->execute([$_SESSION['user_id'], $complaint_id])) {
            $_SESSION['success'] = "Complaint #$complaint_id picked up successfully!";
        } else {
            $_SESSION['error'] = "Failed to pick up complaint.";
        }
        redirect('assigned_complaints.php');
    }

    if (isset($_GET['pickup'])) {
        $complaint_id = (int)$_GET['pickup'];
        $stmt = $pdo->prepare("UPDATE complaints SET assigned_to = ?, status = 'in_progress' WHERE id = ? AND send_to = 'staff' AND assigned_to IS NULL");
        if ($stmt->execute([$_SESSION['user_id'], $complaint_id])) {
            $_SESSION['success'] = "Complaint #$complaint_id picked up successfully!";
        } else {
            $_SESSION['error'] = "Failed to pick up complaint.";
        }
        redirect('assigned_complaints.php');
    }

    // Get filter parameters
    $status_filter = isset($_GET['status']) ? $_GET['status'] : '';
    $priority = isset($_GET['priority']) ? (bool)$_GET['priority'] : false;

    // Build query with filters
    $where_conditions = ["c.assigned_to = ?"];
    $params = [$_SESSION['user_id']];

    if ($status_filter) {
        $where_conditions[] = "c.status = ?";
        $params[] = $status_filter;
    }

    if ($priority) {
        $where_conditions[] = "c.status IN ('pending', 'in_progress')";
    }

    $where_clause = implode(' AND ', $where_conditions);

    // Get staff's assigned complaints with filters
    $stmt = $pdo->prepare("
        SELECT c.*, s.username as student_name, s.email as student_email
        FROM complaints c 
        JOIN users s ON c.student_id = s.id 
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
    ?>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h5 class="text-white">Staff Portal</h5>
                        <small class="text-light"><?php echo htmlspecialchars($_SESSION['username']); ?></small>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="assigned_complaints.php">
                                <i class="fas fa-list-alt"></i> Manage Complaints
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="feedbacks.php">
                                <i class="fas fa-comment-dots"></i> Visitor Feedbacks
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Assigned Complaints</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                    </div>
                </div>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Filters -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form method="GET" class="row g-3">
                                    <div class="col-md-4">
                                        <label for="status" class="form-label">Filter by Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="">All Statuses</option>
                                            <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="in_progress" <?php echo $status_filter == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                            <option value="resolved" <?php echo $status_filter == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                            <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Quick Filters</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="priority" name="priority" value="1" 
                                                   <?php echo $priority ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="priority">
                                                Priority Only (Pending & In Progress)
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary me-2">
                                            <i class="fas fa-filter"></i> Apply Filters
                                        </button>
                                        <a href="assigned_complaints.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-times"></i> Clear
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs for My Complaints and Available -->
                <ul class="nav nav-tabs mb-4 px-3 border-0" id="complaintTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold border-0 bg-transparent py-3" id="my-tab" data-bs-toggle="tab" data-bs-target="#my-complaints" type="button" role="tab">
                            <i class="fas fa-user-check me-2"></i> My Assigned <span class="badge bg-primary ms-1"><?php echo count($complaints); ?></span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold border-0 bg-transparent py-3" id="available-tab" data-bs-toggle="tab" data-bs-target="#available-complaints" type="button" role="tab">
                            <i class="fas fa-clipboard-list me-2"></i> Available to Pickup 
                            <?php 
                            $stmt_avail = $pdo->query("SELECT COUNT(*) FROM complaints WHERE send_to = 'staff' AND assigned_to IS NULL");
                            $avail_count = $stmt_avail->fetchColumn();
                            ?>
                            <span class="badge bg-info ms-1"><?php echo $avail_count; ?></span>
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="complaintTabsContent">
                    <!-- My Assigned Tab -->
                    <div class="tab-pane fade show active" id="my-complaints" role="tabpanel">
                        <div class="row">
                            <?php if (count($complaints) > 0): ?>
                                <?php foreach ($complaints as $complaint): ?>
                                    <div class="col-lg-6 col-xl-4 mb-4">
                                        <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden status-card-wrapper">
                                            <div class="card-header border-0 d-flex justify-content-between align-items-center py-3 px-4 bg-white">
                                                <h6 class="mb-0 fw-bold text-primary">#<?php echo $complaint['id']; ?></h6>
                                                <span class="badge-status <?php echo getStatusBadge($complaint['status']); ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?>
                                                </span>
                                            </div>
                                            <div class="card-body px-4">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="flex-shrink-0 bg-light rounded-circle p-2 me-3">
                                                        <i class="fas fa-user-graduate text-muted"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($complaint['student_name']); ?></div>
                                                        <small class="text-muted"><?php echo htmlspecialchars($complaint['student_email']); ?></small>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <span class="badge bg-purple bg-opacity-10 text-purple border-0 px-2 py-1" style="color: #6C63FF; background-color: rgba(108, 99, 255, 0.1);">
                                                        <i class="fas fa-tag me-1 small"></i> <?php echo ucfirst($complaint['category']); ?>
                                                    </span>
                                                    <span class="small text-muted ms-2"><i class="far fa-calendar-alt me-1"></i> <?php echo formatDate($complaint['created_at']); ?></span>
                                                </div>
                                                <div class="mb-0">
                                                    <p class="small text-muted mb-0 line-clamp-3">
                                                        <?php echo htmlspecialchars($complaint['description']); ?>
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="card-footer bg-white border-0 px-4 pb-4 mt-auto">
                                                <div class="d-flex gap-2 mb-2">
                                                    <button class="btn btn-sm btn-light flex-fill rounded-pill fw-bold" 
                                                            onclick="viewComplaint(<?php echo $complaint['id']; ?>)">
                                                        <i class="fas fa-eye me-1"></i> View Details
                                                    </button>
                                                    <button class="btn btn-sm btn-primary flex-fill rounded-pill fw-bold shadow-sm" 
                                                            onclick="updateComplaint(<?php echo $complaint['id']; ?>)">
                                                        <i class="fas fa-reply me-1"></i> Respond
                                                    </button>
                                                </div>
                                                
                                                <?php if ($complaint['status'] == 'pending'): ?>
                                                    <button class="btn btn-sm btn-outline-info w-100 rounded-pill fw-bold" 
                                                            onclick="updateComplaint(<?php echo $complaint['id']; ?>, 'in_progress')">
                                                        <i class="fas fa-play me-1"></i> Start Working
                                                    </button>
                                                <?php elseif ($complaint['status'] == 'in_progress'): ?>
                                                    <div class="d-flex gap-2">
                                                        <button class="btn btn-sm btn-outline-success flex-fill rounded-pill fw-bold" 
                                                                onclick="updateComplaint(<?php echo $complaint['id']; ?>, 'resolved')">
                                                            <i class="fas fa-check me-1"></i> Resolve
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-danger flex-fill rounded-pill fw-bold" 
                                                                onclick="updateComplaint(<?php echo $complaint['id']; ?>, 'rejected')">
                                                            <i class="fas fa-times me-1"></i> Reject
                                                        </button>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12 text-center py-5">
                                    <div class="bg-light d-inline-block rounded-circle p-4 mb-3">
                                        <i class="fas fa-tasks fa-3x text-muted opacity-50"></i>
                                    </div>
                                    <h5 class="text-muted">No complaints assigned to you</h5>
                                    <p class="text-muted">Check the 'Available' tab to pick up new complaints.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Available Tab -->
                    <div class="tab-pane fade" id="available-complaints" role="tabpanel">
                        <div class="row">
                            <?php 
                            $stmt_all_avail = $pdo->prepare("
                                SELECT c.*, s.username as student_name, s.email as student_email
                                FROM complaints c 
                                JOIN users s ON c.student_id = s.id 
                                WHERE c.send_to = 'staff' AND c.assigned_to IS NULL
                                ORDER BY c.created_at DESC
                            ");
                            $stmt_all_avail->execute();
                            $available_tasks = $stmt_all_avail->fetchAll();
                            
                            if (count($available_tasks) > 0): 
                                foreach ($available_tasks as $task): ?>
                                    <div class="col-lg-6 col-xl-4 mb-4">
                                        <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden">
                                            <div class="card-header border-0 d-flex justify-content-between align-items-center py-3 px-4 bg-white">
                                                <h6 class="mb-0 fw-bold text-info">#<?php echo $task['id']; ?></h6>
                                                <span class="badge bg-warning text-dark rounded-pill small">NEW</span>
                                            </div>
                                            <div class="card-body px-4">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="flex-shrink-0 bg-light rounded-circle p-2 me-3">
                                                        <i class="fas fa-user text-muted"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($task['student_name']); ?></div>
                                                        <small class="text-muted"><?php echo formatDate($task['created_at']); ?></small>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <span class="badge bg-light text-dark border-0 px-2 py-1">
                                                        <?php echo ucfirst($task['category']); ?>
                                                    </span>
                                                </div>
                                                <p class="small text-muted mb-0">
                                                    <?php echo substr(htmlspecialchars($task['description']), 0, 150); ?>...
                                                </p>
                                            </div>
                                            <div class="card-footer bg-white border-0 px-4 pb-4">
                                                <form method="POST">
                                                    <input type="hidden" name="complaint_id" value="<?php echo $task['id']; ?>">
                                                    <input type="hidden" name="action" value="pickup">
                                                    <button type="submit" class="btn btn-info w-100 rounded-pill fw-bold text-white shadow-sm">
                                                        <i class="fas fa-hand-holding me-1"></i> Pickup & Respond
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12 text-center py-5">
                                    <div class="bg-success bg-opacity-10 d-inline-block rounded-circle p-4 mb-3">
                                        <i class="fas fa-check-circle fa-3x text-success"></i>
                                    </div>
                                    <h5 class="text-muted">No available complaints</h5>
                                    <p class="text-muted">You're all caught up! There are no unassigned complaints at the moment.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- View Complaint Modal -->
    <div class="modal fade" id="complaintModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Complaint Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="complaintDetails">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Update Complaint Modal -->
    <div class="modal fade" id="updateComplaintModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Complaint</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="updateComplaintContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Add Lucide Icons Library -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        // Initialize Lucide icons on page load
        lucide.createIcons();
        function viewComplaint(id) {
            const modalBody = document.getElementById('complaintDetails');
            modalBody.innerHTML = '<div class="text-center p-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2 text-muted">Loading online student complaint system details...</p></div>';
            
            const modalElement = document.getElementById('complaintModal');
            const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
            modal.show();

            fetch('complaint_details.php?id=' + id)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.text();
                })
                .then(data => {
                    modalBody.innerHTML = data;
                })
                .catch(error => {
                    console.error('Error:', error);
                    modalBody.innerHTML = '<div class="alert alert-danger mx-3 my-3">online student complaint system: Failed to load complaint details. Please check your connection and try again.</div>';
                });
        }

        function updateComplaint(id, initialStatus = null) {
            const modalBody = document.getElementById('updateComplaintContent');
            modalBody.innerHTML = '<div class="text-center p-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2 text-muted">Preparing update form...</p></div>';
            
            const modalElement = document.getElementById('updateComplaintModal');
            const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
            modal.show();

            let url = 'complaint_update.php?id=' + id;
            if (initialStatus) {
                url += '&status=' + initialStatus;
            }

            fetch(url)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.text();
                })
                .then(data => {
                    modalBody.innerHTML = data;
                })
                .catch(error => {
                    console.error('Error:', error);
                    modalBody.innerHTML = '<div class="alert alert-danger mx-3 my-3">online student complaint system: Failed to load update form. Please try again.</div>';
                });
        }

        // Common functions for update modal
        function applyTemplate(text) {
            const textarea = document.getElementById('admin_remarks');
            if (textarea) {
                textarea.value = text;
                textarea.focus();
            }
        }

        function updateTemplate(status) {
            const textarea = document.getElementById('admin_remarks');
            if (textarea && textarea.value.trim() === '') {
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
            const originalBtnContent = btn ? btn.innerHTML : 'Save';
            
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Updating...';
            }
            
            const formData = new FormData(form);
            
            fetch('complaint_update.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                const contentDiv = document.getElementById('updateComplaintContent');
                if (contentDiv) contentDiv.innerHTML = data;
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the complaint.');
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalBtnContent;
                }
            });
        }

        function quickUpdate(id, status) {
            if (confirm('Are you sure you want to update this complaint status to ' + status.replace('_', ' ') + '?')) {
                const formData = new FormData();
                formData.append('complaint_id', id);
                formData.append('status', status);
                
                let remarks = 'Complaint is being reviewed and worked on.';
                if (status === 'resolved') {
                    remarks = 'Complaint has been resolved. Please contact us if you need further assistance.';
                } else if (status === 'rejected') {
                    remarks = 'Unfortunately, your complaint has been rejected. Please contact the department for more details.';
                }
                formData.append('admin_remarks', remarks);

                fetch('complaint_update.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    location.reload();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error updating complaint');
                });
            }
        }
    </script>
</body>
</html>