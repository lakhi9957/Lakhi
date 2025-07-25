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
$class_id = $_GET['id'] ?? null;

// Handle form submissions
if ($_POST) {
    if (isset($_POST['add_class'])) {
        $name = sanitize($_POST['name']);
        $section = sanitize($_POST['section']);
        $subject_id = sanitize($_POST['subject_id']);
        $teacher_id = sanitize($_POST['teacher_id']);
        $room_number = sanitize($_POST['room_number']);
        $capacity = sanitize($_POST['capacity']);
        $schedule_time = sanitize($_POST['schedule_time']);
        $schedule_days = sanitize($_POST['schedule_days']);
        $fee = sanitize($_POST['fee']);
        
        if (empty($name) || empty($subject_id)) {
            $error = 'Please fill in all required fields.';
        } else {
            $stmt = $conn->prepare("INSERT INTO classes (name, section, subject_id, teacher_id, room_number, capacity, schedule_time, schedule_days, fee) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssiisisss", $name, $section, $subject_id, $teacher_id, $room_number, $capacity, $schedule_time, $schedule_days, $fee);
            
            if ($stmt->execute()) {
                $success = 'Class created successfully!';
            } else {
                $error = 'Failed to create class.';
            }
        }
    }
    
    if (isset($_POST['edit_class'])) {
        $class_id = sanitize($_POST['class_id']);
        $name = sanitize($_POST['name']);
        $section = sanitize($_POST['section']);
        $subject_id = sanitize($_POST['subject_id']);
        $teacher_id = sanitize($_POST['teacher_id']);
        $room_number = sanitize($_POST['room_number']);
        $capacity = sanitize($_POST['capacity']);
        $schedule_time = sanitize($_POST['schedule_time']);
        $schedule_days = sanitize($_POST['schedule_days']);
        $fee = sanitize($_POST['fee']);
        
        if (empty($name) || empty($subject_id)) {
            $error = 'Please fill in all required fields.';
        } else {
            $stmt = $conn->prepare("UPDATE classes SET name = ?, section = ?, subject_id = ?, teacher_id = ?, room_number = ?, capacity = ?, schedule_time = ?, schedule_days = ?, fee = ? WHERE id = ?");
            $stmt->bind_param("ssiisissi", $name, $section, $subject_id, $teacher_id, $room_number, $capacity, $schedule_time, $schedule_days, $fee, $class_id);
            
            if ($stmt->execute()) {
                $success = 'Class updated successfully!';
            } else {
                $error = 'Failed to update class.';
            }
        }
    }
    
    if (isset($_POST['delete_class'])) {
        $class_id = sanitize($_POST['class_id']);
        
        $stmt = $conn->prepare("DELETE FROM classes WHERE id = ?");
        $stmt->bind_param("i", $class_id);
        
        if ($stmt->execute()) {
            $success = 'Class deleted successfully!';
        } else {
            $error = 'Failed to delete class.';
        }
    }
}

// Get class details for editing
if ($action === 'edit' && $class_id) {
    $stmt = $conn->prepare("SELECT c.*, s.name as subject_name, t.teacher_id, u.name as teacher_name FROM classes c JOIN subjects s ON c.subject_id = s.id LEFT JOIN teachers t ON c.teacher_id = t.id LEFT JOIN users u ON t.user_id = u.id WHERE c.id = ?");
    $stmt->bind_param("i", $class_id);
    $stmt->execute();
    $class_edit = $stmt->get_result()->fetch_assoc();
}

// Get classes list
$classes_query = "SELECT c.*, s.name as subject_name, s.code as subject_code, 
                         t.teacher_id, u.name as teacher_name,
                         COUNT(e.id) as student_count
                  FROM classes c 
                  JOIN subjects s ON c.subject_id = s.id 
                  LEFT JOIN teachers t ON c.teacher_id = t.id 
                  LEFT JOIN users u ON t.user_id = u.id 
                  LEFT JOIN enrollments e ON c.id = e.class_id AND e.status = 'active'
                  GROUP BY c.id
                  ORDER BY c.name ASC";
$classes = $conn->query($classes_query);

// Get subjects for dropdown
$subjects = $conn->query("SELECT * FROM subjects ORDER BY name ASC");

