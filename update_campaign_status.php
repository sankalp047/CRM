<?php
// update_campaign_status.php
require 'database.php';
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['ca_id'], $data['action'])) {
    echo json_encode(['success'=>false,'error'=>'Invalid input']);
    exit;
}
$allowed = ['approved','edited','rejected'];
if (!in_array($data['action'], $allowed)) {
    echo json_encode(['success'=>false,'error'=>'Bad action']);
    exit;
}
$stmt = $conn->prepare("UPDATE campaign SET status=? WHERE CA_ID=?");
$stmt->bind_param("si", $data['action'], $data['ca_id']);
if ($stmt->execute()) {
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false,'error'=>$stmt->error]);
}
