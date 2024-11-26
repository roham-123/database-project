<?php
include_once("utilities.php");
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// This block is responsible for checking is the user is logged in and what their role is. Only Buyer has the coorect privilage to see the contents of the page below
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['Role'] !== 'buyer') {
    echo "<script>alert('Access denied. Only buyers can add auctions to their watchlist.'); window.history.back();</script>";
    exit();
}

$auctionID = $_POST['auctionID']; // Gets the value of auctionID from the form that has been submiutted
$userID = $_SESSION['UserID']; // This checks the userID of the logged in user using the $_SESSION method

// This block of code establishes the connection with the database. It ensures that it looks at localhost, the root folder and the correct SQL file
$conn = new mysqli("localhost", "root", "", "AuctionDB");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// This is responisble for checking if the auction already exists in the watchlist
$stmt = $conn->prepare("SELECT * FROM WatchList WHERE AuctionID = ? AND UserID = ?");
$stmt->bind_param("ii", $auctionID, $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) { // If the auction is already in the watchlist then it prints the line below:
    echo "<script>alert('This auction is already in your watchlist.'); window.location.href = 'auction_details.php?auctionID=$auctionID';</script>";
} else {
    // If it isn't already in the watchlist then this inserts the action into the watchlist table
    $stmt = $conn->prepare("INSERT INTO WatchList (AuctionID, UserID) VALUES (?, ?)");
    $stmt->bind_param("ii", $auctionID, $userID);

    if ($stmt->execute()) { // The message lets the user know that their auction has been successfully added to their watchlist
        echo "<script>alert('Auction added to your watchlist!'); window.location.href = 'auction_details.php?auctionID=$auctionID';</script>";
    } else {
        echo "<script>alert('Error adding auction to watchlist: " . addslashes($stmt->error) . "'); window.history.back();</script>";
    }
}

// Closes the connection to avoid duplicating multiple connections to the database
$stmt->close();
$conn->close();
?>