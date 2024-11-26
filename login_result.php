<?php
include_once("utilities.php");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the email and password are provided
if (!isset($_POST['email']) || !isset($_POST['password'])) {
    echo "<script>
            alert('Please enter both email and password.');
            window.location.href = 'browse.php'; 
            window.onload = function() {
                $('#loginModal').modal('show');
            };
          </script>";
    exit();
}

// Get the form data from POST request
$email = trim($_POST['email']);
$password = trim($_POST['password']);

// Prepare an SQL query to fetch the user with the provided email
$stmt = $conn->prepare("SELECT UserID, Username, Password, Role, blacklisted FROM Users WHERE Email = ?");
if ($stmt === false) {
    echo "<script>
            alert('Error preparing statement.');
            window.location.href = 'browse.php';
            window.onload = function() {
                $('#loginModal').modal('show');
            };
          </script>";
    exit();
}
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// Check if user exists
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $hashedPassword = $user['Password'];

    // Check if user is blacklisted
    if ($user['blacklisted'] == 1) {
        echo "<script>
                alert('Your account has been blacklisted for suspicious activity. Please contact admin@example.com for further assistance.');
                window.location.href = 'browse.php';
                window.onload = function() {
                    $('#loginModal').modal('show');
                };
              </script>";
    } else {
        // Verify the provided password with the hashed password in the database
        if (password_verify($password, $hashedPassword)) {
            // Set the session variables
            $_SESSION['UserID'] = $user['UserID'];
            $_SESSION['Username'] = $user['Username'];
            $_SESSION['Role'] = $user['Role'];
            $_SESSION['logged_in'] = true;
            $_SESSION['Email'] = $email;

            // Redirect based on user role
            if ($user['Role'] == 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: browse.php"); 
            }
            exit();
        } else {
            echo "<script>
                    alert('Incorrect password. Please try again.');
                    window.location.href = 'browse.php';
                    window.onload = function() {
                        $('#loginModal').modal('show');
                    };
                  </script>";
        }
    }
} else {
    echo "<script>
            alert('No account found with that email address. Please register first.');
            window.location.href = 'browse.php';
            window.onload = function() {
                $('#loginModal').modal('show');
            };
          </script>";
}

// Close the statement and connection
$stmt->close();
closeConnection($conn);
?>