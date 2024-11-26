<?php include_once("header.php"); ?>

<?php if (session_status() == PHP_SESSION_NONE) {
    session_start();
} ?>

<?php
// This block of code is responsible for ensuring that only users with the role of 'seller' and are logged in that can access this page
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['Role'] !== 'seller') {
  echo "Access denied. Only sellers can create auctions."; //lets the buyer or admin know that they don't have the correct privilages
  exit();
}
?>

<div class="container">

<!-- Create auction form -->
<div style="max-width: 800px; margin: 10px auto">
  <h2 class="my-3">Create new auction</h2>
  <div class="card">
    <div class="card-body">
      <!-- Add enctype="multipart/form-data" to handle file uploads -->
      <form method="post" action="create_auction_result.php" enctype="multipart/form-data">
        <div class="form-group row">
          <label for="auctionTitle" class="col-sm-2 col-form-label text-right">Title of auction</label>
          <div class="col-sm-10">
            <input type="text" class="form-control" id="auctionTitle" name="itemName" placeholder="e.g. Black mountain bike" required>
            <small id="titleHelp" class="form-text text-muted"><span class="text-danger">* Required.</span> A short description of the item you're selling, which will display in listings.</small>
          </div>
        </div>
        <div class="form-group row">
          <label for="auctionDetails" class="col-sm-2 col-form-label text-right">Details</label>
          <div class="col-sm-10">
            <textarea class="form-control" id="auctionDetails" name="description" rows="4" required></textarea>
            <small id="detailsHelp" class="form-text text-muted">Full details of the listing to help bidders decide if it's what they're looking for.</small>
          </div>
        </div>
        <div class="form-group row">
          <label for="auctionCategory" class="col-sm-2 col-form-label text-right">Category</label>
          <div class="col-sm-10">
            <select class="form-control" id="auctionCategory" name="category" required> <!-- This creates the dropdown menu where users can select predefinined categories-->
              <option value="Electronics">Electronics</option>
              <option value="Furniture">Furniture</option>
              <option value="Clothing">Clothing</option>
              <option value="Books">Books</option>
              <option value="Toys">Toys</option>
              <option value="Sports">Sports</option>
              <option value="Other">Other</option>
            </select>
            <small id="categoryHelp" class="form-text text-muted"><span class="text-danger">* Required.</span> Select a category for this item.</small>
          </div>
        </div>
        <div class="form-group row">
          <label for="auctionStartPrice" class="col-sm-2 col-form-label text-right">Starting price</label>
          <div class="col-sm-10">
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text">£</span>
              </div>
              <!-- Set minimum value to 1 so that the users don't penny bid -->
              <input type="number" class="form-control" id="auctionStartPrice" name="startPrice" min="1" required>
            </div>
            <small id="startBidHelp" class="form-text text-muted"><span class="text-danger">* Required.</span> Initial bid amount must be £1 or more.</small>
          </div>
        </div>
        <div class="form-group row">
          <label for="auctionReservePrice" class="col-sm-2 col-form-label text-right">Reserve price</label>
          <div class="col-sm-10">
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text">£</span>
              </div>
              <input type="number" class="form-control" id="auctionReservePrice" name="reservePrice">
            </div>
            <small id="reservePriceHelp" class="form-text text-muted">Optional. Auctions that end below this price will not go through. This value is not displayed in the auction listing.</small>
          </div>
        </div>
        <div class="form-group row">
          <label for="auctionEndDate" class="col-sm-2 col-form-label text-right">End date</label>
          <div class="col-sm-10">
            <?php
              // Calculate minimum end date (next day)
              $minDate = (new DateTime())->modify('+1 day')->format('Y-m-d\TH:i');
            ?>
            <input type="datetime-local" class="form-control" id="auctionEndDate" name="endDate" min="<?php echo $minDate; ?>" required>
            <small id="endDateHelp" class="form-text text-muted"><span class="text-danger">* Required.</span> End date must be at least 1 day from now.</small>
          </div>
        </div>
        <!-- New Photo Upload Field -->
        <div class="form-group row">
          <label for="auctionPhoto" class="col-sm-2 col-form-label text-right">Upload Photo</label>
          <div class="col-sm-10">
            <input type="file" class="form-control-file" id="auctionPhoto" name="auctionPhoto" accept="image/*">
            <small id="photoHelp" class="form-text text-muted">Optional. Upload a photo of the item you're selling.</small>
          </div>
        </div>
        <button type="submit" class="btn btn-primary form-control">Create Auction</button>
      </form>
    </div>
  </div>
</div>

</div>

<?php include_once("footer.php"); ?>