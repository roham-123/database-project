<?php
include_once("utilities.php");
include_once("notification_functions.php");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_POST['notification_id']) && isset($_SESSION['UserID'])) {
    markNotificationAsRead($conn, $_POST['notification_id'], $_SESSION['UserID']);
    echo "success";
}

closeConnection($conn);
?>
