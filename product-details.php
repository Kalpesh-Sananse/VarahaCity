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
$is_in_wishlist = false;

if ($is_logged_in) {
    // Check wishlist status
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
            $remove_sql = "DELETE FROM wishlist WHERE user_id = ? AND product_id = ?";
            $stmt = $conn->prepare($remove_sql);
            $stmt->bind_param("ii", $_SESSION['user_id'], $product_id);
            $stmt->execute();
            $stmt->close();
            $is_in_wishlist = false;
        } else {
            $add_sql = "INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)";
            $stmt = $conn->prepare($add_sql);
            $stmt->bind_param("ii", $_SESSION['user_id'], $product_id);
            $stmt->execute();
            $stmt->close();
            $is_in_wishlist = true;
        }
        
        header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $product_id);
        exit();
    }
}

// Fetch product details with all information
$sql = "SELECT p.*, 
        c.name as category_name, 
        s.name as subcategory_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN subcategories s ON p.subcategory_id = s.id
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
// Add this after fetching the product details:
// Fetch contact details
$contact_sql = "SELECT * FROM contact_details WHERE status = 'active' ORDER BY created_at DESC LIMIT 1";
$contact_result = $conn->query($contact_sql);
$contact_details = $contact_result->fetch_assoc();

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

// Property type labels
$property_types = [
    'buy' => 'For Buy',
    'sale' => 'For Sale',
    'rent' => 'For Rent',
    'lease' => 'For Lease',
    'other' => 'Other'
];

// Property status labels
$property_statuses = [
    'available' => 'Available',
    'sold' => 'Sold Out'
];
?>

<!DOCTYPE html>
<html lang="en">



<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> | Property Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
:root {
    --primary: #1a365d;
    --primary-light: #2c5282;
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
    --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.1);
    --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
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
    padding: 0 20px;
}

/* Header Section */
.search-bar {
    background: white;
    padding: 15px 0;
    box-shadow: var(--shadow-sm);
    position: sticky;
    top: 0;
    z-index: 100;
}

.search-input {
    border-radius: 30px;
    padding: 10px 20px;
    border: 1px solid var(--medium-gray);
    width: 100%;
    max-width: 600px;
    margin: 0 auto;
    display: block;
    font-size: 16px;
}

.search-input:focus {
    outline: none;
    border-color: var(--secondary);
    box-shadow: 0 0 0 3px rgba(43, 119, 230, 0.2);
}

/* Property Header */
.property-header {
    background: #0a142f;
    color: white;
    padding: 2rem 0;
    margin-bottom: 2rem;
}

.property-header .property-title,
.property-header .property-price,
.property-header .meta-item,
.property-header .meta-item span,
.property-header .meta-item i {
    color: white;
}

