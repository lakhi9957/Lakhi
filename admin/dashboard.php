<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

include '../config.php';

// Get dashboard statistics
$stats = [];

// Total jobs
$result = $conn->query("SELECT COUNT(*) as count FROM jobs");
$stats['total_jobs'] = $result->fetch_assoc()['count'];

// Active jobs
$result = $conn->query("SELECT COUNT(*) as count FROM jobs WHERE status = 'active'");
$stats['active_jobs'] = $result->fetch_assoc()['count'];

// Total applications
$result = $conn->query("SELECT COUNT(*) as count FROM job_applications");
$stats['total_applications'] = $result->fetch_assoc()['count'];

// Pending applications
$result = $conn->query("SELECT COUNT(*) as count FROM job_applications WHERE status = 'pending'");
$stats['pending_applications'] = $result->fetch_assoc()['count'];

// Recent jobs
$recent_jobs = [];
$result = $conn->query("SELECT j.*, COUNT(ja.id) as application_count 
                       FROM jobs j 
                       LEFT JOIN job_applications ja ON j.id = ja.job_id 
                       GROUP BY j.id 
                       ORDER BY j.posted_date DESC 
                       LIMIT 5");
while ($row = $result->fetch_assoc()) {
    $recent_jobs[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Job Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 0;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 15px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255,255,255,0.1);
        }
        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-3px);
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .main-content {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .admin-header {
            background: white;
            padding: 20px;
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 30px;
        }
        .job-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 4px 8px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <div class="d-flex flex-column">
                    <div class="text-center py-4">
                        <h4 class="text-white">
                            <i class="fas fa-briefcase me-2"></i>Job Portal
                        </h4>
                        <small class="text-white-50">Admin Panel</small>
                    </div>
                    
                    <nav class="nav nav-pills flex-column">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i>Dashboard
                        </a>
                        <a class="nav-link" href="jobs.php">
                            <i class="fas fa-briefcase"></i>Manage Jobs
                        </a>
                        <a class="nav-link" href="applications.php">
                            <i class="fas fa-file-alt"></i>Applications
                        </a>
                        <a class="nav-link" href="add-job.php">
                            <i class="fas fa-plus"></i>Add New Job
                        </a>
                        <a class="nav-link" href="settings.php">
                            <i class="fas fa-cog"></i>Settings
                        </a>
                    </nav>
                    
                    <div class="mt-auto p-3">
                        <div class="text-center">
                            <p class="text-white-50 mb-2">Welcome</p>
                            <p class="text-white"><?php echo htmlspecialchars($_SESSION['admin_name']); ?></p>
                            <a href="logout.php" class="btn btn-outline-light btn-sm">
                                <i class="fas fa-sign-out-alt me-1"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="admin-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h2>
                        <div>
                            <a href="../index.html" class="btn btn-outline-secondary me-2" target="_blank">
                                <i class="fas fa-external-link-alt me-1"></i>View Site
                            </a>
                            <span class="badge bg-success">Online</span>
                        </div>
                    </div>
                </div>
                
                <div class="container-fluid">
                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="stat-card text-center">
                                <div class="stat-number text-primary"><?php echo $stats['total_jobs']; ?></div>
                                <div class="stat-label">Total Jobs</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stat-card text-center">
                                <div class="stat-number text-success"><?php echo $stats['active_jobs']; ?></div>
                                <div class="stat-label">Active Jobs</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stat-card text-center">
                                <div class="stat-number text-info"><?php echo $stats['total_applications']; ?></div>
                                <div class="stat-label">Total Applications</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stat-card text-center">
                                <div class="stat-number text-warning"><?php echo $stats['pending_applications']; ?></div>
                                <div class="stat-label">Pending Reviews</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Recent Jobs -->
                        <div class="col-lg-8 mb-4">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">
                                        <i class="fas fa-briefcase me-2"></i>Recent Jobs
                                    </h5>
                                    <a href="add-job.php" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus me-1"></i>Add Job
                                    </a>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($recent_jobs)): ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-briefcase fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No jobs posted yet.</p>
                                            <a href="add-job.php" class="btn btn-primary">Post Your First Job</a>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($recent_jobs as $job): ?>
                                            <div class="job-card">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($job['title']); ?></h6>
                                                    <div>
                                                        <span class="badge bg-<?php echo $job['status'] === 'active' ? 'success' : ($job['status'] === 'inactive' ? 'warning' : 'secondary'); ?> status-badge me-2">
                                                            <?php echo ucfirst($job['status']); ?>
                                                        </span>
                                                        <span class="badge bg-primary status-badge">
                                                            <?php echo ucfirst(str_replace('-', ' ', $job['job_type'])); ?>
                                                        </span>
                                                    </div>
                                                </div>
                                                <p class="text-muted small mb-2">
                                                    <i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($job['location'] ?: 'Remote'); ?>
                                                </p>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <small class="text-muted">
                                                        Posted: <?php echo date('M d, Y', strtotime($job['posted_date'])); ?>
                                                        • <?php echo $job['application_count']; ?> applications
                                                    </small>
                                                    <div>
                                                        <a href="edit-job.php?id=<?php echo $job['id']; ?>" class="btn btn-outline-primary btn-sm">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="view-applications.php?job_id=<?php echo $job['id']; ?>" class="btn btn-outline-info btn-sm">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="col-lg-4 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-bolt me-2"></i>Quick Actions
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <a href="add-job.php" class="btn btn-primary">
                                            <i class="fas fa-plus me-2"></i>Post New Job
                                        </a>
                                        <a href="jobs.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-list me-2"></i>View All Jobs
                                        </a>
                                        <a href="applications.php" class="btn btn-outline-info">
                                            <i class="fas fa-file-alt me-2"></i>Review Applications
                                        </a>
                                        <a href="../jobs.php" class="btn btn-outline-success" target="_blank">
                                            <i class="fas fa-external-link-alt me-2"></i>View Public Jobs
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-chart-line me-2"></i>System Status
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <small>Database</small>
                                            <span class="badge bg-success">Online</span>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <small>File System</small>
                                            <span class="badge bg-success">OK</span>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <small>Email Service</small>
                                            <span class="badge bg-warning">Not Configured</span>
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