<?php
session_start();
require_once '../includes/db_connection.php';

// Set defaults
date_default_timezone_set('UTC');
$current_datetime = date('Y-m-d H:i:s');
$current_user = 'Kalpesh-Sananse';

// Initialize messages
$success_msg = $error_msg = '';

// Check if we're in edit mode
$edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;

// Fetch all contacts for the list
$all_contacts = [];
$list_query = "SELECT * FROM contact_details ORDER BY created_at DESC";
$list_result = $conn->query($list_query);
while($row = $list_result->fetch_assoc()) {
    $all_contacts[] = $row;
}

// If in edit mode, fetch the specific contact
if($edit_id > 0) {
    $edit_query = "SELECT * FROM contact_details WHERE id = ?";
    $stmt = $conn->prepare($edit_query);
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $contact = $result->fetch_assoc();
    $stmt->close();
} else {
    $contact = null;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fields = [
        'email', 'phone', 'address', 'whatsapp', 'availability',
        'facebook', 'instagram', 'linkedin', 'twitter', 'maps_link'
    ];
    
    $data = [];
    foreach ($fields as $field) {
        $data[$field] = isset($_POST[$field]) ? trim($conn->real_escape_string($_POST[$field])) : '';
    }

    // Validate required fields
    $required_fields = ['email', 'phone', 'address'];
    $validation_errors = [];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            $validation_errors[] = ucfirst($field) . " is required";
        }
    }

    if (empty($validation_errors)) {
        if (isset($_POST['edit_id']) && $_POST['edit_id'] > 0) {
            // Update existing record
            $stmt = $conn->prepare("UPDATE contact_details SET 
            email = ?, phone = ?, address = ?, whatsapp = ?, availability = ?,
            facebook = ?, instagram = ?, linkedin = ?, twitter = ?,
            maps_link = ?, updated_at = ?, updated_by = ?
            WHERE id = ?");
            $edit_id = (int)$_POST['edit_id'];
            $stmt->bind_param("ssssssssssssi", 
            $data['email'], $data['phone'], $data['address'], $data['whatsapp'], $data['availability'],
            $data['facebook'], $data['instagram'], $data['linkedin'], $data['twitter'],
            $data['maps_link'], $current_datetime, $current_user, $edit_id);
        } else {
            // Insert new record
            $stmt = $conn->prepare("INSERT INTO contact_details 
            (email, phone, address, whatsapp, availability, facebook, instagram, linkedin, twitter, 
            maps_link, created_at, created_by, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')");
           $stmt->bind_param("ssssssssssss", 
           $data['email'], $data['phone'], $data['address'], $data['whatsapp'], $data['availability'],
           $data['facebook'], $data['instagram'], $data['linkedin'], $data['twitter'],
           $data['maps_link'], $current_datetime, $current_user);
       
        }

        if ($stmt->execute()) {
            $success_msg = "Contact details " . (isset($_POST['edit_id']) ? "updated" : "added") . " successfully!";
            header("Location: contactus.php");
            exit;
        } else {
            $error_msg = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error_msg = "Please correct the following errors:<br>" . implode("<br>", $validation_errors);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Contact Details - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .form-section {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .table-section {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .form-label { font-weight: 500; }
        .social-inputs .input-group-text {
            background-color: #f8f9fa;
            width: 45px;
            justify-content: center;
        }
        .badge {
            padding: 8px 12px;
            border-radius: 20px;
        }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
    <div class="container my-5">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><?php echo $edit_id ? 'Edit' : 'Add New'; ?> Contact Details</h2>
                <p class="text-muted">
                    <?php echo $edit_id ? 'Update existing contact information' : 'Add new contact information'; ?>
                </p>
            </div>
            <?php if($edit_id): ?>
          
            <?php endif; ?>
        </div>

        <!-- Alerts -->
        <?php if($success_msg): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle me-2"></i>
                <?php echo $success_msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if($error_msg): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?php echo $error_msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Existing Contacts Table -->
        <div class="table-section">
            <h5 class="mb-4">
                <i class="bi bi-list-ul me-2"></i>
                Existing Contacts
            </h5>
            <div class="table-responsive">
                <table class="table table-hover" id="contactsTable">
                    <!-- In the table header (around line 120) -->
<thead>
    <tr>
        <th>Email</th>
        <th>Phone</th>
        <th>Availability</th>
        <th>Status</th>
        <th>Created Date</th>
        <th>Actions</th>
    </tr>
</thead>

<!-- In the table body (around line 130) -->
<tbody>
    <?php foreach($all_contacts as $item): ?>
        <tr>
            <td><?php echo htmlspecialchars($item['email']); ?></td>
            <td><?php echo htmlspecialchars($item['phone']); ?></td>
            <td><?php echo htmlspecialchars($item['availability']); ?></td>
            <td>
                <span class="badge bg-<?php echo $item['status'] == 'active' ? 'success' : 'secondary'; ?>">
                    <?php echo ucfirst($item['status']); ?>
                </span>
            </td>
            <td><?php echo date('Y-m-d H:i', strtotime($item['created_at'])); ?></td>
            <td>
                <a href="?edit=<?php echo $item['id']; ?>" class="btn btn-sm btn-primary">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
</tbody>
                </table>
            </div>
        </div>

        <!-- Contact Form -->
        <div class="form-section">
            <form method="POST">
                <?php if($edit_id): ?>
                    <input type="hidden" name="edit_id" value="<?php echo $edit_id; ?>">
                <?php endif; ?>

                <!-- Basic Contact Information -->
                <h5 class="mb-4">
                    <i class="bi bi-person-lines-fill me-2"></i>
                    Contact Information
                </h5>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="email" class="form-control" required
                                       value="<?php echo $contact ? htmlspecialchars($contact['email']) : ''; ?>">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                <input type="tel" name="phone" class="form-control" required
                                       value="<?php echo $contact ? htmlspecialchars($contact['phone']) : ''; ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                        <textarea name="address" class="form-control" rows="3" required><?php 
                            echo $contact ? htmlspecialchars($contact['address']) : ''; 
                        ?></textarea>
                    </div>
                </div>
                <div class="row mb-4">
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label">Availability</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-clock"></i></span>
                <input type="text" name="availability" class="form-control" 
                       placeholder="Mon-Fri, 9am-5pm" 
                       value="<?php echo $contact ? htmlspecialchars($contact['availability']) : ''; ?>">
            </div>
            <div class="form-text">Example: Mon-Fri, 9am-5pm</div>
        </div>
    </div>
</div>

                <div class="mb-4">
                    <label class="form-label">Google Maps Embed URL</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-map"></i></span>
                        <input type="text" name="maps_link" class="form-control" 
                               placeholder="https://www.google.com/maps/embed?pb=..." 
                               value="<?php echo $contact ? htmlspecialchars($contact['maps_link']) : ''; ?>">
                        <button class="btn btn-outline-secondary" type="button" 
                                data-bs-toggle="modal" data-bs-target="#mapsHelpModal">
                            <i class="bi bi-question-circle"></i>
                        </button>
                    </div>
                    <div class="form-text">Enter the Google Maps embed URL for your location</div>
                </div>

                <!-- Social Media Links -->
                <h5 class="mb-4">
                    <i class="bi bi-share me-2"></i>
                    Social Media Links
                </h5>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">WhatsApp</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-whatsapp"></i></span>
                            <input type="tel" name="whatsapp" class="form-control"
                                   value="<?php echo $contact ? htmlspecialchars($contact['whatsapp']) : ''; ?>">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Facebook</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-facebook"></i></span>
                            <input type="url" name="facebook" class="form-control"
                                   value="<?php echo $contact ? htmlspecialchars($contact['facebook']) : ''; ?>">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Instagram</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-instagram"></i></span>
                            <input type="url" name="instagram" class="form-control"
                                   value="<?php echo $contact ? htmlspecialchars($contact['instagram']) : ''; ?>">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">LinkedIn</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-linkedin"></i></span>
                            <input type="url" name="linkedin" class="form-control"
                                   value="<?php echo $contact ? htmlspecialchars($contact['linkedin']) : ''; ?>">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Twitter</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-twitter"></i></span>
                            <input type="url" name="twitter" class="form-control"
                                   value="<?php echo $contact ? htmlspecialchars($contact['twitter']) : ''; ?>">
                        </div>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <?php if($edit_id): ?>
                        <a href="manage-contact.php" class="btn btn-secondary me-2">Cancel</a>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>
                        <?php echo $edit_id ? 'Update' : 'Save'; ?> Contact Details
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Maps Help Modal -->
    <div class="modal fade" id="mapsHelpModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">How to Get Google Maps Embed URL</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <ol>
                        <li>Go to <a href="https://maps.google.com" target="_blank">Google Maps</a></li>
                        <li>Search your location</li>
                        <li>Click "Share"</li>
                        <li>Select "Embed a map"</li>
                        <li>Copy the URL from the iframe src attribute (should start with <code>https://www.google.com/maps/embed?pb=</code>)</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>