// Get teachers for dropdown  
$teachers = $conn->query("SELECT t.*, u.name FROM teachers t JOIN users u ON t.user_id = u.id WHERE t.status = 'active' ORDER BY u.name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Management - Excellence Tuition Center</title>
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
                        <a href="classes.php" class="nav-link active">
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
                        <a href="assignments.php" class="nav-link">
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
                            <h5 class="navbar-brand mb-0">Class Management</h5>
                            <div class="d-flex">
                                <?php if ($action === 'list'): ?>
                                    <a href="classes.php?action=add" class="btn btn-primary me-2">
                                        <i class="fas fa-plus me-1"></i>Add Class
                                    </a>
                                    <a href="subjects.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-book me-1"></i>Manage Subjects
                                    </a>
                                <?php else: ?>
                                    <a href="classes.php" class="btn btn-outline-secondary">
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
                            <!-- Add Class Form -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Add New Class</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="name" class="form-label">Class Name *</label>
                                                <input type="text" class="form-control" id="name" name="name" placeholder="e.g., Grade 10 - Mathematics" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="section" class="form-label">Section</label>
                                                <select class="form-select" id="section" name="section">
                                                    <option value="">Select Section</option>
                                                    <option value="A">A</option>
                                                    <option value="B">B</option>
                                                    <option value="C">C</option>
                                                    <option value="D">D</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="subject_id" class="form-label">Subject *</label>
                                                <select class="form-select" id="subject_id" name="subject_id" required>
                                                    <option value="">Select Subject</option>
                                                    <?php $subjects->data_seek(0); ?>
                                                    <?php while ($subject = $subjects->fetch_assoc()): ?>
                                                        <option value="<?php echo $subject['id']; ?>">
                                                            <?php echo $subject['name'] . ' (' . $subject['code'] . ')'; ?>
                                                        </option>
                                                    <?php endwhile; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="teacher_id" class="form-label">Teacher</label>
                                                <select class="form-select" id="teacher_id" name="teacher_id">
                                                    <option value="">Select Teacher</option>
                                                    <?php $teachers->data_seek(0); ?>
                                                    <?php while ($teacher = $teachers->fetch_assoc()): ?>
                                                        <option value="<?php echo $teacher['id']; ?>">
                                                            <?php echo $teacher['name'] . ' (' . $teacher['teacher_id'] . ')'; ?>
                                                        </option>
                                                    <?php endwhile; ?>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label for="room_number" class="form-label">Room Number</label>
                                                <input type="text" class="form-control" id="room_number" name="room_number" placeholder="e.g., R-101">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="capacity" class="form-label">Capacity</label>
                                                <input type="number" class="form-control" id="capacity" name="capacity" min="1" max="100" placeholder="30">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="fee" class="form-label">Fee</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">$</span>
                                                    <input type="number" class="form-control" id="fee" name="fee" step="0.01" min="0" placeholder="100.00">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="schedule_time" class="form-label">Schedule Time</label>
                                                <input type="time" class="form-control" id="schedule_time" name="schedule_time">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="schedule_days" class="form-label">Schedule Days</label>
                                                <input type="text" class="form-control" id="schedule_days" name="schedule_days" placeholder="e.g., Mon,Wed,Fri">
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex gap-2">
                                            <button type="submit" name="add_class" class="btn btn-primary">
                                                <i class="fas fa-save me-1"></i>Add Class
                                            </button>
                                            <a href="classes.php" class="btn btn-secondary">Cancel</a>
                                        </div>
                                    </form>
                                </div>
                            </div>

                        <?php elseif ($action === 'edit' && isset($class_edit)): ?>
                            <!-- Edit Class Form -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Class: <?php echo $class_edit['name']; ?></h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="">
                                        <input type="hidden" name="class_id" value="<?php echo $class_edit['id']; ?>">
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="name" class="form-label">Class Name *</label>
                                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($class_edit['name']); ?>" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="section" class="form-label">Section</label>
                                                <select class="form-select" id="section" name="section">
                                                    <option value="">Select Section</option>
                                                    <option value="A" <?php echo $class_edit['section'] === 'A' ? 'selected' : ''; ?>>A</option>
                                                    <option value="B" <?php echo $class_edit['section'] === 'B' ? 'selected' : ''; ?>>B</option>
                                                    <option value="C" <?php echo $class_edit['section'] === 'C' ? 'selected' : ''; ?>>C</option>
                                                    <option value="D" <?php echo $class_edit['section'] === 'D' ? 'selected' : ''; ?>>D</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="subject_id" class="form-label">Subject *</label>
                                                <select class="form-select" id="subject_id" name="subject_id" required>
                                                    <option value="<?php echo $class_edit['subject_id']; ?>" selected>
                                                        <?php echo $class_edit['subject_name']; ?>
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="teacher_id" class="form-label">Teacher</label>
                                                <select class="form-select" id="teacher_id" name="teacher_id">
                                                    <?php if ($class_edit['teacher_id']): ?>
                                                        <option value="<?php echo $class_edit['teacher_id']; ?>" selected>
                                                            <?php echo $class_edit['teacher_name']; ?>
                                                        </option>
                                                    <?php else: ?>
                                                        <option value="">Select Teacher</option>
                                                    <?php endif; ?>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label for="room_number" class="form-label">Room Number</label>
                                                <input type="text" class="form-control" id="room_number" name="room_number" value="<?php echo htmlspecialchars($class_edit['room_number']); ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="capacity" class="form-label">Capacity</label>
                                                <input type="number" class="form-control" id="capacity" name="capacity" value="<?php echo $class_edit['capacity']; ?>" min="1" max="100">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="fee" class="form-label">Fee</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">$</span>
                                                    <input type="number" class="form-control" id="fee" name="fee" value="<?php echo $class_edit['fee']; ?>" step="0.01" min="0">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="schedule_time" class="form-label">Schedule Time</label>
                                                <input type="time" class="form-control" id="schedule_time" name="schedule_time" value="<?php echo $class_edit['schedule_time']; ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="schedule_days" class="form-label">Schedule Days</label>
                                                <input type="text" class="form-control" id="schedule_days" name="schedule_days" value="<?php echo htmlspecialchars($class_edit['schedule_days']); ?>">
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex gap-2">
                                            <button type="submit" name="edit_class" class="btn btn-primary">
                                                <i class="fas fa-save me-1"></i>Update Class
                                            </button>
                                            <a href="classes.php" class="btn btn-secondary">Cancel</a>
                                        </div>
                                    </form>
                                </div>
                            </div>

                        <?php else: ?>
                            <!-- Classes List -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-door-open me-2"></i>All Classes</h5>
                                </div>
                                <div class="card-body">
                                    <?php if ($classes->num_rows > 0): ?>
                                        <div class="table-responsive">
                                            <table id="classesTable" class="table table-striped table-hover">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th>Class Name</th>
                                                        <th>Subject</th>
                                                        <th>Teacher</th>
                                                        <th>Section</th>
                                                        <th>Room</th>
                                                        <th>Students</th>
                                                        <th>Schedule</th>
                                                        <th>Fee</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $classes->data_seek(0); ?>
                                                    <?php while ($class = $classes->fetch_assoc()): ?>
                                                        <tr>
                                                            <td>
                                                                <div class="fw-bold"><?php echo $class['name']; ?></div>
                                                            </td>
                                                            <td>
                                                                <?php echo $class['subject_name']; ?><br>
                                                                <small class="text-muted"><?php echo $class['subject_code']; ?></small>
                                                            </td>
                                                            <td><?php echo $class['teacher_name'] ?? 'Not assigned'; ?></td>
                                                            <td><?php echo $class['section'] ?? '-'; ?></td>
                                                            <td><?php echo $class['room_number'] ?? '-'; ?></td>
                                                            <td>
                                                                <span class="badge bg-info"><?php echo $class['student_count']; ?></span>
                                                            </td>
                                                            <td>
                                                                <?php if ($class['schedule_time']): ?>
                                                                    <?php echo date('g:i A', strtotime($class['schedule_time'])); ?><br>
                                                                    <small class="text-muted"><?php echo $class['schedule_days']; ?></small>
                                                                <?php else: ?>
                                                                    <span class="text-muted">Not set</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td><?php echo $class['fee'] ? '$' . $class['fee'] : '-'; ?></td>
                                                            <td>
                                                                <div class="btn-group btn-group-sm" role="group">
                                                                    <a href="classes.php?action=edit&id=<?php echo $class['id']; ?>" class="btn btn-outline-success" title="Edit">
                                                                        <i class="fas fa-edit"></i>
                                                                    </a>
                                                                    <button type="button" class="btn btn-outline-danger" onclick="deleteClass(<?php echo $class['id']; ?>, '<?php echo addslashes($class['name']); ?>')" title="Delete">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endwhile; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-5">
                                            <i class="fas fa-door-open fa-4x text-muted mb-3"></i>
                                            <h5 class="text-muted">No classes found</h5>
                                            <p class="text-muted">Start by creating your first class.</p>
                                            <a href="classes.php?action=add" class="btn btn-primary">
                                                <i class="fas fa-plus me-1"></i>Add First Class
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete class <strong id="className"></strong>?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="" style="display: inline;">
                        <input type="hidden" name="class_id" id="deleteClassId">
                        <button type="submit" name="delete_class" class="btn btn-danger">Delete Class</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#classesTable').DataTable({
                responsive: true,
                pageLength: 10,
                order: [[0, 'asc']],
                columnDefs: [
                    { orderable: false, targets: -1 }
                ]
            });
        });

        function deleteClass(classId, className) {
            document.getElementById('className').textContent = className;
            document.getElementById('deleteClassId').value = classId;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
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