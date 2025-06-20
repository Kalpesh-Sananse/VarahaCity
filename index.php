<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

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
    <title>VarahaCity</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <!-- CSS Dependencies -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css">
    <link rel="stylesheet" href="./css/userindex.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Inter:wght@400;500&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Add this in your head section -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="path/to/css/faq-styles.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</head>

<style>
/* FAQ Section Styles */
.faqs-section {
    padding: 80px 0;
    background-color: var(--white);
    position: relative;
}

.faqs-section .section-title {
    font-size: 42px;
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 30px;
}

.faqs-container {
    margin-top: 30px;
}

.faq-card {
    background: var(--white);
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 20px;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 1px solid #E0E3EB;
    position: relative;
}

.faq-card:hover {
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
    transform: translateY(-2px);
}

.faq-number {
    font-size: 24px;
    font-weight: 700;
    color: var(--secondary-color);
    margin-bottom: 16px;
    display: flex;
    align-items: center;
}

.faq-number:before {
    content: '';
    width: 32px;
    height: 2px;
    background: var(--secondary-color);
    margin-right: 12px;
    transition: width 0.3s ease;
}

.faq-question {
    font-size: 18px;
    font-weight: 600;
    color: var(--text-color);
    margin-bottom: 0;
    padding-right: 30px;
    position: relative;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.arrow-icon {
    position: absolute;
    right: 0;
    top: 50%;
    transform: translateY(-50%);
    transition: transform 0.3s ease;
    color: var(--secondary-color);
}

.faq-card.active .arrow-icon {
    transform: translateY(-50%) rotate(180deg);
}

.faq-answer {
    font-size: 16px;
    line-height: 1.6;
    color: #626687;
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid #E0E3EB;
    display: none;
}

.faq-card.active .faq-answer {
    display: block;
    animation: fadeIn 0.4s ease;
}

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

/* Responsive styles */
@media (max-width: 991px) {
    .faqs-section {
        padding: 60px 0;
    }

    .faqs-section .section-title {
        font-size: 36px;
    }
}

@media (max-width: 768px) {
    .faqs-section .section-title {
        font-size: 32px;
    }

    .faq-question {
        font-size: 16px;
    }
}

@media (max-width: 576px) {
    .faqs-section {
        padding: 40px 0;
    }

    .faq-card {
        padding: 16px;
    }
}

.page-container {
    max-width: 1400px;
    margin: 10px auto;
    padding: 0 20px;
}

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
    max-width: 700px;
    
    margin: 0 auto 30px auto;
}

