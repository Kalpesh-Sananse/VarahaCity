<?php
// Start session if not already started

?>

<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
        <!-- Brand -->
        <a class="navbar-brand" href="index.php">
            <img src="./images/logo.jpg" alt="Logo" height="40">
            <span>Varaha City</span>
        </a>

        <!-- Custom Toggle Button -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <div class="hamburger-menu">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <!-- Home -->
                <li class="nav-item">
                    <a class="nav-link active" href="index.php"><span>Home</span></a>
                </li>
                
                <!-- Products/Properties Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                        <span>Properties</span>
                        <i class="fas fa-chevron-down ms-1"></i>
                    </a>
                    <?php
                    // Fetch categories
                    $categories_query = "SELECT * FROM categories ORDER BY name ASC";
                    try {
                        $result = $db->query($categories_query);
                        if ($result && $result->num_rows > 0):
                    ?>
                    <ul class="dropdown-menu main-dropdown">
                        <?php while ($category = $result->fetch_assoc()): ?>
                        <li class="nav-item dropend category-item">
                            <a class="dropdown-item category-link" href="getsubcategoriesuser.php?id=<?= $category['id'] ?>" 
                               data-category-id="<?= $category['id'] ?>">
                                <span><?= htmlspecialchars($category['name']) ?></span>
                                <i class="fas fa-chevron-right float-end mt-1 submenu-arrow"></i>
                            </a>
                            <?php
                            // Fetch subcategories for this category
                            $subcategories_query = "SELECT * FROM subcategories WHERE parent_category_id = " . $category['id'] . " ORDER BY name ASC";
                            $sub_result = $db->query($subcategories_query);
                            if ($sub_result && $sub_result->num_rows > 0):
                            ?>
                            <ul class="dropdown-menu sub-dropdown">
                                <?php while ($subcategory = $sub_result->fetch_assoc()): ?>
                                <li>
                                    <a class="dropdown-item sub-item" href="getsubcdetails.php?id=<?= $subcategory['id'] ?>">
                                        <i class="fas fa-dot-circle me-2"></i>
                                        <?= htmlspecialchars($subcategory['name']) ?>
                                    </a>
                                </li>
                                <?php endwhile; ?>
                            </ul>
                            <?php endif; ?>
                        </li>
                        <?php endwhile; ?>
                    </ul>
                    <?php
                        endif;
                    } catch (Exception $e) {
                        error_log("Error fetching categories: " . $e->getMessage());
                    }
                    ?>
                </li>
                
                <!-- About Us -->
                <li class="nav-item">
                    <a class="nav-link" href="about-details.php"><span>About Us</span></a>
                </li>
                
                <!-- Privacy Policy -->
                <li class="nav-item">
                    <a class="nav-link" href="privacy-policy.php"><span>Privacy Policy</span></a>
                </li>
                
                <!-- Contact -->
                <li class="nav-item">
                    <a class="nav-link" href="contact-us.php"><span>Contact</span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="wishlist.php"><span>Wishlist</span></a>
                </li>
            </ul>

            <!-- Update the authentication section -->
<div class="nav-buttons">
    <?php if(isset($_SESSION['username']) && !empty($_SESSION['username'])): ?>
        <div class="dropdown profile-dropdown">
            <button class="btn profile-btn" type="button" data-bs-toggle="dropdown">
                <i class="fas fa-user-circle"></i>
                <span class="d-none d-lg-inline ms-2"><?= htmlspecialchars($_SESSION['username']) ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item" href="profile.php">
                        <i class="fas fa-user me-2"></i>Profile
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="cart.php">
                        <i class="fas fa-shopping-cart me-2"></i>My Cart
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item text-danger" href="logout.php">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </a>
                </li>
            </ul>
        </div>
    <?php else: ?>
        <a href="login.php" class="btn login-btn">
            <i class="fas fa-sign-in-alt me-2"></i>
            <span>Login</span>
        </a>
        <a href="register.php" class="btn signup-btn ms-2">
            <i class="fas fa-user-plus me-2"></i>
            <span>Sign Up</span>
        </a>
    <?php endif; ?>
</div>
        </div>
    </div>
</nav>

<style>
/* Import Google Fonts */
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

/* Navbar Base Styles */
/* Update navbar base styles */
.navbar {
    background: #fff !important; /* Override any other background */
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 15px 0;
}

/* Update text colors */
.navbar-brand span,
.nav-link,
.dropdown-toggle {
    color: #333 !important;
}

