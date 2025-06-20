<?php
session_start();
include('./includes/db_connection.php');

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

if (!isset($_POST['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'No product specified']);
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'];

// Delete from wishlist
$sql = "DELETE FROM wishlist WHERE user_id = ? AND product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $product_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}

$stmt->close();
$conn->close();
?>