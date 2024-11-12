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

// Track views if the user is logged in
if (isset($_SESSION['UserID'])) {
    $userID = $_SESSION['UserID'];
    
    $checkViewQuery = "SELECT * FROM UserViews WHERE UserID = ? AND AuctionID = ?";
    $checkStmt = $conn->prepare($checkViewQuery);
    $checkStmt->bind_param("ii", $userID, $auctionID);
    $checkStmt->execute();
    $viewResult = $checkStmt->get_result();

    if ($viewResult->num_rows === 0) {
        $insertViewQuery = "INSERT INTO UserViews (UserID, AuctionID) VALUES (?, ?)";
        $insertStmt = $conn->prepare($insertViewQuery);
        $insertStmt->bind_param("ii", $userID, $auctionID);
        $insertStmt->execute();

        $updateViewsQuery = "UPDATE Auction SET Views = Views + 1 WHERE AuctionID = ?";
        $updateStmt = $conn->prepare($updateViewsQuery);
        $updateStmt->bind_param("i", $auctionID);
        $updateStmt->execute();

        $insertStmt->close();
        $updateStmt->close();
    }

    $checkStmt->close();
}

// Fetch auction details including views and image path
$sql = "SELECT a.ItemName, a.Description, a.StartPrice, a.ReservePrice, a.EndDate, a.Image, 
               u.UserName AS SellerName, u.UserID AS SellerID, c.CategoryName, a.Views 
        FROM Auction a
        JOIN Users u ON a.UserID = u.UserID
        JOIN Category c ON a.CategoryID = c.CategoryID
        WHERE a.AuctionID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $auctionID);
$stmt->execute();
$result = $stmt->get_result();

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

    // Fetch seller's average rating
    $sellerID = $auction['SellerID'];
    $avgRatingQuery = "SELECT AVG(Rating) AS AvgRating FROM SellerReviews WHERE SellerID = ?";
    $avgRatingStmt = $conn->prepare($avgRatingQuery);
    $avgRatingStmt->bind_param("i", $sellerID);
    $avgRatingStmt->execute();
    $avgRatingResult = $avgRatingStmt->get_result();
    $avgRatingData = $avgRatingResult->fetch_assoc();
    $avgRating = $avgRatingData['AvgRating'] ?? 0;
    $avgRatingStmt->close();

    // Display auction details with image, reviews, and seller rating
    echo "<div class='container mt-5'>";
    echo "<div class='row'>";
    echo "<div class='col-md-8'>";
    echo "<h2>" . htmlspecialchars($auction['ItemName']) . "</h2>";
    echo "<p><strong>Category:</strong> " . htmlspecialchars($auction['CategoryName']) . "</p>";
    echo "<p><strong>Seller:</strong> " . htmlspecialchars($auction['SellerName']) . "</p>";
    echo "<p><strong>Seller Rating:</strong> " . ($avgRating ? number_format($avgRating, 1) . " / 5" : "No reviews yet") . "</p>";
    
    // Display auction image if available
    if (!empty($auction['Image'])) {
        echo "<img src='" . htmlspecialchars($auction['Image']) . "' alt='Auction Image' class='img-fluid mb-3' />";
    } else {
        echo "<p><em>No image available for this auction.</em></p>";
    }

    echo "<p><strong>Description:</strong><br>" . nl2br(htmlspecialchars($auction['Description'])) . "</p>";
    echo "<p><strong>Views:</strong> " . htmlspecialchars($auction['Views']) . "</p>";
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

    // Watchlist functionality
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && $_SESSION['Role'] === 'buyer') {
        $userID = $_SESSION['UserID'];
        $watchlistQuery = "SELECT * FROM WatchList WHERE AuctionID = ? AND UserID = ?";
        $watchlistStmt = $conn->prepare($watchlistQuery);
        $watchlistStmt->bind_param("ii", $auctionID, $userID);
        $watchlistStmt->execute();
        $watchlistResult = $watchlistStmt->get_result();

        if ($watchlistResult->num_rows > 0) {
            echo "<form method='post' action='remove_from_watchlist.php'>
                    <input type='hidden' name='auctionID' value='$auctionID'>
                    <button type='submit' class='btn btn-secondary btn-block'>Unwatch</button>
                  </form>";
        } else {
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

    // Bid history
    echo "<div class='bid-history mt-5'>";
    echo "<h3>Bid History</h3>";
    $historyQuery = "SELECT b.BidAmount, b.BidTime, u.Username AS BidderName 
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

    // Seller Reviews
    echo "<div class='seller-reviews mt-5'>";
    echo "<h3>Seller Reviews</h3>";
    $reviewQuery = "SELECT Rating, ReviewText, ReviewDate FROM SellerReviews WHERE SellerID = ? ORDER BY ReviewDate DESC";
    $reviewStmt = $conn->prepare($reviewQuery);
    $reviewStmt->bind_param("i", $sellerID);
    $reviewStmt->execute();
    $reviewResult = $reviewStmt->get_result();

    if ($reviewResult->num_rows > 0) {
        echo "<ul class='list-group'>";
        while ($review = $reviewResult->fetch_assoc()) {
            echo "<li class='list-group-item'>";
            echo "<strong>Rating:</strong> " . $review['Rating'] . " / 5<br>";
            echo "<strong>Review:</strong> " . htmlspecialchars($review['ReviewText']) . "<br>";
            echo "<small><strong>Date:</strong> " . date("j M Y", strtotime($review['ReviewDate'])) . "</small>";
            echo "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No reviews for this seller.</p>";
    }

    $reviewStmt->close();
}

$stmt->close();
$bidStmt->close();
closeConnection($conn);

echo "</div>"; // Close container

include_once("footer.php");
?>
