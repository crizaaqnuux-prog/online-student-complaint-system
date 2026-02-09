<?php
/**
 * Cabasho Arday - Student Complaint Management System
 * Configuration File
 */

// Database Configuration (MySQL)
// Using getenv() for dynamic environment variables on live servers
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'student_complaints');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

// Site Configuration
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$current_dir = basename(dirname(__DIR__));

// Use relative paths where possible, but define SITE_URL for absolute requirements
define('SITE_URL', $protocol . "://" . $host . (empty($current_dir) || $current_dir == 'htdocs' ? '' : "/" . $current_dir));
define('SITE_NAME', 'Horn of Africa University SCMS');

// Session Configuration
if (session_status() === PHP_SESSION_NONE) {
    // Secure session settings
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Lax');
    if ($protocol === 'https') {
        ini_set('session.cookie_secure', 1);
    }
    session_start();
}

// Database Connection (MySQL)
try {
    // Try connecting with the database name first
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    // Secure PDO connection
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch(PDOException $e) {
    // If the database doesn't exist, try connecting without it (for setup)
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";charset=utf8mb4", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    } catch(PDOException $e2) {
        // Use branded error message
        die("Cabasho Arday Connection Error: Please contact system administrator. " . (getenv('APP_DEBUG') ? $e2->getMessage() : ''));
    }
}

// Helper function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Helper function to check user role
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

// Helper function to redirect
function redirect($url) {
    header("Location: " . $url);
    exit();
}
?>