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
$teacher_id = $_GET['id'] ?? null;

// Handle form submissions
if ($_POST) {
    if (isset($_POST['add_teacher'])) {
        $name = sanitize($_POST['name']);
        $email = sanitize($_POST['email']);
        $phone = sanitize($_POST['phone']);
        $password = $_POST['password'];
        $qualification = sanitize($_POST['qualification']);
        $experience_years = sanitize($_POST['experience_years']);
        $specialization = sanitize($_POST['specialization']);
        $salary = sanitize($_POST['salary']);
        $address = sanitize($_POST['address']);
        
        if (empty($name) || empty($email) || empty($password) || empty($qualification)) {
            $error = 'Please fill in all required fields.';
        } else {
            // Check if email exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = 'Email address already exists.';
            } else {
                // Insert user
                $hashed_password = hashPassword($password);
                $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, role, address, verified) VALUES (?, ?, ?, ?, 'teacher', ?, 1)");
                $stmt->bind_param("sssss", $name, $email, $phone, $hashed_password, $address);
                
                if ($stmt->execute()) {
                    $user_id = $conn->insert_id;
                    $teacher_code = 'TCH' . str_pad($user_id, 4, '0', STR_PAD_LEFT);
                    
                    // Insert teacher record
                    $stmt = $conn->prepare("INSERT INTO teachers (user_id, teacher_id, qualification, experience_years, specialization, salary, join_date) VALUES (?, ?, ?, ?, ?, ?, CURDATE())");
                    $stmt->bind_param("ississ", $user_id, $teacher_code, $qualification, $experience_years, $specialization, $salary);
                    
                    if ($stmt->execute()) {
                        $success = 'Teacher added successfully!';
                    } else {
                        $error = 'Failed to create teacher record.';
                    }
                } else {
                    $error = 'Failed to create user account.';
                }
            }
        }
    }
    
    if (isset($_POST['edit_teacher'])) {
        $user_id = sanitize($_POST['user_id']);
        $teacher_db_id = sanitize($_POST['teacher_db_id']);
        $name = sanitize($_POST['name']);
        $email = sanitize($_POST['email']);
        $phone = sanitize($_POST['phone']);
        $qualification = sanitize($_POST['qualification']);
        $experience_years = sanitize($_POST['experience_years']);
        $specialization = sanitize($_POST['specialization']);
        $salary = sanitize($_POST['salary']);
        $address = sanitize($_POST['address']);
        $status = sanitize($_POST['status']);
        
        if (empty($name) || empty($email) || empty($qualification)) {
            $error = 'Please fill in all required fields.';
        } else {
            // Update user
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $name, $email, $phone, $address, $user_id);
            
                         if ($stmt->execute()) {
                 // Update teacher record
                                   $stmt = $conn->prepare("UPDATE teachers SET qualification = ?, experience_years = ?, specialization = ?, salary = ?, status = ? WHERE id = ?");
                                     $stmt->bind_param("sisssi", $qualification, $experience_years, $specialization, $salary, $status, $teacher_db_id);
                
                if ($stmt->execute()) {
                    $success = 'Teacher updated successfully!';
                } else {
                    $error = 'Failed to update teacher record.';
                }
            } else {
                $error = 'Failed to update user account.';
            }
        }
    }
    
    if (isset($_POST['delete_teacher'])) {
        $teacher_db_id = sanitize($_POST['teacher_db_id']);
        $user_id = sanitize($_POST['user_id']);
        
        // Set teacher status to inactive instead of deleting
        $stmt = $conn->prepare("UPDATE teachers SET status = 'inactive' WHERE id = ?");
        $stmt->bind_param("i", $teacher_db_id);
        
        if ($stmt->execute()) {
            $success = 'Teacher deactivated successfully!';
        } else {
            $error = 'Failed to deactivate teacher.';
        }
    }
}

// Get teacher details for editing
if ($action === 'edit' && $teacher_id) {
    $stmt = $conn->prepare("SELECT t.*, u.name, u.email, u.phone, u.address FROM teachers t JOIN users u ON t.user_id = u.id WHERE t.id = ?");
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $teacher_edit = $stmt->get_result()->fetch_assoc();
}

