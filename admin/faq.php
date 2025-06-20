<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Set timezone and current date/time
date_default_timezone_set('UTC');
$current_datetime = '2025-05-27 17:49:23';
$current_user = 'Kalpesh-Sananse';

// Database connection
require_once '../includes/db_connection.php';

// Initialize messages
$success_message = '';
$error_message = '';

// Handle Delete
if (isset($_POST['delete_id'])) {
    $id = $conn->real_escape_string($_POST['delete_id']);
    $delete_query = "DELETE FROM faqs WHERE id = '$id'";
    
    if ($conn->query($delete_query)) {
        $success_message = "FAQ deleted successfully!";
    } else {
        $error_message = "Error deleting FAQ: " . $conn->error;
    }
}

// Handle Edit
if (isset($_POST['edit_id'])) {
    $id = $conn->real_escape_string($_POST['edit_id']);
    $question_number = $conn->real_escape_string($_POST['edit_question_number']);
    $question = $conn->real_escape_string($_POST['edit_question']);
    $answer = $conn->real_escape_string($_POST['edit_answer'] ?? ''); // Added null coalescing operator
    
    $update_query = "UPDATE faqs SET 
                    question_number = '$question_number',
                    question = '$question',
                    answer = '$answer'
                    WHERE id = '$id'";

    if ($conn->query($update_query)) {
        $success_message = "FAQ updated successfully!";
    } else {
        $error_message = "Error updating FAQ: " . $conn->error;
    }
}

// Handle Add New FAQ
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['question']) && !isset($_POST['edit_id']) && !isset($_POST['delete_id'])) {
    $question_number = $conn->real_escape_string($_POST['question_number'] ?? '');
    $question = $conn->real_escape_string($_POST['question'] ?? '');
    $answer = $conn->real_escape_string($_POST['answer'] ?? ''); // Added null coalescing operator
    
    if (!empty($question_number) && !empty($question) && !empty($answer)) {
        $query = "INSERT INTO faqs (question_number, question, answer, created_by) 
                  VALUES ('$question_number', '$question', '$answer', '$current_user')";
        
        if ($conn->query($query)) {
            $success_message = "FAQ added successfully!";
        } else {
            $error_message = "Error: " . $conn->error;
        }
    } else {
        $error_message = "All fields are required!";
    }
}

// Fetch existing FAQs
try {
    $faqs = $conn->query("SELECT * FROM faqs ORDER BY question_number ASC");
    if (!$faqs) {
        throw new Exception($conn->error);
    }
} catch (Exception $e) {
    $error_message = "Error fetching FAQs: " . $e->getMessage();
    $faqs = null;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage FAQs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .faq-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .faq-header {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 10px 10px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .faq-body {
            padding: 20px;
        }
        .faq-number {
            font-weight: bold;
            color: #4a6cf7;
            margin-right: 10px;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
        }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Manage FAQs</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="fas fa-plus"></i> Add New FAQ
            </button>
        </div>

        <?php if(isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if(isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- FAQ List -->
        <?php if ($faqs && $faqs->num_rows > 0): ?>
            <?php while($row = $faqs->fetch_assoc()): ?>
                <div class="faq-card">
                    <div class="faq-header">
                        <div>
                            <span class="faq-number"><?php echo htmlspecialchars($row['question_number']); ?></span>
                            <span class="faq-question"><?php echo htmlspecialchars($row['question']); ?></span>
                        </div>
                        <div class="action-buttons">
                            <button class="btn btn-sm btn-outline-primary edit-btn" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editModal"
                                    data-id="<?php echo $row['id']; ?>"
                                    data-number="<?php echo htmlspecialchars($row['question_number']); ?>"
                                    data-question="<?php echo htmlspecialchars($row['question']); ?>"
                                    data-answer="<?php echo htmlspecialchars($row['answer']); ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger delete-btn"
                                    data-bs-toggle="modal"
                                    data-bs-target="#deleteModal"
                                    data-id="<?php echo $row['id']; ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="faq-body">
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($row['answer'])); ?></p>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="text-center py-5">
                <p class="text-muted">No FAQs found. Add your first FAQ!</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New FAQ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Question Number</label>
                            <input type="text" name="question_number" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Question</label>
                            <input type="text" name="question" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Answer</label>
                            <textarea name="answer" class="form-control" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add FAQ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit FAQ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="edit_id" id="edit_id">
                        <div class="mb-3">
                            <label class="form-label">Question Number</label>
                            <input type="text" name="edit_question_number" id="edit_question_number" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Question</label>
                            <input type="text" name="edit_question" id="edit_question" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Answer</label>
                            <textarea name="edit_answer" id="edit_answer" class="form-control" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete FAQ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="delete_id" id="delete_id">
                        <p>Are you sure you want to delete this FAQ?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Edit button handler
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const number = this.getAttribute('data-number');
                const question = this.getAttribute('data-question');
                const answer = this.getAttribute('data-answer');
                
                document.getElementById('edit_id').value = id;
                document.getElementById('edit_question_number').value = number;
                document.getElementById('edit_question').value = question;
                document.getElementById('edit_answer').value = answer;
            });
        });

        // Delete button handler
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                document.getElementById('delete_id').value = id;
            });
        });
    </script>
</body>
</html>