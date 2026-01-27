<?php
$page_title = 'Reports & Analytics';
$extra_head = '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';
require_once 'includes/header.php';

// Handle report generation
if (isset($_GET['export'])) {
    $export_type = $_GET['export'];
    $date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
    $date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
    
    $where_clause = "1=1";
    $params = [];
    
    if ($date_from) {
        $where_clause .= " AND DATE(c.created_at) >= ?";
        $params[] = $date_from;
    }
    
    if ($date_to) {
        $where_clause .= " AND DATE(c.created_at) <= ?";
        $params[] = $date_to;
    }
    
    $stmt = $pdo->prepare("
        SELECT c.*, s.username as student_name, s.email as student_email, u.username as assigned_to_name
        FROM complaints c 
        JOIN users s ON c.student_id = s.id 
        LEFT JOIN users u ON c.assigned_to = u.id 
        WHERE $where_clause
        ORDER BY c.created_at DESC
    ");
    $stmt->execute($params);
    $export_data = $stmt->fetchAll();
    
    if ($export_type === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="complaints_report_' . date('Y-m-d') . '.csv"');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Student Name', 'Student Email', 'Category', 'Description', 'Status', 'Assigned To', 'Admin Remarks', 'Created At', 'Updated At']);
        foreach ($export_data as $row) {
            fputcsv($output, [$row['id'], $row['student_name'], $row['student_email'], $row['category'], $row['description'], $row['status'], $row['assigned_to_name'] ?: 'Not Assigned', $row['admin_remarks'], $row['created_at'], $row['updated_at']]);
        }
        fclose($output);
        exit;
    }
}

// Get statistics
$stmt = $pdo->query("SELECT COUNT(*) as total, SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending, SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress, SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved FROM complaints");
$overall_stats = $stmt->fetch();

// Monthly statistics
$stmt = $pdo->query("SELECT MONTH(created_at) as month, MONTHNAME(created_at) as month_name, COUNT(*) as count, SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved FROM complaints WHERE YEAR(created_at) = YEAR(NOW()) GROUP BY MONTH(created_at) ORDER BY month");
$monthly_stats = $stmt->fetchAll();

// Category statistics
$stmt = $pdo->query("SELECT category, COUNT(*) as total, SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved, ROUND((SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 1) as resolution_rate FROM complaints GROUP BY category ORDER BY total DESC");
$category_stats = $stmt->fetchAll();

require_once 'includes/sidebar.php';
require_once 'includes/navbar.php';
?>

