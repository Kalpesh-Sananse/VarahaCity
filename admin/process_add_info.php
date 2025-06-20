<?php
session_start();
include('../includes/db_connection.php');

if (!isset($_SESSION['admin']) || !isset($_POST['user_id'])) {
    echo "error";
    exit();
}

$user_id = intval($_POST['user_id']);
$info_key = trim($conn->real_escape_string($_POST['info_key']));
$info_value = trim($conn->real_escape_string($_POST['info_value']));

// Validate
if(empty($user_id) || empty($info_key) || empty($info_value)) {
    echo "error";
    exit();
}

// Insert the information
$sql = "INSERT INTO user_additional_info (user_id, info_key, info_value) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $user_id, $info_key, $info_value);

if($stmt->execute()) {
    echo "success";
} else {
    echo "error";
}

$stmt->close();
$conn->close();
?>