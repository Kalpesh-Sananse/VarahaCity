<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include('../includes/db_connection.php');

// Get subcategory ID from URL
$subcategory_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch subcategory details first
$sql = "SELECT s.*, c.name as category_name, c.id as category_id 
        FROM subcategories s 
        JOIN categories c ON s.parent_category_id = c.id 
        WHERE s.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $subcategory_id);
$stmt->execute();
$result = $stmt->get_result();
$subcategory = $result->fetch_assoc();

if (!$subcategory) {
    header("Location: view_categorie.php");
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_user = 'alexdanny090';
    $current_timestamp = '2025-06-05 13:49:58';

    // Handle product creation
    if (isset($_POST['add_product'])) {
        $product_name = $_POST['product_name'];
        $youtube_link = $_POST['youtube_link'] ?? '';
        $category_id = $subcategory['parent_category_id'];
        
        // Handle product image upload
        if (!empty($_FILES['product_image']['name'])) {
            $target_dir = "../uploads/products/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $target_file = $target_dir . basename($_FILES['product_image']['name']);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            
            // Generate unique filename
            $new_filename = uniqid() . '.' . $imageFileType;
            $target_path = $target_dir . $new_filename;
            
            // Check if image file is valid
            $check = getimagesize($_FILES['product_image']['tmp_name']);
            if ($check !== false) {
                if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target_path)) {
                    $image = "uploads/products/" . $new_filename;
                    
                    // Insert product into database
                    $sql = "INSERT INTO products (subcategory_id, category_id, name, image, youtube_link, created_at) 
                           VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("iissss", $subcategory_id, $category_id, 
                                   $product_name, $image, $youtube_link, $current_timestamp);
                    
                    if ($stmt->execute()) {
                        // Redirect to refresh the page and avoid duplicate submissions
                        header("Location: subcategory_details.php?id=" . $subcategory_id);
                        exit();
                    }
                }
            }
        }
    }

    // Handle subcategory update
    if (isset($_POST['update_subcategory'])) {
        $name = $_POST['name'];
        $youtube_link = $_POST['youtube_link'];
        $property_type = $_POST['property_type'];
        $transaction_type = $_POST['transaction_type'];
        $transaction_status = isset($_POST['transaction_status']) ? $_POST['transaction_status'] : '';
        
        if (!empty($_FILES['photo']['name'])) {
            $target_dir = "../uploads/subcategories/";
            $target_file = $target_dir . basename($_FILES['photo']['name']);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            
            $new_filename = uniqid() . '.' . $imageFileType;
            $target_path = $target_dir . $new_filename;
            
            $check = getimagesize($_FILES['photo']['tmp_name']);
            if ($check !== false) {
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_path)) {
                    $photo = "uploads/subcategories/" . $new_filename;
                }
            }
        } else {
            $photo = $_POST['existing_photo'];
        }
        
        $sql = "UPDATE subcategories 
                SET name = ?, photo = ?, youtube_link = ?, 
                    property_type = ?, transaction_type = ?, transaction_status = ? 
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssi", $name, $photo, $youtube_link, 
                         $property_type, $transaction_type, $transaction_status, $subcategory_id);
        
        if ($stmt->execute()) {
            // Redirect to refresh the page
            header("Location: subcategory_details.php?id=" . $subcategory_id);
            exit();
        }
    }
    
    // Handle subcategory deletion
    if (isset($_POST['delete_subcategory'])) {
        // Delete associated products first
        $sql = "DELETE FROM products WHERE subcategory_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $subcategory_id);
        $stmt->execute();
        
        // Then delete the subcategory
        $sql = "DELETE FROM subcategories WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $subcategory_id);
        
        if ($stmt->execute()) {
            header("Location: view_categorie.php");
            exit();
        }
    }
}
// Handle product deletion
if (isset($_POST['delete_product'])) {
    $product_id = intval($_POST['product_id']);
    
    // First get the product image path to delete the file
    $sql = "SELECT image FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    
    if ($product) {
        // Delete the product image file if it exists
        if (!empty($product['image']) && file_exists("../" . $product['image'])) {
            unlink("../" . $product['image']);
        }
        
        // Delete the product from database
        $sql = "DELETE FROM products WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Product deleted successfully!";
        } else {
            $_SESSION['error'] = "Error deleting product: " . $conn->error;
        }
    }
    
    // Redirect back to the same page
    header("Location: subcategory_details.php?id=" . $subcategory_id);
    exit();
}

