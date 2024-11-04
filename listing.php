<?php include_once("header.php"); ?>
<?php require("utilities.php"); ?>

<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_GET['auctionID'])) {
    $item_id = $_GET['auctionID'];

    // Establish database connection
    // Assuming $conn is defined globally in utilities.php or elsewhere
    global $conn;

    // Fetch item details based on item_id
    $sql = "SELECT AuctionID, ItemName, Description, StartPrice, ReservePrice, EndDate FROM Auction WHERE AuctionID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $title = htmlspecialchars($row['ItemName']);
        $description = htmlspecialchars($row['Description']);
        $current_price = $row['StartPrice'];
        $end_time = new DateTime($row['EndDate']);
        
        // Check if the auction is still active
        $now = new DateTime();
        if ($now < $end_time) {
            $time_to_end = date_diff($now, $end_time);
            $time_remaining = ' (in ' . display_time_remaining($time_to_end) . ')';
        } else {
            $time_remaining = "This auction has ended";
        }
    } else {
        echo "<p>Auction not found.</p>";
        exit;
    }
} else {
    echo "<p>Invalid item ID.</p>";
    exit;
}

// Check if the user is watching this item (placeholder for now)
$watching = false; // This would come from a query if watchlist is implemented
?>

<div class="container">

    <div class="row">
        <div class="col-sm-8">
            <h2 class="my-3"><?php echo $title; ?></h2>
        </div>
        <div class="col-sm-4 align-self-center">
            <?php if ($now < $end_time): ?>
                <div id="watch_nowatch" <?php if ($watching) echo 'style="display: none"'; ?>>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addToWatchlist()">+ Add to watchlist</button>
                </div>
                <div id="watch_watching" <?php if (!$watching) echo 'style="display: none"'; ?>>
                    <button type="button" class="btn btn-success btn-sm" disabled>Watching</button>
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeFromWatchlist()">Remove watch</button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-8">
            <div class="itemDescription">
                <p><?php echo $description; ?></p>
            </div>
        </div>

        <div class="col-sm-4">
            <p>
                <?php if ($now > $end_time): ?>
                    This auction ended <?php echo $end_time->format('j M H:i'); ?>
                <?php else: ?>
                    Auction ends <?php echo $end_time->format('j M H:i') . $time_remaining; ?>
                <?php endif; ?>
            </p>
            <p class="lead">Current bid: £<?php echo number_format($current_price, 2); ?></p>

            <?php if ($now < $end_time): ?>
                <form method="POST" action="place_bid.php">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">£</span>
                        </div>
                        <input type="number" class="form-control" id="bid" name="bid" required>
                    </div>
                    <button type="submit" class="btn btn-primary form-control">Place bid</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once("footer.php"); ?>

<script>
function addToWatchlist() {
    $.ajax('watchlist_funcs.php', {
        type: "POST",
        data: {functionname: 'add_to_watchlist', arguments: [<?php echo $item_id; ?>]},
        success: function (response) {
            if (response.trim() === "success") {
                $("#watch_nowatch").hide();
                $("#watch_watching").show();
            } else {
                alert("Failed to add to watchlist.");
            }
        },
        error: function () {
            alert("Error adding to watchlist.");
        }
    });
}

function removeFromWatchlist() {
    $.ajax('watchlist_funcs.php', {
        type: "POST",
        data: {functionname: 'remove_from_watchlist', arguments: [<?php echo $item_id; ?>]},
        success: function (response) {
            if (response.trim() === "success") {
                $("#watch_watching").hide();
                $("#watch_nowatch").show();
            } else {
                alert("Failed to remove from watchlist.");
            }
        },
        error: function () {
            alert("Error removing from watchlist.");
        }
    });
}
</script>