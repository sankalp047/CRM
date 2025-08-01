<?php
session_start();
require 'database.php'; // Include your database connection file

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error()); // Debug database connection
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Fetch and sanitize user inputs
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Ensure email and password are not empty
    if (empty($email) || empty($password)) {
        $error = "Email and password are required.";
    } else {
        // Prepare SQL query to retrieve user by email
        $stmt = $conn->prepare("SELECT * FROM salesperson WHERE email = ?");
        if ($stmt === false) {
            die("SQL Error: " . $conn->error); // Debugging SQL error
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
                $_SESSION['sales_rep_logged_in'] = true;
                $_SESSION['sales_rep_email'] = $user['email'];
                $_SESSION['sales_rep_id'] = $user['Salesperson_ID'];

                // Update last login timestamp
                $update_stmt = $conn->prepare("UPDATE salesperson SET last_login = CURRENT_TIMESTAMP WHERE email = ?");
                if ($update_stmt === false) {
                    die("SQL Error on Update: " . $conn->error); // Debugging update error
                }
                $update_stmt->bind_param("s", $email);
                $update_stmt->execute();

                // Redirect to portal
                header("Location: sales_rep_portal.php");
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
  <title>Sales Rep Login</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Google Font (optional) -->
  <link href="https://fonts.googleapis.com/css?family=Roboto:400,500&display=swap" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100 bg-secondary"> <!-- Added flexbox classes -->
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top"> <!-- Added sticky-top -->
    <div class="container-fluid">
      <a class="navbar-brand" href="#">Sales Rep Portal Login</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
              aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <!-- You can add additional nav links here if needed -->
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <a class="nav-link" href="index.php">Home</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>
  
  <!-- Login Form Container -->
  <div class="container mt-5 flex-grow-1"> <!-- Added flex-grow-1 to make content grow -->
    <div class="row justify-content-center">
      <div class="col-12 col-md-6">
        <div class="card shadow-lg">
          <div class="card-body">
            <h1 class="card-title text-center mb-4">Sales Rep Login</h1>
            <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
            <form method="POST" action="sales_rep.php">
              <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
              </div>
              <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
              </div>
              <button type="submit" class="btn btn-secondary w-100">Login</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="footer mt-auto py-3 bg-dark">
    <div class="container text-center">
      <span class="text-white">© 2025 FunAsia Radio. All rights reserved.</span>
    </div>
  </footer>
</body>
</html>