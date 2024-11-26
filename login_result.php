<?php
include_once("utilities.php"); // Includes utility functions for the application

if (session_status() == PHP_SESSION_NONE) { 
    session_start(); // Start a session if it's not already started
}

# check if the email and password have been provided by the user
if (!isset($_POST['email']) || !isset($_POST['password'])) { 
    echo "<script>
            alert('Please enter both email and password.'); // prompt if either field is missing
            window.location.href = 'browse.php'; // redirects the user to the browse page
            window.onload = function() {
                $('#loginModal').modal('show'); // Reopen the login modal upon page load
            };
          </script>";
    exit();
}

#Get form data from POST request and trim any leading or trailing spaces
$email = trim($_POST['email']);
$password = trim($_POST['password']);

# Prepares the SQL statement to fetch the user with the given email
$stmt = $conn->prepare("SELECT UserID, Username, Password, Role, blacklisted FROM Users WHERE Email = ?");
if ($stmt === false) { // Check for any issues while preparing the statement
    echo "<script>
            alert('Error preparing statement.'); // Notify the user of an issue
            window.location.href = 'browse.php';
            window.onload = function() {
                $('#loginModal').modal('show'); 
            };
          </script>";
    exit();
}

$stmt->bind_param("s", $email); // Bind the email parameter to the SQL query
$stmt->execute(); // Execute the SQL query
$result = $stmt->get_result(); // Fetch the result of the query

// Check if the user exists in the database
if ($result->num_rows > 0) { 
    $user = $result->fetch_assoc(); // Fetch user details
    $hashedPassword = $user['Password']; // Get the hashed password from the database

    // Check if the user's account is blacklisted
    if ($user['blacklisted'] == 1) { 
        echo "<script>
                alert('Your account has been blacklisted for suspicious activity. Please contact admin@example.com for further assistance.');
                window.location.href = 'browse.php';
                window.onload = function() {
                    $('#loginModal').modal('show');
                };
              </script>";
    } else {
        // Verify the provided password matches the hashed password in the database
        if (password_verify($password, $hashedPassword)) { 
            // If valid, set session variables for the logged-in user
            $_SESSION['UserID'] = $user['UserID'];
            $_SESSION['Username'] = $user['Username'];
            $_SESSION['Role'] = $user['Role'];
            $_SESSION['logged_in'] = true;
            $_SESSION['Email'] = $email;

            // Redirect user based on their role
            if ($user['Role'] == 'admin') { 
                header("Location: admin_dashboard.php"); // Redirect admin to their dashboard
            } else {
                header("Location: browse.php"); // Redirect regular users to the browse page
            }
            exit();
        } else { 
            // Notify the user if the password is incorrect
            echo "<script>
                    alert('Incorrect password. Please try again.');
                    window.location.href = 'browse.php';
                    window.onload = function() {
                        $('#loginModal').modal('show'); // Show login modal again
                    };
                  </script>";
        }
    }
} else {
    // Notify the user if no account exists for the given email
    echo "<script>
            alert('No account found with that email address. Please register first.');
            window.location.href = 'browse.php';
            window.onload = function() {
                $('#loginModal').modal('show'); 
            };
          </script>";
}

// Closes the prepared statement and the database connection
$stmt->close();
closeConnection($conn);
?>