// Get teachers list
if ($action === 'list' || $action === 'add' || $action === 'edit') {
    $teachers_query = "SELECT t.*, u.name, u.email, u.phone, u.address,
                              COUNT(c.id) as class_count
                       FROM teachers t 
                       JOIN users u ON t.user_id = u.id 
                       LEFT JOIN classes c ON t.id = c.teacher_id
                       GROUP BY t.id
                       ORDER BY u.name ASC";
    $teachers = $conn->query($teachers_query);
}

// Get subjects for class assignment
$subjects = $conn->query("SELECT * FROM subjects ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Management - Excellence Tuition Center</title>
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
        .teacher-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #28a745, #20c997);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        .status-badge {
            font-size: 0.8rem;
        }
        .teacher-card {
            transition: transform 0.3s ease;
        }
        .teacher-card:hover {
            transform: translateY(-2px);
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
                        <a href="teachers.php" class="nav-link active">
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
                            <h5 class="navbar-brand mb-0">Teacher Management</h5>
                            <div class="d-flex">
                                <?php if ($action === 'list'): ?>
                                    <a href="teachers.php?action=add" class="btn btn-success me-2">
                                        <i class="fas fa-plus me-1"></i>Add Teacher
                                    </a>
                                    <div class="dropdown">
                                        <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-download me-1"></i>Export
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#"><i class="fas fa-file-excel me-2"></i>Excel</a></li>
                                            <li><a class="dropdown-item" href="#"><i class="fas fa-file-pdf me-2"></i>PDF</a></li>
                                            <li><a class="dropdown-item" href="#"><i class="fas fa-file-csv me-2"></i>CSV</a></li>
                                        </ul>
                                    </div>
                                <?php else: ?>
                                    <a href="teachers.php" class="btn btn-outline-secondary">
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
                            <!-- Add Teacher Form -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Add New Teacher</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="name" class="form-label">Full Name *</label>
                                                <input type="text" class="form-control" id="name" name="name" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="email" class="form-label">Email Address *</label>
                                                <input type="email" class="form-control" id="email" name="email" required>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="phone" class="form-label">Phone Number</label>
                                                <input type="tel" class="form-control" id="phone" name="phone">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="password" class="form-label">Password *</label>
                                                <input type="password" class="form-control" id="password" name="password" required>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="qualification" class="form-label">Qualification *</label>
                                                <input type="text" class="form-control" id="qualification" name="qualification" placeholder="e.g., M.Sc. Mathematics" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="experience_years" class="form-label">Experience (Years)</label>
                                                <input type="number" class="form-control" id="experience_years" name="experience_years" min="0" max="50">
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="specialization" class="form-label">Specialization</label>
                                                <input type="text" class="form-control" id="specialization" name="specialization" placeholder="e.g., Mathematics, Physics">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="salary" class="form-label">Salary</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">$</span>
                                                    <input type="number" class="form-control" id="salary" name="salary" step="0.01" min="0">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="address" class="form-label">Address</label>
                                            <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                                        </div>
                                        
                                        <div class="d-flex gap-2">
                                            <button type="submit" name="add_teacher" class="btn btn-success">
                                                <i class="fas fa-save me-1"></i>Add Teacher
                                            </button>
                                            <a href="teachers.php" class="btn btn-secondary">Cancel</a>
                                        </div>
                                    </form>
                                </div>
                            </div>

                        <?php elseif ($action === 'edit' && isset($teacher_edit)): ?>
                            <!-- Edit Teacher Form -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Teacher: <?php echo $teacher_edit['name']; ?></h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="">
                                        <input type="hidden" name="user_id" value="<?php echo $teacher_edit['user_id']; ?>">
                                        <input type="hidden" name="teacher_db_id" value="<?php echo $teacher_edit['id']; ?>">
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="name" class="form-label">Full Name *</label>
                                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($teacher_edit['name']); ?>" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="email" class="form-label">Email Address *</label>
                                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($teacher_edit['email']); ?>" required>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="phone" class="form-label">Phone Number</label>
                                                <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($teacher_edit['phone']); ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="status" class="form-label">Status</label>
                                                <select class="form-select" id="status" name="status">
                                                    <option value="active" <?php echo $teacher_edit['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                                    <option value="inactive" <?php echo $teacher_edit['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="qualification" class="form-label">Qualification *</label>
                                                <input type="text" class="form-control" id="qualification" name="qualification" value="<?php echo htmlspecialchars($teacher_edit['qualification']); ?>" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="experience_years" class="form-label">Experience (Years)</label>
                                                <input type="number" class="form-control" id="experience_years" name="experience_years" value="<?php echo $teacher_edit['experience_years']; ?>" min="0" max="50">
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="specialization" class="form-label">Specialization</label>
                                                <input type="text" class="form-control" id="specialization" name="specialization" value="<?php echo htmlspecialchars($teacher_edit['specialization']); ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="salary" class="form-label">Salary</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">$</span>
                                                    <input type="number" class="form-control" id="salary" name="salary" value="<?php echo $teacher_edit['salary']; ?>" step="0.01" min="0">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="address" class="form-label">Address</label>
                                            <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($teacher_edit['address']); ?></textarea>
                                        </div>
                                        
                                        <div class="d-flex gap-2">
                                            <button type="submit" name="edit_teacher" class="btn btn-primary">
                                                <i class="fas fa-save me-1"></i>Update Teacher
                                            </button>
                                            <a href="teachers.php" class="btn btn-secondary">Cancel</a>
                                        </div>
                                    </form>
                                </div>
                            </div>

                        <?php else: ?>
                            <!-- Teachers List -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-chalkboard-teacher me-2"></i>All Teachers</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="teachersTable" class="table table-striped table-hover">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Teacher ID</th>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Qualification</th>
                                                    <th>Specialization</th>
                                                    <th>Experience</th>
                                                    <th>Classes</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $teachers->data_seek(0); ?>
                                                <?php while ($teacher = $teachers->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><?php echo $teacher['teacher_id']; ?></td>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="teacher-avatar me-2">
                                                                    <?php echo strtoupper(substr($teacher['name'], 0, 1)); ?>
                                                                </div>
                                                                <div>
                                                                    <div class="fw-bold"><?php echo $teacher['name']; ?></div>
                                                                    <small class="text-muted"><?php echo $teacher['phone'] ?? 'No phone'; ?></small>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td><?php echo $teacher['email']; ?></td>
                                                        <td><?php echo $teacher['qualification'] ?? '-'; ?></td>
                                                        <td><?php echo $teacher['specialization'] ?? '-'; ?></td>
                                                        <td><?php echo $teacher['experience_years'] ? $teacher['experience_years'] . ' years' : '-'; ?></td>
                                                        <td>
                                                            <span class="badge bg-info"><?php echo $teacher['class_count']; ?> classes</span>
                                                        </td>
                                                        <td>
                                                            <span class="badge status-badge bg-<?php echo $teacher['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                                <?php echo ucfirst($teacher['status']); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="btn-group btn-group-sm" role="group">
                                                                <button type="button" class="btn btn-outline-info" onclick="viewTeacher(<?php echo $teacher['id']; ?>)" title="View Details">
                                                                    <i class="fas fa-eye"></i>
                                                                </button>
                                                                <a href="teachers.php?action=edit&id=<?php echo $teacher['id']; ?>" class="btn btn-outline-success" title="Edit">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                                <button type="button" class="btn btn-outline-danger" onclick="deleteTeacher(<?php echo $teacher['id']; ?>, '<?php echo $teacher['name']; ?>', <?php echo $teacher['user_id']; ?>)" title="Delete">
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

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to deactivate teacher <strong id="teacherName"></strong>?</p>
                    <p class="text-muted">This action will set the teacher status to inactive.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="" style="display: inline;">
                        <input type="hidden" name="teacher_db_id" id="deleteTeacherId">
                        <input type="hidden" name="user_id" id="deleteUserId">
                        <button type="submit" name="delete_teacher" class="btn btn-danger">Deactivate Teacher</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- View Teacher Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Teacher Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="viewModalBody">
                    <!-- Content loaded via JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
            $('#teachersTable').DataTable({
                responsive: true,
                pageLength: 10,
                order: [[1, 'asc']],
                columnDefs: [
                    { orderable: false, targets: -1 }
                ]
            });
        });

        function deleteTeacher(teacherId, teacherName, userId) {
            document.getElementById('teacherName').textContent = teacherName;
            document.getElementById('deleteTeacherId').value = teacherId;
            document.getElementById('deleteUserId').value = userId;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }

        function viewTeacher(teacherId) {
            // Load teacher details via AJAX
            fetch(`get_teacher_details.php?id=${teacherId}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('viewModalBody').innerHTML = data;
                    new bootstrap.Modal(document.getElementById('viewModal')).show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('viewModalBody').innerHTML = '<p class="text-danger">Error loading teacher details.</p>';
                    new bootstrap.Modal(document.getElementById('viewModal')).show();
                });
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