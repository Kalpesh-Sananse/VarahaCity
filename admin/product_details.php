<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include('../includes/db_connection.php');

function getProductImages($conn, $product_id) {
    $sql = "SELECT id, image_path FROM product_images WHERE product_id = ? ORDER BY id ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $images = [];
    while ($row = $result->fetch_assoc()) {
        $images[] = $row;
    }
    
    return $images;
}

function tableExists($conn, $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    return $result->num_rows > 0;
}

// Check required tables
$required_tables = ['products', 'product_custom_fields', 'product_custom_field_values', 'product_images'];
$missing_tables = [];

foreach ($required_tables as $table) {
    if (!tableExists($conn, $table)) {
        $missing_tables[] = $table;
    }
}

if (!empty($missing_tables)) {
    die("Error: The following tables are missing: " . implode(", ", $missing_tables) . 
        ". Please run the database setup script first.");
}

// Create upload directories
$directories = [
    "../uploads/products",
    "../uploads/product_images"
];

foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
}

// Get product ID
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch product details
$sql = "SELECT p.*, s.name as subcategory_name, s.id as subcategory_id, 
        c.name as category_name, c.id as category_id 
        FROM products p 
        JOIN subcategories s ON p.subcategory_id = s.id 
        JOIN categories c ON p.category_id = c.id 
        WHERE p.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    header("Location: view_categories.php");
    exit();
}

// Initialize variables with product data or defaults
$property_type = $product['property_type'] ?? 'other';
$property_status = $product['property_status'] ?? 'available';
$dimensions = $product['dimensions'] ?? '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_timestamp = date('Y-m-d H:i:s');

    // Handle product update
    if (isset($_POST['update_product'])) {
        // Validate required fields
        $required_fields = [
            'name' => 'Product Name',
            'property_type' => 'Property Type',
            'property_status' => 'Property Status'
        ];
        
        foreach ($required_fields as $field => $label) {
            if (empty($_POST[$field])) {
                $_SESSION['error_message'] = "$label is required";
                header("Location: product_details.php?id=$product_id");
                exit();
            }
        }

        // Get form data
        $name = $_POST['name'];
        $description = $_POST['description'] ?? '';
        $youtube_link = $_POST['youtube_link'] ?? '';
        $location_url = $_POST['location_url'] ?? '';
        $area = $_POST['area'] ?? '';
        $dimensions = $_POST['dimensions'] ?? '';
        $city = $_POST['city'] ?? '';
        $property_type = $_POST['property_type'];
        $property_status = $_POST['property_status'];
        // Get form data - make sure this exists
$taluku = $_POST['taluku'] ?? '';

        // Validate property type and status
        $allowed_types = ['buy', 'sale', 'rent', 'lease', 'other'];
        $allowed_statuses = ['available', 'sold'];
        
        if (!in_array($property_type, $allowed_types)) {
            $property_type = 'other';
        }
        
        if (!in_array($property_status, $allowed_statuses)) {
            $property_status = 'available';
        }

        // Handle image upload
        if (!empty($_FILES['photo']['name'])) {
            $target_dir = "../uploads/products/";
            $target_file = $target_dir . basename($_FILES['photo']['name']);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            
            $new_filename = uniqid() . '.' . $imageFileType;
            $target_path = $target_dir . $new_filename;
            
            $check = getimagesize($_FILES['photo']['tmp_name']);
            if ($check !== false) {
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_path)) {
                    $image = "uploads/products/" . $new_filename;
                }
            }
        } else {
            $image = $_POST['existing_photo'];
        }

        // Update product
        try {
            $sql = "UPDATE products 
            SET name = ?, description = ?, image = ?, youtube_link = ?, 
                location_url = ?, area = ?, taluku = ?, dimensions = ?, city = ?, 
                property_type = ?, property_status = ?,
                updated_at = ? 
            WHERE id = ?";
            $stmt = $conn->prepare($sql);
        // Then in your bind_param:
$stmt->bind_param("ssssssssssssi", 
$name, $description, $image, $youtube_link, 
$location_url, $area, $taluku, $dimensions, $city,
$property_type, 
$property_status,
$current_timestamp, 
$product_id
);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Product updated successfully!";
                header("Location: product_details.php?id=$product_id");
                exit();
            }
        } catch (mysqli_sql_exception $e) {
            $_SESSION['error_message'] = "Database error: " . $e->getMessage();
            header("Location: product_details.php?id=$product_id");
            exit();
        }
    }

    // Handle custom field creation
    if (isset($_POST['add_custom_field'])) {
        $field_title = $_POST['field_title'];
        
        $sql = "INSERT INTO product_custom_fields (product_id, title) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $product_id, $field_title);
        
        if ($stmt->execute()) {
            $field_id = $conn->insert_id;
            
            $options = explode("\n", $_POST['field_options']);
            foreach ($options as $option) {
                $option = trim($option);
                if (empty($option)) continue;
                
                $sql = "INSERT INTO product_custom_field_values (field_id, value) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("is", $field_id, $option);
                $stmt->execute();
            }
            
            $_SESSION['success_message'] = "Custom field added successfully!";
            header("Location: product_details.php?id=$product_id");
            exit();
        }
    }

    // Handle custom field update
    if (isset($_POST['update_field'])) {
        $field_id = $_POST['field_id'];
        $field_title = $_POST['field_title'];
        
        $sql = "UPDATE product_custom_fields SET title = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $field_title, $field_id);
        $stmt->execute();
        
        $sql = "DELETE FROM product_custom_field_values WHERE field_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $field_id);
        $stmt->execute();
        
        $options = explode("\n", $_POST['field_options']);
        foreach ($options as $option) {
            $option = trim($option);
            if (empty($option)) continue;
            
            $sql = "INSERT INTO product_custom_field_values (field_id, value) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $field_id, $option);
            $stmt->execute();
        }
        
        $_SESSION['success_message'] = "Custom field updated successfully!";
        header("Location: product_details.php?id=$product_id");
        exit();
    }

    // Handle image uploads
    if (isset($_POST['add_images'])) {
        $upload_success = false;
        $uploaded_count = 0;
        $target_dir = "../uploads/product_images/";
        
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        foreach ($_FILES['additional_images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['additional_images']['error'][$key] !== UPLOAD_ERR_OK || empty($tmp_name)) {
                continue;
            }

            $check = getimagesize($tmp_name);
            if ($check === false) continue;

            $file_ext = strtolower(pathinfo($_FILES['additional_images']['name'][$key], PATHINFO_EXTENSION));
            $new_filename = uniqid() . '.' . $file_ext;
            $target_path = $target_dir . $new_filename;

            if ($_FILES['additional_images']['size'][$key] <= 5 * 1024 * 1024) {
                if (move_uploaded_file($tmp_name, $target_path)) {
                    $image_path = "uploads/product_images/" . $new_filename;
                    
                    $sql = "INSERT INTO product_images (product_id, image_path) VALUES (?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("is", $product_id, $image_path);
                    if ($stmt->execute()) {
                        $uploaded_count++;
                        $upload_success = true;
                    }
                }
            }
        }

        if ($upload_success) {
            $_SESSION['success_message'] = "Successfully uploaded $uploaded_count image(s)!";
            $additional_images = getProductImages($conn, $product_id);
        } else {
            $_SESSION['error_message'] = "Failed to upload images. Please try again.";
        }

        header("Location: product_details.php?id=$product_id");
        exit();
    }

    // Handle image deletion
    if (isset($_POST['delete_image'])) {
        $image_id = intval($_POST['image_id']);
        
        $sql = "SELECT image_path FROM product_images WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $image_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $image_path = "../" . $row['image_path'];
            
            $sql = "DELETE FROM product_images WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $image_id);
            
            if ($stmt->execute()) {
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
                $_SESSION['success_message'] = "Image deleted successfully!";
            } else {
                $_SESSION['error_message'] = "Failed to delete image from database.";
            }
        } else {
            $_SESSION['error_message'] = "Image not found.";
        }
        
        header("Location: product_details.php?id=$product_id");
        exit();
    }

    // Handle custom field deletion
    if (isset($_POST['delete_field'])) {
        $field_id = $_POST['field_id'];
        
        $sql = "DELETE FROM product_custom_fields WHERE id = ? AND product_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $field_id, $product_id);
        $stmt->execute();
        
        $_SESSION['success_message'] = "Custom field deleted successfully!";
        header("Location: product_details.php?id=$product_id");
        exit();
    }

    // Handle contact details update
    if (isset($_POST['update_contact'])) {
        $contact_name = $_POST['contact_name'];
        $phone = $_POST['phone'];
        $whatsapp = $_POST['whatsapp'];
        $telephone = $_POST['telephone'];
        $email = $_POST['email'];
        $contact_hours = $_POST['contact_hours'];
        
        if ($contact_details) {
            $sql = "UPDATE product_contact_details 
                    SET contact_name = ?, phone = ?, whatsapp = ?, 
                        telephone = ?, email = ?, contact_hours = ?,
                        updated_at = ?
                    WHERE product_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssi", $contact_name, $phone, $whatsapp, 
                             $telephone, $email, $contact_hours,
                             $current_timestamp, $product_id);
        } else {
            $sql = "INSERT INTO product_contact_details 
                    (product_id, contact_name, phone, whatsapp, telephone, email, contact_hours) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("issssss", $product_id, $contact_name, $phone, 
                             $whatsapp, $telephone, $email, $contact_hours);
        }
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Contact details updated successfully!";
            header("Location: product_details.php?id=$product_id");
            exit();
        }
    }
}

