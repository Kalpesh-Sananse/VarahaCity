<?php
// Database connection
$db = new mysqli('localhost', 'root', '', 'mydb');
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professional Website</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- CSS Dependencies -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css">
    <link rel="stylesheet" href="../css/userindex.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="../images/logo.jpg" alt="Logo" height="40">
              Varaha City
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#"><span>Home</span></a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <span>Products</span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">New Arrivals</a></li>
                            <li><a class="dropdown-item" href="#">Best Sellers</a></li>
                            <li><a class="dropdown-item" href="#">Special Offers</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><span>About Us</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><span>Contact</span></a>
                    </li>
                </ul>
                
                <div class="nav-buttons d-flex align-items-center">
                    <a href="#" class="nav-icon me-3">
                        <i class="fas fa-search"></i>
                    </a>
                    <a href="#" class="nav-icon me-3">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-badge">0</span>
                    </a>
                    <a href="#" class="nav-icon me-3">
                        <i class="fas fa-user"></i>
                    </a>
                    <a href="#" class="btn btn-primary nav-btn">Shop Now</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Slider Section -->
    <section class="hero-slider">
        <div class="slider-container">
            <div id="userSlider">
                <?php 
                $user_sliders = $db->query("SELECT * FROM sliders WHERE is_active = 1 ORDER BY display_order ASC");
                if ($user_sliders->num_rows > 0):
                    while ($slider = $user_sliders->fetch_assoc()): 
                ?>
                    <div class="slider-item" data-link="<?= htmlspecialchars($slider['link_url']); ?>" 
                         style="background-image: url('<?= htmlspecialchars($slider['image_path']); ?>');">
                        <div class="slider-overlay"></div>
                        <div class="slider-content">
                            <div class="container">
                                <div class="row">
                                    <div class="col-lg-8">
                                        <?php if (!empty($slider['title'])): ?>
                                            <h1 class="hero-title"><?= htmlspecialchars($slider['title']); ?></h1>
                                        <?php endif; ?>
                                        <?php if (!empty($slider['description'])): ?>
                                            <p class="hero-description"><?= htmlspecialchars($slider['description']); ?></p>
                                        <?php endif; ?>
                                        <a href="<?= htmlspecialchars($slider['link_url']); ?>" class="hero-btn">
                                            Learn More
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php 
                    endwhile;
                endif;
                ?>
            </div>
        </div>
    </section>
    <!--categories -->
    
  
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script>
    <script src="../js/indexuser.js"></script>

    
</body>
</html>