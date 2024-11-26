<?php
// File: place_bid.php
include_once("utilities.php"); // Including utility functions
include_once("config.php"); // Config file for smtp credentials

// Load the required PHPMailer classes for sending email notifications
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Start the session if not already started
}

// // This block checks if the user is logged in and has the 'buyer' role
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['Role'] !== 'buyer') {
    echo "<script>alert('Access denied. Only buyers can place bids.'); window.history.back();</script>";
    exit(); // Exit to prevent further script execution
}

// Collecting form data from POST request
$auctionID = $_POST['auctionID'];
$bidAmount = $_POST['bid_amount'];
$userID = $_SESSION['UserID']; // Get the current logged-in user's ID

// validate bid amout - ensure it's more than 0
if ($bidAmount <= 0) {
    echo "<script>alert('Invalid bid amount.'); window.history.back();</script>";
    exit();
}

// Connect to the database 
$conn = new mysqli("localhost", "root", "", "AuctionDB");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch auction details for validation purposes
$stmt = $conn->prepare("SELECT StartPrice, EndDate, UserID AS SellerID FROM Auction WHERE AuctionID = ?");
$stmt->bind_param("i", $auctionID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) { // If no auction found, display an error
    echo "<script>alert('Auction not found.'); window.history.back();</script>";
    exit();
}

$auction = $result->fetch_assoc();
$startPrice = $auction['StartPrice']; // The starting price of the auction
$endDate = new DateTime($auction['EndDate']); // Auction end time
$sellerID = $auction['SellerID']; // UserID of the auction's seller
$now = new DateTime();

if ($now > $endDate) { // If auction is already over
    echo "<script>alert('The auction has already ended.'); window.history.back();</script>";
    exit();
}

// Check the current highest bid for the auction
$stmt = $conn->prepare("SELECT MAX(BidAmount) AS HighestBid FROM Bid WHERE AuctionID = ?");
$stmt->bind_param("i", $auctionID);
$stmt->execute();
$result = $stmt->get_result();
$highestBidRow = $result->fetch_assoc();
$highestBid = $highestBidRow['HighestBid'] ?? $startPrice; // Default to startPrice if no bids

// Check if there was a previous highest bidder
$previousHighestBidderID = null;
if ($highestBidRow['HighestBid'] !== null) {
    $stmt = $conn->prepare("SELECT UserID FROM Bid WHERE AuctionID = ? AND BidAmount = ?");
    $stmt->bind_param("id", $auctionID, $highestBid);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $previousHighestBid = $result->fetch_assoc();
        $previousHighestBidderID = $previousHighestBid['UserID'];
    }
}

// make sure bid is higher than the current highest bid
if ($bidAmount <= $highestBid) {
    echo "<script>alert('Your bid must be higher than the current highest bid of £" . number_format($highestBid, 2) . "'); window.history.back();</script>";
    exit();
}

// Insert new bid into the Bid table
$stmt = $conn->prepare("INSERT INTO Bid (AuctionID, UserID, BidAmount, BidTime) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("iid", $auctionID, $userID, $bidAmount);

if ($stmt->execute()) {
    // Notify the previous highest bidder if they exist and aren't the current user
    if ($previousHighestBidderID && $previousHighestBidderID != $userID) {
        sendNotification($previousHighestBidderID, $auctionID, 'You have been outbid on an auction');
    }

    // Notify the auction's seller of the new bid
    sendNotification($sellerID, $auctionID, 'New bid placed on your auction');

    // Redirect back to the auction page with a success message
    echo "<script>alert('Bid placed successfully!'); window.location.href = 'auction_details.php?auctionID=$auctionID';</script>";
    exit();
} else {
    // Show an error if the bid could not be placed
    echo "<script>alert('Error placing bid: " . $stmt->error . "'); window.history.back();</script>";
    exit();
}

// clean up resources - close statement and db connection
$stmt->close();
$conn->close();

// Function for sending email notifications using PHPMailer
function sendNotification($userID, $auctionID, $subject) {
    global $conn;

    // Query to fetch user's email address
    $sql = "SELECT Email FROM Users WHERE UserID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    // Query to fetch the auction's name
    $auctionSql = "SELECT ItemName FROM Auction WHERE AuctionID = ?";
    $auctionStmt = $conn->prepare($auctionSql);
    $auctionStmt->bind_param("i", $auctionID);
    $auctionStmt->execute();
    $auctionResult = $auctionStmt->get_result();
    $auction = $auctionResult->fetch_assoc();
    $auctionName = $auction['ItemName'] ?? 'Unknown Auction';
    $auctionStmt->close();

    if ($user) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_EMAIL; // system email
            $mail->Password = SMTP_PASSWORD; // system email password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom(SMTP_EMAIL, 'Auction System'); // sender
            $mail->addAddress($user['Email']); // recipient

            // Set email content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = '
                <p>A new bid has been placed on the auction: <strong>' . htmlspecialchars($auctionName) . '</strong>.</p>
                <p><a href="http://localhost/database-project/browse.php" style="color: blue; text-decoration: underline;">Click here</a> to browse all auctions.</p>';

            // send the email
            $mail->send();
        } catch (Exception $e) {
            echo "Error sending email: {$mail->ErrorInfo}";
        }
    }
}
?>
