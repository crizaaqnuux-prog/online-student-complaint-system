<?php
$page_title = 'Manage Site Images';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';
require_once 'includes/navbar.php';

$success = '';
$error = '';

// Ensure the site_settings table exists
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS site_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(50) UNIQUE NOT NULL,
        setting_value TEXT NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
} catch (PDOException $e) {
    $error = "Migration Error: Could not create settings table.";
}

// Allowed settings
$allowed_keys = ['about_image'];

// Handle Image Upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_image'])) {
    $key = $_POST['setting_key'];
    
    if (in_array($key, $allowed_keys)) {
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
            $file_type = $_FILES['image']['type'];
            
            if (in_array($file_type, $allowed_types)) {
                $upload_dir = '../assets/images/';
                $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $file_name = $key . '_' . time() . '.' . $file_extension;
                $target_path = $upload_dir . $file_name;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                    // Check if setting already exists
                    $stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
                    $stmt->execute([$key]);
                    $old_image = $stmt->fetchColumn();
                    
                    if ($old_image && file_exists($upload_dir . $old_image)) {
                        unlink($upload_dir . $old_image); // Delete old file
                    }
                    
                    // Insert or Update database
                    $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                    if ($stmt->execute([$key, $file_name, $file_name])) {
                        $success = "Image uploaded and updated successfully.";
                    } else {
                        $error = "Database update failed.";
                    }
                } else {
                    $error = "Failed to move uploaded file.";
                }
            } else {
                $error = "Invalid file type. Only JPG, PNG, and WEBP are allowed.";
            }
        } else {
            $error = "Please select a valid image file.";
        }
    } else {
        $error = "Invalid setting key.";
    }
}

// Handle Image Delete
if (isset($_GET['delete'])) {
    $key = $_GET['delete'];
    if (in_array($key, $allowed_keys)) {
        $stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $file_name = $stmt->fetchColumn();
        
        if ($file_name) {
            $upload_dir = '../assets/images/';
            if (file_exists($upload_dir . $file_name)) {
                unlink($upload_dir . $file_name);
            }
            $stmt = $pdo->prepare("DELETE FROM site_settings WHERE setting_key = ?");
            $stmt->execute([$key]);
            $success = "Image deleted successfully.";
        }
    }
}

// Fetch current settings
$settings = [];
$stmt = $pdo->query("SELECT * FROM site_settings");
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>

<div class="fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-gray-800">Manage Site Images</h2>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <?php foreach ($allowed_keys as $key): ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100 border-0 shadow-sm overflow-hidden">
                    <div class="card-header bg-white py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><?php echo ucwords(str_replace('_', ' ', $key)); ?></h6>
                    </div>
                    <div class="card-body text-center p-0">
                        <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px; overflow: hidden;">
                            <?php if (isset($settings[$key])): ?>
                                <img src="../assets/images/<?php echo $settings[$key]; ?>" class="img-fluid" style="object-fit: cover; width: 100%; height: 100%;">
                            <?php else: ?>
                                <div class="text-muted">
                                    <i class="fas fa-image fa-3x mb-2"></i>
                                    <p class="small mb-0">No image uploaded</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="p-4">
                            <form action="" method="POST" enctype="multipart/form-data" class="mb-3">
                                <input type="hidden" name="setting_key" value="<?php echo $key; ?>">
                                <div class="mb-3">
                                    <input type="file" name="image" class="form-control form-control-sm" required>
                                </div>
                                <button type="submit" name="upload_image" class="btn btn-primary btn-sm w-100">
                                    <i class="fas fa-upload me-1"></i> Upload/Replace
                                </button>
                            </form>
                            <?php if (isset($settings[$key])): ?>
                                <a href="?delete=<?php echo $key; ?>" class="btn btn-outline-danger btn-sm w-100" onclick="return confirm('Are you sure you want to delete this image?')">
                                    <i class="fas fa-trash-alt me-1"></i> Delete Image
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
