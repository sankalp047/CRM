<?php
session_start();
require 'database.php';

// Check if the user is logged in
if (!isset($_SESSION['rj_logged_in'])) {
    header("Location: radio_jockey.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Check required fields (log_id, rj_id, process, comments, and log_all_request)
    if (isset($_POST['log_id'], $_POST['rj_id'], $_POST['process'], $_POST['comments'], $_POST['log_all_request'])) {
        $log_id = $_POST['log_id'];
        $rj_id = $_POST['rj_id'];
        $comments = trim($_POST['comments']);
        $log_all_request = trim($_POST['log_all_request']);
        $status = "Processed";
        
        // Insert into taskupdate table
        $stmt = $conn->prepare("INSERT INTO taskupdate (Log_ID, RJ_ID, status, comments) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("iiss", $log_id, $rj_id, $status, $comments);
            if (!$stmt->execute()) {
                die("Error inserting task update: " . $stmt->error);
            }
            $stmt->close();
        } else {
            die("Error preparing statement for taskupdate: " . $conn->error);
        }
        
        // Retrieve the client_name from radio_log for the given log_id
        $stmt2 = $conn->prepare("SELECT client_name FROM radio_log WHERE log_id = ?");
        if ($stmt2) {
            $stmt2->bind_param("i", $log_id);
            $stmt2->execute();
            $stmt2->bind_result($client_name);
            if (!$stmt2->fetch()) {
                $stmt2->close();
                die("Error: radio_log record not found for log_id: " . $log_id);
            }
            $stmt2->close();
        } else {
            die("Error preparing statement for radio_log lookup: " . $conn->error);
        }
        
        // Insert into log_all_requests table with the log_all_request text field.
        $stmt3 = $conn->prepare("INSERT INTO log_all_requests (client_name, log_all_request) VALUES (?, ?)");
        if ($stmt3) {
            $stmt3->bind_param("ss", $client_name, $log_all_request);
            if (!$stmt3->execute()) {
                die("Error inserting log_all_request: " . $stmt3->error);
            }
            $stmt3->close();
        } else {
            die("Error preparing statement for log_all_requests: " . $conn->error);
        }
        
        // Redirect back to the portal after successful update
        header("Location: radio_jockey_portal.php?msg=updated");
        exit();
    } else {
        die("Missing required fields.");
    }
} else {
    header("Location: radio_jockey_portal.php");
    exit();
}
?>
