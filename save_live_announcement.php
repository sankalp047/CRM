<?php
session_start();
require 'database.php'; // Include your database connection file

// Ensure the salesperson's ID is set in the session (set during login)
if (!isset($_SESSION['sales_rep_id'])) {
    die("Salesperson ID not set. Please log in properly.");
}
$salesperson_id = $_SESSION['sales_rep_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve and sanitize form data
    $client_id = intval($_POST['client_id']);
    $la_name = trim($_POST['la_name']);
    $la_request_time = trim($_POST['la_request_time']);
    $la_info = trim($_POST['la_info']);
    $start_date = trim($_POST['start_date']);
    $end_date = trim($_POST['end_date']);

    // Validate required fields
    if (empty($client_id) || empty($la_name) || empty($la_request_time) || empty($start_date) || empty($end_date)) {
        die("Error: All required fields must be filled.");
    }

    // ********** Campaign Check/Insert Block **********
    // Define the campaign name that identifies a live announcement campaign for this client
    $campaign_name = "Live Announcement Campaign for Client $client_id";

    // Check if a campaign for this client already exists
    $check_campaign_stmt = $conn->prepare("SELECT CA_ID FROM campaign WHERE C_ID = ? AND CA_NAME = ?");
    if ($check_campaign_stmt) {
        $check_campaign_stmt->bind_param("is", $client_id, $campaign_name);
        $check_campaign_stmt->execute();
        $campaign_result = $check_campaign_stmt->get_result();
        
        if ($campaign_result && $campaign_result->num_rows > 0) {
            // Use the existing campaign's CA_ID
            $row = $campaign_result->fetch_assoc();
            $ca_id = $row['CA_ID'];
        } else {
            // No existing campaign found; insert a new one
            $campaign_stmt = $conn->prepare("INSERT INTO campaign (C_ID, CA_NAME, Salesperson_ID) VALUES (?, ?, ?)");
            if ($campaign_stmt) {
                $campaign_stmt->bind_param("isi", $client_id, $campaign_name, $salesperson_id);
                $campaign_stmt->execute();
                $ca_id = $campaign_stmt->insert_id;
            } else {
                die("Error inserting campaign: " . $conn->error);
            }
        }
    } else {
        die("Error checking campaign: " . $conn->error);
    }
    // ********** End Campaign Block **********

    // ********** Insert Live Announcement **********
    $stmt = $conn->prepare("INSERT INTO liveannouncement (la_name, la_request_time, la_info, start_date, end_date, CA_ID) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("sssssi", $la_name, $la_request_time, $la_info, $start_date, $end_date, $ca_id);
        if ($stmt->execute()) {
            // If successful, redirect to sales_rep_portal.php with an optional success message
            header("Location: sales_rep_portal.php?msg=LiveAnnouncementSaved");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
    } else {
        echo "SQL Error: " . $conn->error;
    }
}
?>
