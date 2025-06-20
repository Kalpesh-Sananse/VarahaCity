<?php
// Database configuration
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'mydb';

// Create connection without error handling first
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");
// config.php
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

?>