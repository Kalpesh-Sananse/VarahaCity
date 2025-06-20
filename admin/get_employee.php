<?php
session_start();
include('../includes/db_connection.php');

if (!isset($_SESSION['admin'])) {
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

if (isset($_POST['employee_id'])) {
    $employee_id = intval($_POST['employee_id']);
    
    $sql = "SELECT id, name, email, department, role, status FROM office_employees WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $employee = $result->fetch_assoc();
    
    if ($employee) {
        echo json_encode($employee);
    } else {
        echo json_encode(['error' => 'Employee not found']);
    }
}
?>