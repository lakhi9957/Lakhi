<?php
session_start();
require_once '../config.php';

// Check if user is logged in and is teacher
if (!isLoggedIn() || !hasRole('teacher')) {
    redirect('../login.php');
}

// Get teacher info
$stmt = $conn->prepare("SELECT t.*, u.name, u.email, u.phone FROM teachers t JOIN users u ON t.user_id = u.id WHERE t.user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$teacher = $stmt->get_result()->fetch_assoc();

// Get assigned classes
$classes_query = "SELECT c.*, sub.name as subject_name, COUNT(e.id) as student_count
                  FROM classes c
                  JOIN subjects sub ON c.subject_id = sub.id
                  LEFT JOIN enrollments e ON c.id = e.class_id AND e.status = 'active'
                  WHERE c.teacher_id = ?
                  GROUP BY c.id";
$stmt = $conn->prepare($classes_query);
$stmt->bind_param("i", $teacher['id']);
$stmt->execute();
$classes = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - Excellence Tuition Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
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
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <div class="sidebar">
                    <div class="text-center py-4">
                        <h4><i class="fas fa-chalkboard-teacher me-2"></i>Teacher Portal</h4>
                        <p class="mb-0">Welcome, <?php echo $_SESSION['name']; ?></p>
                    </div>
                    
                    <nav class="nav flex-column px-3">
                        <a href="dashboard.php" class="nav-link active">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                        <a href="classes.php" class="nav-link">
                            <i class="fas fa-door-open me-2"></i>My Classes
                        </a>
                        <a href="students.php" class="nav-link">
                            <i class="fas fa-user-graduate me-2"></i>Students
                        </a>
                        <a href="assignments.php" class="nav-link">
                            <i class="fas fa-clipboard-list me-2"></i>Assignments
                        </a>
                        <a href="attendance.php" class="nav-link">
                            <i class="fas fa-calendar-check me-2"></i>Attendance
                        </a>
                        <a href="grades.php" class="nav-link">
                            <i class="fas fa-chart-line me-2"></i>Grades
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
                            <h5 class="navbar-brand mb-0">Teacher Dashboard</h5>
                            <div class="d-flex align-items-center">
                                <span class="me-3">ID: <?php echo $teacher['teacher_id']; ?></span>
                                <div class="dropdown">
                                    <button class="btn btn-outline-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
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
                            <div class="col-12">
                                <div class="stat-card">
                                    <h2 class="mb-3">Welcome back, <?php echo $_SESSION['name']; ?>! 👋</h2>
                                    <p class="text-muted mb-3">Ready to inspire and educate your students today?</p>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="fas fa-id-badge text-success me-2"></i>
                                                <span>ID: <?php echo $teacher['teacher_id']; ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="fas fa-graduation-cap text-info me-2"></i>
                                                <span>Qualification: <?php echo $teacher['qualification'] ?? 'Not specified'; ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="fas fa-calendar text-warning me-2"></i>
                                                <span>Experience: <?php echo $teacher['experience_years'] ?? '0'; ?> years</span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="fas fa-star text-primary me-2"></i>
                                                <span>Specialization: <?php echo $teacher['specialization'] ?? 'General'; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Stats -->
                        <div class="row mb-4">
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="stat-card text-center">
                                    <i class="fas fa-door-open fa-2x text-success mb-3"></i>
                                    <h3 class="fw-bold text-success"><?php echo $classes->num_rows; ?></h3>
                                    <p class="text-muted mb-0">Assigned Classes</p>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="stat-card text-center">
                                    <i class="fas fa-users fa-2x text-info mb-3"></i>
                                    <h3 class="fw-bold text-info">0</h3>
                                    <p class="text-muted mb-0">Total Students</p>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="stat-card text-center">
                                    <i class="fas fa-clipboard-list fa-2x text-warning mb-3"></i>
                                    <h3 class="fw-bold text-warning">0</h3>
                                    <p class="text-muted mb-0">Assignments</p>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="stat-card text-center">
                                    <i class="fas fa-chart-line fa-2x text-primary mb-3"></i>
                                    <h3 class="fw-bold text-primary">95%</h3>
                                    <p class="text-muted mb-0">Average Grade</p>
                                </div>
                            </div>
                        </div>

                        <!-- Classes -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-white">
                                        <h5 class="mb-0"><i class="fas fa-door-open me-2"></i>My Classes</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if ($classes->num_rows > 0): ?>
                                            <div class="row">
                                                <?php while ($class = $classes->fetch_assoc()): ?>
                                                    <div class="col-md-6 col-lg-4 mb-3">
                                                        <div class="card border-success">
                                                            <div class="card-body">
                                                                <h6 class="card-title"><?php echo $class['subject_name']; ?></h6>
                                                                <p class="card-text">
                                                                    <small class="text-muted">
                                                                        Room: <?php echo $class['room_number'] ?? 'TBA'; ?><br>
                                                                        Students: <?php echo $class['student_count']; ?><br>
                                                                        Time: <?php echo $class['schedule_time'] ? date('H:i', strtotime($class['schedule_time'])) : 'TBA'; ?>
                                                                    </small>
                                                                </p>
                                                                <div class="d-flex gap-2">
                                                                    <a href="students.php?class_id=<?php echo $class['id']; ?>" class="btn btn-sm btn-outline-success">View Students</a>
                                                                    <a href="attendance.php?class_id=<?php echo $class['id']; ?>" class="btn btn-sm btn-outline-info">Attendance</a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endwhile; ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-center py-5">
                                                <i class="fas fa-door-open fa-4x text-muted mb-3"></i>
                                                <h5 class="text-muted">No classes assigned yet</h5>
                                                <p class="text-muted">Please contact the administrator to get assigned to classes.</p>
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
</body>
</html>