<?php
$page_title = 'Admin Dashboard';
$extra_head = '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';

require_once 'includes/header.php';
require_once 'includes/sidebar.php';
require_once 'includes/navbar.php';

// Data fetching logic
$stats = getComplaintStats();

// Get recent complaints
$stmt = $pdo->prepare("
    SELECT c.*, s.username as student_name, u.username as assigned_to_name 
    FROM complaints c 
    JOIN users s ON c.student_id = s.id 
    LEFT JOIN users u ON c.assigned_to = u.id 
    ORDER BY c.created_at DESC 
    LIMIT 10
");
$stmt->execute();
$recent_complaints = $stmt->fetchAll();

// Get category-wise statistics
$stmt = $pdo->query("
    SELECT category, COUNT(*) as count 
    FROM complaints 
    GROUP BY category 
    ORDER BY count DESC
");
$category_stats = $stmt->fetchAll();

// Get total users count
$stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users WHERE role = 'student'");
$user_stats = $stmt->fetch();
?>

<!-- Welcome Card -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card welcome-card">
            <div class="card-body">
                <h4 class="card-title">Welcome, Administrator!</h4>
                <p class="card-text">Monitor and manage student complaints from the central dashboard.</p>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stats-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="p-3 rounded-4 bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-folder-open fs-4"></i>
                    </div>
                    <span class="text-success small fw-bold">+12% <i class="fas fa-arrow-up"></i></span>
                </div>
                <h3 class="fw-bold mb-1"><?php echo $stats['total']; ?></h3>
                <p class="text-muted mb-0 small uppercase fw-semibold">Total Complaints</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stats-card h-100" style="--primary-gradient: var(--accent-gradient);">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="p-3 rounded-4 bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-clock fs-4"></i>
                    </div>
                    <span class="text-warning small fw-bold">Active</span>
                </div>
                <h3 class="fw-bold mb-1" style="background: var(--accent-gradient); -webkit-background-clip: text; background-clip: text;"><?php echo $stats['pending']; ?></h3>
                <p class="text-muted mb-0 small uppercase fw-semibold">Pending Issues</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stats-card h-100" style="--primary-gradient: var(--secondary-gradient);">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="p-3 rounded-4 bg-info bg-opacity-10 text-info">
                        <i class="fas fa-spinner fs-4"></i>
                    </div>
                    <span class="text-info small fw-bold">Processing</span>
                </div>
                <h3 class="fw-bold mb-1" style="background: var(--secondary-gradient); -webkit-background-clip: text; background-clip: text;"><?php echo $stats['in_progress']; ?></h3>
                <p class="text-muted mb-0 small uppercase fw-semibold">In Progress</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stats-card h-100" style="--primary-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="p-3 rounded-4 bg-success bg-opacity-10 text-success">
                        <i class="fas fa-check-double fs-4"></i>
                    </div>
                    <span class="text-success small fw-bold">Done</span>
                </div>
                <h3 class="fw-bold mb-1" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); -webkit-background-clip: text; background-clip: text;"><?php echo $stats['resolved']; ?></h3>
                <p class="text-muted mb-0 small uppercase fw-semibold">Resolved Cases</p>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Complaints by Status</h5>
            </div>
            <div class="card-body">
                <canvas id="statusChart" width="400" height="300"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Complaints by Category</h5>
            </div>
            <div class="card-body">
                <canvas id="categoryChart" width="400" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Complaints and Quick Stats -->
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header border-0 bg-transparent d-flex justify-content-between align-items-center pt-4 px-4">
                <h5 class="mb-0 fw-bold">Recent Complaints</h5>
                <a href="complaints.php" class="btn btn-sm btn-light border fw-semibold">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if (count($recent_complaints) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Student</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_complaints as $complaint): ?>
                                    <tr>
                                        <td><span class="fw-bold text-primary">#<?php echo $complaint['id']; ?></span></td>
                                        <td><?php echo htmlspecialchars($complaint['student_name']); ?></td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                <?php echo ucfirst($complaint['category']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="<?php echo getStatusBadge($complaint['status']); ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?>
                                            </span>
                                        </td>
                                        <td><span class="text-muted small"><?php echo formatDate($complaint['created_at']); ?></span></td>
                                        <td>
                                            <a href="complaints.php?id=<?php echo $complaint['id']; ?>" 
                                               class="btn btn-sm btn-primary px-3">
                                                Manage
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <div class="mb-3 text-muted opacity-25">
                            <i class="fas fa-inbox fa-3x"></i>
                        </div>
                        <p class="text-muted mb-0">No complaints found</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">System Overview</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Total Students</span>
                    <span class="badge bg-primary rounded-pill"><?php echo $user_stats['total_users']; ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Resolution Rate</span>
                    <strong>
                        <?php 
                        $resolution_rate = $stats['total'] > 0 ? round(($stats['resolved'] / $stats['total']) * 100, 1) : 0;
                        echo $resolution_rate . '%';
                        ?>
                    </strong>
                </div>
                <div class="progress mb-3" style="height: 6px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $resolution_rate; ?>%" aria-valuenow="<?php echo $resolution_rate; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm" style="background: var(--primary-gradient);">
            <div class="card-body p-4 text-white">
                <h6 class="mb-3">Quick Actions</h6>
                <div class="d-grid gap-2">
                    <a href="complaints.php?status=pending" class="btn btn-light btn-sm text-primary fw-bold py-2">
                        <i class="fas fa-clock me-2"></i> Pending Issues
                    </a>
                    <a href="manage_users.php" class="btn border-white btn-sm text-white fw-bold py-2">
                        <i class="fas fa-users me-2"></i> Manage Users
                    </a>
                    <a href="reports.php" class="btn border-white btn-sm text-white fw-bold py-2">
                        <i class="fas fa-download me-2"></i> Get Reports
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$extra_js = "
<script>
    Chart.defaults.font.family = \"'Outfit', sans-serif\";
    Chart.defaults.color = '#64748b';

    // Status Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'In Progress', 'Resolved', 'Rejected'],
            datasets: [{
                data: [
                    " . $stats['pending'] . ",
                    " . $stats['in_progress'] . ",
                    " . $stats['resolved'] . ",
                    " . $stats['rejected'] . "
                ],
                backgroundColor: ['#f59e0b', '#3b82f6', '#10b981', '#ef4444'],
                borderWidth: 0,
                hoverOffset: 20
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '75%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { padding: 20, usePointStyle: true, pointStyle: 'circle' }
                }
            }
        }
    });

    // Category Chart
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    const gradient = categoryCtx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(99, 102, 241, 1)');
    gradient.addColorStop(1, 'rgba(168, 85, 247, 0.4)');

    new Chart(categoryCtx, {
        type: 'bar',
        data: {
            labels: [" . implode(',', array_map(function($c){return "'".ucfirst($c['category'])."'";}, $category_stats)) . "],
            datasets: [{
                label: 'Complaints',
                data: [" . implode(',', array_column($category_stats, 'count')) . "],
                backgroundColor: gradient,
                borderRadius: 8,
                maxBarThickness: 40
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
                x: { grid: { display: false } }
            },
            plugins: { legend: { display: false } }
        }
    });
</script>";

require_once 'includes/footer.php';
?>