<?php
session_start();
require 'database.php'; // Ensure your DB connection is available

// 1) Authentication
if (!isset($_SESSION['sales_rep_logged_in'])) {
    header('Location: sales_rep.php');
    exit();
}

// 2) Logout
if (isset($_POST['logout'])) {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'],
            $p['secure'], $p['httponly']
        );
    }
    session_destroy();
    header("Location: sales_rep.php");
    exit();
}

// 3) Ensure we have sales_rep_id in session
if (empty($_SESSION['sales_rep_id'])) {
    $email = $_SESSION['sales_rep_email'];
    $stmt  = $conn->prepare("SELECT Salesperson_ID FROM salesperson WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($id);
    if ($stmt->fetch()) {
        $_SESSION['sales_rep_id'] = $id;
    }
    $stmt->close();
}
$sales_rep_id = $_SESSION['sales_rep_id'];

// 4) Fetch campaign details — including `ca.status`
$campaignsDetails = [];
$sql = "
  SELECT 
      c.BUSINESS,
      ca.CA_NAME,
      ca.status                       AS status,
      GROUP_CONCAT(g.G_END_DATE)      AS giveaway_end_dates,
      GROUP_CONCAT(i.IN_INFO)         AS interview_infos,
      GROUP_CONCAT(la.end_date)       AS liveannouncement_end_dates
  FROM campaign ca
  JOIN client c        ON ca.C_ID = c.C_ID
  LEFT JOIN giveaways g       ON ca.CA_ID = g.CA_ID
  LEFT JOIN interview i       ON ca.CA_ID = i.CA_ID
  LEFT JOIN liveannouncement la ON ca.CA_ID = la.CA_ID
  WHERE ca.Salesperson_ID = ?
  GROUP BY ca.CA_ID
  LIMIT 25
";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $sales_rep_id);
    $stmt->execute();
    $campaignsDetails = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sales Rep Portal</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css?family=Roboto:400,500&display=swap" rel="stylesheet">
  <style>
    /* reduce card padding so more vertical space */
    .card-body { padding: 1rem !important; }  /* p-3 */
    /* smaller font and tighter cells */
    .table th, .table td {
      font-size: 0.85rem;
      padding: 0.4rem 0.6rem;
    }
    /* allow vertical growth (no inner scroll) */
    .table-responsive {
      overflow-x: auto;
      overflow-y: visible;
    }
    /* dotted table rows */
    .dotted-table tbody tr {
      border-bottom: 1px dotted #dee2e6;
    }
    .dotted-table tbody tr:last-child {
      border-bottom: none;
    }
  </style>
</head>
<body class="bg-secondary">

  <!-- Navigation Bar (unchanged) -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">Sales Rep Portal</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
              aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <!-- ... your nav items ... -->
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

  <!-- Portal Container -->
  <div class="container mt-5 mb-5">
    <div class="row justify-content-center align-items-start">
      
      <!-- Left Column: welcome/actions (unchanged) -->
      <div class="col-12 col-md-6">
        <div class="card shadow-lg border-0">
          <div class="card-body text-center">
            <h2 class="fw-bold mb-3">Welcome to the Sales Rep Portal</h2>
            <p class="text-danger mb-3">Logged in as: <?= htmlspecialchars($_SESSION['sales_rep_email']) ?></p>
            <div class="d-grid gap-2">
              <button class="btn btn-secondary" onclick="location.href='create_client.php'">Create Client</button>
              <button class="btn btn-secondary" onclick="location.href='create_campaign.php'">Create Campaign</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Campaign Details Table -->
      <div class="col-12 col-md-6 mt-4 mt-md-0">
        <div class="card shadow-lg border-0">
          <div class="card-body">
            <h3 class="fw-bold mb-3">Your Campaigns</h3>
            <?php if (empty($campaignsDetails)): ?>
              <p class="text-muted">No campaigns found.</p>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-striped table-hover dotted-table">
                  <thead class="table-dark">
                    <tr>
                      <th>Business</th>
                      <th>Campaign Name</th>
                      <th>Status</th>
                      <th>Giveaway Ends</th>
                      <th>Interviews</th>
                      <th>Live Ends</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($campaignsDetails as $camp): ?>
                      <tr>
                        <td><?= htmlspecialchars($camp['BUSINESS']) ?></td>
                        <td><?= htmlspecialchars($camp['CA_NAME']) ?></td>
                        <td><?= htmlspecialchars($camp['status']) ?></td>
                        <td><?= $camp['giveaway_end_dates'] ?: 'N/A' ?></td>
                        <td><?= $camp['interview_infos'] ?: 'N/A' ?></td>
                        <td><?= $camp['liveannouncement_end_dates'] ?: 'N/A' ?></td>
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

  <!-- Footer (unchanged) -->
<!-- Footer -->
<footer class="footer fixed-bottom bg-dark py-3">
  <div class="container text-center">
    <span class="text-white">© 2025 FunAsia Radio. All rights reserved.</span>
  </div>
</footer>

</body>
</html>
