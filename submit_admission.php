<?php
$servername = "localhost";
$username = "root";
$password = ""; // your db password
$dbname = "school_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

$name = $_POST['name'];
$email = $_POST['email'];
$grade = $_POST['grade'];

$sql = "INSERT INTO admissions (name, email, grade) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $name, $email, $grade);

if ($stmt->execute()) {
    echo "Admission submitted!";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
