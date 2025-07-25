<?php
include 'config.php';

echo "<h2>Database Connection Test</h2>";

// Test connection
if ($conn->connect_error) {
    echo "❌ Connection failed: " . $conn->connect_error;
} else {
    echo "✅ Database connected successfully!<br>";
    
    // Test if users table exists
    $result = mysqli_query($conn, "SHOW TABLES LIKE 'users'");
    if (mysqli_num_rows($result) > 0) {
        echo "✅ Users table exists<br>";
        
        // Check if admin user exists
        $admin_check = mysqli_query($conn, "SELECT * FROM users WHERE email = 'admin@school.edu'");
        if (mysqli_num_rows($admin_check) > 0) {
            echo "✅ Admin user found in database<br>";
            $admin = mysqli_fetch_assoc($admin_check);
            echo "Admin name: " . $admin['name'] . "<br>";
            echo "Admin email: " . $admin['email'] . "<br>";
            echo "Admin role: " . $admin['role'] . "<br>";
        } else {
            echo "❌ Admin user NOT found. Run create_admin.php<br>";
        }
    } else {
        echo "❌ Users table doesn't exist. Import db.sql first<br>";
    }
}

mysqli_close($conn);
?>

<style>
body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
</style>