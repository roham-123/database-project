<?php
include_once("utilities.php");
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is a buyer
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['Role'] !== 'buyer') {
    echo "Access denied. Only buyers can add auctions to their watchlist.";
    exit();
}

$auctionID = $_POST['auctionID'];
$userID = $_SESSION['UserID'];

// Check if the auction already exists in the watchlist
$stmt = $conn->prepare("SELECT * FROM WatchList WHERE AuctionID = ? AND UserID = ?");
$stmt->bind_param("ii", $auctionID, $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "This auction is already in your watchlist. <a href='auction_details.php?auctionID=$auctionID'>Go back to auction</a>";
} else {
    // Add to watchlist
    $stmt = $conn->prepare("INSERT INTO WatchList (AuctionID, UserID) VALUES (?, ?)");
    $stmt->bind_param("ii", $auctionID, $userID);
    
    if ($stmt->execute()) {
        echo "Auction added to your watchlist! <a href='auction_details.php?auctionID=$auctionID'>Go back to auction</a>";
    } else {
        echo "Error: " . $stmt->error;
    }
}

$stmt->close();
closeConnection($conn);
?>