<?php
$host = 'localhost'; // Database server
$user = 'root';      // Database username
$pass = '';          // Database password
$dbname = 'mydb'; // Your database name

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
