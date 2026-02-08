<?php $current_page = basename($_SERVER['PHP_SELF']); ?>
<nav class="sidebar">
    <div class="position-sticky pt-3">
        <div class="text-center mb-2">
            <div class="d-inline-block p-2 rounded-circle mb-2" style="background: var(--primary-gradient);">
                <i class="fas fa-graduation-cap fa-lg text-white"></i>
            </div>
            <h6 class="text-white mb-0 fw-bold">SCMS</h6>
            <p class="text-muted" style="font-size: 0.7rem;">Admin</p>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="fas fa-home"></i> <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'complaints.php' ? 'active' : ''; ?>" href="complaints.php">
                    <i class="fas fa-exclamation-circle"></i> <span>Complaints</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'manage_users.php' ? 'active' : ''; ?>" href="manage_users.php">
                    <i class="fas fa-users-cog"></i> <span>Users</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'reports.php' ? 'active' : ''; ?>" href="reports.php">
                    <i class="fas fa-chart-bar"></i> <span>Reports</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'feedbacks.php' ? 'active' : ''; ?>" href="feedbacks.php">
                    <i class="fas fa-comment-dots"></i> <span>Feedbacks</span>
                </a>
            </li>
            <li class="nav-item mt-4">
                <a class="nav-link text-danger" href="../logout.php">
                    <i class="fas fa-power-off"></i> <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>
</nav>