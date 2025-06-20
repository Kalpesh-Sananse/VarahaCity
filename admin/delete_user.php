<?php
session_start();
include('../includes/db_connection.php');

if (!isset($_SESSION['admin']) || !isset($_POST['user_id'])) {
    exit('Unauthorized access');
}

$user_id = intval($_POST['user_id']);

// First delete additional info
$sql = "DELETE FROM user_additional_info WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

// Then delete user
$sql = "DELETE FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);

if($stmt->execute()) {
    echo "success";
} else {
    echo "error";
}