<?php
session_start();
include('../includes/db_connection.php');

if (!isset($_SESSION['admin']) || !isset($_POST['field_id'])) {
    echo "error";
    exit();
}

$field_id = intval($_POST['field_id']);

$sql = "DELETE FROM user_custom_fields WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $field_id);

if($stmt->execute()) {
    echo "success";
} else {
    echo "error";
}

$stmt->close();
$conn->close();
?>