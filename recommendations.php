<?php include_once("header.php") ?>
<?php require("utilities.php") ?>

<?php if (session_status() == PHP_SESSION_NONE) {
    session_start();
} ?>

<div class="container">
    <h2 class="my-3">Recommendations for you</h2>

    <?php
    // Check if the user is logged in and is a buyer
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['Role'] !== 'buyer') {
        echo "Access denied. Please log in as a buyer to view recommendations.";
        exit();
    }

    // Get the current user's ID
    $userID = $_SESSION['UserID'];

    // Step 1: Find auctions the current user has bid on
    $query = "
        SELECT DISTINCT AuctionID
        FROM Bid
        WHERE UserID = ?
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();

    // Get all auctions the user has bid on
    $userAuctionIDs = [];
    while ($row = $result->fetch_assoc()) {
        $userAuctionIDs[] = $row['AuctionID'];
    }

    // If the user hasn't bid on any auctions, show a message
    if (empty($userAuctionIDs)) {
        echo "<p>You haven't placed any bids yet. Browse auctions to start getting recommendations.</p>";
        exit();
    }

    // Step 2: Find similar users who have also bid on these auctions
    $query = "
        SELECT DISTINCT b2.AuctionID
        FROM Bid b1
        JOIN Bid b2 ON b1.UserID != b2.UserID
        WHERE b1.AuctionID IN (" . implode(",", $userAuctionIDs) . ")
        AND b2.UserID != ?
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch recommended auctions
    $recommendedAuctions = [];
    while ($row = $result->fetch_assoc()) {
        $recommendedAuctions[] = $row['AuctionID'];
    }

    // Step 3: Fetch the auction details for the recommended auctions
    if (!empty($recommendedAuctions)) {
        echo "<ul class='list-group'>";
        foreach ($recommendedAuctions as $auctionID) {
            $auctionQuery = "SELECT * FROM Auction WHERE AuctionID = ?";
            $auctionStmt = $conn->prepare($auctionQuery);
            $auctionStmt->bind_param("i", $auctionID);
            $auctionStmt->execute();
            $auctionResult = $auctionStmt->get_result();

            while ($auction = $auctionResult->fetch_assoc()) {
                // Display auction details as list items
                echo "<li class='list-group-item'>";
                echo "<h5>" . htmlspecialchars($auction['ItemName']) . "</h5>";
                echo "<p><strong>Starting Price:</strong> £" . number_format($auction['StartPrice'], 2) . "</p>";
                echo "<p><strong>Ends:</strong> " . date("j M Y, H:i", strtotime($auction['EndDate'])) . "</p>";
                echo "<a href='auction_details.php?auctionID=" . $auction['AuctionID'] . "' class='btn btn-primary'>View Auction</a>";
                echo "</li>";
            }
        }
        echo "</ul>";
    } else {
        echo "<p>No recommendations available based on your bid history.</p>";
    }

    $stmt->close();
    closeConnection($conn);
    ?>

</div>

<?php include_once("footer.php") ?>