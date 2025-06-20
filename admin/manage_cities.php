<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include('../includes/db_connection.php');

// Create cities table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS cities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_city'])) {
        $name = trim($_POST['name']);
        if (!empty($name)) {
            $stmt = $conn->prepare("INSERT INTO cities (name) VALUES (?)");
            $stmt->bind_param("s", $name);
            $stmt->execute();
        }
    } elseif (isset($_POST['delete_city'])) {
        $id = intval($_POST['city_id']);
        $stmt = $conn->prepare("DELETE FROM cities WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
}

// Get all cities
$cities = $conn->query("SELECT * FROM cities ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Cities</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container py-4">
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i> Add New City</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">City Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <button type="submit" name="add_city" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> Add City
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-city me-2"></i> City List</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($cities->num_rows > 0): ?>
                        <div class="list-group">
                            <?php while($city = $cities->fetch_assoc()): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <?php echo htmlspecialchars($city['name']); ?>
                                <form method="POST">
                                    <input type="hidden" name="city_id" value="<?php echo $city['id']; ?>">
                                    <button type="submit" name="delete_city" class="btn btn-sm btn-danger" 
                                        onclick="return confirm('Delete this city?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                            <?php endwhile; ?>
                        </div>
                        <?php else: ?>
                        <p class="text-muted">No cities added yet</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>