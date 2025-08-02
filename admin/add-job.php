<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

include '../config.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $requirements = trim($_POST['requirements']);
    $location = trim($_POST['location']);
    $salary_range = trim($_POST['salary_range']);
    $job_type = $_POST['job_type'];
    $deadline = $_POST['deadline'] ?: null;
    $status = $_POST['status'];
    
    // Validation
    if (empty($title) || empty($description) || empty($job_type)) {
        $error = 'Please fill in all required fields.';
    } else {
        // Insert job
        $stmt = $conn->prepare("INSERT INTO jobs (title, description, requirements, location, salary_range, job_type, deadline, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssi", $title, $description, $requirements, $location, $salary_range, $job_type, $deadline, $status, $_SESSION['admin_id']);
        
        if ($stmt->execute()) {
            $success = 'Job posted successfully!';
            // Clear form data
            $_POST = [];
        } else {
            $error = 'Error posting job. Please try again.';
        }
        
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Job - Admin Dashboard</title>
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
        .form-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i>Dashboard
                        </a>
                        <a class="nav-link" href="jobs.php">
                            <i class="fas fa-briefcase"></i>Manage Jobs
                        </a>
                        <a class="nav-link" href="applications.php">
                            <i class="fas fa-file-alt"></i>Applications
                        </a>
                        <a class="nav-link active" href="add-job.php">
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
                        <h2><i class="fas fa-plus me-2"></i>Add New Job</h2>
                        <a href="jobs.php" class="btn btn-outline-secondary">
                            <i class="fas fa-list me-1"></i>View All Jobs
                        </a>
                    </div>
                </div>
                
                <div class="container-fluid">
                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            <div class="form-card">
                                <?php if ($success): ?>
                                    <div class="alert alert-success">
                                        <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($error): ?>
                                    <div class="alert alert-danger">
                                        <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <form method="POST">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="title" class="form-label">Job Title *</label>
                                            <input type="text" class="form-control" id="title" name="title" 
                                                   value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" 
                                                   required>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="job_type" class="form-label">Job Type *</label>
                                            <select class="form-select" id="job_type" name="job_type" required>
                                                <option value="">Select Job Type</option>
                                                <option value="full-time" <?php echo (isset($_POST['job_type']) && $_POST['job_type'] === 'full-time') ? 'selected' : ''; ?>>Full Time</option>
                                                <option value="part-time" <?php echo (isset($_POST['job_type']) && $_POST['job_type'] === 'part-time') ? 'selected' : ''; ?>>Part Time</option>
                                                <option value="contract" <?php echo (isset($_POST['job_type']) && $_POST['job_type'] === 'contract') ? 'selected' : ''; ?>>Contract</option>
                                                <option value="internship" <?php echo (isset($_POST['job_type']) && $_POST['job_type'] === 'internship') ? 'selected' : ''; ?>>Internship</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="location" class="form-label">Location</label>
                                            <input type="text" class="form-control" id="location" name="location" 
                                                   value="<?php echo isset($_POST['location']) ? htmlspecialchars($_POST['location']) : ''; ?>" 
                                                   placeholder="e.g., New York, NY or Remote">
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="salary_range" class="form-label">Salary Range</label>
                                            <input type="text" class="form-control" id="salary_range" name="salary_range" 
                                                   value="<?php echo isset($_POST['salary_range']) ? htmlspecialchars($_POST['salary_range']) : ''; ?>" 
                                                   placeholder="e.g., $50,000 - $70,000">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Job Description *</label>
                                        <textarea class="form-control" id="description" name="description" rows="6" required 
                                                  placeholder="Describe the job role, responsibilities, and what you're looking for..."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="requirements" class="form-label">Requirements</label>
                                        <textarea class="form-control" id="requirements" name="requirements" rows="4" 
                                                  placeholder="List the qualifications, skills, and experience required..."><?php echo isset($_POST['requirements']) ? htmlspecialchars($_POST['requirements']) : ''; ?></textarea>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="deadline" class="form-label">Application Deadline</label>
                                            <input type="date" class="form-control" id="deadline" name="deadline" 
                                                   value="<?php echo isset($_POST['deadline']) ? $_POST['deadline'] : ''; ?>" 
                                                   min="<?php echo date('Y-m-d'); ?>">
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="status" class="form-label">Status</label>
                                            <select class="form-select" id="status" name="status">
                                                <option value="active" <?php echo (!isset($_POST['status']) || $_POST['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                                                <option value="inactive" <?php echo (isset($_POST['status']) && $_POST['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between">
                                        <a href="dashboard.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-arrow-left me-2"></i>Cancel
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Post Job
                                        </button>
                                    </div>
                                </form>
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