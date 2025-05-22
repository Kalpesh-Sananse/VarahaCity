<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include('../includes/db_connection.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Varaha City</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom Styles -->
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7fc;
            color: #444;
        }
        
        /* Enhanced Navbar */
        .navbar {
            background: linear-gradient(135deg, #2980b9, #6dd5fa);
            padding: 15px 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-weight: 700;
            color: #fff !important;
            font-size: 1.5rem;
            letter-spacing: 0.5px;
        }
        
        .navbar-brand img {
            height: 40px;
            margin-right: 10px;
        }
        
        .navbar-light .navbar-nav .nav-link {
            color: rgba(255,255,255,0.85);
            font-weight: 500;
            margin: 0 10px;
            padding: 8px 15px;
            border-radius: 20px;
            transition: all 0.3s ease;
        }
        
        .navbar-light .navbar-nav .nav-link:hover,
        .navbar-light .navbar-nav .active .nav-link {
            color: #fff;
            background-color: rgba(255,255,255,0.15);
            transform: translateY(-2px);
        }

        .navbar-light .navbar-toggler {
            border-color: rgba(255,255,255,0.5);
            background-color: rgba(255,255,255,0.1);
            cursor: pointer;
        }

        .navbar-light .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='30' height='30' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(255, 255, 255, 0.8)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }
        
        /* Mobile navbar styling */
        @media (max-width: 991px) {
            .navbar-collapse {
                background: linear-gradient(135deg, #2980b9, #6dd5fa);
                padding: 20px;
                border-radius: 0 0 10px 10px;
                box-shadow: 0 10px 20px rgba(0,0,0,0.1);
                margin-top: 10px;
            }
            
            .navbar-light .navbar-nav .nav-link {
                margin: 5px 0;
                text-align: center;
            }
            
            .navbar-nav {
                margin-top: 15px !important;
            }
        }
        
        /* Dashboard Welcome Section */
        .welcome-section {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 15px;
            padding: 40px;
            margin-bottom: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            position: relative;
            overflow: hidden;
        }
        
        .welcome-section::before {
            content: "";
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: rgba(255,255,255,0.1);
        }
        
        .welcome-section h1 {
            font-weight: 700;
            margin-bottom: 15px;
            color: #333;
            font-size: 2.5rem;
        }
        
        .welcome-section p {
            font-size: 1.1rem;
            color: #555;
            max-width: 600px;
        }
        
        .welcome-stats {
            display: flex;
            flex-wrap: wrap;
            margin-top: 30px;
            gap: 20px;
        }
        
        .stat-item {
            background: #fff;
            border-radius: 10px;
            padding: 15px 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            flex: 1;
            min-width: 150px;
            max-width: 200px;
            transition: all 0.3s ease;
        }
        
        .stat-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        
        .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            color: #3498db;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #777;
            font-weight: 500;
        }
        
        /* Enhanced Cards */
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            transition: all 0.4s ease;
            height: 100%;
        }
        
        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }
        
        .card-img-top {
            height: 190px;
            object-fit: cover;
            transition: all 0.5s ease;
        }
        
        .card:hover .card-img-top {
            transform: scale(1.05);
        }
        
        .card-body {
            padding: 25px;
        }
        
        .card-title {
            font-weight: 600;
            font-size: 1.3rem;
            margin-bottom: 15px;
            color: #333;
        }
        
        .card-text {
            color: #666;
            margin-bottom: 20px;
        }
        
        .card-icon {
            margin-bottom: 15px;
            font-size: 2.5rem;
            color: #3498db;
            transition: all 0.3s ease;
        }
        
        .btn-dashboard {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.4);
        }
        
        .btn-dashboard:hover {
            background: linear-gradient(135deg, #2980b9, #1c6ca1);
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(52, 152, 219, 0.5);
            color: white;
        }
        
        /* Card Image Overlay */
        .card-img-container {
            position: relative;
            overflow: hidden;
        }
        
        .card-img-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom, rgba(0,0,0,0.1), rgba(0,0,0,0.5));
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: all 0.3s ease;
        }
        
        .card:hover .card-img-overlay {
            opacity: 1;
        }
        
        .quick-action {
            background-color: rgba(255,255,255,0.85);
            color: #333;
            border-radius: 50px;
            padding: 8px 20px;
            font-weight: 500;
            transform: translateY(20px);
            transition: all 0.4s ease;
            text-decoration: none;
        }

        .quick-action:hover {
            background-color: #fff;
            color: #2980b9;
            text-decoration: none;
        }
        
        .card:hover .quick-action {
            transform: translateY(0);
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
        
        .animated {
            animation: fadeInUp 0.5s ease forwards;
        }
        
        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }
        .delay-4 { animation-delay: 0.4s; }
        
        /* Footer */
        .dashboard-footer {
            background-color: #fff;
            border-radius: 15px;
            padding: 20px;
            margin-top: 40px;
            text-align: center;
            font-size: 0.9rem;
            color: #777;
            box-shadow: 0 -5px 20px rgba(0,0,0,0.05);
        }

        /* Responsive fixes */
        @media (max-width: 991px) {
            .welcome-section {
                padding: 30px;
            }
            .welcome-section h1 {
                font-size: 2rem;
            }
        }

        @media (max-width: 767px) {
            .welcome-stats {
                flex-direction: column;
                align-items: center;
            }
            .stat-item {
                width: 100%;
                max-width: 100%;
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>

<!-- Enhanced Navbar -->
<nav class="navbar navbar-expand-lg navbar-light">
  <div class="container">
    <a class="navbar-brand" href="#">
      <i class="fas fa-building mr-2"></i> Varaha City Admin
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item active">
          <a class="nav-link" href="dashboard.php">
            <i class="fas fa-home mr-1"></i> Home
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="add_categories.php">
            <i class="fas fa-plus-circle mr-1"></i> Add Categories
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="view_categorie.php">
            <i class="fas fa-th-large mr-1"></i> View Categories
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="view_properties.php">
            <i class="fas fa-users mr-1"></i> Users
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="slidermanagement.php">
            <i class="fas fa-sign-out-alt mr-1"></i> Logout
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- Dashboard Content -->
<div class="container mt-5">
    <!-- Welcome Section -->
    <div class="welcome-section animated">
        <h1><i class="fas fa-chart-line mr-3"></i>Welcome, Admin!</h1>
        <p class="lead">Manage your real estate platform efficiently. Track listings, categories, and user activity all from this centralized dashboard.</p>
        
        <!-- Stats Row -->
        <div class="welcome-stats">
            <div class="stat-item">
                <div class="stat-number"><?php 
                    // Count categories
                    $query = "SELECT COUNT(*) as total FROM categories";
                    $result = $conn->query($query);
                    echo ($result && $row = $result->fetch_assoc()) ? $row['total'] : 0;
                ?></div>
                <div class="stat-label">Categories</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php 
                    // Count users
                    $query = "SELECT COUNT(*) as total FROM users";
                    $result = $conn->query($query);
                    echo ($result && $row = $result->fetch_assoc()) ? $row['total'] : 0;
                ?></div>
                <div class="stat-label">Users</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php 
                    // Count properties (assuming there's a properties table)
                    $query = "SELECT COUNT(*) as total FROM properties";
                    $result = $conn->query($query);
                    echo ($result && $row = $result->fetch_assoc()) ? $row['total'] : 0;
                    // If properties table doesn't exist, comment out the above and uncomment:
                    // echo "0";
                ?></div>
                <div class="stat-label">Properties</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php 
                    // Count pending listings or another metric
                    $query = "SELECT COUNT(*) as total FROM properties WHERE status='pending'";
                    $result = $conn->query($query);
                    echo ($result && $row = $result->fetch_assoc()) ? $row['total'] : 0;
                    // If not applicable, use a sample value
                    // echo "5";
                ?></div>
                <div class="stat-label">Pending</div>
            </div>
        </div>
    </div>

    <!-- Cards Row -->
    <div class="row">
        <!-- Add Category Card -->
        <div class="col-lg-3 col-md-6 mb-4 animated delay-1">
            <div class="card h-100">
                <div class="card-img-container">
                    <img class="card-img-top" src="../images/images.jpeg" alt="Add Category">
                    <div class="card-img-overlay">
                        <a href="add_categories.php" class="quick-action">Add Now</a>
                    </div>
                </div>
                <div class="card-body text-center">
                    <div class="card-icon">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <h5 class="card-title">Add Categories</h5>
                    <p class="card-text">Create new property categories to organize listings effectively.</p>
                    <a href="add_categories.php" class="btn btn-dashboard"><i class="fas fa-plus mr-2"></i>Add Category</a>
                </div>
            </div>
        </div>

        <!-- View Categories Card -->
        <div class="col-lg-3 col-md-6 mb-4 animated delay-2">
            <div class="card h-100">
                <div class="card-img-container">
                    <img class="card-img-top" src="../images/images.jpeg" alt="View Categories">
                    <div class="card-img-overlay">
                        <a href="view_categories.php" class="quick-action">View All</a>
                    </div>
                </div>
                <div class="card-body text-center">
                    <div class="card-icon">
                        <i class="fas fa-th-list"></i>
                    </div>
                    <h5 class="card-title">View Categories</h5>
                    <p class="card-text">Browse and manage all your existing property categories.</p>
                    <a href="view_categories.php" class="btn btn-dashboard"><i class="fas fa-eye mr-2"></i>View Categories</a>
                </div>
            </div>
        </div>

        <!-- Manage Users Card -->
        <div class="col-lg-3 col-md-6 mb-4 animated delay-3">
            <div class="card h-100">
                <div class="card-img-container">
                    <img class="card-img-top" src="../images/images.jpeg" alt="Users Management">
                    <div class="card-img-overlay">
                        <a href="users.php" class="quick-action">Manage Now</a>
                    </div>
                </div>
                <div class="card-body text-center">
                    <div class="card-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h5 class="card-title">Manage Users</h5>
                    <p class="card-text">Add, edit, or remove user accounts and manage permissions.</p>
                    <a href="users.php" class="btn btn-dashboard"><i class="fas fa-user-cog mr-2"></i>Manage Users</a>
                </div>
            </div>
        </div>
        
        <!-- Properties Card -->
        <div class="col-lg-3 col-md-6 mb-4 animated delay-4">
            <div class="card h-100">
                <div class="card-img-container">
                    <img class="card-img-top" src="../images/images.jpeg" alt="Properties">
                    <div class="card-img-overlay">
                        <a href="properties.php" class="quick-action">Manage Properties</a>
                    </div>
                </div>
                <div class="card-body text-center">
                    <div class="card-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <h5 class="card-title">Properties</h5>
                    <p class="card-text">View and manage all property listings on your platform.</p>
                    <a href="properties.php" class="btn btn-dashboard"><i class="fas fa-list mr-2"></i>View Properties</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Activity Section -->
    <div class="recent-activity animated delay-3">
        <div class="card">
            <div class="card-body">
                <h4 class="mb-4"><i class="fas fa-history mr-2"></i>Recent Activity</h4>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Action</th>
                                <th>Description</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // If you have an activity log table, you can use it here
                            // Otherwise, this is just sample data
                            $activities = [
                                ['New Category Added', 'Residential property category was added', date('Y-m-d H:i:s', strtotime('-1 day'))],
                                ['User Registration', 'New user registered on the platform', date('Y-m-d H:i:s', strtotime('-3 days'))],
                                ['Category Updated', 'Commercial property category was updated', date('Y-m-d H:i:s', strtotime('-5 days'))]
                            ];
                            
                            foreach($activities as $activity) {
                                echo '<tr>
                                    <td>'.$activity[0].'</td>
                                    <td>'.$activity[1].'</td>
                                    <td>'.date('M d, Y', strtotime($activity[2])).'</td>
                                </tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="dashboard-footer">
        <p>© <?php echo date('Y'); ?> Real Estate Admin Dashboard. All rights reserved.</p>
    </div>
</div>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS for Dashboard -->
<script>
    // Add any custom JS here
    $(document).ready(function() {
        // Card icon animation on hover
        $('.card').hover(
            function() {
                $(this).find('.card-icon').css('transform', 'scale(1.1)');
            },
            function() {
                $(this).find('.card-icon').css('transform', 'scale(1)');
            }
        );
        
        // Responsive navbar adjustments
        function adjustNavbar() {
            if ($(window).width() < 992) {
                $('.navbar-nav').css('margin-top', '15px');
            } else {
                $('.navbar-nav').css('margin-top', '0');
            }
        }
        
        // Run on page load
        adjustNavbar();
        
        // Run on window resize
        $(window).resize(function() {
            adjustNavbar();
        });
    });
</script>

</body>
</html>