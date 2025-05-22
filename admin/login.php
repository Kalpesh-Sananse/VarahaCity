<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
?>

<?php
session_start();
include('../includes/db_connection.php');  // Include database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = sha1($_POST['password']);  // SHA1 hashing for password security

    // Check if user exists in the database
    $sql = "SELECT * FROM admin WHERE username = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        // Start session and set session variables
        $user = $result->fetch_assoc();
        $_SESSION['admin'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // Redirect to admin dashboard if login is successful
        header("Location: ../admin/dashboardadmin.php");
        exit();
    } else {
        $error = "Invalid username or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Varaha City - Real Estate</title>
    <link rel="stylesheet" href="../css/login.css"> <!-- Specific styles for login -->
    <link rel="stylesheet" href="../css/style.css"> <!-- Global styles -->
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h2>Admin Login</h2>
            <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
            <form method="POST" action="login.php">
                <div class="input-group">
                    <input type="text" name="username" placeholder="Username" required>
                </div>
                <div class="input-group">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <div class="input-group">
                    <input type="submit" value="Login">
                </div>
            </form>
        </div>
    </div>
</body>
</html>
