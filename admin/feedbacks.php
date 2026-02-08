<?php
$page_title = 'Feedbacks';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';
require_once 'includes/navbar.php';

// Fetch feedbacks
$stmt = $pdo->query("SELECT * FROM contacts ORDER BY created_at DESC");
$feedbacks = $stmt->fetchAll();
?>

<div class="card shadow-sm border-0">
    <div class="card-header border-0 bg-transparent pt-4 px-4 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold">Visitor Feedbacks</h5>
        <span class="badge bg-primary rounded-pill"><?php echo count($feedbacks); ?> Total</span>
    </div>
    <div class="card-body p-4">
        <?php if (count($feedbacks) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th>Date</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($feedbacks as $fb): ?>
                            <tr>
                                <td style="white-space: nowrap;"><span class="text-muted small"><?php echo formatDateTime($fb['created_at']); ?></span></td>
                                <td><h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($fb['name']); ?></h6></td>
                                <td><a href="mailto:<?php echo htmlspecialchars($fb['email']); ?>" class="text-decoration-none small"><?php echo htmlspecialchars($fb['email']); ?></a></td>
                                <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($fb['subject']); ?></span></td>
                                <td>
                                    <p class="mb-0 small text-muted" style="max-width: 400px;"><?php echo htmlspecialchars($fb['message']); ?></p>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <div class="mb-3 text-muted opacity-25">
                    <i class="fas fa-comments fa-3x"></i>
                </div>
                <p class="text-muted mb-0">No feedbacks received yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
