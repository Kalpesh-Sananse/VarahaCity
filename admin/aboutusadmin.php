<?php
session_start();
require_once '../includes/db_connection.php';

// Set defaults
date_default_timezone_set('UTC');
$current_datetime = date('Y-m-d H:i:s');
$current_user = 'Kalpesh-Sananse';

// Initialize messages
$success_msg = $error_msg = '';

// Fetch existing about us data
$about_query = "SELECT * FROM about_us WHERE status = 'active' ORDER BY id DESC LIMIT 1";
$about_result = $conn->query($about_query);
$about_data = $about_result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Initialize variables with empty strings if not set
    $title = isset($_POST['title']) ? $conn->real_escape_string($_POST['title']) : '';
    $short_description = isset($_POST['short_description']) ? $conn->real_escape_string($_POST['short_description']) : '';
    $detailed_description = isset($_POST['detailed_description']) ? $conn->real_escape_string($_POST['detailed_description']) : '';
    $hashtag = isset($_POST['hashtag']) ? $conn->real_escape_string($_POST['hashtag']) : '#BornToTravel';
    
    // Check if we're updating or creating
    $id = isset($_POST['about_id']) ? (int)$_POST['about_id'] : 0;
    
    // Handle image upload
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['image']['name'];
        $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($filetype, $allowed)) {
            $new_filename = 'about-' . time() . '.' . $filetype;
            $upload_dir = '../uploads/about/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_path = $new_filename;
            } else {
                $error_msg = "Failed to upload image.";
            }
        } else {
            $error_msg = "Invalid file type. Only JPG, JPEG, PNG & WEBP allowed.";
        }
    }
    
    if (empty($error_msg)) {
        if ($id > 0) {
            // Update existing record
            $query = "UPDATE about_us SET 
                     title = ?, 
                     short_description = ?, 
                     detailed_description = ?, 
                     hashtag = ?";
            
            if ($image_path) {
                $query .= ", image = ?";
            }
            
            $query .= " WHERE id = ?";
            
            $stmt = $conn->prepare($query);
            
            if ($image_path) {
                $stmt->bind_param("sssssi", $title, $short_description, 
                                $detailed_description, $hashtag, $image_path, $id);
            } else {
                $stmt->bind_param("ssssi", $title, $short_description, 
                                $detailed_description, $hashtag, $id);
            }
        } else {
            // Insert new record
            $stmt = $conn->prepare("INSERT INTO about_us (title, short_description, 
                                  detailed_description, image, hashtag) 
                                  VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $title, $short_description, 
                            $detailed_description, $image_path, $hashtag);
        }
        
        if ($stmt->execute()) {
            $success_msg = ($id > 0) ? "About Us section updated successfully!" : 
                                     "About Us section created successfully!";
            // Refresh the data
            $about_result = $conn->query($about_query);
            $about_data = $about_result->fetch_assoc();
        } else {
            $error_msg = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage About Us Section</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .preview-section {
            background-color: #FFF5F1;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .preview-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 10px;
        }
        .form-section {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .image-preview {
            max-width: 300px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container my-5">
        <div class="row mb-4">
            <div class="col">
                <h2>Manage About Us Section</h2>
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

        <!-- Preview Section -->
        <?php if($about_data): ?>
        <div class="preview-section mb-5">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <img src="../uploads/about/<?php echo $about_data['image']; ?>" 
                         alt="About Us Preview" class="preview-image">
                </div>
                <div class="col-md-6">
                    <h3 class="mt-3"><?php echo htmlspecialchars($about_data['title']); ?></h3>
                    <p class="mt-3"><?php echo nl2br(htmlspecialchars($about_data['short_description'])); ?></p>
                    <p class="fw-bold"><?php echo htmlspecialchars($about_data['hashtag']); ?></p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Form Section -->
        <div class="form-section">
            <form method="POST" enctype="multipart/form-data">
                <?php if($about_data): ?>
                    <input type="hidden" name="about_id" value="<?php echo $about_data['id']; ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control" required
                           value="<?php echo $about_data ? htmlspecialchars($about_data['title']) : ''; ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Short Description</label>
                    <textarea name="short_description" class="form-control" rows="4" required><?php 
                        echo $about_data ? htmlspecialchars($about_data['short_description']) : ''; 
                    ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Detailed Description</label>
                    <textarea name="detailed_description" class="form-control" rows="8" required><?php 
                        echo $about_data ? htmlspecialchars($about_data['detailed_description']) : ''; 
                    ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Image</label>
                    <input type="file" name="image" class="form-control" 
                           <?php echo !$about_data ? 'required' : ''; ?> accept="image/*" 
                           onchange="previewImage(this)">
                    <?php if($about_data): ?>
                        <small class="form-text">Leave empty to keep current image</small>
                    <?php endif; ?>
                    <div class="image-preview mt-2" id="imagePreview"></div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Hashtag</label>
                    <input type="text" name="hashtag" class="form-control" required
                           value="<?php echo $about_data ? htmlspecialchars($about_data['hashtag']) : '#BornToTravel'; ?>">
                </div>

                <button type="submit" class="btn btn-primary">
                    <?php echo $about_data ? 'Update' : 'Create'; ?> About Us Section
                </button>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" class="img-fluid">`;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>