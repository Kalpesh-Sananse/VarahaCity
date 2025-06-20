<?php
session_start();
include('../includes/db_connection.php');

if (!isset($_SESSION['admin'])) {
    echo "unauthorized";
    exit();
}

if (isset($_POST['employee_id'])) {
    $employee_id = intval($_POST['employee_id']);
    
    // First check if employee exists
    $check_sql = "SELECT id FROM office_employees WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $employee_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo "Employee not found";
        exit();
    }
    
    // Delete the employee
    $delete_sql = "DELETE FROM office_employees WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $employee_id);
    
    if ($delete_stmt->execute()) {
        echo "success";
    } else {
        echo "Error deleting employee";
    }
} else {
    echo "Invalid request";
}
?>