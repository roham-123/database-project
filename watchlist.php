<?php
include_once("header.php");
include_once("utilities.php");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in and is a buyer
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['Role'] !== 'buyer') {
    echo "<div class='container mt-5'><p>Access denied. Only buyers can view their watchlist.</p></div>";
    include_once("footer.php");
    exit();
}

$userID = $_SESSION['UserID'];

// Fetch auctions from the user's watchlist
$sql = "SELECT a.AuctionID, a.ItemName, a.Description, a.EndDate, 
               a.StartPrice, c.CategoryName, u.UserName AS SellerName
        FROM WatchList w
        JOIN Auction a ON w.AuctionID = a.AuctionID
        JOIN Users u ON a.UserID = u.UserID
        JOIN Category c ON a.CategoryID = c.CategoryID
        WHERE w.UserID = ?
        ORDER BY a.EndDate ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

// Display the auctions
echo "<div class='container mt-5'>";
echo "<h2>Your Watchlist</h2>";

if ($result->num_rows > 0) {
    echo "<ul class='list-group'>";
    while ($auction = $result->fetch_assoc()) {
        // Calculate time remaining
        $now = new DateTime();
        $endDate = new DateTime($auction['EndDate']);
        $timeRemaining = $now < $endDate ? $endDate->diff($now) : null;
        $timeRemainingStr = $timeRemaining ? display_time_remaining($timeRemaining) . ' remaining' : 'Auction ended';

        // Display the auction
        echo "<li class='list-group-item'>";
        echo "<h5><a href='auction_details.php?auctionID=" . $auction['AuctionID'] . "'>" . htmlspecialchars($auction['ItemName']) . "</a></h5>";
        echo "<p>" . htmlspecialchars($auction['Description']) . "</p>";
        echo "<p><strong>Seller:</strong> " . htmlspecialchars($auction['SellerName']) . "</p>";
        echo "<p><strong>Category:</strong> " . htmlspecialchars($auction['CategoryName']) . "</p>";
        echo "<p><strong>End Date:</strong> " . htmlspecialchars(date("j M Y, H:i", strtotime($auction['EndDate']))) . "</p>";
        echo "<p><strong>Time Remaining:</strong> " . $timeRemainingStr . "</p>";
        echo "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>Your watchlist is empty.</p>";
}

echo "</div>";

$stmt->close();
closeConnection($conn);

include_once("footer.php");
?>