<?php
// Start output buffering
ob_start();

// Basic error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'debug.log');

// Start session
session_start();

// Clear any output
ob_clean();

// Set headers
header('Content-Type: application/json');

try {
    // Include database connection
    require_once('../includes/db_connection.php');
    
    // Test database connection
    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // Return test response
    echo json_encode([
        'success' => true,
        'message' => 'Test successful',
        'debug' => [
            'time' => date('Y-m-d H:i:s'),
            'session_id' => session_id(),
            'user_id' => $_SESSION['user_id'] ?? 'not set'
        ]
    ]);

} catch (Exception $e) {
    // Clear output
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
<script>
    function testSave() {
    console.log('Starting test...');
    
    fetch('test-save.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    })
    .then(async response => {
        const responseText = await response.text();
        console.log('Raw response:', responseText);
        
        try {
            return JSON.parse(responseText);
        } catch (e) {
            console.error('Parse error:', e);
            console.error('Response text:', responseText);
            throw new Error('Invalid response format');
        }
    })
    .then(data => {
        console.log('Success:', data);
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

// Call the test function
testSave();
    </script>