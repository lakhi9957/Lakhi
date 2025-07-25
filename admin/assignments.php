<?php
session_start();
require_once '../config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !hasRole('admin')) {
    redirect('../login.php');
}

$error = '';
$success = '';
$action = $_GET['action'] ?? 'list';
$assignment_id = $_GET['id'] ?? null;

// Handle form submissions
if ($_POST) {
    if (isset($_POST['add_assignment'])) {
        $class_id = sanitize($_POST['class_id']);
        $teacher_id = sanitize($_POST['teacher_id']);
        $title = sanitize($_POST['title']);
        $description = sanitize($_POST['description']);
        $due_date = sanitize($_POST['due_date']);
        $max_marks = sanitize($_POST['max_marks']);
        $attachment = '';
        
        // Handle file upload
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === 0) {
            $upload_dir = '../uploads/assignments/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
            $filename = 'assignment_' . time() . '_' . uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $upload_path)) {
                $attachment = $filename;
            }
        }
        
        if (empty($class_id) || empty($title) || empty($due_date)) {
            $error = 'Please fill in all required fields.';
        } else {
            $stmt = $conn->prepare("INSERT INTO assignments (class_id, teacher_id, title, description, due_date, max_marks, attachment) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iisssis", $class_id, $teacher_id, $title, $description, $due_date, $max_marks, $attachment);
            
            if ($stmt->execute()) {
                $success = 'Assignment created successfully!';
            } else {
                $error = 'Failed to create assignment.';
            }
        }
    }
    
    if (isset($_POST['edit_assignment'])) {
        $assignment_id = sanitize($_POST['assignment_id']);
        $class_id = sanitize($_POST['class_id']);
        $teacher_id = sanitize($_POST['teacher_id']);
        $title = sanitize($_POST['title']);
        $description = sanitize($_POST['description']);
        $due_date = sanitize($_POST['due_date']);
        $max_marks = sanitize($_POST['max_marks']);
        $current_attachment = sanitize($_POST['current_attachment']);
        $attachment = $current_attachment;
        
        // Handle file upload
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === 0) {
            $upload_dir = '../uploads/assignments/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
            $filename = 'assignment_' . time() . '_' . uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $upload_path)) {
                // Delete old file if exists
                if ($current_attachment && file_exists($upload_dir . $current_attachment)) {
                    unlink($upload_dir . $current_attachment);
                }
                $attachment = $filename;
            }
        }
        
        if (empty($class_id) || empty($title) || empty($due_date)) {
            $error = 'Please fill in all required fields.';
        } else {
            $stmt = $conn->prepare("UPDATE assignments SET class_id = ?, teacher_id = ?, title = ?, description = ?, due_date = ?, max_marks = ?, attachment = ? WHERE id = ?");
            $stmt->bind_param("iisssisi", $class_id, $teacher_id, $title, $description, $due_date, $max_marks, $attachment, $assignment_id);
            
            if ($stmt->execute()) {
                $success = 'Assignment updated successfully!';
            } else {
                $error = 'Failed to update assignment.';
            }
        }
    }
    
    if (isset($_POST['delete_assignment'])) {
        $assignment_id = sanitize($_POST['assignment_id']);
        
        // Get attachment filename to delete file
        $stmt = $conn->prepare("SELECT attachment FROM assignments WHERE id = ?");
        $stmt->bind_param("i", $assignment_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $assignment = $result->fetch_assoc();
        
        // Delete assignment
        $stmt = $conn->prepare("DELETE FROM assignments WHERE id = ?");
        $stmt->bind_param("i", $assignment_id);
        
        if ($stmt->execute()) {
            // Delete file if exists
            if ($assignment['attachment'] && file_exists('../uploads/assignments/' . $assignment['attachment'])) {
                unlink('../uploads/assignments/' . $assignment['attachment']);
            }
            $success = 'Assignment deleted successfully!';
        } else {
            $error = 'Failed to delete assignment.';
        }
    }
    
    if (isset($_POST['grade_submission'])) {
        $submission_id = sanitize($_POST['submission_id']);
        $marks_obtained = sanitize($_POST['marks_obtained']);
        $feedback = sanitize($_POST['feedback']);
        $graded_by = $_SESSION['user_id'];
        
        $stmt = $conn->prepare("UPDATE assignment_submissions SET marks_obtained = ?, feedback = ?, graded_by = ?, graded_at = NOW() WHERE id = ?");
        $stmt->bind_param("isii", $marks_obtained, $feedback, $graded_by, $submission_id);
        
        if ($stmt->execute()) {
            $success = 'Submission graded successfully!';
        } else {
            $error = 'Failed to grade submission.';
        }
    }
}

