<?php 
include_once("header.php"); 
include_once("utilities.php"); 

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_GET['auctionID'])) {
    echo "No auction ID provided!";
    include_once("footer.php");
    exit();
}

$auctionID = $_GET['auctionID'];

// Check if the connection is successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Prepare a query to fetch auction details
$sql = "SELECT a.ItemName, a.Description, a.StartPrice, a.ReservePrice, a.EndDate, 
               u.Name AS SellerName, c.CategoryName 
        FROM Auction a
        JOIN Users u ON a.UserID = u.UserID
        JOIN Category c ON a.CategoryID = c.CategoryID
        WHERE a.AuctionID = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Failed to prepare statement: " . $conn->error);
}

$stmt->bind_param("i", $auctionID);
$stmt->execute();
$result = $stmt->get_result();

// Check if the auction exists
if ($result->num_rows === 0) {
    echo "<div class='container mt-5'><p>Auction not found.</p></div>";
} else {
    $auction = $result->fetch_assoc();

    // Fetch the highest bid for this auction
    $bidQuery = "SELECT MAX(BidAmount) AS HighestBid FROM Bid WHERE AuctionID = ?";
    $bidStmt = $conn->prepare($bidQuery);
    $bidStmt->bind_param("i", $auctionID);
    $bidStmt->execute();
    $bidResult = $bidStmt->get_result();
    $bidData = $bidResult->fetch_assoc();

    // Determine the current price to display (highest bid or starting price)
    $currentPrice = $bidData['HighestBid'] ?? $auction['StartPrice'];

    // Display auction details
    echo "<div class='container mt-5'>";
    echo "<h2>" . htmlspecialchars($auction['ItemName']) . "</h2>";
    echo "<p><strong>Category:</strong> " . htmlspecialchars($auction['CategoryName']) . "</p>";
    echo "<p><strong>Seller:</strong> " . htmlspecialchars($auction['SellerName']) . "</p>";
    echo "<p><strong>Description:</strong> " . nl2br(htmlspecialchars($auction['Description'])) . "</p>";
    echo "<p><strong>Starting Price:</strong> £" . number_format($auction['StartPrice'], 2) . "</p>";
    echo "<p><strong>Highest bid:</strong> £" . number_format($currentPrice, 2) . "</p>";
    echo "<p><strong>Reserve Price:</strong> £" . number_format($auction['ReservePrice'], 2) . "</p>";
    echo "<p><strong>End Date:</strong> " . htmlspecialchars($auction['EndDate']) . "</p>";
    
    // Place a bid button/form
    echo "<form method='post' action='place_bid.php'>
            <div class='form-group'>
                <label for='bid_amount'>Your Bid (£):</label>
                <input type='number' class='form-control' id='bid_amount' name='bid_amount' step='0.01' min='" . ($currentPrice + 1) . "' required>
            </div>
            <input type='hidden' name='auctionID' value='$auctionID'>
            <button type='submit' class='btn btn-primary'>Place Bid</button>
          </form>";
    echo "</div>";
}

// Close statement and connection
$stmt->close();
$bidStmt->close();
closeConnection($conn);

include_once("footer.php");
?>