<?php
require_once 'includes/config.php';
try {
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = 'admin@example.com'");
    $stmt->execute([$password]);
    if ($stmt->rowCount() > 0) {
        echo "Admin password reset successfully to: admin123\n";
    } else {
        echo "Admin account with email 'admin@example.com' not found or password already matches.\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
