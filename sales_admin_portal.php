<?php
// sales_admin_portal.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require 'database.php';

// 1) Authentication
if (!isset($_SESSION['sales_admin_logged_in'])) {
    header('Location: index.php');
    exit();
}

// 2) Logout
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit();
}

// 3) Fetch campaigns + client + salesperson
$sql = "
  SELECT 
    c.CA_ID,
    cl.C_NAME           AS client_name,
    cl.business         AS client_business,
    sp.name             AS salesperson_name,
    c.radio_station,
    c.status
  FROM campaign AS c
  JOIN client      AS cl ON c.C_ID          = cl.C_ID
  JOIN salesperson AS sp ON c.Salesperson_ID = sp.Salesperson_ID
  ORDER BY c.CA_ID DESC
";
$campaigns = $conn->query($sql);
if ($campaigns === false) {
    die("SQL Error: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sales Admin Portal</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body { font-family:Arial,sans-serif; background:#f7f7f7; padding:20px; }
    table { width:100%; border-collapse:collapse; margin-top:20px; }
    th,td { border:1px solid #ddd; padding:8px; text-align:left; }
    th { background:#2c3e50; color:#fff; }
    tr:nth-child(even){ background:#f2f2f2; }
    .actions button { margin-right:5px; padding:4px 8px; border:none; border-radius:4px; color:#fff; cursor:pointer; }
    .approve { background:#27ae60; }
    .edit    { background:#f39c12; }
    .reject  { background:#c0392b; }
    .details { display:none; background:#ecf0f1; }
    form.logout { float:right; }
    form.logout button { background:#e74c3c; color:#fff; border:none; padding:6px 12px; border-radius:4px; cursor:pointer; }
    input, select, textarea { width:100%; box-sizing:border-box; margin:4px 0; padding:6px; }
    .save-btn { background:#2980b9; color:#fff; padding:6px 12px; border:none; border-radius:4px; cursor:pointer; }
    .cancel-btn { background:#7f8c8d; color:#fff; padding:6px 12px; border:none; border-radius:4px; cursor:pointer; }
  </style>
</head>
<body>

  <h1>Sales Admin Portal</h1>
  <form class="logout" method="POST">
    <button name="logout">Logout</button>
  </form>

  <h2>All Campaigns (<?= $campaigns->num_rows ?>)</h2>
  <table>
    <thead>
      <tr>
        <th>Client</th>
        <th>Sales Rep</th>
        <th>Station</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php while ($c = $campaigns->fetch_assoc()):
      $ca = (int)$c['CA_ID'];

      // Safe fetch Live Announcement
      $la = ['La_Name'=>'','la_request_time'=>'','start_date'=>'','end_date'=>''];
      if ($r = $conn->query(
        "SELECT La_Name, la_request_time, start_date, end_date
           FROM liveannouncement
          WHERE CA_ID = $ca"
      )) {
        if ($r->num_rows) {
          $la = $r->fetch_assoc();
        }
      }

      // Safe fetch Interview
      $in = ['IN_NAME'=>'','IN_TIME'=>'','IN_DATE'=>'','IN_INFO'=>''];
      if ($r = $conn->query(
        "SELECT IN_NAME, IN_TIME, IN_DATE, IN_INFO
           FROM interview
          WHERE CA_ID = $ca"
      )) {
        if ($r->num_rows) {
          $in = $r->fetch_assoc();
        }
      }

      // Safe fetch Giveaway
      $gw = ['G_NAME'=>'','G_TIME'=>'','G_START_DATE'=>'','G_END_DATE'=>''];
      if ($r = $conn->query(
        "SELECT G_NAME, G_TIME, G_START_DATE, G_END_DATE
           FROM giveaway
          WHERE CA_ID = $ca"
      )) {
        if ($r->num_rows) {
          $gw = $r->fetch_assoc();
        }
      }
    ?>
      <!-- Main row -->
      <tr id="row-<?= $ca ?>">
        <td><?= htmlspecialchars($c['client_name']) ?> (<?= htmlspecialchars($c['client_business']) ?>)</td>
        <td><?= htmlspecialchars($c['salesperson_name']) ?></td>
        <td><?= htmlspecialchars($c['radio_station']) ?></td>
        <td id="status-<?= $ca ?>"><?= htmlspecialchars($c['status']) ?></td>
        <td class="actions">
          <button class="approve" data-id="<?= $ca ?>" data-action="approved">Approve</button>
          <button class="edit"    data-id="<?= $ca ?>">Edit</button>
          <button class="reject"  data-id="<?= $ca ?>" data-action="rejected">Reject</button>
        </td>
      </tr>
      <!-- Inline edit form row -->
      <tr class="details" id="details-<?= $ca ?>">
        <td colspan="5">
          <form data-id="<?= $ca ?>" class="edit-form">
            <h4>Campaign Settings</h4>
            <label>Radio Station</label>
            <select name="radio_station">
              <option value="FunAsia"      <?= $c['radio_station']=='FunAsia'?'selected':'' ?>>FunAsia</option>
              <option value="Radio Sangam" <?= $c['radio_station']=='Radio Sangam'?'selected':'' ?>>Radio Sangam</option>
            </select>

            <h4>Live Announcement</h4>
            <label>Name</label>
            <input name="la_name"             value="<?= htmlspecialchars($la['La_Name']) ?>">
            <label>Request Time</label>
            <input type="time" name="la_request_time" value="<?= htmlspecialchars($la['la_request_time']) ?>">
            <label>Start Date</label>
            <input type="date" name="start_date"       value="<?= htmlspecialchars($la['start_date']) ?>">
            <label>End Date</label>
            <input type="date" name="end_date"         value="<?= htmlspecialchars($la['end_date']) ?>">

            <h4>Interview</h4>
            <label>Name</label>
            <input name="in_name"         value="<?= htmlspecialchars($in['IN_NAME']) ?>">
            <label>Time</label>
            <input type="time" name="in_start_time" value="<?= htmlspecialchars($in['IN_TIME']) ?>">
            <label>Date</label>
            <input type="date" name="in_date"       value="<?= htmlspecialchars($in['IN_DATE']) ?>">
            <label>Info</label>
            <textarea name="in_info"><?= htmlspecialchars($in['IN_INFO']) ?></textarea>

            <h4>Giveaway</h4>
            <label>Name</label>
            <input name="g_name"         value="<?= htmlspecialchars($gw['G_NAME']) ?>">
            <label>Time</label>
            <input type="time" name="g_time"    value="<?= htmlspecialchars($gw['G_TIME']) ?>">
            <label>Start Date</label>
            <input type="date" name="g_start_date" value="<?= htmlspecialchars($gw['G_START_DATE']) ?>">
            <label>End Date</label>
            <input type="date" name="g_end_date"   value="<?= htmlspecialchars($gw['G_END_DATE']) ?>">

            <button type="button" class="save-btn">Save</button>
            <button type="button" class="cancel-btn">Cancel</button>
          </form>
        </td>
      </tr>
    <?php endwhile; ?>
    </tbody>
  </table>

  <script>
    // Approve / Reject
    document.querySelectorAll('.approve, .reject').forEach(btn => {
      btn.onclick = () => {
        const id = btn.dataset.id, action = btn.dataset.action;
        fetch('update_campaign_status.php', {
          method: 'POST',
          headers: {'Content-Type':'application/json'},
          body: JSON.stringify({ca_id: id, action})
        })
        .then(r => r.json())
        .then(j => {
          if (j.success) {
            document.getElementById(`status-${id}`).textContent = action;
            alert('Status updated to ' + action + '!');
          } else {
            alert('Error: ' + (j.error || 'Unknown'));
          }
        });
      };
    });

    // Show inline edit
    document.querySelectorAll('.edit').forEach(btn => {
      btn.onclick = () => {
        document.getElementById(`details-${btn.dataset.id}`).style.display = 'table-row';
      };
    });

    // Cancel inline edit
    document.querySelectorAll('.cancel-btn').forEach(btn => {
      btn.onclick = () => {
        btn.closest('tr.details').style.display = 'none';
      };
    });

    // Save inline edit via AJAX
    document.querySelectorAll('.save-btn').forEach(btn => {
      btn.onclick = () => {
        const form = btn.closest('form'),
              id   = form.dataset.id,
              data = { ca_id: id, action: 'edited' };
        new FormData(form).forEach((v,k) => data[k] = v);

        fetch('update_campaign_details.php', {
          method: 'POST',
          headers: {'Content-Type':'application/json'},
          body: JSON.stringify(data)
        })
        .then(r => r.json())
        .then(j => {
          if (j.success) {
            document.getElementById(`status-${id}`).textContent = 'edited';
            document.getElementById(`details-${id}`).style.display = 'none';
            alert('Record saved!');
          } else {
            alert('Save error: ' + (j.error || 'Unknown'));
          }
        });
      };
    });
  </script>
</body>
</html>
