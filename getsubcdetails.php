<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include('./includes/db_connection.php');

// Get subcategory ID from URL
$subcategory_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch subcategory details
$sql = "SELECT s.*, c.name as category_name, c.id as category_id 
        FROM subcategories s 
        JOIN categories c ON s.parent_category_id = c.id 
        WHERE s.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $subcategory_id);
$stmt->execute();
$result = $stmt->get_result();
$subcategory = $result->fetch_assoc();

// Redirect if subcategory doesn't exist
if (!$subcategory) {
    header("Location: index.php");
    exit();
}

// Fetch custom fields
$sql = "SELECT * FROM custom_fields WHERE subcategory_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $subcategory_id);
$stmt->execute();
$custom_fields = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch additional images
$sql = "SELECT * FROM subcategory_images WHERE subcategory_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $subcategory_id);
$stmt->execute();
$additional_images = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

/**
 * Helper function to ensure image paths are correctly formatted
 * @param string $path The original image path
 * @return string The corrected image path
 */
function formatImagePath($path) {
    // If path is empty, return a default image
    if (empty($path)) {
        return "assets/images/default-property.jpg";
    }
    
    // If path already starts with http/https, it's an external URL, return as is
    if (preg_match('/^https?:\/\//', $path)) {
        return $path;
    }
    
    // If path doesn't start with a slash, add one
    if (substr($path, 0, 1) !== '/') {
        $path = '/' . $path;
    }
    
    // Remove any double slashes
    $path = preg_replace('/\/+/', '/', $path);
    
    // If path doesn't start with the server's document root, prepend it
    $docRoot = $_SERVER['DOCUMENT_ROOT'];
    if (strpos($path, $docRoot) !== 0) {
        // If we need to serve from document root, we should remove the doc root from the path
        if (strpos($path, $docRoot) === false) {
            $path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $path);
        }
    }
    
    // Check if file exists
    $fullServerPath = $_SERVER['DOCUMENT_ROOT'] . $path;
    if (!file_exists($fullServerPath)) {
        // Log the missing file
        error_log("Image not found: " . $fullServerPath);
        return "assets/images/default-property.jpg";
    }
    
    return $path;
}

