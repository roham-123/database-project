<?php
// File: place_bid.php
include_once("utilities.php");
include_once("config.php");  // Include the config file for SMTP credentials
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is a buyer
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['Role'] !== 'buyer') {
    echo "<script>alert('Access denied. Only buyers can place bids.'); window.history.back();</script>";
    exit();
}

// Get form data
$auctionID = $_POST['auctionID'];
$bidAmount = $_POST['bid_amount'];
$userID = $_SESSION['UserID'];

// Validate bid amount
if ($bidAmount <= 0) {
    echo "<script>alert('Invalid bid amount.'); window.history.back();</script>";
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "AuctionDB");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch auction details
$stmt = $conn->prepare("SELECT StartPrice, EndDate, UserID AS SellerID FROM Auction WHERE AuctionID = ?");
$stmt->bind_param("i", $auctionID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Auction not found.'); window.history.back();</script>";
    exit();
}

$auction = $result->fetch_assoc();
$startPrice = $auction['StartPrice'];
$endDate = new DateTime($auction['EndDate']);
$sellerID = $auction['SellerID'];
$now = new DateTime();

if ($now > $endDate) {
    echo "<script>alert('The auction has already ended.'); window.history.back();</script>";
    exit();
}

// Fetch the highest bid amount
$stmt = $conn->prepare("SELECT MAX(BidAmount) AS HighestBid FROM Bid WHERE AuctionID = ?");
$stmt->bind_param("i", $auctionID);
$stmt->execute();
$result = $stmt->get_result();
$highestBidRow = $result->fetch_assoc();
$highestBid = $highestBidRow['HighestBid'] ?? $startPrice;

// Fetch the user ID of the previous highest bidder, if there is one
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

// Ensure bid amount is higher than the current highest bid
if ($bidAmount <= $highestBid) {
    echo "<script>alert('Your bid must be higher than the current highest bid of £" . number_format($highestBid, 2) . "'); window.history.back();</script>";
    exit();
}

// Insert the new bid
$stmt = $conn->prepare("INSERT INTO Bid (AuctionID, UserID, BidAmount, BidTime) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("iid", $auctionID, $userID, $bidAmount);

if ($stmt->execute()) {
    // Notify the previous highest bidder if applicable
    if ($previousHighestBidderID && $previousHighestBidderID != $userID) {
        sendNotification($previousHighestBidderID, $auctionID, 'You have been outbid on an auction');
    }

    // Notify the seller about the new bid
    sendNotification($sellerID, $auctionID, 'New bid placed on your auction');

    // Redirect back to the auction with a success message
    echo "<script>alert('Bid placed successfully!'); window.location.href = 'auction_details.php?auctionID=$auctionID';</script>";
    exit();
} else {
    echo "<script>alert('Error placing bid: " . $stmt->error . "'); window.history.back();</script>";
    exit();
}

// Close statement and connection
$stmt->close();
$conn->close();

// Function to send email notification
function sendNotification($userID, $auctionID, $subject) {
    global $conn;

    $sql = "SELECT Email FROM Users WHERE UserID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_EMAIL;         // Use the system email from config.php
            $mail->Password = SMTP_PASSWORD;      // Use the app password from config.php
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom(SMTP_EMAIL, 'Auction System'); // Set sender to system email
            $mail->addAddress($user['Email']);            // Add recipient email

            // Email content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = 'A new bid has been placed on auction ID: ' . $auctionID . '. Please visit your auction to see the latest bid.';

            // Send email
            $mail->send();
        } catch (Exception $e) {
            echo "Error sending email: {$mail->ErrorInfo}";
        }
    }

    $stmt->close();
}
?>