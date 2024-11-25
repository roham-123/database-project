<?php
include_once("utilities.php");
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is a seller
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['Role'] !== 'seller') {
    echo "Access denied. Only sellers can create auctions.";
    exit();
}

// Get form data
$itemName = $_POST['itemName'];
$description = $_POST['description'];
$category = $_POST['category']; // This is the category name from the form
$startPrice = $_POST['startPrice'];
$reservePrice = $_POST['reservePrice'];
$endDate = $_POST['endDate'];
$userID = $_SESSION['UserID'];
$imagePath = null; // To store the path of the uploaded image

// Handle file upload if a file is uploaded
if (isset($_FILES['auctionPhoto']) && $_FILES['auctionPhoto']['error'] == UPLOAD_ERR_OK) {
    // Set the target directory and unique filename
    $targetDir = "uploads/";
    $fileType = strtolower(pathinfo($_FILES["auctionPhoto"]["name"], PATHINFO_EXTENSION));

    // Allowed file types
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    if (in_array($fileType, $allowedTypes)) {
        $uniqueFileName = uniqid() . "." . $fileType;
        $targetFile = $targetDir . $uniqueFileName;

        // Attempt to move the uploaded file
        if (move_uploaded_file($_FILES["auctionPhoto"]["tmp_name"], $targetFile)) {
            $imagePath = $targetFile; // Save file path to store in the database
        } else {
            echo "<script>alert('Error uploading file.'); window.history.back();</script>";
            exit();
        }
    } else {
        echo "<script>alert('Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.'); window.history.back();</script>";
        exit();
    }
}

// Fetch the CategoryID based on the category name
$stmt = $conn->prepare("SELECT CategoryID FROM Category WHERE CategoryName = ?");
$stmt->bind_param("s", $category);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $categoryID = $row['CategoryID'];
} else {
    echo "<script>alert('Invalid category selected.'); window.history.back();</script>";
    exit();
}

$stmt->close();

// Prepare SQL query to insert auction data into the Auction table, including the image path
$stmt = $conn->prepare("INSERT INTO Auction (UserID, ItemName, Description, CategoryID, StartPrice, ReservePrice, EndDate, Image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("issiddss", $userID, $itemName, $description, $categoryID, $startPrice, $reservePrice, $endDate, $imagePath);

// Execute the query and check if it was successful
if ($stmt->execute()) {
    // Get the ID of the created auction
    $auctionID = $stmt->insert_id;
    $stmt->close();
    closeConnection($conn);

    // Redirect to the auction details page
    header("Location: auction_details.php?auctionID=$auctionID");
    exit();
} else {
    echo "<script>alert('Error creating auction: " . $stmt->error . "'); window.history.back();</script>";
}

// Close statement and connection
$stmt->close();
closeConnection($conn);
?>