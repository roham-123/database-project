<?php
include_once("utilities.php");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== 'buyer') {
    echo "Access denied.";
    exit();
}

// Check if all required fields are provided
if (isset($_POST['auctionID'], $_POST['sellerID'], $_POST['rating'])) {
    $auctionID = $_POST['auctionID'];
    $sellerID = $_POST['sellerID'];
    $buyerID = $_SESSION['UserID'];
    $rating = $_POST['rating'];
    $reviewText = $_POST['review'] ?? '';

    // Insert the review into SellerReviews table
    $insertReviewQuery = "INSERT INTO SellerReviews (AuctionID, BuyerID, SellerID, Rating, ReviewText) VALUES (?, ?, ?, ?, ?)";
    $insertReviewStmt = $conn->prepare($insertReviewQuery);
    $insertReviewStmt->bind_param("iiiss", $auctionID, $buyerID, $sellerID, $rating, $reviewText);

    if ($insertReviewStmt->execute()) {
        echo "Thank you! Your review has been submitted.";
        header("Location: auction_details.php?auctionID=" . $auctionID);
    } else {
        echo "Error: Could not submit your review.";
    }

    $insertReviewStmt->close();
} else {
    echo "All required fields are not provided.";
}

closeConnection($conn);
?>
