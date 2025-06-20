<?php
session_start();
include('../includes/db_connection.php');

if (!isset($_SESSION['admin'])) {
    echo "unauthorized";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $department = trim($_POST['department']);
    $role = trim($_POST['role']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate
    if (empty($name) || empty($email) || empty($department) || empty($role) || empty($password)) {
        echo "All fields are required";
        exit();
    }

    if ($password !== $confirm_password) {
        echo "Passwords do not match";
        exit();
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert employee
    $sql = "INSERT INTO office_employees (name, email, department, role, password, status) 
            VALUES (?, ?, ?, ?, ?, 'active')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $name, $email, $department, $role, $hashed_password);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>