.property-header .property-badge {
    color: white;
    background-color: rgba(255, 255, 255, 0.2);
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.property-title {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    color: var(--primary);
}

.property-price {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--primary);
    margin: 1rem 0;
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
    color: var(--text-secondary);
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
    -webkit-overflow-scrolling: touch;
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

/* Property Specifications */
.specifications-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.spec-item {
    margin-bottom: 1.5rem;
}

.spec-title {
    font-weight: 600;
    color: var(--text-secondary);
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.spec-value {
    font-weight: 600;
    color: var(--primary);
    font-size: 1.1rem;
}

/* Contact Counter */
.contact-counter {
    background: var(--light-gray);
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.9rem;
    color: var(--text-secondary);
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 1rem;
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
    font-weight: 600;
    transition: all 0.2s ease;
    color: white;
    text-align: center;
    text-decoration: none;
}

.btn-primary:hover {
    background: var(--primary-light);
    transform: translateY(-2px);
    color: white;
}

.btn-outline {
    background: transparent;
    border: 2px solid var(--medium-gray);
    color: var(--text-primary);
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.2s ease;
    text-align: center;
    text-decoration: none;
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
    word-break: break-all;
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
    font-weight: 600;
    margin-top: 1rem;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.whatsapp-btn:hover {
    background: #128c7e;
    color: white;
    transform: translateY(-2px);
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

/* Property Info Section */
.property-info-enhanced {
    padding: 1.5rem;
}

.info-item-enhanced {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--medium-gray);
}

.info-item-enhanced:last-child {
    border-bottom: none;
}

.info-label-enhanced {
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.info-value-enhanced {
    font-weight: 500;
    text-align: right;
    color: var(--text-primary);
}

/* Badge styles */
.badge-rent,
.badge-buy,
.badge-sale,
.badge-lease,
.badge-other,
.badge-available,
.badge-sold {
    background-color: rgba(255, 255, 255, 0.2);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    margin-right: 0.5rem;
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

/* ENHANCED RESPONSIVE DESIGN */

/* Large Desktop (1400px and up) */
@media (min-width: 1400px) {
    .container-fluid {
        padding: 0 40px;
    }
    
    .main-content {
        gap: 3rem;
    }
    
    .property-title {
        font-size: 2.5rem;
    }
    
    .property-price {
        font-size: 2rem;
    }
}

/* Desktop (1200px to 1399px) */
@media (max-width: 1399px) {
    .container-fluid {
        padding: 0 30px;
    }
    
    .main-content {
        grid-template-columns: 1fr 320px;
    }
}

/* Large Tablets and Small Desktops (1024px to 1199px) */
@media (max-width: 1199px) {
    .container-fluid {
        padding: 0 25px;
    }
    
    .main-content {
        grid-template-columns: 1fr 300px;
        gap: 1.5rem;
    }
    
    .property-title {
        font-size: 1.8rem;
    }
    
    .property-price {
        font-size: 1.6rem;
    }
    
    .specifications-grid {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    }
    
    .features-grid {
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    }
}

/* Tablets (768px to 1023px) */
@media (max-width: 1023px) {
    .container-fluid {
        padding: 0 20px;
    }
    
    .main-content {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .sidebar {
        order: -1;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1rem;
    }
    
    .property-header {
        padding: 1.5rem 0;
    }
    
    .property-title {
        font-size: 1.7rem;
    }
    
    .property-price {
        font-size: 1.5rem;
    }
    
    .main-image {
        height: 400px;
    }
    
    .specifications-grid {
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1rem;
    }
    
    .features-grid {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    }
    
    .map-container {
        height: 350px;
    }
}

/* Large Mobile Phones (576px to 767px) */
@media (max-width: 767px) {
    .container-fluid {
        padding: 0 15px;
    }
    
    .property-header {
        padding: 1rem 0;
    }
    
    .property-title {
        font-size: 1.5rem;
        line-height: 1.3;
    }
    
    .property-price {
        font-size: 1.3rem;
    }
    
    .property-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
    }
    
    .main-image {
        height: 300px;
    }
    
    .sidebar {
        grid-template-columns: 1fr;
    }
    
    .specifications-grid {
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    
    .features-grid {
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem;
    }
    
    .section-content,
    .action-buttons,
    .contact-info {
        padding: 1rem;
    }
    
    .section-header {
        padding: 1rem;
    }
    
    .section-title {
        font-size: 1.1rem;
    }
    
    .thumbnail-strip {
        padding: 0.75rem;
    }
    
    .thumbnail {
        width: 70px;
        height: 50px;
    }
    
    .contact-person {
        flex-direction: column;
        text-align: center;
        gap: 0.75rem;
    }
    
    .contact-avatar {
        width: 50px;
        height: 50px;
        font-size: 1rem;
    }
    
    .info-item-enhanced {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }
    
    .info-value-enhanced {
        text-align: left;
        width: 100%;
    }
    
    .map-container {
        height: 300px;
    }
}

/* Small Mobile Phones (320px to 575px) */
@media (max-width: 575px) {
    .container-fluid {
        padding: 0 10px;
    }
    
    .property-header {
        padding: 0.75rem 0;
    }
    
    .property-title {
        font-size: 1.3rem;
        line-height: 1.2;
    }
    
    .property-price {
        font-size: 1.2rem;
    }
    
    .main-image {
        height: 250px;
    }
    
    .specifications-grid,
    .features-grid {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }
    
    .section-content,
    .action-buttons,
    .contact-info,
    .property-info-enhanced {
        padding: 0.75rem;
    }
    
    .section-header {
        padding: 0.75rem;
    }
    
    .section-title {
        font-size: 1rem;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }
    
    .thumbnail {
        width: 60px;
        height: 40px;
    }
    
    .thumbnail-strip {
        padding: 0.5rem;
        gap: 0.25rem;
    }
    
    .feature-item {
        padding: 0.5rem;
        gap: 0.5rem;
    }
    
    .btn-primary,
    .btn-outline,
    .whatsapp-btn {
        padding: 0.6rem 1rem;
        font-size: 0.9rem;
    }
    
    .meta-item {
        font-size: 0.85rem;
    }
    
    .spec-title {
        font-size: 0.8rem;
    }
    
    .spec-value {
        font-size: 1rem;
    }
    
    .contact-methods {
        gap: 0.5rem;
    }
    
    .contact-method {
        padding: 0.25rem 0;
    }
    
    .contact-icon {
        width: 30px;
        height: 30px;
    }
    
    .contact-link {
        font-size: 0.9rem;
    }
    
    .map-container {
        height: 250px;
    }
    
    .property-badge {
        font-size: 0.75rem;
        padding: 0.2rem 0.5rem;
    }
}

/* Very Small Screens (280px to 319px) */
@media (max-width: 319px) {
    .container-fluid {
        padding: 0 8px;
    }
    
    .property-title {
        font-size: 1.1rem;
    }
    
    .property-price {
        font-size: 1.1rem;
    }
    
    .main-image {
        height: 200px;
    }
    
    .section-content,
    .action-buttons,
    .contact-info,
    .property-info-enhanced {
        padding: 0.5rem;
    }
    
    .section-header {
        padding: 0.5rem;
    }
    
    .btn-primary,
    .btn-outline,
    .whatsapp-btn {
        padding: 0.5rem 0.75rem;
        font-size: 0.85rem;
    }
    
    .feature-item {
        padding: 0.4rem;
        flex-direction: column;
        text-align: center;
        gap: 0.25rem;
    }
    
    .spec-value {
        font-size: 0.9rem;
    }
    
    .map-container {
        height: 200px;
    }
}

/* Landscape orientation adjustments for mobile */
@media (max-width: 767px) and (orientation: landscape) {
    .main-image {
        height: 250px;
    }
    
    .map-container {
        height: 250px;
    }
    
    .property-header {
        padding: 0.75rem 0;
    }
}

/* High DPI displays */
@media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
    .thumbnail img,
    .main-image {
        image-rendering: -webkit-optimize-contrast;
        image-rendering: crisp-edges;
    }
}

/* Print styles */
@media print {
    .sidebar,
    .action-buttons,
    .whatsapp-btn {
        display: none;
    }
    
    .main-content {
        grid-template-columns: 1fr;
    }
    
    .property-header {
        background: white !important;
        color: black !important;
    }
    
    .property-header .property-title,
    .property-header .property-price,
    .property-header .meta-item {
        color: black !important;
    }
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

    <!-- property header -->
     <div class="property-header">
    <div class="container-fluid">
        <h1 class="property-title">
            <?php echo htmlspecialchars($product['name']); ?>
            <?php if (!empty($product['property_type']) && isset($property_types[$product['property_type']])): ?>
            <span class="transaction-type"><?php echo $property_types[$product['property_type']]; ?></span>
            <?php endif; ?>
        </h1>

        <div class="property-badges-wrapper">
            <?php if (!empty($product['property_type']) && isset($property_types[$product['property_type']])): ?>
            <span class="property-badge badge-<?php echo $product['property_type']; ?>">
                <?php echo $property_types[$product['property_type']]; ?>
            </span>
            <?php endif; ?>

            <?php if (!empty($product['property_status']) && isset($property_statuses[$product['property_status']])): ?>
            <span class="property-badge badge-<?php echo $product['property_status']; ?>">
                <?php echo $property_statuses[$product['property_status']]; ?>
            </span>
            <?php endif; ?>

            <?php if (!empty($product['price'])): ?>
            <div class="property-price">₹<?php echo number_format($product['price']); ?></div>
            <?php endif; ?>
        </div>

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
        </div>
    </div>
</div>

<style>
/* Updated Property Header Styles with Consistent Colors */
.property-header {
    padding: 20px 0;
   
    border-bottom: 2px solid #e2e8f0; /* Subtle border */
}

.container-fluid {
    width: 100%;
    padding-right: 15px;
    padding-left: 15px;
    margin-right: auto;
    margin-left: auto;
    max-width: 1200px;
}

.property-title {
    font-size: 28px;
    margin-bottom: 12px;
    word-wrap: break-word;
    color: #1e293b; /* Dark slate blue for text */
    font-weight: 600;
}

.transaction-type {
    color: white; /* Medium gray for secondary text */
    font-weight: normal;
    font-size: 0.85em;
    margin-left: 8px;
}

.property-badges-wrapper {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
}

.property-badge {
    padding: 6px 12px;
    border-radius: 20px; /* Pill-shaped badges */
    font-size: 14px;
    font-weight: 500;
    color: white;
    background-color: #475569; /* Default slate badge */
}

/* Specific badge colors */
.badge-sale {
    background-color: #dc2626; /* Vibrant red for sale */
}

.badge-rent {
    background-color: #2563eb; /* Bright blue for rent */
}

.badge-featured {
    background-color: #ea580c; /* Warm orange for featured */
    box-shadow: 0 2px 4px rgba(234, 88, 12, 0.2);
}

.property-price {
    font-weight: 700;
    font-size: 20px;
    color: #1e293b; /* Dark text */
    background-color: #f1f5f9; /* Light blue-gray */
    padding: 8px 16px;
    border-radius: 6px;
    margin-left: auto;
}

.property-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    padding-top: 8px;
    border-top: 1px solid #e2e8f0;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 15px;
    color: #475569; /* Slate blue text */
}

.meta-item i {
    color: #64748b; /* Medium gray icons */
    width: 18px;
    text-align: center;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .property-title {
        font-size: 24px;
    }
    
    .property-badges-wrapper {
        gap: 8px;
    }
    
    .property-badge {
        font-size: 13px;
        padding: 4px 10px;
    }
    
    .property-price {
        font-size: 18px;
        padding: 6px 12px;
    }
    
    .property-meta {
        gap: 15px;
    }
    
    .meta-item {
        font-size: 14px;
    }
}

@media (max-width: 576px) {
    .property-title {
        font-size: 22px;
    }
    
    .property-badges-wrapper {
        flex-direction: row;
        align-items: center;
    }
    
    .property-price {
        margin-left: 0;
        margin-top: 8px;
    }
    
    .property-meta {
        flex-direction: column;
        gap: 10px;
    }
}
</style>
    <div class="container-fluid">
        <div class="main-content">
            <!-- Main Content Area -->
            <div class="content-area">
                <!-- Image Gallery -->
                <div class="image-gallery fade-in">
                    <img src="./<?php echo htmlspecialchars($product['image']); ?>"
                        alt="<?php echo htmlspecialchars($product['name']); ?>" class="main-image" id="mainImage">

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

                <!-- Basic Property Information -->
                <div class="property-details fade-in">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-info-circle"></i>
                            Basic Information
                        </h3>
                    </div>
                    <div class="section-content">
                        <div class="specifications-grid">
                            <?php if (!empty($product['property_type'])): ?>
                            <div class="spec-item">
                                <div class="spec-title">Transaction Type</div>
                                <div class="spec-value">
                                    <?php echo isset($property_types[$product['property_type']]) ? 
                                          $property_types[$product['property_type']] : 
                                          htmlspecialchars($product['property_type']); ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($product['property_status'])): ?>
                            <div class="spec-item">
                                <div class="spec-title">Availability</div>
                                <div class="spec-value">
                                    <?php echo isset($property_statuses[$product['property_status']]) ? 
                                          $property_statuses[$product['property_status']] : 
                                          htmlspecialchars($product['property_status']); ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($product['dimensions'])): ?>
                            <div class="spec-item">
                                <div class="spec-title">Dimensions</div>
                                <div class="spec-value"><?php echo htmlspecialchars($product['dimensions']); ?></div>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($product['area'])): ?>
                            <div class="spec-item">
                                <div class="spec-title">Area</div>
                                <div class="spec-value"><?php echo htmlspecialchars($product['area']); ?></div>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($product['taluku'])): ?>
                            <div class="spec-item">
                                <div class="spec-title">Taluku</div>
                                <div class="spec-value"><?php echo htmlspecialchars($product['taluku']); ?></div>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($product['city'])): ?>
                            <div class="spec-item">
                                <div class="spec-title">City</div>
                                <div class="spec-value"><?php echo htmlspecialchars($product['city']); ?></div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Property Specifications -->
                <!-- Property Specifications -->
                <div class="property-details fade-in">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-home"></i>
                            Property Specifications
                        </h3>
                    </div>
                    <div class="section-content">
                        <?php if (!empty($custom_fields)): ?>
                        <div class="specifications-grid">
                            <?php foreach($custom_fields as $field): 
                $values = !empty($field['values_list']) ? explode('|', $field['values_list']) : [];
                if (!empty($values)): ?>
                            <div class="spec-item">
                                <div class="spec-title"><?php echo htmlspecialchars($field['title']); ?></div>
                                <div class="spec-value">
                                    <?php 
                        // Display all values separated by commas
                        echo htmlspecialchars(implode(', ', array_map('trim', $values)));
                        ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info">No specifications added yet</div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Property Features -->
                <div class="property-details fade-in">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-list-check"></i>
                            Property Features
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
                        </div>
                    </div>
                </div>

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
                        <?php if ($is_logged_in): ?>
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
               <!-- Contact Information -->
