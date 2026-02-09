<?php
require_once 'includes/config.php'; 
require_once 'includes/functions.php';

// Fetch dynamic about image
$about_image_path = "https://images.unsplash.com/photo-1541339907198-e08756ebafe3?auto=format&fit=crop&q=80&w=800";
try {
    $stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'about_image'");
    $stmt->execute();
    $db_about_image = $stmt->fetchColumn();
    if ($db_about_image) {
        $about_image_path = "assets/images/" . $db_about_image;
    }
} catch (PDOException $e) {
    // If table doesn't exist, fallback to default image
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        /* Navbar Styles from Index */
        .navbar {
            background: var(--primary-gradient);
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            padding: 1rem 0;
            transition: all 0.3s ease;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: white !important;
            text-decoration: none;
        }

        .navbar-nav .nav-link {
            color: white !important;
            font-weight: 500;
            margin: 0 10px;
            transition: all 0.3s ease;
        }

        .navbar-nav .nav-link:hover {
            color: #ffd700 !important;
        }

        .btn-auth {
            margin: 0 5px;
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-login {
            background: transparent;
            border: 2px solid white;
            color: white;
        }

        .btn-register {
            background: #ffd700;
            border: 2px solid #ffd700;
            color: #333;
        }

        /* Page Header */
        .page-header {
            background: var(--primary-gradient);
            padding: 100px 0 60px;
            color: white;
            text-align: center;
        }

        .page-header h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 20px;
            animation: fadeInUp 0.8s ease;
        }

        .breadcrumb-custom {
            display: flex;
            justify-content: center;
            list-style: none;
            padding: 0;
            animation: fadeInUp 0.8s ease 0.2s both;
        }

        .breadcrumb-item + .breadcrumb-item::before {
            content: "/";
            padding: 0 10px;
            color: rgba(255,255,255,0.6);
        }

        .breadcrumb-item a {
            color: white;
            text-decoration: none;
            opacity: 0.8;
            transition: opacity 0.3s;
        }

        .breadcrumb-item a:hover {
            opacity: 1;
        }

        /* About Detailed Content */
        .about-section {
            padding: 100px 0;
        }

        .about-image {
            position: relative;
            z-index: 1;
        }

        .about-image img {
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }

        .about-image::after {
            content: '';
            position: absolute;
            top: -20px;
            left: -20px;
            width: 100px;
            height: 100px;
            background: var(--primary-gradient);
            border-radius: 50%;
            z-index: -1;
            opacity: 0.2;
        }

        .about-text h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 30px;
            color: #333;
        }

        .about-text p {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 25px;
            line-height: 1.8;
        }

        /* Mission & Vision Cards */
        .mv-section {
            background: #f8f9fa;
            padding: 100px 0;
        }

        .card-mv {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            height: 100%;
            transition: transform 0.3s ease;
            border: none;
        }

        .card-mv:hover {
            transform: translateY(-10px);
        }

        .icon-box {
            width: 70px;
            height: 70px;
            background: var(--primary-gradient);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            margin-bottom: 25px;
        }

        /* Footer from Index */
        .footer {
            background: #333;
            color: white;
            padding: 50px 0 20px;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .footer-section h4 {
            margin-bottom: 20px;
            color: #ffd700;
        }

        .footer-section a {
            color: #ccc;
            text-decoration: none;
            display: block;
            margin-bottom: 8px;
            transition: color 0.3s ease;
        }

        .footer-section a:hover {
            color: #ffd700;
        }

        .footer-bottom {
            border-top: 1px solid #555;
            padding-top: 20px;
            text-align: center;
            color: #ccc;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .footer-content {
                flex-direction: column;
                gap: 30px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-graduation-cap me-2"></i><?php echo SITE_NAME; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto me-4">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#contact">Contact</a>
                    </li>
                </ul>
                <div class="d-flex">
                    <a href="login.php" class="btn-auth btn-login">Login</a>
                    <a href="register.php" class="btn-auth btn-register">Register</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <header class="page-header">
        <div class="container">
            <h1>About Our System</h1>
            <ul class="breadcrumb-custom">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item" style="color: white; opacity: 1;">About Us</li>
            </ul>
        </div>
    </header>

    <!-- Detailed About Section -->
    <section class="about-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="about-image mb-4 mb-lg-0">
                        <img src="<?php echo $about_image_path; ?>" alt="Horn of Africa University" class="img-fluid rounded-4 shadow-lg">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="about-text p-lg-4">
                        <h2>Revolutionizing Student Support</h2>
                        <p>The Online Student Complaint Management System is a futuristic initiative designed to automate and digitalize the traditional, paper-based grievance handling process. At Horn of Africa University, we believe that education thrive when student concerns are addressed with speed, transparency, and integrity.</p>
                        <p>Our platform serves as a secure bridge between the student body and university administration, ensuring that every submission is categorized, tracked in real-time, and resolved by the appropriate officials without administrative bottlenecks.</p>
                        <ul class="list-unstyled">
                            <li class="mb-3"><i class="fas fa-check-circle text-primary me-2"></i> <strong>Efficiency:</strong> Reducing processing time by 70%.</li>
                            <li class="mb-3"><i class="fas fa-check-circle text-primary me-2"></i> <strong>Accountability:</strong> Full audit logs for every complaint.</li>
                            <li class="mb-3"><i class="fas fa-check-circle text-primary me-2"></i> <strong>Scalability:</strong> Supporting thousands of students simultaneously.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Mission & Vision -->
    <section class="mv-section">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card-mv">
                        <div class="icon-box">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <h3>Our Mission</h3>
                        <p>To empower students by providing a seamless digital platform for expressing their concerns, fostering a culture of openness and continuous improvement within educational institutions.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-mv">
                        <div class="icon-box" style="background: var(--secondary-gradient);">
                            <i class="fas fa-eye"></i>
                        </div>
                        <h3>Our Vision</h3>
                        <p>To become the global standard for institutional transparency, transforming how educational entities interact with their most valuable asset—the students.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-mv">
                        <div class="icon-box" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                            <i class="fas fa-gem"></i>
                        </div>
                        <h3>Core Values</h3>
                        <p>Integrity, Confidentiality, and Responsiveness. We believe that a system is only as good as the trust students place in it.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4><?php echo SITE_NAME; ?></h4>
                    <p>Making student voices heard through efficient complaint management and transparent communication.</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <a href="index.php">Home</a>
                    <a href="about.php">About Us</a>
                    <a href="index.php#features">Features</a>
                    <a href="index.php#contact">Contact</a>
                </div>
                <div class="footer-section">
                    <h4>Support</h4>
                    <a href="#">Help Center</a>
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                </div>
                <div class="footer-section">
                    <h4>Connect</h4>
                    <div class="d-flex gap-3 mt-3">
                        <a href="#"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#"><i class="fab fa-linkedin fa-lg"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
