<?php
session_start();
require_once '../config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !hasRole('admin')) {
    redirect('../login.php');
}

// Get dashboard statistics
$stats = [];

// Total students
$result = $conn->query("SELECT COUNT(*) as count FROM students WHERE status = 'active'");
$stats['students'] = $result->fetch_assoc()['count'];

// Total teachers
$result = $conn->query("SELECT COUNT(*) as count FROM teachers WHERE status = 'active'");
$stats['teachers'] = $result->fetch_assoc()['count'];

// Total classes
$result = $conn->query("SELECT COUNT(*) as count FROM classes");
$stats['classes'] = $result->fetch_assoc()['count'];

// Total revenue (paid fees)
$result = $conn->query("SELECT SUM(paid_amount) as total FROM fees WHERE status = 'paid'");
$stats['revenue'] = $result->fetch_assoc()['total'] ?? 0;

// Recent registrations
$recent_query = "SELECT u.name, u.email, u.role, u.created_at 
                 FROM users u 
                 WHERE u.role != 'admin' 
                 ORDER BY u.created_at DESC 
                 LIMIT 5";
$recent_users = $conn->query($recent_query);

// Pending fees
$pending_fees_query = "SELECT u.name, f.amount, f.due_date, s.student_id
                       FROM fees f
                       JOIN students s ON f.student_id = s.id
                       JOIN users u ON s.user_id = u.id
                       WHERE f.status = 'pending' AND f.due_date <= CURDATE() + INTERVAL 7 DAY
                       ORDER BY f.due_date ASC
                       LIMIT 5";
$pending_fees = $conn->query($pending_fees_query);

// Recent announcements
$announcements_query = "SELECT a.title, a.content, a.created_at, u.name as created_by
                        FROM announcements a
                        JOIN users u ON a.created_by = u.id
                        ORDER BY a.created_at DESC
                        LIMIT 3";
