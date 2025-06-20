<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug session
error_log("Session status: " . session_status());
error_log("Session ID: " . session_id());
error_log("User ID: " . ($_SESSION['user_id'] ?? 'not set'));

// If not logged in, redirect to login

?>
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);


$current_datetime = '2025-05-28 04:20:23';
$current_user = 'Kalpesh-Sananse';

include('./includes/db_connection.php');

// Get category ID from URL with validation
$category_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$category_id) {
    header("Location: categories.php");
    exit();
}

// Fetch category with prepared statement
try {
    $sql = "SELECT * FROM categories WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $category = $stmt->get_result()->fetch_assoc();

    if (!$category) {
        throw new Exception("Category not found");
    }

    // Fetch subcategories with saved status if user is logged in
    $user_id = $_SESSION['user_id'] ?? 0;
    $sql = "SELECT s.*, 
            CASE WHEN sp.id IS NOT NULL THEN 1 ELSE 0 END as is_saved
            FROM subcategories s 
            LEFT JOIN saved_properties sp ON s.id = sp.subcategory_id AND sp.user_id = ?
            WHERE s.parent_category_id = ?
            ORDER BY s.name ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $category_id);
    $stmt->execute();
    $subcategories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

} catch (Exception $e) {
    error_log("Error in getsubcategories.php: " . $e->getMessage());
    header("Location: error.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($category['name']); ?> Properties - Varaha City</title>

    <!-- Favicon -->
    <link rel="icon" href="./images/favicon.ico" type="image/x-icon">

    <!-- CSS Dependencies -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
    :root {
        --primary-color: #4D77FF;
        --secondary-color: #22c55e;
        --dark-color: #1a1a1a;
        --light-color: #f8f9fa;
        --transition: all 0.3s ease;
    }

    body {
        font-family: 'Poppins', sans-serif;
        background-color: var(--light-color);

    }

    .page-header {
        background: linear-gradient(135deg, #0A142F 0%, #1a2642 100%);
        color: white;
        padding: 40px 0;
        margin-bottom: 40px;
        text-align: center;
    }

    .subcategory-count {
        display: inline-block;
        padding: 8px 20px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 30px;
        font-size: 14px;
        margin-top: 10px;
    }

    .card-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 25px;
        padding: 20px;
    }

    .property-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        transition: var(--transition);
        height: 100%;
        display: flex;
        flex-direction: column;
        opacity: 0;
        animation: fadeInUp 0.6s ease forwards;
    }

    .property-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
    }

    .property-image {
        position: relative;
        padding-top: 66.67%;
        /* 3:2 Aspect Ratio */
        overflow: hidden;
    }

    .property-image img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: var(--transition);
    }

    .property-card:hover .property-image img {
        transform: scale(1.1);
    }

    .property-content {
        padding: 20px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    .property-title {
        font-size: 28px;
        font-weight: 600;
        color: var(--dark-color);
        margin-bottom: 15px;
    }

    .property-badges {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 15px;
    }

    .badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }

    .badge-buy {
        background-color: var(--primary-color);
        color: white;
    }

    .badge-rent {
        background-color: var(--secondary-color);
        color: white;
    }

    .property-actions {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 10px;
        margin-top: auto;
        
  
    }

    /* Update these badge classes in your existing CSS */
    .badge-sale {
        /* Changed from badge-buy */
        background-color: var(--primary-color);
        color: white;
    }

    .badge-rent {
        background-color: var(--secondary-color);
        color: white;
    }

    /* Add specific styling for badges */
    .badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .btn-view,
    .btn-save {
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 500;
        transition: var(--transition);
        text-align: center;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-view {
        background-color: var(--primary-color);
        color: white;
    }

    .btn-save {
        background-color: var(--secondary-color);
        color: white;
        border: none;
        cursor: pointer;
    }

    .btn-saved {
        background-color: #dc3545;
    }

    .toast-container {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1050;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (max-width: 768px) {
        .page-header {
            padding: 30px 0;
        }

        .card-grid {
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
            padding: 15px;
        }

        .property-title {
            font-size: 26px;
        }

        .property-actions {
            grid-template-columns: 1fr;
        }
    }
    </style>
</head>

<body>
    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1 class="h2 mb-2"><?php echo htmlspecialchars($category['name']); ?></h1>
            <div class="subcategory-count">
                <i class="fas fa-tags me-2"></i><?php echo count($subcategories); ?> Categories Available
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <?php if (!empty($subcategories)): ?>
        <div class="card-grid">
            <?php foreach($subcategories as $index => $subcategory): ?>

                <div class="property-card" style="animation-delay: <?php echo $index * 0.1; ?>s;" onclick="window.location.href='product-listing.php?id=<?php echo $subcategory['id']; ?>'">
    <div class="property-image">
        <img src="./<?php echo htmlspecialchars($subcategory['photo']); ?>"
            alt="<?php echo htmlspecialchars($subcategory['name']); ?>"
            onerror="this.src='./images/placeholder.jpg'">
    </div>
    <div class="property-content">
        <h3 class="property-title"><?php echo htmlspecialchars($subcategory['name']); ?></h3>
        <div class="property-actions" onclick="event.stopPropagation();">
            
            <?php if(isset($_SESSION['user_id'])): ?>
          
            <?php else: ?>
           
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add this CSS to your existing stylesheet -->
<style>
.property-card {
    cursor: pointer;
    transition: transform 0.2s ease;
}

.property-card:hover {
    transform: translateY(-5px);
}

.property-actions {
    position: relative;
    z-index: 2;
}
</style>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-folder-open fa-3x mb-3 text-muted"></i>
            <h3>No Properties Found</h3>
            <p class="text-muted">No properties are available in this category at the moment.</p>
        </div>
        <?php endif; ?>
    </div>

    <?php
// First, get the Google Form link from database
function getContactFormLink($conn) {
    $sql = "SELECT form_link FROM contact_form_links WHERE is_active = 1 ORDER BY id DESC LIMIT 1";
    $result = $conn->query($sql);
    return $result->num_rows > 0 ? $result->fetch_assoc()['form_link'] : '#';
}

$form_link = getContactFormLink($conn);
?>

    <!-- Add this CSS to your stylesheet or in a style tag -->
    <style>
    .contact-section {
        background: linear-gradient(135deg, rgba(10, 20, 47, 0.95), rgba(26, 38, 66, 0.95));
        padding: 50px 20px;
        text-align: center;
        margin: 40px 0;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .contact-section-inner {
        max-width: 800px;
        margin: 0 auto;
    }

    .contact-heading {
        color: #fff;
        font-size: 2.2rem;
        margin-bottom: 20px;
        font-weight: 600;
        line-height: 1.3;
    }

    .contact-subtext {
        color: rgba(255, 255, 255, 0.9);
        font-size: 1.1rem;
        margin-bottom: 30px;
        line-height: 1.6;
    }

    .contact-btn {
        display: inline-block;
        padding: 15px 40px;
        background: linear-gradient(135deg, #4D77FF, #38B6FF);
        color: white;
        text-decoration: none;
        border-radius: 50px;
        font-weight: 600;
        font-size: 1.1rem;
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 4px 15px rgba(77, 119, 255, 0.3);
    }

    .contact-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(77, 119, 255, 0.4);
        color: white;
    }

    .contact-btn i {
        margin-left: 8px;
    }

    /* Animation for the section */
    .contact-section {
        animation: fadeInUp 0.6s ease-out;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Responsive Styles */
    @media (max-width: 768px) {
        .contact-section {
            padding: 40px 15px;
            margin: 30px 0;
        }

        .contact-heading {
            font-size: 1.8rem;
        }

        .contact-subtext {
            font-size: 1rem;
        }

        .contact-btn {
            padding: 12px 30px;
            font-size: 1rem;
        }
    }

    @media (max-width: 480px) {
        .contact-heading {
            font-size: 1.5rem;
        }

        .contact-section {
            padding: 30px 15px;
        }
    }
    </style>

 

    <!-- Make sure you have Bootstrap Icons included -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">

    <!-- Toast Container -->
    <div class="toast-container"></div>

    <!-- JavaScript Dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast show bg-${type} text-white`;
        toast.innerHTML = `
            <div class="toast-body d-flex align-items-center">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                ${message}
            </div>
        `;
        document.querySelector('.toast-container').appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }

    function saveProperty(subcategoryId, button) {
        // Disable button and show loading state
        button.disabled = true;
        const originalContent = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        // Make the request
        fetch('save-property.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    subcategory_id: subcategoryId
                }),
                credentials: 'same-origin'
            })
            .then(async response => {
                const responseText = await response.text();

                // Log the raw response for debugging
                console.log('Raw server response:', responseText);

                try {
                    return JSON.parse(responseText);
                } catch (e) {
                    console.error('Failed to parse response:', responseText);
                    throw new Error('Invalid response format');
                }
            })
            .then(data => {
                console.log('Processed response:', data);

                if (data.success) {
                    const isSaved = button.classList.contains('btn-saved');
                    button.classList.toggle('btn-saved');

                    button.innerHTML = `
                <i class="fas fa-${!isSaved ? 'heart-broken' : 'heart'}"></i>
                <span class="d-none d-sm-inline">${!isSaved ? 'Saved' : 'Save'}</span>
            `;

                    showToast(data.message, 'success');
                } else {
                    button.innerHTML = originalContent;
                    showToast(data.message || 'Failed to save property', 'danger');
                }
            })
            .catch(error => {
                console.error('Save operation failed:', error);
                button.innerHTML = originalContent;
                showToast('Error: ' + error.message, 'danger');
            })
            .finally(() => {
                button.disabled = false;
            });
    }

    function showToast(message, type = 'success') {
        const container = document.querySelector('.toast-container') || (() => {
            const cont = document.createElement('div');
            cont.className = 'toast-container';
            document.body.appendChild(cont);
            return cont;
        })();

        const toast = document.createElement('div');
        toast.className = `toast show bg-${type} text-white`;
        toast.innerHTML = `
        <div class="toast-body d-flex align-items-center">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
            ${message}
        </div>
    `;

        container.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }


    function createToastContainer() {
        const container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
        return container;
    }

    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast show bg-${type} text-white`;
        toast.style.opacity = '1';
        toast.innerHTML = `
        <div class="toast-body d-flex align-items-center">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
            ${message}
        </div>
    `;

        const container = document.querySelector('.toast-container');
        container.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
    </script>
</body>

</html>