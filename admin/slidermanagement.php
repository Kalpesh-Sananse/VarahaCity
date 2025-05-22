<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
?>
<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$db = new mysqli('localhost', 'root', '', 'mydb');
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}
// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_slider'])) {
        // Add new slider
        $title = $db->real_escape_string($_POST['title']);
        $description = $db->real_escape_string($_POST['description']);
        $link_url = $db->real_escape_string($_POST['link_url']);
        $display_order = intval($_POST['display_order']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Handle image upload
        $image_path = '';
        if (isset($_FILES['slider_image']) && $_FILES['slider_image']['error'] == 0) {
            $target_dir = "../uploads/sliders/";
            if (!file_exists($target_dir)) {
                if (!mkdir($target_dir, 0777, true)) {  // Changed permission to 0777 for testing
                    $_SESSION['error'] = "Failed to create upload directory. Please check permissions.";
                    header("Location: slidermanagement.php");  // Fixed redirect path
                    exit();
                }
            }
            
            $file_ext = pathinfo($_FILES['slider_image']['name'], PATHINFO_EXTENSION);
            $file_name = 'slider_' . time() . '.' . $file_ext;
            $target_file = $target_dir . $file_name;
            
            if (move_uploaded_file($_FILES['slider_image']['tmp_name'], $target_file)) {
                $image_path = $target_dir . $file_name;  // Store the relative path
            } else {
                $_SESSION['error'] = "Failed to move uploaded file. Error: " . $_FILES['slider_image']['error'];
                header("Location: slidermanagement.php");  // Fixed redirect path
                exit();
            }
        }
        
        if ($image_path) {
            $stmt = $db->prepare("INSERT INTO sliders (image_path, link_url, title, description, display_order, is_active) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssii", $image_path, $link_url, $title, $description, $display_order, $is_active);
            if ($stmt->execute()) {
                $_SESSION['message'] = "Slider added successfully!";
            } else {
                $_SESSION['error'] = "Failed to add slider to database: " . $db->error;
                // Delete the uploaded file if DB operation failed
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }
        } else {
            $_SESSION['error'] = "Failed to upload image.";
        }
        
        header("Location: slidermanagement.php");  // Fixed redirect path
        exit();
    } elseif (isset($_POST['update_slider'])) {
        // Update existing slider
        $id = intval($_POST['slider_id']);
        $title = $db->real_escape_string($_POST['title']);
        $description = $db->real_escape_string($_POST['description']);
        $link_url = $db->real_escape_string($_POST['link_url']);
        $display_order = intval($_POST['display_order']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Check if new image is uploaded
        if (isset($_FILES['slider_image']) && $_FILES['slider_image']['error'] == 0) {
            $target_dir = "../uploads/sliders/";
            $file_ext = pathinfo($_FILES['slider_image']['name'], PATHINFO_EXTENSION);
            $file_name = 'slider_' . time() . '.' . $file_ext;
            $target_file = $target_dir . $file_name;
            
            if (move_uploaded_file($_FILES['slider_image']['tmp_name'], $target_file)) {
                // Delete old image
                $result = $db->query("SELECT image_path FROM sliders WHERE id = $id");
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    if (!empty($row['image_path']) && file_exists($row['image_path'])) {
                        unlink($row['image_path']);
                    }
                }
                
                $image_path = $target_dir . $file_name; // Store the relative path
                $stmt = $db->prepare("UPDATE sliders SET image_path = ?, link_url = ?, title = ?, description = ?, display_order = ?, is_active = ? WHERE id = ?");
                $stmt->bind_param("ssssiii", $image_path, $link_url, $title, $description, $display_order, $is_active, $id);
            }
        } else {
            $stmt = $db->prepare("UPDATE sliders SET link_url = ?, title = ?, description = ?, display_order = ?, is_active = ? WHERE id = ?");
            $stmt->bind_param("sssiii", $link_url, $title, $description, $display_order, $is_active, $id);
        }
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Slider updated successfully!";
        } else {
            $_SESSION['error'] = "Failed to update slider: " . $db->error;
        }
        
        header("Location: slidermanagement.php");  // Fixed redirect path
        exit();
    }
}

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Get image path first to delete the file
    $result = $db->query("SELECT image_path FROM sliders WHERE id = $id");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (!empty($row['image_path'])) {
            // Check if file exists before attempting to delete
            if (file_exists($row['image_path'])) {
                if (!unlink($row['image_path'])) {
                    $_SESSION['error'] = "Failed to delete slider image file.";
                } 
            }
        }
    }
    
    // Delete from database
    if ($db->query("DELETE FROM sliders WHERE id = $id")) {
        $_SESSION['message'] = "Slider deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete slider from database: " . $db->error;
    }
    
    // Redirect to prevent form resubmission
    header("Location: slidermanagement.php");
    exit();
}

