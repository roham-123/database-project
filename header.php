<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  
  <!-- Bootstrap and FontAwesome CSS -->
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

  <!-- Custom CSS file -->
  <link rel="stylesheet" href="css/custom.css">

  <title>[My Auction Site] <!--CHANGEME!--></title>
</head>

<body>

<!-- Navbars -->
<nav class="navbar navbar-expand-lg navbar-light bg-light mx-2">
  <a class="navbar-brand" href="#">ZARCHE <!--CHANGEME!--></a>
  <ul class="navbar-nav ml-auto">
    <li class="nav-item">
      <?php
      // Displays the username if logged in, otherwise shows login/register options.
      if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true) {
          echo '<span class="navbar-text mr-3">Welcome, ' . htmlspecialchars($_SESSION['Username']) . '</span>';
          echo '<a class="nav-link" href="logout.php">Logout</a>';
      } else {
          echo '<button type="button" class="btn nav-link" data-toggle="modal" data-target="#loginModal">Login</button>';
          // Add Register button when user is not logged in
          echo '<a href="register.php" class="btn btn-primary ml-2">Register</a>';
      }
      ?>
    </li>
  </ul>
</nav>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <ul class="navbar-nav align-middle">
    <li class="nav-item mx-1">
      <a class="nav-link" href="browse.php">Browse</a>
    </li>
    <?php
    // Updated to use consistent session variable for role
    if (isset($_SESSION['Role']) && $_SESSION['Role'] == 'buyer') {
      echo('
      <li class="nav-item mx-1">
        <a class="nav-link" href="mybids.php">My Bids</a>
      </li>
      <li class="nav-item mx-1">
        <a class="nav-link" href="watchlist.php">My Watchlist</a>
      </li>
      <li class="nav-item mx-1">
        <a class="nav-link" href="recommendations.php">Recommended</a>
      </li>');
    }
    if (isset($_SESSION['Role']) && $_SESSION['Role'] == 'seller') {
      echo('
      <li class="nav-item mx-1">
        <a class="nav-link" href="mylistings.php">My Listings</a>
      </li>
      <li class="nav-item ml-3">
        <a class="nav-link btn border-light" href="create_auction.php">+ Create auction</a>
      </li>');
    }
    ?>
  </ul>
</nav>

<!-- Login modal -->
<div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-labelledby="loginModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="loginModalLabel">Login</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form method="POST" action="login_result.php">
          <div class="form-group">
            <label for="loginEmail">Email address</label>
            <input type="email" class="form-control" id="loginEmail" name="email" placeholder="Enter email" required>
          </div>
          <div class="form-group">
            <label for="loginPassword">Password</label>
            <input type="password" class="form-control" id="loginPassword" name="password" placeholder="Password" required>
          </div>
          <button type="submit" class="btn btn-primary">Login</button>
        </form>
      </div>
    </div>
  </div>
</div> <!-- End modal -->