<!-- Action Bar -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <div>
                <h5 class="fw-bold mb-1">Export Generation Center</h5>
                <p class="text-muted small mb-0">Select date ranges to generate institutional reports.</p>
            </div>
            <form method="GET" class="d-flex gap-2">
                <input type="date" name="date_from" class="form-control form-control-sm bg-light border-0" value="<?php echo $_GET['date_from'] ?? ''; ?>">
                <input type="date" name="date_to" class="form-control form-control-sm bg-light border-0" value="<?php echo $_GET['date_to'] ?? ''; ?>">
                <button type="submit" name="export" value="csv" class="btn btn-success btn-sm px-4 fw-bold">
                    <i class="fas fa-file-csv me-2"></i> CSV
                </button>
                <button type="button" onclick="window.print()" class="btn btn-light border btn-sm px-4 fw-bold">
                    <i class="fas fa-print me-2"></i> Print
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Key Performance Indicators -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stats-card h-100">
            <div class="card-body">
                <div class="p-3 rounded-4 bg-primary bg-opacity-10 text-primary w-fit mb-3">
                    <i class="fas fa-database"></i>
                </div>
                <h3 class="fw-bold mb-1"><?php echo $overall_stats['total']; ?></h3>
                <p class="text-muted small uppercase fw-semibold">Lifetime Total</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stats-card h-100" style="--primary-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);">
            <div class="card-body">
                <div class="p-3 rounded-4 bg-success bg-opacity-10 text-success w-fit mb-3">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3 class="fw-bold mb-1" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); -webkit-background-clip: text; background-clip: text;"><?php echo $overall_stats['resolved']; ?></h3>
                <p class="text-muted small uppercase fw-semibold">Resolved Cases</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stats-card h-100" style="--primary-gradient: var(--accent-gradient);">
            <div class="card-body">
                <div class="p-3 rounded-4 bg-warning bg-opacity-10 text-warning w-fit mb-3">
                    <i class="fas fa-fire-alt"></i>
                </div>
                <h3 class="fw-bold mb-1" style="background: var(--accent-gradient); -webkit-background-clip: text; background-clip: text;"><?php echo $overall_stats['pending'] + $overall_stats['in_progress']; ?></h3>
                <p class="text-muted small uppercase fw-semibold">Active Workload</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stats-card h-100" style="--primary-gradient: var(--secondary-gradient);">
            <div class="card-body">
                <div class="p-3 rounded-4 bg-info bg-opacity-10 text-info w-fit mb-3">
                    <i class="fas fa-percentage"></i>
                </div>
                <h3 class="fw-bold mb-1" style="background: var(--secondary-gradient); -webkit-background-clip: text; background-clip: text;">
                    <?php echo $overall_stats['total'] > 0 ? round(($overall_stats['resolved'] / $overall_stats['total']) * 100, 1) : 0; ?>%
                </h3>
                <p class="text-muted small uppercase fw-semibold">Efficiency Rate</p>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h5 class="fw-bold mb-0">Institutional Trends</h5>
            </div>
            <div class="card-body">
                <canvas id="monthlyChart" height="350"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h5 class="fw-bold mb-0">Category Weight</h5>
            </div>
            <div class="card-body">
                <canvas id="categoryChart" height="350"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between">
        <h5 class="fw-bold mb-0">Performance Matrix</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Categorization</th>
                        <th>Total Submitted</th>
                        <th>Resolution Target</th>
                        <th>Efficiency Score</th>
                        <th class="text-end pe-4">Visualization</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($category_stats as $cat): ?>
                        <tr>
                            <td class="ps-4">
                                <span class="badge bg-light text-dark border px-3 py-2 fw-bold"><?php echo ucfirst($cat['category']); ?></span>
                            </td>
                            <td><span class="fw-bold"><?php echo $cat['total']; ?></span> cases</td>
                            <td><span class="text-success"><?php echo $cat['resolved']; ?></span> resolved</td>
                            <td><span class="fw-bold"><?php echo $cat['resolution_rate']; ?>%</span></td>
                            <td class="text-end pe-4" style="width: 200px;">
                                <div class="progress" style="height: 6px; border-radius: 10px;">
                                    <div class="progress-bar <?php echo $cat['resolution_rate'] >= 80 ? 'bg-success' : ($cat['resolution_rate'] >= 60 ? 'bg-warning' : 'bg-danger'); ?>" 
                                         role="progressbar" style="width: <?php echo $cat['resolution_rate']; ?>%"></div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
$extra_js = "
<script>
    Chart.defaults.font.family = \"'Outfit', sans-serif\";
    
    // Monthly Trends
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: [" . implode(',', array_map(function($s){return "'".$s['month_name']."'";}, $monthly_stats)) . "],
            datasets: [{
                label: 'Total Volume',
                data: [" . implode(',', array_column($monthly_stats, 'count')) . "],
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#fff',
                pointBorderWidth: 2
            }, {
                label: 'Success Rate',
                data: [" . implode(',', array_column($monthly_stats, 'resolved')) . "],
                borderColor: '#10b981',
                backgroundColor: 'transparent',
                tension: 0.4,
                pointRadius: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'top', align: 'end' } },
            scales: {
                y: { grid: { color: '#f1f5f9' }, beginAtZero: true },
                x: { grid: { display: false } }
            }
        }
    });

    // Category Weight
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: [" . implode(',', array_map(function($c){return "'".ucfirst($c['category'])."'";}, $category_stats)) . "],
            datasets: [{
                data: [" . implode(',', array_column($category_stats, 'total')) . "],
                backgroundColor: ['#6366f1', '#a855f7', '#3b82f6', '#2dd4bf', '#f59e0b', '#ef4444'],
                borderWidth: 0,
                hoverOffset: 15
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: { legend: { position: 'bottom', labels: { padding: 20, usePointStyle: true } } }
        }
    });
</script>";
require_once 'includes/footer.php';
?>