.search-input {
    border-radius: 50px;
    height: 50px;
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
    border-radius: 10px;
    background: linear-gradient(135deg, #2980b9, #6dd5fa);
    color: white;
    height: 50px;
    border: none;
    padding: 7px 20px;
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
    from {
        opacity: 0;
        transform: translateY(20px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
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

<body>
    <!-- Navigation Bar -->
    <?php include 'navbar.php'; ?>
    <!-- Hero Slider Section -->
    <section class="hero-slider">
        <div class="slider-container">
            <div id="userSlider">
                <?php 
                $user_sliders = $db->query("SELECT * FROM sliders WHERE is_active = 1 ORDER BY display_order ASC");
                if ($user_sliders && $user_sliders->num_rows > 0):
                    while ($slider = $user_sliders->fetch_assoc()): 
                ?>
                <div class="slider-item" data-link="<?= htmlspecialchars($slider['link_url']); ?>"
                    style="background-image: url('<?= htmlspecialchars(str_replace('../uploads/', './uploads/', $slider['image_path'])); ?>');">
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
                                    <a href="<?= htmlspecialchars($slider['link_url'] ?? '#'); ?>" class="hero-btn">
                                        Learn More
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php 
                    endwhile;
                else:
                ?>
                <!-- Default slide -->
                <div class="slider-item" style="background-image: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="slider-overlay"></div>
                    <div class="slider-content">
                        <div class="container">
                            <div class="row">
                                <div class="col-lg-8">
                                    <h1 class="hero-title">Welcome to Varaha City</h1>
                                    <p class="hero-description">Discover amazing properties and experiences</p>
                                    <a href="#" class="hero-btn">Explore Now</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <!-- Categories Section -->
    <section class="categories-section">
        <div class="container">
            <?php
        // Fetch categories with proper error handling
        try {
            $categories_query = "SELECT * FROM categories ORDER BY name ASC";
            $categories_result = $db->query($categories_query);
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
            <div class="page-container">
               

<!-- Search Bar -->
<div class="search-container">
    <form action="search_results.php" method="GET" class="row g-2" id="searchForm">
        <div class="col-md-8">
            <input type="text" 
                   class="form-control search-input" 
                   name="q" 
                   id="searchInput"
                   placeholder="Search properties, cities, categories..."
                   required>
        </div>
        <div class="col-md-4">
            <button type="submit" class="search-btn w-100">
                <i class="fas fa-search me-2"></i> Search
            </button>
        </div>
    </form>
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
                    <a href="getsubcategoriesuser.php?id=<?php echo $category['id']; ?>" class="category-card"
                        style="animation-delay: <?php echo $index * 0.1; ?>s;">
                        <div class="category-image-container">
                            <img src="<?php echo $displayPath; ?>"
                                alt="<?php echo htmlspecialchars($category['name']); ?>" class="category-image"
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
                   
                </div>
                <?php endif; ?>


            </div>
    </section>

    <!--why choose us section-->
    <section class="why-choose-section">
        <div class="container">
            <div class="row align-items-center">
                <!-- Left Column - Image -->
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <div class="image-container">
                        <img src="./images/1747912162_pexels-pixabay-280229.jpg" alt="Luxury Building"
                            class="rounded-image">
                    </div>
                </div>

                <!-- Right Column - Content -->
                <div class="col-lg-6">
                    <div class="content-container">
                        <h2 class="main-title">Why choose us for the purchase of your dream home?</h2>

                        <!-- Features List -->
                        <div class="features-list">
                            <!-- Craftsmanship -->
                            <div class="feature-item">
                                <div class="feature-icon blue">
                                    <i class="bi bi-person-workspace"></i>
                                </div>
                                <div class="feature-content">
                                    <h3>Exceptional Craftsmanship:</h3>
                                    <p>Our homes are more than structures – they're crafted with unparalleled attention
                                        to detail.</p>
                                </div>
                            </div>

                            <!-- Investment -->
                            <div class="feature-item">
                                <div class="feature-icon purple">
                                    <i class="bi bi-graph-up-arrow"></i>
                                </div>
                                <div class="feature-content">
                                    <h3>Smart Investment:</h3>
                                    <p>Our properties not only offer a dream living space but also promise a sound
                                        investment for your future.</p>
                                </div>
                            </div>

                            <!-- Ownership -->
                            <div class="feature-item">
                                <div class="feature-icon green">
                                    <i class="bi bi-house-check"></i>
                                </div>
                                <div class="feature-content">
                                    <h3>Effortless Ownership:</h3>
                                    <p>Enjoy a hassle-free homebuying journey with our dedicated team guiding you every
                                        step of the way.</p>
                                </div>
                            </div>
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
    .why-choose-section {
        padding: 80px 0;
        background-color: #fff;
    }

    .image-container {
        position: relative;
        overflow: hidden;
    }

    .rounded-image {
        width: 100%;
        height: auto;
        border-radius: 24px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }

    .rounded-image:hover {
        transform: scale(1.02);
    }

    .content-container {
        padding: 20px;
    }

    .main-title {
        font-size: 40px;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 40px;
        line-height: 1.2;
    }

    .features-list {
        margin-top: 30px;
    }

    .feature-item {
        display: flex;
        align-items: flex-start;
        margin-bottom: 30px;
    }

    .feature-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 20px;
        flex-shrink: 0;
    }

    .feature-icon i {
        font-size: 24px;
        color: #fff;
    }

    .feature-icon.blue {
        background-color: #4285f4;
    }

    .feature-icon.purple {
        background-color: #a855f7;
    }

    .feature-icon.green {
        background-color: #22c55e;
    }

    .feature-content h3 {
        font-size: 20px;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 8px;
    }

    .feature-content p {
        font-size: 16px;
        color: #666;
        line-height: 1.6;
        margin: 0;
    }

    .learn-more-btn {
        display: inline-block;
        padding: 12px 32px;
        background-color: #4285f4;
        color: #fff;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 500;
        margin-top: 20px;
        transition: all 0.3s ease;
    }

    .learn-more-btn:hover {
        background-color: #3367d6;
        transform: translateY(-2px);
        color: #fff;
    }

    /* Responsive Styles */
    @media (max-width: 991.98px) {
        .why-choose-section {
            padding: 60px 0;
        }

        .main-title {
            font-size: 32px;
            margin-bottom: 30px;
        }
    }

    @media (max-width: 767.98px) {
        .why-choose-section {
            padding: 40px 0;
        }

        .main-title {
            font-size: 28px;
            margin-bottom: 25px;
        }

        .feature-item {
            margin-bottom: 25px;
        }

        .feature-icon {
            width: 45px;
            height: 45px;
        }

        .feature-content h3 {
            font-size: 18px;
        }

        .feature-content p {
            font-size: 15px;
        }
    }
    </style>



    <?php
// Set timezone and get current date/time
date_default_timezone_set('UTC');
$current_datetime = '2025-05-27 18:01:16';
$current_user = 'Kalpesh-Sananse';

// Database connection
require_once './includes/db_connection.php';

// Fetch active FAQs
$query = "SELECT * FROM faqs WHERE status = 'active' ORDER BY question_number ASC";
$faqs = $conn->query($query);

// Check if we have FAQs
$has_faqs = $faqs && $faqs->num_rows > 0;
?>

    <!-- FAQ Section Start -->
    <section class="faq-section" id="faq">
        <div class="container">
            <!-- Section Header -->
            <div class="row justify-content-between align-items-center mb-lg-5 mb-4">
                <div class="col-lg-6">
                    <h2 class="section-title" data-aos="fade-up">
                        Frequently Asked Questions
                    </h2>
                </div>

            </div>

            <!-- FAQs Grid -->
            <div class="faqs-container">
                <?php if ($has_faqs): ?>
                <div class="row">
                    <?php 
                    $delay = 150;
                    while($faq = $faqs->fetch_assoc()): 
                    ?>
                    <div class="col-lg-6 mb-2" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                        <div class="faq-card">
                            <div class="faq-number">
                                <?php echo htmlspecialchars($faq['question_number']); ?>
                            </div>
                            <h3 class="faq-question">
                                <?php echo htmlspecialchars($faq['question']); ?>
                                <span class="arrow-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M19 9L12 16L5 9" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </span>
                            </h3>
                            <div class="faq-answer">
                                <?php echo nl2br(htmlspecialchars($faq['answer'])); ?>
                            </div>
                        </div>
                    </div>
                    <?php 
                    $delay += 50;
                    endwhile; 
                    ?>
                </div>
                <?php else: ?>
                <!-- Empty State -->
                <div class="faq-empty" data-aos="fade-up">
                    <div class="faq-empty-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z"
                                stroke="currentColor" stroke-width="2" />
                            <path d="M12 17V17.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                            <path
                                d="M12 14C12.3416 14 12.6666 13.8829 12.9083 13.6713C13.15 13.4597 13.2916 13.1668 13.2916 12.8607C13.2916 12.5546 13.15 12.2617 12.9083 12.0501C12.6666 11.8385 12.3416 11.7214 12 11.7214C11.6584 11.7214 11.3334 11.8385 11.0917 12.0501C10.85 12.2617 10.7084 12.5546 10.7084 12.8607C10.7084 13.1668 10.85 13.4597 11.0917 13.6713C11.3334 13.8829 11.6584 14 12 14Z"
                                fill="currentColor" />
                        </svg>
                    </div>
                    <p class="faq-empty-text">No FAQs available at the moment.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <!-- FAQ Section End -->

    <section><?php
require_once './includes/db_connection.php';

// Fetch active about us content
$query = "SELECT * FROM about_us WHERE status = 'active' ORDER BY id DESC LIMIT 1";
$result = $conn->query($query);
$about = $result->fetch_assoc();

// Check if data exists
if (!$about) {
    exit('No about us content found.');
}
?>

        <section class="about-section">
            <div class="container-fluid p-0">
                <div class="row g-0">
                    <!-- Left side - Image -->
                    <div class="col-lg-6">
                        <div class="about-image-container">
                            <img src="./uploads/about/<?php echo htmlspecialchars($about['image']); ?>"
                                alt="About Us Image" class="about-image">
                        </div>
                    </div>

                    <!-- Right side - Content -->
                    <div class="col-lg-6">
                        <div class="about-content">
                            <div class="about-content-inner">
                                <div class="about-label">ABOUT US</div>
                                <h1 class="about-title">
                                    <?php echo htmlspecialchars($about['title']); ?>
                                </h1>
                                <div class="about-description">
                                    <?php echo nl2br(htmlspecialchars($about['short_description'])); ?>
                                </div>
                                <div class="about-tag">
                                    <?php echo htmlspecialchars($about['hashtag']); ?>
                                </div>
                                <a href="about-details.php" class="learn-more-btn">LEARN MORE</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <style>
        .about-section {
            width: 100%;
            overflow: hidden;
            position: relative;
        }

        .about-image-container {
            width: 100%;
            height: 100%;
            min-height: 700px;
            /* Increased height */
            position: relative;
        }

        .about-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            top: 0;
            left: 0;
        }

        .about-content {
            background-color: #FFF5F1;
            padding: 120px 80px;
            /* Increased padding */
            height: 100%;
            min-height: 700px;
            /* Increased height to match image */
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .about-content-inner {
            max-width: 570px;
            margin-left: 40px;
            /* Added margin for better alignment */
        }

        .about-label {
            font-size: 14px;
            font-weight: 600;
            color: #1a1a1a;
            letter-spacing: 0.5px;
            margin-bottom: 24px;
            text-transform: uppercase;
        }

        .about-title {
            font-family: 'Outfit', sans-serif;
            font-size: 48px;
            font-weight: 700;
            line-height: 1.2;
            color: #0A142F;
            margin-bottom: 32px;
            /* Increased margin */
            letter-spacing: -0.5px;
            /* Added letter spacing */
        }

        .about-description {
            font-size: 16px;
            line-height: 1.8;
            color: #1a1a1a;
            margin-bottom: 24px;
            text-align: justify;
            /* Added text justify */
            opacity: 0.9;
            /* Slightly reduced opacity for better readability */
        }

        .about-tag {
            font-size: 16px;
            font-weight: 500;
            color: #1a1a1a;
            margin-bottom: 40px;
            /* Increased margin */
        }

        .learn-more-btn {
            display: inline-block;
            padding: 16px 32px;
            background-color: #4D77FF;
            color: #ffffff;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            border-radius: 4px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            min-width: 160px;
            /* Added minimum width */
            text-align: center;
        }

        .learn-more-btn:hover {
            background-color: #3D67FF;
            color: #ffffff;
            transform: translateY(-2px);
        }

        /* Responsive Styles */
        @media (max-width: 1199px) {
            .about-content {
                padding: 100px 60px;
            }

            .about-content-inner {
                margin-left: 20px;
            }
        }

        @media (max-width: 991px) {
            .about-image-container {
                min-height: 500px;
            }

            .about-content {
                padding: 80px 40px;
                min-height: 500px;
            }

            .about-content-inner {
                margin-left: 0;
            }

            .about-title {
                font-size: 36px;
                margin-bottom: 24px;
            }
        }

        @media (max-width: 767px) {
            .about-image-container {
                min-height: 400px;
            }

            .about-content {
                padding: 60px 30px;
                min-height: auto;
            }

            .about-title {
                font-size: 32px;
            }

            .about-description {
                font-size: 15px;
            }
        }

        @media (max-width: 575px) {
            .about-image-container {
                min-height: 300px;
            }

            .about-content {
                padding: 40px 20px;
            }

            .about-title {
                font-size: 28px;
            }

            .learn-more-btn {
                width: 100%;
            }
        }
        </style>

    </section>

        <?php include 'footer.php'; ?>

    <!-- Initialize AOS and FAQ functionality -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize AOS
        AOS.init({
            duration: 800,
            once: true,
            offset: 100
        });

        // FAQ Toggle functionality
        const faqCards = document.querySelectorAll('.faq-card');

        faqCards.forEach(card => {
            card.addEventListener('click', function() {
                const wasActive = this.classList.contains('active');

                // Close all other FAQs
                faqCards.forEach(c => {
                    if (c !== this) {
                        c.classList.remove('active');
                        const answer = c.querySelector('.faq-answer');
                        answer.style.maxHeight = null;
                    }
                });

                // Toggle current FAQ
                this.classList.toggle('active');

                // Animate answer height
                const answer = this.querySelector('.faq-answer');
                if (!wasActive) {
                    answer.style.display = 'block';
                    answer.style.maxHeight = answer.scrollHeight + 'px';
                } else {
                    answer.style.maxHeight = null;
                    setTimeout(() => {
                        if (!this.classList.contains('active')) {
                            answer.style.display = 'none';
                        }
                    }, 300);
                }
            });
        });
    });
    </script>

  

    <script>
// Optional: Live search as user types (with debounce)
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    
    // Debounce function to limit how often search runs
    function debounce(func, wait) {
        let timeout;
        return function() {
            const context = this, args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(context, args), wait);
        };
    }

    // Optional: Live search functionality
    searchInput.addEventListener('input', debounce(function() {
        const query = this.value.trim();
        if(query.length > 2) { // Only search after 3+ characters
            // You could add AJAX live search here if needed
        }
    }, 300));
});
    </script>


    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script>
    <script src="./js/indexuser.js"></script>
</body>

</html>