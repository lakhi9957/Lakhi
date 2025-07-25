<?php
session_start();
include '../config.php';

// Check if logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_notice':
                $title = mysqli_real_escape_string($conn, $_POST['title']);
                $content = mysqli_real_escape_string($conn, $_POST['content']);
                $priority = mysqli_real_escape_string($conn, $_POST['priority']);
                $date = date('Y-m-d');
                
                $query = "INSERT INTO notices (title, content, priority, date_created) VALUES ('$title', '$content', '$priority', '$date')";
                if (mysqli_query($conn, $query)) {
                    $success_message = "Notice added successfully!";
                } else {
                    $error_message = "Error adding notice.";
                }
                break;
                
            case 'delete_notice':
                $notice_id = (int)$_POST['notice_id'];
                $query = "DELETE FROM notices WHERE id = $notice_id";
                if (mysqli_query($conn, $query)) {
                    $success_message = "Notice deleted successfully!";
                } else {
                    $error_message = "Error deleting notice.";
                }
                break;
        }
    }
}

// Fetch notices
$notices_query = "SELECT * FROM notices ORDER BY date_created DESC";
$notices_result = mysqli_query($conn, $notices_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - School Website</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            border-radius: 10px;
            margin: 5px 0;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.2);
        }
        .main-content {
            background: #f8f9fa;
            min-height: 100vh;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .table th {
            background: #f8f9fa;
            border: none;
            font-weight: 600;
        }
        .priority-high { color: #dc3545; }
        .priority-medium { color: #ffc107; }
        .priority-low { color: #198754; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar p-3">
                <div class="text-center mb-4">
                    <i class="fas fa-graduation-cap fa-3x mb-2"></i>
                    <h4>School Admin</h4>
                    <p class="mb-0">Welcome, <?php echo $_SESSION['admin_name']; ?></p>
                </div>
                
                <ul class="nav nav-pills flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="#dashboard" data-tab="dashboard">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#notices" data-tab="notices">
                            <i class="fas fa-bullhorn"></i> Manage Notices
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#gallery" data-tab="gallery">
                            <i class="fas fa-images"></i> Manage Gallery
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#settings" data-tab="settings">
                            <i class="fas fa-cog"></i> Settings
                        </a>
                    </li>
                    <li class="nav-item mt-3">
                        <a class="nav-link" href="../index.html" target="_blank">
                            <i class="fas fa-external-link-alt"></i> View Website
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            </nav>
            
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="py-4">
                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Dashboard Tab -->
                    <div id="dashboard-tab" class="tab-content">
                        <h1 class="h2 mb-4">Dashboard Overview</h1>
                        
                        <div class="row mb-4">
                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <i class="fas fa-bullhorn fa-2x text-primary"></i>
                                            </div>
                                            <div>
                                                <div class="h5 mb-0"><?php echo mysqli_num_rows($notices_result); ?></div>
                                                <div class="text-muted">Total Notices</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <i class="fas fa-images fa-2x text-success"></i>
                                            </div>
                                            <div>
                                                <div class="h5 mb-0">6</div>
                                                <div class="text-muted">Gallery Images</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <i class="fas fa-users fa-2x text-warning"></i>
                                            </div>
                                            <div>
                                                <div class="h5 mb-0">1,200+</div>
                                                <div class="text-muted">Students</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <i class="fas fa-chalkboard-teacher fa-2x text-info"></i>
                                            </div>
                                            <div>
                                                <div class="h5 mb-0">80+</div>
                                                <div class="text-muted">Teachers</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <button class="btn btn-primary w-100" onclick="showTab('notices')">
                                            <i class="fas fa-plus"></i> Add New Notice
                                        </button>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <button class="btn btn-success w-100" onclick="showTab('gallery')">
                                            <i class="fas fa-upload"></i> Upload Images
                                        </button>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <a href="../index.html" target="_blank" class="btn btn-info w-100">
                                            <i class="fas fa-eye"></i> Preview Website
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Notices Tab -->
                    <div id="notices-tab" class="tab-content" style="display: none;">
                        <h1 class="h2 mb-4">Manage Notices</h1>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Add New Notice</h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST">
                                            <input type="hidden" name="action" value="add_notice">
                                            
                                            <div class="mb-3">
                                                <label for="title" class="form-label">Title</label>
                                                <input type="text" class="form-control" id="title" name="title" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="content" class="form-label">Content</label>
                                                <textarea class="form-control" id="content" name="content" rows="4" required></textarea>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="priority" class="form-label">Priority</label>
                                                <select class="form-control" id="priority" name="priority" required>
                                                    <option value="low">Low</option>
                                                    <option value="medium" selected>Medium</option>
                                                    <option value="high">High</option>
                                                </select>
                                            </div>
                                            
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="fas fa-plus"></i> Add Notice
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Existing Notices</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if (mysqli_num_rows($notices_result) > 0): ?>
                                            <div class="table-responsive">
                                                <table class="table">
                                                    <thead>
                                                        <tr>
                                                            <th>Title</th>
                                                            <th>Priority</th>
                                                            <th>Date</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php while ($notice = mysqli_fetch_assoc($notices_result)): ?>
                                                            <tr>
                                                                <td><?php echo htmlspecialchars($notice['title']); ?></td>
                                                                <td>
                                                                    <span class="priority-<?php echo $notice['priority']; ?>">
                                                                        <i class="fas fa-circle"></i> <?php echo ucfirst($notice['priority']); ?>
                                                                    </span>
                                                                </td>
                                                                <td><?php echo date('M j, Y', strtotime($notice['date_created'])); ?></td>
                                                                <td>
                                                                    <form method="POST" style="display: inline;">
                                                                        <input type="hidden" name="action" value="delete_notice">
                                                                        <input type="hidden" name="notice_id" value="<?php echo $notice['id']; ?>">
                                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    </form>
                                                                </td>
                                                            </tr>
                                                        <?php endwhile; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php else: ?>
                                            <p class="text-muted text-center">No notices found. Add your first notice!</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Gallery Tab -->
                    <div id="gallery-tab" class="tab-content" style="display: none;">
                        <h1 class="h2 mb-4">Manage Gallery</h1>
                        
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Gallery Management</h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted">Gallery management functionality will be implemented here. Currently, images are loaded from sample data in the JavaScript file.</p>
                                <p>Features to be added:</p>
                                <ul>
                                    <li>Upload new images</li>
                                    <li>Edit image titles and descriptions</li>
                                    <li>Delete images</li>
                                    <li>Organize images by categories</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Settings Tab -->
                    <div id="settings-tab" class="tab-content" style="display: none;">
                        <h1 class="h2 mb-4">Settings</h1>
                        
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Website Settings</h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted">Website settings and configuration options will be available here.</p>
                                <p>Settings to be added:</p>
                                <ul>
                                    <li>School information</li>
                                    <li>Contact details</li>
                                    <li>Social media links</li>
                                    <li>Admin user management</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Tab switching functionality
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.style.display = 'none';
            });
            
            // Remove active class from all nav links
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName + '-tab').style.display = 'block';
            
            // Add active class to clicked nav link
            document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
        }
        
        // Handle nav link clicks
        document.querySelectorAll('[data-tab]').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const tabName = this.getAttribute('data-tab');
                showTab(tabName);
            });
        });
    </script>
</body>
</html>