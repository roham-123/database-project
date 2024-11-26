<?php 
include_once("header.php"); // include header for navigation and stuff
require("utilities.php");  // to get utility functions

if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Start session if not already started
}
?>

<div class="container">
    <h2 class="my-3">Recommendations for you</h2>

    <?php
    // Check if the user is logged in & if they’re a buyer
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['Role'] !== 'buyer') {
        echo "Access denied. Please log in as a buyer to view recommendations."; // Display error message if user is not logged in as buyer
        exit();
    }

    // get the user's ID from the session
    $userID = $_SESSION['UserID'];

    // Step 1: Find auctions the user has bid on
    $query = "
        SELECT DISTINCT AuctionID
        FROM Bid
        WHERE UserID = ?
    ";
    $stmt = $conn->prepare($query); // Preparing SQL stmt
    if (!$stmt) {
        // Check for errors in the SQL stmt preparation
        echo "Error preparing statement for fetching user auctions: " . $conn->error;
        exit();
    }
    $stmt->bind_param("i", $userID); // Bind user ID to stmt
    $stmt->execute(); // Execute the stmt
    $result = $stmt->get_result(); // Get result from execution

    // Store auctions user has bid on
    $userAuctionIDs = [];
    while ($row = $result->fetch_assoc()) {
        $userAuctionIDs[] = $row['AuctionID']; // push auction IDs to array
    }

    // if user hasn’t bid on anything yet
    if (empty($userAuctionIDs)) {
        echo "<p>You haven't placed any bids yet. Browse auctions to start getting recommendations.</p>"; // Msg for no bids
        exit();
    }

    // Step 2: Find other users who’ve bid on the same auctions
    $placeholders = implode(',', array_fill(0, count($userAuctionIDs), '?')); // Generate placeholder string for query
    $query = "
        SELECT DISTINCT UserID
        FROM Bid
        WHERE AuctionID IN ($placeholders) AND UserID != ?
    ";
    $stmt = $conn->prepare($query);
    if (!$stmt) { 
        // Print error if stmt preparation fails
        echo "Error preparing statement for finding similar users: " . $conn->error;
        exit();
    }

    // Create param string for the stmt
    $types = str_repeat('i', count($userAuctionIDs)) . 'i'; 
    $params = array_merge($userAuctionIDs, [$userID]); // Merge auction IDs and user ID for binding
    $stmt->bind_param($types, ...$params); // bind params dynamically
    $stmt->execute(); 
    $result = $stmt->get_result();

    // Fetch user IDs of ppl who bid on same auctions
    $similarUserIDs = [];
    while ($row = $result->fetch_assoc()) {
        $similarUserIDs[] = $row['UserID']; 
    }

    // Step 3: Get auctions bid by similar users, exclude current user’s bids
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
            exit(); // Break if stmt preparation fails
        }

        // Bind similar user IDs
        $types = str_repeat('i', count($similarUserIDs));
        $stmt->bind_param($types, ...$similarUserIDs); 
        $stmt->execute();
        $result = $stmt->get_result();

        // Collect recommendations
        $recommendedAuctionIDs = [];
        while ($row = $result->fetch_assoc()) {
            $recommendedAuctionIDs[] = $row['AuctionID'];
        }

        // Step 4: Fetch auction details for recommendations
        if (!empty($recommendedAuctionIDs)) { 
            echo "<ul class='list-group'>";
            foreach ($recommendedAuctionIDs as $auctionID) {
                $auctionQuery = "SELECT * FROM Auction WHERE AuctionID = ?";
                $auctionStmt = $conn->prepare($auctionQuery);
                if (!$auctionStmt) {
                    echo "Error preparing statement for fetching auction details: " . $conn->error;
                    exit();
                }

                $auctionStmt->bind_param("i", $auctionID); // bind auction ID
                $auctionStmt->execute(); 
                $auctionResult = $auctionStmt->get_result();

                // Print auctions
                while ($auction = $auctionResult->fetch_assoc()) { 
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
            echo "<p>No recommendations available based on your bid history.</p>"; // Msg if no recommendations
        }
    } else {
        echo "<p>No similar users found who bid on the same auctions as you.</p>"; // Msg for no similar users
    }

    $stmt->close();
    closeConnection($conn); // Close DB connection
    ?>

</div>

<?php include_once("footer.php") ?> 
