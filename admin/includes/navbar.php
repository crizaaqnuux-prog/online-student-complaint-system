<?php
$page_title_display = $page_title ?? 'Dashboard Overview';
?>
<!-- Main content -->
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
    <!-- Top Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white py-3 px-4 rounded-4 mt-3 mb-4 shadow-sm border">
        <div class="container-fluid p-0">
            <div class="d-flex align-items-center">
                <button id="sidebarToggle" class="me-3">
                    <i class="fas fa-bars"></i>
                </button>
                <h4 class="mb-0 fw-bold"><?php echo $page_title_display; ?></h4>
            </div>
            <div class="d-flex align-items-center">
                <div class="me-4 position-relative">
                    <i class="fas fa-bell text-muted fs-5"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.5rem;">
                        3
                    </span>
                </div>
                <div class="d-flex align-items-center border-start ps-4">
                    <div class="text-end me-3 d-none d-md-block">
                        <p class="mb-0 fw-bold small text-dark"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
                        <p class="mb-0 text-muted extra-small" style="font-size: 0.7rem;">System Administrator</p>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 40px; height: 40px; background: var(--primary-gradient);">
                        <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                    </div>
                </div>
            </div>
        </div>
    </nav>
