<?php
include_once("utilities.php");
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is a buyer
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['Role'] !== 'buyer') {
    echo "Access denied. Only buyers can remove auctions from their watchlist.";
    exit();
}

$auctionID = $_POST['auctionID'];
$userID = $_SESSION['UserID'];

// Remove from watchlist
$stmt = $conn->prepare("DELETE FROM WatchList WHERE AuctionID = ? AND UserID = ?");
$stmt->bind_param("ii", $auctionID, $userID);

if ($stmt->execute()) {
    echo "Auction removed from your watchlist! <a href='auction_details.php?auctionID=$auctionID'>Go back to auction</a>";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
closeConnection($conn);
?>