<?php
session_start();
include('../includes/db_connection.php');

if (!isset($_SESSION['admin']) || !isset($_POST['user_id'])) {
    exit('Unauthorized access');
}

$user_id = intval($_POST['user_id']);
$name = trim($_POST['name']);
$username = trim($_POST['username']);
$email = trim($_POST['email']);
$contact_no = trim($_POST['contact_no']);

// Validate inputs
if(empty($name) || empty($username) || empty($email) || empty($contact_no)) {
    echo "error: Empty fields not allowed";
    exit;
}

// Update user information
$sql = "UPDATE users SET 
        name = ?, 
        username = ?, 
        email = ?, 
        contact_no = ?,
        updated_at = CURRENT_TIMESTAMP 
        WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssi", $name, $username, $email, $contact_no, $user_id);

if($stmt->execute()) {
    echo "success";
} else {
    echo "error: " . $conn->error;
}

$stmt->close();
$conn->close();