/* Update dropdown styles */
.dropdown-menu {
    border: none;
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
}

.navbar.scrolled {
    padding: 10px 0;
    box-shadow: 0 4px 30px rgba(0, 0, 0, 0.15);
}

/* Brand Styles */
.navbar-brand {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;

    color: #333 !important;
}

.navbar-brand img {
    border-radius: 8px;
}

.navbar-brand span {
    color: white;
    font-weight: 600;
    font-size: 1.25rem;
}

/* Custom Hamburger Menu */
.navbar-toggler {
    border: none;
    padding: 8px;
   
    background: #000; /* Changed from white to black */
    border-radius: 8px;
    transition: all 0.3s ease;
}

.navbar-toggler:focus {
    box-shadow: none;
}
/* Updated Hamburger Menu CSS */
.navbar-toggler {
    border: none;
    padding: 8px;
    background: transparent;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    position: relative;

   
}

.navbar-toggler:focus {
    box-shadow: none;
    outline: none;
    
}

.hamburger-menu {
    width: 22px;
    height: 17px;
    position: relative;
    display: flex;
    flex-direction: column;
    background: white; /* Changed from white to black */
    justify-content: space-between;

   s */
   
}

.hamburger-menu span {
    display: block;
    height: 2px;
    width: 100%;
    background: #000;
    border-radius: 1px;
    transition: all 0.3s ease;
    transform-origin: left;

}

/* Clean Hamburger Animation */
.navbar-toggler[aria-expanded="true"] .hamburger-menu span:first-child {
  
   
    background: #000; /* Changed from white to black */
    transform-origin: 10% 10%;
    transform: rotate(45deg);
    width: 22px;
}

.navbar-toggler[aria-expanded="true"] .hamburger-menu span:nth-child(2) {
    opacity: 0;
    transform: translateX(-10px);
}

.navbar-toggler[aria-expanded="true"] .hamburger-menu span:last-child {
    transform: rotate(-45deg);
  
    transform-origin: 10% 90%;
    transform: rotate(-45deg);
    width: 22px;
}

/* Mobile specific styles */
@media (max-width: 991.98px) {
    .navbar-collapse {
        position: fixed;
        top: 76px; /* Adjust based on your navbar height */
        left: 0;
        right: 0;
        bottom: 0;
        background: white;
        padding: 1rem;
        overflow-y: auto;
        transform: translateX(-100%);
        transition: transform 0.3s ease;
        will-change: transform;
        z-index: 1030;
    }

    .navbar-collapse.show {
        transform: translateX(0);
    }

    body.menu-open {
        overflow: hidden;
    }
}
/* Navigation Links */
.nav-link {
    color: #333 !important;
    padding: 8px 16px !important;
    opacity: 0.9;
    transition: all 0.3s ease;
    position: relative;
}

.nav-link:hover {
    opacity: 1;
    transform: translateY(-1px);
}

.nav-link span {
    position: relative;
}

.nav-link span::after {
    content: '';
    position: absolute;
    width: 0;
    height: 2px;
    background: white;
    bottom: -4px;
    left: 0;
    transition: width 0.3s ease;
}

.nav-link:hover span::after,
.nav-link.active span::after {
    width: 100%;
}

/* Dropdown Styles */
.dropdown-menu,
.main-dropdown {
    background: white;
    border: none;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    padding: 8px;
    margin-top: 8px;
    min-width: 250px;
}

.dropdown-item {
    padding: 10px 16px;
    border-radius: 8px;
    transition: all 0.3s ease;
    color: #333;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.dropdown-item:hover {
    background: #f8f9fa;
    color: #2980b9;
    transform: translateX(4px);
}

.category-link {
    font-weight: 500;
}

.submenu-arrow {
    font-size: 12px;
    transition: transform 0.3s ease;
}

/* Sub Dropdown */
.sub-dropdown {
    position: absolute;
    top: 0;
    left: 100%;
    margin-left: 8px;
    opacity: 0;
    visibility: hidden;
    transform: translateX(-10px);
    transition: all 0.3s ease;
}

.category-item:hover .sub-dropdown {
    opacity: 1;
    visibility: visible;
    transform: translateX(0);
}

.sub-item {
    padding: 8px 16px;
    font-weight: 400;
    color: #555;
}

.sub-item:hover {
    background: #f1f3f4;
    color: #2980b9;
}

/* Authentication Buttons */
.nav-buttons {
    display: flex;
    gap: 10px;
}

