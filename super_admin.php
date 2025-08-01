<?php
session_start();
require 'database.php';

// (Your login processing code here, if any)
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Super Admin Login</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="d-flex flex-column min-vh-100 bg-secondary">
  <!-- Fixed Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container">
      <a class="navbar-brand" href="#">Super Admin Portal</a>
    </div>
  </nav>

  <!-- Main Content: add top margin and padding to avoid overlap with fixed navbar -->
  <div class="container flex-grow-1 mt-5 pt-5" style="padding-top: 150px;">
    <div class="row justify-content-center">
      <div class="col-12 col-md-6">
        <div class="card shadow">
          <div class="card-body">
            <h1 class="card-title text-center mb-4">Super Admin Login</h1>
            <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
            <form action="super_admin_portal.php" method="POST">
              <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="Enter your email" required>
              </div>
              <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
              </div>
              <button type="submit" class="btn btn-secondary w-100">Login</button>
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
</body>
</html>
