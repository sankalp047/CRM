<?php
session_start();
require 'database.php'; // Include your database connection file

if (!isset($_SESSION['sales_rep_id'])) {
    die("Salesperson ID not set. Please log in properly.");
}
$salesperson_id = $_SESSION['sales_rep_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve and sanitize input values
    $client_id   = intval($_POST['client_id']);
    $in_name     = trim($_POST['in_name']);
    $in_start_time = trim($_POST['in_start_time']);
    $in_end_time   = trim($_POST['in_end_time']);
    $in_info     = trim($_POST['in_info']);
    $in_date     = trim($_POST['in_date']);

    // Validate required fields (end time is optional)
    if (empty($client_id) || empty($in_name) || empty($in_start_time) || empty($in_date)) {
        die("Error: All required fields must be filled.");
    }

    // Append seconds if missing for start and end time
    if (strlen($in_start_time) == 5) {
        $in_start_time .= ":00";
    }
    if (!empty($in_end_time) && strlen($in_end_time) == 5) {
        $in_end_time .= ":00";
    }

    // Append end time to info if provided
    if (!empty($in_end_time)) {
        $in_info .= " (Ends at: $in_end_time)";
    }

    // Campaign block for Interview
    $campaign_name = "Interview Campaign for Client $client_id";
    $check_campaign_stmt = $conn->prepare("SELECT CA_ID FROM campaign WHERE C_ID = ? AND CA_NAME = ?");
    if ($check_campaign_stmt) {
        $check_campaign_stmt->bind_param("is", $client_id, $campaign_name);
        $check_campaign_stmt->execute();
        $campaign_result = $check_campaign_stmt->get_result();
        
        if ($campaign_result && $campaign_result->num_rows > 0) {
            // Use existing campaign's CA_ID
            $row = $campaign_result->fetch_assoc();
            $ca_id = $row['CA_ID'];
        } else {
            // No campaign exists; insert a new one
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

    // Insert the interview record using the obtained CA_ID
    $stmt = $conn->prepare("INSERT INTO interview (IN_NAME, IN_TIME, IN_DATE, IN_INFO, CA_ID) VALUES (?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("ssssi", $in_name, $in_start_time, $in_date, $in_info, $ca_id);
        if ($stmt->execute()) {
            // If successful, redirect to sales_rep_portal.php with an optional success message
            header("Location: sales_rep_portal.php?msg=InterviewSavedSuccessfully");
            exit();
        }  else {
            echo "Error: " . $stmt->error;
        }
    } else {
        echo "SQL Error: " . $conn->error;
    }
}
?>
