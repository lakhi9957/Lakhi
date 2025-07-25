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
    if (isset($_POST['add_subject'])) {
        $name = sanitize($_POST['name']);
        $code = sanitize($_POST['code']);
        $description = sanitize($_POST['description']);
        $class_level = sanitize($_POST['class_level']);
        $credits = sanitize($_POST['credits']);
        
        if (empty($name) || empty($code)) {
            $error = 'Please fill in all required fields.';
        } else {
            // Check if code exists
            $stmt = $conn->prepare("SELECT id FROM subjects WHERE code = ?");
            $stmt->bind_param("s", $code);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = 'Subject code already exists.';
            } else {
                $stmt = $conn->prepare("INSERT INTO subjects (name, code, description, class_level, credits) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssi", $name, $code, $description, $class_level, $credits);
                
                if ($stmt->execute()) {
                    $success = 'Subject added successfully!';
                } else {
                    $error = 'Failed to add subject.';
                }
            }
        }
    }
}

// Get subjects list
$subjects = $conn->query("SELECT * FROM subjects ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subject Management - Excellence Tuition Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
                        <a href="classes.php" class="nav-link">
                            <i class="fas fa-door-open me-2"></i>Classes
                        </a>
                        <a href="subjects.php" class="nav-link active">
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
                            <h5 class="navbar-brand mb-0">Subject Management</h5>
                            <div class="d-flex">
                                <a href="classes.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-1"></i>Back to Classes
                                </a>
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

                        <div class="row">
                            <!-- Add Subject Form -->
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Add New Subject</h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" action="">
                                            <div class="mb-3">
                                                <label for="name" class="form-label">Subject Name *</label>
                                                <input type="text" class="form-control" id="name" name="name" placeholder="e.g., Mathematics" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="code" class="form-label">Subject Code *</label>
                                                <input type="text" class="form-control" id="code" name="code" placeholder="e.g., MATH101" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="class_level" class="form-label">Class Level</label>
                                                <select class="form-select" id="class_level" name="class_level">
                                                    <option value="">Select Level</option>
                                                    <option value="Grade 6">Grade 6</option>
                                                    <option value="Grade 7">Grade 7</option>
                                                    <option value="Grade 8">Grade 8</option>
                                                    <option value="Grade 9">Grade 9</option>
                                                    <option value="Grade 10">Grade 10</option>
                                                    <option value="Grade 11">Grade 11</option>
                                                    <option value="Grade 12">Grade 12</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="credits" class="form-label">Credits</label>
                                                <input type="number" class="form-control" id="credits" name="credits" value="1" min="1" max="10">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="description" class="form-label">Description</label>
                                                <textarea class="form-control" id="description" name="description" rows="3" placeholder="Brief description of the subject"></textarea>
                                            </div>
                                            
                                            <button type="submit" name="add_subject" class="btn btn-primary w-100">
                                                <i class="fas fa-save me-1"></i>Add Subject
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Subjects List -->
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-book me-2"></i>All Subjects</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if ($subjects->num_rows > 0): ?>
                                            <div class="table-responsive">
                                                <table class="table table-striped table-hover">
                                                    <thead class="table-dark">
                                                        <tr>
                                                            <th>Name</th>
                                                            <th>Code</th>
                                                            <th>Level</th>
                                                            <th>Credits</th>
                                                            <th>Description</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php while ($subject = $subjects->fetch_assoc()): ?>
                                                            <tr>
                                                                <td>
                                                                    <div class="fw-bold"><?php echo $subject['name']; ?></div>
                                                                </td>
                                                                <td>
                                                                    <span class="badge bg-primary"><?php echo $subject['code']; ?></span>
                                                                </td>
                                                                <td><?php echo $subject['class_level'] ?? '-'; ?></td>
                                                                <td><?php echo $subject['credits']; ?></td>
                                                                <td><?php echo $subject['description'] ? substr($subject['description'], 0, 50) . '...' : '-'; ?></td>
                                                            </tr>
                                                        <?php endwhile; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-center py-4">
                                                <i class="fas fa-book fa-3x text-muted mb-3"></i>
                                                <h5 class="text-muted">No subjects found</h5>
                                                <p class="text-muted">Add your first subject using the form on the left.</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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