<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include('./includes/db_connection.php');

// Validate product ID
$product_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$product_id) {
    header("Location: index.php");
    exit();
}

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
$is_in_wishlist = false;

// Check if product is in user's wishlist
if ($is_logged_in) {
    $sql = "SELECT id FROM user_wishlist WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $_SESSION['user_id'], $product_id);
    $stmt->execute();
    $is_in_wishlist = $stmt->get_result()->num_rows > 0;
}

// Handle wishlist toggle
if ($is_logged_in && isset($_POST['wishlist_toggle'])) {
    if ($is_in_wishlist) {
        // Remove from wishlist
        $sql = "DELETE FROM user_wishlist WHERE user_id = ? AND product_id = ?";
    } else {
        // Add to wishlist
        $sql = "INSERT INTO user_wishlist (user_id, product_id) VALUES (?, ?)";
    }
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $_SESSION['user_id'], $product_id);
    $stmt->execute();
    
    // Refresh the page to update the wishlist status
    header("Location: product_details.php?id=" . $product_id);
    exit();
}

// Fetch contact details
$sql = "SELECT * FROM product_contact_details WHERE product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$contact_details = $stmt->get_result()->fetch_assoc();

// Handle contact details update
if (isset($_POST['update_contact'])) {
    $contact_name = $_POST['contact_name'];
    $phone = $_POST['phone'];
    $whatsapp = $_POST['whatsapp'];
    $telephone = $_POST['telephone'];
    $email = $_POST['email'];
    $contact_hours = $_POST['contact_hours'];
    $current_timestamp = date('Y-m-d H:i:s');
    
    if ($contact_details) {
        // Update existing contact details
        $sql = "UPDATE product_contact_details 
                SET contact_name = ?, phone = ?, whatsapp = ?, 
                    telephone = ?, email = ?, contact_hours = ?,
                    updated_at = ?
                WHERE product_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssi", $contact_name, $phone, $whatsapp, 
                         $telephone, $email, $contact_hours,
                         $current_timestamp, $product_id);
    } else {
        // Insert new contact details
        $sql = "INSERT INTO product_contact_details 
                (product_id, contact_name, phone, whatsapp, telephone, email, contact_hours) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssss", $product_id, $contact_name, $phone, 
                         $whatsapp, $telephone, $email, $contact_hours);
    }
    
    if ($stmt->execute()) {
        header("Location: product_details.php?id=" . $product_id);
        exit();
    }
}

// Fetch product details
$sql = "SELECT p.*, 
        c.name as category_name, 
        s.name as subcategory_name,
        pc.contact_name, pc.phone, pc.whatsapp, pc.telephone, pc.email, pc.contact_hours
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN subcategories s ON p.subcategory_id = s.id
        LEFT JOIN product_contact_details pc ON p.id = pc.product_id
        WHERE p.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    header("Location: index.php");
    exit();
}

// Fetch custom fields
$sql = "SELECT cf.id, cf.title, GROUP_CONCAT(cfv.value SEPARATOR '|') as values_list
        FROM product_custom_fields cf
        LEFT JOIN product_custom_field_values cfv ON cf.id = cfv.field_id
        WHERE cf.product_id = ?
        GROUP BY cf.id";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$custom_fields = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch additional images
$sql = "SELECT image_path FROM product_images WHERE product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$additional_images = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Head content remains exactly the same as before -->
    <!-- ... -->
