<?php
// Only start session if one isn't already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);
$current_time = gmdate('Y-m-d H:i:s');
$user_name = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Admin';
?>

<!-- Professional Navbar -->
<nav class="admin-navbar navbar navbar-expand-lg">
    <div class="container-fluid px-4">
        <!-- Brand Section -->
        <div class="navbar-brand-section">
            <a class="navbar-brand" href="dashboardadmin.php">
                <i class="fas fa-building brand-icon"></i>
                <span class="brand-text">Varaha City</span>
            </a>
        </div>

        <!-- Center Section - DateTime & User -->
        <div class="navbar-center d-none d-lg-flex">
            <div class="datetime-display">
                <i class="fas fa-clock"></i>
                <span id="currentDateTime"></span>
            </div>
            <div class="user-display">
                <i class="fas fa-user"></i>
                <span><?php echo $user_name; ?></span>
            </div>
        </div>

        <!-- Toggler -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Navigation Items -->
        <div class="collapse navbar-collapse" id="adminNavbar">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                    <a class="nav-link" href="dashboardadmin.php">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item <?php echo $current_page == 'add_categories.php' ? 'active' : ''; ?>">
                    <a class="nav-link" href="add_categories.php">
                        <i class="fas fa-plus-circle"></i>
                        <span>Add Categories</span>
                    </a>
                </li>
                <li class="nav-item <?php echo $current_page == 'view_categorie.php' ? 'active' : ''; ?>">
                    <a class="nav-link" href="view_categorie.php">
                        <i class="fas fa-th-large"></i>
                        <span>View Categories</span>
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle"></i>
                        <span>Account</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <div class="dropdown-header">
                            <div class="user-info">
                                <i class="fas fa-user-circle user-icon"></i>
                                <div class="user-details">
                                    <span class="user-name"><?php echo $user_name; ?></span>
                                    <span class="user-role">Administrator</span>
                                </div>
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="profile.php">
                            <i class="fas fa-user-cog"></i>
                            <span>Profile Settings</span>
                        </a>
                        <a class="dropdown-item" href="password.php">
                            <i class="fas fa-key"></i>
                            <span>Change Password</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item logout-item" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </li>
            </ul>

            <!-- Mobile DateTime & User -->
            <div class="mobile-info d-lg-none">
                <div class="datetime-display">
                    <i class="fas fa-clock"></i>
                    <span id="currentDateTimeMobile"></span>
                </div>
                <div class="user-display">
                    <i class="fas fa-user"></i>
                    <span><?php echo $user_name; ?></span>
                </div>
            </div>
        </div>
    </div>
</nav>

<style>
/* Professional Navbar Styles */
.admin-navbar {
    background: linear-gradient(135deg, #2980b9, #6dd5fa);
    padding: 0.8rem 0;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
}

/* Brand Styling */
.navbar-brand {
    display: flex;
    align-items: center;
    color: #ffffff !important;
    font-size: 1.4rem;
    font-weight: 600;
    padding: 0;
}

.brand-icon {
    font-size: 1.8rem;
    margin-right: 12px;
    color: #ffffff;
}

.brand-text {
    letter-spacing: 0.5px;
}

/* DateTime & User Display */
.navbar-center {
    display: flex;
    align-items: center;
    gap: 24px;
    margin: 0 24px;
    color: rgba(255, 255, 255, 0.9);
}

.datetime-display, .user-display {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.9rem;
    padding: 6px 12px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 6px;
}

.datetime-display i, .user-display i {
    color: #ffffff;
}

/* Navigation Items */
.navbar-nav .nav-item {
    margin: 0 2px;
}

.navbar-nav .nav-link {
    color: rgba(255, 255, 255, 0.9) !important;
    padding: 8px 16px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.navbar-nav .nav-link i {
    font-size: 1.1rem;
    color: #ffffff;
}

.navbar-nav .nav-link:hover,
.navbar-nav .active .nav-link {
    background: rgba(255, 255, 255, 0.15);
    transform: translateY(-1px);
}

/* Dropdown Styling */
.dropdown-menu {
    background: #ffffff;
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    padding: 8px;
    min-width: 260px;
    margin-top: 12px;
}

.dropdown-header {
    padding: 12px 16px;
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 8px;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-icon {
    font-size: 2rem;
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

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 16px;
    border-radius: 6px;
    transition: all 0.2s ease;
}

.dropdown-item i {
    font-size: 1.1rem;
    color: #2980b9;
}

.dropdown-item:hover {
    background: #f8f9fa;
    transform: translateX(3px);
}

.logout-item {
    color: #e74c3c;
}

.logout-item i {
    color: #e74c3c;
}

/* Mobile Styles */
.navbar-toggler {
    color: #ffffff;
    border: none;
    padding: 8px;
}

.navbar-toggler:focus {
    box-shadow: none;
}

.mobile-info {
    margin-top: 16px;
    padding: 16px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    color: white;
}

@media (max-width: 991px) {
    .navbar-collapse {
        background: linear-gradient(135deg, #2980b9, #6dd5fa);
        padding: 16px;
        border-radius: 12px;
        margin-top: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    }

    .navbar-nav .nav-link {
        padding: 12px 16px;
    }

    .dropdown-menu {
        background: rgba(255, 255, 255, 0.95);
        margin-top: 8px;
    }
}
</style>

<script>
// Update DateTime in UTC format
function updateDateTime() {
    const now = new Date();
    const year = now.getUTCFullYear();
    const month = String(now.getUTCMonth() + 1).padStart(2, '0');
    const day = String(now.getUTCDate()).padStart(2, '0');
    const hours = String(now.getUTCHours()).padStart(2, '0');
    const minutes = String(now.getUTCMinutes()).padStart(2, '0');
    const seconds = String(now.getUTCSeconds()).padStart(2, '0');
    
    const formattedDateTime = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
    
    document.getElementById('currentDateTime').textContent = formattedDateTime;
    document.getElementById('currentDateTimeMobile').textContent = formattedDateTime;
}

// Update time every second
updateDateTime();
setInterval(updateDateTime, 1000);

// Add smooth collapse animation
document.querySelector('.navbar-toggler').addEventListener('click', function() {
    this.classList.toggle('active');
});
</script>