<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
?>
<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$db = new mysqli('localhost', 'root', '', 'mydb');
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $result = $db->query("SELECT * FROM sliders WHERE id = $id");
    
    if ($result->num_rows > 0) {
        $slider = $result->fetch_assoc();
        header('Content-Type: application/json');
        echo json_encode($slider);
    } else {
        header('HTTP/1.1 404 Not Found');
    }
} else {
    header('HTTP/1.1 400 Bad Request');
}
?>