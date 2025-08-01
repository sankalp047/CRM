<?php
session_start();
require 'database.php'; // Ensure your DB connection is available

// Check if the production admin is logged in; if not, redirect
if (!isset($_SESSION['production_admin_logged_in'])) {
    header("Location: production_admin.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Ensure required fields are present
    if (isset($_POST['client_id'], $_POST['log_date'], $_POST['start_time'], $_POST['end_time'], $_POST['deliverable_type'])) {
        $client_id         = $_POST['client_id'];
        $log_date          = $_POST['log_date'];
        $start_time        = $_POST['start_time'];
        $end_time          = $_POST['end_time'];
        $deliverable_type  = $_POST['deliverable_type'];
        $deliverable_detail = isset($_POST['deliverable_detail']) ? $_POST['deliverable_detail'] : "";

        // Handle station checkboxes
        $stations = isset($_POST['station']) ? $_POST['station'] : []; // Get selected stations
        $station_value = implode(', ', $stations); // Convert array to comma-separated string

        // Retrieve the client's business name from the client table using client_id
        $stmt = $conn->prepare("SELECT business FROM client WHERE C_ID = ?");
        if ($stmt) {
            $stmt->bind_param("i", $client_id);
            $stmt->execute();
            $stmt->bind_result($business);
            if ($stmt->fetch()) {
                $stmt->close();
                
                // Insert into radio_log table
                // Note: We assume CA_ID is optional and not provided by the form.
                $insert_stmt = $conn->prepare("INSERT INTO radio_log (client_name, log_date, start_time, end_time, deliverable_type, deliverable_detail, station, C_ID) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                if ($insert_stmt) {
                    $insert_stmt->bind_param("sssssssi", $business, $log_date, $start_time, $end_time, $deliverable_type, $deliverable_detail, $station_value, $client_id);
                    if ($insert_stmt->execute()) {
                        $insert_stmt->close();
                        // Redirect back with a success message
                        header("Location: production_admin_portal.php?msg=log_saved");
                        exit();
                    } else {
                        echo "Error inserting log: " . $insert_stmt->error;
                    }
                } else {
                    echo "Prepare failed: " . $conn->error;
                }
            } else {
                echo "Client not found.";
            }
        } else {
            echo "Prepare failed: " . $conn->error;
        }
    } else {
        echo "Missing required fields.";
    }
} else {
    header("Location: production_admin_portal.php");
    exit();
}
?>