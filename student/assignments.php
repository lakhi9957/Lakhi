<?php
session_start();
require_once '../config.php';

// Check if user is logged in and is student
if (!isLoggedIn() || !hasRole('student')) {
    redirect('../login.php');
}

$error = '';
$success = '';
$action = $_GET['action'] ?? 'list';
$assignment_id = $_GET['id'] ?? null;

// Get student info
$stmt = $conn->prepare("SELECT s.*, u.name, u.email FROM students s JOIN users u ON s.user_id = u.id WHERE s.user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

// Handle assignment submission
if ($_POST && isset($_POST['submit_assignment'])) {
    $assignment_id = sanitize($_POST['assignment_id']);
    $submission_text = sanitize($_POST['submission_text']);
    
    // Handle file upload
    $upload_dir = '../uploads/submissions/';
    $attachment = '';
    
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $filename = time() . '_' . basename($_FILES['attachment']['name']);
        $target_path = $upload_dir . $filename;
        
        // Check file size (10MB max)
        if ($_FILES['attachment']['size'] > 10 * 1024 * 1024) {
            $error = 'File size must be less than 10MB.';
        }
        // Check file type
        $allowed_types = ['pdf', 'doc', 'docx', 'txt', 'jpg', 'jpeg', 'png'];
        $file_extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!in_array($file_extension, $allowed_types)) {
            $error = 'Only PDF, DOC, DOCX, TXT, JPG, JPEG, PNG files are allowed.';
        }
        
        if (empty($error) && move_uploaded_file($_FILES['attachment']['tmp_name'], $target_path)) {
            $attachment = $filename;
        } else if (empty($error)) {
            $error = 'Failed to upload file.';
        }
    }
    
    if (empty($error)) {
        // Check if already submitted
        $check_stmt = $conn->prepare("SELECT id FROM assignment_submissions WHERE assignment_id = ? AND student_id = ?");
        $check_stmt->bind_param("ii", $assignment_id, $student['id']);
        $check_stmt->execute();
        $existing = $check_stmt->get_result()->fetch_assoc();
        
        if ($existing) {
            // Update existing submission
            $stmt = $conn->prepare("UPDATE assignment_submissions SET submission_text = ?, attachment = ?, submitted_at = NOW() WHERE id = ?");
            $stmt->bind_param("ssi", $submission_text, $attachment, $existing['id']);
            $success_msg = 'Assignment re-submitted successfully!';
        } else {
            // Create new submission
            $stmt = $conn->prepare("INSERT INTO assignment_submissions (assignment_id, student_id, submission_text, attachment, submitted_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param("iiss", $assignment_id, $student['id'], $submission_text, $attachment);
            $success_msg = 'Assignment submitted successfully!';
        }
        
        if ($stmt->execute()) {
            $success = $success_msg;
            $action = 'list'; // Redirect back to list
        } else {
            $error = 'Failed to submit assignment.';
        }
    }
}

