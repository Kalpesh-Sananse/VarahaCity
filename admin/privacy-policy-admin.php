<?php
session_start();
require_once '../includes/db_connection.php';

// Set defaults
date_default_timezone_set('UTC');
$current_datetime = '2025-05-27 19:50:20';
$current_user = 'Kalpesh-Sananse';

// Initialize messages
$success_msg = $error_msg = '';

// Fetch existing privacy policy data (only the most recent active one)
$policy_query = "SELECT * FROM privacy_policy WHERE status = 'active' ORDER BY id DESC LIMIT 1";
$policy_result = $conn->query($policy_query);
$policy = $policy_result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = isset($_POST['title']) ? $conn->real_escape_string($_POST['title']) : '';
    $description = isset($_POST['description']) ? $_POST['description'] : '';

    if ($policy) {
        // Update existing record
        $stmt = $conn->prepare("UPDATE privacy_policy SET title = ?, description = ?, updated_at = ? WHERE id = ?");
        $stmt->bind_param("sssi", $title, $description, $current_datetime, $policy['id']);
    } else {
        // Insert new record
        $stmt = $conn->prepare("INSERT INTO privacy_policy (title, description, created_by, created_at) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $title, $description, $current_user, $current_datetime);
    }

    if ($stmt->execute()) {
        $success_msg = $policy ? "Privacy policy updated successfully!" : "Privacy policy created successfully!";
        // Refresh the data
        $policy_result = $conn->query($policy_query);
        $policy = $policy_result->fetch_assoc();
    } else {
        $error_msg = "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Privacy Policy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <style>
        .form-section {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .note-editor {
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container my-5">
        <div class="row mb-4">
            <div class="col">
                <h2>Manage Privacy Policy</h2>
            </div>
        </div>

        <?php if($success_msg): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $success_msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if($error_msg): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo $error_msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Privacy Policy Form -->
        <div class="form-section">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control" 
                           value="<?php echo $policy ? htmlspecialchars($policy['title']) : ''; ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Privacy Policy Content</label>
                    <textarea name="description" class="form-control editor" required><?php 
                        echo $policy ? $policy['description'] : ''; 
                    ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">
                    <?php echo $policy ? 'Update' : 'Save'; ?> Privacy Policy
                </button>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.editor').summernote({
                height: 500,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
        });
    </script>
</body>
</html>