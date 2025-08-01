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
    $client_id    = intval($_POST['client_id']);
    $g_name       = trim($_POST['g_name']);
    $g_time       = trim($_POST['g_time']);
    $g_info       = trim($_POST['g_info']);
    $g_start_date = trim($_POST['g_start_date']); // New start date field
    $g_end_date   = trim($_POST['g_end_date']);   // New end date field

    // Validate required fields
    if (empty($client_id) || empty($g_name) || empty($g_time) || empty($g_start_date) || empty($g_end_date)) {
        die("Error: All required fields must be filled.");
    }

    // Append seconds to the time if missing (to match the TIME format HH:MM:SS)
    if (strlen($g_time) == 5) {
        $g_time .= ":00";
    }

    // ********** Campaign Check/Insert Block for Giveaway **********
    // Define a campaign name that identifies a giveaway campaign for this client
    $campaign_name = "Giveaway Campaign for Client $client_id";

    // Check if a campaign for this client (with this giveaway type) already exists
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

    // ********** Insert Giveaway Record **********
    $stmt = $conn->prepare("INSERT INTO giveaways (G_NAME, G_TIME, G_START_DATE, G_END_DATE, G_INFO, CA_ID) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("sssssi", $g_name, $g_time, $g_start_date, $g_end_date, $g_info, $ca_id);
        if ($stmt->execute()) {
            // If successful, redirect to sales_rep_portal.php with an optional success message
            header("Location: sales_rep_portal.php?msg=GiveawaySavedSuccessfully");
            exit();
        } 
        else {
            echo "Error: " . $stmt->error;
        }
    } else {
        echo "SQL Error: " . $conn->error;
    }
}
?>
