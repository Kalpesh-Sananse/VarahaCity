<?php
session_start();
include('../includes/db_connection.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check session
if (!isset($_SESSION['admin'])) {
    echo "error: Not authorized";
    exit();
}

// Check if we have all required data
if (!isset($_POST['user_id']) || !isset($_POST['field_key']) || !isset($_POST['field_value'])) {
    echo "error: Missing required data";
    exit();
}

// Get and sanitize the data
$user_id = intval($_POST['user_id']);
$field_key = trim($conn->real_escape_string($_POST['field_key']));
$field_value = trim($conn->real_escape_string($_POST['field_value']));

// Validate data
if (empty($user_id) || empty($field_key) || empty($field_value)) {
    echo "error: Empty fields";
    exit();
}

try {
    // First check if user exists
    $check_user = "SELECT id FROM users WHERE id = ?";
    $stmt = $conn->prepare($check_user);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo "error: User not found";
        exit();
    }
    
    // Insert the new field
    $insert_sql = "INSERT INTO user_custom_fields (user_id, field_key, field_value) VALUES (?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("iss", $user_id, $field_key, $field_value);
    
    if ($insert_stmt->execute()) {
        echo "success";
    } else {
        echo "error: Insert failed";
    }
    
} catch (Exception $e) {
    error_log("Error in add_user_info.php: " . $e->getMessage());
    echo "error: " . $e->getMessage();
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($insert_stmt)) $insert_stmt->close();
    $conn->close();
}
?>