<?php
session_start();
require 'database.php'; // Include your database connection file

// Check if the user is logged in
if (!isset($_SESSION['sales_rep_logged_in'])) {
    header("Location: sales_rep.php");
    exit();
}

// Get Salesperson_ID from the session (ensure it's stored during login)
$salesperson_email = $_SESSION['sales_rep_email'];
$salesperson_id = null;

// Fetch the Salesperson_ID from the database
$stmt = $conn->prepare("SELECT Salesperson_ID FROM salesperson WHERE email = ?");
if ($stmt) {
    $stmt->bind_param("s", $salesperson_email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $salesperson_id = $row['Salesperson_ID'];
    } else {
        die("Salesperson not found.");
    }
} else {
    die("SQL Error: " . $conn->error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $c_name = trim($_POST['c_name']);
    $business = trim($_POST['business']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);

    // Validate required fields
    if (empty($c_name)) {
        $error = "Client name is required.";
    } elseif (empty($phone)) {
        $error = "Phone number is mandatory.";
    } elseif (empty($email)) {
        $error = "Email is mandatory.";
    } else {
        // Check if client with the same phone or email already exists
        $check_stmt = $conn->prepare("SELECT * FROM client WHERE phone = ? OR email = ?");
        if ($check_stmt) {
            $check_stmt->bind_param("ss", $phone, $email);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            if ($check_result && $check_result->num_rows > 0) {
                $error = "Client with this phone number or email already exists.";
            } else {
                // Insert data into the client table
                $stmt = $conn->prepare("INSERT INTO client (C_NAME, business, phone, email, address, Salesperson_ID) VALUES (?, ?, ?, ?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("sssssi", $c_name, $business, $phone, $email, $address, $salesperson_id);
                    if ($stmt->execute()) {
                        $success = "Client added successfully.";
                    } else {
                        $error = "Error adding client: " . $stmt->error;
                    }
                } else {
                    $error = "SQL Error: " . $conn->error;
                }
            }
        } else {
            $error = "SQL Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Client</title>
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
        .form-container {
            background: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 400px;
        }
        .form-container h1 {
            font-size: 1.5rem;
            margin-bottom: 20px;
        }
        .form-container input, .form-container textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
        }
        .form-container button {
            background-color: #f0c808;
            color: #000;
            border: 1px solid #000;
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            width: 100%;
        }
        .form-container button:hover {
            background-color: #e5b505;
        }
        .error {
            color: red;
            margin-bottom: 15px;
        }
        .success {
            color: green;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Create Client</h1>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <?php if (isset($success)) echo "<p class='success'>$success</p>"; ?>
        <form method="POST" action="create_client.php">
            <input type="text" name="c_name" placeholder="Client Name" required>
            <input type="text" name="business" placeholder="Business">
            <input type="text" name="phone" placeholder="Phone" required>
            <input type="email" name="email" placeholder="Email" required>
            <textarea name="address" placeholder="Address"></textarea>
            <button type="submit">Add Client</button>
        </form>
    </div>
</body>
</html>
