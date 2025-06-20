<?php
session_start();
include('../includes/db_connection.php');

if (!isset($_SESSION['admin'])) {
    echo "unauthorized";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = intval($_POST['employee_id']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $department = trim($_POST['department']);
    $role = trim($_POST['role']);
    $status = trim($_POST['status']);
    
    // Validate
    if (empty($name) || empty($email) || empty($department) || empty($role)) {
        echo "All fields are required";
        exit();
    }
    
    // Check if employee exists
    $check_sql = "SELECT id FROM office_employees WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $employee_id);
    $check_stmt->execute();
    
    if ($check_stmt->get_result()->num_rows === 0) {
        echo "Employee not found";
        exit();
    }
    
    // Update employee
    $update_sql = "UPDATE office_employees SET name = ?, email = ?, department = ?, role = ?, status = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sssssi", $name, $email, $department, $role, $status, $employee_id);
    
    if ($update_stmt->execute()) {
        echo "success";
    } else {
        echo "Error updating employee";
    }
}
?>