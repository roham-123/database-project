<?php
include_once("header.php");
require("utilities.php");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['UserID']) || $_SESSION['Role'] != 'seller') {
    echo "<div class='container'><p>You must be logged in as a seller to view this page.</p></div>";
    include_once("footer.php");
    exit();
}

$userID = $_SESSION['UserID'];

# Get filter option from the URL, defaulting to "active"
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'active';

echo "<div class='container'>";
echo "<h2 class='my-3'>My Listings</h2>";

//dropdown for filtering listings
echo "<div class='mb-3'>
        <label for='filter'>Show:</label>
        <select id='filter' class='form-control' style='width: auto; display: inline-block;'
                onchange='window.location.href=\"mylistings.php?filter=\" + this.value;'>
            <option value='active' " . ($filter === 'active' ? 'selected' : '') . ">Active Listings</option>
            <option value='ended' " . ($filter === 'ended' ? 'selected' : '') . ">Ended Listings</option>
        </select>
      </div>";

//modify SQL query based on filter selection
$sql = "
    SELECT a.AuctionID, a.ItemName, a.Description, a.StartPrice, a.EndDate,
           MAX(b.BidAmount) AS HighestBid, COUNT(b.BidID) AS NumBids
    FROM Auction a
    LEFT JOIN Bid b ON a.AuctionID = b.AuctionID
    WHERE a.UserID = ?
    " . ($filter === 'active' ? "AND a.EndDate > NOW()" : "AND a.EndDate <= NOW()") . "
    GROUP BY a.AuctionID
    ORDER BY a.EndDate DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    echo "<ul class='list-group'>";
    while ($row = $result->fetch_assoc()) {
        $isEnded = new DateTime() > new DateTime($row['EndDate']);
        $hasBids = $row['NumBids'] > 0;

        echo "<li class='list-group-item'>";
        echo "<h5>" . htmlspecialchars($row['ItemName']) . "</h5>";
        echo "<p>" . htmlspecialchars($row['Description']) . "</p>";
        echo "<p>Starting Price: £" . number_format($row['StartPrice'], 2) . "</p>";
        echo "<p>Ends: " . htmlspecialchars($row['EndDate']) . "</p>";
        
        //display status based on bids and end date
        if ($isEnded) {
            if ($hasBids) {
                echo "<p class='text-success'><strong>Sold</strong></p>";
            } else {
                echo "<p class='text-danger'><strong>Ended without sale</strong></p>";
            }
        }

        echo "<a href='auction_details.php?auctionID=" . $row['AuctionID'] . "' class='btn btn-primary'>View Auction</a>";
        echo "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>You have no " . ($filter === 'active' ? "active" : "ended") . " auctions.</p>";
}

echo "</div>";

// Close the statement and connection
$stmt->close();
$conn->close();

include_once("footer.php");
?>