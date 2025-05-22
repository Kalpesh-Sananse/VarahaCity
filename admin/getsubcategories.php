<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Start the session to check if the user is logged in
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include('../includes/db_connection.php');

// Get category ID from URL
$category_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch category details
$category = [];
$sql = "SELECT * FROM categories WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $category_id);
$stmt->execute();
$result = $stmt->get_result();
$category = $result->fetch_assoc();

if (!$category) {
    header("Location: view_categories.php");
    exit();
}

// Fetch all subcategories for this category
$subcategories = [];
$sql = "SELECT * FROM subcategories WHERE parent_category_id = ? ORDER BY name ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $category_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $subcategories[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($category['name']); ?> Subcategories - Real Estate</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #2980b9;
            --secondary-color: #6dd5fa;
            --accent-color: #FF6347;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f6f9;
            background-image: linear-gradient(to right, #f4f6f9, #e9ecef);
        }

        .page-container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
        }

        .page-header h2 {
            font-weight: 700;
            color: #333;
            display: inline-block;
            padding-bottom: 10px;
        }
        
        .page-header h2::after {
            content: '';
            position: absolute;
            width: 80px;
            height: 4px;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            bottom: 0;
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

        .card-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            padding: 20px 0;
        }

        .subcategory-card {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            background-color: #fff;
            cursor: pointer;
            border: none;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .subcategory-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.12);
        }
        
        .subcategory-card:active {
            transform: translateY(-2px);
        }

        .subcategory-image-container {
            height: 220px;
            position: relative;
            overflow: hidden;
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

        .image-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom, rgba(0,0,0,0.1), rgba(0,0,0,0.5));
        }

        .subcategory-content {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .subcategory-name {
            font-size: 1.4rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
            text-align: center;
        }
        
        .subcategory-description {
            color: #666;
            font-size: 0.95rem;
            margin-bottom: 15px;
            text-align: center;
            flex-grow: 1;
        }

        .add-subcategory-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.5rem;
            box-shadow: 0 4px 15px rgba(41, 128, 185, 0.4);
            transition: all 0.3s ease;
            z-index: 1000;
            border: none;
        }

        .add-subcategory-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(41, 128, 185, 0.6);
        }

        .no-subcategories {
            text-align: center;
            padding: 50px;
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            grid-column: 1 / -1;
        }

        .no-subcategories i {
            font-size: 3.5rem;
            color: #ddd;
            margin-bottom: 20px;
        }

        .no-subcategories h3 {
            color: #555;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .no-subcategories p {
            color: #777;
            margin-bottom: 20px;
        }

        /* Subcategory count badge */
        .subcategory-count {
            display: inline-block;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 50px;
            padding: 8px 20px;
            font-size: 1rem;
            font-weight: 600;
            box-shadow: 0 4px 10px rgba(41, 128, 185, 0.3);
            margin-bottom: 20px;
        }
        
        .subcategory-count i {
            margin-right: 8px;
        }

        /* Property type badges */
        .property-badge {
            display: inline-block;
            padding: 6px 15px;
            border-radius: 50px;
            font-weight: 500;
            margin-right: 8px;
            margin-bottom: 8px;
            font-size: 0.8rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
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

        /* Animation for cards */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .subcategory-card {
            animation: fadeIn 0.5s ease-out forwards;
            opacity: 0;
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .card-container {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                gap: 20px;
            }
        }
        
        @media (max-width: 768px) {
            .page-header h2 {
                font-size: 1.8rem;
            }
            
            .add-subcategory-btn {
                width: 50px;
                height: 50px;
                font-size: 1.3rem;
                bottom: 20px;
                right: 20px;
            }
        }
        
        @media (max-width: 576px) {
            .card-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">
            <i class="fas fa-building"></i>Varaha City Admin
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt mr-1"></i> Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="about.php"><i class="fas fa-info-circle mr-1"></i> About</a>
                </li>
                <li class="nav-item active">
                    <a class="nav-link" href="categories.php"><i class="fas fa-list-alt mr-1"></i> Categories</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="users.php"><i class="fas fa-users mr-1"></i> Users</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="contact.php"><i class="fas fa-envelope mr-1"></i> Contact</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt mr-1"></i> Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Main Content -->
<div class="page-container">
    <div class="page-header">
        <div class="breadcrumb-custom">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="view_categories.php" class="text-decoration-none">Categories</a></li>
                    <li class="breadcrumb-item active"><?php echo htmlspecialchars($category['name']); ?></li>
                </ol>
            </nav>
        </div>
        <h2><i class="fas fa-tag mr-2"></i><?php echo htmlspecialchars($category['name']); ?></h2>
    </div>

    <!-- Subcategory Count -->
    <div class="text-center">
        <span class="subcategory-count">
            <i class="fas fa-tags"></i> <?php echo count($subcategories); ?> Subcategories Available
        </span>
    </div>

    <?php if (count($subcategories) > 0): ?>
        <div class="card-container">
            <?php foreach($subcategories as $index => $subcategory): ?>
                <a href="viewsubcategoriesdetails.php?id=<?php echo $subcategory['id']; ?>" class="subcategory-card" style="animation-delay: <?php echo $index * 0.1; ?>s;">
                    <div class="subcategory-image-container">
                        <img src="../<?php echo htmlspecialchars($subcategory['photo']); ?>" alt="<?php echo htmlspecialchars($subcategory['name']); ?>" class="subcategory-image">
                        <div class="image-overlay"></div>
                    </div>
                    <div class="subcategory-content">
                        <h3 class="subcategory-name"><?php echo htmlspecialchars($subcategory['name']); ?></h3>
                        
                        <!-- Property Type Badges -->
                        <div class="text-center mb-2">
                            <?php if (isset($subcategory['property_type']) && !empty($subcategory['property_type'])): ?>
                                <span class="property-badge <?php echo $subcategory['property_type'] === 'buy' ? 'badge-buy' : 'badge-sale'; ?>">
                                    <i class="fas <?php echo $subcategory['property_type'] === 'buy' ? 'fa-shopping-cart' : 'fa-tag'; ?> me-1"></i>
                                    <?php echo ucfirst($subcategory['property_type']); ?>
                                </span>
                            <?php endif; ?>
                            
                            <?php if (isset($subcategory['transaction_type']) && !empty($subcategory['transaction_type'])): ?>
                                <span class="property-badge <?php echo $subcategory['transaction_type'] === 'rent' ? 'badge-rent' : 'badge-lease'; ?>">
                                    <i class="fas <?php echo $subcategory['transaction_type'] === 'rent' ? 'fa-home' : 'fa-file-contract'; ?> me-1"></i>
                                    <?php echo ucfirst($subcategory['transaction_type']); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <p class="subcategory-description">Click to view all properties in this subcategory</p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="no-subcategories">
            <i class="fas fa-folder-open"></i>
            <h3>No Subcategories Found</h3>
            <p>You haven't added any subcategories to this category yet.</p>
            <a href="add_subcategories.php?category_id=<?php echo $category_id; ?>" class="btn btn-primary">
                <i class="fas fa-plus-circle mr-2"></i> Add Your First Subcategory
            </a>
        </div>
    <?php endif; ?>

    <!-- Add Subcategory Floating Button -->
    <a href="add_subcategories.php?category_id=<?php echo $category_id; ?>" class="add-subcategory-btn">
        <i class="fas fa-plus"></i>
    </a>
</div>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>