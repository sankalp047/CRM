<?php
session_start();
require 'database.php'; // Include your database connection file

// Check if the user is logged in
if (!isset($_SESSION['sales_rep_logged_in'])) {
    header("Location: sales_rep.php");
    exit();
}

// Fetch clients for live search
$clients = [];
$query  = "SELECT C_ID, C_NAME FROM client";
$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $clients[] = $row;
    }
} else {
    die("SQL Error: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Create Campaign</title>
  <style>
    body {
      margin: 0; padding: 0;
      font-family: Arial, sans-serif;
      background-color: #f7f7f7;
      display: flex; justify-content: center; align-items: center;
      min-height: 100vh;
    }
    .container {
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      width: 400px;
    }
    .container h1 {
      font-size: 1.5rem;
      margin-bottom: 20px;
      text-align: center;
    }
    label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
    }
    select, input, button, textarea {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 5px;
      font-size: 1rem;
    }
    .dynamic-buttons {
      display: flex;
      justify-content: space-between;
      margin-bottom: 15px;
    }
    .dynamic-buttons button {
      width: 32%;
      cursor: pointer;
      background: #2c3e50;
      color: #fff;
      border: none;
      padding: 10px;
      border-radius: 5px;
      transition: background 0.3s;
    }
    .dynamic-buttons button:hover {
      background: #34495e;
    }
    .form-section {
      display: none;
    }
    .form-section.active {
      display: block;
    }
  </style>
  <script>
    function showButtons(clientId) {
      const station = document.getElementById('station').value;
      // Populate hidden inputs
      document.getElementById('la_station').value = station;
      document.getElementById('in_station').value = station;
      document.getElementById('g_station').value = station;

      // Show or hide the buttons
      document.getElementById('dynamic-buttons').style.display = clientId ? 'flex' : 'none';
      // Also store the client_id
      document.getElementById('la_client_id').value = clientId;
      document.getElementById('in_client_id').value = clientId;
      document.getElementById('g_client_id').value = clientId;
    }

    function toggleForm(sectionId) {
      document.querySelectorAll('.form-section').forEach(s => s.classList.remove('active'));
      document.getElementById(sectionId).classList.add('active');
    }
  </script>
</head>
<body>
  <div class="container">
    <h1>Create Campaign</h1>

    <!-- Client selector -->
    <label for="client">Search Client:</label>
    <select id="client" onchange="showButtons(this.value)">
      <option value="">-- Select Client --</option>
      <?php foreach ($clients as $c): ?>
        <option value="<?= $c['C_ID'] ?>"><?= htmlspecialchars($c['C_NAME']) ?></option>
      <?php endforeach; ?>
    </select>

    <!-- Radio Station selector -->
    <label for="station">Radio Station:</label>
    <select id="station" onchange="showButtons(document.getElementById('client').value)">
      <option value="FunAsia">FunAsia</option>
      <option value="Radio Sangam">Radio Sangam</option>
    </select>

    <!-- Buttons to choose campaign type -->
    <div id="dynamic-buttons" class="dynamic-buttons" style="display:none;">
      <button onclick="toggleForm('live-announcement-form')">Live Announcement</button>
      <button onclick="toggleForm('interview-form')">Interview</button>
      <button onclick="toggleForm('giveaways-form')">Giveaways</button>
    </div>

    <!-- Live Announcement Form -->
    <div id="live-announcement-form" class="form-section">
      <h2>Live Announcement</h2>
      <form method="POST" action="save_live_announcement.php">
        <input type="hidden" name="client_id" id="la_client_id">
        <input type="hidden" name="radio_station" id="la_station">
        <label>Announcement Name</label>
        <input type="text" name="la_name" required>
        <label>Requested Time</label>
        <input type="time" name="la_request_time" required>
        <label>Info</label>
        <textarea name="la_info"></textarea>
        <label>Start Date</label>
        <input type="date" name="start_date" required>
        <label>End Date</label>
        <input type="date" name="end_date" required>
        <button type="submit">Save Live Announcement</button>
      </form>
    </div>

    <!-- Interview Form -->
    <div id="interview-form" class="form-section">
      <h2>Interview</h2>
      <form method="POST" action="save_interview.php">
        <input type="hidden" name="client_id" id="in_client_id">
        <input type="hidden" name="radio_station" id="in_station">
        <label>Interview Name</label>
        <input type="text" name="in_name" required>
        <label>Start Time</label>
        <input type="time" name="in_start_time" required>
        <label>End Time</label>
        <input type="time" name="in_end_time" required>
        <label>Info</label>
        <textarea name="in_info"></textarea>
        <label>Interview Date</label>
        <input type="date" name="in_date" required>
        <button type="submit">Save Interview</button>
      </form>
    </div>

    <!-- Giveaways Form -->
    <div id="giveaways-form" class="form-section">
      <h2>Giveaways</h2>
      <form method="POST" action="save_giveaway.php">
        <input type="hidden" name="client_id" id="g_client_id">
        <input type="hidden" name="radio_station" id="g_station">
        <label>Giveaway Name</label>
        <input type="text" name="g_name" required>
        <label>Giveaway Time</label>
        <input type="time" name="g_time" required>
        <label>Info</label>
        <textarea name="g_info"></textarea>
        <label>Start Date</label>
        <input type="date" name="g_start_date" required>
        <label>End Date</label>
        <input type="date" name="g_end_date" required>
        <button type="submit">Save Giveaway</button>
      </form>
    </div>
  </div>
</body>
</html>
