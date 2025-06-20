<?php
session_start();
include('../includes/db_connection.php');

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// Fetch all office employees
$sql = "SELECT * FROM office_employees ORDER BY created_at DESC";
$result = $conn->query($sql);

date_default_timezone_set('UTC');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Office Management - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
    :root {
        --primary-gradient: linear-gradient(135deg, #0A142F, #1a2642);
        --secondary-gradient: linear-gradient(135deg, #2980b9, #3498db);
        --success-gradient: linear-gradient(135deg, #27ae60, #2ecc71);
        --danger-gradient: linear-gradient(135deg, #e74c3c, #c0392b);
        --border-radius: 12px;
    }

    body {
        background-color: #f8f9fa;
        font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    }

    .top-bar {
        background: linear-gradient(135deg, #2980b9, #6dd5fa);
        padding: 1rem 0;
        margin-bottom: 2rem;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
    }

    .info-widget {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border-radius: var(--border-radius);
        padding: 1rem 1.5rem;
        color: white;
        margin: 0.5rem 0;
    }

    .info-widget i {
        font-size: 1.5rem;
        margin-right: 1rem;
    }

    .card {
        border: none;
        border-radius: var(--border-radius);
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        margin-bottom: 2rem;
    }

    .card-header {
        background: linear-gradient(135deg, #2980b9, #6dd5fa);
        color: white;
        padding: 1.5rem;
        border-radius: var(--border-radius) var(--border-radius) 0 0;
    }

    .action-buttons .btn {
        border-radius: 8px;
        padding: 0.5rem 1rem;
        font-weight: 500;
    }

    .btn-add {
        background: var(--success-gradient);
        color: white;
        border: none;
    }

    .btn-edit {
        background: var(--secondary-gradient);
        color: white;
        border: none;
    }

    .btn-delete {
        background: var(--danger-gradient);
        color: white;
        border: none;
    }

    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-weight: 500;
        font-size: 0.875rem;
    }

    .status-active {
        background-color: #d4edda;
        color: #155724;
    }

    .status-inactive {
        background-color: #f8d7da;
        color: #721c24;
    }

    .table thead th {
        background-color: #f8f9fa;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.875rem;
        letter-spacing: 0.5px;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .card {
        animation: fadeIn 0.6s ease-out;
    }
    </style>
</head>

<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <div class="info-widget d-flex align-items-center">
                        <i class="bi bi-clock"></i>
                        <div>
                            <div class="small opacity-75">Current Date and Time (UTC)</div>
                            <div class="fw-bold"><?php echo date('Y-m-d H:i:s'); ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-widget d-flex align-items-center">
                        <i class="bi bi-person-circle"></i>
                        <div>
                            <div class="small opacity-75">Admin</div>
                            <div class="fw-bold"><?php echo htmlspecialchars($_SESSION['admin']); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <i class="bi bi-building me-2"></i>
                    Office Management
                </h4>
                <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                    <i class="bi bi-plus-lg me-2"></i>Add New Employee
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Employee ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Department</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($employee = $result->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $employee['id']; ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-person-badge me-2"></i>
                                        <?php echo htmlspecialchars($employee['name']); ?>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($employee['email']); ?></td>
                                <td><?php echo htmlspecialchars($employee['department']); ?></td>
                                <td><?php echo htmlspecialchars($employee['role']); ?></td>
                                <td>
                                    <span
                                        class="status-badge <?php echo $employee['status'] == 'active' ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo ucfirst($employee['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('Y-m-d H:i:s', strtotime($employee['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons text-end">
                                        <button class="btn btn-edit btn-sm"
                                            onclick="editEmployee(<?php echo $employee['id']; ?>)"
                                            data-bs-toggle="modal" data-bs-target="#editEmployeeModal">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-delete btn-sm"
                                            onclick="deleteEmployee(<?php echo $employee['id']; ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Employee Modal -->
    <div class="modal fade" id="addEmployeeModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-person-plus me-2"></i>
                        Add New Employee
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addEmployeeForm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Department</label>
                                <input type="text" class="form-control" name="department" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Role</label>
                                <input type="text" class="form-control" name="role" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Password</label>
                                <input type="password" class="form-control" name="password" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" name="confirm_password" required>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveEmployee()">Save Employee</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Employee Modal -->
    <div class="modal fade" id="editEmployeeModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-person-check me-2"></i>
                        Edit Employee
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editEmployeeForm">
                        <input type="hidden" name="employee_id" id="edit_employee_id">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" name="name" id="edit_name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" id="edit_email" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Department</label>
                                <input type="text" class="form-control" name="department" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Role</label>
                                <input type="text" class="form-control" name="role" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status" id="edit_status" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Password (Leave blank to keep current)</label>
                                <input type="password" class="form-control" name="password">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="updateEmployee()">Update Employee</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    function saveEmployee() {
        const form = $('#addEmployeeForm');
        const formData = new FormData(form[0]);

        // Check if passwords match
        if (formData.get('password') !== formData.get('confirm_password')) {
            alert('Passwords do not match!');
            return;
        }

        $.ajax({
            url: 'save_officer.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response === 'success') {
                    location.reload();
                } else {
                    alert('Error saving employee: ' + response);
                }
            }
        });
    }

    function editEmployee(id) {
        $.ajax({
            url: 'get_employee.php',
            type: 'POST',
            data: {
                employee_id: id
            },
            dataType: 'json',
            success: function(response) {
                if (response) {
                    $('#edit_employee_id').val(response.id);
                    $('#edit_name').val(response.name);
                    $('#edit_email').val(response.email);
                    $('#edit_department').val(response.department);
                    $('#edit_role').val(response.role);
                    $('#edit_status').val(response.status);
                } else {
                    alert('Error loading employee data');
                }
            },
            error: function(xhr, status, error) {
                alert('Error: ' + error);
            }
        });
    }

    function updateEmployee() {
        const form = $('#editEmployeeForm');
        const formData = new FormData(form[0]);

        $.ajax({
            url: 'update_employee.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response === 'success') {
                    location.reload();
                } else {
                    alert('Error updating employee: ' + response);
                }
            }
        });
    }

    function deleteEmployee(id) {
        if (confirm('Are you sure you want to delete this employee?')) {
            $.ajax({
                url: 'delete_employee.php',
                type: 'POST',
                data: {
                    employee_id: id
                },
                success: function(response) {
                    if (response === 'success') {
                        location.reload();
                    } else {
                        alert('Error deleting employee: ' + response);
                    }
                }
            });
        }
    }
    </script>
</body>

</html>