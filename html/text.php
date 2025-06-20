<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include('./includes/db_connection.php');

// Validate product ID
$product_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$product_id) {
    header("Location: index.php");
    exit();
}

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
// Check if user is logged in and get wishlist status
$is_in_wishlist = false;
if (isset($_SESSION['user_id'])) {
    $wishlist_check_sql = "SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?";
    $wishlist_stmt = $conn->prepare($wishlist_check_sql);
    $wishlist_stmt->bind_param("ii", $_SESSION['user_id'], $product_id);
    $wishlist_stmt->execute();
    $wishlist_result = $wishlist_stmt->get_result();
    $is_in_wishlist = $wishlist_result->num_rows > 0;
    $wishlist_stmt->close();

    // Handle wishlist toggle
    if (isset($_POST['wishlist_toggle'])) {
        if ($is_in_wishlist) {
            // Remove from wishlist
            $remove_sql = "DELETE FROM wishlist WHERE user_id = ? AND product_id = ?";
            $stmt = $conn->prepare($remove_sql);
            $stmt->bind_param("ii", $_SESSION['user_id'], $product_id);
            $stmt->execute();
            $stmt->close();
            $is_in_wishlist = false;
        } else {
            // Add to wishlist
            $add_sql = "INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)";
            $stmt = $conn->prepare($add_sql);
            $stmt->bind_param("ii", $_SESSION['user_id'], $product_id);
            $stmt->execute();
            $stmt->close();
            $is_in_wishlist = true;
        }
        
        // Redirect to prevent form resubmission
        header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $product_id);
        exit();
    }
}

// Handle contact details update
if (isset($_POST['update_contact'])) {
    $contact_name = trim($_POST['contact_name']);
    $phone = trim($_POST['phone']);
    $whatsapp = trim($_POST['whatsapp']);
    $telephone = trim($_POST['telephone']);
    $email = trim($_POST['email']);
    $contact_hours = trim($_POST['contact_hours']);
    $current_timestamp = date('Y-m-d H:i:s');
    
    // Check if contact details already exist
    $check_sql = "SELECT id FROM product_contact_details WHERE product_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $product_id);
    $check_stmt->execute();
    $existing_contact = $check_stmt->get_result()->fetch_assoc();
    $check_stmt->close();
    
    if ($existing_contact) {
        // Update existing contact details
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
        // Insert new contact details
        $sql = "INSERT INTO product_contact_details 
                (product_id, contact_name, phone, whatsapp, telephone, email, contact_hours) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssss", $product_id, $contact_name, $phone, 
                         $whatsapp, $telephone, $email, $contact_hours);
    }
    
    if ($stmt->execute()) {
        $stmt->close();
        header("Location: product_details.php?id=" . $product_id);
        exit();
    } else {
        echo "Error updating contact details: " . $conn->error;
    }
}

// Fetch product details with contact information
$sql = "SELECT p.*, 
        c.name as category_name, 
        s.name as subcategory_name,
        pc.contact_name, pc.phone, pc.whatsapp, pc.telephone, pc.email, pc.contact_hours
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN subcategories s ON p.subcategory_id = s.id
        LEFT JOIN product_contact_details pc ON p.id = pc.product_id
        WHERE p.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    header("Location: index.php");
    exit();
}

// Fetch custom fields
$sql = "SELECT cf.id, cf.title, GROUP_CONCAT(cfv.value SEPARATOR '|') as values_list
        FROM product_custom_fields cf
        LEFT JOIN product_custom_field_values cfv ON cf.id = cfv.field_id
        WHERE cf.product_id = ?
        GROUP BY cf.id";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$custom_fields = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch additional images
