<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once '../config/db.php';

// Fetch slider images from database
$sliderQuery = "SELECT * FROM sliders WHERE is_active = 1 ORDER BY display_order";
$sliderStmt = $pdo->query($sliderQuery);
$sliders = $sliderStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Safari Bags - Premium Bags & Accessories</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../css/mystyle.css">
    <link rel="stylesheet" href="../css/slider.css">
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container">
            <div class="top-bar-content">
                <div class="contact-info">
                    <span><i class="fas fa-phone"></i> +1 234 567 890</span>
                    <span><i class="fas fa-envelope"></i> info@safaribags.com</span>
                </div>
                <div class="social-icons">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-pinterest"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Navigation -->
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="index.php">
                        <img src="images/logo.png" alt="Safari Bags Logo">
                    </a>
                </div>
                
                <nav class="main-nav">
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li class="dropdown">
                            <a href="#">Shop <i class="fas fa-chevron-down"></i></a>
                            <div class="dropdown-menu">
                                <?php
                                // Fetch main categories for dropdown
                                $categoryQuery = "SELECT * FROM categories WHERE parent_id IS NULL AND is_active = 1";
                                $categoryStmt = $pdo->query($categoryQuery);
                                $categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                foreach ($categories as $category) {
                                    echo '<a href="category.php?id='.$category['id'].'">'.$category['name'].'</a>';
                                }
                                ?>
                            </div>
                        </li>
                        <li><a href="about.php">About</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </nav>
                
                <div class="header-icons">
                    <a href="#" class="search-btn"><i class="fas fa-search"></i></a>
                    <a href="account.php" class="account-btn"><i class="fas fa-user"></i></a>
                    <a href="cart.php" class="cart-btn">
                        <i class="fas fa-shopping-bag"></i>
                        <span class="cart-count">0</span>
                    </a>
                </div>
                
                <div class="mobile-menu-btn">
                    <i class="fas fa-bars"></i>
                </div>
            </div>
        </div>
    </header>

    <!-- Mobile Menu -->
    <div class="mobile-menu">
        <div class="mobile-menu-close">
            <i class="fas fa-times"></i>
        </div>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li class="mobile-dropdown">
                <a href="#">Shop <i class="fas fa-chevron-down"></i></a>
                <div class="mobile-dropdown-menu">
                    <?php
                    foreach ($categories as $category) {
                        echo '<a href="category.php?id='.$category['id'].'">'.$category['name'].'</a>';
                    }
                    ?>
                </div>
            </li>
            <li><a href="about.php">About</a></li>
            <li><a href="contact.php">Contact</a></li>
        </ul>
    </div>

    <!-- Hero Slider -->
    <section class="hero-slider">
        <div class="slider-container">
            <?php if(count($sliders) > 0): ?>
                <?php foreach($sliders as $slider): ?>
                    <div class="slide">
                        <img src="uploads/slider/<?php echo htmlspecialchars($slider['image_path']); ?>" alt="<?php echo htmlspecialchars($slider['alt_text']); ?>">
                        <div class="slide-content">
                            <h2><?php echo htmlspecialchars($slider['title']); ?></h2>
                            <p><?php echo htmlspecialchars($slider['description']); ?></p>
                            <a href="<?php echo htmlspecialchars($slider['link_url']); ?>" class="btn">Shop Now</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="slide">
                    <img src="images/default-slide.jpg" alt="Default Slide">
                    <div class="slide-content">
                        <h2>Premium Bags Collection</h2>
                        <p>Discover our latest collection of high-quality bags and accessories</p>
                        <a href="#" class="btn">Shop Now</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <div class="slider-controls">
            <button class="prev-slide"><i class="fas fa-chevron-left"></i></button>
            <button class="next-slide"><i class="fas fa-chevron-right"></i></button>
        </div>
        <div class="slider-dots"></div>
    </section>