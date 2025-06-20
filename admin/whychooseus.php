<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Set timezone and current date/time
date_default_timezone_set('UTC');
$current_datetime = '2025-05-27 17:27:36';
$current_user = 'Kalpesh-Sananse';

// Database connection
require_once '../includes/db_connection.php';

// Initialize messages
$success_message = '';
$error_message = '';

// Function to handle file uploads
function uploadFile($file, $target_dir) {
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return str_replace('..', '', $target_file);
    }
    return false;
}

// Handle Delete
if (isset($_POST['delete_id'])) {
    $id = $conn->real_escape_string($_POST['delete_id']);
    
    // Get existing files to delete
    $file_query = "SELECT section_image, icon FROM why_choose_us WHERE id = '$id'";
    $file_result = $conn->query($file_query);
    if ($file_result && $file_row = $file_result->fetch_assoc()) {
        // Delete physical files
        if (file_exists('../' . $file_row['section_image'])) unlink('../' . $file_row['section_image']);
        if (file_exists('../' . $file_row['icon'])) unlink('../' . $file_row['icon']);
    }
    
    // Delete record
    $delete_query = "DELETE FROM why_choose_us WHERE id = '$id'";
    if ($conn->query($delete_query)) {
        $success_message = "Feature deleted successfully!";
    } else {
        $error_message = "Error deleting feature: " . $conn->error;
    }
}

// Handle Edit
if (isset($_POST['edit_id'])) {
    $id = $conn->real_escape_string($_POST['edit_id']);
    $title = $conn->real_escape_string($_POST['edit_title']);
    $description = $conn->real_escape_string($_POST['edit_description']);
    $icon_bg_color = $conn->real_escape_string($_POST['edit_icon_bg_color']);
    
    $update_query = "UPDATE why_choose_us SET 
                    title = '$title', 
                    description = '$description', 
                    icon_bg_color = '$icon_bg_color'";

    // Handle section image update
    if (isset($_FILES['edit_section_image']) && $_FILES['edit_section_image']['error'] == 0) {
        $section_image = uploadFile($_FILES['edit_section_image'], '../uploads/');
        if ($section_image) {
            $update_query .= ", section_image = '$section_image'";
        }
    }

    // Handle icon update
    if (isset($_FILES['edit_icon']) && $_FILES['edit_icon']['error'] == 0) {
        $icon = uploadFile($_FILES['edit_icon'], '../uploads/');
        if ($icon) {
            $update_query .= ", icon = '$icon'";
        }
    }

    $update_query .= " WHERE id = '$id'";

    if ($conn->query($update_query)) {
        $success_message = "Feature updated successfully!";
    } else {
        $error_message = "Error updating feature: " . $conn->error;
    }
}

