<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Start the session to check if the user is logged in
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include('../includes/db_connection.php'); // Include the database connection

// Variable to store messages
$message = "";
$messageType = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['photo'])) {
    $name = $_POST['name'];  // Get the category name
    
    // Check if name is provided
    if (empty($name)) {
        $message = "Please enter a category name.";
        $messageType = "error";
    } else {
        $photoName = $_FILES['photo']['name'];  // Get the uploaded file's name
        $photoTmpName = $_FILES['photo']['tmp_name'];  // Get the temporary file path
        
        // Check if a file was actually uploaded
        if (empty($photoName) || $_FILES['photo']['error'] > 0) {
            $message = "Please select a valid image to upload. Error: " . $_FILES['photo']['error'];
            $messageType = "error";
        } else {
            // Set the target directory for the image upload
            $targetDir = "../images/";
            
            // Create the directory if it doesn't exist
            if (!file_exists($targetDir)) {
                if (!mkdir($targetDir, 0777, true)) {
                    $message = "Failed to create images directory. Please create it manually.";
                    $messageType = "error";
                }
            }
            
            // Add timestamp to filename to avoid overwriting existing files
            $uniqueFilename = time() . '_' . basename($photoName);
            $targetFile = $targetDir . $uniqueFilename;
            
            // For database storage, use the relative path that will be referenced from frontend
            $dbFilePath = 'images/' . $uniqueFilename;

            // Validate image file type (allow only JPG, PNG, JPEG, GIF)
            $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
            if (in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
                // Try moving the uploaded file to the target directory
                if (move_uploaded_file($photoTmpName, $targetFile)) {
                    // Prepare SQL query to insert category data into the database
                    $sql = "INSERT INTO categories (name, photo) VALUES (?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ss", $name, $dbFilePath);  // Use relative path for database
                    
                    if ($stmt->execute()) {
                        $message = "Category added successfully!";
                        $messageType = "success";
                    } else {
                        $message = "Error inserting category into the database: " . $stmt->error;
                        $messageType = "error";
                    }
                } else {
                    $error = error_get_last();
                    $message = "Error moving the uploaded image. Please check if the directory has proper permissions.";
                    $messageType = "error";
                }
            } else {
                $message = "Only image files (JPG, JPEG, PNG, GIF) are allowed.";
                $messageType = "error";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Category - Real Estate</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f7fc;
            color: #333;
        }

        .form-container {
            max-width: 650px;
            margin: 50px auto;
            padding: 35px;
            border-radius: 15px;
            background-color: #fff;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .form-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
        }

        .page-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .page-header h2 {
            font-weight: 600;
            color: #2980b9;
            position: relative;
            display: inline-block;
            padding-bottom: 10px;
        }

        .page-header h2:after {
            content: '';
            position: absolute;
            width: 60%;
            height: 3px;
            background: linear-gradient(135deg, #2980b9, #6dd5fa);
            bottom: 0;
            left: 20%;
            border-radius: 3px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            font-weight: 500;
            color: #2980b9;
            margin-bottom: 10px;
            display: block;
        }

        .form-control {
            border-radius: 8px;
            padding: 12px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s;
        }

        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(41, 128, 185, 0.2);
            border-color: #2980b9;
        }

        .file-input-container {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }

        .file-input-button {
            display: block;
            padding: 12px 20px;
            background: linear-gradient(135deg, #2980b9, #6dd5fa);
            border: none;
            border-radius: 8px;
            color: white;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .file-input-button:hover {
            background: linear-gradient(135deg, #2573a7, #5ac8f5);
            transform: translateY(-2px);
        }

        .file-name-display {
            margin-top: 10px;
            font-size: 0.9em;
            color: #666;
        }

        .btn-submit {
            padding: 12px 30px;
            background: linear-gradient(135deg, #2980b9, #6dd5fa);
            border: none;
            border-radius: 50px;
            color: white;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(41, 128, 185, 0.3);
            width: auto;
            min-width: 200px;
        }

        .btn-submit:hover {
            background: linear-gradient(135deg, #2573a7, #5ac8f5);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(41, 128, 185, 0.4);
        }

        .alert {
            border-radius: 8px;
            border: none;
            padding: 15px 20px;
        }

        .alert-success {
            background-color: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid #2e7d32;
        }

        .alert-danger {
            background-color: #fef2f2;
            color: #dc2626;
            border-left: 4px solid #dc2626;
        }

        .progress-bar-container {
            margin-top: 15px;
            border-radius: 10px;
            overflow: hidden;
            background-color: #e0e0e0;
            display: none;
        }

        .progress-bar {
            width: 0%;
            height: 8px;
            background: linear-gradient(135deg, #2980b9, #6dd5fa);
            border-radius: 10px;
            transition: width 0.3s ease;
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .form-container {
                margin: 20px;
                padding: 25px;
            }

            .btn-submit {
                width: 100%;
            }

            .page-header h2 {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .form-container {
                margin: 10px;
                padding: 20px;
            }

            .file-input-button {
                padding: 10px 15px;
            }
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-group {
            animation: fadeIn 0.5s ease-out forwards;
        }

        .form-group:nth-child(1) { animation-delay: 0.1s; }
        .form-group:nth-child(2) { animation-delay: 0.2s; }
        .btn-submit { animation: fadeIn 0.5s ease-out 0.3s forwards; }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<!-- Dashboard Content -->
<div class="container mt-5">
    <div class="form-container">
        <div class="page-header">
            <h2><i class="fas fa-plus-circle mr-2"></i>Add New Category</h2>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $messageType == 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>
        
        <form id="addCategoryForm" action="add_categories.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name"><i class="fas fa-tag mr-1"></i> Category Name</label>
                <input type="text" class="form-control" id="name" name="name" placeholder="Enter category name" required>
            </div>
            <div class="form-group">
                <label for="photo"><i class="fas fa-image mr-1"></i> Category Image</label>
                <div class="file-input-container">
                    <label class="file-input-button" for="photo">
                        <i class="fas fa-cloud-upload-alt"></i> Choose Image
                        <input type="file" class="form-control-file" id="photo" name="photo" style="display: none;" required>
                    </label>
                    <div class="file-name-display" id="file-name">No file chosen</div>
                </div>
            </div>
            <div class="progress-bar-container">
                <div class="progress-bar" id="progress-bar"></div>
            </div>
            <div class="text-center mt-4">
                <button type="submit" class="btn-submit"><i class="fas fa-plus-circle mr-2"></i>Add Category</button>
            </div>
        </form>
    </div>
</div>

<!-- Bootstrap JS and dependencies (Popper.js & jQuery) -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<!-- Custom JS for Form Handling -->
<script>
    // Display file name when selected
    document.getElementById("photo").addEventListener("change", function() {
        const fileName = this.files[0] ? this.files[0].name : "No file chosen";
        document.getElementById("file-name").textContent = fileName;
    });

    // Optional: AJAX form submission with progress bar
    $(document).ready(function() {
        $("#addCategoryForm").submit(function(e) {
            // Don't use AJAX submission for now, as we're handling the form with PHP
            // Keep the progress bar code for future enhancement
            
            /* Uncomment for AJAX submission
            e.preventDefault();
            
            // Show the progress bar
            $('.progress-bar-container').show();
            
            var formData = new FormData(this);
            
            $.ajax({
                url: 'add_categories.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: function() {
                    var xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function(evt) {
                        if (evt.lengthComputable) {
                            var percentComplete = evt.loaded / evt.total;
                            percentComplete = parseInt(percentComplete * 100);
                            $('#progress-bar').css('width', percentComplete + '%');
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    // Handle success
                    $('.success-message').show().text('Category added successfully!');
                    setTimeout(function() {
                        window.location.reload();
                    }, 2000);
                },
                error: function(xhr, status, error) {
                    // Handle error
                    $('.error-message').show().text('Error: ' + error);
                }
            });
            */
        });
    });
    
    // Make alerts auto-dismiss after 5 seconds
    $(document).ready(function(){
        // Auto close alerts after 5 seconds
        window.setTimeout(function() {
            $(".alert").fadeTo(500, 0).slideUp(500, function(){
                $(this).remove(); 
            });
        }, 5000);
    });
</script>

</body>
</html>