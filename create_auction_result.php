<?php
include_once("utilities.php");
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// This block of code is responsible for ensuring that only users with the role of 'seller' and are logged in that can access this page
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['Role'] !== 'seller') {
    echo "Access denied. Only sellers can create auctions."; //lets the user know what role has the required privilages 
    exit();
}

// This gets the form data and assigns it to variables
$itemName = $_POST['itemName'];
$description = $_POST['description'];
$category = $_POST['category']; 
$startPrice = $_POST['startPrice'];
$reservePrice = $_POST['reservePrice'];
$endDate = $_POST['endDate'];
$userID = $_SESSION['UserID'];
$imagePath = null; // To store the path of the uploaded image

// This is responsible for handling the file upload if a file is uploaded
if (isset($_FILES['auctionPhoto']) && $_FILES['auctionPhoto']['error'] == UPLOAD_ERR_OK) {
    // This sets the target directory and unique filename
    $targetDir = "uploads/";
    $fileType = strtolower(pathinfo($_FILES["auctionPhoto"]["name"], PATHINFO_EXTENSION));

    // This handles the type of files that the user allows, any other file time is unlikley to display well and may cause potential users
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    if (in_array($fileType, $allowedTypes)) {
        $uniqueFileName = uniqid() . "." . $fileType;
        $targetFile = $targetDir . $uniqueFileName;

        // This saves the filepath into the database
        if (move_uploaded_file($_FILES["auctionPhoto"]["tmp_name"], $targetFile)) {
            $imagePath = $targetFile; 
        } else {
            echo "<script>alert('Error uploading file.'); window.history.back();</script>";
            exit();
        }
    } else {
        echo "<script>alert('Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.'); window.history.back();</script>";
        exit();
    }
}

// This Fetches the CategoryID based on the category name
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

// This prepares the SQL query to insert auction data into the Auction table and the image path
$stmt = $conn->prepare("INSERT INTO Auction (UserID, ItemName, Description, CategoryID, StartPrice, ReservePrice, EndDate, Image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("issiddss", $userID, $itemName, $description, $categoryID, $startPrice, $reservePrice, $endDate, $imagePath);

// This executes the query and checks if it is successful
if ($stmt->execute()) {
    // Gets the ID of the created auction
    $auctionID = $stmt->insert_id;
    $stmt->close();
    closeConnection($conn);

    // Redirects to the auction details page
    header("Location: auction_details.php?auctionID=$auctionID");
    exit();
} else {
    echo "<script>alert('Error creating auction: " . $stmt->error . "'); window.history.back();</script>";
}

// Closes the statement and connection
$stmt->close();
closeConnection($conn);
?>