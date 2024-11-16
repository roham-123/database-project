<?php 
include_once("header.php");
require("utilities.php");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<div class="container">
    <h2 class="my-3">Recommendations for you</h2>

    <?php
    // Check if the user is logged in and is a buyer.
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
    if (!$stmt) {
        echo "Error preparing statement for fetching user auctions: " . $conn->error;
        exit();
    }
    
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

    // Step 2: Find other users who have bid on the same auctions as the current user
    $placeholders = implode(',', array_fill(0, count($userAuctionIDs), '?'));
    $query = "
        SELECT DISTINCT UserID
        FROM Bid
        WHERE AuctionID IN ($placeholders) AND UserID != ?
    ";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        echo "Error preparing statement for finding similar users: " . $conn->error;
        exit();
    }

    // Create a dynamic array of types for the bind_param
    $types = str_repeat('i', count($userAuctionIDs)) . 'i';
    $params = array_merge($userAuctionIDs, [$userID]);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch the IDs of similar users
    $similarUserIDs = [];
    while ($row = $result->fetch_assoc()) {
        $similarUserIDs[] = $row['UserID'];
    }

    // Step 3: Find auctions that these similar users have bid on but the current user hasn't
    if (!empty($similarUserIDs)) {
        $placeholders = implode(',', array_fill(0, count($similarUserIDs), '?'));
        $query = "
            SELECT DISTINCT AuctionID
            FROM Bid
            WHERE UserID IN ($placeholders) AND AuctionID NOT IN (" . implode(",", $userAuctionIDs) . ")
        ";

        $stmt = $conn->prepare($query);
        if (!$stmt) {
            echo "Error preparing statement for fetching recommended auctions: " . $conn->error;
            exit();
        }

        // Bind similar user IDs to the query
        $types = str_repeat('i', count($similarUserIDs));
        $stmt->bind_param($types, ...$similarUserIDs);
        $stmt->execute();
        $result = $stmt->get_result();

        // Fetch recommended auctions
        $recommendedAuctionIDs = [];
        while ($row = $result->fetch_assoc()) {
            $recommendedAuctionIDs[] = $row['AuctionID'];
        }

        // Step 4: Fetch and display the auction details for the recommended auctions
        if (!empty($recommendedAuctionIDs)) {
            echo "<ul class='list-group'>";
            foreach ($recommendedAuctionIDs as $auctionID) {
                $auctionQuery = "SELECT * FROM Auction WHERE AuctionID = ?";
                $auctionStmt = $conn->prepare($auctionQuery);
                if (!$auctionStmt) {
                    echo "Error preparing statement for fetching auction details: " . $conn->error;
                    exit();
                }

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

                $auctionStmt->close();
            }
            echo "</ul>";
        } else {
            echo "<p>No recommendations available based on your bid history.</p>";
        }
    } else {
        echo "<p>No similar users found who bid on the same auctions as you.</p>";
    }

    $stmt->close();
    closeConnection($conn);
    ?>

</div>

<?php include_once("footer.php") ?>
