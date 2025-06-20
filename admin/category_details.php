<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include('../includes/db_connection.php');

// Get category ID from URL
$category_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle category update
    if (isset($_POST['update_category'])) {
        $name = $_POST['name'];
        
        // Handle image upload
        if (!empty($_FILES['photo']['name'])) {
            $target_dir = "../uploads/categories/";
            $target_file = $target_dir . basename($_FILES['photo']['name']);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            
            // Generate unique filename
            $new_filename = uniqid() . '.' . $imageFileType;
            $target_path = $target_dir . $new_filename;
            
            // Check if image file is valid
            $check = getimagesize($_FILES['photo']['tmp_name']);
            if ($check !== false) {
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_path)) {
                    $photo = "uploads/categories/" . $new_filename;
                }
            }
        } else {
            $photo = $_POST['existing_photo'];
        }
        
        $sql = "UPDATE categories SET name = ?, photo = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $name, $photo, $category_id);
        $stmt->execute();
    }
    
    // Handle subcategory creation
    if (isset($_POST['add_subcategory'])) {
        $sub_name = $_POST['sub_name'];
        $youtube_link = $_POST['youtube_link'];
        
        // Handle subcategory image upload
        if (!empty($_FILES['sub_photo']['name'])) {
            $target_dir = "../uploads/subcategories/";
            $target_file = $target_dir . basename($_FILES['sub_photo']['name']);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            
            // Generate unique filename
            $new_filename = uniqid() . '.' . $imageFileType;
            $target_path = $target_dir . $new_filename;
            
            // Check if image file is valid
            $check = getimagesize($_FILES['sub_photo']['tmp_name']);
            if ($check !== false) {
                if (move_uploaded_file($_FILES['sub_photo']['tmp_name'], $target_path)) {
                    $sub_photo = "uploads/subcategories/" . $new_filename;
                }
            }
        }
        
        $sql = "INSERT INTO subcategories (parent_category_id, name, photo, youtube_link) 
                VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isss", $category_id, $sub_name, $sub_photo, $youtube_link);
        $stmt->execute();
    }
    
  // Handle category deletion
// Handle category deletion
if (isset($_POST['delete_category'])) {
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // First delete all subcategories
        $delete_subcategories = "DELETE FROM subcategories WHERE parent_category_id = ?";
        $stmt = $conn->prepare($delete_subcategories);
        $stmt->bind_param("i", $category_id);
        $stmt->execute();
        
        // Then delete the category
        $delete_category = "DELETE FROM categories WHERE id = ?";
        $stmt = $conn->prepare($delete_category);
        $stmt->bind_param("i", $category_id);
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        $_SESSION['success'] = "Category deleted successfully!";
        
        // Use relative path for redirect (more reliable)
        header("Location: view_categorie.php");
        exit();
    } catch (Exception $e) {
        // Rollback transaction if error occurs
        $conn->rollback();
        $_SESSION['error'] = "Error deleting category: " . $e->getMessage();
        header("Location: category_details.php?id=" . $category_id);
        exit();
    }
}
}
// Handle category update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_category'])) {
    $name = trim($_POST['name']);
    $photo = $_POST['existing_photo']; // Default to existing photo
    
    // Validate category name
    if (empty($name)) {
        $_SESSION['error'] = "Category name cannot be empty";
        header("Location: category_details.php?id=" . $category_id);
        exit();
    }

    // Handle image upload if provided
    if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "../uploads/categories/";
        
        // Create directory if it doesn't exist
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        // Validate and process the image
        $valid_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $file_extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_extension, $valid_extensions)) {
            $_SESSION['error'] = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
            header("Location: category_details.php?id=" . $category_id);
            exit();
        }

        // Generate unique filename
        $new_filename = uniqid() . '.' . $file_extension;
        $target_path = $target_dir . $new_filename;

        if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_path)) {
            $photo = "uploads/categories/" . $new_filename;
            
            // Delete old image if it exists and isn't a default
            if (!empty($_POST['existing_photo']) && 
                file_exists("../" . $_POST['existing_photo']) && 
                strpos($_POST['existing_photo'], 'default') === false) {
                @unlink("../" . $_POST['existing_photo']);
            }
        } else {
            $_SESSION['error'] = "Error uploading image";
            header("Location: category_details.php?id=" . $category_id);
            exit();
        }
    }

    // Update database
    $sql = "UPDATE categories SET name = ?, photo = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $name, $photo, $category_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Category updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating category: " . $conn->error;
    }
    
    header("Location: category_details.php?id=" . $category_id);
    exit();
}