<div class="sidebar-section fade-in">
    <div class="section-header">
        <h4 class="section-title">
            <i class="fas fa-user-tie"></i>
            Contact Us
        </h4>
    </div>
    <div class="contact-info">
        <div class="contact-person">
            <div class="contact-avatar">
                <?php 
                $initials = 'CS'; // Default to "Contact Support"
                echo htmlspecialchars($initials);
                ?>
            </div>
            <div class="contact-details">
                <h4>Contact Support</h4>
                <p class="contact-role">Customer Service</p>
            </div>
        </div>

        <div class="contact-methods">
            <?php if (!empty($contact_details['phone'])): ?>
            <div class="contact-method">
                <div class="contact-icon">
                    <i class="fas fa-phone"></i>
                </div>
                <a href="tel:<?php echo htmlspecialchars($contact_details['phone']); ?>" class="contact-link">
                    <?php echo htmlspecialchars($contact_details['phone']); ?>
                </a>
            </div>
            <?php endif; ?>

            <?php if (!empty($contact_details['whatsapp'])): ?>
            <div class="contact-method">
                <div class="contact-icon">
                    <i class="fab fa-whatsapp"></i>
                </div>
                <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $contact_details['whatsapp']); ?>"
                    class="contact-link" target="_blank">
                    <?php echo htmlspecialchars($contact_details['whatsapp']); ?>
                </a>
            </div>
            <?php endif; ?>

            <?php if (!empty($contact_details['email'])): ?>
            <div class="contact-method">
                <div class="contact-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <a href="mailto:<?php echo htmlspecialchars($contact_details['email']); ?>"
                    class="contact-link">
                    <?php echo htmlspecialchars($contact_details['email']); ?>
                </a>
            </div>
            <?php endif; ?>

            <?php if (!empty($contact_details['availability'])): ?>
            <div class="contact-method">
                <div class="contact-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <span class="contact-link"><?php echo htmlspecialchars($contact_details['availability']); ?></span>
            </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($contact_details['whatsapp'])): ?>
        <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $contact_details['whatsapp']); ?>"
            class="whatsapp-btn" target="_blank">
            <i class="fab fa-whatsapp"></i> Chat on WhatsApp
        </a>
        <?php endif; ?>
    </div>
