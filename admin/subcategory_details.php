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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle subcategory update
    if (isset($_POST['update_subcategory'])) {
        $name = $_POST['name'];
        $youtube_link = $_POST['youtube_link'];
        $property_type = $_POST['property_type']; // buy or sale
        $transaction_type = $_POST['transaction_type']; // rent or lease
        $transaction_status = isset($_POST['transaction_status']) ? $_POST['transaction_status'] : ''; // for rent/needs rent or for lease/needs lease
        
        // Handle image upload
        if (!empty($_FILES['photo']['name'])) {
            $target_dir = "../uploads/subcategories/";
            $target_file = $target_dir . basename($_FILES['photo']['name']);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            
            // Generate unique filename
            $new_filename = uniqid() . '.' . $imageFileType;
            $target_path = $target_dir . $new_filename;
            
            // Check if image file is valid
            $check = getimagesize($_FILES['photo']['tmp_name']);
            if ($check !== false) {
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_path)) {
                    $photo = "uploads/subcategories/" . $new_filename;
                }
            }
        } else {
            $photo = $_POST['existing_photo'];
        }
        
        $sql = "UPDATE subcategories SET name = ?, photo = ?, youtube_link = ?, property_type = ?, transaction_type = ?, transaction_status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssi", $name, $photo, $youtube_link, $property_type, $transaction_type, $transaction_status, $subcategory_id);
        $stmt->execute();
    }
    
    // Handle subcategory deletion
    if (isset($_POST['delete_subcategory'])) {
        // First delete related custom fields and values
        $sql = "DELETE FROM custom_field_values WHERE subcategory_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $subcategory_id);
        $stmt->execute();
        
        // Delete additional images
        $sql = "DELETE FROM subcategory_images WHERE subcategory_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $subcategory_id);
        $stmt->execute();
        
        // Then delete the subcategory
        $sql = "DELETE FROM subcategories WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $subcategory_id);
        $stmt->execute();
        
        header("Location: view_categories.php");
        exit();
    }
    
    // Handle custom field creation
    if (isset($_POST['add_custom_field'])) {
        $field_title = $_POST['field_title'];
        
        // Insert custom field
        $sql = "INSERT INTO custom_fields (subcategory_id, title) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $subcategory_id, $field_title);
        $stmt->execute();
        $field_id = $conn->insert_id;
        
        // Insert field options
        $options = explode("\n", $_POST['field_options']);
        foreach ($options as $option) {
            $option = trim($option);
            if (empty($option)) continue;
            
            $sql = "INSERT INTO custom_field_values (field_id, subcategory_id, value) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iis", $field_id, $subcategory_id, $option);
            $stmt->execute();
        }
    }
    
    // Handle additional image upload
    if (isset($_POST['add_image'])) {
        if (!empty($_FILES['additional_image']['name'])) {
            $target_dir = "../uploads/subcategory_images/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $target_file = $target_dir . basename($_FILES['additional_image']['name']);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            
            // Generate unique filename
            $new_filename = uniqid() . '.' . $imageFileType;
            $target_path = $target_dir . $new_filename;
            
            // Check if image file is valid
            $check = getimagesize($_FILES['additional_image']['tmp_name']);
            if ($check !== false) {
                if (move_uploaded_file($_FILES['additional_image']['tmp_name'], $target_path)) {
                    $image_path = "uploads/subcategory_images/" . $new_filename;
                    
                    $sql = "INSERT INTO subcategory_images (subcategory_id, image_path) VALUES (?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("is", $subcategory_id, $image_path);
                    $stmt->execute();
                }
            }
        }
    }
    
    // Handle custom field deletion
    if (isset($_POST['delete_field'])) {
        $field_id = $_POST['field_id'];
        
        // Delete field values first
        $sql = "DELETE FROM custom_field_values WHERE field_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $field_id);
        $stmt->execute();
        
        // Then delete field
        $sql = "DELETE FROM custom_fields WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $field_id);
        $stmt->execute();
    }
    
    // Handle image deletion
    if (isset($_POST['delete_image'])) {
        $image_id = $_POST['image_id'];
        
        $sql = "DELETE FROM subcategory_images WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $image_id);
        $stmt->execute();
    }
}

// Fetch subcategory details
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
    header("Location: view_categories.php");
    exit();
}