.login-btn,
.signup-btn {
    padding: 8px 20px;
    border-radius: 6px;
    font-weight: 500;
    transition: all 0.3s ease;
    text-decoration: none;
    display: flex;
    align-items: center;
    border: 2px solid white;
}

.login-btn {
    background: transparent;
    color: white;
}

.login-btn:hover {
    background: white;
    color: #2980b9;
}

.signup-btn {
    background: white;
    color: #2980b9;
}

.signup-btn:hover {
    background: transparent;
    color: white;
}

/* Profile Button */
.nav-profile-btn {
    background: rgba(255, 255, 255, 0.1);
    color: white;
    border: 2px solid rgba(255, 255, 255, 0.3);
    padding: 8px 16px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    transition: all 0.3s ease;
}

.nav-profile-btn:hover {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border-color: rgba(255, 255, 255, 0.5);
}

/* Profile Dropdown */
.profile-menu {
    min-width: 280px;
    padding: 0;
    border: none;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    margin-top: 10px;
}

.profile-menu .dropdown-header {
    background: #f8f9fa;
    padding: 16px;
    border-radius: 12px 12px 0 0;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-icon {
    font-size: 2.5rem;
    color: #2980b9;
}

.user-details {
    display: flex;
    flex-direction: column;
}

.user-name {
    font-weight: 600;
    color: #2c3e50;
}

.user-role {
    font-size: 0.8rem;
    color: #7f8c8d;
}

.profile-menu .dropdown-item {
    padding: 12px 16px;
    transition: all 0.2s ease;
}

.profile-menu .dropdown-item:hover {
    background: #f8f9fa;
    transform: translateX(4px);
}

.profile-menu .text-danger {
    color: #dc3545;
}

.profile-menu .text-danger:hover {
    color: #dc3545 !important;
    background: #fff1f1;
}

/* Mobile Responsive Styles */
@media (max-width: 991.98px) {
    .navbar-collapse {
        background: white;
        margin-top: 15px;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.1);
    }

    .nav-link {
        color: #333 !important;
        padding: 12px 0 !important;
        border-bottom: 1px solid #eee;
    }

    .nav-link span::after {
        background: #2980b9;
    }

    /* Mobile Dropdown */
    .dropdown-menu,
    .main-dropdown {
        position: static;
        background: #f8f9fa;
        box-shadow: none;
        border-radius: 8px;
        margin: 10px 0;
        width: 100%;
        display: none;
    }

    .dropdown-menu.show,
    .main-dropdown.show {
        display: block;
    }

    .category-item {
        position: relative;
    }

    .category-link {
        padding: 12px;
        justify-content: space-between;
        background: transparent;
    }

    .category-link:hover {
        background: #e9ecef;
        transform: none;
    }

    /* Mobile Sub Dropdown */
    .sub-dropdown {
        position: static;
        opacity: 1;
        visibility: visible;
        transform: none;
        background: #e9ecef;
        margin: 8px 0 0 20px;
        border-radius: 8px;
        display: none;
    }

    .sub-dropdown.show {
        display: block;
    }

    .sub-item {
        padding: 8px 12px;
        font-size: 0.9rem;
    }

    /* Mobile Navigation Buttons */
    .nav-buttons {
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #eee;
        flex-direction: column;
        width: 100%;
    }

    .login-btn,
    .signup-btn {
        width: 100%;
        text-align: center;
        justify-content: center;
        margin: 0;
        margin-bottom: 10px;
    }

    .login-btn {
        color: #2980b9;
        border-color: #2980b9;
    }

    .signup-btn {
        background: #2980b9;
        color: white;
        border-color: #2980b9;
    }

    .nav-profile-btn {
        width: 100%;
        justify-content: center;
        background: #2980b9;
        color: white !important;
        border-color: #2980b9;
    }

    .profile-menu {
        width: 100%;
        margin-top: 5px;
        box-shadow: none;
        border: 1px solid #eee;
    }
}

/* Scroll effect for navbar */
@media (min-width: 992px) {
    body {
        
    }
}
/* Mobile-specific styles */
@media (max-width: 991.98px) {
    .navbar-collapse {
        position: fixed;
        top: 70px;
        left: 0;
        right: 0;
        bottom: 0;
        background: #fff;
        padding: 20px;
        overflow-y: auto;
        transition: 0.3s ease;
        transform: translateX(-100%);
    }

    .navbar-collapse.show {
        transform: translateX(0);
    }

    .nav-buttons {
        margin-top: 20px;
        border-top: 1px solid #eee;
       
    }

    .profile-dropdown .dropdown-menu {
        position: static !important;
        width: 100%;
        margin-top: 10px;
        box-shadow: none;
        border: 1px solid #eee;
    }

    .login-btn,
    .signup-btn {
        width: 100%;
        margin: 5px 0;
        justify-content: center;
    }

    .profile-btn {
        width: 100%;
        justify-content: center;
        margin-bottom: 10px;
    }
}

