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
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f6f9;
            background-image: linear-gradient(to right, #f4f6f9, #e9ecef);
        }

        .form-container {
            max-width: 650px;
            margin: 50px auto;
            padding: 35px;
            border-radius: 15px;
            background-color: #fff;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
        }

        .form-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
        }

        .progress-bar-container {
            display: none;
            margin-top: 15px;
            border-radius: 10px;
            overflow: hidden;
            background-color: #f0f0f0;
        }

        .progress-bar {
            width: 0%;
            height: 20px;
            background: linear-gradient(to right, #4caf50, #8bc34a);
            border-radius: 10px;
            transition: width 0.3s ease;
        }

        .error-message {
            color: #d32f2f;
            background-color: #ffebee;
            border-left: 4px solid #d32f2f;
            padding: 10px 15px;
            margin-top: 15px;
            border-radius: 4px;
            display: none;
        }

        .success-message {
            color: #388e3c;
            background-color: #e8f5e9;
            border-left: 4px solid #388e3c;
            padding: 10px 15px;
            margin-top: 15px;
            border-radius: 4px;
            display: none;
        }

        .btn-submit {
            padding: 12px 25px;
            background: linear-gradient(to right, #FF6347, #FF4500);
            border: none;
            border-radius: 50px;
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(255, 99, 71, 0.3);
        }

        .btn-submit:hover {
            background: linear-gradient(to right, #FF4500, #FF3300);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(255, 99, 71, 0.4);
        }

        .form-group label {
            font-weight: 600;
            color: #555;
            margin-bottom: 8px;
        }

        .form-control {
            border-radius: 8px;
            padding: 12px;
            border: 1px solid #ddd;
            transition: all 0.3s;
        }

        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(255, 99, 71, 0.2);
            border-color: #FF6347;
        }

        .file-input-container {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }

        .file-input-button {
            display: block;
            padding: 10px 15px;
            background-color: #f0f0f0;
            border: 1px dashed #ccc;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .file-input-button:hover {
            background-color: #e6e6e6;
            border-color: #FF6347;
        }

        .file-input-button i {
            margin-right: 8px;
        }

        .file-name-display {
            margin-top: 8px;
            font-size: 0.9em;
            color: #666;
        }

        .page-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .page-header h2 {
            font-weight: 700;
            color: #333;
            position: relative;
            display: inline-block;
        }

        .page-header h2:after {
            content: '';
            position: absolute;
            width: 60%;
            height: 3px;
            background: linear-gradient(to right, #FF6347, #FF4500);
            bottom: -10px;
            left: 20%;
            border-radius: 3px;
        }

        /* Custom styles for navbar */
        .navbar {
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            background: linear-gradient(to right, #fff, #f8f9fa);
        }

        .navbar-brand {
            font-weight: 700;
            color: #FF6347 !important;
        }

        .nav-item .nav-link {
            font-weight: 600;
            color: #555;
            transition: all 0.3s;
        }

        .nav-item .nav-link:hover {
            color: #FF6347;
        }

        /* Animation for form elements */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-group {
            animation: fadeIn 0.5s ease-out forwards;
        }

        .form-group:nth-child(1) { animation-delay: 0.1s; }
        .form-group:nth-child(2) { animation-delay: 0.2s; }
        button.btn-submit { animation: fadeIn 0.5s ease-out 0.3s forwards; opacity: 0; }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="#"><i class="fas fa-home mr-2"></i>Varaha City</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt mr-1"></i> Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="about.php"><i class="fas fa-info-circle mr-1"></i> About Us</a>
            </li>
            <li class="nav-item active">
                <a class="nav-link" href="categories.php"><i class="fas fa-list-alt mr-1"></i> Categories</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="users.php"><i class="fas fa-users mr-1"></i> Users</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="contact.php"><i class="fas fa-envelope mr-1"></i> Contact Us</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt mr-1"></i> Logout</a>
            </li>
        </ul>
    </div>
</nav>

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