// Fetch products for this subcategory
$sql = "SELECT * FROM products WHERE subcategory_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $subcategory_id);
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($subcategory['name']); ?> - Real Estate</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .delete-product-btn {
    transition: all 0.3s ease;
}

.delete-product-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
}
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
        
        .subcategory-header {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
            padding-bottom: 20px;
        }
        
        .subcategory-header h1 {
            font-weight: 700;
            color: var(--dark-color);
            display: inline-block;
            position: relative;
        }
        
        .subcategory-header h1::after {
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
        
        .breadcrumb-custom {
            background-color: white;
            padding: 10px 20px;
            border-radius: 50px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: inline-flex;
            margin-bottom: 30px;
        }
        
        .subcategory-image-container {
            height: 350px;
            overflow: hidden;
            border-radius: 15px;
            margin-bottom: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            position: relative;
        }
        
        .subcategory-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .product-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .product-card .card-img-top {
            height: 200px;
            object-fit: cover;
        }
        
        .product-card .card-body {
            padding: 1.25rem;
        }
        
        .product-card .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: var(--dark-color);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        }
        
        .action-buttons .btn {
            margin-right: 10px;
            margin-bottom: 10px;
            border-radius: 50px;
            padding: 10px 20px;
            font-weight: 500;
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
        
        .preview-image {
            max-width: 100%;
            max-height: 150px;
            margin-top: 15px;
            border-radius: 8px;
            display: none;
        }
        
        .property-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 50px;
            font-weight: 500;
            margin-right: 10px;
            margin-bottom: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        
        @media (max-width: 768px) {
            .subcategory-image-container {
                height: 250px;
            }
            
            .action-buttons .btn {
                width: 100%;
                margin-right: 0;
            }
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .card {
            animation: fadeIn 0.5s ease-out forwards;
        }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
    <div class="container py-4 py-lg-5">
        <div class="subcategory-header">
            <div class="breadcrumb-custom">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="view_categorie.php" class="text-decoration-none">Categories</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="category_details.php?id=<?php echo $subcategory['category_id']; ?>" 
                               class="text-decoration-none">
                                <?php echo htmlspecialchars($subcategory['category_name']); ?>
                            </a>
                        </li>
                        <li class="breadcrumb-item active">
                            <?php echo htmlspecialchars($subcategory['name']); ?>
                        </li>
                    </ol>
                </nav>
            </div>
            <h1><i class="fas fa-tag me-2"></i><?php echo htmlspecialchars($subcategory['name']); ?></h1>
        </div>
        
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="subcategory-image-container">
                    <img src="../<?php echo htmlspecialchars($subcategory['photo']); ?>" 
                         alt="<?php echo htmlspecialchars($subcategory['name']); ?>" 
                         class="subcategory-image">
                </div>
                
                <div class="mb-4">
                    <?php if (isset($subcategory['property_type']) && !empty($subcategory['property_type'])): ?>
                        <span class="property-badge <?php echo $subcategory['property_type'] === 'buy' ? 'badge-buy' : 'badge-sale'; ?>">
                            <i class="fas <?php echo $subcategory['property_type'] === 'buy' ? 'fa-shopping-cart' : 'fa-tag'; ?> me-2"></i>
                            <?php echo ucfirst($subcategory['property_type']); ?>
                        </span>
                    <?php endif; ?>
                    
                    <?php if (isset($subcategory['transaction_type']) && !empty($subcategory['transaction_type'])): ?>
                        <span class="property-badge <?php echo $subcategory['transaction_type'] === 'rent' ? 'badge-rent' : 'badge-lease'; ?>">
                            <i class="fas <?php echo $subcategory['transaction_type'] === 'rent' ? 'fa-home' : 'fa-file-contract'; ?> me-2"></i>
                            <?php echo ucfirst($subcategory['transaction_type']); ?>
                        </span>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($subcategory['youtube_link'])): ?>
                    <div class="youtube-embed mb-4">
                        <?php
                        $youtube_id = '';
                        if (preg_match('/(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/'
                                     . '|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', 
                                     $subcategory['youtube_link'], $matches)) {
                            $youtube_id = $matches[1];
                        }
                        
                        if ($youtube_id) {
                            echo '<iframe src="https://www.youtube.com/embed/' 
                                 . htmlspecialchars($youtube_id) 
                                 . '" allowfullscreen style="width:100%;height:315px;border:0;"></iframe>';
                        }
                        ?>
                    </div>
                <?php endif; ?>
                
                <div class="action-buttons d-flex flex-wrap">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editSubcategoryModal">
                        <i class="fas fa-edit me-2"></i> Edit Subcategory
                    </button>
                    
                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteSubcategoryModal">
                        <i class="fas fa-trash me-2"></i> Delete Subcategory
                    </button>
                    
                    <a href="category_details.php?id=<?php echo $subcategory['category_id']; ?>" 
                       class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i> Back to Category
                    </a>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i> Add New Product</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Product Name</label>
                                <input type="text" name="product_name" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Product Image</label>
                                <div class="file-upload">
                                    <label class="file-upload-btn">
                                        <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i><br>
                                        <span>Click to upload product image</span>
                                        <span class="d-block small text-muted mt-1">(JPEG, PNG, max 5MB)</span>
                                        <input type="file" name="product_image" class="file-upload-input" 
                                               accept="image/*" required>
                                        <img class="preview-image" id="productImagePreview">
                                    </label>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">YouTube Link (optional)</label>
                                <input type="url" name="youtube_link" class="form-control" 
                                       placeholder="https://youtube.com/...">
                            </div>
                            
                            <button type="submit" name="add_product" class="btn btn-primary w-100 py-2">
                                <i class="fas fa-plus me-2"></i> Create Product
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Products Display -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-box me-2"></i> Products</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($products) > 0): ?>
                            <div class="row row-cols-1 row-cols-md-2 g-4">
                                <?php foreach($products as $product): ?>
                                    <div class="col">
                                        <div class="card h-100 product-card">
                                            <img src="../<?php echo htmlspecialchars($product['image']); ?>" 
                                                 class="card-img-top" 
                                                 alt="<?php echo htmlspecialchars($product['name']); ?>">
                                            <div class="card-body">
                                                <h5 class="card-title">
                                                    <?php echo htmlspecialchars($product['name']); ?>
                                                </h5>
                                                <?php if (!empty($product['youtube_link'])): ?>
                                                    <a href="<?php echo htmlspecialchars($product['youtube_link']); ?>" 
                                                       class="btn btn-sm btn-outline-primary" 
                                                       target="_blank">
                                                        <i class="fab fa-youtube me-1"></i> Watch Video
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                            <div class="card-footer bg-transparent d-flex justify-content-between">
    <a href="product_details.php?id=<?php echo $product['id']; ?>" 
       class="btn btn-primary btn-sm">
        <i class="fas fa-edit me-1"></i> Edit
    </a>
    <button class="btn btn-danger btn-sm delete-product-btn" 
            data-product-id="<?php echo $product['id']; ?>">
        <i class="fas fa-trash me-1"></i> Delete
    </button>
</div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center p-4">
                                <i class="fas fa-box fa-3x text-muted mb-3"></i>
                                <h5>No Products Yet</h5>
                                <p class="text-muted">Add your first product using the form above.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Edit Subcategory Modal -->
    <div class="modal fade" id="editSubcategoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>Edit Subcategory
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Subcategory Name</label>
                            <input type="text" name="name" class="form-control" 
                                   value="<?php echo htmlspecialchars($subcategory['name']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Subcategory Image</label>
                            <input type="hidden" name="existing_photo" 
                                   value="<?php echo htmlspecialchars($subcategory['photo']); ?>">
                            
                            <div class="file-upload">
                                <label class="file-upload-btn">
                                    <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i><br>
                                    <span>Click to change image</span>
                                    <span class="d-block small text-muted mt-1">
                                        (Leave blank to keep current image)
                                    </span>
                                    <input type="file" name="photo" class="file-upload-input" accept="image/*">
                                    <img src="../<?php echo htmlspecialchars($subcategory['photo']); ?>" 
                                         class="preview-image" id="subcategoryPreview" style="display: block;">
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">YouTube Link (optional)</label>
                            <input type="url" name="youtube_link" class="form-control" 
                                   placeholder="https://youtube.com/..."
                                   value="<?php echo htmlspecialchars($subcategory['youtube_link'] ?? ''); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Property Type</label>
                            <div class="toggle-container">
                                <div class="toggle-option">
                                    <input type="radio" id="type-buy" name="property_type" value="buy" 
                                           <?php echo (isset($subcategory['property_type']) && 
                                                     $subcategory['property_type'] === 'buy') ? 'checked' : ''; ?>>
                                    <label for="type-buy" class="toggle-option-label">Buy</label>
                                </div>
                                <div class="toggle-option">
                                    <input type="radio" id="type-sale" name="property_type" value="sale"
                                           <?php echo (isset($subcategory['property_type']) && 
                                                     $subcategory['property_type'] === 'sale') ? 'checked' : ''; ?>>
                                    <label for="type-sale" class="toggle-option-label">Sale</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Transaction Type</label>
                            <div class="toggle-container">
                                <div class="toggle-option">
                                    <input type="radio" id="trans-rent" name="transaction_type" value="rent" 
                                           <?php echo (isset($subcategory['transaction_type']) && 
                                                     $subcategory['transaction_type'] === 'rent') ? 'checked' : ''; ?>>
                                    <label for="trans-rent" class="toggle-option-label">Rent</label>
                                </div>
                                <div class="toggle-option">
                                    <input type="radio" id="trans-lease" name="transaction_type" value="lease"
                                           <?php echo (isset($subcategory['transaction_type']) && 
                                                     $subcategory['transaction_type'] === 'lease') ? 'checked' : ''; ?>>
                                    <label for="trans-lease" class="toggle-option-label">Lease</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_subcategory" class="btn btn-primary">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Delete Subcategory Modal -->
    <div class="modal fade" id="deleteSubcategoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirm Deletion
                    </h5>
                    <button type="button" class="btn-close btn-close-white" 
                            data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <p>Are you sure you want to permanently delete this subcategory?</p>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <strong>Warning:</strong> This will also delete all products associated 
                            with this subcategory!
                        </div>
                        <div class="text-center p-3 bg-light rounded">
                            <h5 class="mb-0"><?php echo htmlspecialchars($subcategory['name']); ?></h5>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="delete_subcategory" class="btn btn-danger">
                            Delete Permanently
                        </button>
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
        
        // Product image preview
        document.querySelector('input[name="product_image"]').addEventListener('change', function() {
            readURL(this, 'productImagePreview');
        });
        
        // Subcategory image preview
        document.querySelector('input[name="photo"]').addEventListener('change', function() {
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
            
            document.querySelectorAll('.card').forEach(item => {
                observer.observe(item);
            });
        });
        // Product deletion handling
document.addEventListener('DOMContentLoaded', function() {
    // Set up delete buttons
    document.querySelectorAll('.delete-product-btn').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            document.getElementById('deleteProductId').value = productId;
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteProductModal'));
            deleteModal.show();
        });
    });
});
    </script>
    <!-- Delete Product Confirmation Modal -->
<div class="modal fade" id="deleteProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirm Deletion
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="deleteProductForm">
                <input type="hidden" name="product_id" id="deleteProductId">
                <div class="modal-body">
                    <p>Are you sure you want to delete this product?</p>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        This action cannot be undone.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="delete_product" class="btn btn-danger">
                        Delete Permanently
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>