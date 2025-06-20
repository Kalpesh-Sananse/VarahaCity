<?php
session_start();
include('./includes/db_connection.php');

$error = '';
$success = '';

// Check if user is already logged in
if(isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password";
    } else {
        // Prepare SQL statement to prevent SQL injection
        $sql = "SELECT id, username, password, name FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['name'] = $user['name'];
                
                // Redirect to previous page if exists, otherwise to index
                $redirect = isset($_SESSION['redirect_url']) ? $_SESSION['redirect_url'] : 'index.php';
                unset($_SESSION['redirect_url']);
                header("Location: " . $redirect);
                exit();
            } else {
                $error = "Invalid password";
            }
        } else {
            $error = "User not found";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Varaha City</title>
    
    <!-- Favicon -->
    <link rel="icon" href="./images/favicon.ico" type="image/x-icon">

    <!-- CSS Dependencies -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
    :root {
        --primary-color: #4D77FF;
        --secondary-color: #22c55e;
        --dark-color: #1a1a1a;
        --light-color: #f8f9fa;
        --transition: all 0.3s ease;
    }

    body {
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(135deg, #0A142F 0%, #1a2642 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .login-container {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        width: 100%;
        max-width: 400px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }

    .login-header {
        background: var(--primary-color);
        color: white;
        padding: 30px;
        text-align: center;
    }

    .login-header h1 {
        font-size: 24px;
        font-weight: 600;
        margin: 0;
    }

    .login-header p {
        margin: 10px 0 0;
        opacity: 0.9;
        font-size: 14px;
    }

    .login-form {
        padding: 30px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        font-weight: 500;
        color: var(--dark-color);
        margin-bottom: 8px;
    }

    .form-control {
        padding: 12px;
        border-radius: 8px;
        border: 1px solid #ddd;
        transition: var(--transition);
    }

    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(77, 119, 255, 0.1);
    }

    .btn-login {
        background-color: var(--primary-color);
        color: white;
        padding: 12px;
        border-radius: 8px;
        font-weight: 500;
        width: 100%;
        border: none;
        transition: var(--transition);
    }

    .btn-login:hover {
        background-color: #3d61cc;
        transform: translateY(-2px);
    }

    .register-link {
        text-align: center;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }

    .register-link a {
        color: var(--primary-color);
        text-decoration: none;
        font-weight: 500;
    }

    .register-link a:hover {
        text-decoration: underline;
    }

    .alert {
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .password-toggle {
        position: relative;
    }

    .password-toggle .toggle-password {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #666;
    }

    .brand-logo {
        width: 80px;
        height: 80px;
        margin-bottom: 15px;
    }

    @media (max-width: 480px) {
        .login-container {
            margin: 10px;
        }

        .login-header {
            padding: 20px;
        }

        .login-form {
            padding: 20px;
        }
    }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <img src="./images/logo.jpg" alt="Varaha City" class="brand-logo">
            <h1>Welcome Back!</h1>
            <p>Please login to continue</p>
        </div>

        <div class="login-form">
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">Username or Email</label>
                    <input type="text" 
                           name="username" 
                           class="form-control" 
                           placeholder="Enter your username or email"
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                           required>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="password-toggle">
                        <input type="password" 
                               name="password" 
                               class="form-control" 
                               placeholder="Enter your password"
                               required>
                        <i class="fas fa-eye toggle-password"></i>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </button>
                </div>
            </form>

            <div class="register-link">
                Don't have an account? <a href="register.php">Register Now</a>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Toggle password visibility
    document.querySelector('.toggle-password').addEventListener('click', function() {
        const password = document.querySelector('input[name="password"]');
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });
    </script>
</body>
</html>