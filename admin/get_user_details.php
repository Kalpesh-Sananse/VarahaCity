<?php
session_start();
include('../includes/db_connection.php');

if (!isset($_SESSION['admin']) || !isset($_POST['user_id'])) {
    exit('Unauthorized access');
}

$user_id = intval($_POST['user_id']);

// Get user details
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get custom fields
$fields_sql = "SELECT * FROM user_custom_fields WHERE user_id = ?";
$fields_stmt = $conn->prepare($fields_sql);
$fields_stmt->bind_param("i", $user_id);
$fields_stmt->execute();
$custom_fields = $fields_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div class="modal-content">
    <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">
            <i class="bi bi-person-circle me-2"></i>User Information
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body p-4">
        <!-- Basic Information Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-bold">Basic Information</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="info-group">
                            <label class="text-muted">Name</label>
                            <p class="mb-3 fs-6"><?php echo htmlspecialchars($user['name']); ?></p>
                        </div>
                        <div class="info-group">
                            <label class="text-muted">Email</label>
                            <p class="mb-3 fs-6"><?php echo htmlspecialchars($user['email']); ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-group">
                            <label class="text-muted">Username</label>
                            <p class="mb-3 fs-6"><?php echo htmlspecialchars($user['username']); ?></p>
                        </div>
                        <div class="info-group">
                            <label class="text-muted">Contact</label>
                            <p class="mb-3 fs-6"><?php echo htmlspecialchars($user['contact_no']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Information Card -->
        <div class="card shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">Additional Information</h6>
            </div>
            <div class="card-body">
                <!-- Add New Field Form -->
                <form id="addFieldForm" class="mb-4">
                    <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <input type="text" class="form-control" name="field_key" placeholder="Field Name" required>
                        </div>
                        <div class="col-md-5">
                            <input type="text" class="form-control" name="field_value" placeholder="Field Value" required>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-plus-lg"></i> Add
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Custom Fields Table -->
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Field</th>
                                <th>Value</th>
                                <th class="text-center" style="width: 100px">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($custom_fields) > 0): ?>
                                <?php foreach($custom_fields as $field): ?>
                                <tr>
                                    <td class="align-middle"><?php echo htmlspecialchars($field['field_key']); ?></td>
                                    <td class="align-middle"><?php echo htmlspecialchars($field['field_value']); ?></td>
                                    <td class="text-center">
                                        <button class="btn btn-danger btn-sm" onclick="deleteField(<?php echo $field['id']; ?>, <?php echo $user_id; ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">
                                        <i class="bi bi-info-circle me-2"></i>No additional information added yet
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.modal-content {
    border: none;
    border-radius: 12px;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.modal-header {
    border-radius: 12px 12px 0 0;
    background: linear-gradient(135deg, #2980b9, #6dd5fa);
}

.card {
    border: none;
    border-radius: 10px;
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08);
}

.card-header {
    border-bottom: 1px solid rgba(0, 0, 0, 0.08);
    padding: 1rem 1.25rem;
}

.info-group label {
    font-size: 0.875rem;
    margin-bottom: 0.25rem;
    color : white;
}

.info-group p {
    font-weight: 500;
    color: #2c3e50;
}

.table {
    margin-bottom: 0;
}

.table thead th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.875rem;
    color: #2c3e50;
}

.btn {
    padding: 0.5rem 1rem;
    font-weight: 500;
    border-radius: 6px;
}

.btn-primary {
    background: linear-gradient(135deg, #2980b9, #6dd5fa);
        border: none;
}

.btn-danger {
    background: linear-gradient(135deg, #dc3545, #c82333);
    border: none;
}

.form-control {
    padding: 0.65rem 1rem;
    border-radius: 6px;
    border: 1px solid #dee2e6;
}

.form-control:focus {
    border-color: #0A142F;
    box-shadow: 0 0 0 0.2rem rgba(10, 20, 47, 0.15);
}
</style>

<script>
$(document).ready(function() {
    $('#addFieldForm').on('submit', function(e) {
        e.preventDefault();
        
        let formData = {
            user_id: $('input[name="user_id"]').val(),
            field_key: $('input[name="field_key"]').val(),
            field_value: $('input[name="field_value"]').val()
        };

        $.ajax({
            url: 'add_user_info.php',
            type: 'POST',
            data: formData,
            success: function(response) {
                if(response.trim() === 'success') {
                    // Clear form
                    $('input[name="field_key"]').val('');
                    $('input[name="field_value"]').val('');
                    
                    // Refresh the modal content
                    viewUser(<?php echo $user_id; ?>);
                } else {
                    alert('Error adding field. Please try again.');
                }
            },
            error: function(xhr, status, error) {
                alert('Error occurred. Please try again.');
            }
        });
    });
});

function deleteField(fieldId, userId) {
    if(confirm('Are you sure you want to delete this field?')) {
        $.ajax({
            url: 'delete_user_info.php',
            type: 'POST',
            data: { field_id: fieldId },
            success: function(response) {
                if(response.trim() === 'success') {
                    viewUser(userId);
                } else {
                    alert('Error deleting field. Please try again.');
                }
            }
        });
    }
}
</script>