// Fetch category details
$sql = "SELECT * FROM categories WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $category_id);
$stmt->execute();
$result = $stmt->get_result();
$category = $result->fetch_assoc();

if (!$category) {
    header("Location: view_categorie.php");
    exit();
}

// Fetch subcategories
$sql = "SELECT * FROM subcategories WHERE parent_category_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $category_id);
$stmt->execute();
$subcategories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($category['name']); ?> - Real Estate</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2980b9;
            --secondary-color: #6dd5fa;
            --accent-color: #FF6347;
            --dark-color: #2c3e50;
            --light-color: #f8f9fa;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f6f9;
            color: #333;
        }
        
        .category-header {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
            padding-bottom: 20px;
        }
        
        .category-header h1 {
            font-weight: 700;
            color: var(--dark-color);
            display: inline-block;
            position: relative;
        }
        
        .category-header h1::after {
            content: '';
            position: absolute;
            width: 80px;
            height: 4px;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 2px;
        }
        
        .category-image-container {
            height: 350px;
            overflow: hidden;
            border-radius: 15px;
            margin-bottom: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            position: relative;
        }
        
        .category-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .category-image-container:hover .category-image {
            transform: scale(1.03);
        }
        
        .action-buttons .btn {
            margin-right: 10px;
            margin-bottom: 10px;
            border-radius: 50px;
            padding: 10px 20px;
            font-weight: 500;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .action-buttons .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.15);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            border: none;
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #95a5a6, #7f8c8d);
            border: none;
        }
        
        .card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            margin-bottom: 25px;
            overflow: hidden;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            font-weight: 600;
            border-radius: 15px 15px 0 0 !important;
            padding: 15px 20px;
        }
        
        .subcategory-card {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            margin-bottom: 20px;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .subcategory-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .subcategory-image-container {
            height: 180px;
            overflow: hidden;
            position: relative;
        }
        
        .subcategory-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .subcategory-card:hover .subcategory-image {
            transform: scale(1.05);
        }
        
        .subcategory-body {
            padding: 15px;
            flex-grow: 1;
        }
        
        .subcategory-title {
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--dark-color);
        }
        
        .youtube-btn {
            background: linear-gradient(135deg, #FF0000, #CC0000);
            border: none;
            border-radius: 50px;
            padding: 5px 15px;
            color: white;
            font-size: 0.8rem;
            font-weight: 500;
            box-shadow: 0 4px 8px rgba(255,0,0,0.2);
        }
        
        .youtube-btn:hover {
            background: linear-gradient(135deg, #CC0000, #990000);
            color: white;
        }
        
        .form-control, .form-select {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid #ddd;
            box-shadow: none;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(41, 128, 185, 0.2);
        }
        
        .file-upload {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }
        
        .file-upload-btn {
            border: 2px dashed #ddd;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background-color: #f8f9fa;
            width: 100%;
        }
        
        .file-upload-btn:hover {
            border-color: var(--primary-color);
            background-color: #f1f8fe;
        }
        
        .file-upload-input {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        
        .preview-image {
            max-width: 100%;
            max-height: 150px;
            margin-top: 15px;
            border-radius: 8px;
            display: none;
        }
        
        .no-subcategories {
            text-align: center;
            padding: 40px;
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .no-subcategories i {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 15px;
        }
        
        .no-subcategories h4 {
            color: #777;
            font-weight: 500;
        }
        
        @media (max-width: 768px) {
            .category-image-container {
                height: 250px;
            }
            
            .action-buttons .btn {
                width: 100%;
                margin-right: 0;
            }
            
            .subcategory-image-container {
                height: 150px;
            }
        }
        
        /* Animation for cards */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .subcategory-card {
            animation: fadeIn 0.5s ease-out forwards;
            opacity: 0;
        }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<!-- Display error/success messages -->

    <div class="container py-4 py-lg-5">
        <div class="category-header">
            <h1><i class="fas fa-tag me-2"></i><?php echo htmlspecialchars($category['name']); ?></h1>
        </div>
        
        <div class="row">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="category-image-container">
                    <img src="../<?php echo htmlspecialchars($category['photo']); ?>" 
                         alt="<?php echo htmlspecialchars($category['name']); ?>" 
                         class="category-image">
                </div>
                
                <div class="action-buttons d-flex flex-wrap">
                    <!-- Edit Category Button (triggers modal) -->
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editCategoryModal">
                        <i class="fas fa-edit me-2"></i> Edit Category
                    </button>
                    
                    <!-- Delete Category Button (triggers modal) -->
                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteCategoryModal">
                        <i class="fas fa-trash me-2"></i> Delete Category
                    </button>
                    
                    <a href="view_categorie.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i> Back to Categories
                    </a>
                </div>
            </div>
            
            <div class="col-lg-6">
                <!-- Add Subcategory Card -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i> Add New Subcategory</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Subcategory Name</label>
                                <input type="text" name="sub_name" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Subcategory Image</label>
                                <div class="file-upload">
                                    <label class="file-upload-btn">
                                        <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i><br>
                                        <span>Click to upload image</span>
                                        <span class="d-block small text-muted mt-1">(JPEG, PNG, max 5MB)</span>
                                        <input type="file" name="sub_photo" class="file-upload-input" accept="image/*" required>
                                        <img class="preview-image" id="subcategoryPreview">
                                    </label>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">YouTube Link (optional)</label>
                                <input type="url" name="youtube_link" class="form-control" placeholder="https://youtube.com/...">
                            </div>
                            
                            <button type="submit" name="add_subcategory" class="btn btn-primary w-100 py-2">
                                <i class="fas fa-save me-2"></i> Save Subcategory
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Subcategories Section - Updated to make cards clickable -->
        <div class="mt-5">
            <h3 class="mb-4"><i class="fas fa-list-ul me-2"></i> Subcategories</h3>
            
            <?php if (count($subcategories) > 0): ?>
                <div class="row">
                    <?php foreach($subcategories as $index => $subcategory): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <a href="subcategory_details.php?id=<?php echo $subcategory['id']; ?>" class="text-decoration-none">
                                <div class="subcategory-card" style="animation-delay: <?php echo $index * 0.1; ?>s;">
                                    <div class="subcategory-image-container">
                                        <img src="../<?php echo htmlspecialchars($subcategory['photo']); ?>" 
                                             class="subcategory-image">
                                    </div>
                                    <div class="subcategory-body">
                                        <h5 class="subcategory-title"><?php echo htmlspecialchars($subcategory['name']); ?></h5>
                                        <?php if ($subcategory['youtube_link']): ?>
                                            <span class="youtube-btn">
                                                <i class="fab fa-youtube me-1"></i> Watch Video
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-subcategories">
                    <i class="fas fa-folder-open"></i>
                    <h4 class="mt-3">No Subcategories Yet</h4>
                    <p class="text-muted">Add your first subcategory using the form above</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
 <!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCategoryModalLabel">
                    <i class="fas fa-edit me-2"></i>Edit Category
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editCategoryForm" method="POST" enctype="multipart/form-data" 
      action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?id=' . $category_id); ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="categoryName" class="form-label">Category Name</label>
                        <input type="text" class="form-control" id="categoryName" name="name" 
                               value="<?php echo htmlspecialchars($category['name']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Category Image</label>
                        <input type="hidden" name="existing_photo" 
                               value="<?php echo htmlspecialchars($category['photo']); ?>">
                        
                        <div class="file-upload">
                            <label class="file-upload-btn">
                                <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i><br>
                                <span>Click to change image</span>
                                <span class="d-block small text-muted mt-1">(Leave blank to keep current image)</span>
                                <input type="file" name="photo" class="file-upload-input" accept="image/*">
                                <img src="../<?php echo htmlspecialchars($category['photo']); ?>" 
                                     class="preview-image" id="categoryPreview">
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_category" class="btn btn-primary" id="saveCategoryChanges">
                        <i class="fas fa-save me-2"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
    
    <!-- Delete Category Modal -->
  <!-- Delete Category Modal -->
<div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Confirm Deletion</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?id=' . $category_id; ?>">
                <div class="modal-body">
                    <p>Are you sure you want to permanently delete this category?</p>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>Warning:</strong> This will also delete all subcategories under it!
                    </div>
                    <div class="text-center p-3 bg-light rounded">
                        <h5 class="mb-0"><?php echo htmlspecialchars($category['name']); ?></h5>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="delete_category" class="btn btn-danger">Delete Permanently</button>
                </div>
            </form>
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Image preview functionality
        function readURL(input, previewId) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                
                reader.onload = function(e) {
                    var preview = document.getElementById(previewId);
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        // Category image preview
        document.querySelector('input[name="photo"]').addEventListener('change', function() {
            readURL(this, 'categoryPreview');
        });
        
        // Subcategory image preview
        document.querySelector('input[name="sub_photo"]').addEventListener('change', function() {
            readURL(this, 'subcategoryPreview');
        });
        
        // Animation for cards when they come into view
        document.addEventListener('DOMContentLoaded', function() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                    }
                });
            }, { threshold: 0.1 });
            
            document.querySelectorAll('.subcategory-card').forEach(card => {
                observer.observe(card);
            });
        });
        // Image preview functionality with validation
