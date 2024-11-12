<?php include_once("header.php")?>
<?php require("utilities.php")?>

<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

if (isset($_SESSION['UserID'])) {
    echo "Logged in as: " . $_SESSION['Username'] . " (Role: " . $_SESSION['Role'] . ")";
} else {
    echo "Not logged in.";
}
?>

<div class="container">

<h2 class="my-3">Browse listings</h2>

<div id="searchSpecs">
<!-- When this form is submitted, this PHP page is what processes it.
     Search/sort specs are passed to this page through parameters in the URL
     (GET method of passing data to a page). -->
<form method="get" action="browse.php">
  <div class="row">
    <div class="col-md-5 pr-0">
      <div class="form-group">
        <label for="keyword" class="sr-only">Search keyword:</label>
	    <div class="input-group">
          <div class="input-group-prepend">
            <span class="input-group-text bg-transparent pr-0 text-muted">
              <i class="fa fa-search"></i>
            </span>
          </div>
          <input type="text" class="form-control border-left-0" id="keyword" name="keyword" placeholder="Search for anything" value="<?php echo isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : ''; ?>">
        </div>
      </div>
    </div>
    <div class="col-md-3 pr-0">
      <div class="form-group">
        <label for="cat" class="sr-only">Search within:</label>
        <select class="form-control" id="cat" name="cat">
          <option selected value="all">All categories</option>
          <option value="Electronics" <?php echo isset($_GET['cat']) && $_GET['cat'] == 'Electronics' ? 'selected' : ''; ?>>Electronics</option>
          <option value="Furniture" <?php echo isset($_GET['cat']) && $_GET['cat'] == 'Furniture' ? 'selected' : ''; ?>>Furniture</option>
          <option value="Clothing" <?php echo isset($_GET['cat']) && $_GET['cat'] == 'Clothing' ? 'selected' : ''; ?>>Clothing</option>
        </select>
      </div>
    </div>
    <div class="col-md-3 pr-0">
      <div class="form-inline">
        <label class="mx-2" for="order_by">Sort by:</label>
        <select class="form-control" id="order_by" name="order_by">
          <option selected value="pricelow" <?php echo isset($_GET['order_by']) && $_GET['order_by'] == 'pricelow' ? 'selected' : ''; ?>>Price (low to high)</option>
          <option value="pricehigh" <?php echo isset($_GET['order_by']) && $_GET['order_by'] == 'pricehigh' ? 'selected' : ''; ?>>Price (high to low)</option>
          <option value="date" <?php echo isset($_GET['order_by']) && $_GET['order_by'] == 'date' ? 'selected' : ''; ?>>Soonest expiry</option>
        </select>
      </div>
    </div>
    <div class="col-md-1 px-0">
      <button type="submit" class="btn btn-primary">Search</button>
    </div>
  </div>
</form>
</div> <!-- end search specs bar -->

</div>

<?php
  // Retrieve these from the URL
  if (!isset($_GET['keyword'])) {
    // TODO: Define behavior if a keyword has not been specified.
  }
  else {
    $keyword = $_GET['keyword'];
  }

  if (!isset($_GET['cat'])) {
    // TODO: Define behavior if a category has not been specified.
  }
  else {
    $category = $_GET['cat'];
  }
  
  if (!isset($_GET['order_by'])) {
    // TODO: Define behavior if an order_by value has not been specified.
  }
  else {
    $ordering = $_GET['order_by'];
  }

  /* TODO: Use above values to construct a query. Use this query to 
     retrieve data from the database. (If there is no form data entered,
     decide on appropriate default value/default query to make. */
?>

<div class="container mt-5">

<!-- TODO: If result set is empty, print an informative message. Otherwise... -->

<ul class="list-group">

<!-- TODO: Use a while loop to print a list item for each auction listing
     retrieved from the query -->

<?php
// Retrieve filters from the URL if set
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
$category = isset($_GET['cat']) ? $_GET['cat'] : 'all';
$ordering = isset($_GET['order_by']) ? $_GET['order_by'] : 'date';

// Set up the base SQL query to fetch auctions
$sql = "SELECT AuctionID, ItemName, Description, StartPrice, ReservePrice, EndDate FROM Auction WHERE EndDate > NOW()";

// Add filter for keyword on ItemName only
if (!empty($keyword)) {
    $sql .= " AND ItemName LIKE ?";
    $keyword_param = "%" . $keyword . "%";
}
if ($category !== 'all') {
    $sql .= " AND CategoryID = (SELECT CategoryID FROM Category WHERE CategoryName = ?)";
}

// Order by user preference
switch ($ordering) {
    case 'pricelow':
        $sql .= " ORDER BY StartPrice ASC";
        break;
    case 'pricehigh':
        $sql .= " ORDER BY StartPrice DESC";
        break;
    case 'date':
        $sql .= " ORDER BY EndDate ASC";
        break;
}

// Prepare and execute the query
$stmt = $conn->prepare($sql);
if (!empty($keyword) && $category !== 'all') {
    $stmt->bind_param("ss", $keyword_param, $category);
} elseif (!empty($keyword)) {
    $stmt->bind_param("s", $keyword_param);
} elseif ($category !== 'all') {
    $stmt->bind_param("s", $category);
}
$stmt->execute();
$result = $stmt->get_result();

// Loop through the fetched results and display each auction
while ($row = $result->fetch_assoc()) {
    $auctionID = $row['AuctionID'];
    $itemName = htmlspecialchars($row['ItemName']);
    $description = htmlspecialchars($row['Description']);
    $startPrice = number_format($row['StartPrice'], 2);
    $endDate = new DateTime($row['EndDate']);
    $formattedDate = $endDate->format('Y-m-d H:i:s');

    echo "<li class='list-group-item'>";
    echo "<h5>$itemName</h5>";
    echo "<p>$description</p>";
    echo "<p>Starting Price: £$startPrice</p>";
    echo "<p>Ends: $formattedDate</p>";
    echo "<a href='auction_details.php?auctionID=$auctionID' class='btn btn-primary'>View Auction</a>";
    echo "</li>";
}

// If no auctions found
if ($result->num_rows == 0) {
    echo "<li class='list-group-item'>No auctions found matching your criteria.</li>";
}

// Close the statement and connection
$stmt->close();
?>

</ul>

</div>

<?php include_once("footer.php")?>