/* Profile Button Styles */
.profile-btn {
    background: transparent;
    border: 1px solid #ddd;
    padding: 8px 16px;
    border-radius: 4px;
    color: #333;
    transition: all 0.3s ease;
}

.profile-btn:hover {
    background: #f8f9fa;
    border-color: #333;
}

/* Dropdown Menu Styles */
.profile-dropdown .dropdown-menu {
    min-width: 200px;
    padding: 8px;
}

.profile-dropdown .dropdown-item {
    padding: 8px 16px;
    border-radius: 4px;
}

.profile-dropdown .dropdown-item:hover {
    background: #f8f9fa;
}

/* Navbar Styles */
.navbar {
    padding: 15px 0;
    background-color: var(--white);
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    z-index: 1000;
}

.navbar.scrolled {
    padding: 10px 0;
    background-color: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
}

.navbar-brand {
    font-weight: 700;
    font-size: 1.5rem;
    color: var(--primary-color);
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
}

.navbar-brand:hover {
    color: var(--primary-color);
}

.navbar-brand img {
    transition: transform 0.3s ease;
}

.navbar-brand:hover img {
    transform: scale(1.05);
}

.nav-link {
    font-weight: 500;
    padding: 8px 16px !important;
    color: var(--text-color) !important;
    position: relative;
    text-decoration: none;
}

.nav-link span {
    position: relative;
    padding-bottom: 5px;
}

.nav-link span::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 0;
    height: 2px;
    background-color: var(--secondary-color);
    transition: width 0.3s ease;
}

.nav-link:hover span::after,
.nav-link.active span::after {
    width: 100%;
}

.dropdown-menu {
    border: none;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    border-radius: 8px;
    padding: 10px;
}

.dropdown-item {
    padding: 8px 20px;
    font-weight: 500;
    color: var(--text-color);
    transition: all 0.3s ease;
    border-radius: 5px;
    text-decoration: none;
}

.dropdown-item:hover {
    background-color: var(--light-gray);
    color: var(--secondary-color);
    transform: translateX(5px);
}

.nav-icon {
    color: var(--text-color);
    font-size: 1.2rem;
    position: relative;
    transition: all 0.3s ease;
    text-decoration: none;
}

.nav-icon:hover {
    color: var(--secondary-color);
    transform: scale(1.1);
}

.cart-badge {
    position: absolute;
    top: -8px;
    right: -8px;
    background-color: var(--accent-color);
    color: white;
    font-size: 0.7rem;
    padding: 2px 6px;
    border-radius: 50%;
}

.nav-btn {
    background-color: var(--secondary-color);
    color: white;
    padding: 8px 20px;
    border-radius: 25px;
    font-weight: 500;
    transition: all 0.3s ease;
    border: 2px solid var(--secondary-color);
    text-decoration: none;
}