$sql = "SELECT image_path FROM product_images WHERE product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$additional_images = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> | Property Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #1a365d;
            --secondary: #2b77e6;
            --accent: #e53e3e;
            --success: #38a169;
            --warning: #d69e2e;
            --light-gray: #f7fafc;
            --medium-gray: #e2e8f0;
            --dark-gray: #2d3748;
            --text-primary: #2d3748;
            --text-secondary: #718096;
            --border-radius: 12px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.1);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--text-primary);
            background: #ffffff;
            line-height: 1.6;
        }

        .container-fluid {
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Header Section */
        .property-header {
            background: linear-gradient(135deg, var(--primary) 0%, #2c5282 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }

        .property-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .property-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            align-items: center;
            margin-top: 1rem;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
            opacity: 0.9;
        }

        .price-display {
            background: rgba(255,255,255,0.15);
            padding: 1rem 1.5rem;
            border-radius: var(--border-radius);
            backdrop-filter: blur(10px);
            margin-top: 1rem;
            display: inline-block;
        }

        .price-amount {
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
        }

        /* Main Content Layout */
        .main-content {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        /* Image Gallery */
        .image-gallery {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow-md);
            margin-bottom: 2rem;
        }

        .main-image {
            width: 100%;
            height: 500px;
            object-fit: cover;
            cursor: zoom-in;
        }

        .thumbnail-strip {
            display: flex;
            gap: 0.5rem;
            padding: 1rem;
            background: var(--light-gray);
            overflow-x: auto;
        }

        .thumbnail {
            width: 80px;
            height: 60px;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .thumbnail:hover {
            border-color: var(--secondary);
            transform: scale(1.05);
        }

        .thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Property Details */
        .property-details {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            margin-bottom: 2rem;
        }

        .section-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--medium-gray);
            background: var(--light-gray);
            border-radius: var(--border-radius) var(--border-radius) 0 0;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-content {
            padding: 1.5rem;
        }

        /* Features Grid */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            background: var(--light-gray);
            border-radius: 8px;
            transition: background 0.2s ease;
        }

        .feature-item:hover {
            background: var(--medium-gray);
        }

        .feature-icon {
            color: var(--secondary);
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }

        /* Custom Fields */
        .custom-fields {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .field-group {
            border-left: 3px solid var(--secondary);
            padding-left: 1rem;
        }

        .field-title {
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .field-values {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .field-value {
            background: var(--light-gray);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        /* Sidebar */
        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .sidebar-section {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            overflow: hidden;
        }

        /* Action Buttons */
        .action-buttons {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .btn-primary {
            background: var(--secondary);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background: #1e5bb8;
            transform: translateY(-1px);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--medium-gray);
            color: var(--text-primary);
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-outline:hover {
            border-color: var(--secondary);
            color: var(--secondary);
        }

        .btn-danger {
            background: var(--accent);
            border: none;
            color: white;
        }

        .btn-danger:hover {
            background: #c53030;
        }

        /* Contact Section */
        .contact-info {
            padding: 1.5rem;
        }

        .contact-person {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .contact-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--secondary), var(--primary));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1.25rem;
        }

        .contact-details h4 {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
        }

        .contact-role {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .contact-methods {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .contact-method {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 0;
        }

        .contact-icon {
            width: 35px;
            height: 35px;
            border-radius: 8px;
            background: var(--light-gray);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--secondary);
        }

        .contact-link {
            color: var(--text-primary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .contact-link:hover {
            color: var(--secondary);
        }

        .whatsapp-btn {
            background: #25d366;
            color: white;
            padding: 0.75rem;
            border-radius: 8px;
            text-decoration: none;
            text-align: center;
            font-weight: 500;
            margin-top: 1rem;
            transition: all 0.2s ease;
        }

        .whatsapp-btn:hover {
            background: #128c7e;
            color: white;
            transform: translateY(-1px);
        }

        /* Property Info */
        .property-info {
            padding: 1.5rem;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--medium-gray);
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .info-value {
            font-weight: 500;
            text-align: right;
        }

        /* Description */
        .description-content {
            line-height: 1.7;
            color: var(--text-secondary);
        }

        /* Map */
        .map-container {
            height: 400px;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow-md);
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .main-content {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            
            .sidebar {
                order: -1;
            }
        }

        @media (max-width: 768px) {
            .property-title {
                font-size: 2rem;
            }
            
            .property-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }
            
            .main-image {
                height: 300px;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
            }
            
            .custom-fields {
                grid-template-columns: 1fr;
            }
            
            .thumbnail-strip {
                padding: 0.75rem;
            }
            
            .section-content,
            .action-buttons,
            .contact-info,
            .property-info {
                padding: 1rem;
            }
        }

        @media (max-width: 480px) {
            .property-header {
                padding: 1.5rem 0;
            }
            
            .price-amount {
                font-size: 1.5rem;
            }
            
            .container-fluid {
                padding: 0 1rem;
            }
        }

        /* Animations */
        .fade-in {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeIn 0.6s ease forwards;
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Loading States */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }

        /* Alert Styles */
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: none;
        }

        .alert-warning {
            background: #fef5e7;
            color: #744210;
        }
    </style>
</head>

<body>
    <!-- Login Alert -->
    <?php if (!$is_logged_in && isset($_POST['wishlist_toggle'])): ?>
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Login Required!</strong> Please log in to add items to your wishlist.
    </div>
    <?php endif; ?>

    <!-- Property Header -->
    <div class="property-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-8">
                    <h1 class="property-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                    
                    <div class="property-meta">
                        <?php if (!empty($product['city'])): ?>
                        <div class="meta-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?php echo htmlspecialchars($product['city']); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($product['category_name'])): ?>
                        <div class="meta-item">
                            <i class="fas fa-tag"></i>
                            <span><?php echo htmlspecialchars($product['category_name']); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="meta-item">
                            <i class="fas fa-calendar"></i>
                            <span>Listed <?php echo date('M j, Y', strtotime($product['created_at'])); ?></span>
                        </div>
                        
                        <div class="meta-item">
                            <i class="fas fa-eye"></i>
                            <span>ID: PR<?php echo substr(md5($product['id']), 0, 6); ?></span>
                        </div>
                    </div>
                </div>
                
               
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="main-content">
            <!-- Main Content Area -->
            <div class="content-area">
                <!-- Image Gallery -->
                <div class="image-gallery fade-in">
                    <img src="./<?php echo htmlspecialchars($product['image']); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                         class="main-image" id="mainImage">
                    
                    <?php if (!empty($additional_images)): ?>
                    <div class="thumbnail-strip">
                        <div class="thumbnail">
                            <img src="./<?php echo htmlspecialchars($product['image']); ?>" alt="Main Image">
                        </div>
                        <?php foreach ($additional_images as $image): ?>
                        <div class="thumbnail">
                            <img src="./<?php echo htmlspecialchars($image['image_path']); ?>" alt="Additional Image">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Property Features -->
                <div class="property-details fade-in">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-home"></i>
                            Property Overview
                        </h3>
                    </div>
                    <div class="section-content">
                        <div class="features-grid">
                            <?php if (!empty($product['category_name'])): ?>
                            <div class="feature-item">
                                <i class="fas fa-layer-group feature-icon"></i>
                                <span><?php echo htmlspecialchars($product['category_name']); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($product['subcategory_name'])): ?>
                            <div class="feature-item">
                                <i class="fas fa-building feature-icon"></i>
                                <span><?php echo htmlspecialchars($product['subcategory_name']); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($product['property_type'])): ?>
                            <div class="feature-item">
                                <i class="fas fa-key feature-icon"></i>
                                <span><?php echo htmlspecialchars($product['property_type']); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($product['transaction_type'])): ?>
                            <div class="feature-item">
                                <i class="fas fa-exchange-alt feature-icon"></i>
                                <span><?php echo htmlspecialchars($product['transaction_type']); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Custom Fields -->
                <?php if (!empty($custom_fields)): ?>
                <div class="property-details fade-in">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-list-check"></i>
                            Property Features
                        </h3>
                    </div>
                    <div class="section-content">
                        <div class="custom-fields">
                            <?php foreach ($custom_fields as $field): 
                                if (!empty($field['values_list'])) {
                                    $values = explode('|', $field['values_list']);
                                } else {
                                    $values = [];
                                }
                            ?>
                            <div class="field-group">
                                <div class="field-title"><?php echo htmlspecialchars($field['title']); ?></div>
                                <div class="field-values">
                                    <?php foreach ($values as $value): ?>
                                    <?php if (!empty(trim($value))): ?>
                                    <span class="field-value"><?php echo htmlspecialchars(trim($value)); ?></span>
                                    <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Description -->
                <?php if (!empty($product['description'])): ?>
                <div class="property-details fade-in">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-align-left"></i>
                            Description
                        </h3>
                    </div>
                    <div class="section-content">
                        <div class="description-content">
                            <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Map Section -->
                <?php if (!empty($product['latitude']) && !empty($product['longitude'])): ?>
                <div class="property-details fade-in">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-map-marker-alt"></i>
                            Location
                        </h3>
                    </div>
                    <div class="section-content">
                        <div id="map" class="map-container"></div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Action Buttons -->
                <div class="sidebar-section fade-in">
                    <div class="action-buttons">
                        <?php if (isset($_SESSION['user_id'])): ?>
                        <form method="POST">
                            <button type="submit" name="wishlist_toggle" 
                                    class="btn w-100 <?php echo $is_in_wishlist ? 'btn-danger' : 'btn-primary'; ?>">
                                <i class="fas fa-heart me-2"></i>
                                <?php echo $is_in_wishlist ? 'Remove from Wishlist' : 'Add to Wishlist'; ?>
                            </button>
                        </form>
                        <?php else: ?>
                        <button type="button" class="btn btn-outline w-100" 
                                onclick="alert('Please log in to use the wishlist feature')">
                            <i class="fas fa-heart me-2"></i>
                            Add to Wishlist
                        </button>
                        <?php endif; ?>
                        
                        <button class="btn btn-outline w-100" onclick="shareProperty()">
                            <i class="fas fa-share-alt me-2"></i>
                            Share Property
                        </button>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="sidebar-section fade-in">
                    <div class="section-header">
                        <h4 class="section-title">
                            <i class="fas fa-user-tie"></i>
                            Contact Agent
                        </h4>
                    </div>
                    <div class="contact-info">
                        <div class="contact-person">
                            <div class="contact-avatar">
                                <?php 
                                $initials = '';
                                if (!empty($product['contact_name'])) {
                                    $names = explode(' ', trim($product['contact_name']));
                                    foreach ($names as $name) {
                                        if (!empty($name)) {
                                            $initials .= strtoupper(substr($name, 0, 1));
                                        }
                                    }
                                    $initials = substr($initials, 0, 2);
                                } else {
                                    $initials = 'AG';
                                }
                                echo htmlspecialchars($initials);
                                ?>
                            </div>
                            <div class="contact-details">
                            <h4><?php echo !empty($product['contact_name']) ? htmlspecialchars($product['contact_name']) : 'Property Agent'; ?></h4>
                                <p class="contact-role">Listing Agent</p>
                            </div>
                        </div>

                        <div class="contact-methods">
                            <?php if (!empty($product['phone'])): ?>
                            <div class="contact-method">
                                <div class="contact-icon">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <a href="tel:<?php echo htmlspecialchars($product['phone']); ?>" class="contact-link">
                                    <?php echo htmlspecialchars($product['phone']); ?>
                                </a>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($product['whatsapp'])): ?>
                            <div class="contact-method">
                                <div class="contact-icon">
                                    <i class="fab fa-whatsapp"></i>
                                </div>
                                <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $product['whatsapp']); ?>" 
                                   class="contact-link" target="_blank">
                                    <?php echo htmlspecialchars($product['whatsapp']); ?>
                                </a>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($product['email'])): ?>
                            <div class="contact-method">
                                <div class="contact-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <a href="mailto:<?php echo htmlspecialchars($product['email']); ?>" class="contact-link">
                                    <?php echo htmlspecialchars($product['email']); ?>
                                </a>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($product['contact_hours'])): ?>
                            <div class="contact-method">
                                <div class="contact-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <span class="contact-link"><?php echo htmlspecialchars($product['contact_hours']); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($product['whatsapp'])): ?>
                        <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $product['whatsapp']); ?>" 
                           class="whatsapp-btn" target="_blank">
                            <i class="fab fa-whatsapp me-2"></i> Chat on WhatsApp
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Property Information -->
                <div class="sidebar-section fade-in">
                    <div class="section-header">
                        <h4 class="section-title">
                            <i class="fas fa-info-circle"></i>
                            Property Info
                        </h4>
                    </div>
                    <div class="property-info">
                        <div class="info-item">
                            <span class="info-label">Property ID</span>
                            <span class="info-value">PR<?php echo substr(md5($product['id']), 0, 6); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Category</span>
                            <span class="info-value"><?php echo htmlspecialchars($product['category_name']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Type</span>
                            <span class="info-value"><?php echo htmlspecialchars($product['subcategory_name']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Listed On</span>
                            <span class="info-value"><?php echo date('M j, Y', strtotime($product['created_at'])); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Last Updated</span>
                            <span class="info-value"><?php echo date('M j, Y', strtotime($product['updated_at'])); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Form Modal -->
    <div class="modal fade" id="contactModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Contact Agent</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="contactForm">
                        <div class="mb-3">
                            <label class="form-label">Your Name</label>
                            <input type="text" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message</label>
                            <textarea class="form-control" rows="4" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Change main image when thumbnail is clicked
        document.querySelectorAll('.thumbnail').forEach(thumb => {
            thumb.addEventListener('click', function() {
                const imgSrc = this.querySelector('img').src;
                document.getElementById('mainImage').src = imgSrc;
            });
        });

        // Share property functionality
        function shareProperty() {
            if (navigator.share) {
                navigator.share({
                    title: '<?php echo addslashes($product['name']); ?>',
                    text: 'Check out this property: <?php echo addslashes($product['name']); ?>',
                    url: window.location.href
                }).catch(err => {
                    console.log('Error sharing:', err);
                });
            } else {
                // Fallback for browsers that don't support Web Share API
                const shareUrl = `mailto:?subject=<?php echo rawurlencode($product['name']); ?>&body=Check out this property: ${window.location.href}`;
                window.location.href = shareUrl;
            }
        }

        // Initialize map if coordinates exist
        <?php if (!empty($product['latitude']) && !empty($product['longitude'])): ?>
        function initMap() {
            const location = { lat: <?php echo $product['latitude']; ?>, lng: <?php echo $product['longitude']; ?> };
            const map = new google.maps.Map(document.getElementById("map"), {
                zoom: 15,
                center: location,
                mapId: "DEMO_MAP_ID",
            });
            new google.maps.Marker({
                position: location,
                map,
                title: "<?php echo addslashes($product['name']); ?>",
            });
        }
        <?php endif; ?>

        // Form submission
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Thank you for your enquiry. We will contact you shortly.');
            const modal = bootstrap.Modal.getInstance(document.getElementById('contactModal'));
            modal.hide();
            this.reset();
        });

        // Animation on scroll
        document.addEventListener('DOMContentLoaded', function() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate');
                    }
                });
            }, { threshold: 0.1 });

            document.querySelectorAll('.fade-in').forEach(el => {
                observer.observe(el);
            });
        });
    </script>

    <?php if (!empty($product['latitude']) && !empty($product['longitude'])): ?>
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap&libraries=&v=weekly" async></script>
    <?php endif; ?>
</body>
</html>