<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include('../includes/db_connection.php');

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle user creation
    if (isset($_POST['create_user'])) {
        $name = $_POST['name'];
        $username = $_POST['username'];
        $email = $_POST['email'];
        $contact_no = $_POST['contact_no'];
        $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
        
        // Check if username or email already exists
        $check_sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ss", $username, $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $_SESSION['error'] = "Username or email already exists!";
        } else {
            // Insert new user (removed role column)
            $insert_sql = "INSERT INTO users (name, username, email, contact_no, password, created_at) 
                          VALUES (?, ?, ?, ?, ?, NOW())";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("sssss", $name, $username, $email, $contact_no, $password);
            
            if ($insert_stmt->execute()) {
                $_SESSION['success'] = "User created successfully!";
            } else {
                $_SESSION['error'] = "Error creating user: " . $conn->error;
            }
        }
        
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
    
    // Handle user deletion (via AJAX)
    if (isset($_POST['delete_user'])) {
        $user_id = intval($_POST['user_id']);
        $delete_sql = "DELETE FROM users WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $user_id);
        if ($delete_stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        exit();
    }
}

// Get all users from the users table
$sql = "SELECT * FROM users ORDER BY created_at DESC";
$result = $conn->query($sql);

// Set timezone to UTC
date_default_timezone_set('UTC');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #0A142F, #1a2642);
            --secondary-gradient: linear-gradient(135deg, #2980b9, #3498db);
            --accent-color: #e74c3c;
            --success-color: #28a745;
            --text-color: #2c3e50;
            --border-radius: 12px;
        }

        body {
            background-color: #f8f9fa;
            padding-top: 40px;
            color: var(--text-color);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }

        .top-bar {
            background: linear-gradient(135deg, #2980b9, #6dd5fa);
            padding: 1rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
        }

        .info-widget {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            margin: 0.5rem 0;
            color: white;
        }

        .info-widget i {
            font-size: 1.5rem;
            margin-right: 1rem;
            opacity: 0.9;
        }

        .info-widget .label {
            font-size: 0.85rem;
            opacity: 0.9;
            margin-bottom: 0.25rem;
        }

        .info-widget .value {
            font-size: 1rem;
            font-weight: 500;
        }

        .card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, #2980b9, #6dd5fa);
            padding: 1.5rem;
            border-bottom: none;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h4 {
            color: white;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background-color: #f8f9fa;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            padding: 1rem;
            border-bottom: 2px solid #eee;
        }

        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
        }

        .action-buttons .btn {
            padding: 0.5rem;
            border-radius: 8px;
            margin: 0 0.2rem;
            transition: all 0.3s ease;
        }

        .btn-view {
            background: var(--secondary-gradient);
            border: none;
            color: white;
        }

        .btn-delete {
            background: var(--accent-color);
            border: none;
            color: white;
        }
        
        .btn-create {
            background: var(--success-color);
            border: none;
            color: white;
            padding: 0.5rem 1rem;
        }

        .modal-content {
            border-radius: var(--border-radius);
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            background: var(--primary-gradient);
            color: white;
            border-bottom: none;
            padding: 1.5rem;
        }

        .modal-title {
            font-weight: 600;
        }

        .btn-close {
            filter: brightness(0) invert(1);
        }

        @media (max-width: 768px) {
            .info-widget {
                margin: 0.5rem 0;
            }

            .table-responsive {
                border-radius: var(--border-radius);
            }

            .action-buttons {
                display: flex;
                justify-content: flex-end;
            }

            .action-buttons .btn {
                padding: 0.4rem 0.6rem;
            }
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .card {
            animation: fadeIn 0.6s ease-out;
        }
        
        /* Form styles */
        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        
        .form-control {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            border: 1px solid #dee2e6;
        }
        
        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
        }
        
        /* Password toggle */
        .password-toggle-container {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            z-index: 5;
        }
        
        .optional-badge {
            font-size: 0.75rem;
            background-color: #6c757d;
            color: white;
            padding: 0.2rem 0.4rem;
            border-radius: 4px;
            margin-left: 0.5rem;
        }
    </style>
</head>
<body>
    <!-- Main Content -->
    <div class="container">
        <!-- Display success/error messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h4>
                    <i class="bi bi-people-fill me-2"></i>
                    User Management
                </h4>
                <button class="btn btn-create" data-bs-toggle="modal" data-bs-target="#createUserModal">
                    <i class="bi bi-plus-lg me-1"></i> Create User
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Contact</th>
                                <th>Created At</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($user = $result->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $user['id']; ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-person-circle me-2"></i>
                                        <?php echo htmlspecialchars($user['name']); ?>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['contact_no']); ?></td>
                                <td><?php echo date('Y-m-d H:i:s', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-view" onclick="viewUser(<?php echo $user['id']; ?>)" 
                                                title="View User">
                                            <i class="bi bi-eye-fill"></i>
                                        </button>
                                        <button class="btn btn-delete" onclick="deleteUser(<?php echo $user['id']; ?>)"
                                                title="Delete User">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Create User Modal -->
    <div class="modal fade" id="createUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-person-plus me-2"></i>
                        Create New User
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="createUserForm" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="contact_no" class="form-label">Contact Number</label>
                            <input type="tel" class="form-control" id="contact_no" name="contact_no" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">
                                Password 
                                <span class="optional-badge">Optional</span>
                            </label>
                            <div class="password-toggle-container">
                                <input type="password" class="form-control" id="password" name="password">
                                <i class="bi bi-eye-slash password-toggle" data-target="password"></i>
                            </div>
                            <small class="text-muted">Leave blank if you don't want to set a password</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="create_user" class="btn btn-success">Create User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View User Modal -->
    <div class="modal fade" id="userModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-person-badge me-2"></i>
                        User Information
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="userModalContent">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    function viewUser(userId) {
        $.ajax({
            url: 'get_user_details.php',
            type: 'POST',
            data: { user_id: userId },
            success: function(response) {
                $('#userModalContent').html(response);
                $('#userModal').modal('show');
            },
            error: function() {
                alert('Error loading user details. Please try again.');
            }
        });
    }

    function deleteUser(userId) {
        if(confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
            $.ajax({
                url: '<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>',
                type: 'POST',
                data: { 
                    delete_user: true,
                    user_id: userId 
                },
                success: function(response) {
                    try {
                        const data = JSON.parse(response);
                        if(data.success) {
                            location.reload();
                        } else {
                            alert('Error: ' + (data.error || 'Unknown error occurred'));
                        }
                    } catch(e) {
                        location.reload();
                    }
                },
                error: function(xhr, status, error) {
                    alert('Error deleting user: ' + error);
                }
            });
        }
    }
    
    // Password toggle functionality
    $(document).ready(function() {
        $('.password-toggle').on('click', function() {
            const targetId = $(this).data('target');
            const passwordInput = $('#' + targetId);
            const isPassword = passwordInput.attr('type') === 'password';
            
            passwordInput.attr('type', isPassword ? 'text' : 'password');
            $(this).toggleClass('bi-eye-slash bi-eye');
        });
        
        // Form validation - only validate password if provided
        $('#createUserForm').on('submit', function(e) {
            const password = $('#password').val();
            
            if (password && password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters if provided');
                return false;
            }
            
            return true;
        });

        // Close alerts after 5 seconds
        setTimeout(function() {
            $('.alert').alert('close');
        }, 5000);
    });
    </script>
</body>
</html>