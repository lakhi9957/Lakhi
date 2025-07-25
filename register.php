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
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = sanitize($_POST['role']);
    $address = sanitize($_POST['address']);
    
    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password) || empty($role)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (!in_array($role, ['student', 'teacher', 'parent'])) {
        $error = 'Please select a valid role.';
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Email address already exists.';
        } else {
            // Insert new user
            $hashed_password = hashPassword($password);
            $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, role, address, verified) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $verified = 1; // Auto-verify for demo purposes
            $stmt->bind_param("ssssssi", $name, $email, $phone, $hashed_password, $role, $address, $verified);
            
            if ($stmt->execute()) {
                $user_id = $conn->insert_id;
                
                // Create role-specific records
                if ($role === 'student') {
                    $student_id = 'STD' . str_pad($user_id, 4, '0', STR_PAD_LEFT);
                    $stmt = $conn->prepare("INSERT INTO students (user_id, student_id, admission_date) VALUES (?, ?, CURDATE())");
                    $stmt->bind_param("is", $user_id, $student_id);
                    $stmt->execute();
                } elseif ($role === 'teacher') {
                    $teacher_id = 'TCH' . str_pad($user_id, 4, '0', STR_PAD_LEFT);
                    $stmt = $conn->prepare("INSERT INTO teachers (user_id, teacher_id, join_date) VALUES (?, ?, CURDATE())");
                    $stmt->bind_param("is", $user_id, $teacher_id);
                    $stmt->execute();
                }
                
                $success = 'Registration successful! You can now log in.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Excellence Tuition Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        .register-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            margin: 20px 0;
        }
        .register-form {
            padding: 40px;
        }
        .register-banner {
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
        .btn-register {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 30px;
        }
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .role-card {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .role-card:hover {
            border-color: #667eea;
            transform: translateY(-2px);
        }
        .role-card.selected {
            border-color: #667eea;
            background-color: rgba(102, 126, 234, 0.1);
        }
        .password-strength {
            height: 5px;
            border-radius: 3px;
            margin-top: 5px;
            transition: all 0.3s ease;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-9">
                <div class="register-container">
                    <div class="row g-0">
                        <div class="col-lg-5">
                            <div class="register-banner">
                                <div>
                                    <i class="fas fa-user-plus fa-4x mb-4"></i>
                                    <h2 class="mb-3">Join Us Today!</h2>
                                    <p class="lead">Create your account and become part of our educational community</p>
                                    <div class="mt-4">
                                        <div class="row g-3">
                                            <div class="col-6">
                                                <div class="text-center">
                                                    <i class="fas fa-book fa-2x"></i>
                                                    <p class="small mt-1">Quality Education</p>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-center">
                                                    <i class="fas fa-users fa-2x"></i>
                                                    <p class="small mt-1">Expert Teachers</p>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-center">
                                                    <i class="fas fa-chart-line fa-2x"></i>
                                                    <p class="small mt-1">Track Progress</p>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-center">
                                                    <i class="fas fa-certificate fa-2x"></i>
                                                    <p class="small mt-1">Achievements</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-7">
                            <div class="register-form">
                                <div class="text-center mb-4">
                                    <h3 class="fw-bold text-dark">Create Account</h3>
                                    <p class="text-muted">Fill in your details to get started</p>
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
                                        <a href="login.php" class="btn btn-sm btn-success ms-2">Login Now</a>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                <?php endif; ?>

                                <form method="POST" action="" id="registerForm">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="name" class="form-label">
                                                <i class="fas fa-user me-2"></i>Full Name *
                                            </label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="name" 
                                                   name="name" 
                                                   placeholder="Enter your full name"
                                                   value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                                                   required>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label">
                                                <i class="fas fa-envelope me-2"></i>Email Address *
                                            </label>
                                            <input type="email" 
                                                   class="form-control" 
                                                   id="email" 
                                                   name="email" 
                                                   placeholder="Enter your email"
                                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                                   required>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="phone" class="form-label">
                                            <i class="fas fa-phone me-2"></i>Phone Number
                                        </label>
                                        <input type="tel" 
                                               class="form-control" 
                                               id="phone" 
                                               name="phone" 
                                               placeholder="Enter your phone number"
                                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-user-tag me-2"></i>Select Your Role *
                                        </label>
                                        <div class="row g-2">
                                            <div class="col-4">
                                                <div class="role-card" data-role="student">
                                                    <input type="radio" name="role" value="student" id="student" class="d-none">
                                                    <i class="fas fa-user-graduate fa-2x text-primary mb-2 d-block"></i>
                                                    <small class="fw-bold">Student</small>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="role-card" data-role="teacher">
                                                    <input type="radio" name="role" value="teacher" id="teacher" class="d-none">
                                                    <i class="fas fa-chalkboard-teacher fa-2x text-success mb-2 d-block"></i>
                                                    <small class="fw-bold">Teacher</small>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="role-card" data-role="parent">
                                                    <input type="radio" name="role" value="parent" id="parent" class="d-none">
                                                    <i class="fas fa-users fa-2x text-info mb-2 d-block"></i>
                                                    <small class="fw-bold">Parent</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="password" class="form-label">
                                                <i class="fas fa-lock me-2"></i>Password *
                                            </label>
                                            <div class="input-group">
                                                <input type="password" 
                                                       class="form-control" 
                                                       id="password" 
                                                       name="password" 
                                                       placeholder="Create a password"
                                                       onkeyup="checkPasswordStrength()"
                                                       required>
                                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password', 'toggleIcon1')">
                                                    <i class="fas fa-eye" id="toggleIcon1"></i>
                                                </button>
                                            </div>
                                            <div class="password-strength" id="passwordStrength"></div>
                                            <small class="text-muted">Minimum 6 characters</small>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="confirm_password" class="form-label">
                                                <i class="fas fa-lock me-2"></i>Confirm Password *
                                            </label>
                                            <div class="input-group">
                                                <input type="password" 
                                                       class="form-control" 
                                                       id="confirm_password" 
                                                       name="confirm_password" 
                                                       placeholder="Confirm your password"
                                                       onkeyup="checkPasswordMatch()"
                                                       required>
                                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_password', 'toggleIcon2')">
                                                    <i class="fas fa-eye" id="toggleIcon2"></i>
                                                </button>
                                            </div>
                                            <small id="passwordMatch" class="text-muted"></small>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="address" class="form-label">
                                            <i class="fas fa-map-marker-alt me-2"></i>Address
                                        </label>
                                        <textarea class="form-control" 
                                                  id="address" 
                                                  name="address" 
                                                  rows="2" 
                                                  placeholder="Enter your address"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                                    </div>

                                    <div class="mb-3 form-check">
                                        <input type="checkbox" class="form-check-input" id="terms" required>
                                        <label class="form-check-label" for="terms">
                                            I agree to the <a href="#" class="text-decoration-none">Terms & Conditions</a> and <a href="#" class="text-decoration-none">Privacy Policy</a>
                                        </label>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-register btn-lg w-100 text-white mb-3">
                                        <i class="fas fa-user-plus me-2"></i>Create Account
                                    </button>
                                </form>

                                <div class="text-center">
                                    <p class="text-muted">Already have an account? 
                                        <a href="login.php" class="text-decoration-none fw-bold">Sign In</a>
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
        // Role selection
        document.querySelectorAll('.role-card').forEach(card => {
            card.addEventListener('click', function() {
                document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');
                
                const radio = this.querySelector('input[type="radio"]');
                radio.checked = true;
            });
        });

        // Password visibility toggle
        function togglePassword(fieldId, iconId) {
            const passwordInput = document.getElementById(fieldId);
            const toggleIcon = document.getElementById(iconId);
            
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

        // Password strength checker
        function checkPasswordStrength() {
            const password = document.getElementById('password').value;
            const strengthBar = document.getElementById('passwordStrength');
            
            let strength = 0;
            if (password.length >= 6) strength++;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            const colors = ['#dc3545', '#fd7e14', '#ffc107', '#28a745'];
            const widths = ['25%', '50%', '75%', '100%'];
            
            if (password.length > 0) {
                strengthBar.style.width = widths[strength - 1] || '25%';
                strengthBar.style.backgroundColor = colors[strength - 1] || '#dc3545';
            } else {
                strengthBar.style.width = '0%';
            }
        }

        // Password match checker
        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const matchIndicator = document.getElementById('passwordMatch');
            
            if (confirmPassword.length > 0) {
                if (password === confirmPassword) {
                    matchIndicator.textContent = 'Passwords match';
                    matchIndicator.className = 'text-success';
                } else {
                    matchIndicator.textContent = 'Passwords do not match';
                    matchIndicator.className = 'text-danger';
                }
            } else {
                matchIndicator.textContent = '';
            }
        }

        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                if (!alert.querySelector('.btn')) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            });
        }, 5000);
    </script>
</body>
</html>