<?php
session_start();
include('./includes/db_connection.php');

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Please login to view your wishlist";
    header("Location: login.php");
    exit();
}

// Get wishlist items with all necessary product details
$user_id = $_SESSION['user_id'];
$sql = "SELECT p.*, c.name as category_name, s.name as subcategory_name, w.created_at as saved_date 
        FROM products p 
        JOIN wishlist w ON p.id = w.product_id 
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN subcategories s ON p.subcategory_id = s.id
        WHERE w.user_id = ? 
        ORDER BY w.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$wishlist_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .card-img-top {
            height: 200px;
            object-fit: cover;
        }
        .property-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .badge-primary { background-color: #0d6efd; }
        .badge-success { background-color: #198754; }
        .price-tag {
            font-weight: bold;
            color: #0d6efd;
            font-size: 1.1rem;
        }
        .empty-wishlist {
            min-height: 50vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
    </style>
</head>
<body>
  

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-heart text-danger me-2"></i> My Wishlist</h2>
            <span class="badge bg-primary"><?php echo count($wishlist_items); ?> items</span>
        </div>
        
        <?php if (empty($wishlist_items)): ?>
            <div class="empty-wishlist text-center py-5">
                <i class="fas fa-heart-broken text-muted fa-4x mb-4"></i>
                <h3 class="mb-3">Your wishlist is empty</h3>
                <p class="text-muted mb-4">You haven't saved any properties yet. Start exploring!</p>
                <a href="index.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-search me-2"></i>Browse Properties
                </a>
            </div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($wishlist_items as $item): ?>
                    <div class="col">
                        <div class="card h-100">
                            <!-- Property Image with Badge -->
                            <div class="position-relative">
                                <img src="./<?php echo htmlspecialchars($item['image']); ?>" 
                                     class="card-img-top" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>"
                                     onerror="this.src='./images/placeholder.jpg'">
                                <?php if ($item['property_status'] == 'sold'): ?>
                                    <span class="property-badge bg-danger">Sold Out</span>
                                <?php elseif ($item['property_status'] == 'available'): ?>
                                    <span class="property-badge bg-success">Available</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="card-body">
                                <!-- Property Title and Price -->
                                <h5 class="card-title"><?php echo htmlspecialchars($item['name']); ?></h5>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="price-tag">₹<?php echo number_format($item['price']); ?></span>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        <?php echo date('M j, Y', strtotime($item['saved_date'])); ?>
                                    </small>
                                </div>
                                
                                <!-- Property Location -->
                                <p class="card-text text-muted small">
                                    <i class="fas fa-map-marker-alt text-danger me-1"></i>
                                    <?php echo htmlspecialchars($item['city']); ?>
                                </p>
                                
                                <!-- Property Features -->
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    <?php if (!empty($item['area'])): ?>
                                        <span class="badge bg-light text-dark">
                                            <i class="fas fa-ruler-combined me-1"></i>
                                            <?php echo htmlspecialchars($item['area']); ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($item['category_name'])): ?>
                                        <span class="badge bg-light text-dark">
                                            <i class="fas fa-tag me-1"></i>
                                            <?php echo htmlspecialchars($item['category_name']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Action Buttons -->
                                <div class="d-flex justify-content-between mt-auto">
                                    <a href="product-details.php?id=<?php echo $item['id']; ?>" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye me-1"></i> View
                                    </a>
                                    <button onclick="removeFromWishlist(<?php echo $item['id']; ?>)" 
                                            class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash me-1"></i> Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function removeFromWishlist(productId) {
        if (confirm('Are you sure you want to remove this property from your wishlist?')) {
            fetch('remove_from_wishlist.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'product_id=' + productId
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Refresh the page to show updated wishlist
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to remove item'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while removing the item');
            });
        }
    }
    </script>
</body>
</html>