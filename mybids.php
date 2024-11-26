<?php
include_once("header.php"); // Include the header for the page layout
include_once("utilities.php"); // Load utility functions for use

if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Start the session if it hasn't already started
}

// Check if the user is logged in and if their role is "buyer"
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['Role'] !== 'buyer') {
    // If not logged in or not a buyer, display an error message and exit
    echo "<div class='container mt-5'><p>Access denied. Only buyers can view their bids.</p></div>";
    include_once("footer.php"); // Keep the footer for consistent design
    exit();
}

$userID = $_SESSION['UserID']; // Get the user's ID from the session

// Query to fetch all auctions where the user has placed a bid
$sql = "SELECT DISTINCT a.AuctionID, a.ItemName, a.Description, a.EndDate, a.StartPrice,
               c.CategoryName, u.UserName AS SellerName,
               (SELECT MAX(b2.BidAmount) FROM Bid b2 WHERE b2.AuctionID = a.AuctionID) AS HighestBid,
               (SELECT MAX(b3.BidAmount) FROM Bid b3 WHERE b3.AuctionID = a.AuctionID AND b3.UserID = ?) AS YourHighestBid
        FROM Bid b
        JOIN Auction a ON b.AuctionID = a.AuctionID
        JOIN Users u ON a.UserID = u.UserID
        JOIN Category c ON a.CategoryID = c.CategoryID
        WHERE b.UserID = ?
        ORDER BY a.EndDate ASC";
$stmt = $conn->prepare($sql); // Prepare the SQL query
$stmt->bind_param("ii", $userID, $userID); // Bind the user ID to the query
$stmt->execute(); // Execute the query
$result = $stmt->get_result(); // Get the query results

// Start displaying the container for the bids
echo "<div class='container mt-5'>";
echo "<h2>Your Bids</h2>";

if ($result->num_rows > 0) {
    echo "<ul class='list-group'>"; // Create a list to display each bid
    while ($auction = $result->fetch_assoc()) {
        // Calculate how much time is left for each auction
        $now = new DateTime(); // Get the current time
        $endDate = new DateTime($auction['EndDate']); // Parse the auction's end date
        $timeRemaining = $now < $endDate ? $endDate->diff($now) : null; // Check if the auction has ended
        $timeRemainingStr = $timeRemaining ? display_time_remaining($timeRemaining) . ' remaining' : 'Auction ended'; // Show remaining time or "ended"

        // Check if the user is currently the highest bidder
        $yourHighestBid = $auction['YourHighestBid']; // Get the user's highest bid
        $highestBid = $auction['HighestBid']; // Get the current highest bid overall
        $isHighestBidder = ($yourHighestBid == $highestBid); // Compare the two to see if the user is winning

        // Start showing the auction details
        echo "<li class='list-group-item'>";
        echo "<h5><a href='auction_details.php?auctionID=" . $auction['AuctionID'] . "'>" . htmlspecialchars($auction['ItemName']) . "</a></h5>"; // Show auction name as a link
        echo "<p>" . htmlspecialchars($auction['Description']) . "</p>"; // Description of the item
        echo "<p><strong>Seller:</strong> " . htmlspecialchars($auction['SellerName']) . "</p>"; // Who's selling
        echo "<p><strong>Category:</strong> " . htmlspecialchars($auction['CategoryName']) . "</p>"; // Category of the auction
        echo "<p><strong>End Date:</strong> " . htmlspecialchars(date("j M Y, H:i", strtotime($auction['EndDate']))) . "</p>"; // Show the end date
        echo "<p><strong>Time Remaining:</strong> " . $timeRemainingStr . "</p>"; // Time left to bid
        echo "<p><strong>Your Highest Bid:</strong> £" . number_format($yourHighestBid, 2) . "</p>"; // User's max bid
        echo "<p><strong>Current Highest Bid:</strong> £" . number_format($highestBid, 2) . "</p>"; // Current leading bid

        // Status messages based on the user's bid
        if ($isHighestBidder && $timeRemaining) {
            echo "<p class='text-success'><strong>You are currently the highest bidder!</strong></p>"; // Winning, auction still live
        } elseif (!$timeRemaining && $isHighestBidder) {
            echo "<p class='text-success'><strong>You have won this auction!</strong></p>"; // Winning, auction ended
        } elseif (!$timeRemaining && !$isHighestBidder) {
            echo "<p class='text-danger'><strong>You did not win this auction.</strong></p>"; // Lost, auction ended
        } else {
            echo "<p class='text-warning'><strong>You have been outbid.</strong></p>"; // Lost, still live
        }

        echo "</li>"; // Close list item
    }
    echo "</ul>"; // Close the list
} else {
    echo "<p>You have not placed any bids yet.</p>"; // No bids placed
}

echo "</div>"; // Close the container

$stmt->close(); // Clean up the statement
closeConnection($conn); // Close the database connection

include_once("footer.php"); // Include the footer for consistency
?>
