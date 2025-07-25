<?php
include 'config.php';

// Admin user details
$name = 'Admin User';
$email = 'admin@school.edu';
$password = 'admin123';
$role = 'admin';

// Hash the password properly
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Delete existing admin user if exists
$delete_query = "DELETE FROM users WHERE email = '$email'";
mysqli_query($conn, $delete_query);

// Insert new admin user
$insert_query = "INSERT INTO users (name, email, password, role, verified) VALUES (?, ?, ?, ?, TRUE)";
$stmt = mysqli_prepare($conn, $insert_query);
mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $hashed_password, $role);

if (mysqli_stmt_execute($stmt)) {
    echo "✅ Admin user created successfully!<br>";
    echo "<strong>Email:</strong> admin@school.edu<br>";
    echo "<strong>Password:</strong> admin123<br>";
    echo "<br>You can now login to the admin panel at: <a href='admin/login.php'>admin/login.php</a>";
} else {
    echo "❌ Error creating admin user: " . mysqli_error($conn);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 600px;
    margin: 50px auto;
    padding: 20px;
    background: #f5f5f5;
}
</style>