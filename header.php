<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<?php
// Add error reporting at the top of header.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
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

<nav class="navbar navbar-expand-lg navbar-light bg-light mx-2">
  <a class="navbar-brand" href="#">Site Name <!--CHANGEME!--></a>
  <ul class="navbar-nav ml-auto">
    <?php 
    try {
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true && isset($_SESSION['UserID'])) {
            $notifications = [];
            $notificationCount = 0;
            
            // Debug output
            echo "<!-- Debug: User is logged in as UserID: " . $_SESSION['UserID'] . " -->";
            
            if (file_exists("notification_functions.php")) {
                require_once("notification_functions.php");
                if (isset($conn)) {
                    $notifications = getUnreadNotifications($conn, $_SESSION['UserID']);
                    $notificationCount = count($notifications);
                } else {
                    echo "<!-- Debug: Database connection not available -->";
                }
            } else {
                echo "<!-- Debug: notification_functions.php not found -->";
            }
    ?>
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="notificationDropdown" role="button" 
               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fa fa-bell"></i>
                <?php if ($notificationCount > 0): ?>
                    <span class="badge badge-danger"><?php echo $notificationCount; ?></span>
                <?php endif; ?>
            </a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="notificationDropdown" style="max-width: 300px;">
                <?php if ($notificationCount > 0): ?>
                    <?php foreach ($notifications as $notification): ?>
                        <a class="dropdown-item notification-item" 
                           href="auction_details.php?auctionID=<?php echo $notification['AuctionID']; ?>"
                           data-notification-id="<?php echo $notification['NotificationID']; ?>">
                            <small class="text-muted">
                                <?php echo date('M j, g:i a', strtotime($notification['NotificationTime'])); ?>
                            </small><br>
                            <?php echo htmlspecialchars($notification['Message']); ?>
                        </a>
                        <div class="dropdown-divider"></div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <span class="dropdown-item-text">No new notifications</span>
                <?php endif; ?>
            </div>
        </li>
    <?php
        }
    } catch (Exception $e) {
        error_log("Error in header.php: " . $e->getMessage());
        echo "<!-- Debug: Error occurred: " . htmlspecialchars($e->getMessage()) . " -->";
    }
    ?>
    <li class="nav-item">
      <?php
      // Displays either login or logout on the right, depending on user's current status (session).
      if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true) {
          echo '<a class="nav-link" href="logout.php">Logout</a>';
      } else {
          echo '<button type="button" class="btn nav-link" data-toggle="modal" data-target="#loginModal">Login</button>';
          echo '<a href="register.php" class="btn btn-primary ml-2">Register</a>';
      }
      ?>
    </li>
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

<script>
$(document).ready(function() {
    // Mark notifications as read when clicked
    $('.notification-item').click(function() {
        var notificationId = $(this).data('notification-id');
        $.post('mark_notification_read.php', {
            notification_id: notificationId
        });
    });
});
</script>