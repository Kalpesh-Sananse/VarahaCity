<?php
session_start();
include('../includes/db_connection.php');

if (!isset($_SESSION['employee'])) {
    header("Location: employee_login.php");
    exit();
}

// Get users count
$sql = "SELECT COUNT(*) as total FROM users";
$result = $conn->query($sql);
$users_count = $result->fetch_assoc()['total'];

// Get recent users with limit 5
$sql = "SELECT * FROM users ORDER BY created_at DESC LIMIT 5";
$recent_users = $conn->query($sql);

// Format current date and time
date_default_timezone_set('UTC');
$formatted_datetime = date('Y-m-d H:i:s');
$current_user = $_SESSION['employee']['name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | <?php echo htmlspecialchars($current_user); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #34495e;
            --accent: #3498db;
            --light: #ecf0f1;
            --dark: #2c3e50;
            --success: #2ecc71;
            --warning: #f1c40f;
            --danger: #e74c3c;
            --gray: #95a5a6;
            --gradient: linear-gradient(135deg, var(--primary), var(--secondary));
        }

        body {
            background-color: #f5f6fa;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        /* Top Bar Styling */
        .dashboard-header {
            background: var(--gradient);
            padding: 1.5rem 0;
            color: white;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .info-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 1.25rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            height: 100%;
        }

        .datetime-display {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            letter-spacing: 0.5px;
        }

        .info-label {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-bottom: 0.25rem;
        }

        .user-profile {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-right: 1rem;
        }

        /* Stats Cards */
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            height: 100%;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .stats-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .stats-value {
            font-size: 2rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .stats-label {
            color: var(--gray);
            font-size: 0.9rem;
        }

        /* Activity Section */
        .activity-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-top: 2rem;
        }

        .section-title {
            color: var(--dark);
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .activity-item {
            padding: 1rem;
            border-radius: 10px;
            border: 1px solid #eee;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .activity-item:hover {
            background: var(--light);
            border-color: var(--accent);
        }

        /* Buttons */
        .btn-custom {
            padding: 0.5rem 1.25rem;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-logout {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-logout:hover {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            transform: translateY(-2px);
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

        .animate-fadeInUp {
            animation: fadeInUp 0.5s ease forwards;
        }
    </style>
</head>
<body>
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="info-card">
                        <div class="info-label">Current Date and Time (UTC - YYYY-MM-DD HH:MM:SS formatted)</div>
                        <div class="datetime-display"><?php echo $formatted_datetime; ?></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-card">
                        <div class="user-profile">
                            <div class="d-flex align-items-center">
                                <div class="user-avatar">
                                    <?php echo strtoupper(substr($current_user, 0, 1)); ?>
                                </div>
                                <div>
                                    <div class="info-label">Current User's Login</div>
                                    <div class="datetime-display"><?php echo htmlspecialchars($current_user); ?></div>
                                </div>
                            </div>
                            <a href="logout.php" class="btn btn-custom btn-logout">
                                <i class="bi bi-box-arrow-right"></i>
                                Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Add this right after the dashboard header section and before the main content -->
<div class="container mb-4">
    <div class="management-bar animate-fadeInUp">
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Dashboard Overview</h4>
            <a href="users_page.php" class="btn btn-custom btn-manage">
                <i class="bi bi-people-fill me-2"></i>
                Manage Users
            </a>
        </div>
    </div>
</div>

<!-- Add these styles to your existing stylesheet -->
<style>
    /* Add these new styles */
    .management-bar {
        background: white;
        padding: 1rem 1.5rem;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }

    .btn-manage {
        background: var(--gradient);
        color: white;
        padding: 0.75rem 1.5rem;
        border: none;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-manage:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(44, 62, 80, 0.2);
        color: white;
    }
</style>

<!-- Create a new file called manage_users.php -->

    <!-- Main Content -->
    <div class="container">
        <!-- Statistics -->
        <div class="row g-4 mb-4">
            <!-- Total Users -->
            <div class="col-md-4">
                <div class="stats-card animate-fadeInUp" style="animation-delay: 0.1s">
                    <div class="stats-icon" style="background: #e3f2fd; color: var(--accent);">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div class="stats-value"><?php echo number_format($users_count); ?></div>
                    <div class="stats-label">Total Users</div>
                </div>
            </div>

            <!-- Department -->
            <div class="col-md-4">
                <div class="stats-card animate-fadeInUp" style="animation-delay: 0.2s">
                    <div class="stats-icon" style="background: #e8f5e9; color: var(--success);">
                        <i class="bi bi-building"></i>
                    </div>
                    <div class="stats-value"><?php echo htmlspecialchars($_SESSION['employee']['department']); ?></div>
                    <div class="stats-label">Department</div>
                </div>
            </div>

            <!-- Role -->
            <div class="col-md-4">
                <div class="stats-card animate-fadeInUp" style="animation-delay: 0.3s">
                    <div class="stats-icon" style="background: #fff3e0; color: var(--warning);">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <div class="stats-value"><?php echo htmlspecialchars($_SESSION['employee']['role']); ?></div>
                    <div class="stats-label">Role</div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="activity-section animate-fadeInUp" style="animation-delay: 0.4s">
            <h3 class="section-title">
                <i class="bi bi-clock-history text-primary"></i>
                Recent User Activity
            </h3>
            
            <?php if($recent_users->num_rows > 0): ?>
                <?php while($user = $recent_users->fetch_assoc()): ?>
                    <div class="activity-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="user-avatar me-3" style="background: var(--gradient)">
                                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                </div>
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($user['name']); ?></h6>
                                    <div class="text-muted small">
                                        <i class="bi bi-envelope me-1"></i>
                                        <?php echo htmlspecialchars($user['email']); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end text-muted small">
                                <i class="bi bi-calendar2 me-1"></i>
                                <?php echo date('Y-m-d H:i:s', strtotime($user['created_at'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    No recent activities found.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>