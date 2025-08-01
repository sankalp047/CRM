<?php
session_start();
require 'database.php';

// 1) Authentication
if (!isset($_SESSION['production_admin_logged_in'])) {
    header("Location: production_admin.php");
    exit();
}

// 2) Logout
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: production_admin.php");
    exit();
}

// 3) Fetch clients for dropdown
$clients = [];
if ($result = $conn->query("SELECT C_ID, business FROM client")) {
    while ($row = $result->fetch_assoc()) {
        $clients[] = $row;
    }
    $result->free();
}

// 4) Fetch approved live announcements
$approvedLA = [];
$sqlLA = "
  SELECT 
    ca.CA_ID,
    c.business,
    la.La_Name        AS la_name,
    la.la_request_time,
    la.start_date     AS la_start,
    la.end_date       AS la_end
  FROM campaign ca
  JOIN client c            ON ca.C_ID = c.C_ID
  JOIN liveannouncement la ON la.CA_ID = ca.CA_ID
  WHERE ca.status = 'approved'
  ORDER BY ca.CA_ID DESC
";
if (!($res = $conn->query($sqlLA))) {
    die("SQL Error (Live Announcements): " . $conn->error);
}
while ($row = $res->fetch_assoc()) {
    $approvedLA[] = $row;
}
$res->free();

// 5) Fetch approved interviews
$approvedInt = [];
$sqlInt = "
  SELECT 
    ca.CA_ID,
    c.business,
    i.IN_NAME AS int_name,
    i.IN_TIME AS int_time,
    i.IN_DATE AS int_date,
    i.IN_INFO AS int_info
  FROM campaign ca
  JOIN client c       ON ca.C_ID = c.C_ID
  JOIN interview i    ON i.CA_ID = ca.CA_ID
  WHERE ca.status = 'approved'
  ORDER BY ca.CA_ID DESC
";
if (!($res = $conn->query($sqlInt))) {
    die("SQL Error (Interviews): " . $conn->error);
}
while ($row = $res->fetch_assoc()) {
    $approvedInt[] = $row;
}
$res->free();

// 6) Fetch approved giveaways
$approvedGW = [];
$sqlGW = "
  SELECT 
    ca.CA_ID,
    c.business,
    g.G_NAME        AS gw_name,
    g.G_TIME        AS gw_time,
    g.G_INFO        AS gw_info,
    g.G_START_DATE  AS gw_start,
    g.G_END_DATE    AS gw_end
  FROM campaign ca
  JOIN client c      ON ca.C_ID = c.C_ID
  JOIN `Giveaways` g ON g.CA_ID = ca.CA_ID
  WHERE ca.status = 'approved'
  ORDER BY ca.CA_ID DESC
";
if (!($res = $conn->query($sqlGW))) {
    die("SQL Error (Giveaways): " . $conn->error);
}
while ($row = $res->fetch_assoc()) {
    $approvedGW[] = $row;
}
$res->free();

