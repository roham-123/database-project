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

// Fetch the CategoryID based on the category name
$stmt = $conn->prepare("SELECT CategoryID FROM Category WHERE CategoryName = ?");
$stmt->bind_param("s", $category);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $categoryID = $row['CategoryID'];
} else {
    die("Invalid category selected.");
}

$stmt->close();

// Prepare SQL query to insert auction data into the Auction table
$stmt = $conn->prepare("INSERT INTO Auction (UserID, ItemName, Description, CategoryID, StartPrice, ReservePrice, EndDate) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("issidds", $userID, $itemName, $description, $categoryID, $startPrice, $reservePrice, $endDate);

// Execute the query and check if it was successful
if ($stmt->execute()) {
    echo "Auction created successfully! <a href='browse.php'>Go back to browse auctions</a>";
} else {
    echo "Error: " . $stmt->error;
}

// Close statement and connection
$stmt->close();
closeConnection($conn);
?>

</div>


<?php include_once("footer.php")?>