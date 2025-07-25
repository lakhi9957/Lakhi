<?php
session_start();
require_once '../config.php';

// Check if user is logged in and is student
if (!isLoggedIn() || !hasRole('student')) {
    redirect('../login.php');
}

// Get student info
$stmt = $conn->prepare("SELECT s.*, u.name, u.email, u.phone FROM students s JOIN users u ON s.user_id = u.id WHERE s.user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

// Get enrolled classes
$classes_query = "SELECT c.*, sub.name as subject_name, sub.code as subject_code, 
                         t.teacher_id, tu.name as teacher_name
                  FROM enrollments e
                  JOIN classes c ON e.class_id = c.id
                  JOIN subjects sub ON c.subject_id = sub.id
                  JOIN teachers t ON c.teacher_id = t.id
                  JOIN users tu ON t.user_id = tu.id
                  WHERE e.student_id = ? AND e.status = 'active'";
$stmt = $conn->prepare($classes_query);
$stmt->bind_param("i", $student['id']);
$stmt->execute();
$classes = $stmt->get_result();

// Get recent assignments
$assignments_query = "SELECT a.*, c.name as class_name, sub.name as subject_name,
                             CASE WHEN asub.id IS NOT NULL THEN 'submitted' ELSE 'pending' END as status
                      FROM assignments a
                      JOIN classes c ON a.class_id = c.id
                      JOIN subjects sub ON c.subject_id = sub.id
                      JOIN enrollments e ON c.id = e.class_id
                      LEFT JOIN assignment_submissions asub ON a.id = asub.assignment_id AND asub.student_id = ?
                      WHERE e.student_id = ? AND e.status = 'active'
                      ORDER BY a.due_date ASC
                      LIMIT 5";
$stmt = $conn->prepare($assignments_query);
$stmt->bind_param("ii", $student['id'], $student['id']);
$stmt->execute();
$assignments = $stmt->get_result();

// Get recent attendance
$attendance_query = "SELECT a.*, c.name as class_name, sub.name as subject_name
                     FROM attendance a
                     JOIN classes c ON a.class_id = c.id
                     JOIN subjects sub ON c.subject_id = sub.id
                     WHERE a.student_id = ?
                     ORDER BY a.date DESC
                     LIMIT 10";
$stmt = $conn->prepare($attendance_query);
$stmt->bind_param("i", $student['id']);
$stmt->execute();
$attendance = $stmt->get_result();

// Get pending fees
$fees_query = "SELECT f.*, c.name as class_name
               FROM fees f
               JOIN classes c ON f.class_id = c.id
               WHERE f.student_id = ? AND f.status = 'pending'
               ORDER BY f.due_date ASC";
$stmt = $conn->prepare($fees_query);
$stmt->bind_param("i", $student['id']);
$stmt->execute();
$pending_fees = $stmt->get_result();

// Get announcements
$announcements_query = "SELECT * FROM announcements 
                        WHERE target_audience IN ('all', 'students') 
                        AND (expires_at IS NULL OR expires_at > NOW())
                        ORDER BY created_at DESC 
                        LIMIT 3";
$announcements = $conn->query($announcements_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Excellence Tuition Center</title>
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
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            height: 100%;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .assignment-card {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #667eea;
        }
        .assignment-card.pending {
            border-left-color: #ffc107;
        }
        .assignment-card.submitted {
            border-left-color: #28a745;
        }
        .profile-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
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
                        <h4><i class="fas fa-user-graduate me-2"></i>Student Portal</h4>
                        <p class="mb-0">Welcome, <?php echo $_SESSION['name']; ?></p>
                    </div>
                    
                    <nav class="nav flex-column px-3">
                        <a href="dashboard.php" class="nav-link active">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                        <a href="classes.php" class="nav-link">
                            <i class="fas fa-door-open me-2"></i>My Classes
                        </a>
                        <a href="assignments.php" class="nav-link">
                            <i class="fas fa-clipboard-list me-2"></i>Assignments
                        </a>
                        <a href="exams.php" class="nav-link">
                            <i class="fas fa-file-alt me-2"></i>Exams & Results
                        </a>
                        <a href="attendance.php" class="nav-link">
                            <i class="fas fa-calendar-check me-2"></i>Attendance
                        </a>
                        <a href="fees.php" class="nav-link">
                            <i class="fas fa-money-bill-wave me-2"></i>Fees
                        </a>
                        <a href="timetable.php" class="nav-link">
                            <i class="fas fa-calendar me-2"></i>Timetable
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
                            <h5 class="navbar-brand mb-0">Student Dashboard</h5>
                            <div class="d-flex align-items-center">
                                <span class="me-3">ID: <?php echo $student['student_id']; ?></span>
                                <div class="dropdown">
                                    <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-user me-1"></i><?php echo $_SESSION['name']; ?>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                                        <li><a class="dropdown-item" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </nav>

                    <!-- Dashboard Content -->
                    <div class="container-fluid py-4">
                        <!-- Welcome Section -->
                        <div class="row mb-4">
                            <div class="col-lg-8">
                                <div class="stat-card">
                                    <h2 class="mb-3">Welcome back, <?php echo $_SESSION['name']; ?>! 👋</h2>
                                    <p class="text-muted mb-3">Here's what's happening with your studies today.</p>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="fas fa-book text-primary me-2"></i>
                                                <span>Class: <?php echo $student['class'] ?? 'Not assigned'; ?></span>
                                            </div>
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="fas fa-layer-group text-success me-2"></i>
                                                <span>Section: <?php echo $student['section'] ?? 'Not assigned'; ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="fas fa-id-card text-info me-2"></i>
                                                <span>Roll No: <?php echo $student['roll_number'] ?? 'Not assigned'; ?></span>
                                            </div>
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="fas fa-calendar text-warning me-2"></i>
                                                <span>Admission: <?php echo $student['admission_date'] ? date('M Y', strtotime($student['admission_date'])) : 'N/A'; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="profile-card">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                                        <i class="fas fa-user-graduate fa-2x"></i>
                                    </div>
                                    <h5><?php echo $_SESSION['name']; ?></h5>
                                    <p class="text-muted"><?php echo $student['student_id']; ?></p>
                                    <span class="badge bg-success">Active Student</span>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Stats -->
                        <div class="row mb-4">
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="stat-card text-center">
                                    <i class="fas fa-door-open fa-2x text-primary mb-3"></i>
                                    <h3 class="fw-bold text-primary"><?php echo $classes->num_rows; ?></h3>
                                    <p class="text-muted mb-0">Enrolled Classes</p>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="stat-card text-center">
                                    <i class="fas fa-clipboard-list fa-2x text-warning mb-3"></i>
                                    <h3 class="fw-bold text-warning"><?php echo $assignments->num_rows; ?></h3>
                                    <p class="text-muted mb-0">Pending Assignments</p>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="stat-card text-center">
                                    <i class="fas fa-money-bill-wave fa-2x text-danger mb-3"></i>
                                    <h3 class="fw-bold text-danger"><?php echo $pending_fees->num_rows; ?></h3>
                                    <p class="text-muted mb-0">Pending Fees</p>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="stat-card text-center">
                                    <i class="fas fa-percentage fa-2x text-success mb-3"></i>
                                    <h3 class="fw-bold text-success">95%</h3>
                                    <p class="text-muted mb-0">Attendance Rate</p>
                                </div>
                            </div>
                        </div>

                        <!-- Main Content Row -->
                        <div class="row">
                            <!-- Assignments -->
                            <div class="col-lg-6 mb-4">
                                <div class="card">
                                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Recent Assignments</h5>
                                        <a href="assignments.php" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye me-1"></i>View All
                                        </a>
                                    </div>
                                    <div class="card-body">
                                        <?php if ($assignments->num_rows > 0): ?>
                                            <?php $assignments->data_seek(0); // Reset pointer ?>
                                            <?php while ($assignment = $assignments->fetch_assoc()): ?>
                                                <div class="assignment-card <?php echo $assignment['status']; ?>">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            <h6 class="mb-1"><?php echo $assignment['title']; ?></h6>
                                                            <small class="text-muted"><?php echo $assignment['subject_name']; ?></small>
                                                            <p class="mb-2 small"><?php echo substr($assignment['description'], 0, 100) . '...'; ?></p>
                                                        </div>
                                                        <span class="badge bg-<?php echo $assignment['status'] === 'submitted' ? 'success' : 'warning'; ?>">
                                                            <?php echo ucfirst($assignment['status']); ?>
                                                        </span>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <small class="text-muted">Due: <?php echo date('M j, Y', strtotime($assignment['due_date'])); ?></small>
                                                        <a href="assignments.php" class="btn btn-sm btn-outline-primary">View</a>
                                                    </div>
                                                </div>
                                            <?php endwhile; ?>
                                            <div class="text-center">
                                                <a href="assignments.php" class="btn btn-outline-primary btn-sm">View All Assignments</a>
                                            </div>
                                        <?php else: ?>
                                            <p class="text-muted text-center">No assignments at this time</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Classes -->
                            <div class="col-lg-6 mb-4">
                                <div class="card">
                                    <div class="card-header bg-white">
                                        <h5 class="mb-0"><i class="fas fa-door-open me-2"></i>My Classes</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if ($classes->num_rows > 0): ?>
                                            <?php $classes->data_seek(0); // Reset pointer ?>
                                            <?php while ($class = $classes->fetch_assoc()): ?>
                                                <div class="d-flex justify-content-between align-items-center mb-3 p-3 border rounded">
                                                    <div>
                                                        <h6 class="mb-1"><?php echo $class['subject_name']; ?></h6>
                                                        <small class="text-muted">Teacher: <?php echo $class['teacher_name']; ?></small>
                                                        <br>
                                                        <small class="text-muted">Room: <?php echo $class['room_number'] ?? 'TBA'; ?></small>
                                                    </div>
                                                    <div class="text-end">
                                                        <span class="badge bg-info"><?php echo $class['schedule_time'] ? date('H:i', strtotime($class['schedule_time'])) : 'TBA'; ?></span>
                                                    </div>
                                                </div>
                                            <?php endwhile; ?>
                                            <div class="text-center">
                                                <a href="classes.php" class="btn btn-outline-primary btn-sm">View All Classes</a>
                                            </div>
                                        <?php else: ?>
                                            <p class="text-muted text-center">No classes enrolled</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Fees and Announcements -->
                        <div class="row">
                            <div class="col-lg-6 mb-4">
                                <div class="card">
                                    <div class="card-header bg-white">
                                        <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Pending Fees</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if ($pending_fees->num_rows > 0): ?>
                                            <?php while ($fee = $pending_fees->fetch_assoc()): ?>
                                                <div class="d-flex justify-content-between align-items-center mb-3 p-3 border rounded">
                                                    <div>
                                                        <h6 class="mb-1"><?php echo $fee['class_name']; ?></h6>
                                                        <small class="text-muted">Due: <?php echo date('M j, Y', strtotime($fee['due_date'])); ?></small>
                                                    </div>
                                                    <div class="text-end">
                                                        <div class="fw-bold text-danger">$<?php echo $fee['amount']; ?></div>
                                                        <button class="btn btn-sm btn-outline-primary">Pay Now</button>
                                                    </div>
                                                </div>
                                            <?php endwhile; ?>
                                            <div class="text-center">
                                                <a href="fees.php" class="btn btn-outline-primary btn-sm">View All Fees</a>
                                            </div>
                                        <?php else: ?>
                                            <p class="text-muted text-center">No pending fees</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6 mb-4">
                                <div class="card">
                                    <div class="card-header bg-white">
                                        <h5 class="mb-0"><i class="fas fa-bullhorn me-2"></i>Announcements</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if ($announcements->num_rows > 0): ?>
                                            <?php while ($announcement = $announcements->fetch_assoc()): ?>
                                                <div class="mb-3 p-3 border rounded">
                                                    <h6 class="mb-1"><?php echo $announcement['title']; ?></h6>
                                                    <p class="mb-2 small"><?php echo substr($announcement['content'], 0, 150) . '...'; ?></p>
                                                    <small class="text-muted"><?php echo date('M j, Y', strtotime($announcement['created_at'])); ?></small>
                                                </div>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <p class="text-muted text-center">No announcements</p>
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
        // Auto-refresh dashboard every 10 minutes
        setInterval(function() {
            location.reload();
        }, 600000);
    </script>
</body>
</html>