// Get assignment details for submission
if ($action === 'submit' && $assignment_id) {
    $stmt = $conn->prepare("SELECT a.*, c.name as class_name, s.name as subject_name, 
                                   u.name as teacher_name,
                                   asub.id as submission_id, asub.submission_text, asub.attachment as submitted_file,
                                   asub.submitted_at, asub.marks_obtained, asub.feedback
                            FROM assignments a
                            JOIN classes c ON a.class_id = c.id
                            JOIN subjects s ON c.subject_id = s.id
                            JOIN teachers t ON a.teacher_id = t.id
                            JOIN users u ON t.user_id = u.id
                            JOIN enrollments e ON c.id = e.class_id
                            LEFT JOIN assignment_submissions asub ON a.id = asub.assignment_id AND asub.student_id = ?
                            WHERE a.id = ? AND e.student_id = ? AND e.status = 'active'");
    $stmt->bind_param("iii", $student['id'], $assignment_id, $student['id']);
    $stmt->execute();
    $assignment_details = $stmt->get_result()->fetch_assoc();
    
    if (!$assignment_details) {
        $error = 'Assignment not found or you are not enrolled in this class.';
        $action = 'list';
    }
}

// Filter parameter
$filter = $_GET['filter'] ?? 'all';

// Get assignments list
$filter_condition = "";
$params = array($student['id'], $student['id']);
$param_types = "ii";

if ($filter === 'pending') {
    $filter_condition = "AND asub.id IS NULL";
} elseif ($filter === 'submitted') {
    $filter_condition = "AND asub.id IS NOT NULL";
} elseif ($filter === 'graded') {
    $filter_condition = "AND asub.marks_obtained IS NOT NULL";
} elseif ($filter === 'overdue') {
    $filter_condition = "AND a.due_date < NOW() AND asub.id IS NULL";
}

$assignments_query = "SELECT a.*, c.name as class_name, s.name as subject_name,
                             u.name as teacher_name,
                             asub.id as submission_id, asub.submitted_at, asub.marks_obtained, asub.feedback,
                             CASE 
                                 WHEN asub.marks_obtained IS NOT NULL THEN 'graded'
                                 WHEN asub.id IS NOT NULL THEN 'submitted'
                                 WHEN a.due_date < NOW() THEN 'overdue'
                                 ELSE 'pending'
                             END as status
                      FROM assignments a
                      JOIN classes c ON a.class_id = c.id
                      JOIN subjects s ON c.subject_id = s.id
                      JOIN teachers t ON a.teacher_id = t.id
                      JOIN users u ON t.user_id = u.id
                      JOIN enrollments e ON c.id = e.class_id
                      LEFT JOIN assignment_submissions asub ON a.id = asub.assignment_id AND asub.student_id = ?
                      WHERE e.student_id = ? AND e.status = 'active' $filter_condition
                      ORDER BY a.due_date ASC";

$stmt = $conn->prepare($assignments_query);
$stmt->bind_param("ii", $student['id'], $student['id']);
$stmt->execute();
$assignments = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Assignments - Excellence Tuition Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 10px 20px;
            border-radius: 5px;
            margin: 5px 0;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        .main-content {
            background: #f8f9fa;
            min-height: 100vh;
        }
        .card {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border: none;
        }
        .assignment-card {
            transition: transform 0.2s ease;
            cursor: pointer;
        }
        .assignment-card:hover {
            transform: translateY(-2px);
        }
        .status-pending { border-left: 4px solid #ffc107; }
        .status-submitted { border-left: 4px solid #17a2b8; }
        .status-graded { border-left: 4px solid #28a745; }
        .status-overdue { border-left: 4px solid #dc3545; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <div class="sidebar">
                    <div class="text-center py-4">
                        <h4><i class="fas fa-user-graduate me-2"></i>Student Portal</h4>
                        <p class="mb-0">Welcome, <?php echo $student['name']; ?></p>
                        <small class="text-light"><?php echo $student['student_id']; ?></small>
                    </div>
                    
                    <nav class="nav flex-column px-3">
                        <a href="dashboard.php" class="nav-link">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                        <a href="assignments.php" class="nav-link active">
                            <i class="fas fa-clipboard-list me-2"></i>Assignments
                        </a>
                        <a href="attendance.php" class="nav-link">
                            <i class="fas fa-calendar-check me-2"></i>Attendance
                        </a>
                        <a href="grades.php" class="nav-link">
                            <i class="fas fa-chart-line me-2"></i>Grades
                        </a>
                        <a href="schedule.php" class="nav-link">
                            <i class="fas fa-calendar-alt me-2"></i>Schedule
                        </a>
                        <a href="fees.php" class="nav-link">
                            <i class="fas fa-money-bill-wave me-2"></i>Fees
                        </a>
                        <a href="profile.php" class="nav-link">
                            <i class="fas fa-user me-2"></i>Profile
                        </a>
                        <hr class="border-light">
                        <a href="../logout.php" class="nav-link">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <div class="main-content">
                    <!-- Top Navigation -->
                    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
                        <div class="container-fluid">
                            <h5 class="navbar-brand mb-0">My Assignments</h5>
                            <div class="d-flex">
                                <?php if ($action === 'list'): ?>
                                    <!-- Filter Buttons -->
                                    <div class="btn-group me-2" role="group">
                                        <a href="assignments.php?filter=all" class="btn <?php echo $filter === 'all' ? 'btn-primary' : 'btn-outline-primary'; ?> btn-sm">
                                            <i class="fas fa-list me-1"></i>All
                                        </a>
                                        <a href="assignments.php?filter=pending" class="btn <?php echo $filter === 'pending' ? 'btn-warning' : 'btn-outline-warning'; ?> btn-sm">
                                            <i class="fas fa-clock me-1"></i>Pending
                                        </a>
                                        <a href="assignments.php?filter=submitted" class="btn <?php echo $filter === 'submitted' ? 'btn-info' : 'btn-outline-info'; ?> btn-sm">
                                            <i class="fas fa-paper-plane me-1"></i>Submitted
                                        </a>
                                        <a href="assignments.php?filter=graded" class="btn <?php echo $filter === 'graded' ? 'btn-success' : 'btn-outline-success'; ?> btn-sm">
                                            <i class="fas fa-check me-1"></i>Graded
                                        </a>
                                        <a href="assignments.php?filter=overdue" class="btn <?php echo $filter === 'overdue' ? 'btn-danger' : 'btn-outline-danger'; ?> btn-sm">
                                            <i class="fas fa-exclamation me-1"></i>Overdue
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <a href="assignments.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left me-1"></i>Back to Assignments
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </nav>

                    <!-- Content -->
                    <div class="container-fluid py-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($action === 'submit' && isset($assignment_details)): ?>
                            <!-- Assignment Submission Form -->
                            <div class="row">
                                <div class="col-lg-8">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="mb-0">
                                                <i class="fas fa-paper-plane me-2"></i>
                                                <?php echo $assignment_details['submission_id'] ? 'Update Submission' : 'Submit Assignment'; ?>
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <form method="POST" enctype="multipart/form-data">
                                                <input type="hidden" name="assignment_id" value="<?php echo $assignment_details['id']; ?>">
                                                
                                                <div class="mb-3">
                                                    <label for="submission_text" class="form-label">Your Answer/Solution *</label>
                                                    <textarea class="form-control" id="submission_text" name="submission_text" rows="8" 
                                                              placeholder="Write your solution, answer, or explanation here..." required><?php echo htmlspecialchars($assignment_details['submission_text'] ?? ''); ?></textarea>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label for="attachment" class="form-label">Attach File (Optional)</label>
                                                    <input type="file" class="form-control" id="attachment" name="attachment" 
                                                           accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png">
                                                    <div class="form-text">
                                                        Allowed: PDF, DOC, DOCX, TXT, JPG, JPEG, PNG (Max: 10MB)
                                                    </div>
                                                    <?php if ($assignment_details['submitted_file']): ?>
                                                        <div class="mt-2">
                                                            <small class="text-muted">Current file: </small>
                                                            <a href="../uploads/submissions/<?php echo $assignment_details['submitted_file']; ?>" target="_blank" class="text-primary">
                                                                <i class="fas fa-file"></i> <?php echo $assignment_details['submitted_file']; ?>
                                                            </a>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <div class="d-flex gap-2">
                                                    <button type="submit" name="submit_assignment" class="btn btn-primary">
                                                        <i class="fas fa-paper-plane me-1"></i>
                                                        <?php echo $assignment_details['submission_id'] ? 'Update Submission' : 'Submit Assignment'; ?>
                                                    </button>
                                                    <a href="assignments.php" class="btn btn-secondary">Cancel</a>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-lg-4">
                                    <!-- Assignment Details -->
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Assignment Details</h6>
                                        </div>
                                        <div class="card-body">
                                            <h5><?php echo $assignment_details['title']; ?></h5>
                                            <p class="text-muted mb-2">
                                                <i class="fas fa-book me-1"></i><?php echo $assignment_details['class_name']; ?> - <?php echo $assignment_details['subject_name']; ?>
                                            </p>
                                            <p class="text-muted mb-3">
                                                <i class="fas fa-user me-1"></i><?php echo $assignment_details['teacher_name']; ?>
                                            </p>
                                            
                                            <div class="mb-3">
                                                <strong>Description:</strong>
                                                <p class="mt-1"><?php echo nl2br(htmlspecialchars($assignment_details['description'])); ?></p>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-6">
                                                    <strong>Due Date:</strong>
                                                    <p class="<?php echo strtotime($assignment_details['due_date']) < time() ? 'text-danger' : 'text-success'; ?>">
                                                        <?php echo date('M j, Y g:i A', strtotime($assignment_details['due_date'])); ?>
                                                    </p>
                                                </div>
                                                <div class="col-6">
                                                    <strong>Max Marks:</strong>
                                                    <p><?php echo $assignment_details['max_marks']; ?></p>
                                                </div>
                                            </div>
                                            
                                            <?php if ($assignment_details['attachment']): ?>
                                                <div class="mb-3">
                                                    <strong>Assignment File:</strong>
                                                    <p>
                                                        <a href="../uploads/assignments/<?php echo $assignment_details['attachment']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-download me-1"></i>Download
                                                        </a>
                                                    </p>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($assignment_details['marks_obtained'] !== null): ?>
                                                <div class="alert alert-success">
                                                    <strong><i class="fas fa-star me-1"></i>Graded:</strong> 
                                                    <?php echo $assignment_details['marks_obtained']; ?>/<?php echo $assignment_details['max_marks']; ?>
                                                    <?php if ($assignment_details['feedback']): ?>
                                                        <hr>
                                                        <strong>Feedback:</strong><br>
                                                        <?php echo nl2br(htmlspecialchars($assignment_details['feedback'])); ?>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <?php else: ?>
                            <!-- Assignments List -->
                            <div class="row">
                                <?php if ($assignments && $assignments->num_rows > 0): ?>
                                    <?php while ($assignment = $assignments->fetch_assoc()): ?>
                                        <div class="col-md-6 col-lg-4 mb-4">
                                            <div class="card assignment-card status-<?php echo $assignment['status']; ?>" onclick="viewAssignment(<?php echo $assignment['id']; ?>)">
                                                <div class="card-header d-flex justify-content-between align-items-start">
                                                    <h6 class="mb-0"><?php echo $assignment['title']; ?></h6>
                                                    <?php
                                                    $status_info = [
                                                        'pending' => ['bg-warning', 'fas fa-clock', 'Pending'],
                                                        'submitted' => ['bg-info', 'fas fa-paper-plane', 'Submitted'],
                                                        'graded' => ['bg-success', 'fas fa-check', 'Graded'],
                                                        'overdue' => ['bg-danger', 'fas fa-exclamation', 'Overdue']
                                                    ];
                                                    $info = $status_info[$assignment['status']];
                                                    ?>
                                                    <span class="badge <?php echo $info[0]; ?>">
                                                        <i class="<?php echo $info[1]; ?> me-1"></i><?php echo $info[2]; ?>
                                                    </span>
                                                </div>
                                                <div class="card-body">
                                                    <p class="text-muted small mb-2">
                                                        <i class="fas fa-book me-1"></i><?php echo $assignment['class_name']; ?> - <?php echo $assignment['subject_name']; ?>
                                                    </p>
                                                    <p class="text-muted small mb-3">
                                                        <i class="fas fa-user me-1"></i><?php echo $assignment['teacher_name']; ?>
                                                    </p>
                                                    
                                                    <div class="row text-center">
                                                        <div class="col-6">
                                                            <small class="text-muted">Due Date</small>
                                                            <p class="mb-0 <?php echo strtotime($assignment['due_date']) < time() ? 'text-danger' : ''; ?>">
                                                                <?php echo date('M j', strtotime($assignment['due_date'])); ?>
                                                            </p>
                                                        </div>
                                                        <div class="col-6">
                                                            <small class="text-muted">Max Marks</small>
                                                            <p class="mb-0"><?php echo $assignment['max_marks']; ?></p>
                                                        </div>
                                                    </div>
                                                    
                                                    <?php if ($assignment['marks_obtained'] !== null): ?>
                                                        <div class="text-center mt-2">
                                                            <span class="badge bg-success">
                                                                Score: <?php echo $assignment['marks_obtained']; ?>/<?php echo $assignment['max_marks']; ?>
                                                            </span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="card-footer">
                                                    <div class="d-flex gap-2">
                                                        <?php if ($assignment['status'] === 'pending' || $assignment['status'] === 'submitted'): ?>
                                                            <a href="assignments.php?action=submit&id=<?php echo $assignment['id']; ?>" class="btn btn-primary btn-sm flex-fill">
                                                                <i class="fas fa-paper-plane me-1"></i>
                                                                <?php echo $assignment['status'] === 'submitted' ? 'Update' : 'Submit'; ?>
                                                            </a>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($assignment['attachment']): ?>
                                                            <a href="../uploads/assignments/<?php echo $assignment['attachment']; ?>" target="_blank" class="btn btn-outline-secondary btn-sm">
                                                                <i class="fas fa-download"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="col-12">
                                        <div class="text-center py-5">
                                            <i class="fas fa-clipboard-list fa-4x text-muted mb-3"></i>
                                            <h5 class="text-muted">No assignments found</h5>
                                            <p class="text-muted">
                                                <?php if ($filter !== 'all'): ?>
                                                    No <?php echo $filter; ?> assignments found. 
                                                    <a href="assignments.php">View all assignments</a>
                                                <?php else: ?>
                                                    Your teachers haven't assigned any work yet.
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script>
        function viewAssignment(assignmentId) {
            window.location.href = 'assignments.php?action=submit&id=' + assignmentId;
        }

        // Auto-dismiss alerts
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>