// 7) Fetch radio log entries
$radio_logs = [];
if ($result = $conn->query("SELECT * FROM radio_log")) {
    while ($row = $result->fetch_assoc()) {
        $radio_logs[] = $row;
    }
    $result->free();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Production Admin Portal</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light">
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">Production Admin Portal</a>
      <button class="navbar-toggler" type="button"
              data-bs-toggle="collapse" data-bs-target="#navbarNav"
              aria-controls="navbarNav" aria-expanded="false"
              aria-label="Toggle navigation">
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

  <!-- Success alert -->
  <?php if (isset($_GET['msg']) && $_GET['msg'] === 'log_saved'): ?>
    <script>alert("Log saved.");</script>
  <?php endif; ?>

  <div class="container mt-4">
    <div class="row">
      <!-- Left: Add Radio Log -->
      <div class="col-12 col-md-4">
        <div class="card shadow-sm mb-4">
          <div class="card-body">
            <h4 class="card-title">Add Radio Log Entry</h4>
            <form method="POST" action="process_radio_log.php">
              <div class="mb-3">
                <label for="client_id" class="form-label">Client Business</label>
                <select class="form-select" id="client_id" name="client_id" required>
                  <option disabled selected>Select a client</option>
                  <?php foreach ($clients as $cl): ?>
                    <option value="<?= htmlspecialchars($cl['C_ID']) ?>">
                      <?= htmlspecialchars($cl['business']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="mb-3">
                <label for="log_date" class="form-label">Date</label>
                <input type="date" class="form-control" id="log_date" name="log_date" required>
              </div>
              <div class="mb-3">
                <label for="start_time" class="form-label">Start Time</label>
                <input type="time" class="form-control" id="start_time" name="start_time" required>
              </div>
              <div class="mb-3">
                <label for="end_time" class="form-label">End Time</label>
                <input type="time" class="form-control" id="end_time" name="end_time" required>
              </div>
              <div class="mb-3">
                <label for="deliverable_type" class="form-label">Deliverable Type</label>
                <select class="form-select" id="deliverable_type" name="deliverable_type" required>
                  <option disabled selected>Select type</option>
                  <option value="live announcement">Live Announcement</option>
                  <option value="interview">Interview</option>
                  <option value="giveaway">Giveaway</option>
                  <option value="other">Other</option>
                </select>
              </div>
              <div class="mb-3">
                <label for="deliverable_detail" class="form-label">Detail</label>
                <textarea class="form-control" id="deliverable_detail" name="deliverable_detail" rows="3"></textarea>
              </div>
              <div class="mb-3">
                <label class="form-label">Station</label>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="station[]" id="funasia" value="FunAsia">
                  <label class="form-check-label" for="funasia">FunAsia</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="station[]" id="caravan" value="Caravan">
                  <label class="form-check-label" for="caravan">Caravan</label>
                </div>
              </div>
              <button type="submit" class="btn btn-primary w-100">Add Log Entry</button>
            </form>
          </div>
        </div>
      </div>

      <!-- Right: Approved Lists & Radio Logs -->
      <div class="col-12 col-md-8">
        <!-- Approved Live Announcements -->
        <div class="card shadow-sm mb-4">
          <div class="card-body">
            <h4 class="card-title">Approved Live Announcements</h4>
            <?php if (empty($approvedLA)): ?>
              <p class="text-muted">None found.</p>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-striped table-hover">
                  <thead class="table-dark">
                    <tr>
                      <th>Business</th>
                      <th>Name</th>
                      <th>Req. Time</th>
                      <th>Start</th>
                      <th>End</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($approvedLA as $r): ?>
                      <tr>
                        <td><?= htmlspecialchars($r['business']) ?></td>
                        <td><?= htmlspecialchars($r['la_name']) ?></td>
                        <td><?= htmlspecialchars($r['la_request_time']) ?></td>
                        <td><?= htmlspecialchars($r['la_start']) ?></td>
                        <td><?= htmlspecialchars($r['la_end']) ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Approved Interviews -->
        <div class="card shadow-sm mb-4">
          <div class="card-body">
            <h4 class="card-title">Approved Interviews</h4>
            <?php if (empty($approvedInt)): ?>
              <p class="text-muted">None found.</p>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-striped table-hover">
                  <thead class="table-dark">
                    <tr>
                      <th>Business</th>
                      <th>Name</th>
                      <th>Time</th>
                      <th>Date</th>
                      <th>Info</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($approvedInt as $r): ?>
                      <tr>
                        <td><?= htmlspecialchars($r['business']) ?></td>
                        <td><?= htmlspecialchars($r['int_name']) ?></td>
                        <td><?= htmlspecialchars($r['int_time']) ?></td>
                        <td><?= htmlspecialchars($r['int_date']) ?></td>
                        <td><?= htmlspecialchars($r['int_info']) ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Approved Giveaways -->
        <div class="card shadow-sm mb-4">
          <div class="card-body">
            <h4 class="card-title">Approved Giveaways</h4>
            <?php if (empty($approvedGW)): ?>
              <p class="text-muted">None found.</p>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-striped table-hover">
                  <thead class="table-dark">
                    <tr>
                      <th>Business</th>
                      <th>Name</th>
                      <th>Time</th>
                      <th>Info</th>
                      <th>Start</th>
                      <th>End</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($approvedGW as $r): ?>
                      <tr>
                        <td><?= htmlspecialchars($r['business']) ?></td>
                        <td><?= htmlspecialchars($r['gw_name']) ?></td>
                        <td><?= htmlspecialchars($r['gw_time']) ?></td>
                        <td><?= htmlspecialchars($r['gw_info']) ?></td>
                        <td><?= htmlspecialchars($r['gw_start']) ?></td>
                        <td><?= htmlspecialchars($r['gw_end']) ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Radio Log Entries -->
        <div class="card shadow-sm mb-4">
          <div class="card-body">
            <h4 class="card-title">Radio Log Entries</h4>
            <?php if (empty($radio_logs)): ?>
              <p class="text-muted">No log entries found.</p>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-striped table-hover">
                  <thead class="table-dark">
                    <tr>
                      <th>Log ID</th>
                      <th>Client</th>
                      <th>Date</th>
                      <th>Start</th>
                      <th>End</th>
                      <th>Type</th>
                      <th>Detail</th>
                      <th>Station</th>
                      <th>Updated</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($radio_logs as $log): ?>
                      <tr>
                        <td><?= htmlspecialchars($log['log_id']) ?></td>
                        <td><?= htmlspecialchars($log['client_name']) ?></td>
                        <td><?= htmlspecialchars($log['log_date']) ?></td>
                        <td><?= htmlspecialchars($log['start_time']) ?></td>
                        <td><?= htmlspecialchars($log['end_time']) ?></td>
                        <td><?= htmlspecialchars($log['deliverable_type']) ?></td>
                        <td><?= htmlspecialchars($log['deliverable_detail']) ?></td>
                        <td><?= htmlspecialchars($log['station']) ?></td>
                        <td><?= htmlspecialchars($log['log_update_timestamp']) ?></td>
                        <td>
                          <a href="update_radio_log.php?log_id=<?= urlencode($log['log_id']) ?>"
                             class="btn btn-sm btn-warning">Update</a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="footer mt-auto py-3 bg-dark fixed-bottom">
    <div class="container text-center">
      <span class="text-white">© 2025 FunAsia Radio. All rights reserved.</span>
    </div>
  </footer>
</body>
</html>
