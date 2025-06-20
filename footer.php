<?php
// Set defaults
$current_datetime = '2025-05-30 18:31:05';
$current_user = 'Kalpesh-Sananse';

// Fetch active contact details
$query = "SELECT * FROM contact_details WHERE status = 'active' ORDER BY id DESC LIMIT 1";
$result = $conn->query($query);
$contact = $result->fetch_assoc();
?>

<style>
/* Modern Footer Styles with Safari Bags theme */
.footer {
    position: relative;
    padding: 70px 0 20px;
    color: #fff;
    overflow: hidden;
    margin-top: 50px;
    z-index: 1;
    background-color: #0b1023;
}

/* Dark blue gradient overlay */
.footer::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(180deg, 
        rgba(26, 35, 61, 0.97) 0%, 
        rgba(11, 16, 35, 0.95) 100%);
    z-index: -1;
}

/* Wave effect */
.footer::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 200px;
    background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1440 320'%3E%3Cpath fill='%23152456' fill-opacity='0.3' d='M0,96L48,112C96,128,192,160,288,186.7C384,213,480,235,576,224C672,213,768,171,864,154.7C960,139,1056,149,1152,154.7C1248,160,1344,160,1392,160L1440,160L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z'%3E%3C/path%3E%3C/svg%3E") no-repeat bottom;
    background-size: 100% 100%;
    z-index: -1;
    opacity: 0.4;
    animation: wave 15s linear infinite;
}

@keyframes wave {
    0% { background-position-x: 0; }
    100% { background-position-x: 1440px; }
}

.footer .container {
    position: relative;
    z-index: 2;
}

.footer-logo {
    max-width: 180px;
    margin-bottom: 20px;
    filter: brightness(1.2);
}

.footer-description {
    color: rgba(255, 255, 255, 0.9);
    font-size: 15px;
    line-height: 1.8;
    margin-bottom: 25px;
}

.footer-heading {
    color: #fff;
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 25px;
    position: relative;
    padding-bottom: 10px;
}

.footer-heading::after {
    content: '';
    position: absolute;
    left: 0;
    bottom: 0;
    width: 40px;
    height: 2px;
    background: #3d5af1;
    transition: width 0.3s ease;
}

.footer-col:hover .footer-heading::after {
    width: 60px;
}

.footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-links li {
    margin-bottom: 12px;
}

.footer-links a {
    color: rgba(255, 255, 255, 0.9);
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    font-size: 15px;
    position: relative;
    padding-left: 5px;
}

.footer-links a::before {
    content: '';
    position: absolute;
    left: 0;
    bottom: -2px;
    width: 0;
    height: 1px;
    background: #3d5af1;
    transition: width 0.3s ease;
}

.footer-links a:hover {
    color: #3d5af1;
    transform: translateX(5px);
}

.footer-links a:hover::before {
    width: 100%;
}

.footer-links i {
    margin-right: 8px;
    font-size: 14px;
    color: #3d5af1;
}

.footer-contact-info {
    color: rgba(255, 255, 255, 0.9);
    margin-bottom: 15px;
    display: flex;
    align-items: start;
}

.footer-contact-info i {
    margin-right: 12px;
    color: #3d5af1;
    font-size: 18px;
}

.social-links {
    margin-top: 25px;
}

.social-links a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: rgba(61, 90, 241, 0.1);
    color: #fff;
    margin-right: 10px;
    text-decoration: none;
    transition: all 0.3s ease;
    border: 1px solid rgba(61, 90, 241, 0.2);
}

.social-links a:hover {
    background: #3d5af1;
    transform: translateY(-3px) scale(1.1);
    border-color: #3d5af1;
    box-shadow: 0 5px 15px rgba(61, 90, 241, 0.2);
}

.footer-bottom {
    background: rgba(11, 16, 35, 0.8);
    padding: 20px 0;
    margin-top: 50px;
    position: relative;
    z-index: 2;
    backdrop-filter: blur(10px);
}

.footer-bottom::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(to right, 
        transparent, 
        rgba(61, 90, 241, 0.2), 
        transparent);
}

.footer-bottom-text {
    color: rgba(255, 255, 255, 0.8);
    font-size: 14px;
}

.footer-bottom-links {
    text-align: right;
}

.footer-bottom-links a {
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    margin-left: 20px;
    font-size: 14px;
    transition: color 0.3s ease;
}

.footer-bottom-links a:hover {
    color: #3d5af1;
}

/* Animations */
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

.footer-col {
    animation: fadeInUp 0.6s ease forwards;
    opacity: 0;
}

.footer-col:nth-child(1) { animation-delay: 0.1s; }
.footer-col:nth-child(2) { animation-delay: 0.2s; }
.footer-col:nth-child(3) { animation-delay: 0.3s; }
.footer-col:nth-child(4) { animation-delay: 0.4s; }

/* Responsive styles */
@media (max-width: 991.98px) {
    .footer {
        padding: 50px 0 20px;
    }

    .footer-col {
        margin-bottom: 40px;
    }
}

