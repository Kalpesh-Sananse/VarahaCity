<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Start the session to check if the user is logged in
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include('../includes/db_connection.php'); // Include the database connection

// Fetch all categories from the database
$sql = "SELECT * FROM categories ORDER BY name ASC";
$result = $conn->query($sql);

$categories = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Categories - Real Estate</title>

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

        .card-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            padding: 20px 0;
        }

        .category-card {
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

        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.12);
        }
        
        .category-card:active {
            transform: translateY(-2px);
        }

        .category-image-container {
            height: 220px;
            position: relative;
            overflow: hidden;
        }

        .category-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .category-card:hover .category-image {
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

        .category-content {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .category-name {
            font-size: 1.4rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
            text-align: center;
        }
        
        .category-description {
            color: #666;
            font-size: 0.95rem;
            margin-bottom: 15px;
            text-align: center;
            flex-grow: 1;
        }

        .add-category-btn {
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

        .add-category-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(41, 128, 185, 0.6);
        }

        .no-categories {
            text-align: center;
            padding: 50px;
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            grid-column: 1 / -1;
        }

        .no-categories i {
            font-size: 3.5rem;
            color: #ddd;
            margin-bottom: 20px;
        }

        .no-categories h3 {
            color: #555;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .no-categories p {
            color: #777;
            margin-bottom: 20px;
        }

        /* Navbar styling */
        .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            padding: 15px 0;
        }
        
        .navbar-brand {
            font-weight: 700;
            color: #fff !important;
            font-size: 1.4rem;
        }
        
        .navbar-brand i {
            margin-right: 8px;
        }
        
        .navbar-nav .nav-link {
            color: rgba(255,255,255,0.9);
            font-weight: 500;
            margin: 0 5px;
            padding: 8px 15px;
            border-radius: 20px;
            transition: all 0.3s ease;
        }
        
        .navbar-nav .nav-link:hover,
        .navbar-nav .active .nav-link {
            color: #fff;
            background-color: rgba(255,255,255,0.15);
        }
        
        .navbar-toggler {
            border-color: rgba(255,255,255,0.3);
        }
        
        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(255, 255, 255, 0.8)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }

        /* Category count badge */
        .category-count {
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
        
        .category-count i {
            margin-right: 8px;
        }

        /* Search bar styling */
        .search-container {
            max-width: 600px;
            margin: 0 auto 30px auto;
        }
        
        .search-input {
            border-radius: 50px;
            padding: 12px 20px;
            border: 1px solid #ddd;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
        }
        
        .search-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(41, 128, 185, 0.2);
        }
        
        .search-btn {
            border-radius: 50px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 10px 25px;
            font-weight: 600;
            box-shadow: 0 4px 10px rgba(41, 128, 185, 0.3);
            transition: all 0.3s;
        }
        
        .search-btn:hover {
            background: linear-gradient(135deg, #2472a4, #5cc4f5);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(41, 128, 185, 0.4);
        }

        /* Animation for cards */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .category-card {
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
            
            .category-count {
                padding: 6px 15px;
                font-size: 0.9rem;
            }
            
            .add-category-btn {
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
            
            .search-container {
                margin-bottom: 20px;
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
        <h2><i class="fas fa-list-alt mr-2"></i>Property Categories</h2>
    </div>

    <!-- Search Bar -->
    <div class="search-container">
        <form class="row g-2" id="searchForm">
            <div class="col-md-8">
                <input type="text" class="form-control search-input" id="searchCategory" placeholder="Search categories...">
            </div>
            <div class="col-md-4">
                <button class="btn search-btn w-100" type="submit">
                    <i class="fas fa-search mr-2"></i> Search
                </button>
            </div>
        </form>
    </div>

    <!-- Category Count -->
    <div class="text-center">
        <span class="category-count">
            <i class="fas fa-tag"></i> <?php echo count($categories); ?> Categories Available
        </span>
    </div>

    <?php if (count($categories) > 0): ?>
        <div class="card-container">
            <?php foreach($categories as $index => $category): ?>
                <a href="category_details.php?id=<?php echo $category['id']; ?>" class="category-card" style="animation-delay: <?php echo $index * 0.1; ?>s;">
                    <div class="category-image-container">
                        <img src="../<?php echo htmlspecialchars($category['photo']); ?>" alt="<?php echo htmlspecialchars($category['name']); ?>" class="category-image">
                        <div class="image-overlay"></div>
                    </div>
                    <div class="category-content">
                        <h3 class="category-name"><?php echo htmlspecialchars($category['name']); ?></h3>
                        <p class="category-description">Click to view all properties in this category</p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="no-categories">
            <i class="fas fa-folder-open"></i>
            <h3>No Categories Found</h3>
            <p>You haven't added any property categories yet.</p>
            <a href="add_categories.php" class="btn btn-primary">
                <i class="fas fa-plus-circle mr-2"></i> Add Your First Category
            </a>
        </div>
    <?php endif; ?>

    <!-- Add Category Floating Button -->
    <a href="add_categories.php" class="add-category-btn">
        <i class="fas fa-plus"></i>
    </a>
</div>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script>
    // Search functionality
    document.addEventListener('DOMContentLoaded', function() {
        const searchForm = document.getElementById('searchForm');
        const searchInput = document.getElementById('searchCategory');
        
        if (searchForm) {
            searchForm.addEventListener('submit', function(e) {
                e.preventDefault();
                filterCategories();
            });
            
            searchInput.addEventListener('input', function() {
                if (this.value === '') {
                    filterCategories();
                }
            });
        }
        
        function filterCategories() {
            const searchTerm = searchInput.value.toLowerCase();
            const cards = document.querySelectorAll('.category-card');
            
            cards.forEach(card => {
                const categoryName = card.querySelector('.category-name').textContent.toLowerCase();
                if (categoryName.includes(searchTerm)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        }
    });
</script>

</body>
</html>