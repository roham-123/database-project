<?php
include_once("utilities.php");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the email and password are provided
if (!isset($_POST['email']) || !isset($_POST['password'])) {
    echo "Please enter both email and password.";
    exit();
}

// Get form data from POST request
$email = trim($_POST['email']);
$password = trim($_POST['password']);

// Prepare SQL query to fetch user with the provided email
$stmt = $conn->prepare("SELECT UserID, Username, Password, Role, blacklisted FROM Users WHERE Email = ?");
if ($stmt === false) {
    echo "Error preparing statement: " . $conn->error;
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
        echo "<div class='container mt-3 alert alert-danger'>Your account has been blacklisted. Please contact support for further assistance.</div>";
    } else {
        // Verify the provided password with the hashed password in the database
        if (password_verify($password, $hashedPassword)) {
            // Set session variables
            $_SESSION['UserID'] = $user['UserID'];
            $_SESSION['Username'] = $user['Username'];
            $_SESSION['Role'] = $user['Role'];
            $_SESSION['logged_in'] = true;
            $_SESSION['Email'] = $email;

            if ($user['Role'] == 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: index.php"); // Normal user homepage
            }
            exit();
        } else {
            echo "<div class='container mt-3 alert alert-danger'>Incorrect password. Please try again.</div>";
        }
    }
} else {
    echo "<div class='container mt-3 alert alert-danger'>No account found with that email address. Please register first.</div>";
}

// Close the statement and connection
$stmt->close();
closeConnection($conn);
?>
