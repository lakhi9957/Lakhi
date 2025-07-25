<?php
echo "<h1>🔧 Admin Login Fix Script</h1>";

include 'config.php';

echo "<h2>Step 1: Testing Database Connection</h2>";

// Test connection
if ($conn->connect_error) {
    echo "❌ Connection failed: " . $conn->connect_error . "<br>";
    echo "<strong>Fix:</strong> Check your config.php database settings<br><br>";
    exit();
} else {
    echo "✅ Database connected successfully!<br><br>";
}

echo "<h2>Step 2: Checking Database Tables</h2>";

// Check if database exists
$db_check = mysqli_query($conn, "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'school_db'");
if (mysqli_num_rows($db_check) == 0) {
    echo "❌ Database 'school_db' doesn't exist<br>";
    echo "<strong>Creating database...</strong><br>";
    mysqli_query($conn, "CREATE DATABASE school_db");
    mysqli_select_db($conn, 'school_db');
    echo "✅ Database created<br><br>";
}

// Check if users table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'users'");
if (mysqli_num_rows($table_check) == 0) {
    echo "❌ Users table doesn't exist<br>";
    echo "<strong>Creating users table...</strong><br>";
    
    $create_table = "CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100),
        email VARCHAR(100) UNIQUE,
        password VARCHAR(255),
        role ENUM('student', 'teacher', 'admin'),
        verified BOOLEAN DEFAULT FALSE
    )";
    
    if (mysqli_query($conn, $create_table)) {
        echo "✅ Users table created<br><br>";
    } else {
        echo "❌ Error creating users table: " . mysqli_error($conn) . "<br><br>";
    }
} else {
    echo "✅ Users table exists<br><br>";
}

// Check notices table
$notices_check = mysqli_query($conn, "SHOW TABLES LIKE 'notices'");
if (mysqli_num_rows($notices_check) == 0) {
    echo "Creating notices table...<br>";
    $create_notices = "CREATE TABLE notices (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
        date_created DATE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    mysqli_query($conn, $create_notices);
    echo "✅ Notices table created<br><br>";
}

echo "<h2>Step 3: Admin User Management</h2>";

// Check if admin user exists
$admin_check = mysqli_query($conn, "SELECT * FROM users WHERE email = 'admin@school.edu'");
if (mysqli_num_rows($admin_check) > 0) {
    echo "⚠️ Admin user already exists. Deleting old one...<br>";
    mysqli_query($conn, "DELETE FROM users WHERE email = 'admin@school.edu'");
    echo "✅ Old admin user deleted<br>";
}

// Create new admin user with proper password hash
$name = 'Admin User';
$email = 'admin@school.edu';
$password = 'admin123';
$role = 'admin';

echo "<strong>Creating new admin user...</strong><br>";
echo "Email: $email<br>";
echo "Password: $password<br>";

// Hash the password properly using PHP's password_hash()
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
echo "Password hash: " . substr($hashed_password, 0, 20) . "...<br>";

// Insert new admin user
$insert_query = "INSERT INTO users (name, email, password, role, verified) VALUES (?, ?, ?, ?, 1)";
$stmt = mysqli_prepare($conn, $insert_query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $hashed_password, $role);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "✅ Admin user created successfully!<br><br>";
        
        // Verify the user was created
        $verify_query = mysqli_query($conn, "SELECT * FROM users WHERE email = 'admin@school.edu'");
        if (mysqli_num_rows($verify_query) > 0) {
            $user = mysqli_fetch_assoc($verify_query);
            echo "<h2>✅ Verification: Admin User Details</h2>";
            echo "ID: " . $user['id'] . "<br>";
            echo "Name: " . $user['name'] . "<br>";
            echo "Email: " . $user['email'] . "<br>";
            echo "Role: " . $user['role'] . "<br>";
            echo "Verified: " . ($user['verified'] ? 'Yes' : 'No') . "<br>";
            echo "Password Hash Length: " . strlen($user['password']) . " characters<br><br>";
        }
    } else {
        echo "❌ Error creating admin user: " . mysqli_stmt_error($stmt) . "<br>";
    }
    mysqli_stmt_close($stmt);
} else {
    echo "❌ Error preparing statement: " . mysqli_error($conn) . "<br>";
}

echo "<h2>Step 4: Test Password Verification</h2>";

// Test if password verification works
$test_query = mysqli_query($conn, "SELECT password FROM users WHERE email = 'admin@school.edu'");
if (mysqli_num_rows($test_query) > 0) {
    $test_user = mysqli_fetch_assoc($test_query);
    $stored_hash = $test_user['password'];
    
    if (password_verify('admin123', $stored_hash)) {
        echo "✅ Password verification test PASSED<br>";
        echo "The password 'admin123' correctly matches the stored hash<br><br>";
    } else {
        echo "❌ Password verification test FAILED<br>";
        echo "There's an issue with password hashing<br><br>";
    }
} else {
    echo "❌ No admin user found for testing<br><br>";
}

echo "<h2>🎉 SETUP COMPLETE!</h2>";
echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
echo "<strong>✅ Your admin login details:</strong><br>";
echo "<strong>URL:</strong> <a href='admin/login.php' target='_blank'>admin/login.php</a><br>";
echo "<strong>Email:</strong> admin@school.edu<br>";
echo "<strong>Password:</strong> admin123<br>";
echo "</div>";

echo "<h3>🔍 Troubleshooting Tips:</h3>";
echo "<ul>";
echo "<li>Make sure you're using the exact email: <code>admin@school.edu</code></li>";
echo "<li>Make sure you're using the exact password: <code>admin123</code></li>";
echo "<li>Clear your browser cache and cookies</li>";
echo "<li>Make sure your web server has PHP sessions enabled</li>";
echo "<li>Check that your MySQL server is running</li>";
echo "</ul>";

echo "<p><a href='admin/login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Try Admin Login Now</a></p>";

mysqli_close($conn);
?>

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    max-width: 800px;
    margin: 20px auto;
    padding: 20px;
    background: #f8f9fa;
    line-height: 1.6;
}

h1 { color: #dc3545; }
h2 { color: #007bff; margin-top: 30px; }
h3 { color: #28a745; }

code {
    background: #e9ecef;
    padding: 2px 4px;
    border-radius: 3px;
    font-family: 'Courier New', monospace;
}

.success { color: #28a745; }
.error { color: #dc3545; }
.warning { color: #ffc107; }
</style>