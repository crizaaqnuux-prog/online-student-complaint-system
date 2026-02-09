<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visitor Feedbacks - Staff Portal</title>
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

    // Fetch feedbacks
    $stmt = $pdo->query("SELECT * FROM contacts ORDER BY created_at DESC");
    $feedbacks = $stmt->fetchAll();
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
                            <a class="nav-link" href="assigned_complaints.php">
                                <i class="fas fa-list-alt"></i> Manage Complaints
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="feedbacks.php">
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
                    <h1 class="h2">Visitor Feedbacks</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <span class="badge bg-primary rounded-pill"><?php echo count($feedbacks); ?> Total</span>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
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
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