// Fetch all sliders
$sliders = $db->query("SELECT * FROM sliders ORDER BY display_order ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Slider Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css">
    <style>
        /* CSS remains the same */
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --accent-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
        }
        
        .admin-header {
            background-color: var(--primary-color);
            color: white;
            padding: 1rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar {
            background-color: var(--secondary-color);
            color: white;
            min-height: calc(100vh - 56px);
            padding: 0;
        }
        
        .sidebar .nav-link {
            color: var(--light-color);
            border-radius: 0;
            padding: 12px 20px;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .sidebar .nav-link i {
            margin-right: 10px;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            transition: transform 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            padding: 15px 20px;
        }
        
        .btn-primary {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
        }
        
        .btn-primary:hover {
            background-color: #c0392b;
            border-color: #c0392b;
        }
        
        .table th {
            background-color: var(--light-color);
        }
        
        .slider-preview {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-top: 30px;
        }
        
        .slider-container {
            position: relative;
            margin: 0 auto;
            max-width: 1200px;
        }
        
        .slider-item {
            position: relative;
            height: 400px;
            background-size: cover;
            background-position: center;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .slider-content {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0, 0, 0, 0.7));
            color: white;
            padding: 20px;
        }
        
        .slick-dots {
            bottom: 20px;
        }
        
        .slick-dots li button:before {
            color: white;
            font-size: 12px;
        }
        
        .slick-prev:before, .slick-next:before {
            color: var(--accent-color);
            font-size: 30px;
        }
        
        .slick-prev {
            left: 20px;
            z-index: 1;
        }
        
        .slick-next {
            right: 20px;
        }
        
        .action-btns .btn {
            padding: 5px 10px;
            font-size: 12px;
            margin-right: 5px;
        }
        
        .modal-content {
            border-radius: 10px;
        }
        
        .preview-image {
            max-height: 200px;
            object-fit: contain;
            margin-bottom: 15px;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-active {
            background-color: #d4edda;
            color: #155724;
        }
        
        .badge-inactive {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1><i class="fas fa-sliders-h"></i> Slider Management</h1>
                </div>
                <div class="col-md-6 text-end">
                    <a href="dashboardadmin.php" class="btn btn-outline-light me-2"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                    <a href="logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </div>
    </header>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 col-lg-2 sidebar p-0">
                <nav class="nav flex-column">
                    <a class="nav-link" href="dashboardadmin.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                    <a class="nav-link active" href="slidermanagement.php"><i class="fas fa-sliders-h"></i> Sliders</a>
                    <!-- Updated links to match your file structure -->
                    <a class="nav-link" href="category_details.php"><i class="fas fa-box"></i> Categories</a>
                    <a class="nav-link" href="subcategory_details.php"><i class="fas fa-sitemap"></i> Subcategories</a>
                    <a class="nav-link" href="view_properties.php"><i class="fas fa-home"></i> Properties</a>
                </nav>
            </div>

            <div class="col-md-9 col-lg-10 p-4">
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $_SESSION['message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $_SESSION['error']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Add New Slider</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="slider_image" class="form-label">Slider Image</label>
                                        <input type="file" class="form-control" id="slider_image" name="slider_image" required>
                                        <small class="text-muted">Recommended size: 1200x400 pixels</small>
                                    </div>
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Title</label>
                                        <input type="text" class="form-control" id="title" name="title">
                                    </div>
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="link_url" class="form-label">Link URL</label>
                                        <input type="url" class="form-control" id="link_url" name="link_url" required placeholder="https://example.com">
                                    </div>
                                    <div class="mb-3">
                                        <label for="display_order" class="form-label">Display Order</label>
                                        <input type="number" class="form-control" id="display_order" name="display_order" value="0">
                                    </div>
                                    <div class="mb-3 form-check">
                                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" checked>
                                        <label class="form-check-label" for="is_active">Active</label>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" name="add_slider" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save Slider</button>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Current Sliders</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Image</th>
                                        <th>Title</th>
                                        <th>Link</th>
                                        <th>Order</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($sliders->num_rows > 0): ?>
                                        <?php while ($slider = $sliders->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= $slider['id']; ?></td>
                                                <td>
                                                    <img src="<?= $slider['image_path']; ?>" alt="Slider Image" style="max-width: 100px; max-height: 50px; object-fit: cover;">
                                                </td>
                                                <td><?= htmlspecialchars($slider['title']); ?></td>
                                                <td>
                                                    <a href="<?= htmlspecialchars($slider['link_url']); ?>" target="_blank" class="text-truncate" style="max-width: 150px; display: inline-block;">
                                                        <?= htmlspecialchars($slider['link_url']); ?>
                                                    </a>
                                                </td>
                                                <td><?= $slider['display_order']; ?></td>
                                                <td>
                                                    <span class="status-badge <?= $slider['is_active'] ? 'badge-active' : 'badge-inactive'; ?>">
                                                        <?= $slider['is_active'] ? 'Active' : 'Inactive'; ?>
                                                    </span>
                                                </td>
                                                <td class="action-btns">
                                                    <button class="btn btn-sm btn-primary edit-slider" data-id="<?= $slider['id']; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <a href="slidermanagement.php?action=delete&id=<?= $slider['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this slider?');">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center">No sliders found. Add your first slider above.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Slider Preview Section -->
                <div class="slider-preview">
                    <h4 class="mb-4"><i class="fas fa-eye me-2"></i>Slider Preview</h4>
                    <div class="slider-container">
                        <div class="slider" id="previewSlider">
                            <?php 
                            // Fetch active sliders for preview
                            $preview_sliders = $db->query("SELECT * FROM sliders WHERE is_active = 1 ORDER BY display_order ASC");
                            if ($preview_sliders->num_rows > 0):
                                while ($slider = $preview_sliders->fetch_assoc()): ?>
                                    <div class="slider-item" style="background-image: url('<?= $slider['image_path']; ?>');">
                                        <div class="slider-content">
                                            <?php if ($slider['title']): ?>
                                                <h3><?= htmlspecialchars($slider['title']); ?></h3>
                                            <?php endif; ?>
                                            <?php if ($slider['description']): ?>
                                                <p><?= htmlspecialchars($slider['description']); ?></p>
                                            <?php endif; ?>
                                            <a href="<?= htmlspecialchars($slider['link_url']); ?>" class="btn btn-primary" target="_blank">Learn More</a>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="text-center py-5 bg-light">
                                    <p class="text-muted">No active sliders to display. Add sliders and mark them as active to see them here.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Slider Modal -->
    <div class="modal fade" id="editSliderModal" tabindex="-1" aria-labelledby="editSliderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editSliderModalLabel"><i class="fas fa-edit me-2"></i>Edit Slider</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" enctype="multipart/form-data" id="editSliderForm">
                    <div class="modal-body">
                        <input type="hidden" name="slider_id" id="edit_slider_id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_slider_image" class="form-label">Slider Image</label>
                                    <input type="file" class="form-control" id="edit_slider_image" name="slider_image">
                                    <small class="text-muted">Leave blank to keep current image</small>
                                    <div class="mt-2">
                                        <img id="current_slider_image" src="" alt="Current Image" class="preview-image img-fluid">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_title" class="form-label">Title</label>
                                    <input type="text" class="form-control" id="edit_title" name="title">
                                </div>
                                <div class="mb-3">
                                    <label for="edit_description" class="form-label">Description</label>
                                    <textarea class="form-control" id="edit_description" name="description" rows="2"></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_link_url" class="form-label">Link URL</label>
                                    <input type="url" class="form-control" id="edit_link_url" name="link_url" required placeholder="https://example.com">
                                </div>
                                <div class="mb-3">
                                    <label for="edit_display_order" class="form-label">Display Order</label>
                                    <input type="number" class="form-control" id="edit_display_order" name="display_order" value="0">
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="edit_is_active" name="is_active">
                                    <label class="form-check-label" for="edit_is_active">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="update_slider" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize slider preview
            $('#previewSlider').slick({
                dots: true,
                infinite: true,
                speed: 500,
                fade: true,
                cssEase: 'linear',
                autoplay: true,
                autoplaySpeed: 3000,
                arrows: true
            });
            
            // Modified to open links in a new tab instead of trying to load in iframe
            $('.slider-link').on('click', function(e) {
                // We don't prevent default so links open normally
                // The target="_blank" attribute will handle opening in a new tab
            });
            
            // Edit slider modal
            $('.edit-slider').on('click', function() {
                var sliderId = $(this).data('id');
                
                // For demonstration, use a simple AJAX call to get data
                // In a real implementation, you'd need to create a get_slider.php file
                $.ajax({
                    url: 'get_slider.php',
                    type: 'GET',
                    data: { id: sliderId },
                    dataType: 'json',
                    success: function(response) {
                        $('#edit_slider_id').val(response.id);
                        $('#edit_title').val(response.title);
                        $('#edit_description').val(response.description);
                        $('#edit_link_url').val(response.link_url);
                        $('#edit_display_order').val(response.display_order);
                        $('#edit_is_active').prop('checked', response.is_active == 1);
                        $('#current_slider_image').attr('src', response.image_path);
                        
                        $('#editSliderModal').modal('show');
                    },
                    error: function(xhr, status, error) {
                        alert('Error fetching slider data: ' + error + '. You may need to create the get_slider.php file.');
                    }
                });
            });
        });
    </script>
</body>
</html>