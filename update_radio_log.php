<?php
session_start();
require 'database.php'; // Ensure your DB connection is available
// Logout handling
if (isset($_POST['logout'])) {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    header("Location: sales_rep.php");
    exit();
}
// Check if the production admin is logged in; if not, redirect to production_admin.php
if (!isset($_SESSION['production_admin_logged_in'])) {
    header("Location: production_admin.php");
    exit();
}

// If the form is submitted (POST request), process the update.
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Retrieve the original keys from hidden inputs.
    $log_id = $_POST['log_id'];
    $original_client_name = $_POST['original_client_name'];

    // Get the updated form values.
    // The client category is submitted via radio buttons, though not stored in our current table.
    $client_category    = $_POST['client_category'];
    $client_id          = $_POST['client_id']; // New client selection (as an ID)
    $log_date           = $_POST['log_date'];
    $start_time         = $_POST['start_time'];
    $end_time           = $_POST['end_time'];
    $deliverable_type   = $_POST['deliverable_type'];
    $deliverable_detail = $_POST['deliverable_detail'];

    // Retrieve the client's business name from the client table using client_id.
    $stmt = $conn->prepare("SELECT business FROM client WHERE C_ID = ?");
    if ($stmt) {
        $stmt->bind_param("i", $client_id);
        $stmt->execute();
        $stmt->bind_result($business);
        if (!$stmt->fetch()) {
            $stmt->close();
            die("Client not found.");
        }
        $stmt->close();
    } else {
        die("Error preparing client lookup: " . $conn->error);
    }

    // Update the radio_log record.
    $update_stmt = $conn->prepare("UPDATE radio_log 
        SET client_name = ?, log_date = ?, start_time = ?, end_time = ?, deliverable_type = ?, deliverable_detail = ?, log_update_timestamp = CURRENT_TIMESTAMP, C_ID = ? 
        WHERE log_id = ? AND client_name = ?");
    if ($update_stmt) {
        // Updated bind_param type string now is "ssssssiis" (6 strings, 2 ints, 1 string)
        $update_stmt->bind_param("ssssssiis", $business, $log_date, $start_time, $end_time, $deliverable_type, $deliverable_detail, $client_id, $log_id, $original_client_name);
        if ($update_stmt->execute()) {
            $update_stmt->close();
            header("Location: production_admin_portal.php?msg=log_updated");
            exit();
        } else {
            die("Error updating log: " . $update_stmt->error);
        }
    } else {
        die("Error preparing update: " . $conn->error);
    }
} else {
    // For a GET request, retrieve the current values for the log record.
    if (!isset($_GET['log_id']) || !isset($_GET['client_name'])) {
        header("Location: production_admin_portal.php");
        exit();
    }
    $log_id = $_GET['log_id'];
    $client_name = $_GET['client_name'];

    $stmt = $conn->prepare("SELECT log_id, client_name, log_date, start_time, end_time, deliverable_type, deliverable_detail, C_ID 
        FROM radio_log WHERE log_id = ? AND client_name = ?");
    if ($stmt) {
        $stmt->bind_param("is", $log_id, $client_name);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 0) {
            $stmt->close();
            die("Log entry not found.");
        }
        $log_entry = $result->fetch_assoc();
        $stmt->close();
    } else {
        die("Error preparing query: " . $conn->error);
    }

    // Fetch all clients for the dropdown using the 'business' field.
    $clients = [];
    $clientQuery = "SELECT C_ID, business FROM client";
    if ($result = $conn->query($clientQuery)) {
        while ($row = $result->fetch_assoc()) {
            $clients[] = $row;
        }
        $result->free();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Update Radio Log Entry</title>
  
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- jQuery (required for bootstrap-select) -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- Bootstrap-Select CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta2/css/bootstrap-select.min.css" integrity="sha512-F2T7EnkAywXNUdp2iOezwhn81qZGn8iA9fRP2N7K8IvF/E2vDpdaFGn1DVB2ek4l+2w6GNDj6lXwTHd9xJ+IOw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <!-- Bootstrap-Select JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta2/js/bootstrap-select.min.js" integrity="sha512-FfUtGLtCzgBAvCnJ7dY60ZeY3dqzXa4pOj8l3NVMVRZ7aNS2WSE5EGZVnCFsE9lIobEnBk6Q+tLLOUjXrYy7iw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    $(document).ready(function(){
      $('.selectpicker').selectpicker();
    });
  </script>
</head>
<body class="bg-light">
  <!-- Navbar (similar to production_admin_portal.php) -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">Production Admin Portal</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
              aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <!-- Logout button -->
          <li class="nav-item">
            <form method="POST" class="d-inline">
              <button type="submit" name="logout" class="btn btn-outline-light">Logout</button>
            </form>
          </li>
        </ul>
      </div>
    </div>
  </nav>
  
  <!-- Content spacing -->
  <div class="container mt-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <h4 class="card-title">Update Radio Log Entry</h4>
        <form method="POST" action="update_radio_log.php">
          <!-- Hidden fields for original primary key values -->
          <input type="hidden" name="log_id" value="<?php echo htmlspecialchars($log_entry['log_id']); ?>">
          <input type="hidden" name="original_client_name" value="<?php echo htmlspecialchars($log_entry['client_name']); ?>">
          
          <!-- Client Category Radio Group -->
          <div class="mb-3">
            <label class="form-label">Client Category</label>
            <div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="client_category" id="funasia" value="FunAsia" required>
                <label class="form-check-label" for="funasia">FunAsia</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="client_category" id="radiocaravan" value="Radio Caravan" required>
                <label class="form-check-label" for="radiocaravan">Radio Caravan</label>
              </div>
            </div>
          </div>
          
          <!-- Client Business Dropdown (using bootstrap-select for searchability) -->
          <div class="mb-3">
            <label for="client_id" class="form-label">Client Business</label>
            <select class="form-select selectpicker" data-live-search="true" id="client_id" name="client_id" required>
              <option value="" disabled>Select a client</option>
              <?php foreach($clients as $client): ?>
                <option value="<?php echo htmlspecialchars($client['C_ID']); ?>"
                  <?php echo ($client['business'] == $log_entry['client_name']) ? "selected" : ""; ?>>
                  <?php echo htmlspecialchars($client['business']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <!-- Date Input -->
          <div class="mb-3">
            <label for="log_date" class="form-label">Date</label>
            <input type="date" class="form-control" id="log_date" name="log_date" value="<?php echo htmlspecialchars($log_entry['log_date']); ?>" required>
          </div>
          <!-- Start Time Input -->
          <div class="mb-3">
            <label for="start_time" class="form-label">Start Time</label>
            <input type="time" class="form-control" id="start_time" name="start_time" value="<?php echo htmlspecialchars($log_entry['start_time']); ?>" required>
          </div>
          <!-- End Time Input -->
          <div class="mb-3">
            <label for="end_time" class="form-label">End Time</label>
            <input type="time" class="form-control" id="end_time" name="end_time" value="<?php echo htmlspecialchars($log_entry['end_time']); ?>" required>
          </div>
          <!-- Deliverable Type -->
          <div class="mb-3">
            <label for="deliverable_type" class="form-label">Deliverable Type</label>
            <select class="form-select" id="deliverable_type" name="deliverable_type" required>
              <option value="" disabled>Select type</option>
              <option value="live announcement" <?php echo ($log_entry['deliverable_type'] == 'live announcement') ? "selected" : ""; ?>>Live Announcement</option>
              <option value="interview" <?php echo ($log_entry['deliverable_type'] == 'interview') ? "selected" : ""; ?>>Interview</option>
              <option value="giveaway" <?php echo ($log_entry['deliverable_type'] == 'giveaway') ? "selected" : ""; ?>>Giveaway</option>
              <option value="other" <?php echo ($log_entry['deliverable_type'] == 'other') ? "selected" : ""; ?>>Other</option>
            </select>
          </div>
          <!-- Deliverable Detail -->
          <div class="mb-3">
            <label for="deliverable_detail" class="form-label">Deliverable Detail</label>
            <textarea class="form-control" id="deliverable_detail" name="deliverable_detail" rows="3" placeholder="Enter details here"><?php echo htmlspecialchars($log_entry['deliverable_detail']); ?></textarea>
          </div>
          <button type="submit" class="btn btn-primary w-100">Update Log Entry</button>
        </form>
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
