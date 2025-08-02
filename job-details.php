<?php
include 'config.php';

$job_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$job = null;
$error = '';

if ($job_id > 0) {
    $stmt = $conn->prepare("SELECT j.*, u.name as posted_by FROM jobs j LEFT JOIN users u ON j.created_by = u.id WHERE j.id = ? AND j.status = 'active'");
    $stmt->bind_param("i", $job_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $job = $result->fetch_assoc();
    } else {
        $error = 'Job not found or no longer available.';
    }
    $stmt->close();
} else {
    $error = 'Invalid job ID.';
}

// Handle application submission
$application_success = '';
$application_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $job) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $cover_letter = trim($_POST['cover_letter']);
    
    if (empty($name) || empty($email) || empty($cover_letter)) {
        $application_error = 'Please fill in all required fields.';
    } else {
        // Insert application
        $stmt = $conn->prepare("INSERT INTO job_applications (job_id, applicant_name, applicant_email, applicant_phone, cover_letter) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $job_id, $name, $email, $phone, $cover_letter);
        
        if ($stmt->execute()) {
            $application_success = 'Your application has been submitted successfully!';
        } else {
            $application_error = 'Error submitting application. Please try again.';
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
    <title><?php echo $job ? htmlspecialchars($job['title']) . ' - Job Portal' : 'Job Not Found'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .job-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 0;
        }
        .job-detail-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            margin-top: -50px;
            position: relative;
            z-index: 1;
        }
        .application-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 30px;
            margin-top: 30px;
        }
        .job-meta {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .job-meta .meta-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .job-meta .meta-item:last-child {
            margin-bottom: 0;
        }
        .job-meta .meta-item i {
            width: 20px;
            margin-right: 10px;
            color: #667eea;
        }
        .requirements-list {
            list-style: none;
            padding-left: 0;
        }
        .requirements-list li {
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .requirements-list li:before {
            content: "✓";
            color: #28a745;
            font-weight: bold;
            margin-right: 10px;
        }
        .apply-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 30px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.html">
                <i class="fas fa-briefcase me-2"></i>Job Portal
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.html">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="jobs.php">Browse Jobs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.html#about">About</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="admin/login.php">
                            <i class="fas fa-user-shield me-1"></i>Admin Login
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <?php if ($job): ?>
        <!-- Job Header -->
        <section class="job-header">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-start flex-wrap">
                            <div>
                                <h1 class="display-5 fw-bold mb-3"><?php echo htmlspecialchars($job['title']); ?></h1>
                                <p class="lead mb-2">
                                    <i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($job['location'] ?: 'Remote'); ?>
                                </p>
                                <?php if ($job['salary_range']): ?>
                                    <p class="lead mb-0">
                                        <i class="fas fa-dollar-sign me-2"></i><?php echo htmlspecialchars($job['salary_range']); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-light text-dark fs-6 me-2"><?php echo ucfirst(str_replace('-', ' ', $job['job_type'])); ?></span>
                                <span class="badge bg-success fs-6">Active</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="job-detail-card">
                        <!-- Job Meta Information -->
                        <div class="job-meta">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="meta-item">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span>Posted: <?php echo date('M d, Y', strtotime($job['posted_date'])); ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-clock"></i>
                                        <span>Type: <?php echo ucfirst(str_replace('-', ' ', $job['job_type'])); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <?php if ($job['deadline']): ?>
                                        <div class="meta-item">
                                            <i class="fas fa-calendar-times"></i>
                                            <span>Apply by: <?php echo date('M d, Y', strtotime($job['deadline'])); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($job['posted_by']): ?>
                                        <div class="meta-item">
                                            <i class="fas fa-user"></i>
                                            <span>Posted by: <?php echo htmlspecialchars($job['posted_by']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Job Description -->
                        <div class="mb-4">
                            <h3><i class="fas fa-info-circle me-2"></i>Job Description</h3>
                            <div class="mt-3">
                                <?php echo nl2br(htmlspecialchars($job['description'])); ?>
                            </div>
                        </div>

                        <!-- Requirements -->
                        <?php if ($job['requirements']): ?>
                            <div class="mb-4">
                                <h3><i class="fas fa-list-check me-2"></i>Requirements</h3>
                                <ul class="requirements-list mt-3">
                                    <?php 
                                    $requirements = explode('•', $job['requirements']);
                                    foreach ($requirements as $req): 
                                        $req = trim($req);
                                        if (!empty($req)):
                                    ?>
                                        <li><?php echo htmlspecialchars($req); ?></li>
                                    <?php 
                                        endif;
                                    endforeach; 
                                    ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <!-- Application Form -->
                        <div class="application-card">
                            <h3><i class="fas fa-paper-plane me-2"></i>Apply for this Position</h3>
                            
                            <?php if ($application_success): ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($application_success); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($application_error): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($application_error); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!$application_success): ?>
                                <form method="POST" class="mt-4">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="name" class="form-label">Full Name *</label>
                                            <input type="text" class="form-control" id="name" name="name" 
                                                   value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label">Email Address *</label>
                                            <input type="email" class="form-control" id="email" name="email" 
                                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" 
                                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="cover_letter" class="form-label">Cover Letter *</label>
                                        <textarea class="form-control" id="cover_letter" name="cover_letter" rows="6" 
                                                  placeholder="Tell us why you're the perfect fit for this role..." required><?php echo isset($_POST['cover_letter']) ? htmlspecialchars($_POST['cover_letter']) : ''; ?></textarea>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary apply-btn">
                                        <i class="fas fa-paper-plane me-2"></i>Submit Application
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar -->
                <div class="col-lg-4">
                    <div class="card mt-4 mt-lg-0">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Quick Info</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>Job Type:</strong><br>
                                <span class="badge bg-primary"><?php echo ucfirst(str_replace('-', ' ', $job['job_type'])); ?></span>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Location:</strong><br>
                                <i class="fas fa-map-marker-alt text-muted me-1"></i><?php echo htmlspecialchars($job['location'] ?: 'Remote'); ?>
                            </div>
                            
                            <?php if ($job['salary_range']): ?>
                                <div class="mb-3">
                                    <strong>Salary:</strong><br>
                                    <span class="text-success fw-bold"><?php echo htmlspecialchars($job['salary_range']); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <strong>Posted:</strong><br>
                                <?php echo date('M d, Y', strtotime($job['posted_date'])); ?>
                            </div>
                            
                            <?php if ($job['deadline']): ?>
                                <div class="mb-3">
                                    <strong>Deadline:</strong><br>
                                    <span class="text-danger"><?php echo date('M d, Y', strtotime($job['deadline'])); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-share me-2"></i>Share This Job</h5>
                        </div>
                        <div class="card-body text-center">
                            <div class="d-grid gap-2">
                                <a href="#" class="btn btn-outline-primary" onclick="copyJobUrl()">
                                    <i class="fas fa-copy me-2"></i>Copy Link
                                </a>
                                <a href="mailto:?subject=Job Opportunity: <?php echo urlencode($job['title']); ?>&body=Check out this job: <?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
                                   class="btn btn-outline-secondary">
                                    <i class="fas fa-envelope me-2"></i>Email
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- Error Message -->
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-6 text-center">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                        <h4>Job Not Found</h4>
                        <p><?php echo htmlspecialchars($error); ?></p>
                        <a href="jobs.php" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Browse All Jobs
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p>&copy; 2024 Job Portal. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-end">
                    <a href="jobs.php" class="text-white me-3">Browse Jobs</a>
                    <a href="index.html" class="text-white">Home</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function copyJobUrl() {
            navigator.clipboard.writeText(window.location.href).then(function() {
                alert('Job link copied to clipboard!');
            }).catch(function(err) {
                console.error('Could not copy text: ', err);
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = window.location.href;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                alert('Job link copied to clipboard!');
            });
        }
    </script>
</body>
</html>