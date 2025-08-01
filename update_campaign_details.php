<?php
// update_campaign_details.php
header('Content-Type: application/json');
require 'database.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['ca_id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid payload']);
    exit;
}
$ca = (int)$input['ca_id'];

// 1) Update radio_station & status
$stmt = $conn->prepare("
  UPDATE campaign 
     SET radio_station = ?, status = 'edited'
   WHERE CA_ID = ?
");
$stmt->bind_param("si", $input['radio_station'], $ca);
$stmt->execute();
$stmt->close();

// 2) Live Announcement
$stmt = $conn->prepare("
  UPDATE liveannouncement
     SET La_Name = ?, la_request_time = ?, start_date = ?, end_date = ?
   WHERE CA_ID = ?
");
$stmt->bind_param(
  "ssssi",
  $input['la_name'],
  $input['la_request_time'],
  $input['start_date'],
  $input['end_date'],
  $ca
);
$stmt->execute();
$stmt->close();

// 3) Interview (including IN_INFO)
$stmt = $conn->prepare("
  UPDATE interview
     SET IN_NAME = ?, IN_TIME = ?, IN_DATE = ?, IN_INFO = ?
   WHERE CA_ID = ?
");
$stmt->bind_param(
  "ssssi",
  $input['in_name'],
  $input['in_start_time'],
  $input['in_date'],
  $input['in_info'],
  $ca
);
$stmt->execute();
$stmt->close();

// 4) Giveaway
$stmt = $conn->prepare("
  UPDATE giveaway
     SET G_NAME = ?, G_TIME = ?, G_START_DATE = ?, G_END_DATE = ?
   WHERE CA_ID = ?
");
$stmt->bind_param(
  "ssssi",
  $input['g_name'],
  $input['g_time'],
  $input['g_start_date'],
  $input['g_end_date'],
  $ca
);
$stmt->execute();
$stmt->close();

echo json_encode(['success' => true]);
