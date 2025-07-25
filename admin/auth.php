<?php
// Admin Authentication System
session_start();
include '../config.php';

class AdminAuth {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    // Check if user is logged in
    public function isLoggedIn() {
        return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    }
    
    // Login function
    public function login($email, $password) {
        $email = mysqli_real_escape_string($this->conn, trim($email));
        
        $query = "SELECT * FROM users WHERE email = '$email' AND role = 'admin' AND verified = 1";
        $result = mysqli_query($this->conn, $query);
        
        if ($result && mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);
            
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_name'] = $user['name'];
                $_SESSION['admin_email'] = $user['email'];
                $_SESSION['login_time'] = time();
                
                // Update last login
                $this->updateLastLogin($user['id']);
                
                return true;
            }
        }
        return false;
    }
    
    // Register new admin
    public function register($name, $email, $password, $admin_code) {
        // Validate admin code
        if ($admin_code !== 'SCHOOL2024') {
            return ['success' => false, 'message' => 'Invalid admin registration code'];
        }
        
        // Validate inputs
        if (empty($name) || empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'All fields are required'];
        }
        
        if (strlen($password) < 6) {
            return ['success' => false, 'message' => 'Password must be at least 6 characters long'];
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email format'];
        }
        
        // Check if email already exists
        $email_escaped = mysqli_real_escape_string($this->conn, trim($email));
        $check_query = "SELECT * FROM users WHERE email = '$email_escaped'";
        $check_result = mysqli_query($this->conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            return ['success' => false, 'message' => 'Email already exists'];
        }
        
        // Hash password and insert user
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $name_escaped = mysqli_real_escape_string($this->conn, trim($name));
        
        $insert_query = "INSERT INTO users (name, email, password, role, verified, created_at) VALUES (?, ?, ?, 'admin', 1, NOW())";
        $stmt = mysqli_prepare($this->conn, $insert_query);
        mysqli_stmt_bind_param($stmt, "sss", $name_escaped, $email_escaped, $hashed_password);
        
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            return ['success' => true, 'message' => 'Admin account created successfully'];
        } else {
            mysqli_stmt_close($stmt);
            return ['success' => false, 'message' => 'Error creating account'];
        }
    }
    
    // Logout function
    public function logout() {
        session_unset();
        session_destroy();
        return true;
    }
    
    // Get current admin info
    public function getCurrentAdmin() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['admin_id'],
            'name' => $_SESSION['admin_name'],
            'email' => $_SESSION['admin_email'],
            'login_time' => $_SESSION['login_time']
        ];
    }
    
    // Update last login time
    private function updateLastLogin($user_id) {
        $query = "UPDATE users SET last_login = NOW() WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    
    // Check session timeout (24 hours)
    public function checkSessionTimeout() {
        if ($this->isLoggedIn()) {
            $timeout = 24 * 60 * 60; // 24 hours
            if (time() - $_SESSION['login_time'] > $timeout) {
                $this->logout();
                return false;
            }
        }
        return true;
    }
    
    // Require admin login (redirect if not logged in)
    public function requireLogin() {
        if (!$this->isLoggedIn() || !$this->checkSessionTimeout()) {
            header('Location: login.php');
            exit();
        }
    }
}

// Create global auth instance
$auth = new AdminAuth($conn);

// Auto-check session timeout
$auth->checkSessionTimeout();
?>