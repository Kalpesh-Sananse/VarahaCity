<?php
// Start output buffering
ob_start();

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear any previous output
ob_clean();

// Set JSON headers
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

try {
    // Verify user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Please login to save properties');
    }

    // Get and decode JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    error_log("Received data: " . print_r($data, true));

    // Validate input
    if (!isset($data['subcategory_id'])) {
        throw new Exception('Missing subcategory ID');
    }

    // Include database connection
    require_once('../includes/db_connection.php');

    // Sanitize inputs
    $user_id = filter_var($_SESSION['user_id'], FILTER_VALIDATE_INT);
    $subcategory_id = filter_var($data['subcategory_id'], FILTER_VALIDATE_INT);

    if ($user_id === false || $subcategory_id === false) {
        throw new Exception('Invalid input data');
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Check if subcategory exists
        $stmt = $conn->prepare("SELECT id, name FROM subcategories WHERE id = ? LIMIT 1");
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }

        $stmt->bind_param("i", $subcategory_id);
        if (!$stmt->execute()) {
            throw new Exception("Error checking subcategory: " . $stmt->error);
        }

        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            $stmt->close();
            throw new Exception("Subcategory not found");
        }
        
        $subcategory = $result->fetch_assoc();
        $stmt->close();

        // Check if property is already saved
        $stmt = $conn->prepare("SELECT id FROM saved_properties WHERE user_id = ? AND subcategory_id = ? LIMIT 1");
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }

        $stmt->bind_param("ii", $user_id, $subcategory_id);
        if (!$stmt->execute()) {
            throw new Exception("Error checking saved status: " . $stmt->error);
        }

        $result = $stmt->get_result();
        $is_saved = $result->num_rows > 0;
        $stmt->close();

        if ($is_saved) {
            // Remove from saved
            $stmt = $conn->prepare("DELETE FROM saved_properties WHERE user_id = ? AND subcategory_id = ?");
            if (!$stmt) {
                throw new Exception("Database error: " . $conn->error);
            }

            $stmt->bind_param("ii", $user_id, $subcategory_id);
            if (!$stmt->execute()) {
                throw new Exception("Error removing property: " . $stmt->error);
            }
            $stmt->close();

            $message = "Property removed from saved items";
        } else {
            // Create saved_properties table if it doesn't exist
            $conn->query("CREATE TABLE IF NOT EXISTS saved_properties (
                id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                user_id INT(11) NOT NULL,
                subcategory_id INT(11) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_save (user_id, subcategory_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

            // Add to saved
            $stmt = $conn->prepare("INSERT INTO saved_properties (user_id, subcategory_id) VALUES (?, ?)");
            if (!$stmt) {
                throw new Exception("Database error: " . $conn->error);
            }

            $stmt->bind_param("ii", $user_id, $subcategory_id);
            if (!$stmt->execute()) {
                // Check if error is due to duplicate entry
                if ($conn->errno === 1062) {
                    throw new Exception("This property is already saved");
                }
                throw new Exception("Error saving property: " . $stmt->error);
            }
            $stmt->close();

            $message = "Property saved successfully";
        }

        // Commit transaction
        $conn->commit();

        // Clear output buffer
        ob_clean();

        // Send success response
        echo json_encode([
            'success' => true,
            'message' => $message,
            'is_saved' => !$is_saved,
            'debug' => [
                'user_id' => $user_id,
                'subcategory_id' => $subcategory_id,
                'subcategory_name' => $subcategory['name'],
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ]);

    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    // Log error
    error_log("Save property error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());

    // Clear output buffer
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

// End output buffering and exit
ob_end_flush();
exit();
?>