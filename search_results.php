<?php
session_start();
include('./includes/db_connection.php');

// Get search query
$searchTerm = isset($_GET['q']) ? trim($_GET['q']) : '';

// If empty search, show empty results
if(empty($searchTerm)) {
    $results = [];
    $isEmptySearch = true;
} else {
    try {
        $results = [];
        $searchParam = "%$searchTerm%";
        
        // 1. Search Products (properties)
        $sql = "SELECT p.id, p.name, p.image, p.city, 
                       p.property_type, p.property_status,
                       'property' as type,
                       c.name as category_name,
                       s.name as subcategory_name
                FROM products p
                LEFT JOIN subcategories s ON p.subcategory_id = s.id
                LEFT JOIN categories c ON s.parent_category_id = c.id
                WHERE p.name LIKE ? 
                   OR p.description LIKE ?
                   OR p.city LIKE ?
                   OR p.area LIKE ?
                   OR p.property_type LIKE ?
                   OR p.property_status LIKE ?
                   OR c.name LIKE ?
                   OR s.name LIKE ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssss", 
            $searchParam, $searchParam, $searchParam, 
            $searchParam, $searchParam, $searchParam,
            $searchParam, $searchParam);
        $stmt->execute();
        $propertyResults = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $results = array_merge($results, $propertyResults);

        // 2. Search Subcategories
        $sql = "SELECT s.id, s.name, s.photo as image, 
                       'subcategory' as type,
                       c.name as parent_name
                FROM subcategories s
                JOIN categories c ON s.parent_category_id = c.id
                WHERE s.name LIKE ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $searchParam);
        $stmt->execute();
        $subcategoryResults = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $results = array_merge($results, $subcategoryResults);

        // 3. Search Categories
        $sql = "SELECT id, name, photo as image, 
                       'category' as type
                FROM categories
                WHERE name LIKE ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $searchParam);
        $stmt->execute();
        $categoryResults = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $results = array_merge($results, $categoryResults);

    } catch(Exception $e) {
        error_log("Search error: " . $e->getMessage());
        $results = [];
    }
    $isEmptySearch = false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results | Your Site Name</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --accent-color: #e74c3c;
            --light-bg: #f8f9fa;
            --dark-text: #2c3e50;
            --light-text: #7f8c8d;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-bg);
            color: var(--dark-text);
        }
        
        .search-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 20px 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .search-box {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .search-input {
            border-radius: 50px;
            padding: 15px 25px;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .search-btn {
            border-radius: 50px;
            padding: 15px 30px;
            font-weight: 600;
            background-color: var(--accent-color);
            border: none;
        }
        
        .result-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
            margin-bottom: 25px;
            height: 100%;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            background-color: white;
        }
        
        .result-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        
        .result-image-container {
            height: 220px;
            overflow: hidden;
            position: relative;
        }
        
        .result-image {
            height: 100%;
            width: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .result-card:hover .result-image {
            transform: scale(1.05);
        }
        
        .badge-type {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 12px;
            padding: 8px 12px;
            border-radius: 50px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .property-badge {
            background-color: var(--primary-color);
        }
        
        .subcategory-badge {
            background-color: #2ecc71;
        }
        
        .category-badge {
            background-color: #9b59b6;
        }
        
        .card-body {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
        }
        
        .card-title {
            font-weight: 700;
            margin-bottom: 0.75rem;
            color: var(--secondary-color);
            font-size: 1.25rem;
        }
        
        .card-text {
            color: var(--light-text);
            margin-bottom: 1rem;
            font-size: 0.95rem;
        }
        
        .property-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
        }
        
        .property-status {
            font-weight: 600;
        }
        
        .available {
            color: #2ecc71;
        }
        
        .sold {
            color: var(--accent-color);
        }
        
        .view-btn {
            margin-top: auto;
            align-self: flex-start;
            padding: 8px 20px;
            border-radius: 50px;
            font-weight: 600;
            background-color: var(--primary-color);
            border: none;
            transition: all 0.3s ease;
        }
        
        .view-btn:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        .no-results {
            text-align: center;
            padding: 4rem;
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .no-results i {
            font-size: 3rem;
            color: var(--light-text);
            margin-bottom: 1rem;
        }
        
        .section-title {
            position: relative;
            margin-bottom: 2rem;
            font-weight: 700;
            color: var(--secondary-color);
        }
        
        .section-title:after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -10px;
            width: 50px;
            height: 3px;
            background-color: var(--primary-color);
        }
    </style>
</head>
<body>
  

    <div class="search-header">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10 text-center">
                    <h1 class="display-5 mb-4">Find What You're Looking For</h1>
                    <form action="search_results.php" method="GET" class="search-box">
                        <div class="input-group">
                            <input type="text" 
                                   class="form-control search-input" 
                                   name="q" 
                                   value="<?= htmlspecialchars($searchTerm) ?>"
                                   placeholder="Search properties, categories, cities..."
                                   required>
                            <button class="btn btn-danger search-btn" type="submit">
                                <i class="fas fa-search me-2"></i> Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="container mb-5">
        <h2 class="section-title">Search Results for "<?= htmlspecialchars($searchTerm) ?>"</h2>
        
        <?php if($isEmptySearch): ?>
            <div class="no-results">
                <i class="fas fa-search"></i>
                <h3>Please enter a search term</h3>
                <p class="text-muted">Type something in the search box above to find properties, categories, or locations</p>
            </div>
        <?php elseif(empty($results)): ?>
            <div class="no-results">
                <i class="far fa-frown"></i>
                <h3>No results found</h3>
                <p class="text-muted">We couldn't find any matches for "<?= htmlspecialchars($searchTerm) ?>"</p>
                <a href="index.php" class="btn btn-primary mt-3">Browse All Properties</a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach($results as $item): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <?php if($item['type'] === 'property'): ?>
                            <a href="product-details.php?id=<?= $item['id'] ?>" class="text-decoration-none">
                                <div class="card result-card h-100">
                                    <div class="result-image-container">
                                        <?php if(!empty($item['image'])): ?>
                                            <img src="./<?= htmlspecialchars($item['image']) ?>" 
                                                 class="result-image" 
                                                 alt="<?= htmlspecialchars($item['name']) ?>"
                                                 onerror="this.src='./images/placeholder.jpg'">
                                        <?php else: ?>
                                            <img src="./images/placeholder.jpg" 
                                                 class="result-image" 
                                                 alt="Property image">
                                        <?php endif; ?>
                                        <span class="badge badge-type property-badge">
                                            <i class="fas fa-home me-1"></i> Property
                                        </span>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($item['name']) ?></h5>
                                        
                                        <?php if(!empty($item['category_name']) || !empty($item['subcategory_name'])): ?>
                                            <p class="card-text">
                                                <small>
                                                    <?php if(!empty($item['subcategory_name'])): ?>
                                                        <i class="fas fa-tag text-primary me-1"></i><?= htmlspecialchars($item['subcategory_name']) ?>
                                                    <?php endif; ?>
                                                    <?php if(!empty($item['category_name'])): ?>
                                                        <span class="ms-2">
                                                            <i class="fas fa-layer-group text-primary me-1"></i><?= htmlspecialchars($item['category_name']) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </small>
                                            </p>
                                        <?php endif; ?>
                                        
                                        <div class="property-meta">
                                            <span class="card-text">
                                                <i class="fas fa-map-marker-alt text-primary me-1"></i><?= htmlspecialchars($item['city'] ?? 'N/A') ?>
                                            </span>
                                            <span class="property-status <?= ($item['property_status'] === 'available') ? 'available' : 'sold' ?>">
                                                <?= htmlspecialchars($item['property_status'] ?? '') ?>
                                            </span>
                                        </div>
                                        
                                        <span class="badge bg-light text-dark mb-3 align-self-start">
                                            <?= htmlspecialchars($item['property_type'] ?? '') ?>
                                        </span>
                                        
                                        <button class="btn btn-primary view-btn">
                                            View Details <i class="fas fa-arrow-right ms-2"></i>
                                        </button>
                                    </div>
                                </div>
                            </a>
                        
                        <?php elseif($item['type'] === 'subcategory'): ?>
                            <a href="product-listing.php?subcategory_id=<?= $item['id'] ?>" class="text-decoration-none">
                                <div class="card result-card h-100">
                                    <div class="result-image-container">
                                        <?php if(!empty($item['image'])): ?>
                                            <img src="./<?= htmlspecialchars($item['image']) ?>" 
                                                 class="result-image" 
                                                 alt="<?= htmlspecialchars($item['name']) ?>"
                                                 onerror="this.src='./images/placeholder.jpg'">
                                        <?php else: ?>
                                            <img src="./images/placeholder.jpg" 
                                                 class="result-image" 
                                                 alt="Subcategory image">
                                        <?php endif; ?>
                                        <span class="badge badge-type subcategory-badge">
                                            <i class="fas fa-tags me-1"></i> Subcategory
                                        </span>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($item['name']) ?></h5>
                                        <?php if(!empty($item['parent_name'])): ?>
                                            <p class="card-text">
                                                <small><i class="fas fa-layer-group text-primary me-1"></i><?= htmlspecialchars($item['parent_name']) ?></small>
                                            </p>
                                        <?php endif; ?>
                                        <button class="btn btn-primary view-btn mt-auto">
                                            Browse Properties <i class="fas fa-arrow-right ms-2"></i>
                                        </button>
                                    </div>
                                </div>
                            </a>
                        
                        <?php elseif($item['type'] === 'category'): ?>
                            <a href="getsubcategoriesuser.php?id=<?= $item['id'] ?>" class="text-decoration-none">
                                <div class="card result-card h-100">
                                    <div class="result-image-container">
                                        <?php if(!empty($item['image'])): ?>
                                            <img src="./<?= htmlspecialchars($item['image']) ?>" 
                                                 class="result-image" 
                                                 alt="<?= htmlspecialchars($item['name']) ?>"
                                                 onerror="this.src='./images/placeholder.jpg'">
                                        <?php else: ?>
                                            <img src="./images/placeholder.jpg" 
                                                 class="result-image" 
                                                 alt="Category image">
                                        <?php endif; ?>
                                        <span class="badge badge-type category-badge">
                                            <i class="fas fa-layer-group me-1"></i> Category
                                        </span>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($item['name']) ?></h5>
                                        <button class="btn btn-primary view-btn mt-auto">
                                            View Subcategories <i class="fas fa-arrow-right ms-2"></i>
                                        </button>
                                    </div>
                                </div>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>