<?php
include_once("header.php");
include_once("utilities.php");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_GET['auctionID'])) {
    echo "<div class='container mt-5'><p>No auction ID provided!</p></div>";
    include_once("footer.php");
    exit();
}

$auctionID = $_GET['auctionID'];

// Prepare a query to fetch auction details
$sql = "SELECT a.ItemName, a.Description, a.StartPrice, a.ReservePrice, a.EndDate, 
               u.Name AS SellerName, c.CategoryName 
        FROM Auction a
        JOIN Users u ON a.UserID = u.UserID
        JOIN Category c ON a.CategoryID = c.CategoryID
        WHERE a.AuctionID = ?";
$stmt = $conn->prepare($sql);
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
    $currentPrice = $bidData['HighestBid'] ?? $auction['StartPrice'];

    // Check if the auction has ended
    $now = new DateTime();
    $endDate = new DateTime($auction['EndDate']);
    $timeRemaining = $now < $endDate ? $endDate->diff($now) : null;

    // Display auction details
    echo "<div class='container mt-5'>";
    echo "<div class='row'>";
    echo "<div class='col-md-8'>";
    echo "<h2>" . htmlspecialchars($auction['ItemName']) . "</h2>";
    echo "<p><strong>Category:</strong> " . htmlspecialchars($auction['CategoryName']) . "</p>";
    echo "<p><strong>Seller:</strong> " . htmlspecialchars($auction['SellerName']) . "</p>";
    echo "<p><strong>Description:</strong><br>" . nl2br(htmlspecialchars($auction['Description'])) . "</p>";
    echo "</div>";
    echo "<div class='col-md-4'>";
    echo "<div class='card mb-3'>";
    echo "<div class='card-body'>";
    echo "<h4 class='card-title'>Auction Details</h4>";
    echo "<p><strong>Starting Price:</strong> £" . number_format($auction['StartPrice'], 2) . "</p>";
    echo "<p><strong>Current Price:</strong> £" . number_format($currentPrice, 2) . "</p>";
    if (!empty($auction['ReservePrice'])) {
        echo "<p><strong>Reserve Price:</strong> £" . number_format($auction['ReservePrice'], 2) . "</p>";
    }
    echo "<p><strong>End Date:</strong> " . htmlspecialchars(date("j M Y, H:i", strtotime($auction['EndDate']))) . "</p>";
    if ($timeRemaining) {
        echo "<p><strong>Time Remaining:</strong> " . display_time_remaining($timeRemaining) . "</p>";
    } else {
        echo "<p class='text-danger'><strong>This auction has ended.</strong></p>";
    }

    // Add to Watchlist button
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && $_SESSION['Role'] === 'buyer') {
        // Check if the auction is already in the user's watchlist
        $userID = $_SESSION['UserID'];
        $watchlistQuery = "SELECT * FROM WatchList WHERE AuctionID = ? AND UserID = ?";
        $watchlistStmt = $conn->prepare($watchlistQuery);
        $watchlistStmt->bind_param("ii", $auctionID, $userID);
        $watchlistStmt->execute();
        $watchlistResult = $watchlistStmt->get_result();

        if ($watchlistResult->num_rows > 0) {
            // Auction is in watchlist
            echo "<form method='post' action='remove_from_watchlist.php'>
                    <input type='hidden' name='auctionID' value='$auctionID'>
                    <button type='submit' class='btn btn-secondary btn-block'>Unwatch</button>
                  </form>";
        } else {
            // Auction is not in watchlist
            echo "<form method='post' action='add_to_watchlist.php'>
                    <input type='hidden' name='auctionID' value='$auctionID'>
                    <button type='submit' class='btn btn-primary btn-block'>Add to Watchlist</button>
                  </form>";
        }
        $watchlistStmt->close();
    } else {
        echo "<p><a href='login.php'>Log in as a buyer</a> to add this auction to your watchlist.</p>";
    }

    echo "</div>"; // Close card-body
    echo "</div>"; // Close card

    // Place a bid form
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && $_SESSION['Role'] === 'buyer') {
        if ($timeRemaining) {
            echo "<div class='card'>";
            echo "<div class='card-body'>";
            echo "<h4 class='card-title'>Place a Bid</h4>";
            echo "<form method='post' action='place_bid.php'>
                    <div class='form-group'>
                        <label for='bid_amount'>Your Bid (£):</label>
                        <input type='number' class='form-control' id='bid_amount' name='bid_amount' step='0.01' min='" . ($currentPrice + 0.01) . "' required>
                    </div>
                    <input type='hidden' name='auctionID' value='$auctionID'>
                    <button type='submit' class='btn btn-success btn-block'>Place Bid</button>
                  </form>";
            echo "</div>"; // Close card-body
            echo "</div>"; // Close card
        } else {
            echo "<p class='text-danger'>You cannot place a bid because this auction has ended.</p>";
        }
    }

    echo "</div>"; // Close col-md-4
    echo "</div>"; // Close row

    // Fetch and display bid history
    echo "<div class='bid-history mt-5'>";
    echo "<h3>Bid History</h3>";
    $historyQuery = "SELECT b.BidAmount, b.BidTime, u.Name AS BidderName 
                     FROM Bid b 
                     JOIN Users u ON b.UserID = u.UserID 
                     WHERE b.AuctionID = ? 
                     ORDER BY b.BidTime DESC";
    $historyStmt = $conn->prepare($historyQuery);
    $historyStmt->bind_param("i", $auctionID);
    $historyStmt->execute();
    $historyResult = $historyStmt->get_result();

    if ($historyResult->num_rows > 0) {
        echo "<table class='table'>";
        echo "<thead><tr><th>Bidder</th><th>Amount (£)</th><th>Time</th></tr></thead>";
        echo "<tbody>";
        while ($bidRow = $historyResult->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($bidRow['BidderName']) . "</td>";
            echo "<td>£" . number_format($bidRow['BidAmount'], 2) . "</td>";
            echo "<td>" . date("j M Y, H:i", strtotime($bidRow['BidTime'])) . "</td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
    } else {
        echo "<p>No bids yet.</p>";
    }
    echo "</div>"; // Close bid-history div

    // Close the bid history statement
    $historyStmt->close();
}

$stmt->close();
$bidStmt->close();
closeConnection($conn);

echo "</div>"; // Close container

include_once("footer.php");
?>