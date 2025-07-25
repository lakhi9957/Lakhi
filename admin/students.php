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

// Handle form submissions
if ($_POST) {
    if (isset($_POST['add_student'])) {
        $name = sanitize($_POST['name']);
        $email = sanitize($_POST['email']);
        $phone = sanitize($_POST['phone']);
        $password = $_POST['password'];
        $class = sanitize($_POST['class']);
        $section = sanitize($_POST['section']);
        $roll_number = sanitize($_POST['roll_number']);
        $date_of_birth = sanitize($_POST['date_of_birth']);
        $address = sanitize($_POST['address']);
        
        if (empty($name) || empty($email) || empty($password) || empty($class)) {
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
                $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, role, address, verified) VALUES (?, ?, ?, ?, 'student', ?, 1)");
                $stmt->bind_param("sssss", $name, $email, $phone, $hashed_password, $address);
                
                if ($stmt->execute()) {
                    $user_id = $conn->insert_id;
                    $student_id = 'STD' . str_pad($user_id, 4, '0', STR_PAD_LEFT);
                    
                    // Insert student record
                    $stmt = $conn->prepare("INSERT INTO students (user_id, student_id, class, section, roll_number, date_of_birth, admission_date) VALUES (?, ?, ?, ?, ?, ?, CURDATE())");
                    $stmt->bind_param("isssss", $user_id, $student_id, $class, $section, $roll_number, $date_of_birth);
                    
                    if ($stmt->execute()) {
                        $success = 'Student added successfully!';
                    } else {
                        $error = 'Failed to create student record.';
                    }
                } else {
                    $error = 'Failed to create user account.';
                }
            }
        }
    }
}

// Get students list
if ($action === 'list' || $action === 'add') {
    $students_query = "SELECT s.*, u.name, u.email, u.phone, u.address 
                       FROM students s 
                       JOIN users u ON s.user_id = u.id 
                       WHERE s.status = 'active'
                       ORDER BY u.name ASC";
    $students = $conn->query($students_query);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management - Excellence Tuition Center</title>
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
                        <a href="students.php" class="nav-link active">
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
                            <h5 class="navbar-brand mb-0">Student Management</h5>
                            <div class="d-flex">
                                <?php if ($action === 'list'): ?>
                                    <a href="students.php?action=add" class="btn btn-primary">
                                        <i class="fas fa-plus me-1"></i>Add Student
                                    </a>
                                <?php else: ?>
                                    <a href="students.php" class="btn btn-outline-secondary">
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
                            <!-- Add Student Form -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Add New Student</h5>
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
                                            <div class="col-md-4 mb-3">
                                                <label for="class" class="form-label">Class *</label>
                                                <select class="form-select" id="class" name="class" required>
                                                    <option value="">Select Class</option>
                                                    <option value="Grade 6">Grade 6</option>
                                                    <option value="Grade 7">Grade 7</option>
                                                    <option value="Grade 8">Grade 8</option>
                                                    <option value="Grade 9">Grade 9</option>
                                                    <option value="Grade 10">Grade 10</option>
                                                    <option value="Grade 11">Grade 11</option>
                                                    <option value="Grade 12">Grade 12</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="section" class="form-label">Section</label>
                                                <select class="form-select" id="section" name="section">
                                                    <option value="">Select Section</option>
                                                    <option value="A">A</option>
                                                    <option value="B">B</option>
                                                    <option value="C">C</option>
                                                    <option value="D">D</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="roll_number" class="form-label">Roll Number</label>
                                                <input type="text" class="form-control" id="roll_number" name="roll_number">
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="date_of_birth" class="form-label">Date of Birth</label>
                                                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth">
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="address" class="form-label">Address</label>
                                            <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                                        </div>
                                        
                                        <div class="d-flex gap-2">
                                            <button type="submit" name="add_student" class="btn btn-primary">
                                                <i class="fas fa-save me-1"></i>Add Student
                                            </button>
                                            <a href="students.php" class="btn btn-secondary">Cancel</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Students List -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>All Students</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="studentsTable" class="table table-striped table-hover">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Student ID</th>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Class</th>
                                                    <th>Section</th>
                                                    <th>Phone</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($student = $students->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><?php echo $student['student_id']; ?></td>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px;">
                                                                    <?php echo strtoupper(substr($student['name'], 0, 1)); ?>
                                                                </div>
                                                                <?php echo $student['name']; ?>
                                                            </div>
                                                        </td>
                                                        <td><?php echo $student['email']; ?></td>
                                                        <td><?php echo $student['class'] ?? '-'; ?></td>
                                                        <td><?php echo $student['section'] ?? '-'; ?></td>
                                                        <td><?php echo $student['phone'] ?? '-'; ?></td>
                                                        <td>
                                                            <span class="badge bg-<?php echo $student['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                                <?php echo ucfirst($student['status']); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="btn-group btn-group-sm" role="group">
                                                                <button type="button" class="btn btn-outline-primary" title="View Details">
                                                                    <i class="fas fa-eye"></i>
                                                                </button>
                                                                <button type="button" class="btn btn-outline-success" title="Edit">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <button type="button" class="btn btn-outline-danger" title="Delete">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#studentsTable').DataTable({
                responsive: true,
                pageLength: 10,
                order: [[1, 'asc']],
                columnDefs: [
                    { orderable: false, targets: -1 }
                ]
            });
        });

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