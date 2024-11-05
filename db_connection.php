<?php
// Database connection parameters
$host = 'localhost';      // Hostname of the MariaDB server
$dbname = 'ncc'; // Replace 'your_database' with the actual database name
$username = 'root'; // Replace 'your_username' with your MariaDB username
$password = ''; // Replace 'your_password' with your MariaDB password

// Create a connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Optional: Set the charset to utf8mb4 for better encoding support
$conn->set_charset("utf8mb4");
?>