</div>

                <!-- Property Information -->
                <!-- Property Information -->
                <div class="sidebar-section fade-in">
                    <div class="section-header">
                        <h4 class="section-title">
                            <i class="fas fa-info-circle"></i>
                            Property Info
                        </h4>
                    </div>
                    <div class="section-content property-info-enhanced">


                        <div class="info-item-enhanced">
                            <div class="info-label-enhanced">Category</div>
                            <div class="info-value-enhanced"><?php echo htmlspecialchars($product['category_name']); ?>
                            </div>
                        </div>

                        <div class="info-item-enhanced">
                            <div class="info-label-enhanced">Type</div>
                            <div class="info-value-enhanced">
                                <?php echo htmlspecialchars($product['subcategory_name']); ?></div>
                        </div>

                        <?php if (!empty($product['property_type'])): ?>
                        <div class="info-item-enhanced">
                            <div class="info-label-enhanced">Transaction Type</div>
                            <div class="info-value-enhanced">
                                <?php echo isset($property_types[$product['property_type']]) ? 
                      $property_types[$product['property_type']] : 
                      htmlspecialchars($product['property_type']); ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($product['property_status'])): ?>
                        <div class="info-item-enhanced">
                            <div class="info-label-enhanced">Status</div>
                            <div class="info-value-enhanced">
                                <?php echo isset($property_statuses[$product['property_status']]) ? 
                      $property_statuses[$product['property_status']] : 
                      htmlspecialchars($product['property_status']); ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($product['listed_on'])): ?>
                        <div class="info-item-enhanced">
                            <div class="info-label-enhanced">Listed On</div>
                            <div class="info-value-enhanced">
                                <?php echo date('M j, Y', strtotime($product['listed_on'])); ?></div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($product['last_updated'])): ?>
                        <div class="info-item-enhanced">
                            <div class="info-label-enhanced">Last Updated</div>
                            <div class="info-value-enhanced">
                                <?php echo date('M j, Y', strtotime($product['last_updated'])); ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
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
            const shareUrl =
                `mailto:?subject=<?php echo rawurlencode($product['name']); ?>&body=Check out this property: ${window.location.href}`;
            window.location.href = shareUrl;
        }
    }

    // Initialize map if coordinates exist
    <?php if (!empty($product['latitude']) && !empty($product['longitude'])): ?>

    function initMap() {
        const location = {
            lat: <?php echo $product['latitude']; ?>,
            lng: <?php echo $product['longitude']; ?>
        };
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

    // Animation on scroll
    document.addEventListener('DOMContentLoaded', function() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate');
                }
            });
        }, {
            threshold: 0.1
        });

        document.querySelectorAll('.fade-in').forEach(el => {
            observer.observe(el);
        });
    });
    </script>

    <?php if (!empty($product['latitude']) && !empty($product['longitude'])): ?>
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap&libraries=&v=weekly" async>
    </script>
    <?php endif; ?>
</body>

</html>