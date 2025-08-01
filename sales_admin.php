<?php
session_start();
require 'database.php'; // Include your database connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Fetch and sanitize user inputs
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Ensure email and password are not empty
    if (empty($email) || empty($password)) {
        $error = "Email and password are required.";
    } else {
        // Query to retrieve user by email
        $stmt = $conn->prepare("SELECT * FROM AdminSales WHERE email = ?");
        if ($stmt === false) {
            die("SQL Error: " . $conn->error);
        }

        // Bind email parameter and execute
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if a user with this email exists
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verify the hashed password
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['sales_admin_logged_in'] = true;
                $_SESSION['sales_admin_email'] = $user['email'];

                // Update last login timestamp
                $update_stmt = $conn->prepare("UPDATE AdminSales SET last_login = CURRENT_TIMESTAMP WHERE email = ?");
                $update_stmt->bind_param("s", $email);
                $update_stmt->execute();

                // Redirect to portal
                header("Location: sales_admin_portal.php");
                exit();
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Admin Login</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-container {
            background: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
        }
        .login-container h1 {
            font-size: 1.5rem;
            margin-bottom: 20px;
        }
        .login-container input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
        }
        .login-container button {
            background-color: #f0c808;
            color: #000;
            border: 1px solid #000;
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            width: 100%;
        }
        .login-container button:hover {
            background-color: #e5b505;
        }
        .error {
            color: red;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Sales Admin Login</h1>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST" action="sales_admin.php">
            <input type="email" name="email" placeholder="Enter your email" required>
            <input type="password" name="password" placeholder="Enter your password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
