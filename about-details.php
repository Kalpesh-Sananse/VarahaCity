<?php
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Detailed Information</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Add FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-overlay"></div>
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h1 class="hero-title">About Us</h1>
                <p class="hero-subtitle"><?php echo htmlspecialchars($about['title']); ?></p>
            </div>
        </div>
    </div>
</section>

<!-- Main Content Section -->
<section class="main-content">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Vision Section -->
                <div class="content-block">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <img src="./uploads/about/<?php echo htmlspecialchars($about['image']); ?>" 
                                 alt="About Us" class="img-fluid rounded shadow-lg">
                        </div>
                        <div class="col-md-6">
                            <div class="content-text">
                                <h2 class="section-title">Our Vision</h2>
                                <p class="section-description">
                                    <?php echo nl2br(htmlspecialchars($about['short_description'])); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detailed Description Section -->
                <div class="content-block">
                    <div class="detailed-content">
                        <h2 class="section-title text-center mb-4">Our Story</h2>
                        <div class="detailed-text">
                            <?php echo nl2br(htmlspecialchars($about['detailed_description'])); ?>
                        </div>
                        <div class="hashtag-section text-center mt-4">
                            <span class="hashtag"><?php echo htmlspecialchars($about['hashtag']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* General Styles */
body {
    font-family: 'Outfit', sans-serif;
    line-height: 1.8;
    color: #1B1B1B;
}

/* Hero Section */
.hero-section {
    position: relative;
    background: url('./uploads/about/<?php echo htmlspecialchars($about['image']); ?>') center/cover no-repeat;
    height: 400px;
    display: flex;
    align-items: center;
    text-align: center;
    margin-bottom: 80px;
}

.hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(10, 20, 47, 0.7);
}

.hero-title {
    color: #ffffff;
    font-size: 48px;
    font-weight: 700;
    position: relative;
    margin-bottom: 20px;
}

.hero-subtitle {
    color: #ffffff;
    font-size: 24px;
    font-weight: 400;
    position: relative;
    opacity: 0.9;
}

/* Main Content Section */
.main-content {
    padding: 0 0 80px;
}

.content-block {
    margin-bottom: 80px;
    background-color: #ffffff;
    border-radius: 15px;
    padding: 40px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
}

.section-title {
    color: #0A142F;
    font-size: 36px;
    font-weight: 700;
    margin-bottom: 25px;
}

.section-description {
    color: #1B1B1B;
    font-size: 16px;
    line-height: 1.8;
    text-align: justify;
}

.detailed-content {
    padding: 20px;
}

.detailed-text {
    font-size: 16px;
    line-height: 1.8;
    color: #1B1B1B;
    text-align: justify;
}

.hashtag-section {
    margin-top: 40px;
}

.hashtag {
    display: inline-block;
    padding: 12px 24px;
    background-color: #FFF5F1;
    color: #1B1B1B;
    font-weight: 600;
    border-radius: 30px;
    font-size: 18px;
}

/* Image Styles */
.img-fluid {
    border-radius: 15px;
    transition: transform 0.3s ease;
}

.img-fluid:hover {
    transform: scale(1.02);
}

/* Responsive Styles */
@media (max-width: 991px) {
    .hero-section {
        height: 400px;
    }

    .hero-title {
        font-size: 40px;
    }

    .hero-subtitle {
        font-size: 20px;
    }

    .section-title {
        font-size: 32px;
    }

    .content-block {
        padding: 30px;
        margin-bottom: 60px;
    }
}

@media (max-width: 767px) {
    .hero-section {
        height: 300px;
    }

    .hero-title {
        font-size: 32px;
    }

    .hero-subtitle {
        font-size: 18px;
    }

    .content-block {
        padding: 20px;
        margin-bottom: 40px;
    }

    .section-title {
        font-size: 28px;
        text-align: center;
    }

    .content-text {
        margin-top: 30px;
        text-align: center;
    }
}

@media (max-width: 575px) {
    .hero-section {
        height: 250px;
    }

    .hero-title {
        font-size: 28px;
    }

    .hero-subtitle {
        font-size: 16px;
    }

    .hashtag {
        font-size: 16px;
        padding: 10px 20px;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>