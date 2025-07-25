<?php
session_start();
require_once '../config.php';

// Check if user is logged in and is parent
if (!isLoggedIn() || !hasRole('parent')) {
    redirect('../login.php');
}

// Get children (students associated with this parent)
$children_query = "SELECT s.*, u.name, u.email
                   FROM students s
                   JOIN users u ON s.user_id = u.id
                   WHERE s.parent_id = ? AND s.status = 'active'";
$stmt = $conn->prepare($children_query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$children = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Dashboard - Excellence Tuition Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
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
        .child-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
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
                        <h4><i class="fas fa-users me-2"></i>Parent Portal</h4>
                        <p class="mb-0">Welcome, <?php echo $_SESSION['name']; ?></p>
                    </div>
                    
                    <nav class="nav flex-column px-3">
                        <a href="dashboard.php" class="nav-link active">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                        <a href="children.php" class="nav-link">
                            <i class="fas fa-child me-2"></i>My Children
                        </a>
                        <a href="attendance.php" class="nav-link">
                            <i class="fas fa-calendar-check me-2"></i>Attendance
                        </a>
                        <a href="grades.php" class="nav-link">
                            <i class="fas fa-chart-line me-2"></i>Grades & Results
                        </a>
                        <a href="fees.php" class="nav-link">
                            <i class="fas fa-money-bill-wave me-2"></i>Fees & Payments
                        </a>
                        <a href="communication.php" class="nav-link">
                            <i class="fas fa-comments me-2"></i>Communication
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
                            <h5 class="navbar-brand mb-0">Parent Dashboard</h5>
                            <div class="d-flex align-items-center">
                                <div class="dropdown">
                                    <button class="btn btn-outline-info dropdown-toggle" type="button" data-bs-toggle="dropdown">
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
                                    <h2 class="mb-3">Welcome back, <?php echo $_SESSION['name']; ?>! 👨‍👩‍👧‍👦</h2>
                                    <p class="text-muted mb-3">Monitor your children's academic progress and stay connected with their education.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Stats -->
                        <div class="row mb-4">
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="stat-card text-center">
                                    <i class="fas fa-child fa-2x text-info mb-3"></i>
                                    <h3 class="fw-bold text-info"><?php echo $children->num_rows; ?></h3>
                                    <p class="text-muted mb-0">Children Enrolled</p>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="stat-card text-center">
                                    <i class="fas fa-percentage fa-2x text-success mb-3"></i>
                                    <h3 class="fw-bold text-success">92%</h3>
                                    <p class="text-muted mb-0">Average Attendance</p>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="stat-card text-center">
                                    <i class="fas fa-star fa-2x text-warning mb-3"></i>
                                    <h3 class="fw-bold text-warning">A-</h3>
                                    <p class="text-muted mb-0">Average Grade</p>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="stat-card text-center">
                                    <i class="fas fa-dollar-sign fa-2x text-danger mb-3"></i>
                                    <h3 class="fw-bold text-danger">0</h3>
                                    <p class="text-muted mb-0">Pending Fees</p>
                                </div>
                            </div>
                        </div>

                        <!-- Children Overview -->
                        <div class="row">
                            <div class="col-12">
                                <h4 class="mb-4"><i class="fas fa-child me-2"></i>My Children</h4>
                                
                                <?php if ($children->num_rows > 0): ?>
                                    <?php while ($child = $children->fetch_assoc()): ?>
                                        <div class="child-card">
                                            <div class="row align-items-center">
                                                <div class="col-md-3">
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                                            <i class="fas fa-user-graduate fa-lg"></i>
                                                        </div>
                                                        <div>
                                                            <h5 class="mb-1"><?php echo $child['name']; ?></h5>
                                                            <small class="text-muted"><?php echo $child['student_id']; ?></small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <small class="text-muted d-block">Class</small>
                                                            <span><?php echo $child['class'] ?? 'Not assigned'; ?></span>
                                                        </div>
                                                        <div class="col-6">
                                                            <small class="text-muted d-block">Section</small>
                                                            <span><?php echo $child['section'] ?? 'Not assigned'; ?></span>
                                                        </div>
                                                    </div>
                                                    <div class="row mt-2">
                                                        <div class="col-6">
                                                            <small class="text-muted d-block">Roll Number</small>
                                                            <span><?php echo $child['roll_number'] ?? 'Not assigned'; ?></span>
                                                        </div>
                                                        <div class="col-6">
                                                            <small class="text-muted d-block">Status</small>
                                                            <span class="badge bg-success"><?php echo ucfirst($child['status']); ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3 text-end">
                                                    <div class="btn-group-vertical" role="group">
                                                        <a href="attendance.php?student_id=<?php echo $child['id']; ?>" class="btn btn-sm btn-outline-info mb-1">
                                                            <i class="fas fa-calendar-check me-1"></i>Attendance
                                                        </a>
                                                        <a href="grades.php?student_id=<?php echo $child['id']; ?>" class="btn btn-sm btn-outline-success mb-1">
                                                            <i class="fas fa-chart-line me-1"></i>Grades
                                                        </a>
                                                        <a href="fees.php?student_id=<?php echo $child['id']; ?>" class="btn btn-sm btn-outline-warning">
                                                            <i class="fas fa-money-bill me-1"></i>Fees
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="text-center py-5">
                                        <i class="fas fa-child fa-4x text-muted mb-3"></i>
                                        <h5 class="text-muted">No children enrolled</h5>
                                        <p class="text-muted">Please contact the school administration to register your children.</p>
                                        <a href="../register.php" class="btn btn-primary">Register New Student</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-white">
                                        <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3 mb-3">
                                                <a href="attendance.php" class="btn btn-outline-info w-100">
                                                    <i class="fas fa-calendar-check fa-2x d-block mb-2"></i>
                                                    View Attendance
                                                </a>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <a href="grades.php" class="btn btn-outline-success w-100">
                                                    <i class="fas fa-chart-line fa-2x d-block mb-2"></i>
                                                    Check Grades
                                                </a>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <a href="fees.php" class="btn btn-outline-warning w-100">
                                                    <i class="fas fa-money-bill-wave fa-2x d-block mb-2"></i>
                                                    Pay Fees
                                                </a>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <a href="communication.php" class="btn btn-outline-primary w-100">
                                                    <i class="fas fa-comments fa-2x d-block mb-2"></i>
                                                    Contact Teachers
                                                </a>
                                            </div>
                                        </div>
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