// Process main photo path
$mainPhoto = isset($subcategory['photo']) ? formatImagePath($subcategory['photo']) : "assets/images/default-property.jpg";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($subcategory['name']); ?> - Real Estate</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2980b9;
            --secondary-color: #6dd5fa;
            --accent-color: #FF6347;
            --dark-color: #2c3e50;
            --light-color: #f8f9fa;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f6f9;
            color: #333;
        }
        
        .subcategory-header {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
            padding-bottom: 20px;
        }
        
        .subcategory-header h1 {
            font-weight: 700;
            color: var(--dark-color);
            display: inline-block;
            position: relative;
        }
        
        .subcategory-header h1::after {
            content: '';
            position: absolute;
            width: 80px;
            height: 4px;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 2px;
        }
        
        .breadcrumb-custom {
            background-color: white;
            padding: 10px 20px;
            border-radius: 50px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: inline-flex;
            margin-bottom: 30px;
        }
        
        .subcategory-image-container {
            height: 350px;
            overflow: hidden;
            border-radius: 15px;
            margin-bottom: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            position: relative;
        }
        
        .subcategory-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .subcategory-image-container:hover .subcategory-image {
            transform: scale(1.03);
        }
        
        /* Image Error Handling */
        .subcategory-image-container .image-error {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: rgba(0,0,0,0.7);
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .image-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            opacity: 0;
            transition: opacity 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }
        
        .gallery-item:hover .image-overlay {
            opacity: 1;
        }
        
        .property-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 50px;
            font-weight: 500;
            margin-right: 10px;
            margin-bottom: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        
        .badge-buy {
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            color: white;
        }
        
        .badge-sale {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
        }
        
        .badge-rent {
            background: linear-gradient(135deg, #e67e22, #d35400);
            color: white;
        }
        
        .badge-lease {
            background: linear-gradient(135deg, #9b59b6, #8e44ad);
            color: white;
        }
        
        .badge-status {
            background: linear-gradient(135deg, #f1c40f, #f39c12);
            color: white;
        }
        
        .youtube-embed {
            position: relative;
            padding-bottom: 56.25%; /* 16:9 aspect ratio */
            height: 0;
            overflow: hidden;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
        
        .youtube-embed iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 15px;
            border: none;
        }
        
        .card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            margin-bottom: 25px;
            overflow: hidden;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            font-weight: 600;
            border-radius: 15px 15px 0 0 !important;
            padding: 15px 20px;
        }
        
        .custom-field-item {
            background-color: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
            position: relative;
        }
        
        .custom-field-title {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .custom-field-value {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 5px 10px;
            margin-right: 8px;
            margin-bottom: 8px;
            display: inline-block;
            font-size: 0.9rem;
        }
        
        .gallery-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .gallery-item {
            position: relative;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            aspect-ratio: 1/1;
        }
        
        .gallery-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }
        
        .gallery-item:hover .gallery-img {
            transform: scale(1.05);
        }
        
        .btn-back {
            background: linear-gradient(135deg, #95a5a6, #7f8c8d);
            border: none;
            border-radius: 50px;
            padding: 10px 25px;
            color: white;
            font-weight: 500;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .btn-back:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.15);
            color: white;
        }
        
        .description-section {
            background-color: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }
        
        .contact-info {
            background: linear-gradient(135deg, #2980b9, #6dd5fa);
            border-radius: 15px;
            padding: 25px;
            color: white;
            margin-bottom: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .contact-info h4 {
            border-bottom: 1px solid rgba(255,255,255,0.3);
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        
        .contact-info i {
            width: 25px;
            text-align: center;
            margin-right: 10px;
        }
        
        /* Animation for cards */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .card, .custom-field-item {
            animation: fadeIn 0.5s ease-out forwards;
        }
        
        @media (max-width: 768px) {
            .subcategory-image-container {
                height: 250px;
            }
            
            .gallery-container {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
        }
        
        /* Modal for image viewing */
        .image-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            padding-top: 50px;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.9);
        }
        
        .modal-content {
            margin: auto;
            display: block;
            max-width: 90%;
            max-height: 80%;
            object-fit: contain;
        }
        
        .close {
            position: absolute;
            top: 15px;
            right: 25px;
            color: #f1f1f1;
            font-size: 40px;
            font-weight: bold;
            transition: 0.3s;
            z-index: 1001;
        }
        
        .close:hover,
        .close:focus {
            color: #bbb;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container py-4 py-lg-5">
        <div class="subcategory-header">
            
            <h1><?php echo htmlspecialchars($subcategory['name']); ?></h1>
        </div>
        
        <div class="row">
            <div class="col-lg-8">
                <!-- Main Image -->
                <div class="subcategory-image-container">
                <img src="./<?php echo htmlspecialchars($subcategory['photo']); ?>" 
                                 alt="<?php echo htmlspecialchars($subcategory['name']); ?>" 
                                 class="subcategory-image"
                         onerror="this.onerror=null; this.src='assets/images/default-property.jpg'; this.parentNode.innerHTML += '<div class=\'image-error\'>Image could not be loaded</div>'">
                </div>
                
                <!-- Property Type Badges -->
                <div class="mb-4">
                    <?php if (isset($subcategory['property_type']) && !empty($subcategory['property_type'])): ?>
                        <span class="property-badge <?php echo $subcategory['property_type'] === 'buy' ? 'badge-buy' : 'badge-sale'; ?>">
                            <i class="fas <?php echo $subcategory['property_type'] === 'buy' ? 'fa-shopping-cart' : 'fa-tag'; ?> me-2"></i>
                            <?php echo ucfirst($subcategory['property_type']); ?>
                        </span>
                    <?php endif; ?>
                    
                    <?php if (isset($subcategory['transaction_type']) && !empty($subcategory['transaction_type'])): ?>
                        <span class="property-badge <?php echo $subcategory['transaction_type'] === 'rent' ? 'badge-rent' : 'badge-lease'; ?>">
                            <i class="fas <?php echo $subcategory['transaction_type'] === 'rent' ? 'fa-home' : 'fa-file-contract'; ?> me-2"></i>
                            <?php echo ucfirst($subcategory['transaction_type']); ?>
                        </span>
                    <?php endif; ?>
                    
                    <?php if (isset($subcategory['transaction_status']) && !empty($subcategory['transaction_status'])): ?>
                        <span class="property-badge badge-status">
                            <i class="fas fa-info-circle me-2"></i>
                            <?php echo ucfirst($subcategory['transaction_status']); ?>
                        </span>
                    <?php endif; ?>
                </div>
                
                <!-- YouTube Video if available -->
                <?php 
                $youtube_id = '';
                if (!empty($subcategory['youtube_link']) && 
                    preg_match('/(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', 
                    $subcategory['youtube_link'], $matches)) {
                    $youtube_id = $matches[1];
                }
                
                if ($youtube_id):
                ?>
                    <div class="youtube-embed">
                        <iframe src="https://www.youtube.com/embed/<?php echo htmlspecialchars($youtube_id); ?>" 
                                allowfullscreen
                                onerror="this.parentNode.innerHTML = '<div class=\'p-5 text-center\'><i class=\'fas fa-exclamation-circle fa-3x text-muted mb-3\'></i><p>Video could not be loaded</p></div>'">
                        </iframe>
                    </div>
                <?php endif; ?>
                
                <!-- Description Section -->
                <div class="description-section">
                    <h4><i class="fas fa-info-circle me-2"></i>About this property</h4>
                    <p>
                        <?php 
                        if (isset($subcategory['description']) && !empty($subcategory['description'])) {
                            echo nl2br(htmlspecialchars($subcategory['description']));
                        } else {
                            echo "This is a " . htmlspecialchars($subcategory['name']) . " property available for " . 
                                 (isset($subcategory['transaction_type']) ? htmlspecialchars($subcategory['transaction_type']) : "purchase") . ".";
                        }
                        ?>
                    </p>
                </div>
                
                <!-- Additional Images Gallery -->
                <?php if (count($additional_images) > 0): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-images me-2"></i> Property Gallery</h5>
                        </div>
                        <div class="card-body">
                            <div class="gallery-container">
                                <?php foreach ($additional_images as $image): 
                                    $imagePath = formatImagePath($image['image_path']);
                                ?>
                                    <div class="gallery-item" onclick="openModal('<?php echo htmlspecialchars($imagePath); ?>')">
                                    <img src="./<?php echo htmlspecialchars($image['image_path']); ?>" class="gallery-img"
                                            
                                             onerror="this.onerror=null; this.src='assets/images/default-property.jpg';">
                                        <div class="image-overlay">
                                            <i class="fas fa-search-plus fa-2x"></i>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Custom Fields Section -->
                <?php if (count($custom_fields) > 0): ?>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-list-ul me-2"></i> Property Features</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach($custom_fields as $index => $field): ?>
                                    <?php 
                                    // Fetch values for this field
                                    $sql = "SELECT * FROM custom_field_values WHERE field_id = ?";
                                    $stmt = $conn->prepare($sql);
                                    $stmt->bind_param("i", $field['id']);
                                    $stmt->execute();
                                    $values = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                                    ?>
                                    <div class="col-md-6 mb-4">
                                        <div class="custom-field-item" style="animation-delay: <?php echo $index * 0.1; ?>s;">
                                            <h5 class="custom-field-title"><?php echo htmlspecialchars($field['title']); ?></h5>
                                            <?php if (count($values) > 0): ?>
                                                <?php foreach($values as $value): ?>
                                                    <span class="custom-field-value">
                                                        <i class="fas fa-check-circle me-1 text-success"></i>
                                                        <?php echo htmlspecialchars($value['value']); ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <p class="text-muted small">No values available</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="col-lg-4">
                <!-- Contact Information Section -->
                <div class="contact-info">
                    <h4><i class="fas fa-address-card me-2"></i> Contact Info</h4>
                    <p><i class="fas fa-user"></i> John Doe</p>
                    <p><i class="fas fa-phone"></i> (123) 456-7890</p>
                    <p><i class="fas fa-envelope"></i> info@example.com</p>
                    <p><i class="fas fa-map-marker-alt"></i> 123 Main St, Anytown</p>
                    
                    <div class="mt-4">
                        <a href="contact-us.php?property=<?php echo $subcategory_id; ?>" class="btn btn-light w-100">
                            <i class="fas fa-paper-plane me-2"></i> Send Message
                        </a>
                    </div>
                </div>
                
                <!-- Similar Properties -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-clone me-2"></i> Similar Properties</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php
                        // Fetch similar subcategories (same category, different id)
                        $sql = "SELECT id, name, photo FROM subcategories 
                                WHERE parent_category_id = ? AND id != ? 
                                ORDER BY RAND() LIMIT 3";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("ii", $subcategory['category_id'], $subcategory_id);
                        $stmt->execute();
                        $similar = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                        
                        if (count($similar) > 0):
                            foreach($similar as $item):
                                $itemPhoto = formatImagePath($item['photo']);
                        ?>
                           
                                <div class="d-flex align-items-center p-3 border-bottom">
                                    <div class="flex-shrink-0" style="width: 70px; height: 70px; overflow: hidden; border-radius: 8px;">
                                    <img src="./<?php echo htmlspecialchars($subcategory['photo']); ?>" 
                                 alt="<?php echo htmlspecialchars($subcategory['name']); ?>" 
                                 class="subcategory-image"
                                             alt="<?php echo htmlspecialchars($item['name']); ?>"
                                             style="width: 100%; height: 100%; object-fit: cover;"
                                             onerror="this.onerror=null; this.src='assets/images/default-property.jpg';">
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="mb-0"><?php echo htmlspecialchars($item['name']); ?></h6>
                                        <small class="text-muted">View details</small>
                                    </div>
                                </div>
                           
                        <?php 
                            endforeach;
                        else:
                        ?>
                            <div class="p-4 text-center">
                                <p class="text-muted mb-0">No similar properties found</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Back button -->
                <div class="text-center mt-4">
                    <a href="category.php?id=<?php echo $subcategory['category_id']; ?>" class="btn btn-back">
                        <i class="fas fa-arrow-left me-2"></i> Back to <?php echo htmlspecialchars($subcategory['category_name']); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Image Modal -->
    <div id="imageModal" class="image-modal">
        <span class="close" onclick="closeModal()">&times;</span>
        <img class="modal-content" id="modalImage">
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Animation for cards when they come into view
        document.addEventListener('DOMContentLoaded', function() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                    }
                });
            }, { threshold: 0.1 });
            
            document.querySelectorAll('.custom-field-item, .card').forEach(item => {
                observer.observe(item);
            });
            
            // Add error handlers for all images
            document.querySelectorAll('img').forEach(img => {
                img.addEventListener('error', function() {
                    console.log('Image failed to load: ' + this.src);
                    // Fallback already handled via onerror attribute
                });
            });
        });
        
        // Image modal functionality
        function openModal(imgSrc) {
            document.getElementById('imageModal').style.display = 'block';
            document.getElementById('modalImage').src = imgSrc;
        }
        
        function closeModal() {
            document.getElementById('imageModal').style.display = 'none';
        }
        
        // Close modal when clicking outside the image
        window.onclick = function(event) {
            const modal = document.getElementById('imageModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>