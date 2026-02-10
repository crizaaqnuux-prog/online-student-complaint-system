<?php
$page_title = 'Individual Case Report';
require_once 'includes/header.php';

// Pagination/Individual navigation
$current_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch all complaint IDs for navigation
$stmt = $pdo->query("SELECT id FROM complaints ORDER BY created_at DESC");
$all_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
$total_cases = count($all_ids);

if ($total_cases == 0) {
    require_once 'includes/sidebar.php';
    require_once 'includes/navbar.php';
    echo '<div class="alert alert-info">No complaints found to generate reports.</div>';
    require_once 'includes/footer.php';
    exit;
}

// If no ID provided, take the first one
if ($current_id <= 0 || !in_array($current_id, $all_ids)) {
    $current_id = $all_ids[0];
}

// Find neighbors for navigation
$current_index = array_search($current_id, $all_ids);
$prev_id = ($current_index > 0) ? $all_ids[$current_index - 1] : null;
$next_id = ($current_index < $total_cases - 1) ? $all_ids[$current_index + 1] : null;

// Get complaint details
$stmt = $pdo->prepare("
    SELECT c.*, s.username as student_name, s.email as student_email, u.username as assigned_to_name
    FROM complaints c 
    JOIN users s ON c.student_id = s.id 
    LEFT JOIN users u ON c.assigned_to = u.id 
    WHERE c.id = ?
");
$stmt->execute([$current_id]);
$complaint = $stmt->fetch();

$categories = getComplaintCategories();

require_once 'includes/sidebar.php';
require_once 'includes/navbar.php';
?>

<div class="no-print mb-4">
    <div class="card border-0 shadow-sm">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div class="d-flex gap-2">
                <a href="reports.php" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back to Analytics
                </a>
                <div class="btn-group">
                    <?php if ($prev_id): ?>
                        <a href="?id=<?php echo $prev_id; ?>" class="btn btn-light border btn-sm">
                            <i class="fas fa-chevron-left me-1"></i> Previous Case
                        </a>
                    <?php else: ?>
                        <button class="btn btn-light border btn-sm" disabled><i class="fas fa-chevron-left me-1"></i> Previous Case</button>
                    <?php endif; ?>

                    <span class="btn btn-light border btn-sm disabled fw-bold">
                        Case <?php echo ($current_index + 1); ?> of <?php echo $total_cases; ?>
                    </span>

                    <?php if ($next_id): ?>
                        <a href="?id=<?php echo $next_id; ?>" class="btn btn-light border btn-sm">
                            Next Case <i class="fas fa-chevron-right ms-1"></i>
                        </a>
                    <?php else: ?>
                        <button class="btn btn-light border btn-sm" disabled>Next Case <i class="fas fa-chevron-right ms-1"></i></button>
                    <?php endif; ?>
                </div>
            </div>
            <button onclick="window.print()" class="btn btn-primary btn-sm px-4">
                <i class="fas fa-print me-2"></i> Print Report
            </button>
        </div>
    </div>
</div>

<div class="report-document bg-white shadow-sm p-5 mx-auto" style="max-width: 900px; min-height: 1100px; border: 1px solid #eee;">
    <!-- Report Header -->
    <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-5">
        <div>
            <h2 class="fw-bold text-primary mb-1">Case Investigation Report</h2>
            <p class="text-muted mb-0">online student complaint system Official Documentation</p>
        </div>
        <div class="text-end">
            <div class="fw-bold fs-5">#CASE-<?php echo str_pad($complaint['id'], 6, '0', STR_PAD_LEFT); ?></div>
            <div class="text-muted small">Generated on: <?php echo date('F j, Y, g:i a'); ?></div>
        </div>
    </div>

    <!-- Case Identity -->
    <div class="row mb-5">
        <div class="col-6">
            <h6 class="text-muted text-uppercase fw-bold small mb-3">Complainant Information</h6>
            <div class="p-3 bg-light rounded-3">
                <div class="fw-bold fs-6"><?php echo htmlspecialchars($complaint['student_name']); ?></div>
                <div class="text-muted"><?php echo htmlspecialchars($complaint['student_email']); ?></div>
                <div class="small mt-1 text-muted">Student ID: #<?php echo $complaint['student_id']; ?></div>
            </div>
        </div>
        <div class="col-6">
            <h6 class="text-muted text-uppercase fw-bold small mb-3">Case Metadata</h6>
            <div class="p-3 bg-light rounded-3">
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted small">Category:</span>
                    <span class="fw-bold small"><?php echo isset($categories[$complaint['category']]) ? $categories[$complaint['category']] : ucfirst($complaint['category']); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted small">Submitted:</span>
                    <span class="fw-bold small"><?php echo formatDate($complaint['created_at']); ?></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">Status:</span>
                    <span class="fw-bold small text-uppercase"><?php echo str_replace('_', ' ', $complaint['status']); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Case Description -->
    <div class="mb-5">
        <h6 class="text-muted text-uppercase fw-bold small mb-3 border-bottom pb-2">Problem Statement / Description</h6>
        <div class="p-4 bg-white border rounded-3" style="font-size: 1.1rem; line-height: 1.8; min-height: 200px;">
            <?php echo nl2br(htmlspecialchars($complaint['description'])); ?>
        </div>
    </div>

    <!-- Resolution & Handling -->
    <div class="row mb-5">
        <div class="col-12">
            <h6 class="text-muted text-uppercase fw-bold small mb-3 border-bottom pb-2">Investigation & Resolution Details</h6>
            <div class="p-3 border rounded-3 bg-white">
                <div class="row mb-3">
                    <div class="col-4 text-muted small">Handled By:</div>
                    <div class="col-8 fw-bold"><?php echo $complaint['assigned_to_name'] ?: 'Pending Assignment'; ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-4 text-muted small">Official Remarks:</div>
                    <div class="col-8">
                        <?php if ($complaint['admin_remarks']): ?>
                            <div class="fst-italic text-dark"><?php echo nl2br(htmlspecialchars($complaint['admin_remarks'])); ?></div>
                        <?php else: ?>
                            <span class="text-muted italic small">No official remarks recorded yet.</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-4 text-muted small">Resolution Date:</div>
                    <div class="col-8 fw-bold">
                        <?php echo $complaint['status'] == 'resolved' ? formatDate($complaint['updated_at']) : 'In Progress'; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Institutional Footer -->
    <div class="mt-auto pt-5 border-top">
        <div class="row">
            <div class="col-6">
                <div class="border-bottom mb-2" style="height: 60px;"></div>
                <div class="small text-muted">Authorized Signature</div>
            </div>
            <div class="col-6 text-end">
                <div class="border-bottom mb-2" style="height: 60px;"></div>
                <div class="small text-muted">Institutional Seal / Stamp</div>
            </div>
        </div>
        <div class="text-center mt-5">
            <p class="small text-muted">This is an electronically generated report for the online student complaint system. Authenticity can be verified via the system portal using Case ID #<?php echo $complaint['id']; ?>.</p>
        </div>
    </div>
</div>

<style>
    @media print {
        .no-print { display: none !important; }
        .report-document { 
            box-shadow: none !important; 
            border: none !important; 
            padding: 0 !important;
            margin: 0 !important;
            max-width: 100% !important;
        }
        body { background: white !important; }
        .sidebar, .navbar { display: none !important; }
        .content-wrapper { margin-left: 0 !important; padding: 0 !important; }
    }
    .report-document {
        font-family: 'Outfit', sans-serif;
        color: #1a202c;
    }
    .uppercase { text-transform: uppercase; }
</style>

<?php require_once 'includes/footer.php'; ?>
