<?php
require_once './includes/db_connection.php';

// Fetch active contact details
$query = "SELECT * FROM contact_details WHERE status = 'active' ORDER BY id DESC LIMIT 1";
$result = $conn->query($query);
$contact = $result->fetch_assoc();

// Check if contact details exist
if (!$contact) {
    exit('Contact information is currently unavailable.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Varaha City</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Outfit', sans-serif;
            color: #1B1B1B;
            line-height: 1.8;
        }
        .hero-section {
            background-color: #0A142F;
            padding: 80px 0 60px 0;
            position: relative;
            margin-bottom: 60px;
        }
        .hero-content {
            position: relative;
            z-index: 1;
            text-align: center;
            color: #fff;
        }
        .hero-title {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 20px;
            letter-spacing: 2px;
        }
        .hero-subtitle {
            font-size: 20px;
            opacity: 0.95;
            max-width: 600px;
            margin: 0 auto;
            font-weight: 500;
        }
        .contact-section {
            padding: 50px 0 40px 0;
        }
        .contact-card {
            background-color: #fff;
            border-radius: 18px;
            box-shadow: 0 5px 28px rgba(44, 62, 80, 0.07);
            padding: 38px 32px;
            height: 100%;
            transition: transform 0.18s;
            position: relative;
            overflow: hidden;
        }
        .contact-card:hover {
            transform: translateY(-7px) scale(1.015);
            box-shadow: 0 8px 38px rgba(44, 62, 80, 0.11);
        }
        .contact-icon {
            width: 62px;
            height: 62px;
            background: linear-gradient(135deg, #4D77FF, #0A142F);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 22px;
        }
        .contact-icon i {
            font-size: 28px;
            color: #fff;
        }
        .contact-title {
            font-size: 22px;
            font-weight: 700;
            color: #0A142F;
            margin-bottom: 12px;
        }
        .contact-info {
            color: #1B1B1B;
            font-size: 17px;
            font-weight: 500;
        }
        .social-links {
            margin-top: 18px;
        }
        .social-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #f8f9fa;
            color: #0A142F;
            margin-right: 12px;
            font-size: 1.45rem;
            box-shadow: 0 2px 8px rgba(77,119,255,0.07);
            text-decoration: none;
            transition: all 0.25s;
        }
        .social-link:hover {
            background: linear-gradient(135deg, #4D77FF, #0A142F);
            color: #fff;
            transform: translateY(-2px) scale(1.08);
        }
        .location-section {
            padding: 50px 0 40px 0;
            background: #f4f7fa;
        }
        .map-wrapper {
            margin-top: 20px;
            border-radius: 16px;
            overflow: hidden;
            background-color: #e4e8f3;
            box-shadow: 0 2px 18px rgba(44,62,80,0.08);
        }
        .map-container {
            position: relative;
            width: 100%;
            height: 420px;
        }
        .map-container iframe {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            border: 0;
        }
        .map-placeholder {
            width: 100%;
            height: 420px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            border-radius: 14px;
        }
        .placeholder-content {
            text-align: center;
            color: #6c757d;
        }
        .placeholder-content i {
            font-size: 46px;
            margin-bottom: 12px;
        }
        .placeholder-content p {
            margin: 0;
            font-size: 16px;
        }
        @media (max-width: 991.98px) {
            .contact-card { padding: 28px 16px;}
            .hero-title { font-size: 32px; }
        }
        @media (max-width: 767.98px) {
            .hero-section { padding: 50px 0 32px 0;}
            .contact-section, .location-section { padding: 32px 0 20px 0; }
            .map-container, .map-placeholder { height: 300px; }
        }
    </style>
</head>
<body>
    
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">Contact Us</h1>
                <p class="hero-subtitle">We're here for your queries about our properties, investments, or partnership opportunities.</p>
            </div>
        </div>
    </section>

    <!-- Contact Information Section -->
    <section class="contact-section">
        <div class="container">
            <div class="row justify-content-center">
                <!-- Email -->
                <div class="col-md-4 mb-4">
                    <div class="contact-card text-center h-100">
                        <div class="contact-icon mx-auto">
                            <i class="bi bi-envelope"></i>
                        </div>
                        <div class="contact-title">Email Us</div>
                        <div class="contact-info">
                            <a href="mailto:<?php echo htmlspecialchars($contact['email']); ?>" class="text-decoration-none text-dark">
                                <?php echo htmlspecialchars($contact['email']); ?>
                            </a>
                        </div>
                    </div>
                </div>
                <!-- Phone & WhatsApp -->
                <div class="col-md-4 mb-4">
                    <div class="contact-card text-center h-100">
                        <div class="contact-icon mx-auto">
                            <i class="bi bi-telephone"></i>
                        </div>
                        <div class="contact-title">Call / WhatsApp</div>
                        <div class="contact-info">
                            <a href="tel:<?php echo htmlspecialchars($contact['phone']); ?>" class="text-decoration-none text-dark">
                                <?php echo htmlspecialchars($contact['phone']); ?>
                            </a>
                            <?php if($contact['whatsapp']): ?>
                                <span class="d-block mt-2">
                                    <i class="bi bi-whatsapp text-success"></i>
                                    <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $contact['whatsapp']); ?>"
                                       class="text-decoration-none text-dark" target="_blank">
                                        <?php echo htmlspecialchars($contact['whatsapp']); ?>
                                    </a>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <!-- Address -->
                <div class="col-md-4 mb-4">
                    <div class="contact-card text-center h-100">
                        <div class="contact-icon mx-auto">
                            <i class="bi bi-geo-alt"></i>
                        </div>
                        <div class="contact-title">Visit Us</div>
                        <div class="contact-info">
                            <?php echo nl2br(htmlspecialchars($contact['address'])); ?>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Social Media Links -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="contact-card text-center">
                        <div class="contact-title mb-2">Connect With Us</div>
                        <div class="social-links">
                            <?php if($contact['facebook']): ?>
                                <a href="<?php echo htmlspecialchars($contact['facebook']); ?>" class="social-link" target="_blank" aria-label="Facebook">
                                    <i class="bi bi-facebook"></i>
                                </a>
                            <?php endif; ?>
                            <?php if($contact['instagram']): ?>
                                <a href="<?php echo htmlspecialchars($contact['instagram']); ?>" class="social-link" target="_blank" aria-label="Instagram">
                                    <i class="bi bi-instagram"></i>
                                </a>
                            <?php endif; ?>
                            <?php if($contact['linkedin']): ?>
                                <a href="<?php echo htmlspecialchars($contact['linkedin']); ?>" class="social-link" target="_blank" aria-label="LinkedIn">
                                    <i class="bi bi-linkedin"></i>
                                </a>
                            <?php endif; ?>
                            <?php if($contact['twitter']): ?>
                                <a href="<?php echo htmlspecialchars($contact['twitter']); ?>" class="social-link" target="_blank" aria-label="Twitter">
                                    <i class="bi bi-twitter"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Google Maps Section -->
    <section class="location-section">
        <div class="container">
            <div class="contact-card">
                <div class="contact-title text-center mb-3"><i class="bi bi-geo-alt me-2"></i>Our Location</div>
                <div class="map-wrapper">
                <?php 
                if(!empty($contact['maps_link']) && strpos($contact['maps_link'], 'google.com/maps/embed') !== false): ?>
                    <div class="map-container">
                        <iframe 
                            src="<?php echo htmlspecialchars($contact['maps_link']); ?>"
                            allowfullscreen
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                <?php else: ?>
                    <div class="map-placeholder">
                        <div class="placeholder-content">
                            <i class="bi bi-geo-alt"></i>
                            <p>Please provide a valid Google Maps embed URL</p>
                        </div>
                    </div>
                <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    <footer class="text-center py-3 text-secondary small mt-3">
        &copy; <?php echo date('Y'); ?> Varaha City. All rights reserved.
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
</body>
</html>