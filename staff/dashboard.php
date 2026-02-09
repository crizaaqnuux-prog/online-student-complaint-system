<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is a staff member
if (!isLoggedIn() || !hasRole('staff')) {
    redirect('../index.php');
}

// Get staff's assigned complaints
$stmt = $pdo->prepare("
    SELECT c.*, s.username as student_name, s.email as student_email
    FROM complaints c 
    JOIN users s ON c.student_id = s.id 
    WHERE c.assigned_to = ?
    ORDER BY 
        CASE WHEN c.status = 'in_progress' THEN 1 
             WHEN c.status = 'pending' THEN 2 
             WHEN c.status = 'resolved' THEN 3 
             ELSE 4 END,
        c.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$assigned_complaints = $stmt->fetchAll();

// Get unassigned complaints intended for staff
$stmt = $pdo->query("
    SELECT c.*, s.username as student_name, s.email as student_email
    FROM complaints c 
    JOIN users s ON c.student_id = s.id 
    WHERE c.send_to = 'staff' AND c.assigned_to IS NULL
    ORDER BY c.created_at DESC
");
$available_complaints = $stmt->fetchAll();

// Get statistics for this staff member
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
        SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
    FROM complaints 
    WHERE assigned_to = ?
");
$stmt->execute([$_SESSION['user_id']]);
$stats = $stmt->fetch();

// Calculate resolution rate
$resolution_rate = $stats['total'] > 0 ? round(($stats['resolved'] / $stats['total']) * 100, 1) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root {
            --purple-main: #6C63FF;
            --gold-main: #FFD700;
            --dark-sidebar: #1e1b4b;
        }
        
        body {
            background-color: #f0f2f5;
        }

        .sidebar {
            background: linear-gradient(180deg, var(--dark-sidebar) 0%, #2e1065 100%);
            width: 260px;
        }

        .main-content {
            margin-left: 260px;
            padding: 2rem;
        }

        .welcome-card {
            background: linear-gradient(135deg, #6C63FF 0%, #4338ca 100%);
            border: none;
            border-radius: 24px;
            color: white;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(108, 99, 255, 0.3);
            position: relative;
            overflow: hidden;
        }

        .welcome-card::after {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .nav-link {
            border-radius: 12px;
            margin: 0.5rem 1rem;
            color: #94a3b8 !important;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.8rem 1rem;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.05);
            color: white !important;
            transform: translateX(5px);
        }

        .nav-link.active {
            background: var(--purple-main) !important;
            color: white !important;
            box-shadow: 0 4px 15px rgba(108, 99, 255, 0.4);
        }

        .stat-card {
            border: none;
            border-radius: 20px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            background: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            height: 100%;
        }

        .stat-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .icon-box {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .complaint-card {
            border: none;
            border-radius: 16px;
            background: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .complaint-card:hover {
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            transform: scale(1.02);
        }

        .btn-pickup {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            border: none;
            color: white;
            border-radius: 12px;
            font-weight: 600;
        }

        .badge-status {
            padding: 0.5rem 0.8rem;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 6px;
        }

        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark d-lg-none shadow-sm" style="background: var(--dark-sidebar);">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="#">Staff Portal</a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mobileSidebar">
                <i data-lucide="menu"></i>
            </button>
        </div>
    </nav>

    <!-- Mobile Sidebar Collapse -->
    <div class="collapse d-lg-none" id="mobileSidebar" style="background: #2e1065;">
        <div class="nav flex-column p-3">
            <a class="nav-link text-white mb-2" href="dashboard.php"><i data-lucide="layout-dashboard" class="me-2"></i> Dashboard</a>
            <a class="nav-link text-white mb-2" href="assigned_complaints.php"><i data-lucide="clipboard-list" class="me-2"></i> Manage Complaints</a>
            <a class="nav-link text-white mb-2" href="feedbacks.php"><i data-lucide="message-square" class="me-2"></i> Feedbacks</a>
            <a class="nav-link text-danger" href="../logout.php"><i data-lucide="log-out" class="me-2"></i> Logout</a>
        </div>
    </div>

    <div class="container-fluid p-0">
        <!-- Sidebar (Desktop) -->
        <nav class="sidebar position-fixed h-100 d-none d-lg-block">
            <div class="px-4 py-5 text-center">
                <div class="bg-white p-2 rounded-circle d-inline-block mb-3 shadow-sm">
                    <i data-lucide="shield-check" class="text-primary" style="width: 32px; height: 32px;"></i>
                </div>
                <h5 class="text-white fw-bold mb-1">Staff Portal</h5>
                <p class="text-light opacity-50 small mb-0"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
            </div>
            
            <div class="nav flex-column mt-2">
                <a class="nav-link active" href="dashboard.php">
                    <i data-lucide="layout-dashboard"></i> Dashboard
                </a>
                <a class="nav-link" href="assigned_complaints.php">
                    <i data-lucide="clipboard-list"></i> Manage Complaints
                </a>
                <a class="nav-link" href="feedbacks.php">
                    <i data-lucide="message-square"></i> Feedbacks
                </a>
                <div class="mt-auto px-4 py-4">
                    <a class="nav-link bg-danger bg-opacity-10 text-danger border-0" href="../logout.php">
                        <i data-lucide="log-out"></i> Logout
                    </a>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-dark mb-0">Staff Dashboard</h2>
                    <p class="text-muted small">Welcome back to your workspace.</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-white shadow-sm rounded-pill px-4" onclick="location.reload()">
                        <i data-lucide="refresh-cw" class="me-2" style="width: 18px;"></i> Refresh
                    </button>
                </div>
            </div>

            <!-- Welcome Card -->
            <div class="welcome-card mb-4 animate__animated animate__fadeIn">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h1 class="fw-bold mb-2">Hello, <?php echo htmlspecialchars($_SESSION['username']); ?>! </h1>
                        <p class="opacity-75 lead mb-4">You have <?php echo $stats['pending'] + $stats['in_progress']; ?> active complaints that require your attention today.</p>
                        <a href="assigned_complaints.php?status=pending" class="btn btn-light rounded-pill px-4 fw-bold text-primary">View Queue</a>
                    </div>
                    <div class="col-lg-4 d-none d-lg-block text-center text-white text-opacity-25">
                         <i data-lucide="user-check" style="width: 150px; height: 150px;"></i>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="stat-card p-4">
                        <div class="icon-box bg-primary bg-opacity-10 text-primary">
                            <i data-lucide="layers"></i>
                        </div>
                        <h3 class="fw-bold mb-1"><?php echo $stats['total']; ?></h3>
                        <p class="text-muted small mb-0">Total Assigned</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card p-4">
                        <div class="icon-box bg-warning bg-opacity-10 text-warning">
                            <i data-lucide="clock"></i>
                        </div>
                        <h3 class="fw-bold mb-1"><?php echo $stats['pending']; ?></h3>
                        <p class="text-muted small mb-0">Pending Review</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card p-4">
                        <div class="icon-box bg-info bg-opacity-10 text-info">
                            <i data-lucide="activity"></i>
                        </div>
                        <h3 class="fw-bold mb-1"><?php echo $stats['in_progress']; ?></h3>
                        <p class="text-muted small mb-0">In Progress</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card p-4">
                        <div class="icon-box bg-success bg-opacity-10 text-success">
                            <i data-lucide="check-circle"></i>
                        </div>
                        <h3 class="fw-bold mb-1"><?php echo $stats['resolved']; ?></h3>
                        <p class="text-muted small mb-0">Already Resolved</p>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Priority List -->
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-white border-0 py-4 px-4 d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold mb-0">Priority Complaints</h5>
                            <a href="assigned_complaints.php" class="text-primary text-decoration-none small fw-bold">View All</a>
                        </div>
                        <div class="card-body p-4 pt-0">
                            <?php 
                            $priority_complaints = array_filter($assigned_complaints, function($complaint) {
                                return in_array($complaint['status'], ['pending', 'in_progress']);
                            });
                            ?>
                            
                            <?php if (count($priority_complaints) > 0): ?>
                                <div class="row g-3">
                                    <?php foreach (array_slice($priority_complaints, 0, 4) as $complaint): ?>
                                        <div class="col-12">
                                            <div class="complaint-card p-3 border" onclick="viewComplaint(<?php echo $complaint['id']; ?>)">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h6 class="fw-bold mb-1">#<?php echo $complaint['id']; ?> - <?php echo ucfirst($complaint['category']); ?></h6>
                                                        <p class="text-muted small mb-2">Student: <?php echo htmlspecialchars($complaint['student_name']); ?></p>
                                                        <div class="d-flex align-items-center gap-3">
                                                            <span class="small text-muted"><i data-lucide="calendar" class="me-1" style="width: 14px;"></i> <?php echo formatDate($complaint['created_at']); ?></span>
                                                            <span class="badge-status <?php echo getStatusBadge($complaint['status']); ?>">
                                                                <?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="dropdown" onclick="event.stopPropagation()">
                                                        <button class="btn btn-light btn-sm rounded-circle" data-bs-toggle="dropdown">
                                                            <i data-lucide="more-vertical" style="width: 16px;"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="viewComplaint(<?php echo $complaint['id']; ?>)"><i data-lucide="eye" class="me-2" style="width: 14px;"></i> View Details</a></li>
                                                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="updateComplaint(<?php echo $complaint['id']; ?>)"><i data-lucide="edit-3" class="me-2" style="width: 14px;"></i> Update Status</a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <div class="bg-success bg-opacity-10 text-success p-4 rounded-circle d-inline-block mb-3">
                                        <i data-lucide="smile" style="width: 48px; height: 48px;"></i>
                                    </div>
                                    <h5>All Caught Up!</h5>
                                    <p class="text-muted">No pending complaints assigned to you.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Side Actions & Analysis -->
                <div class="col-lg-4">
                    <!-- Performance -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4 text-center">
                            <h6 class="fw-bold mb-4">Your Performance</h6>
                            <div class="position-relative d-inline-block mb-3">
                                <svg width="120" height="120" viewBox="0 0 120 120">
                                    <circle cx="60" cy="60" r="54" fill="none" stroke="#f0f2f5" stroke-width="12" />
                                    <circle cx="60" cy="60" r="54" fill="none" stroke="url(#gradient)" stroke-width="12" 
                                            stroke-dasharray="<?php echo (2 * pi() * 54); ?>" 
                                            stroke-dashoffset="<?php echo (2 * pi() * 54) * (1 - $resolution_rate / 100); ?>" 
                                            transform="rotate(-90 60 60)" stroke-linecap="round" />
                                    <defs>
                                        <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                            <stop offset="0%" stop-color="#6C63FF" />
                                            <stop offset="100%" stop-color="#4338ca" />
                                        </linearGradient>
                                    </defs>
                                </svg>
                                <div class="position-absolute top-50 start-50 translate-middle">
                                    <h4 class="fw-bold mb-0"><?php echo $resolution_rate; ?>%</h4>
                                </div>
                            </div>
                            <p class="text-muted small">Complaint Resolution Rate</p>
                            <div class="row mt-2 g-2">
                                <div class="col-6">
                                    <div class="p-2 border rounded-3 bg-light text-start">
                                        <p class="text-muted extra-small mb-0">Pending</p>
                                        <h6 class="fw-bold mb-0"><?php echo $stats['pending']; ?></h6>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-2 border rounded-3 bg-light text-start">
                                        <p class="text-muted extra-small mb-0">Resolved</p>
                                        <h6 class="fw-bold mb-0"><?php echo $stats['resolved']; ?></h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pickup Center -->
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-header bg-dark border-0 py-3 px-4">
                            <h6 class="text-white mb-0 d-flex align-items-center gap-2">
                                <i data-lucide="hand" style="width: 18px;"></i> Available Center
                            </h6>
                        </div>
                        <div class="card-body p-4 bg-light">
                            <?php if (count($available_complaints) > 0): ?>
                                <p class="small text-muted mb-3"><?php echo count($available_complaints); ?> complaints waiting for pickup.</p>
                                <div class="d-grid gap-2">
                                    <?php foreach (array_slice($available_complaints, 0, 3) as $comp): ?>
                                        <div class="bg-white p-3 rounded-3 shadow-sm border mb-2">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span class="badge bg-purple bg-opacity-10 text-purple extra-small" style="color: #6C63FF;"><?php echo ucfirst($comp['category']); ?></span>
                                                <span class="text-muted extra-small"><?php echo formatDate($comp['created_at']); ?></span>
                                            </div>
                                            <p class="small fw-bold mb-2"><?php echo substr(htmlspecialchars($comp['description']), 0, 50); ?>...</p>
                                            <form method="POST" action="assigned_complaints.php">
                                                <input type="hidden" name="complaint_id" value="<?php echo $comp['id']; ?>">
                                                <input type="hidden" name="action" value="pickup">
                                                <button type="submit" class="btn btn-pickup btn-sm w-100 py-2">Pickup Task</button>
                                            </form>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <a href="assigned_complaints.php" class="btn btn-link w-100 text-decoration-none small text-muted mt-2">View All Available</a>
                            <?php else: ?>
                                <p class="text-center text-muted small my-3">No unassigned complaints.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- View Complaint Modal -->
    <div class="modal fade" id="complaintModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Complaint Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" id="complaintDetails">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Update Complaint Modal -->
    <div class="modal fade" id="updateComplaintModal" tabindex="-1">
        <div class="modal-dialog modal-md">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Update Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" id="updateComplaintContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize Lucide Icons
        lucide.createIcons();

        function viewComplaint(id) {
            fetch('complaint_details.php?id=' + id)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('complaintDetails').innerHTML = data;
                    new bootstrap.Modal(document.getElementById('complaintModal')).show();
                    // Re-init icons in modal if any
                    lucide.createIcons();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading complaint details');
                });
        }

        function updateComplaint(id) {
            fetch('complaint_update.php?id=' + id)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('updateComplaintContent').innerHTML = data;
                    new bootstrap.Modal(document.getElementById('updateComplaintModal')).show();
                    if(typeof lucide !== "undefined") lucide.createIcons();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading complaint update form');
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

        // Auto-refresh every 120 seconds if no modal is open
        setInterval(function() {
            if (!document.querySelector('.modal.show')) {
                location.reload();
            }
        }, 120000);
    </script>
</body>
</html>