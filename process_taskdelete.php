<?php
session_start();
require 'database.php';

// Check if the user is logged in
if (!isset($_SESSION['rj_logged_in'])) {
    header("Location: radio_jockey.php");
    exit();
}

// Ensure task_id is provided via POST
if (!isset($_POST['task_id'])) {
    header("Location: radio_jockey_portal.php");
    exit();
}

$task_id = filter_var($_POST['task_id'], FILTER_SANITIZE_NUMBER_INT);

// Prepare deletion statement
$stmt = $conn->prepare("DELETE FROM taskupdate WHERE Task_ID = ?");
if(!$stmt){
    die("Error preparing deletion: " . $conn->error);
}
$stmt->bind_param("i", $task_id);
if(!$stmt->execute()){
    die("Error executing deletion: " . $stmt->error);
}
$stmt->close();

// Redirect back to the portal after deletion
header("Location: radio_jockey_portal.php");
exit();
?>
