<?php
session_start();
require_once 'config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    $role = $_SESSION['role'];
    redirect("{$role}/dashboard.php");
}

$error = '';
$success = '';

if ($_POST) {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $conn->prepare("SELECT u.id, u.name, u.email, u.password, u.role, u.verified FROM users u WHERE u.email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (verifyPassword($password, $user['password'])) {
                if ($user['verified']) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['name'] = $user['name'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    
                    // Redirect based on role
                    redirect($user['role'] . '/dashboard.php');
                } else {
                    $error = 'Please verify your email address before logging in.';
                }
            } else {
                $error = 'Invalid email or password.';
            }
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Excellence Tuition Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .login-form {
            padding: 40px;
        }
        .login-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 30px;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8">
                <div class="login-container">
                    <div class="row g-0">
                        <div class="col-lg-6">
                            <div class="login-banner">
                                <div>
                                    <i class="fas fa-graduation-cap fa-4x mb-4"></i>
                                    <h2 class="mb-3">Welcome Back!</h2>
                                    <p class="lead">Sign in to access your tuition center portal</p>
                                    <div class="mt-4">
                                        <div class="d-flex justify-content-center gap-3 mb-3">
                                            <div class="text-center">
                                                <i class="fas fa-user-graduate fa-2x"></i>
                                                <p class="small mt-1">Students</p>
                                            </div>
                                            <div class="text-center">
                                                <i class="fas fa-chalkboard-teacher fa-2x"></i>
                                                <p class="small mt-1">Teachers</p>
                                            </div>
                                            <div class="text-center">
                                                <i class="fas fa-users fa-2x"></i>
                                                <p class="small mt-1">Parents</p>
                                            </div>
                                            <div class="text-center">
                                                <i class="fas fa-cog fa-2x"></i>
                                                <p class="small mt-1">Admin</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="login-form">
                                <div class="text-center mb-4">
                                    <h3 class="fw-bold text-dark">Sign In</h3>
                                    <p class="text-muted">Enter your credentials to continue</p>
                                </div>

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

                                <form method="POST" action="">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">
                                            <i class="fas fa-envelope me-2"></i>Email Address
                                        </label>
                                        <input type="email" 
                                               class="form-control form-control-lg" 
                                               id="email" 
                                               name="email" 
                                               placeholder="Enter your email"
                                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                               required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="password" class="form-label">
                                            <i class="fas fa-lock me-2"></i>Password
                                        </label>
                                        <div class="input-group">
                                            <input type="password" 
                                                   class="form-control form-control-lg" 
                                                   id="password" 
                                                   name="password" 
                                                   placeholder="Enter your password"
                                                   required>
                                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                                <i class="fas fa-eye" id="toggleIcon"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                            <label class="form-check-label" for="remember">
                                                Remember me
                                            </label>
                                        </div>
                                        <a href="forgot-password.php" class="text-decoration-none">Forgot password?</a>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-login btn-lg w-100 text-white mb-3">
                                        <i class="fas fa-sign-in-alt me-2"></i>Sign In
                                    </button>
                                </form>

                                <div class="text-center">
                                    <p class="text-muted">Don't have an account? 
                                        <a href="register.php" class="text-decoration-none fw-bold">Create Account</a>
                                    </p>
                                </div>

                                <hr class="my-4">

                                <div class="text-center">
                                    <a href="index.html" class="btn btn-outline-secondary">
                                        <i class="fas fa-home me-2"></i>Back to Home
                                    </a>
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
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Auto-dismiss alerts after 5 seconds
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