function readURL(input, previewId) {
    if (input.files && input.files[0]) {
        // Check file size (max 5MB)
        if (input.files[0].size > 5 * 1024 * 1024) {
            alert('File size exceeds 5MB limit');
            input.value = ''; // Clear the file input
            return;
        }
        
        var reader = new FileReader();
        
        reader.onload = function(e) {
            var preview = document.getElementById(previewId);
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}

// Category image preview
document.querySelector('input[name="photo"]')?.addEventListener('change', function() {
    readURL(this, 'categoryPreview');
});

// Subcategory image preview
document.querySelector('input[name="sub_photo"]')?.addEventListener('change', function() {
    readURL(this, 'subcategoryPreview');
});


   
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize all Bootstrap tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Ensure modals work properly
        var editModal = document.getElementById('editCategoryModal');
        if (editModal) {
            editModal.addEventListener('show.bs.modal', function (event) {
                // Modal show logic if needed
            });
        }
        
        // Image preview functionality with validation
        function readURL(input, previewId) {
            if (input.files && input.files[0]) {
                // Check file size (max 5MB)
                if (input.files[0].size > 5 * 1024 * 1024) {
                    alert('File size exceeds 5MB limit');
                    input.value = ''; // Clear the file input
                    return;
                }
                
                var reader = new FileReader();
                
                reader.onload = function(e) {
                    var preview = document.getElementById(previewId);
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        // Category image preview
        document.querySelector('input[name="photo"]')?.addEventListener('change', function() {
            readURL(this, 'categoryPreview');
        });
        
        // Subcategory image preview
        document.querySelector('input[name="sub_photo"]')?.addEventListener('change', function() {
            readURL(this, 'subcategoryPreview');
        });
    });
    <script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize form validation
    const editForm = document.getElementById('editCategoryForm');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            // Client-side validation
            const categoryName = document.getElementById('categoryName').value.trim();
            if (!categoryName) {
                e.preventDefault();
                alert('Category name is required');
                return false;
            }
            
            // If file is selected, validate it
            const fileInput = document.querySelector('input[name="photo"]');
            if (fileInput.files.length > 0) {
                const file = fileInput.files[0];
                const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
                const maxSize = 5 * 1024 * 1024; // 5MB
                
                if (!validTypes.includes(file.type)) {
                    e.preventDefault();
                    alert('Only JPG, PNG, and GIF images are allowed');
                    return false;
                }
                
                if (file.size > maxSize) {
                    e.preventDefault();
                    alert('Image size must be less than 5MB');
                    return false;
                }
            }
            
            return true;
        });
    }

    // Image preview functionality
    function readURL(input, previewId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById(previewId).src = e.target.result;
                document.getElementById(previewId).style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    document.querySelector('input[name="photo"]')?.addEventListener('change', function() {
        readURL(this, 'categoryPreview');
    });
});
</script>
</script>
    </script>
</body>
</html>