.nav-btn:hover {
    background-color: transparent;
    color: var(--secondary-color);
    transform: translateY(-2px);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Current user and time information
    const currentUser = 'Kalpesh-Sananse';
    const currentTime = '2025-05-30 15:09:33';

    // Cache DOM elements
    const navbar = document.querySelector('.navbar');
    const navbarToggler = document.querySelector('.navbar-toggler');
    const navbarCollapse = document.querySelector('.navbar-collapse');
    const dropdowns = document.querySelectorAll('.dropdown');
    const profileDropdown = document.querySelector('.profile-dropdown');

    // Navbar scroll effect
    let lastScrollPosition = 0;
    function handleScroll() {
        lastScrollPosition = window.scrollY;
        if (lastScrollPosition > 50) {
            navbar.classList.add('scrolled');
            navbar.style.padding = '10px 0';
            navbar.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
        } else {
            navbar.classList.remove('scrolled');
            navbar.style.padding = '15px 0';
            navbar.style.boxShadow = 'none';
        }
    }

    // Mobile menu functionality
    function initializeMobileMenu() {
        let isMenuOpen = false;

        navbarToggler.addEventListener('click', function() {
            isMenuOpen = !isMenuOpen;
            this.setAttribute('aria-expanded', isMenuOpen);
            navbarCollapse.classList.toggle('show');
            document.body.style.overflow = isMenuOpen ? 'hidden' : '';
            navbarCollapse.style.transform = isMenuOpen ? 'translateX(0)' : 'translateX(-100%)';
        });
    }

    // Dropdown handling
    function initializeDropdowns() {
        dropdowns.forEach(dropdown => {
            const toggle = dropdown.querySelector('.dropdown-toggle');
            const menu = dropdown.querySelector('.dropdown-menu');

            // Desktop behavior
            if (window.innerWidth >= 992) {
                dropdown.addEventListener('mouseenter', () => {
                    menu.style.display = 'block';
                    setTimeout(() => {
                        menu.style.opacity = '1';
                        menu.style.transform = 'translateY(0)';
                    }, 10);
                });

                dropdown.addEventListener('mouseleave', () => {
                    menu.style.opacity = '0';
                    menu.style.transform = 'translateY(10px)';
                    setTimeout(() => {
                        menu.style.display = 'none';
                    }, 300);
                });
            } 
            // Mobile behavior
            else {
                toggle.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();

                    // Close other dropdowns
                    dropdowns.forEach(otherDropdown => {
                        if (otherDropdown !== dropdown) {
                            const otherMenu = otherDropdown.querySelector('.dropdown-menu');
                            const otherToggle = otherDropdown.querySelector('.dropdown-toggle');
                            if (otherMenu.classList.contains('show')) {
                                otherMenu.classList.remove('show');
                                otherToggle.setAttribute('aria-expanded', 'false');
                            }
                        }
                    });

                    // Toggle current dropdown
                    menu.classList.toggle('show');
                    const isExpanded = menu.classList.contains('show');
                    toggle.setAttribute('aria-expanded', isExpanded);
                });

                // Handle dropdown items click
                const items = dropdown.querySelectorAll('.dropdown-item');
                items.forEach(item => {
                    item.addEventListener('click', () => {
                        menu.classList.remove('show');
                        toggle.setAttribute('aria-expanded', 'false');
                        if (window.innerWidth < 992) {
                            navbarCollapse.classList.remove('show');
                            navbarToggler.setAttribute('aria-expanded', 'false');
                            document.body.style.overflow = '';
                            navbarCollapse.style.transform = 'translateX(-100%)';
                        }
                    });
                });
            }
        });
    }

    // Profile dropdown handling
    function initializeProfileDropdown() {
        if (profileDropdown) {
            const profileBtn = profileDropdown.querySelector('.profile-btn');
            const profileMenu = profileDropdown.querySelector('.dropdown-menu');

            profileBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();

                // Close other dropdowns
                dropdowns.forEach(dropdown => {
                    const menu = dropdown.querySelector('.dropdown-menu');
                    const toggle = dropdown.querySelector('.dropdown-toggle');
                    if (menu.classList.contains('show')) {
                        menu.classList.remove('show');
                        toggle.setAttribute('aria-expanded', 'false');
                    }
                });

                // Toggle profile dropdown
                profileMenu.classList.toggle('show');
            });
        }
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', (e) => {
        if (!navbar.contains(e.target)) {
            // Close all dropdowns
            dropdowns.forEach(dropdown => {
                const menu = dropdown.querySelector('.dropdown-menu');
                const toggle = dropdown.querySelector('.dropdown-toggle');
                if (menu.classList.contains('show')) {
                    menu.classList.remove('show');
                    toggle.setAttribute('aria-expanded', 'false');
                }
            });

            // Close profile dropdown
            if (profileDropdown) {
                const profileMenu = profileDropdown.querySelector('.dropdown-menu');
                if (profileMenu.classList.contains('show')) {
                    profileMenu.classList.remove('show');
                }
            }

            // Close mobile menu
            if (window.innerWidth < 992) {
                navbarCollapse.classList.remove('show');
                navbarToggler.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
                navbarCollapse.style.transform = 'translateX(-100%)';
            }
        }
    });

    // Handle window resize
    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            if (window.innerWidth >= 992) {
                // Reset mobile states
                navbarCollapse.classList.remove('show');
                navbarToggler.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
                navbarCollapse.style.transform = '';
            }
            initializeDropdowns();
        }, 250);
    });

    // Initialize everything
    window.addEventListener('scroll', handleScroll, { passive: true });
    initializeMobileMenu();
    initializeDropdowns();
    initializeProfileDropdown();
});
    </script>


<!-- Font Awesome Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<!-- Bootstrap CSS (if not already included) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Bootstrap JS (if not already included) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>