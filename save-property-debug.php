
<?php
// Enable detailed error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');

// Log start of request
error_log("\n=== New Request " . date('Y-m-d H:i:s') . " ===");

// Start output buffering
ob_start();

// Start session
session_start();

// Log session data
error_log("Session data: " . print_r($_SESSION, true));

try {
    // Set headers
    header('Content-Type: application/json');
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not logged in');
    }

    // Get raw input data
    $raw_input = file_get_contents('php://input');
    error_log("Raw input: " . $raw_input);

    // Parse JSON
    $data = json_decode($raw_input, true);
    
    // Log decoded data
    error_log("Decoded data: " . print_r($data, true));

    // Validate input
    if (!isset($data['subcategory_id'])) {
        throw new Exception('Missing subcategory_id');
    }

    // Include database connection
    require_once('../includes/db_connection.php');
    
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception('Database connection failed: ' . ($conn->connect_error ?? 'Unknown error'));
    }

    // Clean any previous output
    ob_clean();

    // Return success response for testing
    echo json_encode([
        'success' => true,
        'message' => 'Debug mode - connection successful',
        'debug' => [
            'user_id' => $_SESSION['user_id'],
            'subcategory_id' => $data['subcategory_id'],
            'time' => date('Y-m-d H:i:s')
        ]
    ]);

} catch (Exception $e) {
    // Log error
    error_log("Error: " . $e->getMessage());
    
    // Clean any previous output
    ob_clean();
    
    // Send error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'error' => $e->getMessage(),
            'time' => date('Y-m-d H:i:s')
        ]
    ]);
}

// End output buffering
ob_end_flush();
exit();
?>