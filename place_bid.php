<?php
include_once("utilities.php");
include_once("notification_functions.php");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is a buyer
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['Role'] !== 'buyer') {
    echo "Access denied. Only buyers can place bids.";
    exit();
}

// Get form data
$auctionID = $_POST['auctionID'];
$bidAmount = $_POST['bid_amount'];
$userID = $_SESSION['UserID'];

// Validate bid amount
if (!is_numeric($bidAmount) || $bidAmount <= 0) {
    echo "Invalid bid amount.";
    exit();
}

// Fetch the current auction details
$stmt = $conn->prepare("SELECT StartPrice, EndDate FROM Auction WHERE AuctionID = ?");
$stmt->bind_param("i", $auctionID);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo "Auction not found.";
    exit();
}

$auction = $result->fetch_assoc();
$startPrice = $auction['StartPrice'];
$endDate = new DateTime($auction['EndDate']);
$now = new DateTime();

if ($now > $endDate) {
    echo "The auction has already ended.";
    exit();
}

// Fetch the highest bid for this auction
$stmt = $conn->prepare("SELECT MAX(BidAmount) AS HighestBid FROM Bid WHERE AuctionID = ?");
$stmt->bind_param("i", $auctionID);
$stmt->execute();
$result = $stmt->get_result();
$highestBidRow = $result->fetch_assoc();
$highestBid = $highestBidRow['HighestBid'] ?? $startPrice;

// Ensure bid amount is higher than the current highest bid
if ($bidAmount <= $highestBid) {
    echo "Your bid must be higher than the current highest bid of £" . number_format($highestBid, 2);
    exit();
}

// Insert the new bid into the Bid table with the current timestamp
$stmt = $conn->prepare("INSERT INTO Bid (AuctionID, UserID, BidAmount, BidTime) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("iid", $auctionID, $userID, $bidAmount);

if ($stmt->execute()) {
    // Get the previous highest bidder
    $stmt = $conn->prepare("SELECT UserID FROM Bid 
                           WHERE AuctionID = ? AND UserID != ? 
                           ORDER BY BidAmount DESC LIMIT 1");
    $stmt->bind_param("ii", $auctionID, $userID);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($previousBidder = $result->fetch_assoc()) {
        createNotification($conn, $previousBidder['UserID'], $auctionID, 
            "You have been outbid! New highest bid is £" . number_format($bidAmount, 2), 'outbid');
    }
    echo "Bid placed successfully! <a href='auction_details.php?auctionID=$auctionID'>Go back to auction</a>";
} else {
    echo "Error: " . $stmt->error;
}

// Close statement and connection
$stmt->close();
closeConnection($conn);
?>