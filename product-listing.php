<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
include('./includes/db_connection.php');

$current_timestamp = '2025-06-06 04:16:16';
$current_user = 'alexdanny090';

// Validate subcategory ID
$subcategory_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$subcategory_id) {
    header("Location: index.php");
    exit();
}

// Property type labels (same as in product-details.php)
$property_types = [
    'buy' => 'For Buy',
    'sale' => 'For Sale',
    'rent' => 'For Rent',
    'lease' => 'For Lease',
    'other' => 'Other'
];

// Property status labels (same as in product-details.php)
$property_statuses = [
    'available' => 'Available',
    'sold' => 'Sold Out'
];

// Fetch subcategory details with category name
$sql = "SELECT s.*, c.name as category_name 
        FROM subcategories s 
        JOIN categories c ON s.parent_category_id = c.id 
        WHERE s.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $subcategory_id);
$stmt->execute();
$subcategory = $stmt->get_result()->fetch_assoc();

// Redirect if subcategory doesn't exist
if (!$subcategory) {
    header("Location: index.php");
    exit();
}

// Fetch products for this subcategory (updated to include property_type and property_status)
$sql = "SELECT p.id, p.name, p.image, p.property_type, p.property_status
        FROM products p 
        WHERE p.subcategory_id = ? 
        ORDER BY p.created_at DESC";
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
    <title><?php echo htmlspecialchars($subcategory['name']); ?> - Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4D77FF;
            --secondary-color: #38B6FF;
            --dark-color: #1a1a1a;
            --light-color: #f8f9fa;
            --success-color: #2ECC71;
            --border-radius: 12px;
            --box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5;
            color: var(--dark-color);
        }

        .page-header {
            background: white;
            padding: 20px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 24px;
            padding: 0;
            margin-bottom: 40px;
        }

        .product-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .product-image-wrapper {
            position: relative;
            padding-top: 66.67%;
            overflow: hidden;
            background: #f8f9fa;
        }

        .product-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .product-card:hover .product-image {
            transform: scale(1.08);
        }

        .product-content {
            padding: 24px;
        }

        .product-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 10px;
            line-height: 1.4;
        }

        .product-price {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin: 0.5rem 0;
        }

        .transaction-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin: 12px 0;
        }

        /* Replace the existing badge styles with these new blue-themed styles */
.badge-transaction {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background-color: #EFF6FF; /* Light blue background */
    border: 1px solid transparent;
    transition: all 0.3s ease;
}

/* Blue color variations for different badge types */
.badge-buy { 
    background-color: #EFF6FF;
    color: #2563EB;
    border-color: #BFDBFE;
}

.badge-sale { 
    background-color: #E0E7FF;
    color: #4F46E5;
    border-color: #C7D2FE;
}

.badge-rent { 
    background-color: #E0F2FE;
    color: #0369A1;
    border-color: #BAE6FD;
}

.badge-lease { 
    background-color: #ECFDF5;
    color: #059669;
    border-color: #A7F3D0;
}

.badge-other { 
    background-color: #F5F3FF;
    color: #7C3AED;
    border-color: #DDD6FE;
}

.badge-available { 
    background-color: #ECFDF5;
    color: #059669;
    border-color: #A7F3D0;
}

.badge-sold { 
    background-color: #FEF2F2;
    color: #DC2626;
    border-color: #FECACA;
}

