<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

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
                if (!mkdir($target_dir, 0777, true)) {
                    $_SESSION['error'] = "Failed to create upload directory. Please check permissions.";
                    header("Location: slidermanagement.php");
                    exit();
                }
            }
            
            $file_ext = pathinfo($_FILES['slider_image']['name'], PATHINFO_EXTENSION);
            $file_name = 'slider_' . time() . '.' . $file_ext;
            $target_file = $target_dir . $file_name;
            
            if (move_uploaded_file($_FILES['slider_image']['tmp_name'], $target_file)) {
                $image_path = $target_dir . $file_name;
            } else {
                $_SESSION['error'] = "Failed to move uploaded file. Error: " . $_FILES['slider_image']['error'];
                header("Location: slidermanagement.php");
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
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }
        } else {
            $_SESSION['error'] = "Failed to upload image.";
        }
        
        header("Location: slidermanagement.php");
        exit();
    } elseif (isset($_POST['update_slider'])) {
        // Update existing slider
        $id = intval($_POST['slider_id']);
        $title = $db->real_escape_string($_POST['title']);
        $description = $db->real_escape_string($_POST['description']);
        $link_url = $db->real_escape_string($_POST['link_url']);
        $display_order = intval($_POST['display_order']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        if (isset($_FILES['slider_image']) && $_FILES['slider_image']['error'] == 0) {
            $target_dir = "../uploads/sliders/";
            $file_ext = pathinfo($_FILES['slider_image']['name'], PATHINFO_EXTENSION);
            $file_name = 'slider_' . time() . '.' . $file_ext;
            $target_file = $target_dir . $file_name;
            
            if (move_uploaded_file($_FILES['slider_image']['tmp_name'], $target_file)) {
                $result = $db->query("SELECT image_path FROM sliders WHERE id = $id");
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    if (!empty($row['image_path']) && file_exists($row['image_path'])) {
                        unlink($row['image_path']);
                    }
                }
                
                $image_path = $target_dir . $file_name;
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
        
        header("Location: slidermanagement.php");
        exit();
    }
}

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $result = $db->query("SELECT image_path FROM sliders WHERE id = $id");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (!empty($row['image_path']) && file_exists($row['image_path'])) {
            unlink($row['image_path']);
        }
    }
    
    if ($db->query("DELETE FROM sliders WHERE id = $id")) {
        $_SESSION['message'] = "Slider deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete slider: " . $db->error;
    }
    
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
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Slick Carousel -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #2980b9, #6dd5fa);
            --primary-color: #2980b9;
            --secondary-color: #6dd5fa;
            --text-color: #2c3e50;
            --light-bg: #f4f7fc;
            --border-radius: 12px;
            --box-shadow: 0 4px 20px rgba(41, 128, 185, 0.1);
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--light-bg);
            color: var(--text-color);
            line-height: 1.6;
        }

    
        .card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 25px;
            overflow: hidden;
            background: white;
        }

        .card-header {
            background: var(--primary-gradient);
            color: white;
            padding: 20px;
            border: none;
        }

        .card-body {
            padding: 25px;
        }

        .form-label {
            font-weight: 500;
            color: var(--primary-color);
            margin-bottom: 8px;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid rgba(41, 128, 185, 0.2);
            padding: 12px;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(41, 128, 185, 0.1);
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 500;
            transition: var(--transition);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(41, 128, 185, 0.3);
        }

        .table {
            margin: 0;
        }

        .table th {
            background: rgba(41, 128, 185, 0.1);
            color: var(--primary-color);
            font-weight: 600;
            border: none;
            padding: 15px;
        }

        .table td {
            vertical-align: middle;
            border-color: rgba(41, 128, 185, 0.1);
            padding: 15px;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .badge-active {
            background: rgba(41, 128, 185, 0.1);
            color: var(--primary-color);
        }

        .badge-inactive {
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
        }

        .alert {
            border-radius: var(--border-radius);
            border: none;
            padding: 15px 20px;
        }

        .alert-success {
            background: rgba(41, 128, 185, 0.1);
            color: var(--primary-color);
            border-left: 4px solid var(--primary-color);
        }

        .alert-danger {
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
            border-left: 4px solid #e74c3c;
        }

        .modal-content {
            border-radius: var(--border-radius);
            border: none;
        }

        .modal-header {
            background: var(--primary-gradient);
            color: white;
            border: none;
        }

        .modal-body {
            padding: 25px;
        }

        .preview-image {
            max-height: 200px;
            object-fit: cover;
            border-radius: 8px;
            margin-top: 10px;
        }
      

/* Update the layout for smaller screens */
@media (max-width: 768px) {
    .time-info .container {
        flex-direction: column;
        align-items: flex-start;
        padding: 10px 15px;
    }
    
    .divider {
        display: none;
    }
}

        @media (max-width: 768px) {
            .datetime-display {
                flex-direction: column;
                align-items: flex-start;
            }

            .card-body {
                padding: 15px;
            }

            .table-responsive {
                border-radius: var(--border-radius);
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .card {
            animation: fadeIn 0.5s ease forwards;
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>


<div class="container mt-4">
    <!-- DateTime Display -->


    <!-- Messages -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?= $_SESSION['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= $_SESSION['error']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Add New Slider Card -->
    <div class="card">
        <div class="card-header">
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
                <button type="submit" name="add_slider" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Save Slider
                </button>
            </form>
        </div>
    </div>

    <!-- Current Sliders Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Current Sliders</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
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
                                        <img src="<?= $slider['image_path']; ?>" alt="Slider Image" 
                                             style="max-width: 100px; max-height: 50px; object-fit: cover; border-radius: 4px;">
                                    </td>
                                    <td><?= htmlspecialchars($slider['title']); ?></td>
                                    <td>
                                        <a href="<?= htmlspecialchars($slider['link_url']); ?>" target="_blank"
                                           class="text-truncate" style="max-width: 150px; display: inline-block;">
                                            <?= htmlspecialchars($slider['link_url']); ?>
                                        </a>
                                    </td>
                                    <td><?= $slider['display_order']; ?></td>
                                    <td>
                                        <span class="status-badge <?= $slider['is_active'] ? 'badge-active' : 'badge-inactive'; ?>">
                                            <?= $slider['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary edit-slider" data-id="<?= $slider['id']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="slidermanagement.php?action=delete&id=<?= $slider['id']; ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Are you sure you want to delete this slider?');">
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
</div>

<!-- Edit Slider Modal -->
<div class="modal fade" id="editSliderModal" tabindex="-1" aria-labelledby="editSliderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editSliderModalLabel">
                    <i class="fas fa-edit me-2"></i>Edit Slider
                </h5>
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
                                <input type="url" class="form-control" id="edit_link_url" name="link_url" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_display_order" class="form-label">Display Order</label>
                                <input type="number" class="form-control" id="edit_display_order" name="display_order">
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
                    <button type="submit" name="update_slider" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script>

<script>
// Update DateTime function
function updateDateTime() {
    const now = new Date();
    const year = now.getUTCFullYear();
    const month = String(now.getUTCMonth() + 1).padStart(2, '0');
    const day = String(now.getUTCDate()).padStart(2, '0');
    const hours = String(now.getUTCHours()).padStart(2, '0');
    const minutes = String(now.getUTCMinutes()).padStart(2, '0');
    const seconds = String(now.getUTCSeconds()).padStart(2, '0');
    
    const formattedDateTime = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
   
}

// Update time every second
updateDateTime();
setInterval(updateDateTime, 1000);

// Edit slider functionality
$('.edit-slider').on('click', function() {
    const sliderId = $(this).data('id');
    
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
            alert('Error fetching slider data. Please try again.');
        }
    });
});

// Auto-dismiss alerts after 5 seconds
setTimeout(function() {
    $('.alert').fadeOut('slow');
}, 5000);
</script>

</body>
</html>