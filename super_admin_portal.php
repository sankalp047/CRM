<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root"; // Replace with your database username
$password = ""; // Replace with your database password
$dbname = "funasiacrm"; // Replace with your database name

$conn = new mysqli($servername, $username, $password, $dbname);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Enable detailed error reporting for MySQL

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Separate login validation logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && isset($_POST['password']) && !isset($_POST['create_account'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Query the SuperAdmin table
    $sql = "SELECT * FROM SuperAdmin WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password, $row['password'])) {
            // Set session variables for logged-in user
            $_SESSION['super_admin_logged_in'] = true;
            $_SESSION['super_admin_id'] = $row['SuperAdmin_ID'];
        } else {
            echo "<script>alert('Wrong Password!');</script>";
            echo "<script>window.location.href = 'super_admin.php';</script>";
            exit;
        }
    } else {
        echo "<script>alert('No user found with this email!');</script>";
        echo "<script>window.location.href = 'super_admin.php';</script>";
        exit;
    }
}

// Redirect to login page if not logged in
if (!isset($_SESSION['super_admin_logged_in']) || $_SESSION['super_admin_logged_in'] !== true) {
    header("Location: super_admin.php");
    exit;
}

// Handle account creation logic separately
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_account'])) {
    $role = $_POST['role'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Secure password hashing
    $phone = $_POST['phone'];

    // Determine the table based on the selected role
    $table = "";
    switch ($role) {
        case "Sales Admin":
            $table = "AdminSales";
            break;
        case "Production Admin":
            $table = "AdminProduction";
            break;
        case "Sales Rep":
            $table = "Salesperson";
            break;
        case "Radio Jockey":
            $table = "RJ";
            break;
        default:
            $table = "";
            break;
    }

    if ($table) {
        // Debug: Check table and input values
        echo "Inserting into table: $table<br>";
        echo "Name: $name, Email: $email, Password: (hashed), Phone: $phone<br>";

        // Insert into the appropriate table
        $sql = "INSERT INTO $table (name, email, password, phone) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $name, $email, $password, $phone);

        if ($stmt->execute()) {
            echo "<script>alert('Account created successfully!');</script>";
        } else {
            echo "<script>alert('Error: " . $stmt->error . "');</script>";
        }
    } else {
        echo "<script>alert('Invalid role selected!');</script>";
    }
}

// Handle logout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    session_destroy();
    header("Location: super_admin.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Portal</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .portal-container {
            background: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 400px;
            text-align: center;
        }
        .portal-container h1 {
            font-size: 1.5rem;
            margin-bottom: 20px;
        }
        .portal-container form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .portal-container select, .portal-container input, .portal-container button {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
        }
        .logout-button {
            background-color: #f0c808;
            color: #000;
            border: 1px solid #000;
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            text-align: center;
            margin-top: 10px;
        }
        .logout-button:hover {
            background-color: #e5b505;
        }
    </style>
</head>
<body>
    <div class="portal-container">
        <h1>Welcome, Super Admin</h1>
        <!-- Form to add other 4 roles -->
        <form method="POST">
            <select name="role" required>
                <option value="" disabled selected>Select Role</option>
                <option value="Sales Admin">Sales Admin</option>
                <option value="Production Admin">Production Admin</option>
                <option value="Sales Rep">Sales Rep</option>
                <option value="Radio Jockey">Radio Jockey</option>
            </select>
            <input type="text" name="name" placeholder="Enter Name" required>
            <input type="email" name="email" placeholder="Enter Email" required>
            <input type="password" name="password" placeholder="Enter Password" required>
            <input type="tel" name="phone" placeholder="Enter Phone Number" required>
            <button type="submit" name="create_account">Create Account</button>
        </form>
        <!-- Logout button -->
        <form method="POST">
            <button type="submit" name="logout" class="logout-button">Logout</button>
        </form>
    </div>
</body>
</html>
