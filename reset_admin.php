<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

echo "<h2>Admin Password Reset Tool</h2>";

try {
    // Check if tables exist
    $pdo->exec("USE student_complaints");
    
    // Delete existing admin to be sure
    $pdo->exec("DELETE FROM users WHERE email = 'admin@example.com' OR username = 'admin'");
    
    // Create fresh admin
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute(['admin', 'admin@example.com', $adminPassword, 'admin']);
    
    echo "<p style='color: green;'>✅ Admin user has been reset successfully!</p>";
    echo "<p><strong>Email:</strong> admin@example.com</p>";
    echo "<p><strong>Password:</strong> admin123</p>";
    echo "<br><a href='login.php'>Go to Login Page</a>";

} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Make sure you have run <a href='setup_database.php'>setup_database.php</a> first.</p>";
}
?>
