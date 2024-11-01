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

echo "<div class='container'>";
echo "<h2 class='my-3'>My Listings</h2>";

// Basic SQL to get auctions for the logged-in seller
$sql = "SELECT AuctionID, ItemName, Description, StartPrice, EndDate FROM Auction WHERE UserID = '$userID' AND EndDate > NOW()";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    echo "<ul class='list-group'>";
    while ($row = $result->fetch_assoc()) {
        echo "<li class='list-group-item'>";
        echo "<h5>" . htmlspecialchars($row['ItemName']) . "</h5>";
        echo "<p>" . htmlspecialchars($row['Description']) . "</p>";
        echo "<p>Starting Price: £" . number_format($row['StartPrice'], 2) . "</p>";
        echo "<p>Ends: " . htmlspecialchars($row['EndDate']) . "</p>";
        echo "<a href='auction_details.php?auctionID=" . $row['AuctionID'] . "' class='btn btn-primary'>View Auction</a>";
        echo "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>You have no active auctions.</p>";
}

echo "</div>";

// Close the connection at the end
$conn->close();

include_once("footer.php");
?>