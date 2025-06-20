<?php
session_start();
include '../includes/db_connection.php';


// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_link'])) {
    $new_link = mysqli_real_escape_string($conn, $_POST['form_link']);
    $admin_name = mysqli_real_escape_string($conn, $_SESSION['admin_name']);
    
    // Update or insert new link
    $sql = "INSERT INTO contact_form_links (form_link, created_by) 
            VALUES ('$new_link', '$admin_name')
            ON DUPLICATE KEY UPDATE 
            form_link = VALUES(form_link),
            updated_at = CURRENT_TIMESTAMP";
    
    if ($conn->query($sql)) {
        $success_msg = "Form link updated successfully!";
    } else {
        $error_msg = "Error updating link: " . $conn->error;
    }
}

// Get current link
$sql = "SELECT form_link FROM contact_form_links WHERE is_active = 1 ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);
$current_link = $result->num_rows > 0 ? $result->fetch_assoc()['form_link'] : '';

// Get current date time in your format
$current_datetime = date('Y-m-d H:i:s');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Contact Form Link</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary-color: #0A142F;
            --secondary-color: #1a2642;
        }

        .page-wrapper {
            min-height: 100vh;
            background: #f8f9fa;
            padding: 20px;
        }

        .datetime-user {
            border-radius: 50px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 10px 25px;
            font-weight: 600;
            box-shadow: 0 4px 10px rgba(10, 20, 47, 0.3);
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .datetime-user-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .datetime-user-item i {
            color: #4D77FF;
            font-size: 16px;
        }

        .datetime-user-label {
            font-size: 12px;
            opacity: 0.8;
        }

        .datetime-user-value {
            font-size: 14px;
        }

        .form-container {
            max-width: 800px;
            margin: 20px auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 30px;
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
        }

        .form-control {
            border-radius: 8px;
            padding: 12px;
            border: 2px solid #e1e1e1;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(10, 20, 47, 0.25);
        }

        .btn-submit {
            border-radius: 50px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            box-shadow: 0 4px 10px rgba(10, 20, 47, 0.3);
            transition: all 0.3s;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(10, 20, 47, 0.4);
            color: white;
        }

        @media (max-width: 768px) {
            .datetime-user {
                flex-direction: column;
                align-items: flex-start;
                padding: 15px 20px;
            }
            
            .form-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        

        <div class="form-container">
            <div class="page-header">
                <h4 class="mb-0"><i class="bi bi-link-45deg me-2"></i>Manage Contact Form Link</h4>
            </div>

            <?php if(isset($success_msg)): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i><?php echo $success_msg; ?>
                </div>
            <?php endif; ?>
            
            <?php if(isset($error_msg)): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle me-2"></i><?php echo $error_msg; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-4">
                    <label for="form_link" class="form-label">
                        <i class="bi bi-link me-2"></i>Google Form Link
                    </label>
                    <input type="url" 
                           class="form-control" 
                           id="form_link" 
                           name="form_link" 
                           value="<?php echo htmlspecialchars($current_link); ?>" 
                           placeholder="Enter Google Form URL"
                           required>
                    <div class="form-text mt-2">
                        <i class="bi bi-info-circle me-2"></i>
                        Enter the complete Google Form URL
                    </div>
                </div>
                <button type="submit" name="update_link" class="btn btn-submit">
                    <i class="bi bi-check2-circle me-2"></i>Update Link
                </button>
            </form>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>