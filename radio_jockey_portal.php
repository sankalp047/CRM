<?php
session_start();
require 'database.php';

if (!isset($_SESSION['rj_logged_in'])) {
    header("Location: radio_jockey.php");
    exit();
}

if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: radio_jockey.php");
    exit();
}

// Ensure RJ's ID is available in session; if not, retrieve it
if (!isset($_SESSION['rj_id'])) {
    $stmt = $conn->prepare("SELECT RJ_ID FROM rj WHERE email = ?");
    $stmt->bind_param("s", $_SESSION['rj_email']);
    $stmt->execute();
    $stmt->bind_result($rj_id);
    if ($stmt->fetch()) {
        $_SESSION['rj_id'] = $rj_id;
    }
    $stmt->close();
}
$rj_id = $_SESSION['rj_id'];

// Ensure the station is set in session (set during login)
if (!isset($_SESSION['rj_station'])) {
    $_SESSION['rj_station'] = '';
}
$rj_station = $_SESSION['rj_station'];

// Adjust the station value if it contains "Radio " (e.g. "Radio Caravan" becomes "Caravan")
if (stripos($rj_station, 'radio ') !== false) {
    $rj_station = trim(str_ireplace('radio ', '', $rj_station));
}

// ----------------------------
// Retrieve Pending Updates
// ----------------------------
$sql_pending = "SELECT rl.*
FROM radio_log rl
LEFT JOIN taskupdate tu ON rl.log_id = tu.Log_ID
WHERE tu.Log_ID IS NULL
  AND CURTIME() BETWEEN rl.start_time AND rl.end_time
  AND LOWER(rl.station) = LOWER(?)
ORDER BY rl.start_time ASC";
$stmt = $conn->prepare($sql_pending);
$stmt->bind_param("s", $rj_station);
$stmt->execute();
$result_pending = $stmt->get_result();
$pending_logs = [];
while ($row = $result_pending->fetch_assoc()) {
    $pending_logs[] = $row;
}
$stmt->close();

// ----------------------------
// Retrieve Processed Updates
// ----------------------------
$sql_processed = "SELECT tu.Task_ID, tu.Log_ID, tu.update_time, tu.status, tu.comments,
                         rl.client_name, rl.start_time, rl.end_time, rl.deliverable_type, rl.deliverable_detail
FROM taskupdate tu
JOIN radio_log rl ON tu.Log_ID = rl.log_id
WHERE tu.RJ_ID = ?
ORDER BY tu.update_time DESC";
$stmt = $conn->prepare($sql_processed);
$stmt->bind_param("i", $rj_id);
$stmt->execute();
$result_processed = $stmt->get_result();
$processed_updates = [];
while ($row = $result_processed->fetch_assoc()) {
    $processed_updates[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Radio Jockey Portal</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="d-flex flex-column min-vh-100 bg-light">
  <!-- Fixed Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">Radio Jockey Portal</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
              aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <form method="POST" class="d-inline">
              <button type="submit" name="logout" class="btn btn-outline-light">Logout</button>
            </form>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Main Content (with extra top padding to avoid fixed navbar) -->
  <div class="container mt-5 pt-5 flex-grow-1" style="padding-top:150px;">
    <h2 class="text-center mb-4">Welcome to the Radio Jockey Portal</h2>
    <p class="text-center">Logged in as: <?php echo htmlspecialchars($_SESSION['rj_email']); ?></p>

    <!-- Pending Updates Section -->
    <div class="mb-5">
      <h3 class="mb-3">Pending Updates</h3>
      <?php if (empty($pending_logs)) : ?>
        <div class="alert alert-info text-center">No pending log entries to process.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-striped table-hover">
            <thead class="table-dark">
              <tr>
                <th>Action</th>
                <th>Client Name</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Type</th>
                <th>Detail</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($pending_logs as $log) : ?>
                <tr>
                  <td>
                    <!-- Each row gets its own form -->
                    <form method="POST" action="process_taskupdate.php">
                      <input type="hidden" name="log_id" value="<?php echo htmlspecialchars($log['log_id']); ?>">
                      <input type="hidden" name="rj_id" value="<?php echo htmlspecialchars($rj_id); ?>">
                      <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="process" id="process_<?php echo htmlspecialchars($log['log_id']); ?>" value="1" required>
                        <label class="form-check-label" for="process_<?php echo htmlspecialchars($log['log_id']); ?>">Process</label>
                      </div>
                      <div class="mb-2">
                        <textarea name="comments" class="form-control" placeholder="Enter comments" rows="2" required></textarea>
                      </div>
                      <!-- Added text area for additional log details -->
                      <div class="mb-2">
                        <textarea name="log_all_request" class="form-control" placeholder="Enter additional log details" rows="2" required></textarea>
                      </div>
                      <button type="submit" class="btn btn-primary btn-sm">Process</button>
                    </form>
                  </td>
                  <td><?php echo htmlspecialchars($log['client_name']); ?></td>
                  <td><?php echo htmlspecialchars($log['start_time']); ?></td>
                  <td><?php echo htmlspecialchars($log['end_time']); ?></td>
                  <td><?php echo htmlspecialchars($log['deliverable_type']); ?></td>
                  <td><?php echo htmlspecialchars($log['deliverable_detail']); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <!-- Processed Updates Section -->
    <div class="mb-5">
      <h3 class="mb-3">Processed Updates</h3>
      <?php if (empty($processed_updates)) : ?>
        <div class="alert alert-info text-center">No processed updates found.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-striped table-hover">
            <thead class="table-dark">
              <tr>
                <th>Task ID</th>
                <th>Log ID</th>
                <th>Client Name</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Type</th>
                <th>Detail</th>
                <th>Updated At</th>
                <th>Status</th>
                <th>Comments</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($processed_updates as $update) : ?>
                <tr>
                  <td><?php echo htmlspecialchars($update['Task_ID']); ?></td>
                  <td><?php echo htmlspecialchars($update['Log_ID']); ?></td>
                  <td><?php echo htmlspecialchars($update['client_name']); ?></td>
                  <td><?php echo htmlspecialchars($update['start_time']); ?></td>
                  <td><?php echo htmlspecialchars($update['end_time']); ?></td>
                  <td><?php echo htmlspecialchars($update['deliverable_type']); ?></td>
                  <td><?php echo htmlspecialchars($update['deliverable_detail']); ?></td>
                  <td><?php echo htmlspecialchars($update['update_time']); ?></td>
                  <td><?php echo htmlspecialchars($update['status']); ?></td>
                  <td><?php echo htmlspecialchars($update['comments']); ?></td>
                  <td>
                  <form method="POST" action="process_taskdelete.php" onsubmit="return confirm('Are you sure you want to delete this update?');">
    <input type="hidden" name="task_id" value="<?php echo htmlspecialchars($update['Task_ID']); ?>">
    <button type="submit" name="delete_update" class="btn btn-danger btn-sm">Delete</button>
</form>

                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
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