$announcements = $conn->query($announcements_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Excellence Tuition Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js">
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
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .main-content {
            background: #f8f9fa;
            min-height: 100vh;
        }
        .navbar-brand {
            font-weight: bold;
        }
        .chart-container {
            position: relative;
            height: 300px;
        }
        .recent-item {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
                        <a href="dashboard.php" class="nav-link active">
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
                            <h5 class="navbar-brand mb-0">Dashboard Overview</h5>
                            <div class="d-flex">
                                <div class="dropdown">
                                    <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-plus me-1"></i>Quick Actions
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="students.php?action=add"><i class="fas fa-user-plus me-2"></i>Add Student</a></li>
                                        <li><a class="dropdown-item" href="teachers.php?action=add"><i class="fas fa-chalkboard-teacher me-2"></i>Add Teacher</a></li>
                                        <li><a class="dropdown-item" href="classes.php?action=add"><i class="fas fa-door-open me-2"></i>Add Class</a></li>
                                        <li><a class="dropdown-item" href="announcements.php?action=add"><i class="fas fa-bullhorn me-2"></i>New Announcement</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </nav>

                    <!-- Dashboard Content -->
                    <div class="container-fluid py-4">
                        <!-- Statistics Cards -->
                        <div class="row mb-4">
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="stat-card">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3 class="fw-bold text-primary"><?php echo $stats['students']; ?></h3>
                                            <p class="text-muted mb-0">Active Students</p>
                                        </div>
                                        <div class="text-primary">
                                            <i class="fas fa-user-graduate fa-2x"></i>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <small class="text-success"><i class="fas fa-arrow-up"></i> 12% from last month</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="stat-card">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3 class="fw-bold text-success"><?php echo $stats['teachers']; ?></h3>
                                            <p class="text-muted mb-0">Active Teachers</p>
                                        </div>
                                        <div class="text-success">
                                            <i class="fas fa-chalkboard-teacher fa-2x"></i>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <small class="text-success"><i class="fas fa-arrow-up"></i> 5% from last month</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="stat-card">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3 class="fw-bold text-info"><?php echo $stats['classes']; ?></h3>
                                            <p class="text-muted mb-0">Total Classes</p>
                                        </div>
                                        <div class="text-info">
                                            <i class="fas fa-door-open fa-2x"></i>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <small class="text-info"><i class="fas fa-arrow-up"></i> 2 new this week</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="stat-card">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3 class="fw-bold text-warning">$<?php echo number_format($stats['revenue'], 2); ?></h3>
                                            <p class="text-muted mb-0">Total Revenue</p>
                                        </div>
                                        <div class="text-warning">
                                            <i class="fas fa-dollar-sign fa-2x"></i>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <small class="text-success"><i class="fas fa-arrow-up"></i> 18% from last month</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Charts and Recent Activity -->
                        <div class="row">
                            <!-- Charts -->
                            <div class="col-lg-8 mb-4">
                                <div class="card">
                                    <div class="card-header bg-white">
                                        <h5 class="card-title mb-0">Monthly Overview</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="chart-container">
                                            <canvas id="monthlyChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Recent Activity -->
                            <div class="col-lg-4 mb-4">
                                <div class="card">
                                    <div class="card-header bg-white">
                                        <h5 class="card-title mb-0">Recent Registrations</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php while ($user = $recent_users->fetch_assoc()): ?>
                                            <div class="recent-item">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-1"><?php echo $user['name']; ?></h6>
                                                        <small class="text-muted"><?php echo ucfirst($user['role']); ?> • <?php echo $user['email']; ?></small>
                                                    </div>
                                                    <small class="text-muted"><?php echo date('M j', strtotime($user['created_at'])); ?></small>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pending Fees and Announcements -->
                        <div class="row">
                            <div class="col-lg-6 mb-4">
                                <div class="card">
                                    <div class="card-header bg-white">
                                        <h5 class="card-title mb-0">Pending Fees (Due Soon)</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if ($pending_fees->num_rows > 0): ?>
                                            <?php while ($fee = $pending_fees->fetch_assoc()): ?>
                                                <div class="recent-item">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <h6 class="mb-1"><?php echo $fee['name']; ?></h6>
                                                            <small class="text-muted">ID: <?php echo $fee['student_id']; ?></small>
                                                        </div>
                                                        <div class="text-end">
                                                            <div class="fw-bold text-danger">$<?php echo $fee['amount']; ?></div>
                                                            <small class="text-muted">Due: <?php echo date('M j', strtotime($fee['due_date'])); ?></small>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endwhile; ?>
                                            <div class="text-center mt-3">
                                                <a href="fees.php" class="btn btn-outline-primary btn-sm">View All Fees</a>
                                            </div>
                                        <?php else: ?>
                                            <p class="text-muted text-center">No pending fees due soon</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6 mb-4">
                                <div class="card">
                                    <div class="card-header bg-white">
                                        <h5 class="card-title mb-0">Recent Announcements</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if ($announcements->num_rows > 0): ?>
                                            <?php while ($announcement = $announcements->fetch_assoc()): ?>
                                                <div class="recent-item">
                                                    <h6 class="mb-1"><?php echo $announcement['title']; ?></h6>
                                                    <p class="text-muted mb-2"><?php echo substr($announcement['content'], 0, 100) . '...'; ?></p>
                                                    <small class="text-muted">By <?php echo $announcement['created_by']; ?> • <?php echo date('M j, Y', strtotime($announcement['created_at'])); ?></small>
                                                </div>
                                            <?php endwhile; ?>
                                            <div class="text-center mt-3">
                                                <a href="announcements.php" class="btn btn-outline-primary btn-sm">View All Announcements</a>
                                            </div>
                                        <?php else: ?>
                                            <p class="text-muted text-center">No recent announcements</p>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Monthly Overview Chart
        const ctx = document.getElementById('monthlyChart').getContext('2d');
        const monthlyChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Students Enrolled',
                    data: [12, 19, 23, 17, 25, 32, 28, 35, 42, 38, 45, 52],
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Revenue ($)',
                    data: [1200, 1900, 2300, 1700, 2500, 3200, 2800, 3500, 4200, 3800, 4500, 5200],
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Auto-refresh dashboard every 5 minutes
        setInterval(function() {
            location.reload();
        }, 300000);
    </script>
</body>
</html>