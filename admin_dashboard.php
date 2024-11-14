<?php
include_once("header.php");
include_once("utilities.php");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is an admin
if (!isset($_SESSION['Role']) || $_SESSION['Role'] != 'admin') {
    echo "<div class='container mt-5'><p>Access denied. Only admins can view this page.</p></div>";
    exit;
}

// Function to log admin actions
function logAdminAction($conn, $adminID, $actionType, $actionDescription) {
    $logQuery = "INSERT INTO AdminActions (AdminID, ActionType, ActionDescription) VALUES (?, ?, ?)";
    if ($stmt = $conn->prepare($logQuery)) {
        $stmt->bind_param("iss", $adminID, $actionType, $actionDescription);
        $stmt->execute();
        $stmt->close();
    }
}

// Handle delete auction request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_auction'])) {
    $auctionID = $_POST['delete_auction'];
    $deleteQuery = "DELETE FROM Auction WHERE AuctionID = ?";
    if ($stmt = $conn->prepare($deleteQuery)) {
        $stmt->bind_param("i", $auctionID);
        if ($stmt->execute()) {
            echo "<div class='container mt-3 alert alert-success'>Auction deleted successfully.</div>";

            // Log the admin action
            $adminID = $_SESSION['UserID'];
            $actionType = "Delete Auction";
            $actionDescription = "Deleted auction with AuctionID: $auctionID";
            logAdminAction($conn, $adminID, $actionType, $actionDescription);
        } else {
            echo "<div class='container mt-3 alert alert-danger'>Error deleting auction: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }
}

// Handle blacklist user request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['blacklist_user'])) {
    $userID = $_POST['blacklist_user'];
    $blacklistQuery = "UPDATE Users SET blacklisted = 1 WHERE UserID = ?";
    if ($stmt = $conn->prepare($blacklistQuery)) {
        $stmt->bind_param("i", $userID);
        if ($stmt->execute()) {
            echo "<div class='container mt-3 alert alert-success'>User blacklisted successfully.</div>";

            // Log the admin action
            $adminID = $_SESSION['UserID'];
            $actionType = "Blacklist User";
            $actionDescription = "Blacklisted user with UserID: $userID";
            logAdminAction($conn, $adminID, $actionType, $actionDescription);
        } else {
            echo "<div class='container mt-3 alert alert-danger'>Error blacklisting user: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }
}

// Fetch all auctions
$auctionsQuery = "SELECT * FROM Auction";
$auctionsResult = $conn->query($auctionsQuery);

// Fetch all users
$usersQuery = "SELECT * FROM Users WHERE Role != 'admin'";
$usersResult = $conn->query($usersQuery);

// Fetch all admin actions
$logsQuery = "SELECT AdminActions.*, Users.Username FROM AdminActions JOIN Users ON AdminActions.AdminID = Users.UserID ORDER BY ActionDate DESC";
$logsResult = $conn->query($logsQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="bootstrap.min.css"> <!-- Assuming Bootstrap is included -->
</head>
<body>
<div class="container mt-5">
    <h1>Admin Dashboard</h1>

    <h2>Manage Auctions</h2>
    <form method="POST" action="">
        <table class="table table-bordered">
            <tr>
                <th>AuctionID</th>
                <th>Item Name</th>
                <th>Actions</th>
            </tr>
            <?php while ($auction = $auctionsResult->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($auction['AuctionID']); ?></td>
                    <td><?php echo htmlspecialchars($auction['ItemName']); ?></td>
                    <td>
                        <button type="submit" name="delete_auction" value="<?php echo $auction['AuctionID']; ?>"
                                class="btn btn-danger"
                                onclick="return confirm('Are you sure you want to delete this auction?');">Delete
                        </button>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </form>

    <h2>Manage Users</h2>
    <form method="POST" action="">
        <table class="table table-bordered">
            <tr>
                <th>UserID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
            <?php while ($user = $usersResult->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['UserID']); ?></td>
                    <td><?php echo htmlspecialchars($user['Username']); ?></td>
                    <td><?php echo htmlspecialchars($user['Email']); ?></td>
                    <td>
                        <?php if (!$user['blacklisted']) { ?>
                            <button type="submit" name="blacklist_user" value="<?php echo $user['UserID']; ?>"
                                    class="btn btn-warning"
                                    onclick="return confirm('Are you sure you want to blacklist this user?');">Blacklist
                            </button>
                        <?php } else { ?>
                            <span class="text-danger">Blacklisted</span>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </form>

    <h2>Admin Action Logs</h2>
    <table class="table table-bordered">
        <tr>
            <th>ActionID</th>
            <th>Admin Username</th>
            <th>Action Type</th>
            <th>Action Description</th>
            <th>Action Date</th>
        </tr>
        <?php while ($log = $logsResult->fetch_assoc()) { ?>
            <tr>
                <td><?php echo htmlspecialchars($log['ActionID']); ?></td>
                <td><?php echo htmlspecialchars($log['Username']); ?></td>
                <td><?php echo htmlspecialchars($log['ActionType']); ?></td>
                <td><?php echo htmlspecialchars($log['ActionDescription']); ?></td>
                <td><?php echo htmlspecialchars($log['ActionDate']); ?></td>
            </tr>
        <?php } ?>
    </table>
</div>
</body>
</html>

<?php
$conn->close();
?>
