<?php
session_start();
require_once 'config.php';

// If user is already logged in, redirect to appropriate dashboard
if (isLoggedIn()) {
    $role = $_SESSION['role'];
    redirect("{$role}/dashboard.php");
}

// Otherwise, redirect to the main homepage
redirect('index.html');
?>