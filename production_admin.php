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
        // Query to retrieve user by email from adminproduction table
        $stmt = $conn->prepare("SELECT * FROM adminproduction WHERE email = ?");
        if ($stmt === false) {
            die("SQL Error: " . $conn->error);
        }
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if a user with this email exists
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verify the hashed password
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['production_admin_logged_in'] = true;
                $_SESSION['production_admin_email'] = $user['email'];

                // Update last login timestamp
                $update_stmt = $conn->prepare("UPDATE adminproduction SET last_login = CURRENT_TIMESTAMP WHERE email = ?");
                $update_stmt->bind_param("s", $email);
                $update_stmt->execute();

                // Redirect to portal
                header("Location: production_admin_portal.php");
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
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Production Admin Login</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <!-- Fixed Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container">
      <a class="navbar-brand" href="#">Production Admin Portal</a>
    </div>
  </nav>

  <!-- Main Content -->
  <!-- Use extra top and bottom margins to avoid overlap with fixed navbar and footer -->
  <div class="container" style="margin-top:100px; margin-bottom:100px;">
    <div class="row justify-content-center">
      <div class="col-md-4">
        <div class="card shadow">
          <div class="card-body">
            <h1 class="card-title text-center mb-4">Production Admin Login</h1>
            <?php if (isset($error)) : ?>
              <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST" action="production_admin.php">
              <div class="mb-3">
                <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
              </div>
              <div class="mb-3">
                <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
              </div>
              <div class="d-grid">
                <button type="submit" class="btn btn-warning">Login</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Fixed Footer -->
  <footer class="bg-dark text-white text-center py-3 fixed-bottom">
    <div class="container">
      <span>© 2025 FunAsia Radio. All rights reserved.</span>
    </div>
  </footer>

  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
