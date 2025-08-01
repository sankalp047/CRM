<?php
// Database connection
$servername = "localhost";
$username = "root"; // Replace with your database username
$password = ""; // Replace with your database password
$dbname = "funasiacrm"; // Replace with your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Super Admin details
$name = "Sankalp Singh";
$email = "sankalp@funasia.net"; // Replace with your desired email
$password = password_hash("admin123", PASSWORD_BCRYPT); // Replace with your desired password

// Insert query
$sql = "INSERT INTO SuperAdmin (name, email, password) VALUES ('$name', '$email', '$password')";

if ($conn->query($sql) === TRUE) {
    echo "Super Admin created successfully!";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
