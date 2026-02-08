<?php
require_once 'includes/config.php';
try {
    $password = password_hash('staff123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = 'staff@example.com'");
    $stmt->execute([$password]);
    if ($stmt->rowCount() > 0) {
        echo "Staff password reset successfully to: staff123\n";
    } else {
        echo "Staff account with email 'staff@example.com' not found or password already matches.\n";
        // Check if it exists at all
        $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = 'staff@example.com'");
        $check->execute();
        if ($check->fetchColumn() == 0) {
            echo "Email 'staff@example.com' does not exist in the database. Creating it...\n";
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute(['staff1', 'staff@example.com', $password, 'staff']);
            echo "Staff account created successfully.\n";
        }
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