// Fetch custom fields
$sql = "SELECT * FROM custom_fields WHERE subcategory_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $subcategory_id);
$stmt->execute();
$custom_fields = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch additional images
$sql = "SELECT * FROM subcategory_images WHERE subcategory_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $subcategory_id);
$stmt->execute();
$additional_images = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
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
        
        .subcategory-image-container:hover .subcategory-image {
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
        
        .youtube-embed {
            position: relative;
            padding-bottom: 56.25%; /* 16:9 aspect ratio */
            height: 0;
            overflow: hidden;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
        
        .youtube-embed iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 15px;
            border: none;
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
        
        .badge-buy {
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            color: white;
        }
        
        .badge-sale {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
        }
        
        .badge-rent {
            background: linear-gradient(135deg, #e67e22, #d35400);
            color: white;
        }
        
        .badge-lease {
            background: linear-gradient(135deg, #9b59b6, #8e44ad);
            color: white;
        }
        
        .badge-status {
            background: linear-gradient(135deg, #f1c40f, #f39c12);
            color: white;
        }
        
        .custom-field-item {
            background-color: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
            position: relative;
        }
        
        .custom-field-title {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .custom-field-value {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 5px 10px;
            margin-right: 8px;
            margin-bottom: 8px;
            display: inline-block;
            font-size: 0.9rem;
        }
        
        .delete-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background-color: #f8f9fa;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #e74c3c;
            transition: all 0.3s;
        }
        
        .delete-btn:hover {
            background-color: #e74c3c;
            color: white;
        }
        
        .toggle-container {
            position: relative;
            margin-bottom: 25px;
        }
        
        .toggle-option {
            display: inline-block;
            position: relative;
        }
        
        .toggle-option input[type="radio"] {
            opacity: 0;
            position: absolute;
        }
        
        .toggle-option-label {
            display: inline-block;
            padding: 12px 30px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
        }
        
        .toggle-option:first-child .toggle-option-label {
            border-radius: 10px 0 0 10px;
        }
        
        .toggle-option:last-child .toggle-option-label {
            border-radius: 0 10px 10px 0;
        }
        
        .toggle-option input[type="radio"]:checked + .toggle-option-label {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-color: var(--primary-color);
            box-shadow: 0 4px 10px rgba(41, 128, 185, 0.3);
        }
        
        .gallery-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .gallery-item {
            position: relative;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            aspect-ratio: 1/1;
        }
        
        .gallery-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }
        
        .gallery-item:hover .gallery-img {
            transform: scale(1.05);
        }
        
        .delete-image-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: rgba(0,0,0,0.6);
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            opacity: 0;
        }
        
        .gallery-item:hover .delete-image-btn {
            opacity: 1;
        }
        
        .delete-image-btn:hover {
            background-color: #e74c3c;
        }
        
        .conditional-field {
            display: none;
        }
        
        @media (max-width: 768px) {
            .subcategory-image-container {
                height: 250px;
            }
            
            .gallery-container {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
            
            .action-buttons .btn {
                width: 100%;
                margin-right: 0;
            }
        }
        
        /* Animation for cards */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .card, .custom-field-item {
            animation: fadeIn 0.5s ease-out forwards;
        }
    </style>
</head>
<body>
    <div class="container py-4 py-lg-5">
        <div class="subcategory-header">
            <div class="breadcrumb-custom">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="view_categories.php" class="text-decoration-none">Categories</a></li>
                        <li class="breadcrumb-item"><a href="category_details.php?id=<?php echo $subcategory['category_id']; ?>" class="text-decoration-none"><?php echo htmlspecialchars($subcategory['category_name']); ?></a></li>
                        <li class="breadcrumb-item active"><?php echo htmlspecialchars($subcategory['name']); ?></li>
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
                
                <!-- Property Type Badges -->
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
                    
                    <?php if (isset($subcategory['transaction_status']) && !empty($subcategory['transaction_status'])): ?>
                        <span class="property-badge badge-status">
                            <i class="fas fa-info-circle me-2"></i>
                            <?php echo ucfirst($subcategory['transaction_status']); ?>
                        </span>
                    <?php endif; ?>
                </div>
                
                <!-- YouTube Video if available -->
                <?php if (!empty($subcategory['youtube_link'])): ?>
                    <div class="youtube-embed">
                        <?php
                        // Extract YouTube video ID
                        $youtube_id = '';
                        if (preg_match('/(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $subcategory['youtube_link'], $matches)) {
                            $youtube_id = $matches[1];
                        }
                        
                        if ($youtube_id) {
                            echo '<iframe src="https://www.youtube.com/embed/' . htmlspecialchars($youtube_id) . '" allowfullscreen></iframe>';
                        } else {
                            echo '<div class="alert alert-warning">Invalid YouTube link</div>';
                        }
                        ?>
                    </div>
                <?php endif; ?>
                
                <!-- Additional Images Gallery -->
                <?php if (count($additional_images) > 0): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-images me-2"></i> Additional Images</h5>
                        </div>
                        <div class="card-body">
                            <div class="gallery-container">
                                <?php foreach ($additional_images as $image): ?>
                                    <div class="gallery-item">
                                        <img src="../<?php echo htmlspecialchars($image['image_path']); ?>" class="gallery-img">
                                        <form method="POST">
                                            <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                                            <button type="submit" name="delete_image" class="delete-image-btn" onclick="return confirm('Are you sure you want to delete this image?')">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="action-buttons d-flex flex-wrap">
                    <!-- Edit Subcategory Button (triggers modal) -->
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editSubcategoryModal">
                        <i class="fas fa-edit me-2"></i> Edit Subcategory
                    </button>
                    
                    <!-- Delete Subcategory Button (triggers modal) -->
                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteSubcategoryModal">
                        <i class="fas fa-trash me-2"></i> Delete Subcategory
                    </button>
                    
                    <a href="category_details.php?id=<?php echo $subcategory['category_id']; ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i> Back to Category
                    </a>
                </div>
            </div>
            
            <div class="col-lg-6">
                <!-- Add Additional Image -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i> Add Additional Image</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <div class="file-upload">
                                    <label class="file-upload-btn">
                                        <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i><br>
                                        <span>Click to upload image</span>
                                        <span class="d-block small text-muted mt-1">(JPEG, PNG, max 5MB)</span>
                                        <input type="file" name="additional_image" class="file-upload-input" accept="image/*" required>
                                        <img class="preview-image" id="additionalImagePreview">
                                    </label>
                                </div>
                            </div>
                            
                            <button type="submit" name="add_image" class="btn btn-primary w-100 py-2">
                                <i class="fas fa-save me-2"></i> Upload Image
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Add Custom Field -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i> Add Custom Field</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Field Title</label>
                                <input type="text" name="field_title" class="form-control" placeholder="e.g. Security Features" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Field Options (one per line)</label>
                                <textarea name="field_options" class="form-control" rows="5" placeholder="Compound
Fencing
Electrical Fencing" required></textarea>
                            </div>
                            
                            <button type="submit" name="add_custom_field" class="btn btn-primary w-100 py-2">
                                <i class="fas fa-save me-2"></i> Add Field
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Custom Fields Section -->
        <?php if (count($custom_fields) > 0): ?>
            <div class="mt-5">
                <h3 class="mb-4"><i class="fas fa-list-ul me-2"></i> Custom Fields</h3>
                <div class="row">
                    <?php foreach($custom_fields as $index => $field): ?>
                        <?php 
                        // Fetch values for this field
                        $sql = "SELECT * FROM custom_field_values WHERE field_id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $field['id']);
                        $stmt->execute();
                        $values = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                        ?>
                        <div class="col-md-6 mb-4">
                            <div class="custom-field-item" style="animation-delay: <?php echo $index * 0.1; ?>s;">
                                <h5 class="custom-field-title"><?php echo htmlspecialchars($field['title']); ?></h5>
                                <form method="POST">
                                    <input type="hidden" name="field_id" value="<?php echo $field['id']; ?>">
                                    <button type="submit" name="delete_field" class="delete-btn" onclick="return confirm('Are you sure you want to delete this field?')">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                                <?php foreach($values as $value): ?>
                                    <span class="custom-field-value">
                                        <?php echo htmlspecialchars($value['value']); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="mt-5 text-center">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle fa-2x mb-3"></i>
                    <h4>No Custom Fields Yet</h4>
                    <p class="mb-0">Add custom fields using the form above</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Edit Subcategory Modal -->
    <div class="modal fade" id="editSubcategoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Subcategory</h5>
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
                            <input type="hidden" name="existing_photo" value="<?php echo htmlspecialchars($subcategory['photo']); ?>">
                            
                            <div class="file-upload">
                                <label class="file-upload-btn">
                                    <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i><br>
                                    <span>Click to change image</span>
                                    <span class="d-block small text-muted mt-1">(Leave blank to keep current image)</span>
                                    <input type="file" name="photo" class="file-upload-input" accept="image/*">
                                    <img src="../<?php echo htmlspecialchars($subcategory['photo']); ?>" class="preview-image" id="subcategoryPreview" style="display: block;">
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">YouTube Link (optional)</label>
                            <input type="url" name="youtube_link" class="form-control" placeholder="https://youtube.com/..."
                                   value="<?php echo htmlspecialchars($subcategory['youtube_link'] ?? ''); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Property Type</label>
                            <div class="toggle-container">
                                <div class="toggle-option">
                                    <input type="radio" id="type-buy" name="property_type" value="buy" 
                                           <?php echo (isset($subcategory['property_type']) && $subcategory['property_type'] === 'buy') ? 'checked' : ''; ?>>
                                    <label for="type-buy" class="toggle-option-label">Buy</label>
                                </div>
                                <div class="toggle-option">
                                    <input type="radio" id="type-sale" name="property_type" value="sale"
                                           <?php echo (isset($subcategory['property_type']) && $subcategory['property_type'] === 'sale') ? 'checked' : ''; ?>>
                                    <label for="type-sale" class="toggle-option-label">Sale</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Transaction Type</label>
                            <div class="toggle-container">
                                <div class="toggle-option">
                                    <input type="radio" id="trans-rent" name="transaction_type" value="rent" 
                                           <?php echo (isset($subcategory['transaction_type']) && $subcategory['transaction_type'] === 'rent') ? 'checked' : ''; ?>>
                                    <label for="trans-rent" class="toggle-option-label">Rent</label>
                                </div>
                                <div class="toggle-option">
                                    <input type="radio" id="trans-lease" name="transaction_type" value="lease"
                                           <?php echo (isset($subcategory['transaction_type']) && $subcategory['transaction_type'] === 'lease') ? 'checked' : ''; ?>>
                                    <label for="trans-lease" class="toggle-option-label">Lease</label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Conditional Fields based on Transaction Type -->
                        <div id="rent-options" class="mb-3 conditional-field <?php echo (isset($subcategory['transaction_type']) && $subcategory['transaction_type'] === 'rent') ? 'd-block' : ''; ?>">
                            <label class="form-label">Rent Status</label>
                            <div class="toggle-container">
                                <div class="toggle-option">
                                    <input type="radio" id="rent-for" name="transaction_status" value="for rent" 
                                           <?php echo (isset($subcategory['transaction_status']) && $subcategory['transaction_status'] === 'for rent') ? 'checked' : ''; ?>>
                                    <label for="rent-for" class="toggle-option-label">For Rent</label>
                                </div>
                                <div class="toggle-option">
                                    <input type="radio" id="rent-needs" name="transaction_status" value="needs to be rent"
                                           <?php echo (isset($subcategory['transaction_status']) && $subcategory['transaction_status'] === 'needs to be rent') ? 'checked' : ''; ?>>
                                    <label for="rent-needs" class="toggle-option-label">Needs to be Rent</label>
                                </div>
                            </div>
                        </div>
                        
                        <div id="lease-options" class="mb-3 conditional-field <?php echo (isset($subcategory['transaction_type']) && $subcategory['transaction_type'] === 'lease') ? 'd-block' : ''; ?>">
                            <label class="form-label">Lease Status</label>
                            <div class="toggle-container">
                                <div class="toggle-option">
                                    <input type="radio" id="lease-for" name="transaction_status" value="for lease" 
                                           <?php echo (isset($subcategory['transaction_status']) && $subcategory['transaction_status'] === 'for lease') ? 'checked' : ''; ?>>
                                    <label for="lease-for" class="toggle-option-label">For Lease</label>
                                </div>
                                <div class="toggle-option">
                                    <input type="radio" id="lease-needs" name="transaction_status" value="needs to be lease"
                                           <?php echo (isset($subcategory['transaction_status']) && $subcategory['transaction_status'] === 'needs to be lease') ? 'checked' : ''; ?>>
                                    <label for="lease-needs" class="toggle-option-label">Needs to be Lease</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_subcategory" class="btn btn-primary">Save Changes</button>
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
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Confirm Deletion</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <p>Are you sure you want to permanently delete this subcategory?</p>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <strong>Warning:</strong> This will also delete all custom fields and images associated with this subcategory!
                        </div>
                        <div class="text-center p-3 bg-light rounded">
                            <h5 class="mb-0"><?php echo htmlspecialchars($subcategory['name']); ?></h5>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="delete_subcategory" class="btn btn-danger">Delete Permanently</button>
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
        
        // Subcategory image preview
        document.querySelector('input[name="photo"]').addEventListener('change', function() {
            readURL(this, 'subcategoryPreview');
        });
        
        // Additional image preview
        document.querySelector('input[name="additional_image"]').addEventListener('change', function() {
            readURL(this, 'additionalImagePreview');
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
            
            document.querySelectorAll('.custom-field-item, .card').forEach(item => {
                observer.observe(item);
            });
            
            // Handle conditional form fields
            const rentOptions = document.getElementById('rent-options');
            const leaseOptions = document.getElementById('lease-options');
            
            document.querySelectorAll('input[name="transaction_type"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.value === 'rent') {
                        rentOptions.style.display = 'block';
                        leaseOptions.style.display = 'none';
                    } else if (this.value === 'lease') {
                        rentOptions.style.display = 'none';
                        leaseOptions.style.display = 'block';
                    }
                });
            });
        });
    </script>
</body>
</html>