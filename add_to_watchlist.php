<?php
include_once("utilities.php");
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in and is a buyer
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['Role'] !== 'buyer') {
    echo "<script>alert('Access denied. Only buyers can add auctions to their watchlist.'); window.history.back();</script>";
    exit();
}

$auctionID = $_POST['auctionID'];
$userID = $_SESSION['UserID'];

// Database connection
$conn = new mysqli("localhost", "root", "", "AuctionDB");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the auction already exists in the watchlist
$stmt = $conn->prepare("SELECT * FROM WatchList WHERE AuctionID = ? AND UserID = ?");
$stmt->bind_param("ii", $auctionID, $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Auction is already in the watchlist
    echo "<script>alert('This auction is already in your watchlist.'); window.location.href = 'auction_details.php?auctionID=$auctionID';</script>";
} else {
    // Add to watchlist
    $stmt = $conn->prepare("INSERT INTO WatchList (AuctionID, UserID) VALUES (?, ?)");
    $stmt->bind_param("ii", $auctionID, $userID);

    if ($stmt->execute()) {
        echo "<script>alert('Auction added to your watchlist!'); window.location.href = 'auction_details.php?auctionID=$auctionID';</script>";
    } else {
        echo "<script>alert('Error adding auction to watchlist: " . addslashes($stmt->error) . "'); window.history.back();</script>";
    }
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>