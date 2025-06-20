<?php
require_once './includes/db_connection.php';

$current_datetime = '2025-05-27 21:13:52';
$current_user = 'Kalpesh-Sananse';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Categories - Varaha City</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .categories-section {
            padding: 80px 0;
            background: #f8f9fa;
        }

        .category-count {
            display: inline-block;
            padding: 10px 20px;
            background: #fff;
            border-radius: 30px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            color: #0A142F;
            margin-bottom: 40px;
            font-size: 16px;
        }

        .category-count i {
            color: #4D77FF;
            margin-right: 8px;
        }

        .card-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            padding: 20px 0;
        }

        .category-card {
            background: #fff;
            border-radius: 15px;
            overflow: hidden;
            text-decoration: none;
            color: inherit;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            opacity: 0;
            animation: fadeInUp 0.6s ease forwards;
        }

        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
        }

        .category-image-container {
            position: relative;
            height: 200px;
            overflow: hidden;
        }

        .category-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .category-card:hover .category-image {
            transform: scale(1.1);
        }

        .image-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, rgba(0,0,0,0) 0%, rgba(0,0,0,0.4) 100%);
        }

        .category-content {
            padding: 20px;
        }

        .category-name {
            font-size: 18px;
            font-weight: 600;
            color: #0A142F;
            margin-bottom: 8px;
        }

        .category-description {
            font-size: 14px;
            color: #6c757d;
            margin: 0;
        }

        .no-categories {
            text-align: center;
            padding: 60px 20px;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }

        .no-categories i {
            font-size: 48px;
            color: #4D77FF;
            margin-bottom: 20px;
        }

        .no-categories h3 {
            font-size: 24px;
            color: #0A142F;
            margin-bottom: 10px;
        }

        .no-categories p {
            color: #6c757d;
            margin-bottom: 20px;
        }

        .no-categories .btn {
            padding: 12px 30px;
            font-weight: 500;
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

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .categories-section {
                padding: 40px 0;
            }
            
            .card-container {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 20px;
            }
            
            .category-image-container {
                height: 180px;
            }
        }
    </style>
</head>
<body>

    <section class="categories-section" id="categories-section">
        <div class="container">
            <?php
            // Fetch categories with proper error handling
            try {
                $categories_query = "SELECT * FROM categories ORDER BY name ASC";
                $categories_result = $conn->query($categories_query);
                $categories = [];
                
                if ($categories_result && $categories_result->num_rows > 0) {
                    while ($row = $categories_result->fetch_assoc()) {
                        $categories[] = $row;
                    }
                }
            } catch (Exception $e) {
                error_log("Error fetching categories: " . $e->getMessage());
                $categories = [];
            }
            ?>
            
            <!-- Category Count -->
            <div class="text-center">
                <span class="category-count">
                    <i class="fas fa-tag"></i> <?php echo count($categories); ?> Categories Available
                </span>
            </div>

            <?php if (count($categories) > 0): ?>
                <div class="card-container">
                    <?php foreach($categories as $index => $category): 
                        // Determine the correct image path
                        $baseImagePath = './'; // Base path where images are stored
                        $imageFile = htmlspecialchars($category['photo']);
                        $fullImagePath = $baseImagePath . $imageFile;
                        
                        // Check if image exists, otherwise use placeholder
                        if (!empty($imageFile) && file_exists($fullImagePath)) {
                            $displayPath = $fullImagePath;
                        } else {
                            $displayPath = $baseImagePath . 'placeholder.jpg';
                        }
                    ?>
                        <a href="getsubcategoriesuser.php?id=<?php echo $category['id']; ?>" 
                           class="category-card" 
                           style="animation-delay: <?php echo $index * 0.1; ?>s;">
                            <div class="category-image-container">
                                <img src="<?php echo $displayPath; ?>" 
                                     alt="<?php echo htmlspecialchars($category['name']); ?>" 
                                     class="category-image"
                                     onerror="this.src='<?php echo $baseImagePath; ?>placeholder.jpg'">
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
                        <i class="fas fa-plus-circle me-2"></i> Add Your First Category
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include_once 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>