@media (max-width: 767.98px) {
    .footer-bottom-links {
        text-align: center;
        margin-top: 15px;
    }

    .footer-bottom-text {
        text-align: center;
    }

    .footer-bottom-links a {
        margin: 0 10px;
    }

    .footer-contact-info {
        font-size: 14px;
    }
}
</style>

<footer class="footer">
    <div class="container">
        <div class="row">
            <!-- Company Info -->
            <div class="col-lg-4 col-md-6 footer-col">
                <img src="./images/logo.jpg" alt="Varaha City" class="footer-logo">
                <p class="footer-description">
                    Building dreams into reality. Varaha City offers premium residential and commercial properties
                    designed for modern living and sustainable future.
                </p>
                <div class="social-links">
                    <?php if($contact['facebook']): ?>
                    <a href="<?php echo htmlspecialchars($contact['facebook']); ?>" target="_blank" aria-label="Facebook">
                        <i class="bi bi-facebook"></i>
                    </a>
                    <?php endif; ?>
                    <?php if($contact['instagram']): ?>
                    <a href="<?php echo htmlspecialchars($contact['instagram']); ?>" target="_blank" aria-label="Instagram">
                        <i class="bi bi-instagram"></i>
                    </a>
                    <?php endif; ?>
                    <?php if($contact['linkedin']): ?>
                    <a href="<?php echo htmlspecialchars($contact['linkedin']); ?>" target="_blank" aria-label="LinkedIn">
                        <i class="bi bi-linkedin"></i>
                    </a>
                    <?php endif; ?>
                    <?php if($contact['twitter']): ?>
                    <a href="<?php echo htmlspecialchars($contact['twitter']); ?>" target="_blank" aria-label="Twitter">
                        <i class="bi bi-twitter"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-2 col-md-6 footer-col">
                <h4 class="footer-heading">Quick Links</h4>
                <ul class="footer-links">
                    <li><a href="about-details.php"><i class="bi bi-chevron-right"></i>About Us</a></li>
                    <li><a href="categories.php"><i class="bi bi-chevron-right"></i>Properties</a></li>
                    <li><a href="/services"><i class="bi bi-chevron-right"></i>Services</a></li>
                    <li><a href="/gallery"><i class="bi bi-chevron-right"></i>Gallery</a></li>
                    <li><a href="contact-us.php"><i class="bi bi-chevron-right"></i>Contact</a></li>
                </ul>
            </div>

            <!-- Our Services -->
            <div class="col-lg-3 col-md-6 footer-col">
                <h4 class="footer-heading">Our Services</h4>
                <ul class="footer-links">
                    <li><a href="#"><i class="bi bi-chevron-right"></i>Residential Properties</a></li>
                    <li><a href="#"><i class="bi bi-chevron-right"></i>Commercial Spaces</a></li>
                    <li><a href="#"><i class="bi bi-chevron-right"></i>Investment Consulting</a></li>
                    <li><a href="#"><i class="bi bi-chevron-right"></i>Property Management</a></li>
                    <li><a href="#"><i class="bi bi-chevron-right"></i>Legal Services</a></li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div class="col-lg-3 col-md-6 footer-col">
                <h4 class="footer-heading">Contact Info</h4>
                <?php if($contact): ?>
                <div class="footer-contact-info">
                    <i class="bi bi-geo-alt"></i>
                    <span><?php echo nl2br(htmlspecialchars($contact['address'])); ?></span>
                </div>
                <div class="footer-contact-info">
                    <i class="bi bi-telephone"></i>
                    <span>
                        <a href="tel:<?php echo htmlspecialchars($contact['phone']); ?>" style="color: inherit; text-decoration: none;">
                            <?php echo htmlspecialchars($contact['phone']); ?>
                        </a>
                    </span>
                </div>
                <div class="footer-contact-info">
                    <i class="bi bi-envelope"></i>
                    <span>
                        <a href="mailto:<?php echo htmlspecialchars($contact['email']); ?>" style="color: inherit; text-decoration: none;">
                            <?php echo htmlspecialchars($contact['email']); ?>
                        </a>
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer Bottom -->
    <div class="footer-bottom">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="footer-bottom-text">
                        © <?php echo date('Y'); ?> Varaha City. All rights reserved.
                        <br>
                       
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="footer-bottom-links">
                        <a href="/privacy-policy">Privacy Policy</a>
                        <a href="/terms-conditions">Terms & Conditions</a>
                        <a href="/sitemap">Sitemap</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Optional: Add this script for enhanced animations -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Observe footer columns for animation
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, {
        threshold: 0.1
    });

    document.querySelectorAll('.footer-col').forEach(col => {
        observer.observe(col);
    });

    // Enhanced hover effects for links
    document.querySelectorAll('.footer-links a').forEach(link => {
        link.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(5px)';
        });
        link.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0)';
        });
    });
});
</script>