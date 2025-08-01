<?php
session_start();
require 'database.php'; // Include your database connection file

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Fetch and sanitize user inputs
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $station = trim($_POST['station']);  // New: Station selection from radio buttons

    // Ensure email, password, and station are not empty
    if (empty($email) || empty($password) || empty($station)) {
        $error = "Email, password, and station are required.";
    } else {
        // Prepare SQL query to retrieve user by email from the rj table
        $stmt = $conn->prepare("SELECT * FROM rj WHERE email = ?");
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
                // Update the rj table with the chosen station.
                // (Assumes that the rj table has been altered to include a `station` column.)
                $update_stmt = $conn->prepare("UPDATE rj SET station = ? WHERE email = ?");
                if ($update_stmt === false) {
                    die("SQL Error on Update: " . $conn->error);
                }
                $update_stmt->bind_param("ss", $station, $email);
                $update_stmt->execute();
                $update_stmt->close();

                // Set session variables
                $_SESSION['rj_logged_in'] = true;
                $_SESSION['rj_email'] = $user['email'];
                $_SESSION['rj_station'] = $station;  // Save station in session

                // Update last login timestamp
                $update_stmt2 = $conn->prepare("UPDATE rj SET last_login = CURRENT_TIMESTAMP WHERE email = ?");
                if ($update_stmt2 === false) {
                    die("SQL Error on Update: " . $conn->error);
                }
                $update_stmt2->bind_param("s", $email);
                $update_stmt2->execute();
                $update_stmt2->close();

                // Redirect to the radio jockey portal
                header("Location: radio_jockey_portal.php");
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
  <title>Radio Jockey Login</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Google Font (optional) -->
  <link href="https://fonts.googleapis.com/css?family=Roboto:400,500&display=swap" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100 bg-secondary">
  <!-- Fixed Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">Radio Jockey Portal Login</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
              aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <!-- Additional nav links can be added here -->
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
  <!-- Added pt-5 (padding-top) to create more space from the navbar -->
  <div class="container mt-5 pt-5 flex-grow-1">
    <div class="row justify-content-center">
      <div class="col-12 col-md-6">
        <div class="card shadow-lg">
          <div class="card-body">
            <h1 class="card-title text-center mb-4">Radio Jockey Login</h1>
            <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
            <form method="POST" action="radio_jockey.php">
              <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
              </div>
              <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
              </div>
              <!-- Station Selection: Only one option can be selected -->
              <div class="mb-3">
                <label class="form-label">Station</label>
                <div>
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="station" id="stationFunAsia" value="FunAsia" required>
                    <label class="form-check-label" for="stationFunAsia">FunAsia</label>
                  </div>
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="station" id="stationCaravan" value="Radio Caravan" required>
                    <label class="form-check-label" for="stationCaravan">Radio Caravan</label>
                  </div>
                </div>
              </div>
              <button type="submit" class="btn btn-secondary w-100">Login</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Fixed Footer -->
  <footer class="footer mt-auto py-3 bg-dark fixed-bottom">
    <div class="container text-center">
      <span class="text-white">© 2025 FunAsia Radio. All rights reserved.</span>
    </div>
  </footer>
</body>
</html>