// Get assignment details for editing
if ($action === 'edit' && $assignment_id) {
    $stmt = $conn->prepare("SELECT a.*, c.name as class_name, s.name as subject_name, t.teacher_id, u.name as teacher_name 
                           FROM assignments a 
                           JOIN classes c ON a.class_id = c.id 
                           JOIN subjects s ON c.subject_id = s.id 
                           JOIN teachers t ON a.teacher_id = t.id 
                           JOIN users u ON t.user_id = u.id 
                           WHERE a.id = ?");
    $stmt->bind_param("i", $assignment_id);
    $stmt->execute();
    $assignment_edit = $stmt->get_result()->fetch_assoc();
}

// Get assignments list with details
$assignments_query = "SELECT a.*, c.name as class_name, s.name as subject_name, 
                             t.teacher_id, u.name as teacher_name,
                             COUNT(sub.id) as submission_count,
                             COUNT(CASE WHEN sub.marks_obtained IS NOT NULL THEN 1 END) as graded_count
                      FROM assignments a 
                      JOIN classes c ON a.class_id = c.id 
                      JOIN subjects s ON c.subject_id = s.id 
                      JOIN teachers t ON a.teacher_id = t.id 
                      JOIN users u ON t.user_id = u.id 
                      LEFT JOIN assignment_submissions sub ON a.id = sub.assignment_id
                      GROUP BY a.id
                      ORDER BY a.created_at DESC";
$assignments = $conn->query($assignments_query);

// Get classes for dropdown with better info
$classes = $conn->query("SELECT c.*, s.name as subject_name, s.code as subject_code, u.name as teacher_name 
                        FROM classes c 
                        JOIN subjects s ON c.subject_id = s.id 
                        LEFT JOIN teachers t ON c.teacher_id = t.id 
                        LEFT JOIN users u ON t.user_id = u.id 
                        ORDER BY c.name ASC");

// Get teachers for dropdown
$teachers = $conn->query("SELECT t.*, u.name FROM teachers t JOIN users u ON t.user_id = u.id WHERE t.status = 'active' ORDER BY u.name ASC");

// Get submissions for specific assignment if viewing
if ($action === 'submissions' && $assignment_id) {
    $submissions_query = "SELECT sub.*, s.student_id, u.name as student_name, 
                                 grader.name as graded_by_name
                          FROM assignment_submissions sub
                          JOIN students s ON sub.student_id = s.id
                          JOIN users u ON s.user_id = u.id
                          LEFT JOIN users grader ON sub.graded_by = grader.id
                          WHERE sub.assignment_id = ?
                          ORDER BY sub.submitted_at DESC";
    $stmt = $conn->prepare($submissions_query);
    $stmt->bind_param("i", $assignment_id);
    $stmt->execute();
    $submissions = $stmt->get_result();
    
    // Get assignment details
    $stmt = $conn->prepare("SELECT a.*, c.name as class_name, s.name as subject_name FROM assignments a JOIN classes c ON a.class_id = c.id JOIN subjects s ON c.subject_id = s.id WHERE a.id = ?");
    $stmt->bind_param("i", $assignment_id);
    $stmt->execute();
    $assignment_details = $stmt->get_result()->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignment Management - Excellence Tuition Center</title>
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
            transition: transform 0.3s ease;
            border-left: 4px solid #667eea;
        }
        .assignment-card:hover {
            transform: translateY(-2px);
        }
        .status-badge {
            font-size: 0.8rem;
        }
        .submission-item {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            border-left: 3px solid #dee2e6;
        }
        .submission-item.graded {
            border-left-color: #28a745;
        }
        .submission-item.pending {
            border-left-color: #ffc107;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <div class="sidebar">
                    <div class="text-center py-4">
                        <h4><i class="fas fa-graduation-cap me-2"></i>Admin Panel</h4>
                        <p class="mb-0">Welcome, <?php echo $_SESSION['name']; ?></p>
                    </div>
                    
                    <nav class="nav flex-column px-3">
                        <a href="dashboard.php" class="nav-link">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                        <a href="students.php" class="nav-link">
                            <i class="fas fa-user-graduate me-2"></i>Students
                        </a>
                        <a href="teachers.php" class="nav-link">
                            <i class="fas fa-chalkboard-teacher me-2"></i>Teachers
                        </a>
                        <a href="classes.php" class="nav-link">
                            <i class="fas fa-door-open me-2"></i>Classes
                        </a>
                        <a href="subjects.php" class="nav-link">
                            <i class="fas fa-book me-2"></i>Subjects
                        </a>
                        <a href="fees.php" class="nav-link">
                            <i class="fas fa-money-bill-wave me-2"></i>Fees
                        </a>
                        <a href="attendance.php" class="nav-link">
                            <i class="fas fa-calendar-check me-2"></i>Attendance
                        </a>
                        <a href="assignments.php" class="nav-link active">
                            <i class="fas fa-clipboard-list me-2"></i>Assignments
                        </a>
                        <a href="exams.php" class="nav-link">
                            <i class="fas fa-file-alt me-2"></i>Exams
                        </a>
                        <a href="announcements.php" class="nav-link">
                            <i class="fas fa-bullhorn me-2"></i>Announcements
                        </a>
                        <a href="reports.php" class="nav-link">
                            <i class="fas fa-chart-bar me-2"></i>Reports
                        </a>
                        <a href="settings.php" class="nav-link">
                            <i class="fas fa-cog me-2"></i>Settings
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
                            <h5 class="navbar-brand mb-0">Assignment Management</h5>
                            <div class="d-flex">
                                <?php if ($action === 'list'): ?>
                                    <a href="assignments.php?action=add" class="btn btn-primary me-2">
                                        <i class="fas fa-plus me-1"></i>Create Assignment
                                    </a>
                                    <div class="dropdown">
                                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-filter me-1"></i>Filter
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#"><i class="fas fa-clock me-2"></i>Due Today</a></li>
                                            <li><a class="dropdown-item" href="#"><i class="fas fa-exclamation me-2"></i>Overdue</a></li>
                                            <li><a class="dropdown-item" href="#"><i class="fas fa-check me-2"></i>Completed</a></li>
                                        </ul>
                                    </div>
                                <?php elseif ($action === 'submissions'): ?>
                                    <a href="assignments.php" class="btn btn-outline-secondary me-2">
                                        <i class="fas fa-arrow-left me-1"></i>Back to Assignments
                                    </a>
                                    <button class="btn btn-success" onclick="gradeAllSubmissions()">
                                        <i class="fas fa-check-double me-1"></i>Grade All
                                    </button>
                                <?php else: ?>
                                    <a href="assignments.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left me-1"></i>Back to List
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

                        <?php if ($action === 'add'): ?>
                            <!-- Add Assignment Form -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Create New Assignment</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="" enctype="multipart/form-data">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="class_id" class="form-label">Class *</label>
                                                <select class="form-select" id="class_id" name="class_id" required>
                                                    <option value="">Select Class</option>
                                                    <?php if ($classes && $classes->num_rows > 0): ?>
                                                        <?php while ($class = $classes->fetch_assoc()): ?>
                                                            <option value="<?php echo $class['id']; ?>">
                                                                <?php echo $class['name'] . ' - ' . $class['subject_name'] . ' (' . ($class['teacher_name'] ?? 'No Teacher') . ')'; ?>
                                                            </option>
                                                        <?php endwhile; ?>
                                                    <?php else: ?>
                                                        <option value="" disabled>No classes available - Please create classes first</option>
                                                    <?php endif; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="teacher_id" class="form-label">Teacher *</label>
                                                <select class="form-select" id="teacher_id" name="teacher_id" required>
                                                    <option value="">Select Teacher</option>
                                                    <?php while ($teacher = $teachers->fetch_assoc()): ?>
                                                        <option value="<?php echo $teacher['id']; ?>">
                                                            <?php echo $teacher['name'] . ' (' . $teacher['teacher_id'] . ')'; ?>
                                                        </option>
                                                    <?php endwhile; ?>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="title" class="form-label">Assignment Title *</label>
                                            <input type="text" class="form-control" id="title" name="title" placeholder="Enter assignment title" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="description" class="form-label">Description</label>
                                            <textarea class="form-control" id="description" name="description" rows="4" placeholder="Assignment instructions and details"></textarea>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="due_date" class="form-label">Due Date *</label>
                                                <input type="datetime-local" class="form-control" id="due_date" name="due_date" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="max_marks" class="form-label">Maximum Marks</label>
                                                <input type="number" class="form-control" id="max_marks" name="max_marks" min="1" max="1000" value="100">
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="attachment" class="form-label">Attachment (Optional)</label>
                                            <input type="file" class="form-control" id="attachment" name="attachment" accept=".pdf,.doc,.docx,.txt,.zip">
                                            <small class="form-text text-muted">Supported formats: PDF, DOC, DOCX, TXT, ZIP (Max: 10MB)</small>
                                        </div>
                                        
                                        <div class="d-flex gap-2">
                                            <button type="submit" name="add_assignment" class="btn btn-primary">
                                                <i class="fas fa-save me-1"></i>Create Assignment
                                            </button>
                                            <a href="assignments.php" class="btn btn-secondary">Cancel</a>
                                        </div>
                                    </form>
                                </div>
                            </div>

                        <?php elseif ($action === 'edit' && isset($assignment_edit)): ?>
                            <!-- Edit Assignment Form -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Assignment: <?php echo $assignment_edit['title']; ?></h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="" enctype="multipart/form-data">
                                        <input type="hidden" name="assignment_id" value="<?php echo $assignment_edit['id']; ?>">
                                        <input type="hidden" name="current_attachment" value="<?php echo $assignment_edit['attachment']; ?>">
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="class_id" class="form-label">Class *</label>
                                                <select class="form-select" id="class_id" name="class_id" required>
                                                    <option value="<?php echo $assignment_edit['class_id']; ?>" selected>
                                                        <?php echo $assignment_edit['class_name'] . ' - ' . $assignment_edit['subject_name']; ?>
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="teacher_id" class="form-label">Teacher *</label>
                                                <select class="form-select" id="teacher_id" name="teacher_id" required>
                                                    <option value="<?php echo $assignment_edit['teacher_id']; ?>" selected>
                                                        <?php echo $assignment_edit['teacher_name']; ?>
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="title" class="form-label">Assignment Title *</label>
                                            <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($assignment_edit['title']); ?>" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="description" class="form-label">Description</label>
                                            <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($assignment_edit['description']); ?></textarea>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="due_date" class="form-label">Due Date *</label>
                                                <input type="datetime-local" class="form-control" id="due_date" name="due_date" value="<?php echo date('Y-m-d\TH:i', strtotime($assignment_edit['due_date'])); ?>" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="max_marks" class="form-label">Maximum Marks</label>
                                                <input type="number" class="form-control" id="max_marks" name="max_marks" value="<?php echo $assignment_edit['max_marks']; ?>" min="1" max="1000">
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="attachment" class="form-label">Attachment</label>
                                            <?php if ($assignment_edit['attachment']): ?>
                                                <div class="mb-2">
                                                    <span class="badge bg-info">Current: <?php echo $assignment_edit['attachment']; ?></span>
                                                    <a href="../uploads/assignments/<?php echo $assignment_edit['attachment']; ?>" target="_blank" class="btn btn-sm btn-outline-primary ms-2">
                                                        <i class="fas fa-download me-1"></i>Download
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                            <input type="file" class="form-control" id="attachment" name="attachment" accept=".pdf,.doc,.docx,.txt,.zip">
                                            <small class="form-text text-muted">Leave empty to keep current attachment. Upload new file to replace.</small>
                                        </div>
                                        
                                        <div class="d-flex gap-2">
                                            <button type="submit" name="edit_assignment" class="btn btn-primary">
                                                <i class="fas fa-save me-1"></i>Update Assignment
                                            </button>
                                            <a href="assignments.php" class="btn btn-secondary">Cancel</a>
                                        </div>
                                    </form>
                                </div>
                            </div>

                        <?php elseif ($action === 'submissions' && isset($assignment_details)): ?>
                            <!-- View Submissions -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-clipboard-list me-2"></i>
                                        Submissions for: <?php echo $assignment_details['title']; ?>
                                    </h5>
                                    <small class="text-muted">
                                        Class: <?php echo $assignment_details['class_name']; ?> | 
                                        Subject: <?php echo $assignment_details['subject_name']; ?> | 
                                        Due: <?php echo date('M j, Y g:i A', strtotime($assignment_details['due_date'])); ?>
                                    </small>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <div class="text-center">
                                                <h4 class="text-primary"><?php echo $submissions->num_rows; ?></h4>
                                                <small class="text-muted">Total Submissions</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center">
                                                <h4 class="text-success">
                                                    <?php 
                                                    $graded = 0;
                                                    $submissions->data_seek(0);
                                                    while ($sub = $submissions->fetch_assoc()) {
                                                        if ($sub['marks_obtained'] !== null) $graded++;
                                                    }
                                                    echo $graded;
                                                    ?>
                                                </h4>
                                                <small class="text-muted">Graded</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center">
                                                <h4 class="text-warning"><?php echo $submissions->num_rows - $graded; ?></h4>
                                                <small class="text-muted">Pending</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center">
                                                <h4 class="text-info"><?php echo $assignment_details['max_marks']; ?></h4>
                                                <small class="text-muted">Max Marks</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">All Submissions</h6>
                                </div>
                                <div class="card-body">
                                    <?php if ($submissions->num_rows > 0): ?>
                                        <?php $submissions->data_seek(0); ?>
                                        <?php while ($submission = $submissions->fetch_assoc()): ?>
                                            <div class="submission-item <?php echo $submission['marks_obtained'] !== null ? 'graded' : 'pending'; ?>">
                                                <div class="row align-items-center">
                                                    <div class="col-md-3">
                                                        <div class="d-flex align-items-center">
                                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px;">
                                                                <?php echo strtoupper(substr($submission['student_name'], 0, 1)); ?>
                                                            </div>
                                                            <div>
                                                                <div class="fw-bold"><?php echo $submission['student_name']; ?></div>
                                                                <small class="text-muted"><?php echo $submission['student_id']; ?></small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <small class="text-muted">Submitted:</small><br>
                                                        <span class="small"><?php echo date('M j, g:i A', strtotime($submission['submitted_at'])); ?></span>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <?php if ($submission['marks_obtained'] !== null): ?>
                                                            <span class="badge bg-success">
                                                                <?php echo $submission['marks_obtained']; ?>/<?php echo $assignment_details['max_marks']; ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge bg-warning">Pending</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <?php if ($submission['attachment']): ?>
                                                            <a href="../uploads/submissions/<?php echo $submission['attachment']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-download me-1"></i>Download
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <button class="btn btn-sm btn-primary" onclick="gradeSubmission(<?php echo $submission['id']; ?>, '<?php echo addslashes($submission['student_name']); ?>', <?php echo $submission['marks_obtained'] ?? 0; ?>, '<?php echo addslashes($submission['feedback'] ?? ''); ?>')">
                                                            <i class="fas fa-star me-1"></i>Grade
                                                        </button>
                                                    </div>
                                                </div>
                                                <?php if ($submission['submission_text']): ?>
                                                    <div class="mt-2">
                                                        <small class="text-muted">Submission:</small>
                                                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($submission['submission_text'])); ?></p>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($submission['feedback']): ?>
                                                    <div class="mt-2">
                                                        <small class="text-muted">Feedback:</small>
                                                        <p class="mb-0 text-success"><?php echo nl2br(htmlspecialchars($submission['feedback'])); ?></p>
                                                        <small class="text-muted">Graded by: <?php echo $submission['graded_by_name']; ?></small>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">No submissions yet</h5>
                                            <p class="text-muted">Students haven't submitted their assignments yet.</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                        <?php else: ?>
                            <!-- Assignments List -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>All Assignments</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="assignmentsTable" class="table table-striped table-hover">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Title</th>
                                                    <th>Class/Subject</th>
                                                    <th>Teacher</th>
                                                    <th>Due Date</th>
                                                    <th>Max Marks</th>
                                                    <th>Submissions</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($assignment = $assignments->fetch_assoc()): ?>
                                                    <tr>
                                                        <td>
                                                            <div class="fw-bold"><?php echo $assignment['title']; ?></div>
                                                            <?php if ($assignment['attachment']): ?>
                                                                <small class="text-muted">
                                                                    <i class="fas fa-paperclip"></i> Has attachment
                                                                </small>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?php echo $assignment['class_name']; ?><br>
                                                            <small class="text-muted"><?php echo $assignment['subject_name']; ?></small>
                                                        </td>
                                                        <td><?php echo $assignment['teacher_name']; ?></td>
                                                        <td>
                                                            <?php 
                                                            $due_date = strtotime($assignment['due_date']);
                                                            $now = time();
                                                            echo date('M j, Y', $due_date);
                                                            ?>
                                                            <br>
                                                            <small class="<?php echo ($due_date < $now) ? 'text-danger' : 'text-muted'; ?>">
                                                                <?php echo date('g:i A', $due_date); ?>
                                                            </small>
                                                        </td>
                                                        <td><?php echo $assignment['max_marks']; ?></td>
                                                        <td>
                                                            <span class="badge bg-info"><?php echo $assignment['submission_count']; ?></span>
                                                            <span class="badge bg-success"><?php echo $assignment['graded_count']; ?> graded</span>
                                                        </td>
                                                        <td>
                                                            <?php
                                                            $due_date = strtotime($assignment['due_date']);
                                                            $now = time();
                                                            if ($due_date < $now): ?>
                                                                <span class="badge bg-danger">Overdue</span>
                                                            <?php elseif ($due_date < $now + 86400): ?>
                                                                <span class="badge bg-warning">Due Soon</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-success">Active</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <div class="btn-group btn-group-sm" role="group">
                                                                <a href="assignments.php?action=submissions&id=<?php echo $assignment['id']; ?>" class="btn btn-outline-info" title="View Submissions">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                                <a href="assignments.php?action=edit&id=<?php echo $assignment['id']; ?>" class="btn btn-outline-success" title="Edit">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                                <button type="button" class="btn btn-outline-danger" onclick="deleteAssignment(<?php echo $assignment['id']; ?>, '<?php echo addslashes($assignment['title']); ?>')" title="Delete">
                                                                    <i class="fas fa-trash"></i>
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
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Assignment Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete assignment <strong id="assignmentTitle"></strong>?</p>
                    <p class="text-danger">This action cannot be undone and will delete all submissions.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="" style="display: inline;">
                        <input type="hidden" name="assignment_id" id="deleteAssignmentId">
                        <button type="submit" name="delete_assignment" class="btn btn-danger">Delete Assignment</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Grade Submission Modal -->
    <div class="modal fade" id="gradeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Grade Submission</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="submission_id" id="gradeSubmissionId">
                        
                        <div class="mb-3">
                            <label class="form-label">Student: <strong id="gradeStudentName"></strong></label>
                        </div>
                        
                        <div class="mb-3">
                            <label for="marks_obtained" class="form-label">Marks Obtained</label>
                            <input type="number" class="form-control" id="marks_obtained" name="marks_obtained" min="0" max="<?php echo $assignment_details['max_marks'] ?? 100; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="feedback" class="form-label">Feedback</label>
                            <textarea class="form-control" id="feedback" name="feedback" rows="3" placeholder="Provide feedback to the student"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="grade_submission" class="btn btn-success">Save Grade</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#assignmentsTable').DataTable({
                responsive: true,
                pageLength: 10,
                order: [[3, 'desc']],
                columnDefs: [
                    { orderable: false, targets: -1 }
                ]
            });
        });

        function deleteAssignment(assignmentId, assignmentTitle) {
            document.getElementById('assignmentTitle').textContent = assignmentTitle;
            document.getElementById('deleteAssignmentId').value = assignmentId;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }

        function gradeSubmission(submissionId, studentName, currentMarks, currentFeedback) {
            document.getElementById('gradeSubmissionId').value = submissionId;
            document.getElementById('gradeStudentName').textContent = studentName;
            document.getElementById('marks_obtained').value = currentMarks;
            document.getElementById('feedback').value = currentFeedback;
            new bootstrap.Modal(document.getElementById('gradeModal')).show();
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