/* Hover effects */
.badge-transaction:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}
        

        .btn-view-details {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: var(--primary-color);
            color: white;
            padding: 14px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            width: 100%;
            margin-top: 16px;
        }

        .btn-view-details:hover {
            background: #3D5CCC;
            color: white;
            transform: translateY(-2px);
        }

        /* Breadcrumb styling */
        .breadcrumb {
            margin: 0;
        }

        .breadcrumb-item a {
            color: #6B7280;
            text-decoration: none;
            transition: var(--transition);
        }

        .breadcrumb-item a:hover {
            color: var(--primary-color);
        }

        .breadcrumb-item.active {
            color: #111827;
            font-weight: 500;
        }

        @media (max-width: 991px) {
            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                gap: 20px;
            }
        }

        @media (max-width: 768px) {
            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 16px;
            }

            .product-title {
                font-size: 1.1rem;
            }
        }

        @media (max-width: 576px) {
            .product-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <?php if (isset($subcategory['category_name'])): ?>
                    <li class="breadcrumb-item">
                        <a href="categories.php"><?php echo htmlspecialchars($subcategory['category_name']); ?></a>
                    </li>
                    <?php endif; ?>
                    <li class="breadcrumb-item active"><?php echo htmlspecialchars($subcategory['name']); ?></li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="container">
        <div class="product-grid">
            <?php foreach($products as $product): ?>
            <div class="product-card">
                <div class="product-image-wrapper">
                    <img src="./<?php echo htmlspecialchars($product['image']); ?>"
                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                         class="product-image"
                         onerror="this.src='./images/placeholder.jpg'">
                </div>
                
                <div class="product-content">
                    <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
             
                    
                    <div class="transaction-badges">
                        <?php if (!empty($product['property_type']) && isset($property_types[$product['property_type']])): ?>
                        <span class="badge-transaction badge-<?php echo $product['property_type']; ?>">
                            <?php echo $property_types[$product['property_type']]; ?>
                        </span>
                        <?php endif; ?>
                        
                        <?php if (!empty($product['property_status']) && isset($property_statuses[$product['property_status']])): ?>
                        <span class="badge-transaction badge-<?php echo $product['property_status']; ?>">
                            <?php echo $property_statuses[$product['property_status']]; ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    
                    <a href="product-details.php?id=<?php echo $product['id']; ?>" 
                       class="btn-view-details">
                        <i class="fas fa-eye"></i> View Details
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if (empty($products)): ?>
        <div class="text-center py-5">
            <div class="empty-state">
                <i class="fas fa-box-open fs-1 text-muted mb-3"></i>
                <h3>No Products Available</h3>
                <p class="text-muted">Check back later for new products in this category.</p>
            </div>
        </div>
        <?php endif; ?>
    </div>
       <!-- The HTML Section -->
       <style>
    .contact-section {
        background: linear-gradient(135deg, rgba(10, 20, 47, 0.95), rgba(26, 38, 66, 0.95));
        padding: 50px 20px;
        text-align: center;
        margin: 40px 0;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .contact-section-inner {
        max-width: 800px;
        margin: 0 auto;
    }

    .contact-heading {
        color: #fff;
        font-size: 2.2rem;
        margin-bottom: 20px;
        font-weight: 600;
        line-height: 1.3;
    }

    .contact-subtext {
        color: rgba(255, 255, 255, 0.9);
        font-size: 1.1rem;
        margin-bottom: 30px;
        line-height: 1.6;
    }

    .contact-btn {
        display: inline-block;
        padding: 15px 40px;
        background: linear-gradient(135deg, #4D77FF, #38B6FF);
        color: white;
        text-decoration: none;
        border-radius: 50px;
        font-weight: 600;
        font-size: 1.1rem;
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 4px 15px rgba(77, 119, 255, 0.3);
    }

    .contact-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(77, 119, 255, 0.4);
        color: white;
    }

    .contact-btn i {
        margin-left: 8px;
    }

    /* Animation for the section */
    .contact-section {
        animation: fadeInUp 0.6s ease-out;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Responsive Styles */
    @media (max-width: 768px) {
        .contact-section {
            padding: 40px 15px;
            margin: 30px 0;
        }

        .contact-heading {
            font-size: 1.8rem;
        }

        .contact-subtext {
            font-size: 1rem;
        }

        .contact-btn {
            padding: 12px 30px;
            font-size: 1rem;
        }
    }

    @media (max-width: 480px) {
        .contact-heading {
            font-size: 1.5rem;
        }

        .contact-section {
            padding: 30px 15px;
        }
    }
    </style>
       <div class="container">
        <div class="contact-section">
            <div class="contact-section-inner">
                <h2 class="contact-heading">Haven't Found Your Dream Property?</h2>
                <p class="contact-subtext">Let us help you find the perfect property that matches your requirements.
                    Contact us now and our expert team will assist you in your search.</p>
                <a href="<?php echo htmlspecialchars($form_link); ?>" target="_blank" class="contact-btn">
                    Contact Us Now <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>