// Fetch additional data
$sql = "SELECT * FROM product_custom_fields WHERE product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$custom_fields = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$additional_images = [];
if (tableExists($conn, 'product_images')) {
    $additional_images = getProductImages($conn, $product_id);
}

$contact_details = [];
if (tableExists($conn, 'product_contact_details')) {
    $sql = "SELECT * FROM product_contact_details WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $contact_details = $stmt->get_result()->fetch_assoc();
}

// Fetch cities for dropdown
$cities = [];
if (tableExists($conn, 'cities')) {
    $sql = "SELECT * FROM cities ORDER BY name ASC";
    $result = $conn->query($sql);
    $cities = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Product Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
    /* Alert Styles */
    .alert {
        border-radius: 8px;
        padding: 15px 20px;
        margin-bottom: 20px;
        border: none;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .alert-success {
        background-color: #d1fae5;
        color: #065f46;
    }

    .alert-danger {
        background-color: #fee2e2;
        color: #b91c1c;
    }

    .alert i {
        margin-right: 8px;
    }

    .btn-close {
        padding: 0.5rem 0.5rem;
        background-size: 0.75rem;
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

    .product-header {
        text-align: center;
        margin-bottom: 40px;
        position: relative;
        padding-bottom: 20px;
    }

    .product-header h1 {
        font-weight: 700;
        color: var(--dark-color);
        display: inline-block;
        position: relative;
    }

    .product-header h1::after {
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
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        display: inline-flex;
        margin-bottom: 30px;
    }

    .product-image-container {
        height: 350px;
        overflow: hidden;
        border-radius: 15px;
        margin-bottom: 25px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        position: relative;
    }

    .product-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .card {
        border-radius: 15px;
        border: none;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        margin-bottom: 25px;
        overflow: hidden;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    }

    .card-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        font-weight: 600;
        border-radius: 15px 15px 0 0 !important;
        padding: 15px 20px;
    }

    .form-control,
    .form-select {
        border-radius: 10px;
        padding: 12px 15px;
        border: 1px solid #ddd;
        box-shadow: none;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(41, 128, 185, 0.2);
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        border: none;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
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
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        aspect-ratio: 1/1;
    }

    .gallery-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s;
    }

    .delete-image-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        background-color: rgba(0, 0, 0, 0.6);
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

    .gallery-item:hover .gallery-img {
        transform: scale(1.05);
    }

    .gallery-item:hover .delete-image-btn {
        opacity: 1;
    }

    .custom-field-item {
        background-color: white;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 15px;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
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

    @media (max-width: 768px) {
        .product-image-container {
            height: 250px;
        }

        .gallery-container {
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        }
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .card,
    .custom-field-item {
        animation: fadeIn 0.5s ease-out forwards;
    }

    /* Add these styles to your existing CSS */
    .file-upload {
        position: relative;
        width: 100%;
        margin-bottom: 20px;
    }

    .file-upload-btn {
        border: 2px dashed #ddd;
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        display: block;
        width: 100%;
        background: #f8f9fa;
        transition: all 0.3s ease;
    }

    .file-upload-btn:hover {
        border-color: var(--primary-color);
        background: #f1f8fe;
    }

    .file-upload-input {
        position: absolute;
        width: 0;
        height: 0;
        opacity: 0;
    }

    .preview-image {
        max-width: 200px;
        max-height: 200px;
        border-radius: 8px;
        margin-top: 15px;
        display: none;
        margin: 15px auto 0;
    }

    /* Add product details section styles */
    .product-details-section {
        background: white;
        border-radius: 15px;
        padding: 25px;
        margin-top: 30px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    }

    .detail-row {
        display: flex;
        border-bottom: 1px solid #eee;
        padding: 15px 0;
    }

    .detail-label {
        width: 150px;
        font-weight: 600;
        color: var(--dark-color);
    }

    .detail-value {
        flex: 1;
        color: #666;
    }

    .map-link {
        color: var(--primary-color);
        text-decoration: none;
    }

    .map-link:hover {
        text-decoration: underline;
    }

    /* Product Details Tabs Styling */
    .nav-tabs {
        border: none;
        margin-bottom: -1px;
    }

    .nav-tabs .nav-link {
        border: none;
        color: #666;
        padding: 15px 25px;
        font-weight: 500;
        border-radius: 10px 10px 0 0;
        transition: all 0.3s ease;
    }

    .nav-tabs .nav-link:hover {
        color: var(--primary-color);
    }

    .nav-tabs .nav-link.active {
        color: var(--primary-color);
        background: white;
        border-bottom: 3px solid var(--primary-color);
    }

    .specifications-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }

    .spec-group {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 15px;
    }

    .spec-title {
        color: var(--dark-color);
        font-weight: 600;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #dee2e6;
    }

    .spec-values {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .spec-badge {
        background: white;
        color: #666;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.9rem;
        border: 1px solid #dee2e6;
    }

    .detail-card {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        height: 100%;
    }

    .detail-card-title {
        color: var(--dark-color);
        font-weight: 600;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #dee2e6;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px dashed #dee2e6;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        color: #666;
        font-weight: 500;
    }

    .info-value {
        color: var(--dark-color);
    }

    .map-container {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    }

    .contact-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
        padding: 10px;
    }

    .contact-item {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 15px;
        background: white;
        border-radius: 10px;
        transition: all 0.3s ease;
    }

    .contact-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    }

    .contact-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--primary-color);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }

    .contact-info {
        flex: 1;
    }

    .contact-info label {
        display: block;
        font-size: 0.8rem;
        color: #666;
        margin-bottom: 2px;
    }

    .contact-info span {
        color: var(--dark-color);
        font-weight: 500;
    }

    .contact-info a {
        color: var(--primary-color);
        text-decoration: none;
    }

    .contact-info a:hover {
        text-decoration: underline;
    }

    /* Add these new styles after your existing CSS */

    /* Modern Card Styling */
    .card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.06);
        margin-bottom: 30px;
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
    }

    .card-header {
        background: linear-gradient(135deg, #2980b9, #6dd5fa);
        padding: 20px;
        border-bottom: none;
    }

    /* Image Container Enhancement */
    .product-image-container {
        height: 400px;
        border-radius: 20px;
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        position: relative;
    }

    .product-image-container::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(to bottom, rgba(0, 0, 0, 0) 70%, rgba(0, 0, 0, 0.1));
        pointer-events: none;
    }

    /* Form Controls Enhancement */
    .form-control,
    .form-select {
        padding: 15px;
        border-radius: 12px;
        border: 2px solid #eef2f7;
        font-size: 0.95rem;
        transition: all 0.3s ease;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #2980b9;
        box-shadow: 0 0 0 4px rgba(41, 128, 185, 0.1);
    }

    /* Button Styling */
    .btn {
        padding: 12px 25px;
        border-radius: 12px;
        font-weight: 500;
        letter-spacing: 0.3px;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background: linear-gradient(135deg, #2980b9, #6dd5fa);
        border: none;
        box-shadow: 0 4px 15px rgba(41, 128, 185, 0.2);
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #2472a4, #5bc4e8);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(41, 128, 185, 0.3);
    }

    /* Tab Navigation Enhancement */
    .nav-tabs {
        border-bottom: none;
        gap: 10px;
        padding: 0 10px;
    }

    .nav-tabs .nav-link {
        border: none;
        padding: 15px 30px;
        border-radius: 15px;
        font-weight: 500;
        color: #64748b;
        background: #f1f5f9;
        transition: all 0.3s ease;
    }

    .nav-tabs .nav-link.active {
        color: white;
        background: linear-gradient(135deg, #2980b9, #6dd5fa);
        box-shadow: 0 4px 15px rgba(41, 128, 185, 0.2);
    }

    /* Specification Grid Enhancement */
    .specifications-grid {
        gap: 25px;
        padding: 20px;
    }

    .spec-group {
        background: white;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }

    .spec-group:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
    }

    .spec-title {
        font-size: 1.1rem;
        color: #1e293b;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f1f5f9;
    }

    .spec-badge {
        background: #f8fafc;
        color: #475569;
        padding: 8px 16px;
        border-radius: 25px;
        font-size: 0.9rem;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
    }

    .spec-badge:hover {
        background: #2980b9;
        color: white;
        border-color: #2980b9;
    }

    /* Detail Card Enhancement */
    .detail-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        height: 100%;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    }

    .detail-card-title {
        font-size: 1.2rem;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f1f5f9;
    }

    /* Contact Grid Enhancement */
    .contact-grid {
        gap: 25px;
        padding: 20px 10px;
    }

    .contact-item {
        background: white;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    }

    .contact-icon {
        width: 45px;
        height: 45px;
        background: linear-gradient(135deg, #2980b9, #6dd5fa);
        box-shadow: 0 4px 15px rgba(41, 128, 185, 0.2);
    }

    /* Image Upload Enhancement */
    .file-upload-btn {
        border: 2px dashed #cbd5e1;
        border-radius: 15px;
        padding: 30px;
        background: #f8fafc;
    }

    .file-upload-btn:hover {
        border-color: #2980b9;
        background: #f0f9ff;
    }

    .preview-image {
        max-width: 250px;
        max-height: 250px;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    /* Animation Enhancement */
    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .card,
    .spec-group,
    .detail-card,
    .contact-item {
        animation: slideUp 0.5s ease forwards;
    }

    /* Property Type Toggle Styling */
    .btn-check:checked+.btn-outline-success {
        background-color: #198754;
        color: white;
    }

    .btn-check:checked+.btn-outline-warning {
        background-color: #ffc107;
        color: #000;
    }

    .form-check-input:checked {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }

    .btn-group {
        gap: 10px;
    }

    .btn-group>.btn {
        border-radius: 8px !important;
        flex: 1;
        padding: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
    }

    /* Property Type Badges */
    .property-badges {
        position: absolute;
        top: 15px;
        left: 15px;
        display: flex;
        gap: 8px;
    }

    .badge-property {
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .badge-buy {
        background: linear-gradient(135deg, #4CAF50, #45a049);
        color: white;
    }

    .badge-sale {
        background: linear-gradient(135deg, #2196F3, #1976D2);
        color: white;
    }

    .badge-rent {
        background: linear-gradient(135deg, #FF9800, #F57C00);
        color: white;
    }

    .badge-lease {
        background: linear-gradient(135deg, #9C27B0, #7B1FA2);
        color: white;
    }

    /* Property Options Styling */
    .property-options {
        --toggle-height: 45px;
        --toggle-radius: 12px;
        --toggle-gap: 10px;
        --toggle-transition: all 0.3s ease;
    }

    .option-group {
        margin-bottom: 1.5rem;
    }

    .property-toggle,
    .status-toggle {
        display: flex;
        gap: var(--toggle-gap);
        width: 100%;
    }

    .btn-toggle {
        flex: 1;
        height: var(--toggle-height);
        border: 2px solid #e2e8f0;
        border-radius: var(--toggle-radius);
        background: white;
        color: #64748b;
        font-weight: 500;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        cursor: pointer;
        transition: var(--toggle-transition);
        padding: 0 15px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .btn-toggle i {
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .btn-check:checked+.btn-toggle {
        border-color: var(--primary-color);
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        box-shadow: 0 4px 15px rgba(41, 128, 185, 0.2);
    }

    /* Status-specific styles */
    .status-available {
        border-color: #22c55e;
        color: #22c55e;
    }

    .status-needs {
        border-color: #eab308;
        color: #eab308;
    }

    .btn-check:checked+.status-available {
        background: linear-gradient(135deg, #22c55e, #16a34a);
        border-color: #22c55e;
        color: white;
    }

    .btn-check:checked+.status-needs {
        background: linear-gradient(135deg, #eab308, #ca8a04);
        border-color: #eab308;
        color: white;
    }

    /* Property Badges */
    .property-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 500;
        margin: 4px;
    }

    .badge-buy {
        background: linear-gradient(135deg, #22c55e, #16a34a);
        color: white;
    }

    .badge-sale {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: white;
    }

    .badge-rent {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: white;
    }

    .badge-lease {
        background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        color: white;
    }

    /* Main Container Styles */
    .product-details-section {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        margin-top: 2rem;
        overflow: hidden;
    }

    /* Tab Navigation Styles */
    .custom-tabs {
        background: #f8fafc;
        padding: 15px 15px 0;
        border: none;
        gap: 10px;
        display: flex;
        flex-wrap: wrap;
    }

    .custom-tabs .nav-item {
        margin: 0;
    }

    .custom-tabs .nav-link {
        border: none;
        background: #ffffff;
        color: #64748b;
        padding: 15px 25px;
        border-radius: 12px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s ease;
    }

    .custom-tabs .nav-link i {
        font-size: 1.1rem;
    }

    .custom-tabs .nav-link:hover {
        color: #2980b9;
        background: #f0f9ff;
    }

    .custom-tabs .nav-link.active {
        background: linear-gradient(135deg, #2980b9, #6dd5fa);
        color: white;
        box-shadow: 0 4px 15px rgba(41, 128, 185, 0.2);
    }

    /* Tab Content Styles */
    .custom-tab-content {
        padding: 30px;
    }

    /* Specifications Grid */
    .specifications-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 25px;
        margin-bottom: 40px;
    }

    .spec-card {
        background: white;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }

    .spec-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
    }

    .spec-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f1f5f9;
    }

    .spec-header i {
        color: #2980b9;
        font-size: 1.2rem;
    }

    .spec-header h6 {
        margin: 0;
        font-size: 1.1rem;
        color: #1e293b;
        font-weight: 600;
    }

    .spec-values {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .spec-badge {
        background: #f8fafc;
        color: #475569;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }

    .spec-badge:hover {
        background: #2980b9;
        color: white;
    }

    /* Contact Section Styles */
    .contact-section {
        margin-top: 40px;
    }

    .section-title {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 30px;
    }

    .section-title i {
        font-size: 1.8rem;
        color: #2980b9;
    }

    .section-title h3 {
        margin: 0;
        font-size: 1.5rem;
        color: #1e293b;
        font-weight: 600;
    }

    .contact-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
    }

    .contact-card {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 20px;
        background: #f8fafc;
        border-radius: 15px;
        transition: all 0.3s ease;
    }

    .contact-card:hover {
        transform: translateY(-3px);
        background: white;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
    }

    .contact-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        color: white;
    }

    .contact-icon.person {
        background: linear-gradient(135deg, #2980b9, #6dd5fa);
    }

    .contact-icon.phone {
        background: linear-gradient(135deg, #4CAF50, #45a049);
    }

    .contact-icon.whatsapp {
        background: linear-gradient(135deg, #25D366, #128C7E);
    }

    .contact-icon.email {
        background: linear-gradient(135deg, #FF9800, #F57C00);
    }

    .contact-icon.hours {
        background: linear-gradient(135deg, #9C27B0, #7B1FA2);
    }

    .contact-info {
        flex: 1;
    }

    .contact-info label {
        display: block;
        font-size: 0.85rem;
        color: #64748b;
        margin-bottom: 4px;
    }

    .contact-info span,
    .contact-info a {
        color: #1e293b;
        font-weight: 500;
        font-size: 1rem;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .contact-info a:hover {
        color: #2980b9;
    }

    /* Details Tab Styles */
    .details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 25px;
    }

    .detail-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }

    .detail-card .card-header {
        background: linear-gradient(135deg, #2980b9, #6dd5fa);
        color: white;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .card-header i {
        font-size: 1.3rem;
    }

    .card-header h3 {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 500;
    }

    .card-content {
        padding: 20px;
    }

    .info-item {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px dashed #e2e8f0;
    }

    .info-item:last-child {
        border-bottom: none;
    }

    .info-item .label {
        color: #64748b;
        font-weight: 500;
    }

    .info-item .value {
        color: #1e293b;
        font-weight: 600;
    }

    /* Location Tab Styles */
    .map-container {
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-state i {
        font-size: 3rem;
        color: #cbd5e1;
        margin-bottom: 15px;
    }

    .empty-state p {
        color: #64748b;
        font-size: 1.1rem;
        margin: 0;
    }

    /* Responsive Styles */
    @media (max-width: 768px) {
        .custom-tab-content {
            padding: 20px;
        }

        .custom-tabs .nav-link {
            padding: 12px 20px;
            font-size: 0.9rem;
        }

        .specifications-grid,
        .contact-grid,
        .details-grid {
            grid-template-columns: 1fr;
        }

        .section-title {
            flex-direction: column;
            text-align: center;
        }

        .contact-card {
            padding: 15px;
        }
    }

    @media (max-width: 576px) {
        .custom-tabs {
            padding: 10px 10px 0;
        }

        .custom-tabs .nav-link {
            padding: 10px 15px;
            font-size: 0.85rem;
            width: 100%;
            justify-content: center;
        }

        .nav-item {
            width: 100%;
        }

        .spec-header {
            flex-direction: column;
            text-align: center;
        }

        .contact-card {
            flex-direction: column;
            text-align: center;
        }

        .contact-info label {
            margin-top: 10px;
        }
    }

    /* Animation Effects */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .tab-pane.fade.show {
        animation: fadeIn 0.3s ease-out forwards;
    }

    /* Custom Scrollbar */
    .custom-tab-content {
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 #f8fafc;
    }

    .custom-tab-content::-webkit-scrollbar {
        width: 6px;
    }

    .custom-tab-content::-webkit-scrollbar-track {
        background: #f8fafc;
    }

    .custom-tab-content::-webkit-scrollbar-thumb {
        background-color: #cbd5e1;
        border-radius: 3px;
    }

    /* Professional Product Details Section */
    .product-details-section {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
        margin: 1rem 0;
    }

    /* Streamlined Tab Navigation */
    .nav-tabs {
        display: flex;
        background: #f8fafc;
        padding: 10px 10px 0;
        border: none;
        gap: 1px;
    }

    .nav-tabs .nav-item {
        flex: 1;
    }

    .nav-tabs .nav-link {
        width: 100%;
        padding: 12px 16px;
        color: #64748b;
        font-weight: 500;
        font-size: 14px;
        border: none;
        border-radius: 8px 8px 0 0;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: all 0.2s ease;
        background: transparent;
    }

    .nav-tabs .nav-link i {
        font-size: 16px;
    }

    .nav-tabs .nav-link.active {
        color: var(--primary-color);
        background: #ffffff;
        font-weight: 600;
    }

    /* Compact Tab Content */
    .tab-content {
        padding: 15px;
    }

    /* Efficient Specifications Grid */
    .specifications-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 15px;
    }

    .spec-group {
        background: #ffffff;
        border-radius: 8px;
        padding: 15px;
        border: 1px solid #e2e8f0;
    }

    .spec-title {
        font-size: 15px;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .spec-values {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .spec-badge {
        background: #f8fafc;
        color: #475569;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 13px;
        border: 1px solid #e2e8f0;
    }

    /* Compact Contact Grid */
    .contact-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 15px;
        margin-top: 15px;
    }

    .contact-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        background: #ffffff;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
    }

    .contact-icon {
        width: 40px;
        height: 40px;
        min-width: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        color: #ffffff;
    }

    .contact-info {
        flex: 1;
        min-width: 0;
    }

    .contact-info label {
        display: block;
        font-size: 12px;
        color: #64748b;
        margin-bottom: 2px;
    }

    .contact-info span,
    .contact-info a {
        display: block;
        font-size: 14px;
        font-weight: 500;
        color: #1e293b;
        text-decoration: none;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .nav-tabs {
            overflow-x: auto;
            flex-wrap: nowrap;
            scrollbar-width: none;
            -webkit-overflow-scrolling: touch;
            padding: 8px 8px 0;
        }

        .nav-tabs::-webkit-scrollbar {
            display: none;
        }

        .nav-tabs .nav-item {
            flex: 0 0 auto;
        }

        .nav-tabs .nav-link {
            padding: 10px 14px;
            white-space: nowrap;
        }

        .tab-content {
            padding: 12px;
        }

        .specifications-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .contact-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }
    }

    @media (max-width: 576px) {
        .product-details-section {
            margin: 0.5rem 0;
            border-radius: 8px;
        }

        .nav-tabs .nav-link {
            font-size: 13px;
        }

        .spec-group,
        .contact-item {
            padding: 12px;
        }

        .contact-icon {
            width: 36px;
            height: 36px;
            min-width: 36px;
            font-size: 15px;
        }
    }

    /* Icon Gradients */
    .contact-icon.phone {
        background: linear-gradient(135deg, #4CAF50, #45a049);
    }

    .contact-icon.whatsapp {
        background: linear-gradient(135deg, #25D366, #128C7E);
    }

    .contact-icon.email {
        background: linear-gradient(135deg, #FF9800, #F57C00);
    }

    .contact-icon.person {
        background: linear-gradient(135deg, #2980b9, #6dd5fa);
    }

    .contact-icon.hours {
        background: linear-gradient(135deg, #9C27B0, #7B1FA2);
    }

    /* Gallery Styles */
    .gallery-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 15px;
    }

    .gallery-item {
        position: relative;
        border-radius: 6px;
        overflow: hidden;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s;
    }

    .gallery-item:hover {
        transform: translateY(-3px);
    }

    .gallery-img {
        width: 100%;
        height: 150px;
        object-fit: cover;
        display: block;
    }

    .delete-image-btn {
        position: absolute;
        top: 5px;
        right: 5px;
        background: rgba(255, 0, 0, 0.7);
        color: white;
        border: none;
        border-radius: 50%;
        width: 28px;
        height: 28px;
        font-size: 12px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }

    .delete-image-btn:hover {
        background: rgba(255, 0, 0, 1);
        transform: scale(1.1);
    }

    /* Preview specific styles */
    .preview-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 15px;
        margin-top: 15px;
    }

    .preview-item {
        border: 1px dashed #ccc;
    }

    .preview-img {
        opacity: 0.8;
        height: 150px;
        object-fit: cover;
    }
    </style>
</head>

<body>
    <!-- Message Display Section -->
    <?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <?php echo $_SESSION['success_message']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        <?php echo $_SESSION['error_message']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
    <?php include 'navbar.php'; ?>
    <div class="container py-4 py-lg-5">
        <div class="product-header">

            <h1><i class="fas fa-box me-2"></i><?php echo htmlspecialchars($product['name']); ?></h1>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="product-image-container">
                    <img src="../<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars(
                            $product['name']); ?>" class="product-image">
                </div>

                <?php if (!empty($product['youtube_link'])): ?>
                <div class="youtube-embed mb-4">
                    <?php
                        $youtube_id = '';
                        if (preg_match('/(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/'
                                     . '|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', 
                                     $product['youtube_link'], $matches)) {
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

                <!-- Existing Images Gallery -->
                <!-- Existing Images Gallery (keep your current code) -->
                <?php if (count($additional_images) > 0): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-images me-2"></i> Additional Images</h5>
                    </div>
                    <div class="card-body">
                        <div class="gallery-container">
                            <?php foreach($additional_images as $image): ?>
                            <div class="gallery-item">
                                <img src="../<?php echo htmlspecialchars($image['image_path']); ?>" class="gallery-img">
                                <form method="POST">
                                    <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                                    <button type="submit" name="delete_image" class="delete-image-btn"
                                        onclick="return confirm('Delete this image?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            </div>

            <div class="col-lg-6">
                <!-- Edit Product Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-edit me-2"></i> Edit Product</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Product Name</label>
                                <input type="text" name="name" class="form-control"
                                    value="<?php echo htmlspecialchars($product['name']); ?>" required>
                            </div>
                            <div class="row mb-4">
                                <!-- Property Type -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Property Type <span class="text-danger">*</span></label>
                                    <div class="d-flex flex-wrap gap-2">
                                        <?php 
        $types = [
            'buy' => ['label' => 'Buy', 'icon' => 'fa-shopping-cart'],
            'sale' => ['label' => 'Sale', 'icon' => 'fa-tag'],
            'rent' => ['label' => 'Rent', 'icon' => 'fa-key'],
            'lease' => ['label' => 'Lease', 'icon' => 'fa-file-contract'],
            'other' => ['label' => 'Other', 'icon' => 'fa-question']
        ];
        foreach ($types as $value => $data): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="property_type"
                                                id="type<?= ucfirst($value) ?>" value="<?= $value ?>"
                                                <?= ($property_type === $value) ? 'checked' : '' ?> required>
                                            <label class="form-check-label" for="type<?= ucfirst($value) ?>">
                                                <i class="fas <?= $data['icon'] ?> me-1"></i> <?= $data['label'] ?>
                                            </label>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Property Status -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Property Status <span class="text-danger">*</span></label>
                                    <div class="btn-group w-100" role="group">
                                        <input type="radio" class="btn-check" name="property_status"
                                            id="statusAvailable" value="available"
                                            <?= ($property_status === 'available') ? 'checked' : '' ?> required>
                                        <label class="btn btn-outline-success" for="statusAvailable">
                                            <i class="fas fa-check-circle me-2"></i> Available
                                        </label>

                                        <input type="radio" class="btn-check" name="property_status" id="statusSold"
                                            value="sold" <?= ($property_status === 'sold') ? 'checked' : '' ?> required>
                                        <label class="btn btn-outline-danger" for="statusSold">
                                            <i class="fas fa-times-circle me-2"></i> Sold Out
                                        </label>
                                    </div>
                                </div>


                            </div>
                            <div class="mb-3">
                                <label class="form-label">Product Image</label>
                                <input type="hidden" name="existing_photo"
                                    value="<?php echo htmlspecialchars($product['image']); ?>">

                                <div class="file-upload">
                                    <label class="file-upload-btn">
                                        <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i><br>
                                        <span>Click to change image</span>
                                        <span class="d-block small text-muted mt-1">
                                            (Leave blank to keep current image)
                                        </span>
                                        <input type="file" name="photo" class="file-upload-input" accept="image/*">
                                        <img src="../<?php echo htmlspecialchars($product['image']); ?>"
                                            class="preview-image" id="productPreview" style="display: block;">
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">YouTube Link (optional)</label>
                                <input type="url" name="youtube_link" class="form-control"
                                    placeholder="https://youtube.com/..."
                                    value="<?php echo htmlspecialchars($product['youtube_link'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="5"
                                    placeholder="Enter detailed description..."><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">YouTube Link (optional)</label>
                                <!-- Existing YouTube link field -->
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Location URL (optional)</label>
                                <input type="url" name="location_url" class="form-control"
                                    placeholder="https://maps.google.com/..."
                                    value="<?php echo htmlspecialchars($product['location_url'] ?? ''); ?>">
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Area</label>
                                    <input type="text" name="area" class="form-control" placeholder="local area"
                                        value="<?php echo htmlspecialchars($product['area'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Taluku</label>
                                    <input type="text" name="taluku" class="form-control" placeholder="taluku name"
                                        value="<?php echo htmlspecialchars($product['taluku'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Dimensions</label>
                                    <input type="text" name="dimensions" class="form-control"
                                        value="<?= htmlspecialchars($product['dimensions'] ?? '') ?>"
                                        placeholder="e.g., 20x30 ft">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">City</label>
                                    <select name="city" class="form-select" required>
                                        <option value="">Select City</option>
                                        <?php
        $cities = $conn->query("SELECT * FROM cities ORDER BY name ASC");
        while($city = $cities->fetch_assoc()): ?>
                                        <option value="<?= htmlspecialchars($city['name']) ?>"
                                            <?= ($product['city'] === $city['name']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($city['name']) ?>
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>

                            <button type="submit" name="update_product" class="btn btn-primary w-100 py-2">
                                <i class="fas fa-save me-2"></i> Save Changes
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Add Multiple Images -->

                <!-- New Multiple Image Upload Section -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i> Add Additional Images</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data" id="multiUploadForm">
                            <div class="mb-3">
                                <div class="file-upload">
                                    <label class="file-upload-btn">
                                        <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i><br>
                                        <span>Click to upload images</span>
                                        <span class="d-block small text-muted mt-1">(JPEG, PNG, max 5MB each)</span>
                                        <input type="file" name="additional_images[]" id="multiImageInput"
                                            class="file-upload-input" accept="image/*" multiple required>
                                    </label>
                                </div>
                                <!-- Preview container for selected images -->
                                <div id="imagePreviewContainer" class="preview-container mt-3"></div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="button" id="clearSelectionBtn" class="btn btn-outline-danger flex-grow-1"
                                    disabled>
                                    <i class="fas fa-times me-2"></i>Clear Selection
                                </button>
                                <button type="submit" name="add_images" id="uploadBtn"
                                    class="btn btn-primary flex-grow-1" disabled>
                                    <i class="fas fa-upload me-2"></i>Upload All
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Add Custom Field -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i> Add Custom Field</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Field Title</label>
                                <input type="text" name="field_title" class="form-control" placeholder="e.g. Features"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Field Options (one per line)</label>
                                <textarea name="field_options" class="form-control" rows="5"
                                    placeholder="Swimming Pool&#10;Garage&#10;Garden" required></textarea>
                            </div>


                            <button type="submit" name="add_custom_field" class="btn btn-primary w-100 py-2">
                                <i class="fas fa-save me-2"></i> Add Field
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="product-details-section">
            <!-- Tab Navigation -->
            <div class="tab-navigation">
                <ul class="nav nav-tabs custom-tabs" id="productTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="specs-tab" data-bs-toggle="tab" data-bs-target="#specs"
                            type="button" role="tab">
                            <i class="fas fa-list-ul"></i>
                            <span>Specifications</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="details-tab" data-bs-toggle="tab" data-bs-target="#details"
                            type="button" role="tab">
                            <i class="fas fa-info-circle"></i>
                            <span>Details</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="location-tab" data-bs-toggle="tab" data-bs-target="#location"
                            type="button" role="tab">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Location</span>
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Tab Content -->
            <div class="tab-content custom-tab-content" id="productTabsContent">
                <!-- Specifications Tab -->
                <div class="tab-pane fade show active" id="specs" role="tabpanel">
                    <div class="specifications-wrapper">
                        <?php if (count($custom_fields) > 0): ?>
                        <div class="specifications-grid">
                            <?php foreach($custom_fields as $field): 
                            $sql = "SELECT * FROM product_custom_field_values WHERE field_id = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("i", $field['id']);
                            $stmt->execute();
                            $values = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                        ?>
                            <div class="spec-card">
                                <div class="spec-header">
                                    <i class="fas fa-check-circle"></i>
                                    <h6><?php echo htmlspecialchars($field['title']); ?></h6>
                                </div>
                                <div class="spec-values">
                                    <?php foreach($values as $value): ?>
                                    <span class="spec-badge">
                                        <?php echo htmlspecialchars($value['value']); ?>
                                    </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-clipboard"></i>
                            <p>No specifications added yet</p>
                        </div>
                        <?php endif; ?>

                        <?php if ($contact_details): ?>
                        <div class="contact-section">
                            <div class="section-title">
                                <i class="fas fa-address-card"></i>
                                <h3>Contact Information</h3>
                            </div>
                            <div class="contact-grid">
                                <!-- Contact Person -->
                                <?php if (!empty($contact_details['contact_name'])): ?>
                                <div class="contact-card">
                                    <div class="contact-icon person">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="contact-info">
                                        <label>Contact Person</label>
                                        <span><?php echo htmlspecialchars($contact_details['contact_name']); ?></span>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <!-- Phone -->
                                <?php if (!empty($contact_details['phone'])): ?>
                                <div class="contact-card">
                                    <div class="contact-icon phone">
                                        <i class="fas fa-phone"></i>
                                    </div>
                                    <div class="contact-info">
                                        <label>Phone</label>
                                        <a href="tel:<?php echo htmlspecialchars($contact_details['phone']); ?>">
                                            <?php echo htmlspecialchars($contact_details['phone']); ?>
                                        </a>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <!-- WhatsApp -->
                                <?php if (!empty($contact_details['whatsapp'])): ?>
                                <div class="contact-card">
                                    <div class="contact-icon whatsapp">
                                        <i class="fab fa-whatsapp"></i>
                                    </div>
                                    <div class="contact-info">
                                        <label>WhatsApp</label>
                                        <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $contact_details['whatsapp']); ?>"
                                            target="_blank">
                                            <?php echo htmlspecialchars($contact_details['whatsapp']); ?>
                                        </a>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <!-- Email -->
                                <?php if (!empty($contact_details['email'])): ?>
                                <div class="contact-card">
                                    <div class="contact-icon email">
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                    <div class="contact-info">
                                        <label>Email</label>
                                        <a href="mailto:<?php echo htmlspecialchars($contact_details['email']); ?>">
                                            <?php echo htmlspecialchars($contact_details['email']); ?>
                                        </a>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <!-- Available Hours -->
                                <?php if (!empty($contact_details['contact_hours'])): ?>
                                <div class="contact-card">
                                    <div class="contact-icon hours">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div class="contact-info">
                                        <label>Available Hours</label>
                                        <span><?php echo htmlspecialchars($contact_details['contact_hours']); ?></span>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Details Tab -->
                <div class="tab-pane fade" id="details" role="tabpanel">
                    <div class="details-grid">
                        <div class="detail-card property-info">
                            <div class="card-header">
                                <i class="fas fa-building"></i>
                                <h3>Property Information</h3>
                            </div>
                            <div class="card-content">
                                <div class="info-item">
                                    <span class="label">Category</span>
                                    <span
                                        class="value"><?php echo htmlspecialchars($product['category_name']); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="label">Type</span>
                                    <span
                                        class="value"><?php echo htmlspecialchars($product['subcategory_name']); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="label">Area</span>
                                    <span
                                        class="value"><?php echo htmlspecialchars($product['area'] ?? 'Not specified'); ?></span>
                                </div>

                                <div class="info-item">
                                    <span class="label">Taluku</span>
                                    <span
                                        class="value"><?php echo htmlspecialchars($product['taluku'] ?? 'Not specified'); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="label">City</span>
                                    <span
                                        class="value"><?php echo htmlspecialchars($product['city'] ?? 'Not specified'); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="detail-card listing-info">
                            <div class="card-header">
                                <i class="fas fa-clock"></i>
                                <h3>Listing Information</h3>
                            </div>
                            <div class="card-content">
                                <div class="info-item">
                                    <span class="label">Listed On</span>
                                    <span
                                        class="value"><?php echo date('F j, Y', strtotime($product['created_at'])); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="label">Last Updated</span>
                                    <span
                                        class="value"><?php echo date('F j, Y', strtotime($product['updated_at'])); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="label">Property ID</span>
                                    <span
                                        class="value">#<?php echo str_pad($product['id'], 6, '0', STR_PAD_LEFT); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Location Tab -->
                <div class="tab-pane fade" id="location" role="tabpanel">
                    <?php if (!empty($product['location_url'])): ?>
                    <div class="map-container">
                        <div class="ratio ratio-16x9">
                            <?php
                        $map_url = $product['location_url'];
                        if (strpos($map_url, 'google.com/maps') !== false) {
                            $map_url = str_replace('maps?', 'maps/embed?', $map_url);
                        }
                        ?>
                            <iframe src="<?php echo htmlspecialchars($map_url); ?>" allowfullscreen=""
                                loading="lazy"></iframe>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-map-marker-alt"></i>
                        <p>No location map available</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- Custom Fields Section -->
        <?php if (count($custom_fields) > 0): ?>
        <div class="mt-5">
            <h3 class="mb-4"><i class="fas fa-list-ul me-2"></i> Custom Fields</h3>
            <div class="row">
                <?php foreach($custom_fields as $field): 
            $values = $conn->query("SELECT * FROM product_custom_field_values 
                                  WHERE field_id = {$field['id']}")->fetch_all(MYSQLI_ASSOC);
        ?>
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><?= htmlspecialchars($field['title']) ?></h5>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-primary edit-field-btn"
                                    data-field-id="<?= $field['id'] ?>"
                                    data-title="<?= htmlspecialchars($field['title']) ?>"
                                    data-values="<?= htmlspecialchars(json_encode(array_column($values, 'value'))) ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST">
                                    <input type="hidden" name="field_id" value="<?= $field['id'] ?>">
                                    <button type="submit" name="delete_field" class="btn btn-sm btn-danger"
                                        onclick="return confirm('Delete this field?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php foreach($values as $value): ?>
                            <span class="badge bg-secondary me-2 mb-2">
                                <?= htmlspecialchars($value['value']) ?>
                            </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Edit Field Modal -->
        <div class="modal fade" id="editFieldModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" id="editFieldForm">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Custom Field</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="field_id" id="editFieldId">
                            <div class="mb-3">
                                <label class="form-label">Field Title</label>
                                <input type="text" name="field_title" id="editFieldTitle" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Field Options (one per line)</label>
                                <textarea name="field_options" id="editFieldOptions" class="form-control" rows="5"
                                    required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="update_field" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const editButtons = document.querySelectorAll('.edit-field-btn');
            const editModal = new bootstrap.Modal('#editFieldModal');

            editButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    const fieldId = this.dataset.fieldId;
                    const title = this.dataset.title;
                    const values = JSON.parse(this.dataset.values);

                    document.getElementById('editFieldId').value = fieldId;
                    document.getElementById('editFieldTitle').value = title;
                    document.getElementById('editFieldOptions').value = values.join("\n");

                    editModal.show();
                });
            });
        });
        </script>
        <?php endif; ?>

        <!-- Contact Details Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-address-card me-2"></i>Contact Details</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contact Person Name</label>
                            <input type="text" name="contact_name" class="form-control"
                                value="<?php echo htmlspecialchars($contact_details['contact_name'] ?? ''); ?>"
                                placeholder="e.g., John Doe">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control"
                                value="<?php echo htmlspecialchars($contact_details['email'] ?? ''); ?>"
                                placeholder="e.g., john@example.com">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                <input type="tel" name="phone" class="form-control"
                                    value="<?php echo htmlspecialchars($contact_details['phone'] ?? ''); ?>"
                                    placeholder="e.g., +1 234 567 8900">
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">WhatsApp Number</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fab fa-whatsapp"></i></span>
                                <input type="tel" name="whatsapp" class="form-control"
                                    value="<?php echo htmlspecialchars($contact_details['whatsapp'] ?? ''); ?>"
                                    placeholder="e.g., +1 234 567 8900">
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Telephone</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-phone-square"></i></span>
                                <input type="tel" name="telephone" class="form-control"
                                    value="<?php echo htmlspecialchars($contact_details['telephone'] ?? ''); ?>"
                                    placeholder="e.g., +1 234 567 8900">
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contact Hours</label>
                            <input type="text" name="contact_hours" class="form-control"
                                value="<?php echo htmlspecialchars($contact_details['contact_hours'] ?? ''); ?>"
                                placeholder="e.g., Mon-Fri, 9 AM - 5 PM">
                        </div>
                    </div>

                    <button type="submit" name="update_contact" class="btn btn-primary w-100 py-2">
                        <i class="fas fa-save me-2"></i>Update Contact Details
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const multiImageInput = document.getElementById('multiImageInput');
        const previewContainer = document.getElementById('imagePreviewContainer');
        const clearSelectionBtn = document.getElementById('clearSelectionBtn');
        const uploadBtn = document.getElementById('uploadBtn');
        const multiUploadForm = document.getElementById('multiUploadForm');

        // Store selected files
        let selectedFiles = [];

        // Handle file selection
        multiImageInput.addEventListener('change', function(e) {
            const files = Array.from(e.target.files);

            // Filter valid images
            const validFiles = files.filter(file => {
                const isValidType = file.type.startsWith('image/');
                const isValidSize = file.size <= 5 * 1024 * 1024; // 5MB

                if (!isValidType) {
                    alert(
                        `"${file.name}" is not a valid image file. Only JPEG, PNG are allowed.`
                    );
                    return false;
                }

                if (!isValidSize) {
                    alert(`"${file.name}" exceeds the 5MB size limit.`);
                    return false;
                }

                return true;
            });

            // Add to selected files
            selectedFiles = [...selectedFiles, ...validFiles];
            updatePreview();
            updateButtons();
        });

        // Update image preview
        function updatePreview() {
            previewContainer.innerHTML = '';

            if (selectedFiles.length === 0) {
                multiImageInput.value = ''; // Reset file input
                return;
            }

            selectedFiles.forEach((file, index) => {
                const reader = new FileReader();

                reader.onload = function(e) {
                    const previewItem = document.createElement('div');
                    previewItem.className = 'gallery-item preview-item position-relative';

                    const img = document.createElement('img');
                    img.className = 'gallery-img preview-img w-100';
                    img.src = e.target.result;
                    img.alt = file.name;

                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'delete-image-btn';
                    removeBtn.innerHTML = '<i class="fas fa-trash-alt"></i>';
                    removeBtn.title = 'Remove this image';
                    removeBtn.onclick = () => removeImage(index);

                    previewItem.appendChild(img);
                    previewItem.appendChild(removeBtn);
                    previewContainer.appendChild(previewItem);
                };

                reader.readAsDataURL(file);
            });
        }

        // Remove image from selection
        function removeImage(index) {
            selectedFiles.splice(index, 1);
            updatePreview();
            updateButtons();
        }

        // Clear all selections
        clearSelectionBtn.addEventListener('click', function() {
            selectedFiles = [];
            updatePreview();
            updateButtons();
        });

        // Update button states
        function updateButtons() {
            const hasFiles = selectedFiles.length > 0;
            uploadBtn.disabled = !hasFiles;
            clearSelectionBtn.disabled = !hasFiles;
        }

        // Handle form submission
        multiUploadForm.addEventListener('submit', function(e) {
            if (selectedFiles.length === 0) {
                e.preventDefault();
                alert('Please select at least one image to upload.');
                return;
            }

            // Create a new DataTransfer to set files before upload
            const dataTransfer = new DataTransfer();
            selectedFiles.forEach(file => dataTransfer.items.add(file));
            multiImageInput.files = dataTransfer.files;
        });
    });
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

    // After successful upload, refresh the gallery
    function refreshImageGallery() {
        fetch(window.location.href, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newGallery = doc.querySelector('.gallery-container');

                if (newGallery) {
                    document.querySelector('.gallery-container').innerHTML = newGallery.innerHTML;
                }
            })
            .catch(error => console.error('Error refreshing gallery:', error));
    }

    // Modify your form submission handler
    multiUploadForm.addEventListener('submit', function(e) {
        if (selectedFiles.length === 0) {
            e.preventDefault();
            alert('Please select at least one image to upload.');
            return;
        }

        // Create a new DataTransfer to set files before upload
        const dataTransfer = new DataTransfer();
        selectedFiles.forEach(file => dataTransfer.items.add(file));
        multiImageInput.files = dataTransfer.files;

        // Submit the form normally - the page will refresh after upload
    });
    // Product image preview
    document.querySelector('input[name="photo"]').addEventListener('change', function() {
        readURL(this, 'productPreview');
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
        }, {
            threshold: 0.1
        });

        document.querySelectorAll('.card, .custom-field-item').forEach(item => {
            observer.observe(item);
        });
    });
    document.addEventListener('DOMContentLoaded', function() {
        const rentRadio = document.getElementById('typeRent');
        const leaseRadio = document.getElementById('typeLease');
        const statusNeedsLabel = document.querySelector('label[for="statusNeeds"]');

        function updateStatusLabel() {
            const text = rentRadio.checked ? 'Rented' : 'Leased';
            statusNeedsLabel.innerHTML = `<i class="fas fa-clock me-2"></i>Needs to be ${text}`;
        }

        rentRadio.addEventListener('change', updateStatusLabel);
        leaseRadio.addEventListener('change', updateStatusLabel);
    });
    document.querySelector('form').addEventListener('submit', function(e) {
        // Ensure property type is selected
        if (!document.querySelector('input[name="property_type"]:checked')) {
            e.preventDefault();
            alert('Please select a property type');
            return false;
        }

        // Ensure property status is selected
        if (!document.querySelector('input[name="property_status"]:checked')) {
            e.preventDefault();
            alert('Please select a property status');
            return false;
        }
    });
    </script>
</body>

</html>