<?php
session_start();
include('../includes/db_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "All fields are required";
        header("Location: employee_login.php");
        exit();
    }

    $sql = "SELECT * FROM office_employees WHERE email = ? AND status = 'active'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $employee = $result->fetch_assoc();

    if ($employee && password_verify($password, $employee['password'])) {
        $_SESSION['employee'] = [
            'id' => $employee['id'],
            'name' => $employee['name'],
            'email' => $employee['email'],
            'department' => $employee['department'],
            'role' => $employee['role']
        ];
        header("Location: employee_dashboard.php");
        exit();
    } else {
        $_SESSION['error'] = "Invalid email or password";
        header("Location: employee_login.php");
        exit();
    }
}
?>