<?php
$servername = "localhost";
$username = "root";        // default for XAMPP
$password = "";            // leave empty for XAMPP
$dbname = "clothingstore";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Optional (good practice)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
?>