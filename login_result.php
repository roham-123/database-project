<?php
include_once("utilities.php");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Checks if the email and password are provided
if (!isset($_POST['email']) || !isset($_POST['password'])) {
    echo "Please enter both email and password.";
    exit();
}

// Gets the form data from POST request
$email = trim($_POST['email']);
$password = trim($_POST['password']);

// Prepares an SQL query to fetch the user with the provided email
$stmt = $conn->prepare("SELECT UserID, Username, Password, Role, blacklisted FROM Users WHERE Email = ?");
if ($stmt === false) {
    echo "Error preparing statement: " . $conn->error;
    exit();
}
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// Checks if user exists
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $hashedPassword = $user['Password'];

    // Checks if user is blacklisted
    if ($user['blacklisted'] == 1) {
        echo "<div class='container mt-3 alert alert-danger'>Your account has been blacklisted for suspicious activity. Please contact 'admin@example.com' for further assistance.</div>";
    } else {
        // Verifies that the provided password with the hashed password is in the database
        if (password_verify($password, $hashedPassword)) {
            // Sets the session variables
            $_SESSION['UserID'] = $user['UserID'];
            $_SESSION['Username'] = $user['Username'];
            $_SESSION['Role'] = $user['Role'];
            $_SESSION['logged_in'] = true;
            $_SESSION['Email'] = $email;

            if ($user['Role'] == 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: index.php"); 
            }
            exit();
        } else {
            echo "<div class='container mt-3 alert alert-danger'>Incorrect password. Please try again.</div>";
        }
    }
} else {
    echo "<div class='container mt-3 alert alert-danger'>No account found with that email address. Please register first.</div>";
}

// Closes the statement and connection
$stmt->close();
closeConnection($conn);
?>
