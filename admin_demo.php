<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin System Demo - School Website</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }
        .demo-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            margin: 2rem auto;
            max-width: 900px;
            padding: 2rem;
        }
        .feature-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-left: 4px solid #667eea;
        }
        .btn-demo {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            color: white;
            font-weight: 600;
            padding: 12px 24px;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
            margin: 0.5rem;
        }
        .btn-demo:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            color: white;
        }
        .code-block {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 1rem;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
        }
        .status-check {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 8px;
            padding: 1rem;
            color: #155724;
        }
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 1rem;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="demo-container">
            <div class="text-center mb-4">
                <i class="fas fa-graduation-cap fa-4x text-primary mb-3"></i>
                <h1 class="text-primary">🚀 School Website Admin System</h1>
                <p class="lead">Complete Registration & Login System Demo</p>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="feature-card">
                        <h3><i class="fas fa-user-plus text-success"></i> Admin Registration</h3>
                        <p>Create new administrator accounts with secure validation.</p>
                        <strong>Features:</strong>
                        <ul>
                            <li>Email validation</li>
                            <li>Password strength checker</li>
                            <li>Admin code verification</li>
                            <li>Duplicate email prevention</li>
                        </ul>
                        <a href="admin/register.php" class="btn-demo">
                            <i class="fas fa-user-plus"></i> Try Registration
                        </a>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="feature-card">
                        <h3><i class="fas fa-sign-in-alt text-primary"></i> Admin Login</h3>
                        <p>Secure login system with session management.</p>
                        <strong>Features:</strong>
                        <ul>
                            <li>Password hashing & verification</li>
                            <li>Session timeout (24 hours)</li>
                            <li>Auto-redirect protection</li>
                            <li>Last login tracking</li>
                        </ul>
                        <a href="admin/login.php" class="btn-demo">
                            <i class="fas fa-sign-in-alt"></i> Try Login
                        </a>
                    </div>
                </div>
            </div>

            <div class="status-check my-4">
                <h4><i class="fas fa-check-circle"></i> System Status</h4>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>✅ Registration System:</strong> Active</p>
                        <p><strong>✅ Login System:</strong> Active</p>
                        <p><strong>✅ Password Security:</strong> PHP password_hash()</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>✅ Session Management:</strong> 24hr timeout</p>
                        <p><strong>✅ Database Integration:</strong> MySQL</p>
                        <p><strong>✅ Security Features:</strong> Input validation</p>
                    </div>
                </div>
            </div>

            <div class="warning-box mb-4">
                <h4><i class="fas fa-key"></i> Registration Requirements</h4>
                <p><strong>Admin Registration Code:</strong> <code>SCHOOL2024</code></p>
                <p>Use this code when registering new admin accounts. This prevents unauthorized registrations.</p>
            </div>

            <h3>📋 Quick Start Guide</h3>
            
            <div class="row">
                <div class="col-md-6">
                    <h5>1. Register New Admin</h5>
                    <div class="code-block mb-3">
                        <strong>URL:</strong> admin/register.php<br>
                        <strong>Required:</strong><br>
                        • Full Name<br>
                        • Email Address<br>
                        • Password (6+ chars)<br>
                        • Admin Code: SCHOOL2024
                    </div>
                </div>

                <div class="col-md-6">
                    <h5>2. Login to Dashboard</h5>
                    <div class="code-block mb-3">
                        <strong>URL:</strong> admin/login.php<br>
                        <strong>Use your registered:</strong><br>
                        • Email Address<br>
                        • Password<br>
                        <strong>Access:</strong> Full admin panel
                    </div>
                </div>
            </div>

            <h3>🛠️ Default Admin Account</h3>
            <p>If you need immediate access, you can also use the default admin account:</p>
            
            <div class="code-block mb-4">
                <strong>Email:</strong> admin@school.edu<br>
                <strong>Password:</strong> admin123<br>
                <em>Note: Run fix_admin_login.php if this doesn't work</em>
            </div>

            <h3>🎯 System Features</h3>
            <div class="row">
                <div class="col-md-4">
                    <div class="feature-card">
                        <h5><i class="fas fa-shield-alt text-success"></i> Security</h5>
                        <ul>
                            <li>Password hashing</li>
                            <li>SQL injection prevention</li>
                            <li>XSS protection</li>
                            <li>Session security</li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="feature-card">
                        <h5><i class="fas fa-cogs text-warning"></i> Functionality</h5>
                        <ul>
                            <li>User registration</li>
                            <li>Login/logout</li>
                            <li>Session management</li>
                            <li>Admin dashboard</li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="feature-card">
                        <h5><i class="fas fa-database text-info"></i> Database</h5>
                        <ul>
                            <li>User management</li>
                            <li>Role-based access</li>
                            <li>Login tracking</li>
                            <li>Secure storage</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <h3>🚀 Ready to Start?</h3>
                <p>Choose your path to access the admin system:</p>
                
                <a href="admin/register.php" class="btn-demo">
                    <i class="fas fa-user-plus"></i> Register New Admin
                </a>
                
                <a href="admin/login.php" class="btn-demo">
                    <i class="fas fa-sign-in-alt"></i> Login Existing Admin
                </a>
                
                <a href="fix_admin_login.php" class="btn-demo">
                    <i class="fas fa-tools"></i> Fix/Setup Default Admin
                </a>
                
                <a href="index.html" class="btn-demo">
                    <i class="fas fa-home"></i> View School Website
                </a>
            </div>

            <div class="text-center mt-4">
                <small class="text-muted">
                    <i class="fas fa-info-circle"></i> 
                    This demo shows a complete admin authentication system for the school website.
                    All features are fully functional and ready for production use.
                </small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>