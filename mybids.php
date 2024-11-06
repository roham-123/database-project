<?php
include_once("header.php");
include_once("utilities.php");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in and is a buyer
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['Role'] !== 'buyer') {
    echo "<div class='container mt-5'><p>Access denied. Only buyers can view their bids.</p></div>";
    include_once("footer.php");
    exit();
}

$userID = $_SESSION['UserID'];

// Fetch auctions where the user has placed bids
$sql = "SELECT DISTINCT a.AuctionID, a.ItemName, a.Description, a.EndDate, a.StartPrice,
               c.CategoryName, u.Name AS SellerName,
               (SELECT MAX(b2.BidAmount) FROM Bid b2 WHERE b2.AuctionID = a.AuctionID) AS HighestBid,
               (SELECT MAX(b3.BidAmount) FROM Bid b3 WHERE b3.AuctionID = a.AuctionID AND b3.UserID = ?) AS YourHighestBid
        FROM Bid b
        JOIN Auction a ON b.AuctionID = a.AuctionID
        JOIN Users u ON a.UserID = u.UserID
        JOIN Category c ON a.CategoryID = c.CategoryID
        WHERE b.UserID = ?
        ORDER BY a.EndDate ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $userID, $userID);
$stmt->execute();
$result = $stmt->get_result();

// Display the auctions
echo "<div class='container mt-5'>";
echo "<h2>Your Bids</h2>";

if ($result->num_rows > 0) {
    echo "<ul class='list-group'>";
    while ($auction = $result->fetch_assoc()) {
        // Calculate time remaining
        $now = new DateTime();
        $endDate = new DateTime($auction['EndDate']);
        $timeRemaining = $now < $endDate ? $endDate->diff($now) : null;
        $timeRemainingStr = $timeRemaining ? display_time_remaining($timeRemaining) . ' remaining' : 'Auction ended';

        // Determine if the user is the highest bidder
        $yourHighestBid = $auction['YourHighestBid'];
        $highestBid = $auction['HighestBid'];
        $isHighestBidder = ($yourHighestBid == $highestBid);

        // Display the auction
        echo "<li class='list-group-item'>";
        echo "<h5><a href='auction_details.php?auctionID=" . $auction['AuctionID'] . "'>" . htmlspecialchars($auction['ItemName']) . "</a></h5>";
        echo "<p>" . htmlspecialchars($auction['Description']) . "</p>";
        echo "<p><strong>Seller:</strong> " . htmlspecialchars($auction['SellerName']) . "</p>";
        echo "<p><strong>Category:</strong> " . htmlspecialchars($auction['CategoryName']) . "</p>";
        echo "<p><strong>End Date:</strong> " . htmlspecialchars(date("j M Y, H:i", strtotime($auction['EndDate']))) . "</p>";
        echo "<p><strong>Time Remaining:</strong> " . $timeRemainingStr . "</p>";
        echo "<p><strong>Your Highest Bid:</strong> £" . number_format($yourHighestBid, 2) . "</p>";
        echo "<p><strong>Current Highest Bid:</strong> £" . number_format($highestBid, 2) . "</p>";

        if ($isHighestBidder && $timeRemaining) {
            echo "<p class='text-success'><strong>You are currently the highest bidder!</strong></p>";
        } elseif (!$timeRemaining && $isHighestBidder) {
            echo "<p class='text-success'><strong>You have won this auction!</strong></p>";
        } elseif (!$timeRemaining && !$isHighestBidder) {
            echo "<p class='text-danger'><strong>You did not win this auction.</strong></p>";
        } else {
            echo "<p class='text-warning'><strong>You have been outbid.</strong></p>";
        }

        echo "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>You have not placed any bids yet.</p>";
}

echo "</div>";

$stmt->close();
closeConnection($conn);

include_once("footer.php");
?>