<?php
$servername = "localhost";
$username = "root"; // Adjust based on your environment
$password = ""; // Adjust based on your environment
$dbname = "employee_attendance";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
