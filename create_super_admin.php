<?php
// Database connection details
$servername = "localhost"; // Change if using a remote server
$username = "root"; // Change if needed
$password = ""; // Change if you have a database password
$database = "funasiacrm"; // Your database name

// Create a connection
$conn = new mysqli($servername, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Super Admin credentials
$superadmin_name = "Admin";
$superadmin_email = "admin@funasia.net";
$superadmin_password = password_hash("12345", PASSWORD_DEFAULT); // Hashing password

// Check if the super admin already exists
$sql_check = "SELECT * FROM superadmin WHERE email = ?";
$stmt = $conn->prepare($sql_check);
$stmt->bind_param("s", $superadmin_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "Super Admin already exists.";
} else {
    // Insert new Super Admin
    $sql_insert = "INSERT INTO superadmin (name, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql_insert);
    $stmt->bind_param("sss", $superadmin_name, $superadmin_email, $superadmin_password);

    if ($stmt->execute()) {
        echo "Super Admin created successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
}

// Close the connection
$stmt->close();
$conn->close();
?>