// Handle Add New Feature
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['delete_id']) && !isset($_POST['edit_id'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $icon_bg_color = $conn->real_escape_string($_POST['icon_bg_color']);
    
    // Initialize variables
    $section_image = '';
    $icon = '';
    
    // Handle section image upload
    if (isset($_FILES['section_image']) && $_FILES['section_image']['error'] == 0) {
        $section_image = uploadFile($_FILES['section_image'], '../uploads/');
        if (!$section_image) {
            $error_message = "Error uploading section image";
        }
    }
    
    // Handle icon upload
    if (isset($_FILES['icon']) && $_FILES['icon']['error'] == 0) {
        $icon = uploadFile($_FILES['icon'], '../uploads/');
        if (!$icon) {
            $error_message = "Error uploading icon";
        }
    }
    
    // Only proceed if both files were uploaded successfully
    if ($section_image && $icon && empty($error_message)) {
        $query = "INSERT INTO why_choose_us (section_image, title, description, icon, icon_bg_color, created_by) 
                  VALUES ('$section_image', '$title', '$description', '$icon', '$icon_bg_color', '$current_user')";
        
        if ($conn->query($query)) {
            $success_message = "Feature added successfully!";
        } else {
            $error_message = "Error: " . $conn->error;
        }
    }
}

// Fetch existing features
try {
    $features = $conn->query("SELECT * FROM why_choose_us ORDER BY id DESC");
    if (!$features) {
        throw new Exception($conn->error);
    }
} catch (Exception $e) {
    $error_message = "Error fetching features: " . $e->getMessage();
    $features = null;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Why Choose Us Section - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4a6cf7;
            --secondary-color: #6483f7;
            --background-color: #f8f9fa;
            --border-color: #e9ecef;
        }

        body {
            background-color: var(--background-color);
            font-family: 'Inter', sans-serif;
        }

        .admin-header {
            background: #fff;
            padding: 1rem 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.04);
            margin-bottom: 2rem;
        }

        .page-title {
            color: #333;
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-info i {
            color: var(--primary-color);
        }

        .timestamp {
            color: #666;
            font-size: 0.9rem;
        }

        .content-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.04);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .form-label {
            font-weight: 500;
            color: #444;
        }

        .preview-icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 10px 0;
            border: 2px solid var(--border-color);
        }

        .section-image-preview {
            max-width: 300px;
            margin: 10px 0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 0.5rem 1.5rem;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .table {
            border-collapse: separate;
            border-spacing: 0;
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            padding: 1rem;
        }

        .table td {
            padding: 1rem;
            vertical-align: middle;
        }

        .table img {
            border-radius: 8px;
            object-fit: cover;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .modal-content {
            border-radius: 10px;
            border: none;
        }

        .modal-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid var(--border-color);
        }

        .alert {
            border-radius: 8px;
            border: none;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
        }

        .alert-danger {
            background-color: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="admin-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="page-title">Manage Why Choose Us Section</h1>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="user-info">
                        <i class="fas fa-user-circle fa-lg"></i>
                        <span class="fw-500"><?php echo htmlspecialchars($current_user); ?></span>
                        <span class="timestamp ms-3">
                            <i class="far fa-clock"></i>
                            <?php echo $current_datetime; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <?php if(isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if(isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Add New Feature Form -->
        <div class="content-card">
            <h4 class="mb-4">Add New Feature</h4>
            <form method="POST" enctype="multipart/form-data" class="row">
                <div class="col-md-6">
                    <div class="mb-4">
                        <label class="form-label">Section Image (Building)</label>
                        <input type="file" name="section_image" class="form-control" accept="image/*" required>
                        <div id="section-image-preview" class="section-image-preview"></div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-4">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" required></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Icon</label>
                        <input type="file" name="icon" class="form-control" accept="image/*" required>
                        <div id="icon-preview" class="preview-icon"></div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Icon Background Color</label>
                        <input type="color" name="icon_bg_color" class="form-control" value="#f0f4ff">
                    </div>
                </div>

                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-2"></i>Add Feature
                    </button>
                </div>
            </form>
        </div>

        <!-- Features Table -->
        <div class="content-card">
            <h4 class="mb-4">Existing Features</h4>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Section Image</th>
                            <th>Icon</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Created By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($features && $features->num_rows > 0): ?>
                            <?php while($row = $features->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo htmlspecialchars($row['section_image']); ?>" 
                                         alt="Section Image" style="width: 100px; height: 60px; object-fit: cover;">
                                </td>
                                <td>
                                    <div class="preview-icon" style="background-color: <?php echo htmlspecialchars($row['icon_bg_color']); ?>">
                                        <img src="<?php echo htmlspecialchars($row['icon']); ?>" 
                                             alt="Icon" style="width: 25px;">
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                <td>
                                    <div style="max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        <?php echo htmlspecialchars($row['description']); ?>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($row['created_by']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-sm btn-outline-primary edit-btn" 
                                                data-id="<?php echo $row['id']; ?>"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editModal">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger delete-btn"
                                                data-id="<?php echo $row['id']; ?>"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteModal">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="fas fa-inbox fa-2x text-muted mb-3 d-block"></i>
                                    <p class="text-muted">No features found</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Feature</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="edit_id" id="edit_id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Section Image</label>
                                    <input type="file" name="edit_section_image" class="form-control" accept="image/*">
                                    <div id="edit-section-preview" class="section-image-preview mt-2"></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Title</label>
                                    <input type="text" name="edit_title" id="edit_title" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="edit_description" id="edit_description" class="form-control" rows="3" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Icon</label>
                                    <input type="file" name="edit_icon" class="form-control" accept="image/*">
                                    <div id="edit-icon-preview" class="preview-icon mt-2"></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Icon Background Color</label>
                                    <input type="color" name="edit_icon_bg_color" id="edit_icon_bg_color" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Feature</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="delete_id" id="delete_id">
                        <p class="mb-0">Are you sure you want to delete this feature? This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Preview uploaded images
        function previewImage(input, previewElement) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.maxWidth = '100%';
                    previewElement.innerHTML = '';
                    previewElement.appendChild(img);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // File upload preview handlers
        document.querySelector('input[name="section_image"]').addEventListener('change', function() {
            previewImage(this, document.getElementById('section-image-preview'));
        });

        document.querySelector('input[name="icon"]').addEventListener('change', function() {
            previewImage(this, document.getElementById('icon-preview'));
        });

        document.querySelector('input[name="edit_section_image"]').addEventListener('change', function() {
            previewImage(this, document.getElementById('edit-section-preview'));
        });

        document.querySelector('input[name="edit_icon"]').addEventListener('change', function() {
            previewImage(this, document.getElementById('edit-icon-preview'));
        });

        // Edit button handler
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function() {
                const row = this.closest('tr');
                const id = this.getAttribute('data-id');
                const title = row.querySelector('td:nth-child(3)').textContent;
                const description = row.querySelector('td:nth-child(4) div').textContent;
                const iconBgColor = row.querySelector('.preview-icon').style.backgroundColor;
                
                document.getElementById('edit_id').value = id;
                document.getElementById('edit_title').value = title;
                document.getElementById('edit_description').value = description;
                document.getElementById('edit_icon_bg_color').value = iconBgColor;
                
                // Show current images in preview
                const sectionImg = row.querySelector('td:nth-child(1) img').cloneNode(true);
                const iconImg = row.querySelector('td:nth-child(2) img').cloneNode(true);
                
                document.getElementById('edit-section-preview').innerHTML = '';
                document.getElementById('edit-section-preview').appendChild(sectionImg);
                
                document.getElementById('edit-icon-preview').innerHTML = '';
                document.getElementById('edit-icon-preview').appendChild(iconImg);
            });
        });

        // Delete button handler
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                document.getElementById('delete_id').value = id;
            });
        });
    </script>
</body>
</html>