</head>
<body>
    <div class="container py-5">
        <!-- Product Header Section -->
        <div class="product-header p-4 fade-in">
            <div class="row">
                <div class="col-lg-8">
                    <!-- Left column content remains exactly the same -->
                    <!-- ... -->
                </div>
                
                <div class="col-lg-4">
                    <!-- Right column content remains exactly the same -->
                    <!-- ... -->
                    
                    <!-- Enhanced Contact Agent Section -->
                    <div class="contact-card fade-in">
                        <div class="contact-header">
                            <div class="contact-avatar">
                                <?php 
                                $initials = '';
                                if (!empty($product['contact_name'])) {
                                    $names = explode(' ', $product['contact_name']);
                                    foreach ($names as $name) {
                                        $initials .= strtoupper(substr($name, 0, 1));
                                    }
                                    $initials = substr($initials, 0, 2);
                                } else {
                                    $initials = 'AG';
                                }
                                echo $initials;
                                ?>
                            </div>
                            <div>
                                <div class="contact-name">
                                    <?php echo !empty($product['contact_name']) ? htmlspecialchars($product['contact_name']) : 'Contact Agent'; ?>
                                </div>
                                <div class="contact-role">Property Specialist</div>
                            </div>
                        </div>
                        
                        <div class="contact-methods">
                            <?php if (!empty($product['phone'])): ?>
                            <div class="contact-method">
                                <i class="fas fa-phone"></i>
                                <div>
                                    <div class="method-label">Phone</div>
                                    <a href="tel:<?php echo htmlspecialchars($product['phone']); ?>" class="method-value">
                                        <?php echo htmlspecialchars($product['phone']); ?>
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($product['whatsapp'])): ?>
                            <div class="contact-method">
                                <i class="fab fa-whatsapp"></i>
                                <div>
                                    <div class="method-label">WhatsApp</div>
                                    <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $product['whatsapp']); ?>" target="_blank" class="method-value">
                                        <?php echo htmlspecialchars($product['whatsapp']); ?>
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($product['telephone'])): ?>
                            <div class="contact-method">
                                <i class="fas fa-phone-alt"></i>
                                <div>
                                    <div class="method-label">Telephone</div>
                                    <a href="tel:<?php echo htmlspecialchars($product['telephone']); ?>" class="method-value">
                                        <?php echo htmlspecialchars($product['telephone']); ?>
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($product['email'])): ?>
                            <div class="contact-method">
                                <i class="fas fa-envelope"></i>
                                <div>
                                    <div class="method-label">Email</div>
                                    <a href="mailto:<?php echo htmlspecialchars($product['email']); ?>" class="method-value">
                                        <?php echo htmlspecialchars($product['email']); ?>
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($product['contact_hours'])): ?>
                            <div class="contact-method">
                                <i class="fas fa-clock"></i>
                                <div>
                                    <div class="method-label">Available Hours</div>
                                    <div class="method-value"><?php echo htmlspecialchars($product['contact_hours']); ?></div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($product['whatsapp'])): ?>
                        <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $product['whatsapp']); ?>" target="_blank" class="whatsapp-btn mt-3">
                            <i class="fab fa-whatsapp me-2"></i> Start WhatsApp Chat
                        </a>
                        <?php endif; ?>
                        
                        <!-- Add Contact Update Form for Admin -->
                        <?php if ($is_logged_in && $_SESSION['user_role'] === 'admin'): ?>
                        <button class="btn btn-sm btn-outline-primary w-100 mt-3" data-bs-toggle="modal" data-bs-target="#editContactModal">
                            <i class="fas fa-edit me-2"></i> Edit Contact Details
                        </button>
                        
                        <!-- Edit Contact Modal -->
                        <div class="modal fade" id="editContactModal" tabindex="-1" aria-labelledby="editContactModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editContactModalLabel">Edit Contact Details</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form method="POST">
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label for="contact_name" class="form-label">Contact Name</label>
                                                <input type="text" class="form-control" id="contact_name" name="contact_name" 
                                                       value="<?php echo htmlspecialchars($product['contact_name'] ?? ''); ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label for="phone" class="form-label">Mobile Number</label>
                                                <input type="tel" class="form-control" id="phone" name="phone" 
                                                       value="<?php echo htmlspecialchars($product['phone'] ?? ''); ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label for="whatsapp" class="form-label">WhatsApp Number</label>
                                                <input type="tel" class="form-control" id="whatsapp" name="whatsapp" 
                                                       value="<?php echo htmlspecialchars($product['whatsapp'] ?? ''); ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label for="telephone" class="form-label">Telephone</label>
                                                <input type="tel" class="form-control" id="telephone" name="telephone" 
                                                       value="<?php echo htmlspecialchars($product['telephone'] ?? ''); ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label for="email" class="form-label">Email</label>
                                                <input type="email" class="form-control" id="email" name="email" 
                                                       value="<?php echo htmlspecialchars($product['email'] ?? ''); ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label for="contact_hours" class="form-label">Contact Hours</label>
                                                <input type="text" class="form-control" id="contact_hours" name="contact_hours" 
                                                       value="<?php echo htmlspecialchars($product['contact_hours'] ?? ''); ?>">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" name="update_contact" class="btn btn-primary">Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- JavaScript remains exactly the same -->
    <!-- ... -->
</body>
</html>